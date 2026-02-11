# Plan de Implementación - Módulo Comercial

## 📋 Resumen Ejecutivo

**Objetivo**: Migrar 28 mantenedores del módulo Comercial del sistema antiguo (Symfony 2) al nuevo sistema (Symfony 6 + Multi-tenancy).

**Alcance Total**:
- 28+ entidades con relaciones
- 28+ repositorios
- 28+ controladores CRUD
- 28+ formularios Symfony
- 28+ vistas Twig
- Migraciones de base de datos
- Configuración de menú y permisos

**Tiempo estimado**: 3-5 semanas
**Complejidad**: Alta (relaciones complejas entre entidades)

---

## 🎯 Tier List de Complejidad - Análisis Legacy

Basado en análisis del código legacy en `/MantenedorComercial`, organizados por complejidad de implementación:

### ⚡ Tier S - Muy Simple (305-320 líneas)
**Características**: CRUD básico, sin relaciones complejas, validaciones simples
- **TipoBloqueo** (305 líneas) - Tipos de bloqueo
- **TipoPrestacion** (305 líneas) - Tipos de prestación
- **TipoAnulacion** (308 líneas) - Tipos de anulación  
- **TipoCama** (309 líneas) - Tipos de cama
- **TipoConsulta** (315 líneas) - Tipos de consulta
- **TipoTratamiento** (317 líneas) - Tipos de tratamiento

**Estimado**: 2-3 horas c/u | **Total**: 12-18 horas

---

### 🟢 Tier A - Simple (343-398 líneas)
**Características**: CRUD + algunas relaciones básicas, validaciones intermedias
- **EmpresaSolicitante** (343 líneas) - Empresas solicitantes
- **EspecialidadPorSucursal** (347 líneas) - Relación especialidad-sucursal
- **PrestadorPorSucursal** (352 líneas) - Relación prestador-sucursal
- **ItemCirugia** (360 líneas) - Items de cirugía
- **DerivadorExterno** (364 líneas) - Derivadores externos
- **Especialidad** (382 líneas) - Especialidades médicas
- **Sala** (398 líneas) - Salas hospitalarias

**Estimado**: 4-5 horas c/u | **Total**: 28-35 horas

---

### 🟡 Tier B - Medio (448-523 líneas)
**Características**: Múltiples relaciones, lógica de negocio, validaciones complejas
- **ArticuloPorPatologia** (448 líneas) - Artículos por patología
- **Eno** (472 líneas) - Patologías ENO
- **Patologia** (476 líneas) - Patologías GES
- **PrestacionPorItem** (521 líneas) - Relación prestación-item presupuestario
- **PrestacionPorServicio** (523 líneas) - Relación prestación-servicio
- **PaquetePrestacion** (537 líneas) - Paquetes de prestaciones

**Estimado**: 6-8 horas c/u | **Total**: 36-48 horas

---

### 🔴 Tier C - Complejo (617-765 líneas)
**Características**: Lógica compleja, muchas relaciones, funcionalidades especiales
- **PrestacionPorTipoCama** (617 líneas) - Relación prestación-tipo cama con cantidades
- **Prestador** (665 líneas) - Prestadores/Financiadores (Payers) con múltiples campos
- **Prestacion** (765 líneas) - Prestaciones médicas (MedicalServices)
  * 7 controladores separados
  * Asociación códigos IMED
  * Exportación Excel
  * Validaciones múltiples

**Estimado**: 10-15 horas c/u | **Total**: 30-45 horas

---

### 📊 Resumen de Estimaciones por Tier

| Tier | Cantidad | Líneas Promedio | Tiempo/Unidad | Tiempo Total |
|------|----------|----------------|---------------|--------------|
| **S** | 6 | 309 | 2-3h | 12-18h |
| **A** | 7 | 369 | 4-5h | 28-35h |
| **B** | 6 | 494 | 6-8h | 36-48h |
| **C** | 3 | 683 | 10-15h | 30-45h |
| **TOTAL** | **22** | **464** | - | **106-146h** |

