<?php

namespace App\Tests\Unit\Security\Voter;

use App\Entity\Tenant\Gender;
use App\Entity\Tenant\Member;
use App\Repository\Tenant\MaintainerRolePermissionRepository;
use App\Security\Voter\MaintainerContext;
use App\Security\Voter\MaintainerVoter;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Test Suite para MaintainerVoter
 * 
 * ACTUALIZADO (Sprint 3 - Phase 2): Tests extendidos con granularidad por categoría.
 * Ahora incluye tests para MaintainerContext y verific ación de permisos por categoría.
 * 
 * Verifica que el sistema de permisos de mantenedores funciona correctamente
 * en todos los escenarios posibles:
 * - ROLE_ADMIN → Acceso completo (wildcard *)
 * - ROLE_MAINTAINER_MANAGER → CRUD completo + Export
 * - ROLE_MAINTAINER_USER → Solo READ
 * - ROLE_CLINICAL_MANAGER → Permisos sobre categoría 'clinical'
 * - Usuario sin rol específico → Sin acceso
 * - Usuario no autenticado → Sin acceso
 * - MaintainerContext con categoría explícita
 * - Retrocompatibilidad con formato legacy
 * 
 * @author Melisa Development Team
 * @since Sprint 1.5 - Database-driven permissions (Feb 2026)
 * @since Sprint 3 - Phase 2 Category Granularity (Feb 2026)
 */
class MaintainerVoterTest extends TestCase
{
    // ===== PROPIEDADES DE LA CLASE =====
    
    /** @var MaintainerVoter - El voter REAL que vamos a probar */
    private MaintainerVoter $voter;
    
    /** @var Member&MockObject - Mock del usuario autenticado */
    private Member&MockObject $user;
    
    /** @var TokenInterface&MockObject - Mock del token de seguridad */
    private TokenInterface&MockObject $token;
    
    /** @var MaintainerRolePermissionRepository&MockObject - Mock del repositorio */
    private MaintainerRolePermissionRepository&MockObject $repository;

    /**
     * setUp() se ejecuta ANTES de cada test
     * 
     * Preparamos el voter, mocks y configuramos respuestas del repositorio.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear mock del repositorio
        $this->repository = $this->createMock(MaintainerRolePermissionRepository::class);
        
        // Configurar mock inteligente que responde según el rol
        $this->repository->expects($this->any())
            ->method('hasPermission')
            ->willReturnCallback(function($role, $permission, $category = null, $maintainer = null) {
                // ROLE_ADMIN: wildcard - acceso a todo
                if ($role === 'ROLE_ADMIN') {
                    return true;
                }
                
                // ROLE_MAINTAINER_MANAGER: CRUD completo + Export
                if ($role === 'ROLE_MAINTAINER_MANAGER') {
                    return in_array($permission, [
                        MaintainerVoter::CREATE,
                        MaintainerVoter::READ,
                        MaintainerVoter::UPDATE,
                        MaintainerVoter::DELETE,
                        MaintainerVoter::EXPORT,
                    ]);
                }
                
                // ROLE_MAINTAINER_USER: Solo READ
                if ($role === 'ROLE_MAINTAINER_USER') {
                    return $permission === MaintainerVoter::READ;
                }
                
                // ROLE_CLINICAL_MANAGER: Solo categoría 'clinical' (Phase 2)
                if ($role === 'ROLE_CLINICAL_MANAGER') {
                    return $category === 'clinical';
                }
                
                // ========== PHASE 3: Roles con granularidad por maintainer ==========
                
                // ROLE_CLINICAL_NURSE: Solo READ en Disease
                if ($role === 'ROLE_CLINICAL_NURSE') {
                    return $permission === MaintainerVoter::READ
                        && $category === 'clinical'
                        && $maintainer === 'Disease';
                }
                
                // ROLE_CLINICAL_VIEWER: READ en toda categoría clinical
                if ($role === 'ROLE_CLINICAL_VIEWER') {
                    return $permission === MaintainerVoter::READ
                        && $category === 'clinical';
                }
                
                // ROLE_DISEASE_EDITOR: CRUD completo solo en Disease
                if ($role === 'ROLE_DISEASE_EDITOR') {
                    return $category === 'clinical'
                        && $maintainer === 'Disease'
                        && in_array($permission, [
                            MaintainerVoter::CREATE,
                            MaintainerVoter::READ,
                            MaintainerVoter::UPDATE,
                            MaintainerVoter::DELETE,
                        ]);
                }
                
                // ROLE_SPECIALTY_EDITOR: READ+UPDATE solo en Specialty
                if ($role === 'ROLE_SPECIALTY_EDITOR') {
                    return $category === 'clinical'
                        && $maintainer === 'Specialty'
                        && in_array($permission, [
                            MaintainerVoter::READ,
                            MaintainerVoter::UPDATE,
                        ]);
                }
                
                // Por defecto: sin permiso
                return false;
            });
        
        // Crear el voter real con el repositorio mockeado
        $this->voter = new MaintainerVoter($this->repository);
        
        // Crear mocks del usuario y token
        $this->user = $this->createMock(Member::class);
        $this->token = $this->createMock(TokenInterface::class);
        
        // Configurar el token para que devuelva nuestro usuario
        $this->token->expects($this->any())
            ->method('getUser')
            ->willReturn($this->user);
    }
    
    /**
     * Configura el mock del repositorio para simular permisos de ROLE_ADMIN
     * ROLE_ADMIN tiene wildcard (*) = acceso completo
     */
    // ===== TESTS: ROLE_ADMIN (God mode) =====

