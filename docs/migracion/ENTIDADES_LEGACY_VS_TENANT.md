# Análisis de Entidades: Admisión (Legacy vs Tenant)

> Comparación entre `/var/www/html/melisa_prod/src/Rebsol/HermesBundle/Entity` (Symfony 3)
> y `/var/www/html/tenant/src/Entity/Tenant` (Symfony 7.4)
>
> **Objetivo:** Identificar campos y relaciones faltantes en las entidades ya migradas.
>
> **Alcance:** Módulo Admisión (primera fase) + auditoría general de entidades tenant (segunda fase).

---

## Resumen de Entidades Migradas del Módulo Admisión

| Legacy (Symfony 3)                       | Tenant (Symfony 7.4)              | Estado       |
|------------------------------------------|-----------------------------------|--------------|
| `Paciente`                               | `Patient`                         | ✅ Completa   |
| `DatoIngreso`                            | `AdmissionRecord`                 | ✅ Completa (módulo admisión) |
| `PrPlan`                                 | `InsurancePlan`                   | ✅ Completa   |
| `ConvenioEmpresa`                        | `CompanyAgreement`                | ✅ Completa   |
| `RchCategoriaCuidados`                   | `CareCategory`                    | ✅ Completa   |
| `RchCuidados`                            | `CareIntervention`                | ✅ Completa   |
| `CierreAtencionFcDestino`                | `CareClosureDestination`          | ✅ Completa   |
| `TipoAtencionFc`                         | `CareType`                        | ✅ Completa   |
| `TipoConsultaUrgencia`                   | `EmergencyConsultationType`       | ✅ Completa   |
| `MotivoAnulacionIngreso`                 | `CancellationReason`              | ✅ Completa   |
| `TipoAnulacion`                          | `CancellationType`                | ✅ Completa   |
| `Origen`                                 | `Origin`                          | ✅ Completa   |
| `DerivadorExterno`                       | `ExternalReferrer`                | ✅ Completa   |

---

## Entidades con Cambios Aplicados

### 1. `AdmissionRecord` ← `DatoIngreso`

**Tabla:** `admission_record` / `dato_ingreso`

#### Campos faltantes añadidos

| Campo Tenant               | Campo Legacy                | Tipo         | Nullable | Default |
|----------------------------|-----------------------------|--------------|----------|---------|
| `admissionDate`            | `FECHA_INGRESO`             | datetime     | true     | —       |
| `preAdmissionDate`         | `FECHA_PREADMISION`         | datetime     | true     | —       |
| `cancellationDate`         | `FECHA_ANULACION`           | date         | true     | —       |
| `number`                   | `NUMERO`                    | integer      | false    | —       |
| `isSurgicalAdmission`      | `INGRESO_QUIRURGICO`        | boolean      | false    | false   |
| `hasMedicalOrder`          | `ORDEN_MEDICA`              | boolean      | false    | false   |
| `notes`                    | `OBSERVACION`               | string(240)  | true     | —       |
| `emergencyNotice`          | `EMERGENCIA_AVISO`          | string(100)  | true     | —       |
| `emergencyPhone`           | `EMERGENCIA_TELEFONO`       | string(10)   | true     | —       |
| `medicalOrderFile`         | `NOMBRE_ARCHIVO_ORDEN_MEDICA` | string(50) | true     | —       |
| `otherOrigin`              | `OTRO_ORIGEN`               | string(255)  | true     | —       |
| `cancellationNotes`        | `OBSERVACION_ANULACION`     | string(2000) | true     | —       |
| `withFees`                 | `CON_HONORARIOS`            | boolean      | false    | true    |
| `dau`                      | `DAU`                       | integer      | true     | —       |
| `referringDoctor`          | `MEDICO_DERIVADOR`          | string(255)  | true     | —       |

#### Relaciones faltantes añadidas