**Estimado General**: 13-18 días laborales (8h/día)

---

## 🗂️ Inventario de Mantenedores

### Categoría: Tipos y Configuraciones Base (9)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 1 | TipoPrestador | TipoFinanciador | ⭐⭐⭐ Alta | Ninguna |
| 2 | TipoPrestacion | TipoPrestacion | ⭐⭐⭐ Alta | Ninguna |
| 3 | TipoConsulta | TipoConsulta | ⭐⭐ Media | Ninguna |
| 4 | TipoCama | TipoCama | ⭐⭐ Media | Ninguna |
| 5 | TipoTratamiento | TipoTratamiento | ⭐⭐ Media | Ninguna |
| 6 | TipoAnulacion | TipoAnulacion | ⭐ Baja | Ninguna |
| 7 | TipoBloqueo | TipoBloqueo | ⭐ Baja | Ninguna |
| 8 | TipoAtencionPorSucursal | TipoAtencionPorSucursal | ⭐ Baja | Branch |
| 9 | ItemPresupuestario | ItemPresupuestario | ⭐⭐ Media | Ninguna |

### Categoría: Prestadores y Financiadores (2)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 10 | Prestador | Prestador | ⭐⭐⭐ Alta | TipoFinanciador |
| 11 | PrestadorPorSucursal | PrestadorPorSucursal | ⭐⭐ Media | Prestador, Branch |

### Categoría: Prestaciones y Servicios (7)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 12 | Prestacion | Prestacion | ⭐⭐⭐ Alta | TipoPrestacion |
| 13 | PrestacionCentroCosto | PrestacionCentroCosto | ⭐⭐ Media | Prestacion, CostCenter |
| 14 | PrestacionPorItem | PrestacionPorItem | ⭐⭐ Media | Prestacion, ItemPresupuestario |
| 15 | PrestacionPorServicio | PrestacionPorServicio | ⭐⭐ Media | Prestacion, MedicalService |
| 16 | PrestacionPorTipoCama | PrestacionPorTipoCama | ⭐⭐ Media | Prestacion, TipoCama |
| 17 | PaquetePrestacion | PaquetePrestacion | ⭐ Baja | Prestacion |
| 18 | ItemCirugia | ItemCirugia | ⭐⭐ Media | Ninguna |

### Categoría: Patologías GES/ENO (3)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 19 | Patologia | PatologiaGES | ⭐⭐⭐ Alta | Ninguna |
| 20 | Eno | PatologiaENO | ⭐⭐⭐ Alta | Ninguna |
| 21 | ArticuloPorPatologia | ArticuloPorPatologia | ⭐⭐ Media | PatologiaGES |

### Categoría: Especialidades (2)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 22 | Especialidad | Especialidad | ⭐⭐⭐ Alta | Ninguna |
| 23 | EspecialidadPorSucursal | EspecialidadPorSucursal | ⭐⭐ Media | Especialidad, Branch |

### Categoría: Infraestructura Hospitalaria (2)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 24 | Sala | Sala | ⭐⭐ Media | Branch |
| 25 | Cama | Cama | ⭐⭐ Media | Sala, TipoCama |

### Categoría: Relaciones Externas (3)
| # | Mantenedor | Entidad | Prioridad | Dependencias |
|---|-----------|---------|-----------|--------------|
| 26 | DerivadorExterno | DerivadorExterno | ⭐⭐ Media | Ninguna |
| 27 | EmpresaSolicitante | EmpresaSolicitante | ⭐⭐ Media | Ninguna |
| 28 | PaqueteArticulo | PaqueteArticulo | ⭐ Baja | Ninguna |

---

## 📊 Análisis de Relaciones

### Mapa de Dependencias