    /**
     * ROLE_ADMIN debe tener acceso a CREATE
     */
    public function testAdminCanCreateMaintainer(): void
    {
        // Arrange: Usuario con ROLE_ADMIN
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        // Act: Solicitar permiso CREATE
        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::CREATE]);

        // Assert: El voto debe ser ACCESS_GRANTED
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ROLE_ADMIN debe tener acceso a READ
     */
    public function testAdminCanReadMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ROLE_ADMIN debe tener acceso a UPDATE
     */
    public function testAdminCanUpdateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::UPDATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ROLE_ADMIN debe tener acceso a DELETE
     */
    public function testAdminCanDeleteMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * ROLE_ADMIN debe tener acceso a EXPORT
     */
    public function testAdminCanExportMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_ADMIN']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::EXPORT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    // ===== TESTS: ROLE_MAINTAINER_MANAGER (CRUD completo) =====

    /**
     * 
     * ROLE_MAINTAINER_MANAGER debe tener acceso a CREATE
     */
    public function testManagerCanCreateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_MANAGER debe tener acceso a READ
     */
    public function testManagerCanReadMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_MANAGER debe tener acceso a UPDATE
     */
    public function testManagerCanUpdateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::UPDATE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_MANAGER debe tener acceso a DELETE
     */
    public function testManagerCanDeleteMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_MANAGER debe tener acceso a EXPORT
     */
    public function testManagerCanExportMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_MANAGER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::EXPORT]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    // ===== TESTS: ROLE_MAINTAINER_USER (Solo lectura) =====

    /**
     * 
     * ROLE_MAINTAINER_USER NO debe tener acceso a CREATE
     */
    public function testUserCannotCreateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_USER SÍ debe tener acceso a READ
     */
    public function testUserCanReadMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_USER NO debe tener acceso a UPDATE
     */
    public function testUserCannotUpdateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::UPDATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_USER NO debe tener acceso a DELETE
     */
    public function testUserCannotDeleteMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER']);

        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * 
     * ROLE_MAINTAINER_USER NO debe tener acceso a EXPORT
     */
    public function testUserCannotExportMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::EXPORT]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // ===== TESTS: Usuario sin rol específico =====

    /**
     * 
     * Usuario sin rol de mantenedores NO debe tener acceso a CREATE
     */
    public function testRegular_userCannotCreateMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER']); // Solo ROLE_USER básico

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::CREATE]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * 
     * Usuario sin rol de mantenedores NO debe tener acceso a READ
     */
    public function testRegular_userCannotReadMaintainer(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER']);

        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // ===== TESTS: Usuario no autenticado =====

    /**
     * 
     * Usuario no autenticado NO debe tener ningún acceso
     */
    public function testUnauthenticated_userHasNo_access(): void
    {
        // Token que devuelve null como usuario (no autenticado)
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn(null);

        $result = $this->voter->vote($token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * 
     * Usuario que no es instancia de Member NO debe tener acceso
     */
    public function testUserNotInstanceOfMemberHasNoAccess(): void
    {
        // Token que devuelve un mock que no es Member
        $token = $this->createMock(TokenInterface::class);
        $notMemberUser = $this->createMock(\Symfony\Component\Security\Core\User\UserInterface::class);
        $token->expects($this->any())
            ->method('getUser')
            ->willReturn($notMemberUser);

        $result = $this->voter->vote($token, Gender::class, [MaintainerVoter::READ]);

        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    // ===== TESTS: Atributos no soportados =====

    /**
     * 
     * El voter debe abstenerse para atributos no soportados
     */
    public function testVoterAbstainsFor_unsupported_attribute(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        // Atributo inventado que no existe
        $result = $this->voter->vote($this->token, Gender::class, ['INVALID_PERMISSION']);

        // El voter debe ABSTENERSE (no votar ni conceder ni denegar)
        $this->assertEquals(VoterInterface::ACCESS_ABSTAIN, $result);
    }

    // ===== TESTS: Jerarquía de roles =====

    /**
     * 
     * ROLE_ADMIN tiene prioridad sobre otros roles
     */
    public function testAdminRoleTakesPrecedence(): void
    {
        // Usuario con múltiples roles, incluyendo ADMIN
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_USER', 'ROLE_MAINTAINER_USER', 'ROLE_ADMIN']);

        // ROLE_ADMIN debe ganar, permitiendo DELETE
        $gender = new Gender();
        $result = $this->voter->vote($this->token, $gender, [MaintainerVoter::DELETE]);

        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * 
     * Verificar que todos los permisos funcionan para ROLE_ADMIN
     */
    public function testAdminHasAll_permissions(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);

        $allPermissions = [
            MaintainerVoter::CREATE,
            MaintainerVoter::READ,
            MaintainerVoter::UPDATE,
            MaintainerVoter::DELETE,
            MaintainerVoter::EXPORT,
        ];

        foreach ($allPermissions as $permission) {
            $result = $this->voter->vote($this->token, Gender::class, [$permission]);
            $this->assertEquals(
                VoterInterface::ACCESS_GRANTED, 
                $result, 
                "ROLE_ADMIN debe tener acceso a $permission"
            );
        }
    }

    /**
     * 
     * Verificar matriz completa de permisos para ROLE_MAINTAINER_MANAGER
     */
    public function testManagerHasCrud_and_export_permissions(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_MAINTAINER_MANAGER']);

        $allowedPermissions = [
            MaintainerVoter::CREATE,
            MaintainerVoter::READ,
            MaintainerVoter::UPDATE,
            MaintainerVoter::DELETE,
            MaintainerVoter::EXPORT,
        ];

        foreach ($allowedPermissions as $permission) {
            $result = $this->voter->vote($this->token, Gender::class, [$permission]);
            $this->assertEquals(
                VoterInterface::ACCESS_GRANTED, 
                $result, 
                "ROLE_MAINTAINER_MANAGER debe tener acceso a $permission"
            );
        }
    }

    /**
     * 
     * Verificar que ROLE_MAINTAINER_USER solo tiene READ
     */
    public function testUser_onlyHasRead_permission(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_MAINTAINER_USER']);

        // Debe tener READ
        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);

        // NO debe tener CREATE, UPDATE, DELETE, EXPORT
        $deniedPermissions = [
            MaintainerVoter::CREATE,
            MaintainerVoter::UPDATE,
            MaintainerVoter::DELETE,
            MaintainerVoter::EXPORT,
        ];

        foreach ($deniedPermissions as $permission) {
            $result = $this->voter->vote($this->token, Gender::class, [$permission]);
            $this->assertEquals(
                VoterInterface::ACCESS_DENIED, 
                $result, 
                "ROLE_MAINTAINER_USER NO debe tener acceso a $permission"
            );
        }
    }

    // ========================================================================
    // TESTS PHASE 2 - GRANULARIDAD POR CATEGORÍA
    // ========================================================================

    /**
     * Test: MaintainerContext permite especificar categoría explícita
     * 
     * Verifica que cuando se usa MaintainerContext con categoría explícita,
     * el voter la considera al verificar permisos.
     */
    public function testContextWithExplicitCategory_isRespected(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_CLINICAL_MANAGER']);
        
        // Mock: ROLE_CLINICAL_MANAGER solo tiene permisos sobre categoría 'clinical'
        $this->repository->expects($this->any())
            ->method('hasPermission')
            ->willReturnCallback(function($role, $permission, $category) {
                if ($role === 'ROLE_CLINICAL_MANAGER' && $category === 'clinical') {
                    return true; // Acceso completo a 'clinical'
                }
                return false;
            });
        
        // Caso 1: Entidad con categoría 'clinical' → GRANTED
        $clinicalContext = new MaintainerContext(Gender::class, 'clinical');
        $result = $this->voter->vote($this->token, $clinicalContext, [MaintainerVoter::READ]);
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $result,
            'ROLE_CLINICAL_MANAGER debe tener acceso a categoría clinical'
        );
        
        // Caso 2: Entidad con categoría 'commercial' → DENIED
        $commercialContext = new MaintainerContext(Gender::class, 'commercial');
        $result = $this->voter->vote($this->token, $commercialContext, [MaintainerVoter::READ]);
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $result,
            'ROLE_CLINICAL_MANAGER NO debe tener acceso a categoría commercial'
        );
    }

    /**
     * Test: Retrocompatibilidad - sin MaintainerContext funciona igual
     * 
     * Verifica que el voter sigue funcionando con el formato antiguo
     * (string o entidad directamente) extrayendo categoría del namespace.
     */
    public function testLegacyFormat_stillWorks(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);
        
        // Formato antiguo: string directamente
        $result = $this->voter->vote($this->token, Gender::class, [MaintainerVoter::READ]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
        
        // Formato antiguo: objeto directamente
        $entity = $this->createMock(Gender::class);
        $result = $this->voter->vote($this->token, $entity, [MaintainerVoter::UPDATE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
    }

    /**
     * Test: Wildcard (*) da acceso a todas las categorías
     * 
     * Verifica que un permiso wildcard otorga acceso sin importar la categoría.
     */
    public function testWildcardPermission_grantsAccessToAllCategories(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_ADMIN']);
        
        $categories = ['basic', 'clinical', 'commercial', 'hospital', 'human'];
        
        foreach ($categories as $category) {
            $context = new MaintainerContext(Gender::class, $category);
            $result = $this->voter->vote($this->token, $context, [MaintainerVoter::CREATE]);
            $this->assertEquals(
                VoterInterface::ACCESS_GRANTED,
                $result,
                "ROLE_ADMIN debe tener acceso a categoría $category"
            );
        }
    }

    /**
     * Test: Permiso NULL category aplica a todas las categorías
     * 
     * Verifica que un permiso sin categoría específica (NULL) es un "catch-all"
     * que otorga acceso sin importar la categoría solicitada.
     */
    public function testNullCategoryPermission_appliesToAll(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_MAINTAINER_MANAGER']);
        
        // Mock: ROLE_MAINTAINER_MANAGER tiene permisos con category=NULL (aplica a todas)
        $this->repository->expects($this->any())
            ->method('hasPermission')
            ->willReturn(true); // Sin restricción de categoría
        
        $categories = ['basic', 'clinical', 'commercial'];
        
        foreach ($categories as $category) {
            $context = new MaintainerContext(Gender::class, $category);
            $result = $this->voter->vote($this->token, $context, [MaintainerVoter::CREATE]);
            $this->assertEquals(
                VoterInterface::ACCESS_GRANTED,
                $result,
                "Permiso con category=NULL debe aplicar a $category"
            );
        }
    }

    // ========================================================================
    // TESTS PHASE 3 - GRANULARIDAD POR MANTENEDOR ESPECÍFICO
    // ========================================================================

    /**
     * Test: Permiso específico por mantenedor se respeta
     * 
     * Verifica que un permiso restringido a un mantenedor específico
     * solo otorga acceso a ese mantenedor, no a otros de la misma categoría.
     */
    public function testSpecificMaintainerPermission_isRespected(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_CLINICAL_NURSE']);
        
        // Caso 1: Disease → GRANTED
        $diseaseContext = new MaintainerContext('App\Entity\Tenant\Clinical\Disease', 'clinical', 'Disease');
        $result = $this->voter->vote($this->token, $diseaseContext, [MaintainerVoter::READ]);
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $result,
            'ROLE_CLINICAL_NURSE debe poder leer Disease'
        );
        
        // Caso 2: Specialty (misma categoría, diferente mantenedor) → DENIED
        $specialtyContext = new MaintainerContext('App\Entity\Tenant\Clinical\Specialty', 'clinical', 'Specialty');
        $result = $this->voter->vote($this->token, $specialtyContext, [MaintainerVoter::READ]);
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $result,
            'ROLE_CLINICAL_NURSE NO debe poder leer Specialty'
        );
    }

    /**
     * Test: NULL maintainer aplica a todos los mantenedores de la categoría
     * 
     * Verifica que un permiso sin maintainer específico (NULL) es un "catch-all"
     * dentro de su categoría, otorgando acceso a todos los mantenedores.
     */
    public function testNullMaintainerPermission_appliesToAllInCategory(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_CLINICAL_MANAGER']);
        
        $maintainers = ['Disease', 'Specialty', 'Department', 'TreatmentType'];
        
        foreach ($maintainers as $maintainer) {
            $context = new MaintainerContext(
                "App\\Entity\\Tenant\\Clinical\\$maintainer",
                'clinical',
                $maintainer
            );
            $result = $this->voter->vote($this->token, $context, [MaintainerVoter::CREATE]);
            $this->assertEquals(
                VoterInterface::ACCESS_GRANTED,
                $result,
                "ROLE_CLINICAL_MANAGER debe tener acceso a $maintainer"
            );
        }
    }

    /**
     * Test: Combinación category + maintainer funciona correctamente
     * 
     * Verifica que se pueden combinar filtros de categoría y mantenedor
     * para granularidad máxima.
     */
    public function testCategoryAndMaintainerCombination_works(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_SPECIALTY_EDITOR']);
        
        // Caso 1: Specialty + clinical → GRANTED
        $context = new MaintainerContext(
            'App\Entity\Tenant\Clinical\Specialty',
            'clinical',
            'Specialty'
        );
        $result = $this->voter->vote($this->token, $context, [MaintainerVoter::UPDATE]);
        $this->assertEquals(VoterInterface::ACCESS_GRANTED, $result);
        
        // Caso 2: Specialty pero category diferente → DENIED
        $wrongCategoryContext = new MaintainerContext(
            'App\Entity\Tenant\Basic\Specialty', // Asumiendo que existe
            'basic',
            'Specialty'
        );
        $result = $this->voter->vote($this->token, $wrongCategoryContext, [MaintainerVoter::UPDATE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
        
        // Caso 3: Disease + clinical (categoría correcta, mantenedor incorrecto) → DENIED
        $wrongMaintainerContext = new MaintainerContext(
            'App\Entity\Tenant\Clinical\Disease',
            'clinical',
            'Disease'
        );
        $result = $this->voter->vote($this->token, $wrongMaintainerContext, [MaintainerVoter::UPDATE]);
        $this->assertEquals(VoterInterface::ACCESS_DENIED, $result);
    }

    /**
     * Test: Permiso más específico prevalece (priority resolution)
     * 
     * Verifica que cuando un usuario tiene múltiples permisos que podrían aplicar,
     * el más específico (category+maintainer) se evalúa correctamente.
     * 
     * Caso: Usuario tiene:
     * - ROLE_CLINICAL_VIEWER (categoría completa, solo READ)
     * - ROLE_DISEASE_EDITOR (solo Disease, CRUD completo)
     * 
     * El voter debe otorgar el permiso más amplio que encuentre.
     */
    public function testPriorityResolution_moreSpecificPermissionWorks(): void
    {
        $this->user->expects($this->any())
            ->method('getRoles')
            ->willReturn(['ROLE_CLINICAL_VIEWER', 'ROLE_DISEASE_EDITOR']);
        
        // Test 1: Disease + UPDATE → GRANTED (por ROLE_DISEASE_EDITOR)
        $diseaseContext = new MaintainerContext(
            'App\Entity\Tenant\Clinical\Disease',
            'clinical',
            'Disease'
        );
        $result = $this->voter->vote($this->token, $diseaseContext, [MaintainerVoter::UPDATE]);
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $result,
            'Debe poder UPDATE Disease gracias a ROLE_DISEASE_EDITOR'
        );
        
        // Test 2: Specialty + READ → GRANTED (por ROLE_CLINICAL_VIEWER)
        $specialtyContext = new MaintainerContext(
            'App\Entity\Tenant\Clinical\Specialty',
            'clinical',
            'Specialty'
        );
        $result = $this->voter->vote($this->token, $specialtyContext, [MaintainerVoter::READ]);
        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $result,
            'Debe poder READ Specialty gracias a ROLE_CLINICAL_VIEWER'
        );
        
        // Test 3: Specialty + UPDATE → DENIED (ningún rol lo permite)
        $result = $this->voter->vote($this->token, $specialtyContext, [MaintainerVoter::UPDATE]);
        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $result,
            'NO debe poder UPDATE Specialty (solo tiene READ genérico)'
        );
    }
}

