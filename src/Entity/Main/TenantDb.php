<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use Hakam\MultiTenancyBundle\Enum\DatabaseStatusEnum;
use Hakam\MultiTenancyBundle\Enum\DriverTypeEnum;
use Hakam\MultiTenancyBundle\Services\TenantDbConfigurationInterface;
use phpDocumentor\Reflection\DocBlock\Tags\TagWithType;

/**
 * Entity para tenant_db que implementa la interface requerida por el bundle
 */
#[ORM\Entity]
#[ORM\Table(name: 'tenant_db')]
class TenantDb implements TenantDbConfigurationInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, name: 'database_name')]
    private ?string $databaseName = null;

    #[ORM\Column(length: 50, name: 'database_status')]
    private ?string $databaseStatus = 'DATABASE_MIGRATED';

    #[ORM\Column(length: 100, unique: true, nullable: false)]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'is_active', options: ['default' => true])]
    private bool $active = true;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdentifierValue(): mixed
    {
        return $this->id;
    }

    public function getDatabaseName(): ?string
    {
        return $this->databaseName;
    }

    public function setDatabaseName(string $databaseName): self
    {
        $this->databaseName = $databaseName;
        return $this;
    }

    public function getDatabaseStatus(): DatabaseStatusEnum
    {
        return DatabaseStatusEnum::from($this->databaseStatus ?? 'DATABASE_MIGRATED');
    }

    public function setDatabaseStatus(DatabaseStatusEnum $databaseStatus): self
    {
        $this->databaseStatus = $databaseStatus->value;
        return $this;
    }

    // Métodos adicionales requeridos por la interface
    
    public function getDbName(): string
    {
        return $this->databaseName ?? '';
    }

    public function getDbUsername(): string
    {
        return $_ENV['TENANT_DB_USER'] ?? 'tenant';
    }

    public function getDbPassword(): string
    {
        return $_ENV['TENANT_DB_PASSWORD'] ?? '';
    }

    public function getDbHost(): string
    {
        return 'localhost';
    }

    public function getDbPort(): int
    {
        return 3306;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): void
    {
        $this->slug = $slug;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    public function getDsnUrl(): string
    {
        // Construir DSN en formato: mysql://user:pass@host:port/dbname
        return sprintf(
            'mysql://%s:%s@%s:%d/%s',
            $this->getDbUsername(),
            $this->getDbPassword(),
            $this->getDbHost(),
            $this->getDbPort(),
            $this->getDbName()
        );
    }

    public function getDriverType(): DriverTypeEnum
    {
        return DriverTypeEnum::MYSQL;
    }
}