```
Nivel 0 (Sin dependencias):
├── TipoFinanciador ✅ CREADA
├── TipoPrestacion
├── TipoConsulta
├── TipoCama
├── TipoTratamiento
├── TipoAnulacion
├── TipoBloqueo
├── ItemPresupuestario
├── PatologiaGES
├── PatologiaENO
├── Especialidad
├── DerivadorExterno
├── EmpresaSolicitante
└── ItemCirugia

Nivel 1 (Dependen de Nivel 0):
├── Prestador ✅ CREADA (→ TipoFinanciador)
├── Prestacion (→ TipoPrestacion)
├── ArticuloPorPatologia (→ PatologiaGES)
├── Sala (→ Branch*)
├── PaqueteArticulo
└── TipoAtencionPorSucursal (→ Branch*)

Nivel 2 (Dependen de Nivel 1):
├── PrestadorPorSucursal (→ Prestador, Branch*)
├── EspecialidadPorSucursal (→ Especialidad, Branch*)
├── Cama (→ Sala, TipoCama)
├── PrestacionCentroCosto (→ Prestacion, CostCenter*)
├── PrestacionPorItem (→ Prestacion, ItemPresupuestario)
├── PrestacionPorServicio (→ Prestacion, MedicalService*)
├── PrestacionPorTipoCama (→ Prestacion, TipoCama)
└── PaquetePrestacion (→ Prestacion)

* Entidades ya existentes en el sistema (módulo Estructura)
```

---

## 🎯 Fases de Implementación

### **FASE 1: Fundamentos (Semana 1)** ⭐⭐⭐ CRÍTICA

**Objetivo**: Crear las entidades base sin dependencias

#### Entidades a Crear (14):
1. ✅ TipoFinanciador
2. ✅ Prestador  
3. TipoPrestacion
4. TipoConsulta
5. TipoCama
6. TipoTratamiento
7. TipoAnulacion
8. TipoBloqueo
9. ItemPresupuestario
10. PatologiaGES
11. PatologiaENO
12. Especialidad
13. DerivadorExterno
14. EmpresaSolicitante

#### Tareas:
- [x] Crear entidad TipoFinanciador + Repository
- [x] Crear entidad Prestador + Repository
- [ ] Crear 12 entidades restantes + Repositories
- [ ] Generar migraciones con Hakam
- [ ] Ejecutar migraciones en DB de prueba
- [ ] Insertar datos semilla (seed data)

#### Validación:
```bash
# Verificar que las tablas existen
php bin/console hakam:doctrine:schema:validate tenant
```

---

### **FASE 2: Controladores y CRUD Básicos (Semana 1-2)** ⭐⭐⭐

**Objetivo**: Implementar CRUD para entidades sin relaciones complejas

#### Componentes por cada entidad:
- Controller (extends AbstractMantenedorController)
- Form Type
- Repository methods
- Vista Twig (modern_index.html.twig)
- Export functionality

#### Orden de implementación:
1. TipoFinanciador (Tipo Prestador)
2. TipoPrestacion
3. TipoConsulta
4. TipoCama
5. TipoTratamiento
6. ItemPresupuestario
7. PatologiaGES
8. PatologiaENO
9. Especialidad
10. DerivadorExterno
11. EmpresaSolicitante

#### Template por controlador:
```php
// Ejemplo: TipoPrestacionController.php
#[Route('/maintainers/commercial/tipo-prestacion')]
class TipoPrestacionController extends AbstractMantenedorController
{
    public function __construct(
        private TipoPrestacionRepository $repository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService
    ) {
        parent::__construct($tenantEntityManager);
        $this->setExportService($exportService);
    }
    
    // CRUD methods + export()
}
```

---

### **FASE 3: Entidades con Relaciones (Semana 2-3)** ⭐⭐

**Objetivo**: Crear entidades que dependen de Fase 1

#### Entidades a Crear (8):
1. Prestacion (→ TipoPrestacion)
2. PrestadorPorSucursal (→ Prestador, Branch)
3. EspecialidadPorSucursal (→ Especialidad, Branch)
4. ArticuloPorPatologia (→ PatologiaGES)
5. Sala (→ Branch)
6. TipoAtencionPorSucursal (→ Branch)
7. PaqueteArticulo
8. ItemCirugia

