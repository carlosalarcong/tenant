# Documentacion de Migracion - Mantenedores

Este directorio contiene la documentacion completa de migracion para todas las categorias de mantenedores del sistema legacy al nuevo sistema multi-tenant.

## Indice de Categorias

| # | Categoria | Mantenedores | Complejidad | Archivo |
|---|-----------|--------------|-------------|---------|
| 1 | **Admision** | 3 | Baja | [PLAN_MIGRACION_ADMISION.md](admision/PLAN_MIGRACION_ADMISION.md) |
| 2 | **Apoyo Clinico** | 1 | Baja | [PLAN_MIGRACION_APOYO_CLINICO.md](apoyo_clinico/PLAN_MIGRACION_APOYO_CLINICO.md) |
| 3 | **Facturacion** | 1 | Baja | [PLAN_MIGRACION_FACTURACION.md](facturacion/PLAN_MIGRACION_FACTURACION.md) |
| 4 | **Liquidaciones** | 5 | Media | [PLAN_MIGRACION_LIQUIDACIONES.md](liquidaciones/PLAN_MIGRACION_LIQUIDACIONES.md) |
| 5 | **Presupuesto** | 3 | Media | [PLAN_MIGRACION_PRESUPUESTO.md](presupuesto/PLAN_MIGRACION_PRESUPUESTO.md) |
| 6 | **Taller** | 1 | Baja | [PLAN_MIGRACION_TALLER.md](taller/PLAN_MIGRACION_TALLER.md) |
| 7 | **Clinico** | 29 (5 fases) | Alta | [PLAN_MIGRACION_CLINICO.md](clinico/PLAN_MIGRACION_CLINICO.md) |
| | **TOTAL** | **43** | | **7 documentos** |

## Categorias Existentes (Referencia)

| Categoria | Mantenedores | Estado | Archivo |
|-----------|--------------|--------|---------|
| Tesoreria | 16 | COMPLETADO | [PLAN_MIGRACION_TESORERIA.md](tesoreria/PLAN_MIGRACION_TESORERIA.md) |
| Logistica | 10 | COMPLETADO | [PLAN_MIGRACION_LOGISTICA.md](logistica/PLAN_MIGRACION_LOGISTICA.md) |
| Pabellon | 13 | COMPLETADO | [PLAN_MIGRACION_PABELLON.md](pabellon/PLAN_MIGRACION_PABELLON.md) |

## Resumen de Nuevas Categorias

### 1. Admision (3 mantenedores)
- **Complejidad:** Baja
- **Entidades:** ConvenioEmpresa, MotivoAnulacion, TipoConsultaUrgencia
- **Destino:** `src/Controller/Maintainers/Admission/`
- **Archivos a crear:** 15 (3 entidades x 5 archivos)

### 2. Apoyo Clinico (1 mantenedor)
- **Complejidad:** Baja
- **Entidades:** InformeExamen
- **Destino:** `src/Controller/Maintainers/ClinicalSupport/`
- **Archivos a crear:** 5 (1 entidad x 5 archivos)

### 3. Facturacion (1 mantenedor)
- **Complejidad:** Baja
- **Entidades:** Item
- **Destino:** `src/Controller/Maintainers/Billing/`
- **Archivos a crear:** 5 (1 entidad x 5 archivos)

### 4. Liquidaciones (5 mantenedores)
- **Complejidad:** Media
- **Entidades:** AsociaEmpresaUsuario, BaseLiquidaciones, CuentasBancarias, ParticipacionProfesional, UFDiarias
- **Destino:** `src/Controller/Maintainers/Settlements/`
- **Archivos a crear:** 25 (5 entidades x 5 archivos)
- **Dependencias:** Bank, BankAccountType (ya existen en Tesoreria)

### 5. Presupuesto (3 mantenedores)
- **Complejidad:** Media
- **Entidades:** PiePresupuesto, PiePresupuestoPorFinanciador, PresupuestoPieFinanciador
- **Destino:** `src/Controller/Maintainers/Budget/`
- **Archivos a crear:** 15 (3 entidades x 5 archivos)
- **Dependencias internas:** BudgetFooter (crear primero)

