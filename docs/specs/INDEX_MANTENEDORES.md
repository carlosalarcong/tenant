# 📋 Índice de Especificaciones - Sistema de Mantenedores

**Sistema**: Melisa Healthcare Multi-Tenant  
**Total Mantenedores**: 132  
**Total Categorías**: 14  
**Última actualización**: 2026-02-09

---

## 🎯 Descripción General

Sistema completo de mantenedores (datos maestros) construido sobre arquitectura estandarizada con patrón Template Method.

**Características comunes:**
- ✅ CRUD completo con AbstractMantenedorController
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV
- ✅ Traducciones i18n

---

## 📚 Especificaciones por Categoría

### Categorías Grandes (10+ mantenedores)

| Categoría | Mantenedores | Estado | Documento |
|-----------|--------------|--------|-----------|
| **Hospital** | 24 | ✅ Implementado | [SPEC_MANTENEDORES_HOSPITAL.md](SPEC_MANTENEDORES_HOSPITAL.md) |
| **Commercial** | 22 | ✅ Implementado | [SPEC_MANTENEDORES_COMMERCIAL.md](SPEC_MANTENEDORES_COMMERCIAL.md) |
| **Treasury** | 17 | ✅ Implementado | [SPEC_MANTENEDORES_TREASURY.md](SPEC_MANTENEDORES_TREASURY.md) |
| **Basic** | 14 | ✅ Implementado | [SPEC_MANTENEDORES_BASIC.md](SPEC_MANTENEDORES_BASIC.md) |
| **Surgery** | 13 | ✅ Implementado | [SPEC_MANTENEDORES_SURGERY.md](SPEC_MANTENEDORES_SURGERY.md) |
| **Clinical** | 12 | ✅ Implementado | [SPEC_MANTENEDORES_CLINICAL.md](SPEC_MANTENEDORES_CLINICAL.md) |
| **Logistics** | 10 | ✅ Implementado | [SPEC_MANTENEDORES_LOGISTICS.md](SPEC_MANTENEDORES_LOGISTICS.md) |

**Subtotal**: 112 mantenedores (85%)

---

### Categorías Medianas (3-6 mantenedores)

| Categoría | Mantenedores | Estado | Documento |
|-----------|--------------|--------|-----------|
| **Structure** | 6 | ✅ Implementado | [SPEC_MANTENEDORES_STRUCTURE.md](SPEC_MANTENEDORES_STRUCTURE.md) |
| **Settlements** | 5 | ✅ Implementado | [SPEC_MANTENEDORES_SETTLEMENTS.md](SPEC_MANTENEDORES_SETTLEMENTS.md) |
| **Admission** | 3 | ✅ Implementado | [SPEC_MANTENEDORES_ADMISSION.md](SPEC_MANTENEDORES_ADMISSION.md) |
| **Budget** | 3 | ✅ Implementado | [SPEC_MANTENEDORES_BUDGET.md](SPEC_MANTENEDORES_BUDGET.md) |

**Subtotal**: 17 mantenedores (13%)

---

### Categorías Pequeñas (1 mantenedor)

| Categoría | Mantenedores | Estado | Documento |
|-----------|--------------|--------|-----------|
| **Billing** | 1 | ✅ Implementado | [SPEC_MANTENEDORES_BILLING.md](SPEC_MANTENEDORES_BILLING.md) |
| **ClinicalSupport** | 1 | ✅ Implementado | [SPEC_MANTENEDORES_CLINICALSUPPORT.md](SPEC_MANTENEDORES_CLINICALSUPPORT.md) |
| **Workshop** | 1 | ✅ Implementado | [SPEC_MANTENEDORES_WORKSHOP.md](SPEC_MANTENEDORES_WORKSHOP.md) |

**Subtotal**: 3 mantenedores (2%)

---

## 🔍 Búsqueda Rápida por Dominio

### Gestión de Pacientes
- **Basic**: Gender, MaritalStatus, EducationLevel, EthnicGroup, Religion
- **Clinical**: MedicalHistory, PhysicalExamField, Diagnosis
- **Admission**: CancellationReason, EmergencyConsultationType

### Servicios Médicos
- **Commercial**: Specialty, MedicalService, BedType, Room
- **Structure**: MedicalService, ServiceType, Branch, Department
- **Surgery**: SurgicalBlock, AnesthesiaType, BloodType
- **Hospital**: CareIntervention, PhysicalExamField, PrescriptionDose

### Finanzas & Facturación
- **Treasury**: BankAccount, CreditCard, PaymentMethod, Cashier
- **Billing**: BillingItem
- **Budget**: BudgetFooter, BudgetFunderFooter
- **Commercial**: Payer, PayerType, TreatmentType

### Logística & Inventario
- **Logistics**: Article, ArticleWarehouse, ArticleSupplier, WarehouseSpecialty
- **Hospital**: PharmaceuticalForm, MedicationType, AdministrationRoute

