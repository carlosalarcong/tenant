# CAJA — Historial de Migraciones (Tenant)

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23
> **Directorio:** `migrations/Tenant/`

---

## Índice

1. [Resumen ejecutivo](#1-resumen-ejecutivo)
2. [Migraciones previas al módulo de Caja (Feb 1–18)](#2-migraciones-previas-al-módulo-de-caja-feb-1--18)
3. [Migraciones del módulo de Admisión (Feb 18–21)](#3-migraciones-del-módulo-de-admisión-feb-18--21)
4. [Migraciones de infraestructura y roles (Feb 9–10)](#4-migraciones-de-infraestructura-y-roles-feb-9--10)
5. [Migraciones del módulo de Caja (Feb 22–23)](#5-migraciones-del-módulo-de-caja-feb-22--23)
6. [Línea de tiempo visual](#6-línea-de-tiempo-visual)
7. [Inventario completo de tablas por migración](#7-inventario-completo-de-tablas-por-migración)

---

## 1. Resumen ejecutivo

| Métrica | Valor |
|---|---|
| Total de migraciones | 30 |
| Rango de fechas | 2026-02-01 → 2026-02-23 |
| Tablas creadas | ~100+ |
| Migraciones con descripción explícita | 5 |
| Migraciones más grandes | Version20260220040055 (410 líneas), Version20260221030322 (323 líneas), Version20260218185255 (227 líneas), Version20260222052433 (157 líneas) |
| Entidad más modificada | `admission_record` (3 alteraciones) |

### Dominios cubiertos

| Dominio | # Tablas | Migraciones principales |
|---|---|---|
| Catálogos de pago / bancarios | ~14 | Version20260201135350 |
| Artículos e inventario | ~7 | Version20260203121750 |
| Cirugía / Anestesia | ~13 | Version20260203132251 |
| Clínica (acciones, prescripciones, físico) | ~20+ | Version20260203151950 |
| Catálogos varios (diagnóstico, exámenes) | ~15 | Version20260204* |
| Roles y permisos | 2 | Version20260209164222, Version20260209161841 |
| Admisión | ~10 | Version20260218185255, Version20260221030322 |
| Caja / Revenue (MVP) | 9 tablas nuevas + 2 FKs | Version20260222052433 |
| BonoWeb / DTE | 3 | Version20260223023720 |

---

## 2. Migraciones previas al módulo de Caja (Feb 1–18)

### Version20260201135350 — Catálogos de pago e infraestructura bancaria

**Fecha:** 2026-02-01
**Descripción:** *(vacía)*

Crea 18 secuencias y 18 tablas fundacionales del dominio financiero:

#### Banca y medios de pago
| Tabla | Campos clave |
|---|---|
| `bank` | rut, name, current_account, is_active, id_estado |
| `bank_account_type` | name, is_active, id_estado |
| `credit_card` | credit_card_type_id FK, name, abbreviation, is_active |
| `credit_card_type` | name, is_active |
| `currency_type` | name, is_clp, is_active |
| `transfer_indicator` | code, name, is_active |

#### Facturación
| Tabla | Campos clave |
|---|---|
| `billing_payment_method` | code (3 chars), name, is_cash, is_active |
| `payment_condition` | name, interface_code, max_term, is_up_to_date, is_active |
| `payment_method` | parent_id (self-ref), payment_method_type_id FK, code, name, issuesReceipt, isGuarantee, isProfessionalPayment, isWebPayment, documentTypeCode, accountingCode, visibleInCashRegister, creditCardPayment, is_active |
| `payment_method_type` | name, is_active |

#### Diferencias y gratuidades
| Tabla | Campos clave |
|---|---|
| `cash_register_location` | branch_id FK, name, description, is_active |
| `difference_direction` | name, is_active |
| `difference_reason` | difference_direction_id FK, name, is_active |
| `difference_type` | difference_direction_id FK, name, description, is_active |
| `gratuity_reason` | branch_id FK, gratuity_type_id FK, name, is_active |
| `gratuity_type` | name, is_active |
| `document_type` | sii_code, name, is_dte, is_logistics, is_active |

---

### Version20260202222352 — Duplicado completo (artefacto de desarrollo)

**Fecha:** 2026-02-02
**Descripción:** *(vacía)*

**Nota:** Recrea exactamente las mismas 18 tablas que `Version20260201135350`. Artefacto de proceso de desarrollo — probablemente generado por un diff incompleto.

---

### Version20260203115437 — Artículos: tipos de egreso e inventario

**Fecha:** 2026-02-03

**Tablas creadas:**
- `article_outflow_type`: tipos de egreso de artículos
- `inventory_adjustment_reason`: motivos de ajuste de inventario
- `product_condition_type`: tipos de condición de producto

**Modificación:** Elimina `DEFAULT` de columnas `id` en las 18 tablas anteriores.

---

### Version20260203121750 — Artículos y bodegas

**Fecha:** 2026-02-03

**Tablas creadas:**

| Tabla | Descripción |
|---|---|
| `article` | Artículo clínico/farmacéutico. Incluye campos: code, name, stock thresholds, cenabast_code, is_pharmaceutical, account_group_code |
| `article_supplier` | Proveedor de artículo |
| `article_type` | Tipo de artículo (vinculado a bodega) |
| `article_warehouse` | Stock por bodega con umbrales críticos |
| `dispatch_type` | Tipos de despacho |
| `signature_footer` | Pie de firma por sucursal |
| `warehouse_specialty` | Especialidades asociadas a una bodega |

---

### Version20260203132251 — Cirugía y bloque quirúrgico

**Fecha:** 2026-02-03

**Tablas creadas (13):**
- `anesthesia_type`, `blood_type`, `wound_type` — catálogos básicos
- `surgery_block_reason`, `surgery_cancellation_reason`, `surgery_suspension_cause` — motivos
- `surgery_patient_status`, `surgery_patient_status_config` — estado del paciente quirúrgico
- `surgical_block` — bloque físico del pabellón (vinculado a servicio médico)
- `surgical_stage` — etapas del proceso quirúrgico
- `surgical_stage_item` — ítems dentro de una etapa (auto-referencia padre-hijo)
- `surgical_team_role` — roles del equipo quirúrgico
- `treatment_regimen` — regímenes de tratamiento

---

### Version20260203151950 — Sistema clínico: acciones, prescripciones, examen físico

**Fecha:** 2026-02-03

**Tablas creadas (23+):**

#### ClinicalAction (Cuestionario clínico)
| Tabla | Descripción |
|---|---|
| `clinical_action_category` | Categoría de acción clínica |
| `clinical_action_question` | Pregunta parametrizada |
| `clinical_action_answer` | Respuesta/opción a una pregunta |

#### Prescripciones
| Tabla | Descripción |
|---|---|
| `prescription_type` | Tipos de prescripción |
| `prescription_format` | Formatos |
| `prescription_frequency` | Frecuencias |
| `prescription_route` | Vías de administración |
| `prescription_dosage` | Dosis |
| `prescription_dispensation` | Dispensación |
| `prescription_rule_detail` | Reglas de dosificación |

#### Examen físico
| Tabla | Descripción |
|---|---|
| `physical_exam_base_field` | Campo base de examen |
| `physical_exam_grouping` | Agrupamiento de campos |
| `physical_exam_field` | Campo completo con rango, edad, flags de medición |

#### Nutrición y diagnóstico
- `nutritional_diagnosis`, `nutritionist_bmi_index`, `nutritionist_index_classification`, `nutritionist_te_index`
- `eating_disorder_history`, `intoxication_state`

#### Cuidados
- `care_category`, `care_intervention`, `care_closure_destination`, `dosage_type`, `medical_device`

---

### Version20260204112730 — Catálogos de admisión

**Fecha:** 2026-02-04

**Tablas creadas:**
- `cancellation_reason` — motivos de cancelación de admisión
- `company_agreement` — convenios con empresas
- `emergency_consultation_type` — tipos de consulta de urgencia

---

### Version20260204113831 / 120247 — Catálogos de exámenes y facturación

**Fecha:** 2026-02-04

- `exam_report` — informes de examen
- `billing_item` — ítems de facturación (prestaciones)

---

### Version20260204192124 — Cuentas bancarias y settlement

**Fecha:** 2026-02-04

**Tablas creadas:**
- `bank_account` — cuentas bancarias del tenant (bank_id + bank_account_type_id FK)
- `company_user_association` — asociación usuario-empresa
- `daily_uf` — valor diario de la UF (Unidad de Fomento)
- `professional_participation` — tipos de participación de profesionales
- `settlement_base` — bases de liquidación

---

### Version20260204192638 / 192829 / 193532 / 200708 — Presupuesto, talleres, diagnósticos, exámenes

**Fecha:** 2026-02-04 (múltiples)

- `budget_footer`, `budget_footer_by_funder`, `budget_funder_footer` — pies de presupuesto
- `workshop` — talleres
- `diagnosis`, `diagnosis_by_pathology`, `diagnosis_status`, `immunotherapy_diagnosis` — diagnósticos
- `medical_history`, `medical_history_type` — historias clínicas
- `exam_group`, `exam_service`, `exam_service_type`, `physical_exam_group`, `physical_exam_type` — grupos de exámenes

---

### Version20260204203000 — Menú: secuencia de autoincremento

**Fecha:** 2026-02-04
**Descripción:** `"Agrega secuencia de autoincremento al campo id de menu_items"`

```sql
CREATE SEQUENCE IF NOT EXISTS menu_items_id_seq ...
ALTER TABLE menu_items ALTER COLUMN id SET DEFAULT nextval('menu_items_id_seq')
```

---

## 3. Migraciones del módulo de Admisión (Feb 18–21)

### Version20260210185459 — Tabla admission_record base

**Fecha:** 2026-02-10
**Descripción:** `"Crear tabla admission_record con relaciones hacia person/payer/agreement/service/bed"`

Primera creación de `admission_record` con campos básicos: `person_id`, `payer_id`, `agreement_id`, `service_id`, `bed_id`, `admission_type`, `status`, `triage`, `consultation_reason`.

---

### Version20260211200342 — Patient, InsurancePlan, CareType

**Fecha:** 2026-02-11

| Tabla | Campos destacados |
|---|---|
| `care_type` | name, route, is_active |
| `insurance_plan` | name, is_package, is_active |
| `patient` | person_id, payer_id, agreement_id, insurance_plan_id, care_type_id, admission_date, is_external |

---

### Version20260211201748 — Renombrado person→patient en admission_record

**Fecha:** 2026-02-11

```sql
ALTER TABLE admission_record RENAME COLUMN person_id TO patient_id;
/* FK ahora apunta a patient, no a person */
```

---

### Version20260218185255 — Admisión: estados, asignación de cama, urgencias

**Fecha:** 2026-02-18

**Tablas creadas:**
- `account_type` — tipos de cuenta (hospitalización, cirugía, urgencia)
- `admission_status` — estados de admisión
- `bed_assignment_status`, `bed_patient_assignment` — asignación de cama al paciente
- `triage_category` — categorías de triage (con color)
- `warehouse_type` — tipos de bodega
- `emergency_admission_complement` — datos de urgencia (triage, lesión, acompañante, policial, etc.)
- `emergency_admission_complement_detail` — artículos/paquetes en la urgencia

**Columnas agregadas a `admission_record`:**
`admission_status_id`, `branch_id`, `professional_id`, `cancellation_reason_id`, `specialty_id`, `insurance_plan_id`, `origin_id`, `account_type_id`, `bed_patient_assignment_id`, `admission_date`, `pre_admission_date`, `cancellation_date`, `number`, `is_surgical_admission`, `has_medical_order`, `notes`, `emergency_notice`, `emergency_phone`, `medical_order_file`, `other_origin`, `cancellation_notes`, `with_fees`, `dau`, `referring_doctor`

---

### Version20260218203018 — PatientAccount, PaymentAccount, AccountStatus

**Fecha:** 2026-02-18

**Tablas creadas:**
- `account_status` — estados de cuenta del paciente
- `payment_status` — estados de pago
- `patient_account` — cuenta de un paciente: total, descuentos, balance, previsiones
- `payment_account` — un cobro específico: payment_status FK, montos, folio, fecha de cancelación, regularización

**Modificaciones a `admission_record`:**
- Añade `patient_account_id` FK con UNIQUE constraint
- Cambia `patient_id` a UNIQUE INDEX

---

### Version20260220040055 — RESET MASIVO de desarrollo

**Fecha:** 2026-02-20
**Descripción:** `"Add room.service_id FK, create nursing tables, clean up legacy tables (patient, care_type, etc.), align admission_record schema"`

⚠️ **Esta migración elimina 14 tablas** (TRUNCATE + DROP) y revierte `admission_record` a su estado base. Es un reset de datos de desarrollo por incompatibilidad de esquemas.

**Tablas eliminadas:**
`patient`, `care_type`, `insurance_plan`, `patient_account`, `payment_account`, `payment_status`, `bed_patient_assignment`, `account_type`, `admission_status`, `bed_assignment_status`, `emergency_admission_complement`, `emergency_admission_complement_detail`, `triage_category`, `warehouse_type`

**Tablas creadas (nuevas):** 8 tablas de enfermería:
- `nursing_charge` — cargos de enfermería (payload JSON)
- `nursing_concurrency` — bloqueo de edición concurrente (UNIQUE en admission_record_id)
- `nursing_discharge` — alta de enfermería
- `nursing_order` — órdenes de enfermería
- `nursing_prescription` — prescripciones de enfermería
- `nursing_prescription_item` — ítems de prescripción
- `nursing_return` — devoluciones
- `nursing_transfer` — traslados entre servicios

**Otras modificaciones:**
- `room`: añade `service_id` FK
- `admission_record`: revierte a `person_id` FK hacia `person`, elimina todas las columnas nuevas

---

### Version20260221030322 — Restauración de tablas de paciente y cuenta

**Fecha:** 2026-02-21
**Descripción:** *(vacía)*

Recrea las mismas 14 tablas eliminadas en `Version20260220040055`, restaurando la infraestructura de admisión-caja. Esencialmente el estado final correcto del esquema.

Diferencias respecto a `Version20260218`:
- `admission_record` vuelve a tener `patient_id` FK hacia `patient`
- Todas las tablas de nursing permanecen (no se tocan)

---

## 4. Migraciones de infraestructura y roles (Feb 9–10)

### Version20260209161841 — Permisos dinámicos de mantenedores

**Fecha:** 2026-02-09
**Descripción:** `"Crear tabla maintainer_role_permission para gestión dinámica de permisos de mantenedores"`

**Tabla:** `maintainer_role_permission` — `role`, `permission`, `granted`, `category`, `maintainer`, `description`, `priority`, `is_active`

**Datos semilla:**

| Rol | Permisos | Prioridad |
|---|---|---|
| `ROLE_ADMIN` | `*` (wildcard) | 100 |
| `ROLE_MAINTAINER_MANAGER` | CREATE, READ, UPDATE, DELETE, EXPORT | 50 |
| `ROLE_MAINTAINER_USER` | READ | 10 |

---

### Version20260209164222 — Tabla de roles del sistema

**Fecha:** 2026-02-09
**Descripción:** `"Crea tabla role para gestión dinámica de roles del sistema y seed de roles iniciales"`

**Tabla:** `role` — `code` (UNIQUE), `name`, `description`, `position`, `is_active`, `is_system`

**9 roles semilla:**
1. `ROLE_ADMIN` (pos. 1)
2. `ROLE_MAINTAINER_MANAGER` (pos. 2)
3. `ROLE_MAINTAINER_USER` (pos. 3)
4. `ROLE_DOCTOR` (pos. 4)
5. `ROLE_ENFERMERA` (pos. 5)
6. `ROLE_RECEPCION` (pos. 6)
7. `ROLE_CLINICAL_MANAGER` (pos. 7)
8. `ROLE_FINANCE` (pos. 8)
9. `ROLE_HR` (pos. 9)

---

### Version20260209210804 — Soft delete en tablas de mantenedores

**Fecha:** 2026-02-09
**Descripción:** `"Add soft delete support: deletedAt and deleted_by_id fields to all maintainer tables"`

Añade a **41 tablas** de mantenedores:
```sql
ALTER TABLE {tabla} ADD deleted_at TIMESTAMP DEFAULT NULL;
ALTER TABLE {tabla} ADD deleted_by_id INT DEFAULT NULL;
```

*(No hay FK en `deleted_by_id`, solo almacena el ID del usuario.)*

---

### Version20260210015000 — Auditoría en tablas de mantenedores

**Fecha:** 2026-02-10
**Descripción:** `"Add audit trail support: createdAt, createdBy, updatedAt, updatedBy to all maintainer tables"`

Añade a las mismas 41 tablas:
```sql
created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
created_by INT DEFAULT NULL
updated_at TIMESTAMP DEFAULT NULL
updated_by INT DEFAULT NULL
```

---

## 5. Migraciones del módulo de Caja (Feb 22–23)

### Version20260222050302 — Campo `code` en AccountStatus

**Fecha:** 2026-02-22
**Descripción:** `"Add code column to account_status for machine-readable status identification"`

```sql
ALTER TABLE account_status ADD code VARCHAR(60) DEFAULT '' NOT NULL;
```

---

### Version20260222050303 — Seed de códigos de AccountStatus

**Fecha:** 2026-02-22
**Descripción:** `"Populate account_status.code with machine-readable slugs for all 12 existing records"`

```sql
UPDATE account_status SET code = 'cerrada_pagada'                    WHERE id = 1;
UPDATE account_status SET code = 'anulada'                           WHERE id = 2;
UPDATE account_status SET code = 'abierta_en_garantia'               WHERE id = 3;
UPDATE account_status SET code = 'cerrada_revision_interna'          WHERE id = 4;
UPDATE account_status SET code = 'cerrada_revision_financiador'      WHERE id = 5;
UPDATE account_status SET code = 'cerrada_pendiente_pago'            WHERE id = 6;
UPDATE account_status SET code = 'cerrada_cobranza_interna'          WHERE id = 7;
UPDATE account_status SET code = 'cerrada_cobranza_judicial'         WHERE id = 8;
UPDATE account_status SET code = 'cerrada_pagada_con_saldo_pendiente' WHERE id = 9;
UPDATE account_status SET code = 'cerrada_pagada_total'              WHERE id = 10;
UPDATE account_status SET code = 'abierta_pendiente_pago'            WHERE id = 11;
UPDATE account_status SET code = 'abierta_pagada_total'              WHERE id = 12;
```

Estos códigos se usan en `PatientAccount::PENDING_ACCOUNT_STATUS_CODES` para determinar si la cuenta está pendiente de pago.

---

### Version20260222052433 — Phase A: tablas core del módulo de Caja ⭐

**Fecha:** 2026-02-22
**Descripción:** `"Phase A: create Revenue/CashRegister core tables — cashier_assignment, cash_register, cash_register_detail, cash_register_check_detail, voucher, voucher_entry, difference, clinical_action_patient, payment_account_detail"`

Esta es la migración central del módulo de Caja. Crea **9 secuencias** y **9 tablas**.

#### cash_register — Sesión de caja de un cajero

```sql
CREATE TABLE cash_register (
    id INT NOT NULL,
    member_id INT NOT NULL,                      -- Cajero FK → member
    cash_register_location_id INT NOT NULL,       -- Ubicación FK → cash_register_location
    branch_id INT DEFAULT NULL,                   -- Sucursal FK → branch
    reopened_by_member_id INT DEFAULT NULL,       -- Quién reabrió
    opened_at TIMESTAMP NOT NULL,
    closed_at TIMESTAMP DEFAULT NULL,
    initial_amount NUMERIC(12,2) DEFAULT '0.00',
    real_amount NUMERIC(12,2) DEFAULT NULL,
    surplus NUMERIC(12,2) DEFAULT NULL,
    deficit NUMERIC(12,2) DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'abierta',
    reopened_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

#### cash_register_detail — Detalle de formas de pago al cerrar

```sql
CREATE TABLE cash_register_detail (
    id INT NOT NULL,
    cash_register_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    bank_id INT DEFAULT NULL,
    amount NUMERIC(12,2) DEFAULT '0.00',
    deposit_number VARCHAR(50) DEFAULT NULL
)
```

#### cash_register_check_detail — Detalle de cheques al cerrar

```sql
CREATE TABLE cash_register_check_detail (
    id INT NOT NULL,
    cash_register_id INT NOT NULL,
    bank_id INT DEFAULT NULL,
    check_number VARCHAR(30) DEFAULT NULL,
    amount NUMERIC(12,2) DEFAULT '0.00',
    check_date DATE DEFAULT NULL
)
```

#### cashier_assignment — Asignación cajero → ubicación

```sql
CREATE TABLE cashier_assignment (
    id INT NOT NULL,
    member_id INT NOT NULL,
    cash_register_location_id INT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

#### voucher — Talonario de folios

```sql
CREATE TABLE voucher (
    id INT NOT NULL,
    cash_register_location_id INT NOT NULL,
    sub_company_id INT DEFAULT NULL,
    folio_from INT NOT NULL,
    folio_to INT NOT NULL,
    current_folio INT NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

#### voucher_entry — Consumo de un folio al cobrar

```sql
CREATE TABLE voucher_entry (
    id INT NOT NULL,
    voucher_id INT NOT NULL,
    payment_account_id INT NOT NULL,
    member_id INT DEFAULT NULL,
    folio_number INT NOT NULL,
    issued_at TIMESTAMP NOT NULL,
    is_cancelled BOOLEAN DEFAULT false
)
```

#### difference — Solicitud de descuento/diferencia

```sql
CREATE TABLE difference (
    id INT NOT NULL,
    requested_by_member_id INT NOT NULL,
    difference_reason_id INT DEFAULT NULL,
    difference_type_id INT DEFAULT NULL,
    difference_direction_id INT DEFAULT NULL,
    patient_account_id INT DEFAULT NULL,
    authorized_by_member_id INT DEFAULT NULL,
    cancelled_by_member_id INT DEFAULT NULL,
    requested_at TIMESTAMP NOT NULL,
    total_account NUMERIC(12,2) DEFAULT '0.00',
    total_discount NUMERIC(12,2) DEFAULT '0.00',
    total_after_discount NUMERIC(12,2) DEFAULT '0.00',
    status VARCHAR(20) DEFAULT 'solicitada',   -- solicitada|autorizada|rechazada|anulada|auto_aprobada
    authorized_at TIMESTAMP DEFAULT NULL,
    cancelled_at TIMESTAMP DEFAULT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

#### payment_account_detail — Detalle de medios de pago de un cobro

```sql
CREATE TABLE payment_account_detail (
    id INT NOT NULL,
    payment_account_id INT NOT NULL,
    payment_method_id INT NOT NULL,
    voucher_entry_id INT DEFAULT NULL,
    amount NUMERIC(12,2) DEFAULT '0.00',
    reference_number VARCHAR(100) DEFAULT NULL,
    card_last_digits VARCHAR(4) DEFAULT NULL,
    installments INT DEFAULT NULL,
    is_cancelled BOOLEAN DEFAULT false,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

#### clinical_action_patient — Prestación cobrada

```sql
CREATE TABLE clinical_action_patient (
    id INT NOT NULL,
    payment_account_id INT NOT NULL,
    billing_item_id INT DEFAULT NULL,
    difference_id INT DEFAULT NULL,      -- diferencia aplicada a esta prestación
    professional_id INT DEFAULT NULL,
    quantity INT DEFAULT 1,
    unit_price NUMERIC(12,2) DEFAULT '0.00',
    total_amount NUMERIC(12,2) DEFAULT '0.00',
    discount_amount NUMERIC(12,2) DEFAULT '0.00',
    is_cancelled BOOLEAN DEFAULT false,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

---

### Version20260223023720 — BonoWeb FONASA + DTE ACES ⭐

**Fecha:** 2026-02-23
**Descripción:** `"BonoWeb FONASA + DTE Aces: create bono_web_voucher, bono_web_voucher_detail, dte_document; add billing_item.tax_affectation_type_id FK; add payment_account.cash_register_id FK"`

#### bono_web_voucher — Voucher de bono electrónico FONASA

```sql
CREATE TABLE bono_web_voucher (
    id INT NOT NULL,
    payment_account_detail_id INT DEFAULT NULL UNIQUE,  -- FK → payment_account_detail
    voucher_id VARCHAR(100) NOT NULL UNIQUE,            -- UUID del voucher Snabb
    voucher_url VARCHAR(500) DEFAULT NULL,
    status VARCHAR(30) DEFAULT 'Created',
    copago_total NUMERIC(10,2) DEFAULT '0.00',
    bonificacion_total NUMERIC(10,2) DEFAULT '0.00',
    created_at TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT NULL
)
```

**Estados de `status`:** Created → Verified → Paid → Done | Canceled

#### bono_web_voucher_detail — Prestaciones del bono

```sql
CREATE TABLE bono_web_voucher_detail (
    id INT NOT NULL,
    bono_web_voucher_id INT NOT NULL,
    billing_item_id INT DEFAULT NULL,
    service_name VARCHAR(200) NOT NULL,
    service_code VARCHAR(20) DEFAULT NULL,   -- código FONASA (ej. P0301)
    copago NUMERIC(10,2) DEFAULT '0.00',
    bonificacion NUMERIC(10,2) DEFAULT '0.00',
    quantity INT DEFAULT 1
)
```

#### dte_document — Documento tributario electrónico ACES

```sql
CREATE TABLE dte_document (
    id INT NOT NULL,
    payment_account_id INT NOT NULL,
    tipodte VARCHAR(5) NOT NULL,         -- '39' (afecto IVA) | '41' (exento)
    folio_number INT NOT NULL,
    status VARCHAR(30) DEFAULT 'sent',   -- sent | delivered | error
    aces_response JSON DEFAULT NULL,
    retry_data JSON DEFAULT NULL,
    sent_at TIMESTAMP DEFAULT NULL,      -- datetime_immutable
    created_at TIMESTAMP NOT NULL        -- datetime_immutable
)
```

#### FKs agregadas a tablas existentes

```sql
-- billing_item: tipo de afectación tributaria (para determinar tipodte 39 vs 41)
ALTER TABLE billing_item ADD tax_affectation_type_id INT DEFAULT NULL;
ALTER TABLE billing_item ADD CONSTRAINT ... FOREIGN KEY (tax_affectation_type_id)
    REFERENCES tax_affectation_type (id);

-- payment_account: caja de la que proviene el cobro
ALTER TABLE payment_account ADD cash_register_id INT DEFAULT NULL;
ALTER TABLE payment_account ADD CONSTRAINT FK_PA_CASH_REGISTER
    FOREIGN KEY (cash_register_id) REFERENCES cash_register (id);
```

---

## 6. Línea de tiempo visual

```
2026-02-01  ████ Version20260201135350  Catálogos de pago, banca, diferencias, gratuidades
2026-02-02  ░░░░ Version20260202222352  (duplicado, artefacto)
2026-02-03  ████ Version20260203115437  Artículos: tipos egreso, inventario
2026-02-03  ████ Version20260203121750  Artículos y bodegas
2026-02-03  ████ Version20260203132251  Cirugía y bloque quirúrgico
2026-02-03  ████ Version20260203151950  Clínica: ClinicalAction, prescripciones, examen físico
2026-02-04  ████ Version20260204112730  Catálogos admisión (CancellationReason, etc.)
2026-02-04  ░░░░ Version20260204113831  exam_report
2026-02-04  ░░░░ Version20260204120247  billing_item
2026-02-04  ████ Version20260204192124  Cuentas bancarias, UF, settlement
2026-02-04  ░░░░ Version20260204192638  Budget footers
2026-02-04  ░░░░ Version20260204192829  Workshop
2026-02-04  ████ Version20260204193532  Diagnósticos, historias clínicas
2026-02-04  ████ Version20260204200708  Exámenes (grupos, servicios, tipos)
2026-02-04  ░░░░ Version20260204203000  Fix secuencia menu_items
2026-02-09  ████ Version20260209161841  Permisos dinámicos de mantenedores
2026-02-09  ████ Version20260209164222  Tabla de roles + seed 9 roles
2026-02-09  ████ Version20260209210804  Soft delete en 41 tablas
2026-02-10  ████ Version20260210015000  Auditoría (createdAt/updatedAt) en 41 tablas
2026-02-10  ████ Version20260210185459  admission_record base
2026-02-11  ████ Version20260211200342  Patient, InsurancePlan, CareType
2026-02-11  ░░░░ Version20260211201748  Renombrado person_id → patient_id
2026-02-18  ████ Version20260218185255  Admisión: estados, cama, urgencias, emergency_complement
2026-02-18  ████ Version20260218203018  PatientAccount, PaymentAccount, AccountStatus
2026-02-20  ████ Version20260220040055  ⚠️ RESET DEV: drop 14 tablas + create 8 nursing tables
2026-02-21  ████ Version20260221030322  Restauración de las 14 tablas eliminadas
2026-02-22  ░░░░ Version20260222050302  account_status: campo code
2026-02-22  ░░░░ Version20260222050303  Seed 12 códigos de account_status
2026-02-22  ████ Version20260222052433  ⭐ CAJA Phase A: 9 tablas core (cash_register, voucher, etc.)
2026-02-23  ████ Version20260223023720  ⭐ BonoWeb FONASA + DTE ACES
```

**Leyenda:** `████` migración significativa · `░░░░` migración menor

---

## 7. Inventario completo de tablas por migración

| Migración | Tablas creadas |
|---|---|
| 20260201135350 | bank, bank_account_type, credit_card, credit_card_type, currency_type, transfer_indicator, billing_payment_method, payment_condition, payment_method, payment_method_type, cash_register_location, difference_direction, difference_reason, difference_type, gratuity_reason, gratuity_type, document_type |
| 20260203115437 | article_outflow_type, inventory_adjustment_reason, product_condition_type |
| 20260203121750 | article, article_supplier, article_type, article_warehouse, dispatch_type, signature_footer, warehouse_specialty |
| 20260203132251 | anesthesia_type, blood_type, surgery_block_reason, surgery_cancellation_reason, surgery_patient_status, surgery_patient_status_config, surgery_suspension_cause, wound_type, surgical_block, surgical_stage, surgical_stage_item, surgical_team_role, treatment_regimen |
| 20260203151950 | clinical_action_category, clinical_action_question, clinical_action_answer, prescription_type, prescription_format, prescription_frequency, prescription_route, prescription_dosage, prescription_dispensation, prescription_rule_detail, physical_exam_base_field, physical_exam_grouping, physical_exam_field, care_category, care_intervention, care_closure_destination, dosage_type, medical_device, nutritional_diagnosis, nutritionist_bmi_index, nutritionist_index_classification, nutritionist_te_index, eating_disorder_history, intoxication_state |
| 20260204112730 | cancellation_reason, company_agreement, emergency_consultation_type |
| 20260204113831 | exam_report |
| 20260204120247 | billing_item |
| 20260204192124 | bank_account, company_user_association, daily_uf, professional_participation, settlement_base |
| 20260204192638 | budget_footer, budget_footer_by_funder, budget_funder_footer |
| 20260204192829 | workshop |
| 20260204193532 | diagnosis, diagnosis_by_pathology, diagnosis_status, immunotherapy_diagnosis, medical_history, medical_history_type |
| 20260204200708 | exam_group, exam_service, exam_service_type, physical_exam_group, physical_exam_type |
| 20260209161841 | maintainer_role_permission |
| 20260209164222 | role |
| 20260210185459 | admission_record |
| 20260211200342 | care_type, insurance_plan, patient |
| 20260218185255 | account_type, admission_status, bed_assignment_status, bed_patient_assignment, triage_category, warehouse_type, emergency_admission_complement, emergency_admission_complement_detail |
| 20260218203018 | account_status, payment_status, patient_account, payment_account |
| 20260220040055 | nursing_charge, nursing_concurrency, nursing_discharge, nursing_order, nursing_prescription, nursing_prescription_item, nursing_return, nursing_transfer *(+ drop 14 tablas)* |
| 20260221030322 | Restaura las 14 tablas de 20260218 (account_type, admission_status, bed_assignment_status, bed_patient_assignment, triage_category, warehouse_type, emergency_admission_complement, emergency_admission_complement_detail, patient, care_type, insurance_plan, patient_account, payment_account, payment_status) |
| 20260222052433 | cash_register, cash_register_detail, cash_register_check_detail, cashier_assignment, voucher, voucher_entry, difference, payment_account_detail, clinical_action_patient |
| 20260223023720 | bono_web_voucher, bono_web_voucher_detail, dte_document *(+ 2 FKs)* |