### 6. Taller (1 mantenedor)
- **Complejidad:** Baja
- **Entidades:** Taller
- **Destino:** `src/Controller/Maintainers/Workshop/`
- **Archivos a crear:** 5 (1 entidad x 5 archivos)

### 7. Clinico (29 mantenedores - DIVIDIDO EN 5 FASES)
- **Complejidad:** Alta
- **Destino:** `src/Controller/Maintainers/Clinical/`
- **Archivos a crear:** 145 (29 entidades x 5 archivos)

#### Fase 1: Diagnosticos (6 entidades)
- Antecedente, Diagnostico, DiagnosticoInmunoterapia, DiagnosticoPorPatologia, EstadoDiagnostico, TipoAntecedente

#### Fase 2: Examenes (6 entidades)
- AgrupacionExamen, ExamenFisicoAgrupacion, ExamenFisicoCampo, ExamenPrestacion, TipoExamenFisico, TipoPrestacionExamen

#### Fase 3: Medicamentos (7 entidades)
- Concentracion, Dosis, Indicacion, MedicamentoBioequivalente, Periodicidad, Reaccion, TipoConcentracion

#### Fase 4: Documentos (5 entidades)
- DocumentoEncabezadoPie, DocumentoPlantillaEncabezado, DocumentoPlantillaFc, DocumentoPlantillaPiePagina, TipoDocumentoFc

#### Fase 5: Oftalmologia/Otros (5 entidades)
- TipoLente, TipoLenteDetalle, ItemAtencionPorEspecialidad, Sintoma, UbicacionCuerpo

## Resumen Total

| Concepto | Cantidad |
|----------|----------|
| Nuevas Categorias | 7 |
| Total Mantenedores | 43 |
| Total Archivos a Crear | 215 |
| Total Entities | 43 |
| Total Repositories | 43 |
| Total FormTypes | 43 |
| Total Controllers | 43 |
| Total Templates | 43 |

## Estructura de Cada Documento

Cada documento de migracion sigue la siguiente estructura estandarizada:

1. **Resumen Ejecutivo**
   - Categoria, origen, destino, total entidades, complejidad

2. **Inventario Completo**
   - Tabla con todas las entidades a migrar
   - Dependencias externas e internas
   - Entidades descartadas con razon

3. **Patron a Seguir (Referencia)**
   - Ejemplos de archivos existentes
   - Herencia de controllers
   - Convenciones de nombres
   - Formato getColumns() asociativo

4. **Fases de Migracion**
   - Entidades organizadas por complejidad
   - Para cada entidad: 5 prompts Copilot completos
     * Entity
     * Repository
     * FormType
     * Controller
     * Template

5. **Migracion de Base de Datos**
   - Comandos para generar y ejecutar migraciones

6. **Orden de Ejecucion Recomendado**
   - Secuencia optima de implementacion

7. **Registro en Menu**
   - Scripts SQL completos para menu_items
   - Comandos de cache

8. **Checklist de Validacion (DoD)**
   - Lista completa de verificacion

9. **Archivos Totales a Crear**
   - Tabla resumen por tipo de archivo

## Patron de Nombres

### Entidades (Ingles)
- Legacy ES: `ConvenioEmpresa` -> Nuevo EN: `CompanyAgreement`
- Legacy ES: `MotivoAnulacion` -> Nuevo EN: `CancellationReason`
- Legacy ES: `TipoConsultaUrgencia` -> Nuevo EN: `EmergencyConsultationType`

### Tablas (snake_case)
- `company_agreement`
- `cancellation_reason`
- `emergency_consultation_type`

### Rutas
- Patron: `app_maintainers_{category}_{entity_snake}_{action}`
- Ejemplo: `app_maintainers_admission_company_agreement_index`

### Namespaces
- Entities: `App\Entity\Tenant`
- Repositories: `App\Repository\Tenant`
- FormTypes: `App\Form\Maintainers\{Category}`
- Controllers: `App\Controller\Maintainers\{Category}`
- Templates: `templates/maintainers/{category}/`