#### Consideraciones especiales:
- **Prestacion**: Entidad compleja con múltiples relaciones
- **Sala**: Requiere validación de Branch activa
- **PrestadorPorSucursal**: Many-to-Many con tabla intermedia

#### Ejemplo de relación:
```php
// En Prestacion.php
#[ORM\ManyToOne(targetEntity: TipoPrestacion::class)]
#[ORM\JoinColumn(nullable: false)]
private ?TipoPrestacion $tipoPrestacion = null;
```

---

### **FASE 4: Relaciones Complejas (Semana 3-4)** ⭐⭐

**Objetivo**: Implementar tablas de relación y configuraciones avanzadas

#### Entidades a Crear (6):
1. PrestacionCentroCosto (→ Prestacion, CostCenter)
2. PrestacionPorItem (→ Prestacion, ItemPresupuestario)
3. PrestacionPorServicio (→ Prestacion, MedicalService)
4. PrestacionPorTipoCama (→ Prestacion, TipoCama)
5. Cama (→ Sala, TipoCama)
6. PaquetePrestacion (→ Prestacion)

#### Características:
- Tablas de asociación (Many-to-Many)
- Configuraciones con datos adicionales
- Validaciones cruzadas entre entidades

---

### **FASE 5: Menú y Permisos (Semana 4)** ⭐⭐⭐

**Objetivo**: Integrar en el sistema de menú y configurar permisos

#### Tareas:
1. **Actualizar MenuDefinition.php**
```php
'maintenance_commercial' => [
    'label' => 'Comercial',
    'icon' => 'bx-dollar-circle',
    'route' => null,
    'children' => [
        'tipo_financiador' => [
            'label' => 'Tipo Financiador',
            'route' => 'app_maintainers_tipo_financiador_index',
            'icon' => 'bx-category'
        ],
        // ... más items
    ]
]
```

2. **Insertar en menu_items (melisalacolina)**
```sql
INSERT INTO menu_items (parent_id, label, route, icon, display_order, section) 
VALUES 
(NULL, 'Comercial', NULL, 'bx-dollar-circle', 30, 'maintenance'),
(LAST_INSERT_ID(), 'Tipo Financiador', 'app_maintainers_tipo_financiador_index', 'bx-category', 1, 'maintenance');
```

3. **Configurar iconos en MenuIconsTrait.php**

4. **Actualizar MenuBuilder.php** (agregar a shouldExpand)

---

### **FASE 6: Testing y Validación (Semana 4-5)** ⭐⭐

**Objetivo**: Verificar funcionalidad completa y corregir bugs

#### Checklist por entidad:
- [ ] CRUD completo funciona
- [ ] Export a CSV funciona
- [ ] Validaciones de formulario correctas
- [ ] Relaciones entre entidades correctas
- [ ] Filtros y búsquedas funcionan
- [ ] Paginación correcta
- [ ] Estados activo/inactivo funcionan
- [ ] Turbo Frames no interfieren
- [ ] Responsive design OK
- [ ] Cache se invalida correctamente

#### Tests automatizados:
```php
// tests/Functional/Maintainers/Commercial/TipoPrestacionControllerTest.php
class TipoPrestacionControllerTest extends WebTestCase
{
    public function testIndex(): void
    {
        // Test CRUD operations
    }
}
```

---

### **FASE 7: Migración de Datos (Semana 5)** ⭐⭐⭐

**Objetivo**: Migrar datos del sistema antiguo al nuevo

#### Scripts de migración:
```php
// src/Command/MigrateComercialDataCommand.php
class MigrateComercialDataCommand extends Command
{
    // Migrar tipo_prevision → tipo_financiador
    // Migrar prevision → prestador
    // etc.
}
```

#### Orden de migración:
1. Tipos y configuraciones (sin FK)
2. Entidades con FK nivel 1
3. Tablas de relación
4. Verificación de integridad

