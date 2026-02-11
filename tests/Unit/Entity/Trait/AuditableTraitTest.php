<?php

namespace App\Tests\Unit\Entity\Trait;

use App\Entity\Trait\AuditableTrait;
use PHPUnit\Framework\TestCase;

class AuditableTraitTest extends TestCase
{
    private function createTestEntity(): object
    {
        return new class {
            use AuditableTrait;
        };
    }

    public function testMarkCreatedSetsTimestampAndUser(): void
    {
        $entity = $this->createTestEntity();
        $userId = 123;
        
        $entity->markCreated($userId);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertSame($userId, $entity->getCreatedBy());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getUpdatedBy());
    }

    public function testMarkCreatedWithoutUserStillSetsTimestamp(): void
    {
        $entity = $this->createTestEntity();
        
        $entity->markCreated(null);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertNull($entity->getCreatedBy());
    }

    public function testMarkUpdatedSetsTimestampAndUser(): void
    {
        $entity = $this->createTestEntity();
        $entity->markCreated(100);
        
        sleep(1); // Ensure different timestamps
        
        $userId = 456;
        $entity->markUpdated($userId);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getUpdatedAt());
        $this->assertSame($userId, $entity->getUpdatedBy());
        $this->assertNotEquals($entity->getCreatedAt(), $entity->getUpdatedAt());
    }

    public function testMarkUpdatedWithoutUserStillSetsTimestamp(): void
    {
        $entity = $this->createTestEntity();
        $entity->markCreated(100);
        
        sleep(1);
        
        $entity->markUpdated(null);
        
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getUpdatedAt());
        $this->assertNull($entity->getUpdatedBy());
    }

    public function testSetCreatedAtAcceptsDateTime(): void
    {
        $entity = $this->createTestEntity();
        $date = new \DateTime('2026-01-15 10:30:00');
        
        $result = $entity->setCreatedAt($date);
        
        $this->assertSame($date, $entity->getCreatedAt());
        $this->assertSame($entity, $result); // Fluent interface
    }

    public function testSetCreatedByAcceptsInteger(): void
    {
        $entity = $this->createTestEntity();
        $userId = 789;
        
        $result = $entity->setCreatedBy($userId);
        
        $this->assertSame($userId, $entity->getCreatedBy());
        $this->assertSame($entity, $result);
    }

    public function testSetUpdatedAtAcceptsDateTime(): void
    {
        $entity = $this->createTestEntity();
        $date = new \DateTime('2026-02-10 15:45:00');
        
        $result = $entity->setUpdatedAt($date);
        
        $this->assertSame($date, $entity->getUpdatedAt());
        $this->assertSame($entity, $result);
    }

    public function testSetUpdatedByAcceptsInteger(): void
    {
        $entity = $this->createTestEntity();
        $userId = 321;
        
        $result = $entity->setUpdatedBy($userId);
        
        $this->assertSame($userId, $entity->getUpdatedBy());
        $this->assertSame($entity, $result);
    }

    public function testFluentInterfaceWorks(): void
    {
        $entity = $this->createTestEntity();
        $createdAt = new \DateTime();
        $updatedAt = new \DateTime('+1 hour');
        
        $result = $entity
            ->setCreatedAt($createdAt)
            ->setCreatedBy(111)
            ->setUpdatedAt($updatedAt)
            ->setUpdatedBy(222);
        
        $this->assertSame($entity, $result);
        $this->assertSame($createdAt, $entity->getCreatedAt());
        $this->assertSame(111, $entity->getCreatedBy());
        $this->assertSame($updatedAt, $entity->getUpdatedAt());
        $this->assertSame(222, $entity->getUpdatedBy());
    }

    public function testCreatedAtIsRequiredButOthersAreNullable(): void
    {
        $entity = $this->createTestEntity();
        
        // Only set createdAt (required)
        $entity->setCreatedAt(new \DateTime());
        
        $this->assertInstanceOf(\DateTimeInterface::class, $entity->getCreatedAt());
        $this->assertNull($entity->getCreatedBy());
        $this->assertNull($entity->getUpdatedAt());
        $this->assertNull($entity->getUpdatedBy());
    }

    public function testMarkCreatedDoesNotOverwriteExistingTimestamp(): void
    {
        $entity = $this->createTestEntity();
        $firstTimestamp = new \DateTime('2026-01-01 00:00:00');
        $entity->setCreatedAt($firstTimestamp);
        
        $entity->markCreated(999);
        
        // Should not overwrite if already set
        $this->assertSame($firstTimestamp, $entity->getCreatedAt());
        $this->assertSame(999, $entity->getCreatedBy());
    }
}
