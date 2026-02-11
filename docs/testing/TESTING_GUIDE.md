# 📚 Guía Completa de Tests Unitarios en Symfony con PHPUnit

## 📋 Índice
- [🎯 ¿Qué son los Tests Unitarios?](#qué-son-los-tests-unitarios)
- [🏗️ Estructura Básica](#estructura-básica)
- [🎭 Mocking: Simulación de Objetos](#mocking-simulación-de-objetos)
- [✅ Assertions: Verificaciones](#assertions-verificaciones)
- [📝 Ejemplo Completo Paso a Paso](#ejemplo-completo-paso-a-paso)
- [🚀 Comandos de Ejecución](#comandos-de-ejecución)
- [💡 Mejores Prácticas](#mejores-prácticas)
- [🔧 Configuración del Proyecto](#configuración-del-proyecto)

---

## 🎯 ¿Qué son los Tests Unitarios?

Los **tests unitarios** son pruebas automatizadas que verifican el funcionamiento correcto de **una sola unidad de código** (método, clase) de forma **aislada**.

### ✅ **Características clave:**
- **Rápidos**: Se ejecutan en milisegundos
- **Aislados**: No dependen de BD, APIs externas, archivos
- **Repetibles**: Mismo resultado siempre
- **Automatizados**: Se ejecutan sin intervención manual

### 🎯 **Beneficios:**
- ✅ Detectan errores temprano
- ✅ Facilitan refactoring seguro
- ✅ Documentan el comportamiento esperado
- ✅ Mejoran la confianza en el código

---

## 🏗️ Estructura Básica

### **Patrón AAA (Arrange-Act-Assert)**

```php
public function testNombreDescriptivo(): void
{
    // 1. ARRANGE (Preparar): Configurar datos y mocks
    $input = 'valor_entrada';
    $expectedOutput = 'valor_esperado';
    
    // 2. ACT (Actuar): Ejecutar el método bajo prueba
    $result = $this->service->metodoAProbar($input);
    
    // 3. ASSERT (Verificar): Comprobar que el resultado es correcto
    $this->assertEquals($expectedOutput, $result);
}
```

### **Estructura de archivo de test:**

```php
<?php

namespace App\Tests\Unit\Service;

use App\Service\MiServicio;
use PHPUnit\Framework\TestCase;

class MiServicioTest extends TestCase
{
    private MiServicio $servicio;

    protected function setUp(): void
    {
        $this->servicio = new MiServicio();
    }

    public function testMetodoEspecifico(): void
    {
        // Test implementation
    }
}
```

---

## 🎭 Mocking: Simulación de Objetos

Los **Mocks** son objetos "falsos" que simulan el comportamiento de dependencias externas.

### **¿Cuándo usar Mocks?**
- ✅ Conexiones a base de datos
- ✅ APIs externas
- ✅ Servicios complejos
- ✅ Operaciones costosas (archivos, red)

### **Tipos de Mocks:**

#### **1. createMock() - Mock completo:**
```php
// Mockea TODOS los métodos de la clase
$mock = $this->createMock(TenantResolver::class);
```

#### **2. createPartialMock() - Mock parcial:**
```php
// Solo mockea métodos específicos, el resto funciona normal
$mock = $this->createPartialMock(TenantResolver::class, ['getTenantBySlug']);
```

### **Configuración de Mocks:**

```php
// Configurar expectativas del mock
$mock->expects($this->once())                    // ← Debe llamarse exactamente 1 vez
    ->method('getTenantBySlug')                  // ← Método a interceptar
    ->with('melisahospital')                     // ← Parámetro esperado
    ->willReturn($expectedData);                 // ← Valor a retornar
```

### **Opciones de expects():**
- `$this->once()` - Se llama exactamente 1 vez
- `$this->exactly(3)` - Se llama exactamente 3 veces
- `$this->atLeast(1)` - Se llama al menos 1 vez
- `$this->never()` - Nunca se debe llamar

### **Opciones de will():**
- `->willReturn($value)` - Retorna un valor
- `->willThrowException($exception)` - Lanza excepción
- `->willReturnCallback($callback)` - Ejecuta función personalizada

---

## ✅ Assertions: Verificaciones

### **Assertions básicos:**

```php
// Igualdad
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual);          // Comparación estricta (===)

// Existencia
$this->assertNotNull($value);
$this->assertNull($value);

// Booleanos
$this->assertTrue($condition);
$this->assertFalse($condition);

// Arrays
$this->assertIsArray($value);
$this->assertCount(3, $array);
$this->assertArrayHasKey('key', $array);

// Objetos
$this->assertInstanceOf(Connection::class, $object);

// Excepciones
$this->expectException(\Exception::class);
$this->expectExceptionMessage('Error message');
```

### **Assertions avanzados:**

```php
// Strings
$this->assertStringContains('substring', $string);
$this->assertStringStartsWith('prefix', $string);

// Números
$this->assertGreaterThan(10, $number);
$this->assertLessThan(100, $number);

// Files
$this->assertFileExists('/path/to/file');
```

---

## 📝 Ejemplo Completo Paso a Paso

### **Caso práctico: TenantResolver**

```php
<?php

namespace App\Tests\Unit\Service;

use App\Service\TenantResolver;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TenantResolverTest extends TestCase
{
    private TenantResolver $tenantResolver;

    /**
     * Se ejecuta antes de cada test
     * Crea una instancia limpia para cada prueba
     */
    protected function setUp(): void
    {
        $this->tenantResolver = new TenantResolver();
    }

    /**
     * Test: Extracción correcta de tenant desde URL
     * 
     * OBJETIVO: Verificar que puede extraer "melisahospital" de una URL
     * MÉTODO PROBADO: resolveTenantFromRequest()
     * DEPENDENCIA MOCKEADA: getTenantBySlug() (evita conexión a BD)
     */
    public function testResolveTenantFromRequestWithValidSubdomain(): void
    {
        // 1. ARRANGE: Preparar datos de entrada y mocks
        
        // Crear request con URL que contiene subdomain
        $request = Request::create('http://melisahospital.melisaupgrade.prod/some/path');
        
        // Datos que simularían venir de la base de datos
        $expectedTenant = [
            'id' => 3,
            'name' => 'Hospital Central',
            'subdomain' => 'melisahospital',
            'database_name' => 'melisahospital',
            'host' => 'localhost',
            'host_port' => 3306,
            'db_user' => 'melisa',
            'db_password' => 'melisamelisa',
            'driver' => 'mysql',
            'is_active' => 1
        ];

        // Crear mock parcial (solo mockea getTenantBySlug, resto funciona normal)
        $resolverMock = $this->createPartialMock(TenantResolver::class, ['getTenantBySlug']);
        
        // Configurar expectativas del mock
        $resolverMock->expects($this->once())                    // ← Debe llamarse exactamente 1 vez
                    ->method('getTenantBySlug')                  // ← Mock este método específico
                    ->with('melisahospital')                     // ← Debe recibir este parámetro exacto
                    ->willReturn($expectedTenant);               // ← Retornar estos datos falsos

        // 2. ACT: Ejecutar el método que queremos probar
        $result = $resolverMock->resolveTenantFromRequest($request);

        // 3. ASSERT: Verificar que el resultado es correcto
        $this->assertNotNull($result);                          // ← Debe retornar algo, no null
        $this->assertEquals('melisahospital', $result['subdomain']); // ← Subdomain extraído correctamente
        $this->assertEquals('Hospital Central', $result['name']);     // ← Nombre del tenant correcto
        $this->assertEquals('melisahospital', $result['database_name']); // ← BD asignada correctamente
        $this->assertTrue((bool)$result['is_active']);          // ← Tenant debe estar activo
    }

    /**
     * Test: URL sin subdomain debe retornar null
     * 
     * OBJETIVO: Verificar manejo de URLs sin tenant válido
     * CASO: melisaupgrade.prod (sin subdomain)
     */
    public function testResolveTenantFromRequestWithNoSubdomain(): void
    {
        // ARRANGE
        $request = Request::create('http://melisaupgrade.prod/some/path');
        
        // ACT
        $result = $this->tenantResolver->resolveTenantFromRequest($request);

        // ASSERT
        $this->assertNull($result);
    }

    /**
     * Test: Manejo de errores de base de datos
     * 
     * OBJETIVO: Verificar que las excepciones se propagan correctamente
     * ESCENARIO: Simular fallo de conexión a BD
     */
    public function testGetTenantBySlugThrowsExceptionOnDatabaseError(): void
    {
        // ARRANGE
        $resolverMock = $this->createPartialMock(TenantResolver::class, ['getTenantBySlug']);
        $resolverMock->expects($this->once())
                    ->method('getTenantBySlug')
                    ->with('invalid_tenant')
                    ->willThrowException(new \Exception('Database connection failed'));

        // ASSERT (configurar expectativa de excepción)
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database connection failed');

        // ACT (esto debe lanzar la excepción)
        $resolverMock->getTenantBySlug('invalid_tenant');
    }
}
```

---

## 🚀 Comandos de Ejecución

### **Comandos básicos:**

```bash
# Ejecutar todos los tests del archivo
php bin/phpunit tests/Unit/Service/TenantResolverTest.php

# Formato descriptivo (recomendado)
php bin/phpunit tests/Unit/Service/TenantResolverTest.php --testdox

# Test específico
php bin/phpunit --filter testResolveTenantFromRequestWithValidSubdomain tests/Unit/Service/TenantResolverTest.php

# Ejecutar todos los tests unitarios
php bin/phpunit tests/Unit/

# Detener en primer fallo
php bin/phpunit tests/Unit/Service/TenantResolverTest.php --stop-on-failure

# Información detallada
php bin/phpunit tests/Unit/Service/TenantResolverTest.php --verbose
```

### **Filtros útiles:**

```bash
# Filtrar por patrón de nombre
php bin/phpunit --filter "Subdomain" tests/Unit/Service/TenantResolverTest.php --testdox

# Ver todos los tests disponibles
php bin/phpunit tests/Unit/Service/TenantResolverTest.php --list-tests

# Filtrar tests de manejo de errores
php bin/phpunit --filter "Exception|Null" tests/Unit/Service/TenantResolverTest.php --testdox
```

### **Interpretando resultados:**

```bash
# ✅ Todos los tests pasan
OK (12 tests, 42 assertions)

# ❌ Hay fallos
FAILURES!
Tests: 12, Assertions: 40, Failures: 1.

# 🔶 Tests con warnings
WARNINGS!
Tests: 12, Assertions: 42, Warnings: 1.
```

---

## 💡 Mejores Prácticas

### **🏷️ Naming (Nomenclatura):**

```php
// ✅ BUENO: Descriptivo y claro
public function testResolveTenantFromRequestWithValidSubdomain(): void
public function testCreateUserThrowsExceptionWhenEmailExists(): void
public function testCalculateDiscountReturnsZeroForInvalidUser(): void

// ❌ MALO: Genérico y poco claro
public function testResolve(): void
public function testCreate(): void
public function testMethod(): void
```

### **📋 Documentación:**

```php
/**
 * Test: Extracción correcta de tenant desde URL
 * 
 * OBJETIVO: Verificar que puede extraer "melisahospital" de una URL
 * MÉTODO PROBADO: resolveTenantFromRequest()
 * DEPENDENCIA MOCKEADA: getTenantBySlug() (evita conexión a BD)
 * CASOS CUBIERTOS:
 * - URL con subdomain válido
 * - Extracción correcta del subdomain
 * - Respuesta de BD simulada
 * - Verificación de estructura de datos
 */
public function testResolveTenantFromRequestWithValidSubdomain(): void
```

### **🎯 Una cosa a la vez:**

```php
// ✅ BUENO: Un test, una responsabilidad
public function testExtractSubdomainFromUrl(): void
public function testValidateTenantData(): void
public function testHandleDatabaseError(): void

// ❌ MALO: Un test haciendo muchas cosas
public function testEverything(): void
```

### **🔒 Aislamiento:**

```php
// ✅ BUENO: Cada test es independiente
public function testMethodA(): void
{
    $service = new MyService();
    // Test específico para método A
}

public function testMethodB(): void
{
    $service = new MyService();
    // Test específico para método B
}

// ❌ MALO: Tests dependientes entre sí
private $sharedData; // Evitar estado compartido
```

### **📊 Cobertura de casos:**

```php
// Cubrir casos positivos
public function testValidInput(): void

// Cubrir casos negativos  
public function testInvalidInput(): void

// Cubrir casos edge/límite
public function testEmptyInput(): void
public function testNullInput(): void
public function testMaximumInput(): void

// Cubrir excepciones
public function testExceptionHandling(): void
```

---

## 🔧 Configuración del Proyecto

### **Estructura de directorios:**

```
tests/
├── Unit/                 # Tests unitarios
│   ├── Service/
│   │   ├── TenantResolverTest.php
│   │   └── PaisServiceTest.php
│   ├── Repository/
│   └── EventListener/
├── Integration/          # Tests de integración
├── Functional/          # Tests funcionales
└── bootstrap.php        # Configuración inicial
```

### **phpunit.dist.xml básico:**

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="tests/bootstrap.php">
    
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
    </testsuites>

    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

### **Dependencias en composer.json:**

```json
{
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "symfony/test-pack": "^1.0"
    }
}
```

---

## 🎯 Conclusión

Los tests unitarios son una **inversión** en la calidad del código que:

- ✅ **Previenen regresiones** cuando cambias código
- ✅ **Documentan comportamiento** esperado
- ✅ **Facilitan refactoring** con confianza  
- ✅ **Mejoran el diseño** del código (testeable = mejor diseñado)
- ✅ **Ahorran tiempo** a largo plazo

### **🚀 Próximos pasos:**

1. **Practica** escribiendo tests para tu código existente
2. **Adopta TDD** (Test-Driven Development) para código nuevo
3. **Mide cobertura** para identificar áreas sin tests
4. **Automatiza** la ejecución en CI/CD

### **📚 Recursos adicionales:**

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Testing Guide](https://symfony.com/doc/current/testing.html)
- [Test-Driven Development](https://en.wikipedia.org/wiki/Test-driven_development)

---

**Happy Testing! 🧪✨**