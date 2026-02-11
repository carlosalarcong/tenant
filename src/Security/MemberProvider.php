<?php

namespace App\Security;

use App\Entity\Tenant\Member;
use App\Repository\Tenant\MemberRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Provider de usuarios usando TenantEntityManager
 * 
 * Este provider busca usuarios en la BD del tenant activo.
 * El cambio de BD lo hace automáticamente TenantDatabaseSwitchListener.
 */
class MemberProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private MemberRepository $memberRepository,
        private LoggerInterface $logger
    ) {}

    /**
     * Carga usuario por identificador (username o email)
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $this->logger->info('🔍 MemberProvider: Buscando usuario', [
            'identifier' => $identifier
        ]);

        // Buscar por username
        $user = $this->memberRepository->findOneBy([
            'username' => $identifier,
            'isActive' => true
        ]);

        // Si no se encuentra, intentar por email
        if (!$user) {
            $user = $this->memberRepository->findOneBy([
                'email' => $identifier,
                'isActive' => true
            ]);
        }

        if (!$user) {
            $this->logger->warning('⚠️ Usuario no encontrado', [
                'identifier' => $identifier
            ]);
            
            throw new UserNotFoundException(sprintf(
                'Usuario "%s" no encontrado o inactivo.',
                $identifier
            ));
        }

        $this->logger->info('✅ Usuario encontrado', [
            'id' => $user->getId(),
            'username' => $user->getUsername()
        ]);

        return $user;
    }

    /**
     * Refresca el usuario desde la BD
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof Member) {
            throw new UnsupportedUserException(sprintf(
                'Instancia de "%s" no soportada.',
                get_class($user)
            ));
        }

        // Recargar usuario desde BD
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    /**
     * Verifica si esta clase es compatible
     */
    public function supportsClass(string $class): bool
    {
        return Member::class === $class || is_subclass_of($class, Member::class);
    }

    /**
     * Actualiza la contraseña hasheada del usuario
     * DESHABILITADO: Causa problemas de refresh de token después del login
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        // Temporalmente deshabilitado para evitar que el token no pueda refrescarse
        // después del login debido a cambios en el objeto Member
        return;
        
        /* CODIGO ORIGINAL - COMENTADO
        if (!$user instanceof Member) {
            return;
        }

        $user->setPassword($newHashedPassword);
        
        // El flush se hará automáticamente después de la autenticación
        // No necesitamos acceder al EntityManager aquí
        
        $this->logger->info('🔐 Contraseña actualizada', [
            'user_id' => $user->getId()
        ]);
        */
    }
}
