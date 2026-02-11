<?php

namespace App\Tests\Unit\Entity\Trait;

use App\Entity\Trait\SoftDeletableTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Tests para SoftDeletableTrait
 */
class SoftDeletableTraitTest extends TestCase
{
    private object $entity;

    protected function setUp(): void
    {
        // Crear entidad anónima que usa el trait
        $this->entity = new class {
            use SoftDeletableTrait;
        };
    }

    public function testSoftDeleteMarksEntityAsDeleted(): void
    {
        // Arrange
        $user = $this->createMockUser(123);

        // Act
        $this->entity->softDelete($user);

        // Assert
        $this->assertNotNull($this->entity->getDeletedAt());
        $this->assertEquals(123, $this->entity->getDeletedById());
        $this->assertTrue($this->entity->isDeleted());
        $this->assertInstanceOf(\DateTimeInterface::class, $this->entity->getDeletedAt());
    }

    public function testSoftDeleteWithoutUserStillMarksDate(): void
    {
        // Act
        $this->entity->softDelete(null);

        // Assert
        $this->assertNotNull($this->entity->getDeletedAt());
        $this->assertNull($this->entity->getDeletedById());
        $this->assertTrue($this->entity->isDeleted());
    }

    public function testRestoreClearsDeletedFields(): void
    {
        // Arrange
        $user = $this->createMockUser(456);
        $this->entity->softDelete($user);

        // Act
        $this->entity->restore();

        // Assert
        $this->assertNull($this->entity->getDeletedAt());
        $this->assertNull($this->entity->getDeletedById());
        $this->assertFalse($this->entity->isDeleted());
    }

    public function testIsDeletedReturnsTrueWhenDeletedAtIsSet(): void
    {
        // Arrange
        $this->entity->setDeletedAt(new \DateTime());

        // Assert
        $this->assertTrue($this->entity->isDeleted());
    }

    public function testIsDeletedReturnsFalseWhenDeletedAtIsNull(): void
    {
        // Assert
        $this->assertFalse($this->entity->isDeleted());
    }

    public function testSetDeletedAtAcceptsDateTime(): void
    {
        // Arrange
        $date = new \DateTime('2026-02-09 14:30:00');

        // Act
        $result = $this->entity->setDeletedAt($date);

        // Assert
        $this->assertSame($date, $this->entity->getDeletedAt());
        $this->assertSame($this->entity, $result); // Fluent interface
    }

    public function testSetDeletedByIdAcceptsInteger(): void
    {
        // Act
        $result = $this->entity->setDeletedById(789);

        // Assert
        $this->assertEquals(789, $this->entity->getDeletedById());
        $this->assertSame($this->entity, $result); // Fluent interface
    }

    public function testFluentInterfaceWorks(): void
    {
        // Act
        $result = $this->entity
            ->setDeletedAt(new \DateTime())
            ->setDeletedById(999);

        // Assert
        $this->assertSame($this->entity, $result);
        $this->assertNotNull($this->entity->getDeletedAt());
        $this->assertEquals(999, $this->entity->getDeletedById());
    }

    /**
     * Helper para crear mock de usuario
     */
    private function createMockUser(int $id): object
    {
        return new class($id) {
            private int $id;

            public function __construct(int $id)
            {
                $this->id = $id;
            }

            public function getId(): int
            {
                return $this->id;
            }
        };
    }
}
