<?php

namespace App\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * AuditTrailListener
 * 
 * Listener de Doctrine que automáticamente llena los campos de auditoría
 * (createdAt, createdBy, updatedAt, updatedBy) en entidades que usan AuditableTrait.
 * 
 * Se ejecuta automáticamente en:
 * - PrePersist: cuando se crea un nuevo registro → markCreated()
 * - PreUpdate: cuando se modifica un registro → markUpdated()
 * 
 * El usuario actual se obtiene de Security y se pasa a los métodos mark*()
 */
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
class AuditTrailListener
{
    public function __construct(
        private readonly Security $security
    ) {
    }

    /**
     * Se ejecuta antes de persist() en nuevas entidades
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        // Solo procesar entidades con el método markCreated (AuditableTrait)
        if (!method_exists($entity, 'markCreated')) {
            return;
        }

        $user = $this->security->getUser();
        $entity->markCreated($user);
    }

    /**
     * Se ejecuta antes de flush() en entidades modificadas
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        // Solo procesar entidades con el método markUpdated (AuditableTrait)
        if (!method_exists($entity, 'markUpdated')) {
            return;
        }

        $user = $this->security->getUser();
        $entity->markUpdated($user);
    }
}