| Relación Tenant       | Relación Legacy          | Tipo      | Entidad Tenant     | Nullable |
|-----------------------|--------------------------|-----------|--------------------|----------|
| `branch`              | `ID_SUCURSAL`            | ManyToOne | `Branch`           | true     |
| `cancellationReason`  | `ID_MOTIVO_ANULACION_INGRESO` | ManyToOne | `CancellationReason` | true |
| `specialty`           | `ID_ESPECIALIDAD_MEDICA` | ManyToOne | `Specialty`        | true     |
| `professional`        | `ID_PROFESIONAL`         | ManyToOne | `Professional`     | true     |
| `insurancePlan`       | `ID_PR_PLAN`             | ManyToOne | `InsurancePlan`    | true     |
| `origin`              | `ID_ORIGEN`              | ManyToOne | `Origin`           | true     |

> **Nota:** Los campos `idUsuarioPreadmision`, `idUsuarioAnulacion`, `idUsuarioIngreso` del legacy
> se omiten porque en Symfony 7 el usuario autenticado se resuelve vía Security Component,
> no como FK en la entidad.

---

### 2. `InsurancePlan` ← `PrPlan`

**Tabla:** `insurance_plan` / `pr_plan`

#### Campos faltantes añadidos

| Campo Tenant        | Campo Legacy             | Tipo     | Nullable | Default |
|---------------------|--------------------------|----------|----------|---------|
| `cancellationDate`  | `FECHA_ANULACION`        | datetime | true     | —       |
| `isTelemedicine`    | `ES_PLAN_TELECONSULTA`   | boolean  | true     | —       |
| `isDisabled`        | `ES_INHABIL`             | boolean  | false    | false   |

#### Relaciones faltantes añadidas

| Relación Tenant    | Relación Legacy                  | Tipo      | Entidad Tenant    | Nullable |
|--------------------|----------------------------------|-----------|-------------------|----------|
| `parentPlan`       | `ID_PR_PLAN_PAQUETE_PRESTACION`  | ManyToOne | `InsurancePlan`   | true     |
| `branchPayer`      | `ID_REL_SUCURSAL_PREVISION`      | ManyToOne | `BranchPayer`     | true     |
| `cancellationUser` | `ID_USUARIO_ANULACION`           | ManyToOne | `Member`          | true     |

---

### 3. `CompanyAgreement` ← `ConvenioEmpresa`

**Tabla:** `company_agreement` / `convenio_empresa`

#### Campos faltantes añadidos

| Campo Tenant | Campo Legacy | Tipo    | Nullable | Default |
|--------------|--------------|---------|----------|---------|
| `code`       | `CODIGO`     | integer | true     | —       |

> **Nota:** El campo `ID_EMPRESA` del legacy se omite porque en el sistema multi-tenant
> la empresa/clínica es implícita al tenant (TenantEntityManager).

---

## Entidades Completas (sin cambios)

### `Patient` ← `Paciente`
Todos los campos y relaciones del legacy están presentes:
- `person` (idPnatural), `tutor` (idTutor), `payer` (idFinanciador)
- `agreement` (idConvenio), `insurancePlan` (idPlan), `careType` (idTipoAtencionFc)
- `origin` (idOrigen), `externalReferrer` (idDerivadorExterno), `professional` (idProfesional)
- `requestingCompany` (idEmpresaSolicitante)
- `eventNumber`, `careNumber`, `admissionDate`, `isExternal`, `externalProfessional`, `examOrder`

### `CareCategory` ← `RchCategoriaCuidados`
Estructura cubierta: `name`, `isActive`, `createdAt`, `updatedAt`.

### `CareIntervention` ← `RchCuidados`
Estructura cubierta: `description`, `careCategory`, `isActive`, `createdAt`, `updatedAt`.

### `CareClosureDestination` ← `CierreAtencionFcDestino`
Estructura cubierta: `name`, `isActive`, `createdAt`, `updatedAt`.

### `CareType` ← `TipoAtencionFc`
Estructura cubierta: `name`, `route`, `isActive`.

---

## Entidades Nuevas Creadas (con relaciones a tenant existente)

### Fase 1 — Módulo Admisión