### Recursos Humanos
- **Basic**: JobPosition, DoctorType, Occupation
- **Structure**: Branch, Department, CostCenter
- **Surgery**: SurgicalTeamRole

---

## 📊 Estadísticas del Sistema

### Por Implementación

| Feature | Implementado | Pendiente | Porcentaje |
|---------|--------------|-----------|------------|
| **CRUD Completo** | 132 | 0 | 100% ✅ |
| **Paginación** | 132 | 0 | 100% ✅ |
| **Exportación CSV** | 132 | 0 | 100% ✅ |
| **Turbo Frames** | 132 | 0 | 100% ✅ |
| **Forms documentados** | ~50 | ~82 | ~38% ⚠️ |
| **Tests unitarios** | 0 | 132 | 0% ❌ |
| **Permisos/Roles** | 0 | 132 | 0% ❌ |

### Por Complejidad

| Tipo | Cantidad | Ejemplos |
|------|----------|----------|
| **Simples** (name, code, isActive) | ~80 | Gender, Religion, PaymentMethod |
| **Con Relaciones** (FK, joins) | ~40 | Department→Branch, DiagnosisByPathology |
| **Complejos** (20+ campos) | ~12 | Article, Prescription, CareIntervention |

---

## 🏗️ Arquitectura Técnica

### Estructura de Directorios
```
src/Controller/Maintainers/
├── Admission/           (3 controllers)
├── Basic/               (14 controllers)
├── Billing/             (1 controller)
├── Budget/              (3 controllers)
├── Clinical/            (12 controllers)
├── ClinicalSupport/     (1 controller)
├── Commercial/          (22 controllers)
├── Hospital/            (24 controllers)
├── Logistics/           (10 controllers)
├── Settlements/         (5 controllers)
├── Structure/           (6 controllers)
├── Surgery/             (13 controllers)
├── Treasury/            (17 controllers)
└── Workshop/            (1 controller)

templates/maintainers/
├── _base_index.html.twig
├── _base_form.html.twig
├── _modal_form.html.twig
├── modern_index.html.twig
├── admission/
├── basic/
├── billing/
├── budget/
├── clinical/
├── clinical_support/
├── commercial/
├── hospital/
├── logistics/
├── settlements/
├── structure/
├── surgery/
├── treasury/
└── workshop/
```

### Stack Tecnológico
- **Backend**: Symfony 7.x + Doctrine ORM
- **Frontend**: Bootstrap 5.3 + Turbo Frames
- **Multi-tenancy**: hakam/multi-tenancy-bundle
- **Iconos**: BoxIcons (bx-*)
- **Confirmaciones**: SweetAlert2
- **Exportación**: CSV nativo

---

## 🎯 Casos de Uso Comunes

### Agregar Nuevo Mantenedor

1. Crear entidad en `src/Entity/Tenant/`
2. Crear repository en `src/Repository/Tenant/`
3. Crear controller extendiendo `AbstractMantenedorController`
4. Crear form en `src/Form/Maintainers/{Category}/`
5. Crear template en `templates/maintainers/{category}/{name}/index.html.twig`
6. Ejecutar migraciones

**Tiempo estimado**: 15-20 minutos

### Personalizar Mantenedor Existente

Ver hooks disponibles en AbstractMantenedorController:
- `beforeIndex()`, `afterIndex()`
- `beforeSave()`, `afterSave()`
- `beforeDelete()`, `afterDelete()`
- `processData()`

---

## 📖 Documentación Relacionada

- [SISTEMA_MANTENEDORES.md](../modulos/SISTEMA_MANTENEDORES.md) - Arquitectura general
- [AbstractMantenedorController.php](../../src/Controller/AbstractMantenedorController.php) - Controlador base
- [ARCHITECTURE.md](../../ARCHITECTURE.md) - Arquitectura del sistema completo

---

## 🚀 Roadmap

### Corto Plazo (Q1 2026)
- [ ] Documentar Forms faltantes (~82)
- [ ] Implementar búsqueda/filtros en listados
- [ ] Agregar soft delete configurable
- [ ] Tests unitarios para AbstractMantenedorController

### Mediano Plazo (Q2 2026)
- [ ] Sistema de permisos granulares por rol
- [ ] Import CSV masivo
- [ ] Audit trail (log de cambios)
- [ ] API REST para integraciones

### Largo Plazo (Q3-Q4 2026)
- [ ] Versionado de registros
- [ ] Workflow de aprobaciones
- [ ] Templates de export personalizables
- [ ] Dashboard analytics de uso

---

## 📞 Contacto y Soporte

Para consultas sobre mantenedores específicos, referirse al SPEC correspondiente de cada categoría.

**Última revisión**: 2026-02-09  
**Versión del sistema**: Symfony 7.x  
**Estado del branch**: `feature/mantenedores`
