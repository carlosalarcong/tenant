<?php

namespace App\Tests\Unit\Repository;

use App\Repository\Tenant\PersonRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitarios para PersonRepository::findTutorFullNameByDocument
 *
 * CÓMO EJECUTAR:
 *   php bin/phpunit tests/Unit/Repository/PersonRepositoryTest.php --testdox
 *   php bin/phpunit tests/Unit/Repository/PersonRepositoryTest.php --filter testFound
 */
class PersonRepositoryTest extends TestCase
{
    private Connection $connection;
    private PersonRepository $repository;

    protected function setUp(): void
    {
        $this->connection = $this->createMock(Connection::class);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getConnection')->willReturn($this->connection);

        // disableOriginalConstructor evita la cadena ManagerRegistry → ClassMetadata.
        // onlyMethods(['getEntityManager']) intercepta la única dependencia interna
        // que usa findTutorFullNameByDocument.
        $this->repository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager'])
            ->getMock();

        $this->repository->method('getEntityManager')->willReturn($em);
    }

    // ── Documento vacío ────────────────────────────────────────────────────

    /**
     * Documento vacío → retorna null sin consultar la BD.
     */
    public function testEmptyDocumentReturnsNull(): void
    {
        $this->connection->expects($this->never())->method('fetchAssociative');

        $this->assertNull($this->repository->findTutorFullNameByDocument(''));
        $this->assertNull($this->repository->findTutorFullNameByDocument('   '));
    }

    // ── Encontrado en paso 1 (coincidencia exacta) ─────────────────────────

    /**
     * Documento con formato "12.345.678-9" → encontrado en el primer query (exacto).
     */
    public function testFoundByExactFormattedDocument(): void
    {
        $this->connection
            ->expects($this->once())                   // solo se ejecuta el query exacto
            ->method('fetchAssociative')
            ->willReturn([
                'name'        => 'María',
                'last_name'   => 'González',
                'middle_name' => 'Pérez',
            ]);

        $result = $this->repository->findTutorFullNameByDocument('12.345.678-9');

        $this->assertSame('María González Pérez', $result);
    }

    /**
     * Documento sin puntos "123456789" → encontrado en el primer query porque
     * la BD lo almacena normalizado (identification = :normalized).
     */
    public function testFoundByNormalizedDocument(): void
    {
        $this->connection
            ->expects($this->once())
            ->method('fetchAssociative')
            ->willReturn([
                'name'        => 'Carlos',
                'last_name'   => 'Rojas',
                'middle_name' => null,
            ]);

        $result = $this->repository->findTutorFullNameByDocument('123456789');

        $this->assertSame('Carlos Rojas', $result);
    }

    // ── Encontrado en paso 2 (fallback fuzzy) ─────────────────────────────

    /**
     * El primer query (exacto) no encuentra nada; el segundo (fuzzy con REPLACE)
     * sí encuentra porque la BD almacena el documento con un formato distinto.
     */
    public function testFoundByFuzzyFallback(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method('fetchAssociative')
            ->willReturnOnConsecutiveCalls(
                false,                                  // paso 1: sin resultado
                [                                       // paso 2: coincidencia fuzzy
                    'name'        => 'Ana',
                    'last_name'   => 'López',
                    'middle_name' => 'Silva',
                ]
            );

        $result = $this->repository->findTutorFullNameByDocument('12.345.678-9');

        $this->assertSame('Ana López Silva', $result);
    }

    // ── No encontrado ──────────────────────────────────────────────────────

    /**
     * Ninguno de los dos queries encuentra el documento → retorna null.
     */
    public function testNotFoundReturnNull(): void
    {
        $this->connection
            ->expects($this->exactly(2))
            ->method('fetchAssociative')
            ->willReturn(false);

        $result = $this->repository->findTutorFullNameByDocument('99.999.999-9');

        $this->assertNull($result);
    }

    // ── Nombre completo con campos parciales ──────────────────────────────

    /**
     * Si middle_name es null o vacío, el nombre completo no incluye ese segmento.
     */
    public function testFullNameSkipsEmptyMiddleName(): void
    {
        $this->connection
            ->method('fetchAssociative')
            ->willReturn([
                'name'        => 'Juan',
                'last_name'   => 'Soto',
                'middle_name' => '',
            ]);

        $this->assertSame('Juan Soto', $this->repository->findTutorFullNameByDocument('11111111-1'));
    }

    /**
     * Si todos los segmentos de nombre son vacíos o nulos → retorna null.
     */
    public function testAllNameFieldsEmptyReturnsNull(): void
    {
        $this->connection
            ->method('fetchAssociative')
            ->willReturn([
                'name'        => '',
                'last_name'   => null,
                'middle_name' => '  ',
            ]);

        $this->assertNull($this->repository->findTutorFullNameByDocument('11111111-1'));
    }
}