| Legacy                                    | Tenant (nuevo)                        | Referencia a                                          |
|-------------------------------------------|---------------------------------------|-------------------------------------------------------|
| `EstadoIngreso`                           | `AdmissionStatus`                     | (catálogo) → usada por AdmissionRecord                |
| `Categorizacion`                          | `TriageCategory`                      | (catálogo) → usada por EmergencyAdmissionComplement   |
| `TipoCuenta`                              | `AccountType`                         | (catálogo) → usada por AdmissionRecord                |
| `EstadoRelCamaPaciente`                   | `BedAssignmentStatus`                 | (catálogo) → usada por BedPatientAssignment           |
| `RelCamaPaciente`                         | `BedPatientAssignment`                | Patient, Bed, BedAssignmentStatus                     |
| `DatoIngresoComplementoUrgencia`          | `EmergencyAdmissionComplement`        | AdmissionRecord, EmergencyConsultationType, CompanyAgreement, TriageCategory |
| `DatoIngresoComplementoUrgenciaDetalle`   | `EmergencyAdmissionComplementDetail`  | EmergencyAdmissionComplement, Article, ArticlePackage, ServicePackage |

#### Relaciones agregadas a `AdmissionRecord` en esta fase
- `admissionStatus` → `AdmissionStatus` (idEstadoIngreso) — reemplaza campo string `status`
- `accountType` → `AccountType` (idTipoCuenta)
- `bedAssignment` → `BedPatientAssignment` (idRelCamaPaciente)

### Fase 2 — Auditoría General

| Legacy       | Tenant (nuevo)  | Referencia a                                |
|--------------|-----------------|---------------------------------------------|
| `TipoBodega` | `WarehouseType` | (catálogo) → usada por Warehouse            |

---

## Auditoría General de Entidades Tenant (Fase 2)

> Revisión de entidades ya existentes en tenant contra su equivalente legacy para detectar
> campos y relaciones faltantes fuera del módulo Admisión.

> **Omisiones intencionadas:** `Person` / `Member` (tenant) y `Persona` / `Pnatural` / `UsuariosRebsol`
> (legacy) no se comparan — la arquitectura de usuario/persona del sistema nuevo es diferente
> por diseño.

### 4. `Origin` ← `Origen`

**Tabla:** `maintainer_origin` / `origen`

#### Relaciones faltantes añadidas

| Relación Tenant | Relación Legacy | Tipo      | Entidad Tenant | Nullable |
|-----------------|-----------------|-----------|----------------|----------|
| `branch`        | `ID_SUCURSAL`   | ManyToOne | `Branch`       | true     |

---

### 5. `Diagnosis` ← `Diagnostico`

**Tabla:** `diagnosis` / `diagnostico`

#### Campos faltantes añadidos

| Campo Tenant      | Campo Legacy           | Tipo        | Nullable | Default |
|-------------------|------------------------|-------------|----------|---------|
| `code`            | `CODIGO_DIAGNOSTICO`   | string(6)   | true     | —       |

#### Relaciones faltantes añadidas

| Relación Tenant   | Relación Legacy | Tipo      | Entidad Tenant | Nullable |
|-------------------|-----------------|-----------|----------------|----------|
| `parentDiagnosis` | `ID_PADRE`      | ManyToOne | `Diagnosis`    | true     |

> **Nota:** La relación `ID_PADRE` es auto-referencial (jerarquía de diagnósticos CIE-10).

---

### 6. `Warehouse` ← `Bodega`

**Tabla:** `warehouse` / `bodega`

#### Relaciones faltantes añadidas

| Relación Tenant | Relación Legacy   | Tipo      | Entidad Tenant  | Nullable |
|-----------------|-------------------|-----------|-----------------|----------|
| `warehouseType` | `ID_TIPO_BODEGA`  | ManyToOne | `WarehouseType` | true     |

---

## Entidades Legacy sin migrar (fuera del alcance actual)

| Legacy              | Descripción                                     |
|---------------------|-------------------------------------------------|
| `EntradaPacientes`  | Cola de entrada de pacientes externos           |