## Convenciones Importantes

### getColumns() - Formato Asociativo (NUEVO 2026)
```php
protected function getColumns(): array {
    return [
        'name' => 'Nombre',
        'code' => 'Codigo',
        'isActive' => 'Estado',
        'branch.name' => 'Sucursal'  // Relaciones
    ];
}
```

### Multi-Tenancy
- NO agregar campo `idEmpresa` (manejado por Hakam)
- Todos los controllers extienden `AbstractTenantAwareController`

### Campos Estandar
Todas las entidades incluyen:
- `id`: integer, PK, auto-increment
- `isActive`: boolean, default true
- `idEstado`: integer, default 1
- `createdAt`: datetime, auto-set
- `updatedAt`: datetime, nullable

## Orden de Implementacion Sugerido

### Prioridad 1 (Baja Complejidad - Semana 1)
1. Taller (1 mantenedor - 5 archivos)
2. Apoyo Clinico (1 mantenedor - 5 archivos)
3. Facturacion (1 mantenedor - 5 archivos)
4. Admision (3 mantenedores - 15 archivos)
**Total Semana 1:** 6 mantenedores, 30 archivos

### Prioridad 2 (Media Complejidad - Semana 2)
5. Presupuesto (3 mantenedores - 15 archivos)
6. Liquidaciones (5 mantenedores - 25 archivos)
**Total Semana 2:** 8 mantenedores, 40 archivos

### Prioridad 3 (Alta Complejidad - Semanas 3-5)
7. Clinico - Fase 1: Diagnosticos (6 mantenedores - 30 archivos)
8. Clinico - Fase 2: Examenes (6 mantenedores - 30 archivos)
9. Clinico - Fase 3: Medicamentos (7 mantenedores - 35 archivos)
10. Clinico - Fase 4: Documentos (5 mantenedores - 25 archivos)
11. Clinico - Fase 5: Oftalmologia/Otros (5 mantenedores - 25 archivos)
**Total Semanas 3-5:** 29 mantenedores, 145 archivos

## Herramientas de Validacion

### Checklist por Mantenedor
```
[ ] Entity creada con campos correctos
[ ] Repository creado con findAllActive()
[ ] FormType creado con campos correctos
[ ] Controller creado con 5 rutas
[ ] Template creado extiende modern_index.html.twig
[ ] Migracion BD ejecutada sin errores
[ ] CRUD funciona completo
[ ] Modal funciona correctamente
[ ] Paginacion funciona
[ ] Export CSV funciona
[ ] Multi-tenant validado (Tenant A != Tenant B)
[ ] Cross-tenant access retorna 404
[ ] Sin errores en consola navegador
[ ] Registrado en menu
```

### Comandos Utiles

```bash
# Generar migracion
php bin/console tenant:migrations:diff <tenant_id>

# Ejecutar migracion
php bin/console tenant:migrations:migrate <tenant_id>

# Limpiar cache
php bin/console cache:clear

# Verificar rutas
php bin/console debug:router | grep maintainers
```

## Notas Importantes

1. **NO inventar campos** - Si no hay info del legacy, usar campos basicos
2. **Seguir EXACTAMENTE el formato** de los prompts Copilot
3. **Usar nombres en ingles** para entidades/tablas (no español)
4. **Mantener consistencia** en rutas, namespaces y convenciones
5. **Validar multi-tenancy** en cada mantenedor
6. **Crear dependencias ANTES** que las entidades que las requieren

## Soporte

Para preguntas o aclaraciones sobre cualquier categoria:
- Revisar los ejemplos existentes (Tesoreria, Logistica, Pabellon)
- Verificar el patron en `AbstractMantenedorController`
- Consultar el formato asociativo de `getColumns()`

---

**Ultima Actualizacion:** 2026-02-03
**Total Documentos:** 7 nuevas categorias + 3 existentes = 10 documentos
**Total Mantenedores Nuevos:** 43
**Total Archivos a Crear:** 215