---

## 📝 Estructura de Archivos por Mantenedor

Para cada mantenedor se debe crear:

```
src/
├── Entity/Tenant/
│   └── [NombreEntidad].php
├── Repository/Tenant/
│   └── [NombreEntidad]Repository.php
├── Controller/Maintainers/Commercial/
│   └── [NombreEntidad]Controller.php
├── Form/Maintainers/
│   └── [NombreEntidad]Type.php
templates/maintainers/commercial/[nombre_entidad]/
└── index.html.twig

migrations/Tenant/
└── Version[TIMESTAMP]_create_[nombre_tabla].php
```

---

## 🔧 Comandos Útiles

### Generar entidad y repositorio:
```bash
php bin/console make:entity --tenant Tenant/NombreEntidad
```

### Generar migración:
```bash
php bin/console hakam:doctrine:migrations:diff tenant
```

### Ejecutar migraciones:
```bash
php bin/console hakam:doctrine:migrations:migrate tenant
```

### Validar schema:
```bash
php bin/console hakam:doctrine:schema:validate tenant
```

### Limpiar cache:
```bash
php bin/console cache:clear
```

---

## ⚠️ Riesgos y Consideraciones

### Riesgos Técnicos:
1. **Relaciones circulares**: Prestacion ↔ ItemPresupuestario
2. **Nombres legacy**: `prevision` vs `prestador`
3. **Campos HL7**: Compatibilidad con sistema de laboratorio
4. **Códigos FONASA/ISAPRE**: Validación de códigos oficiales

### Dependencias Externas:
- Branch (Sucursal) - Ya existe en Estructura
- CostCenter - Ya existe en Estructura  
- MedicalService - Ya existe en Estructura
- Department - Ya existe en Estructura

### Performance:
- Prestacion puede tener miles de registros
- Export debe usar streaming (ya implementado)
- Índices en columnas de búsqueda frecuente

---

## 📈 Métricas de Progreso

| Fase | Entidades | Controllers | Forms | Views | Estado |
|------|-----------|-------------|-------|-------|--------|
| Fase 1 | 2/14 | 0/14 | 0/14 | 0/14 | 🟡 14% |
| Fase 2 | 0/11 | 0/11 | 0/11 | 0/11 | ⚪ 0% |
| Fase 3 | 0/8 | 0/8 | 0/8 | 0/8 | ⚪ 0% |
| Fase 4 | 0/6 | 0/6 | 0/6 | 0/6 | ⚪ 0% |
| Fase 5 | N/A | N/A | N/A | N/A | ⚪ 0% |
| Fase 6 | N/A | N/A | N/A | N/A | ⚪ 0% |
| Fase 7 | N/A | N/A | N/A | N/A | ⚪ 0% |
| **TOTAL** | **2/39** | **0/39** | **0/39** | **0/39** | **🟡 5%** |

---

## 🎯 Próximos Pasos Inmediatos

1. ✅ Documento de planificación creado
2. ⏭️ Completar entidades Fase 1 (12 restantes)
3. ⏭️ Crear repositorios para todas las entidades
4. ⏭️ Generar migraciones con Hakam
5. ⏭️ Ejecutar migraciones y validar schema
6. ⏭️ Iniciar Fase 2: Controladores básicos

---

## 📚 Referencias

- [SISTEMA_MANTENEDORES.md](./SISTEMA_MANTENEDORES.md) - Arquitectura de mantenedores
- [SISTEMA_EXPORTACION.md](./SISTEMA_EXPORTACION.md) - Sistema de exportación CSV
- [MODAL_SYSTEM.md](./MODAL_SYSTEM.md) - Sistema de modales
- [MIGRACIONES_HAKAM.md](./MIGRACIONES_HAKAM.md) - Migraciones multi-tenant

---

**Última actualización**: 2026-01-26  
**Responsable**: Equipo de Desarrollo  
**Estado**: 🟡 En Progreso (Fase 1)
