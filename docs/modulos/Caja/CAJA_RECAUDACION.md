# Módulo Caja — Submódulo Recaudación

**Rama:** `feature/caja`
**Namespace base:** `App\Controller\Revenue\CashRegister\`, `App\Service\Revenue\`
**Ruta HTTP base:** `/revenue/cash-register`
**Fecha de implementación:** Febrero 2026

---

## Índice

1. [Visión General](#1-visión-general)
2. [Arquitectura y Capas](#2-arquitectura-y-capas)
3. [Entidades](#3-entidades)
4. [Repositorios](#4-repositorios)
5. [Servicios](#5-servicios)
6. [Controladores](#6-controladores)
7. [Handlers de Medios de Pago](#7-handlers-de-medios-de-pago)
8. [Integraciones Externas — BonoWeb (Snabb)](#8-integraciones-externas--bonoweb-snabb)
9. [Integraciones Externas — DTE (Aces/SII)](#9-integraciones-externas--dte-acessii)
10. [Templates y Turbo Frames](#10-templates-y-turbo-frames)
11. [Stimulus Controllers (JavaScript)](#11-stimulus-controllers-javascript)
12. [Flujo de Negocio Completo](#12-flujo-de-negocio-completo)
13. [Migraciones de Base de Datos](#13-migraciones-de-base-de-datos)
14. [Validación y Seguridad](#14-validación-y-seguridad)
15. [Pendientes y Limitaciones Conocidas](#15-pendientes-y-limitaciones-conocidas)

---

## 1. Visión General

El submódulo **Recaudación** es el núcleo operativo del módulo Caja. Gestiona el ciclo completo de un turno de cajero:

```
Apertura de caja
  ↓
Búsqueda de paciente
  ↓
Selección de prestaciones (servicios)
  ↓
Selección de medios de pago (hasta 9 métodos)
  ↓
Confirmación del pago (transacción atómica)
  ↓
Emisión de boleta DTE (Aces/SII) — fuera de transacción
  ↓
Resumen post-pago + impresión
  ↓
Cierre de caja con desglose por forma de pago
```

**Equivalente legacy:** `RecaudacionBundle/_Default/Recaudacion/` (13 controllers en Symfony 3)

**Contextos de entrada:**
| URL de entrada | Contexto | Diferencia |
|---|---|---|
| `/revenue/cash-register` | `direct` | Cobro sin origen específico. Sin BonoWeb habilitado. |
| `/revenue/cash-register/from-admission/{id}` | `from_admission` | Cobro vinculado a admisión hospitalaria. BonoWeb habilitado. |
| `/revenue/cash-register/from-appointment/{id}` | `from_appointment` | Cobro vinculado a cita médica. BonoWeb habilitado. |

---

## 2. Arquitectura y Capas

```
HTTP Request
  ↓
Controller (Revenue/CashRegister/)
  ├─ CashRegisterController        — shell principal, status bar
  ├─ CashOpeningController         — apertura / cierre / reporte
  ├─ PatientSearchController       — búsqueda de paciente
  ├─ CashRegisterServicesController — panel de prestaciones
  ├─ PaymentConfirmController      — confirmación de pago (núcleo transaccional)
  ├─ PostPaymentController         — resumen, historial, PDF, anulación
  └─ DifferenceController          — solicitud y polling de diferencias

Service Layer
  ├─ CashRegisterService           — ciclo de vida de caja
  ├─ VoucherService                — consumo de folios con lock pesimista
  ├─ DifferenceService             — solicitudes de descuento
  ├─ BonoWebService / BonoWebClient — integración Snabb
  ├─ DteService / AcesClient       — emisión de boletas SII
  └─ PaymentBatchProcessor         — validación de medios de pago

Entity Layer (Tenant DB)
  ├─ CashRegister                  — sesión de caja
  ├─ CashRegisterDetail            — detalle de cierre
  ├─ Voucher                       — talonario de folios
  ├─ VoucherEntry                  — consumo de folio
  ├─ ClinicalActionPatient         — prestaciones cobradas
  ├─ PaymentAccountDetail          — detalle por forma de pago
  ├─ Difference                    — solicitudes de descuento
  ├─ BonoWebVoucher                — voucher Snabb
  ├─ BonoWebVoucherDetail          — detalle de prestaciones BonoWeb
  └─ DteDocument                   — DTE emitido
```

---

## 3. Entidades

### 3.1 CashRegister (Caja)

**Archivo:** `src/Entity/Tenant/CashRegister.php`
**Tabla:** `cash_register`
**Equivalente legacy:** `Caja` (tabla `caja_registro`)

Registro de una sesión de caja: desde que el cajero la abre hasta que la cierra. Un cajero solo puede tener una caja abierta a la vez.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK, autoincrement) | no | — | Identificador |
| `member_id` | FK → Member | no | — | Cajero que abrió la caja |
| `cash_register_location_id` | FK → CashRegisterLocation | no | — | Punto de cobro físico |
| `branch_id` | FK → Branch | sí | null | Sucursal (multi-sucursal) |
| `opened_at` | datetime | no | — | Fecha/hora de apertura |
| `closed_at` | datetime | sí | null | Fecha/hora de cierre; null = abierta |
| `initial_amount` | decimal(12,2) | no | 0.00 | Monto inicial declarado por el cajero |
| `real_amount` | decimal(12,2) | sí | null | Monto real contabilizado al cierre |
| `surplus` | decimal(12,2) | sí | null | Diferencia positiva (real > esperado) |
| `deficit` | decimal(12,2) | sí | null | Diferencia negativa (real < esperado) |
| `status` | varchar(20) | no | 'abierta' | 'abierta' \| 'cerrada' \| 'sin_cerrar' |
| `reopened_by_member_id` | FK → Member | sí | null | Supervisor que autorizó reapertura |
| `reopened_at` | datetime | sí | null | Fecha de reapertura autorizada |
| `created_at` | datetime | no | — | Timestamp de creación |
| `updated_at` | datetime | sí | null | Timestamp de última modificación |

**Métodos de negocio:**
- `isOpen(): bool` — retorna `true` si `closedAt === null`
- Getters/setters fluent para todos los campos

**Relaciones:**
- `CashRegisterDetail[]` — detalles de cierre por forma de pago
- Referenciado por `PaymentAccount.cashRegister` (FK agrega campo `nrocaja` en DTE)

---

### 3.2 CashRegisterDetail (DetalleCaja)

**Archivo:** `src/Entity/Tenant/CashRegisterDetail.php`
**Tabla:** `cash_register_detail`
**Equivalente legacy:** `DetalleCaja`

Una línea por forma de pago en el desglose del cierre de caja.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `cash_register_id` | FK → CashRegister | no | — | Caja a la que pertenece |
| `payment_method_id` | FK → PaymentMethod | no | — | Forma de pago |
| `bank_id` | FK → Bank | sí | null | Banco (cheques, transferencias) |
| `amount` | decimal(12,2) | no | 0.00 | Monto real contabilizado |
| `deposit_number` | varchar(50) | sí | null | Número de depósito bancario |

**Lógica de negocio:**
- Creado en `CashRegisterService::closeRegister()` a partir del formulario de cierre.
- La suma de todos los `amount` = `CashRegister.realAmount`.

---

### 3.3 CashRegisterCheckDetail (DetalleCajaCheque)

**Archivo:** `src/Entity/Tenant/CashRegisterCheckDetail.php`
**Tabla:** `cash_register_check_detail`

Detalle individual de cheques recibidos durante la jornada.

**Campos:**

| Campo | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | int (PK) | no | Identificador |
| `cash_register_id` | FK → CashRegister | no | Caja |
| `bank_id` | FK → Bank | sí | Banco emisor del cheque |
| `check_number` | varchar(30) | sí | Número del cheque |
| `amount` | decimal(12,2) | no | Monto del cheque |
| `check_date` | date | sí | Fecha del cheque |

---

### 3.4 Voucher (Talonario)

**Archivo:** `src/Entity/Tenant/Voucher.php`
**Tabla:** `voucher`
**Equivalente legacy:** `Talonario`

Stock de folios/boletas asignado a una ubicación de caja. Define el rango numérico de folios disponibles.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `cash_register_location_id` | FK → CashRegisterLocation | no | — | Caja propietaria |
| `sub_company_id` | FK → SubCompany | sí | null | Sub-empresa emisora (multi-empresa) |
| `folio_from` | int | no | — | Primer folio del rango |
| `folio_to` | int | no | — | Último folio del rango |
| `current_folio` | int | no | — | Próximo folio a usar |
| `is_active` | boolean | no | true | Si tiene folios disponibles |
| `created_at` | datetime | no | — | Creación |
| `updated_at` | datetime | sí | null | Modificación |

**Métodos de negocio:**
- `hasAvailableFolios(): bool` — retorna `isActive && currentFolio <= folioTo`

**Lógica de negocio:**
- Al crear: `currentFolio = folioFrom`.
- Cada cobro incrementa `currentFolio` en 1.
- Cuando `currentFolio > folioTo`, el talonario se desactiva automáticamente en `VoucherService`.
- **No se puede editar** un talonario con folios ya consumidos (bloqueado en `VoucherController`).
- Gestionado por el Mantenedor Treasury (`/maintainers/treasury/voucher/`).

---

### 3.5 VoucherEntry (DetalleTalonario)

**Archivo:** `src/Entity/Tenant/VoucherEntry.php`
**Tabla:** `voucher_entry`
**Equivalente legacy:** `DetalleTalonario`

Registro del consumo de un folio al procesar un pago. Vincula el folio con el pago específico.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `voucher_id` | FK → Voucher | no | — | Talonario de origen |
| `payment_account_id` | FK → PaymentAccount | no | — | Pago al que se emitió |
| `member_id` | FK → Member | sí | null | Cajero que emitió |
| `folio_number` | int | no | — | Número de folio asignado |
| `issued_at` | datetime | no | — | Fecha/hora de emisión |
| `is_cancelled` | boolean | no | false | Si fue anulado |

**Lógica de negocio:**
- Creado en `VoucherService::consumeNextFolio()` con **lock pesimista PESSIMISTIC_WRITE**.
- Al anular un pago (`PostPaymentController::void()`), se marca `is_cancelled = true`.
- El supervisor puede anular folios individualmente desde `VoucherManagementController`.
- Un folio anulado queda registrado (no se elimina), garantizando auditoría completa.

---

### 3.6 ClinicalActionPatient (AccionClinicaPaciente)

**Archivo:** `src/Entity/Tenant/ClinicalActionPatient.php`
**Tabla:** `clinical_action_patient`
**Equivalente legacy:** `AccionClinicaPaciente`

Una prestación clínica cobrada a un paciente dentro de un pago específico. Un `PaymentAccount` puede tener N prestaciones.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `payment_account_id` | FK → PaymentAccount | no | — | Pago al que pertenece |
| `billing_item_id` | FK → BillingItem | sí | null | Prestación/servicio |
| `difference_id` | FK → Difference | sí | null | Descuento aplicado |
| `professional_id` | FK → Professional | sí | null | Profesional que la realizó |
| `quantity` | int | no | 1 | Cantidad de unidades |
| `unit_price` | decimal(12,2) | no | 0.00 | Precio unitario |
| `total_amount` | decimal(12,2) | no | 0.00 | Monto total (quantity × unitPrice) |
| `discount_amount` | decimal(12,2) | no | 0.00 | Monto de descuento aplicado |
| `is_cancelled` | boolean | no | false | Si fue anulada |
| `notes` | text | sí | null | Observaciones adicionales |
| `created_at` | datetime | no | — | Creación |
| `updated_at` | datetime | sí | null | Modificación |

**Lógica de negocio:**
- Creada en `PaymentConfirmController::confirm()` dentro de la transacción principal.
- Cada fila del `services_payload` JSON genera una `ClinicalActionPatient`.
- Si `is_cancelled = true`, se omite en cálculos de DTE.
- `DteService` itera estas entidades para clasificar prestaciones en afectas/exentas.
- `BillingItem.taxAffectationType.name` contiene "afect" (case-insensitive) → afecta (tipodte=39).
- Sin `taxAffectationType` o nombre sin "afect" → exenta (tipodte=41).

---

### 3.7 PaymentAccountDetail (DetallePagoCuenta)

**Archivo:** `src/Entity/Tenant/PaymentAccountDetail.php`
**Tabla:** `payment_account_detail`
**Equivalente legacy:** `DetallePagoCuenta`

Una línea por forma de pago usada en un cobro. Un pago puede usar múltiples formas de pago simultáneamente (ej: mitad efectivo, mitad tarjeta).

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `payment_account_id` | FK → PaymentAccount | no | — | Pago al que pertenece |
| `payment_method_id` | FK → PaymentMethod | no | — | Forma de pago |
| `voucher_entry_id` | FK → VoucherEntry | sí | null | Folio emitido (si aplica) |
| `amount` | decimal(12,2) | no | 0.00 | Monto pagado con esta forma |
| `reference_number` | varchar(100) | sí | null | N° de referencia/autorización/cheque |
| `card_last_digits` | varchar(4) | sí | null | Últimos 4 dígitos de tarjeta |
| `installments` | int | sí | null | Cuotas (solo tarjeta de crédito) |
| `is_cancelled` | boolean | no | false | Si fue anulado |
| `created_at` | datetime | no | — | Creación |
| `updated_at` | datetime | sí | null | Modificación |

**Lógica de negocio:**
- Suma de `amount` en todos los detalles = monto total del `PaymentAccount`.
- `reference_number` se extrae mediante `PaymentConfirmController::extractReference()` según el código de método:
  - `bank_transfer` → `transfer_number`
  - `electronic_voucher` / `manual_voucher` → `folio`
  - `debit_card` → `voucher`
  - `check` → `check_number`
  - `credit_card` → `authorization_code`
- Al anular pago: `is_cancelled = true` en todos los detalles.

---

### 3.8 Difference (Diferencia/Descuento)

**Archivo:** `src/Entity/Tenant/Difference.php`
**Tabla:** `difference`
**Equivalente legacy:** `Diferencia`

Solicitud de descuento o ajuste de monto iniciada por un cajero durante el cobro. Si el tipo lo permite, se auto-aprueba; de lo contrario, espera autorización de supervisor.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `requested_by_member_id` | FK → Member | no | — | Cajero que solicita |
| `difference_reason_id` | FK → DifferenceReason | sí | null | Motivo del descuento |
| `difference_type_id` | FK → DifferenceType | sí | null | Tipo de descuento |
| `difference_direction_id` | FK → DifferenceDirection | sí | null | Dirección (cargo / abono) |
| `patient_account_id` | FK → PatientAccount | sí | null | Cuenta del paciente afectada |
| `authorized_by_member_id` | FK → Member | sí | null | Supervisor que resuelve |
| `cancelled_by_member_id` | FK → Member | sí | null | Quién anuló |
| `requested_at` | datetime | no | — | Fecha de solicitud |
| `total_account` | decimal(12,2) | no | 0.00 | Monto total antes del ajuste |
| `total_discount` | decimal(12,2) | no | 0.00 | Monto del descuento solicitado |
| `total_after_discount` | decimal(12,2) | no | 0.00 | Monto resultante |
| `status` | varchar(20) | no | 'solicitada' | Ver ciclo de estados |
| `authorized_at` | datetime | sí | null | Fecha de resolución |
| `cancelled_at` | datetime | sí | null | Fecha de anulación |
| `created_at` | datetime | no | — | Creación |
| `updated_at` | datetime | sí | null | Modificación |

**Ciclo de estados:**
```
solicitada
  ├─→ auto_aprobada  (si DifferenceType.idEstado === 2)
  ├─→ autorizada     (supervisor aprueba)
  ├─→ rechazada      (supervisor rechaza)
  └─→ anulada        (cajero cancela)
```

**Métodos de negocio:**
- `isPending(): bool` — `status === 'solicitada'`
- `isApproved(): bool` — `status in ['autorizada', 'auto_aprobada']`

---

### 3.9 BonoWebVoucher

**Archivo:** `src/Entity/Tenant/BonoWebVoucher.php`
**Tabla:** `bono_web_voucher`

Voucher de bono FONASA electrónico generado en el sistema Snabb. Nuevo en Tenant (sin equivalente legacy directo).

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `payment_account_detail_id` | OneToOne → PaymentAccountDetail | sí | null | Se vincula al confirmar pago |
| `voucher_id` | varchar(100) UNIQUE | no | — | UUID del voucher en Snabb |
| `voucher_url` | varchar(500) | sí | null | URL de pago para el paciente |
| `status` | varchar(30) | no | 'Created' | Ver ciclo de estados Snabb |
| `copago_total` | decimal(10,2) | no | 0.00 | Total copago del paciente (CLP) |
| `bonificacion_total` | decimal(10,2) | no | 0.00 | Total bonificado por FONASA (CLP) |
| `details` | OneToMany → BonoWebVoucherDetail | — | — | Prestaciones enviadas |
| `created_at` | datetime_immutable | no | — | Creación |
| `updated_at` | datetime | sí | null | Última sincronización |

**Ciclo de estados Snabb:**
```
Created → Verified → Waiting for Payment → Paid → Done
                                           ↓
                                         Canceled
```

**Métodos de negocio:**
- `isPaid(): bool` — `status === 'Paid'`
- `isDone(): bool` — `status === 'Done'`
- `isCanceled(): bool` — `status === 'Canceled'`
- `isPending(): bool` — status no es 'Done' ni 'Canceled'

---

### 3.10 BonoWebVoucherDetail

**Archivo:** `src/Entity/Tenant/BonoWebVoucherDetail.php`
**Tabla:** `bono_web_voucher_detail`

Una línea de prestación dentro de un voucher BonoWeb.

**Campos:**

| Campo | Tipo | Nullable | Descripción |
|---|---|---|---|
| `id` | int (PK) | no | Identificador |
| `bono_web_voucher_id` | FK → BonoWebVoucher | no | Voucher padre |
| `billing_item_id` | FK → BillingItem | sí | Ítem local (si aplica) |
| `service_name` | varchar(200) | no | Nombre de la prestación |
| `service_code` | varchar(20) | sí | Código FONASA (ej: P0301) |
| `copago` | decimal(10,2) | no | Copago del paciente por esta prestación |
| `bonificacion` | decimal(10,2) | no | Bonificación FONASA |
| `quantity` | int | no | Cantidad (default 1) |

---

### 3.11 DteDocument

**Archivo:** `src/Entity/Tenant/DteDocument.php`
**Tabla:** `dte_document`

Registro de un documento tributario electrónico (DTE) emitido vía Aces al SII de Chile. Nuevo en Tenant.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `payment_account_id` | FK → PaymentAccount | no | — | Pago al que corresponde |
| `tipodte` | varchar(5) | no | — | '39' (Afecta) \| '41' (Exenta) |
| `folio_number` | int | no | — | Folio del talonario usado |
| `status` | varchar(30) | no | 'sent' | 'sent' \| 'error' \| 'retry_pending' \| 'cancelled' |
| `aces_response` | json | sí | null | Respuesta completa de Aces |
| `retry_data` | json | sí | null | Payload de negocio para reintento (sin credenciales) |
| `sent_at` | datetime_immutable | sí | null | Timestamp de envío exitoso |
| `created_at` | datetime_immutable | no | — | Creación |

**Ciclo de estados:**
```
[emisión]
  ├─→ sent          (éxito)
  └─→ error         (fallo Aces)
        └─→ retry_pending  (primer reintento fallido)
              └─→ sent     (reintento exitoso)
```

**Métodos de negocio:**
- `isRetryable(): bool` — `status in ['error', 'retry_pending']`

**Decisión de diseño crítica:**
> `DteDocument` se persiste **después del commit de la transacción principal**. Si falla la emisión DTE, el pago **no se revierte**. El error queda registrado en `status='error'` con `retryData` para reintento manual desde `DteController::retry()`.

---

## 4. Repositorios

### 4.1 CashRegisterRepository

**Archivo:** `src/Repository/Tenant/CashRegisterRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findOpenByMember` | `(Member): ?CashRegister` | Caja con status='abierta' del cajero. Join eager a `cashRegisterLocation`. OrderBy openedAt DESC, limit 1. |
| `findWithDetailsById` | `(int): ?CashRegister` | Por ID con joins a `cashRegisterLocation`, `member`, `branch`. Evita N+1 en reportes. |

---

### 4.2 VoucherRepository

**Archivo:** `src/Repository/Tenant/VoucherRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findActiveByLocation` | `(CashRegisterLocation): ?Voucher` | Talonario activo con folios disponibles (`currentFolio <= folioTo`). Usado en validaciones de estado. |

---

### 4.3 VoucherEntryRepository

**Archivo:** `src/Repository/Tenant/VoucherEntryRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findOneByPaymentAccount` | `(PaymentAccount): ?VoucherEntry` | Folio activo (`isCancelled=false`) vinculado al pago. Con join a `voucher`. |

---

### 4.4 ClinicalActionPatientRepository

**Archivo:** `src/Repository/Tenant/ClinicalActionPatientRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findByPaymentAccount` | `(PaymentAccount): ClinicalActionPatient[]` | Todas las prestaciones de un pago. Usada por `DteService` y resumen post-pago. |

---

### 4.5 PaymentAccountDetailRepository

**Archivo:** `src/Repository/Tenant/PaymentAccountDetailRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findByPaymentAccount` | `(PaymentAccount): PaymentAccountDetail[]` | Todos los detalles de pago de un cobro. |

---

### 4.6 DifferenceRepository

**Archivo:** `src/Repository/Tenant/DifferenceRepository.php`

| Método | Firma | Descripción |
|---|---|---|
| `findWithDetailsById` | `(int): ?Difference` | Por ID con joins a `requestedByMember`, `differenceType`, `differenceReason`, `differenceDirection`, `patientAccount`. |

---

## 5. Servicios

### 5.1 CashRegisterService

**Archivo:** `src/Service/Revenue/CashRegister/CashRegisterService.php`

Encapsula las operaciones del ciclo de vida de una caja de cobro.

**Constructor:**
```php
public function __construct(
    private readonly TenantEntityManager $em,
    private readonly CashRegisterRepository $cashRegisterRepository,
    private readonly VoucherRepository $voucherRepository,
)
```

**Métodos públicos:**

#### `validateOperatingStatus(Member $member): string`
Evalúa el estado operativo de la caja para el cajero dado. Retorna uno de:

| Valor | Condición | Significado |
|---|---|---|
| `'open'` | Hay caja abierta hoy + talonario con folios | Puede procesar cobros |
| `'no_voucher'` | Hay caja abierta hoy pero sin talonario activo | Error operativo: sin talonario |
| `'unclosed'` | Hay caja de días anteriores sin cerrar | Error operativo: debe cerrar la anterior |
| `'closed'` | No hay caja abierta | Debe abrir una nueva |

Árbol de decisión:
```
¿Existe CashRegister abierta para el cajero?
├─ NO → 'closed'
├─ SÍ:
    ¿La apertura es de hoy (misma fecha)?
    ├─ NO → 'unclosed'
    ├─ SÍ:
        ¿Existe Voucher activo con currentFolio <= folioTo?
        ├─ NO → 'no_voucher'
        └─ SÍ → 'open'
```

#### `openForUser(Member $member, CashRegisterLocation $location): CashRegister`
Abre una nueva caja. El llamador debe haber verificado previamente que el estado es `'closed'`.
- Crea `CashRegister` con `status='abierta'`, `openedAt=now()`.
- Persiste y flush.
- Retorna la nueva caja.

#### `closeRegister(CashRegister $cashRegister, array $detailData): void`
Cierra la caja procesando el desglose por forma de pago.
- `$detailData`: array de `[payment_method_id, amount, bank_id?, deposit_number?]`.
- Para cada fila crea `CashRegisterDetail`.
- Calcula `realAmount` (suma de amounts), `expectedTotal` (suma de ingresos del sistema — actualmente stub: 0.00), `surplus`, `deficit`.
- Marca `status='cerrada'`, `closedAt=now()`.
- Flush.

**Limitación conocida:** `calculateExpectedTotal()` retorna `'0.00'` actualmente. Pendiente calcular el total esperado sumando los `PaymentAccount` de la jornada.

---

### 5.2 VoucherService

**Archivo:** `src/Service/Revenue/CashRegister/VoucherService.php`

Gestiona el ciclo de vida de talonarios (folios/boletas). Crítico para la coherencia de numeración.

**Constructor:**
```php
public function __construct(
    private readonly TenantEntityManager $em,
    private readonly VoucherRepository $voucherRepository,
)
```

**Métodos públicos:**

#### `hasAvailableVoucher(CashRegisterLocation $location): bool`
Consulta sin lock si existe talonario con folios disponibles. Usar solo para validaciones previas (en `validateOperatingStatus`), **nunca** dentro del flujo transaccional.

#### `consumeNextFolio(CashRegisterLocation $location, PaymentAccount $paymentAccount, Member $member): VoucherEntry`
**DEBE llamarse dentro de una transacción Doctrine.**

Algoritmo con lock pesimista:
1. Obtiene el `Voucher` activo con `PESSIMISTIC_WRITE` (bloquea la fila).
2. Lee `currentFolio` actual → lo asigna al nuevo `VoucherEntry`.
3. Incrementa `Voucher.currentFolio += 1`.
4. Si `currentFolio > folioTo` → `Voucher.isActive = false` (talonario agotado).
5. Persiste `VoucherEntry`.

Lanza `\RuntimeException` si no hay talonario disponible (segunda validación dentro de transacción).

**Por qué PESSIMISTIC_WRITE:** En entornos con múltiples cajeros operando simultáneamente, sin lock dos transacciones concurrentes leerían el mismo `currentFolio`, generando folios duplicados. El lock garantiza unicidad absoluta.

---

### 5.3 DifferenceService

**Archivo:** `src/Service/Revenue/CashRegister/DifferenceService.php`

Gestiona el ciclo de solicitud, aprobación, rechazo y anulación de descuentos.

**Constructor:**
```php
public function __construct(
    private readonly TenantEntityManager $em,
    private readonly DifferenceTypeRepository $differenceTypeRepository,
    private readonly DifferenceReasonRepository $differenceReasonRepository,
    private readonly DifferenceDirectionRepository $differenceDirectionRepository,
)
```

**Métodos públicos:**

#### `requestDiscount(Member $requestedBy, array $data, ?PatientAccount $patientAccount = null): Difference`
Crea solicitud con `status='solicitada'`.
- `$data` esperado: `difference_type_id`, `difference_reason_id`, `difference_direction_id`, `total_account`, `total_discount`, `total_after_discount`.
- Resuelve FKs desde IDs.
- Persiste y flush.

#### `approve(Difference $difference, Member $authorizedBy): void`
- `status='autorizada'`, registra supervisor y `authorizedAt=now()`. Flush.

#### `reject(Difference $difference, Member $authorizedBy): void`
- `status='rechazada'`, registra supervisor y `authorizedAt=now()`. Flush.

#### `cancel(Difference $difference): void`
- Solo si `status='solicitada'`. `status='anulada'`, `cancelledByMember=requestedByMember`, `cancelledAt=now()`. Flush.

#### `autoApproveIfEligible(Difference $difference): bool`
- Si `DifferenceType.idEstado === 2` → `status='auto_aprobada'`, `authorizedAt=now()`. Flush. Retorna `true`.
- Caso contrario retorna `false`.
- **Nota:** La condición usa el campo legacy `idEstado`. Pendiente agregar campo dedicado `allows_auto_approve` en próxima migración.

---

## 6. Controladores

### 6.1 CashRegisterController (Shell Principal)

**Archivo:** `src/Controller/Revenue/CashRegister/CashRegisterController.php`
**Ruta base:** `/revenue/cash-register` | `app_revenue_cash_register_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register` | GET | `index()` | `app_revenue_cash_register_index` |
| `/revenue/cash-register/from-admission/{id}` | GET | `fromAdmission(int)` | `app_revenue_cash_register_from_admission` |
| `/revenue/cash-register/from-appointment/{id}` | GET | `fromAppointment(int)` | `app_revenue_cash_register_from_appointment` |
| `/revenue/cash-register/status-bar` | GET | `statusBar()` | `app_revenue_cash_register_status_bar` |

**Responsabilidades:**
- `index()`, `fromAdmission()`, `fromAppointment()`: llaman a `renderShell()` con el contexto correspondiente.
- `renderShell()`: renderiza `revenue/cash-register/index.html.twig` inyectando todas las URLs de Turbo Frames y la colección inicial de filas de métodos de pago.
- `statusBar()`: Turbo Frame `cash-register-status-bar`. Consulta `validateOperatingStatus()` y pinta badge de estado (abierta, sin talonario, sin cerrar, cerrada) con botones de acción.
- `createInitialPaymentRows()`: itera `PaymentMethodConfigRegistry::all()` y crea una `FormView` por cada método. Estas filas se inyectan en el template para que el JavaScript las cargue cuando el cajero las seleccione.

---

### 6.2 CashOpeningController (Apertura / Cierre)

**Archivo:** `src/Controller/Revenue/CashRegister/CashOpeningController.php`
**Ruta base:** `/revenue/cash-register` | `app_revenue_cash_register_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register/open` | GET | `open()` | `app_revenue_cash_register_open` |
| `/revenue/cash-register/open` | POST | `openSubmit()` | `app_revenue_cash_register_open_submit` |
| `/revenue/cash-register/close/{id}` | GET | `close(int)` | `app_revenue_cash_register_close` |
| `/revenue/cash-register/close/{id}` | POST | `closeSubmit(int)` | `app_revenue_cash_register_close_submit` |
| `/revenue/cash-register/report/{id}` | GET | `report(int)` | `app_revenue_cash_register_report` |

**Descripción de cada action:**

#### `open(): Response`
Renderiza el panel de apertura de caja (Turbo Frame `cash-register-open`). Comportamiento según estado:

| Estado | Renderizado |
|---|---|
| `'closed'` | Selector de ubicación de caja disponible. Botón Abrir. |
| `'open'` | Aviso: "Ya tienes una caja abierta hoy." |
| `'unclosed'` | Error: "Tienes una caja anterior sin cerrar." + enlace al cierre. |
| `'no_voucher'` | Error: "La caja no tiene talonario con folios disponibles." |

#### `openSubmit(Request $request): Response`
Procesa la apertura:
1. Valida CSRF (`cash_open`).
2. Verifica estado = `'closed'`.
3. Obtiene `CashRegisterLocation` por `cash_register_location_id`.
4. Llama `CashRegisterService::openForUser()`.
5. Guarda `cash_register_id` en sesión.
6. Redirige a GET `/open` (el Turbo Frame se refresca con estado `'open'`).

#### `close(int $id): Response`
Renderiza formulario de cierre (Turbo Frame `cash-register-close`):
- Verifica que la caja pertenezca al cajero (o que el usuario tenga `ROLE_ADMIN`).
- Carga todas las `PaymentMethod` activas para mostrar una fila de entrada por cada una.

#### `closeSubmit(int $id, Request $request): Response`
Procesa el cierre:
1. Valida CSRF (`cash_close_{id}`).
2. Construye `detailData` desde `amounts[{payment_method_id}]` → monto ingresado.
3. Llama `CashRegisterService::closeRegister()`.
4. Limpia `cash_register_id` de sesión.
5. Redirige a `report/{id}`.

#### `report(int $id): Response`
Resumen HTML del cierre. Carga `CashRegisterDetail[]`, muestra surplus/deficit, totales. Sin PDF por ahora (pendiente).

---

### 6.3 PatientSearchController (Búsqueda de Paciente)

**Archivo:** `src/Controller/Revenue/CashRegister/PatientSearchController.php`
**Ruta base:** `/revenue/cash-register/patient` | `app_revenue_cash_register_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register/patient/search` | GET | `search()` | `app_revenue_cash_register_patient_search` |
| `/revenue/cash-register/patient/find` | GET | `find()` | `app_revenue_cash_register_patient_find` |
| `/revenue/cash-register/patient/{id}/context` | GET | `context(int)` | `app_revenue_cash_register_patient_context` |

#### `search(): Response`
Renderiza el panel de búsqueda vacío (Turbo Frame `patient-search`). Solo el contenedor y el input.

#### `find(Request $request): JsonResponse`
Autocomplete en tiempo real. Query param: `q` (mínimo 2 caracteres).
- Llama `PatientRepository::searchByQuery($q, 10)`.
- Respuesta JSON por paciente:
  ```json
  {
    "id": 123,
    "fullName": "Juan Pérez",
    "rut": "12345678-9",
    "birthDate": "1990-01-15",
    "patientAccountId": 456,
    "contextUrl": "/revenue/cash-register/patient/123/context"
  }
  ```

#### `context(int $id): Response`
Carga el contexto demográfico del paciente (Turbo Frame `patient-context`).
- Renderiza nombre, RUT, edad, financiador.
- Incluye campos hidden `#ctx-patient-id` y `#ctx-patient-account-id` que el orquestador Stimulus usa al confirmar el pago.

---

### 6.4 CashRegisterServicesController (Panel de Prestaciones)

**Archivo:** `src/Controller/Revenue/CashRegister/CashRegisterServicesController.php`
**Ruta base:** `/revenue/cash-register/services` | `app_revenue_cash_register_services_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register/services` | GET | `panel()` | `app_revenue_cash_register_services_panel` |
| `/revenue/cash-register/services/search` | GET | `search()` | `app_revenue_cash_register_services_search` |
| `/revenue/cash-register/services/add` | POST | `add()` | `app_revenue_cash_register_services_add` |
| `/revenue/cash-register/services/remove` | DELETE | `remove()` | `app_revenue_cash_register_services_remove` |

**Estado persistido en sesión:** clave `'cash_register_services'` → `array<int, ServiceRow>`.

#### `panel(): Response`
Renderiza Turbo Frame `services-panel` con la tabla de prestaciones seleccionadas y el total calculado.

#### `search(Request $request): JsonResponse`
Autocomplete de prestaciones. `?q=` → busca en `BillingItem` con `idem` activos. Límite 15 resultados.
```json
[{"id": 1, "name": "Consulta médica", "price": 25000}]
```

#### `add(Request $request): Response`
Turbo Stream `append` a la tabla de servicios:
- Agrega nueva fila a sesión con `billingItemId`, `name`, `quantity`, `unitAmount`, `discount`, `totalAmount`.
- Recalcula `services-payload-hidden` y total.
- Retorna `Turbo Stream` con acción `append` sobre `services-table` y `replace` sobre total.

#### `remove(Request $request): Response`
Turbo Stream `remove` de la tabla:
- Elimina la fila por `rowKey` de sesión.
- Retorna `Turbo Stream` con acción `remove`.

**Lógica de cálculo:**
- Todos los montos calculados con `bcmath` (2 decimales) para evitar errores de punto flotante.
- `totalAmount = max(0, quantity × unitAmount − discount)`.

---

### 6.5 PaymentConfirmController (Núcleo Transaccional)

**Archivo:** `src/Controller/Revenue/CashRegister/PaymentConfirmController.php`
**Ruta:** `POST /revenue/cash-register/payment/confirm` | `app_revenue_cash_register_payment_confirm`

**Esta es la action más crítica del sistema.** Ejecuta el cobro completo en una única transacción Doctrine.

**POST body (form-encoded):**

| Campo | Tipo | Descripción |
|---|---|---|
| `_csrf_token` | string | Token CSRF `payment_confirm` |
| `patient_id` | int | ID del paciente |
| `admission_record_id` | int \| null | Si viene de admisión |
| `appointment_id` | int \| null | Si viene de cita |
| `services_payload` | JSON string | Array de prestaciones seleccionadas |
| `payment_batch[rows][{method}][{n}][...]` | array | Datos de cada fila de pago |

**Estructura de `services_payload`:**
```json
[
  {
    "billingItemId": 1,
    "name": "Consulta médica",
    "quantity": 1,
    "unitAmount": 25000,
    "discount": 0,
    "totalAmount": 25000
  }
]
```

**Flujo interno de `confirm()`:**

```
1. Validar CSRF → renderErrorStream si falla
2. Parsear patient_id, services_payload, payment_batch
3. Validar paciente > 0 → renderErrorStream si falla
4. json_decode(services_payload) → renderErrorStream si no es array
5. PaymentBatchProcessor::process(payment_batch) → renderErrorStream si !isValid()
6. PatientRepository::find(patient_id) → renderErrorStream si null
7. resolveCurrentMember() → AccessDeniedException si no es Member
8. resolveCurrentCashRegister() → renderErrorStream si null

== TRANSACCIÓN ==
9.  Crear/recuperar PatientAccount
10. Crear PaymentAccount (con patient, member, cashRegister, cashRegisterLocation, date, amount)
11. Para cada fila en services_payload:
    - BillingItemRepository::find(billingItemId)
    - Crear ClinicalActionPatient
12. Para cada PaymentRowDTO en batch.getRowsFlat():
    - PaymentMethodRepository::findByCode(methodCode)
    - Crear PaymentAccountDetail
13. VoucherService::consumeNextFolio() [PESSIMISTIC_WRITE]
14. em->flush()
15. em->commit()
== FIN TRANSACCIÓN ==

16. DteService::emitirBoletas($paymentAccount) [fuera de transacción]
    └─ Si falla: log error, NO revierte el pago
17. session->remove('cash_register_services')
18. Generar URL del resumen → Turbo Stream reemplaza payment-panel
```

**Respuestas Turbo Stream:**
- **Éxito:** `replace` de `#payment-panel` → `<turbo-frame src="{summaryUrl}">` (HTTP 200)
- **Error:** `replace` de `#payment-panel` → lista de errores (HTTP 422)

---

### 6.6 PostPaymentController (Post-Pago)

**Archivo:** `src/Controller/Revenue/CashRegister/PostPaymentController.php`
**Ruta base:** `/revenue/cash-register/post-payment` | `app_revenue_cash_register_post_payment_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register/post-payment/{id}` | GET | `summary(int)` | `app_revenue_cash_register_post_payment_summary` |
| `/revenue/cash-register/post-payment/{id}/history` | GET | `history(int)` | `app_revenue_cash_register_post_payment_history` |
| `/revenue/cash-register/post-payment/{id}/print` | GET | `print(int)` | `app_revenue_cash_register_post_payment_print` |
| `/revenue/cash-register/post-payment/{id}/void` | POST | `void(int)` | `app_revenue_cash_register_post_payment_void` |

#### `summary(int $id): Response`
Resumen del pago (Turbo Frame `payment-panel`). Muestra:
- Datos del paciente (nombre, RUT).
- Prestaciones cobradas (nombre, cantidad, precio, descuento, total).
- Medios de pago usados (monto, referencia, dígitos de tarjeta).
- Folio de boleta asignado.
- Botones: Imprimir, Historial, Anular.

#### `history(int $id): Response`
Turbo Frame `post-payment-history`. Los últimos 20 `PaymentAccount` del paciente, ordenados por fecha descendente.

#### `print(int $id): Response`
Genera y retorna PDF inline (`Content-Type: application/pdf`, `Content-Disposition: inline`).
- El enlace en el template **DEBE** usar `data-turbo="false"` + `target="_blank"` para que abra en nueva pestaña sin interceptación de Turbo.
- Llama `PdfService::generatePaymentPdf($paymentAccount)`.

#### `void(int $id, Request $request): Response`
Anula el pago:
1. Valida CSRF (`payment_void_{id}`).
2. Carga `PaymentAccount`.
3. Marca `cancellationDate=now()`, `cancelledByMember=member`, `cancellationReason`.
4. Busca `PaymentStatus` con nombre 'Anulado' y lo asigna.
5. Marca `VoucherEntry.isCancelled=true`.
6. Marca `PaymentAccountDetail.isCancelled=true` en todos los detalles.
7. Flush.
8. Redirect a `summary/{id}`.

---

### 6.7 DifferenceController (Solicitud de Descuentos)

**Archivo:** `src/Controller/Revenue/CashRegister/DifferenceController.php`
**Ruta base:** `/revenue/cash-register/difference` | `app_revenue_cash_register_difference_`

| Ruta | Método HTTP | Action | Nombre |
|---|---|---|---|
| `/revenue/cash-register/difference/form` | GET | `form()` | `app_revenue_cash_register_difference_form` |
| `/revenue/cash-register/difference/request` | POST | `request()` | `app_revenue_cash_register_difference_request` |
| `/revenue/cash-register/difference/{id}/status` | GET | `status(int)` | `app_revenue_cash_register_difference_status` |
| `/revenue/cash-register/difference/{id}/cancel` | POST | `cancel(int)` | `app_revenue_cash_register_difference_cancel` |

#### `form(Request $request): Response`
Renderiza formulario de solicitud (Turbo Frame `difference-panel`).
- Query params opcionales: `total_account`, `patient_account_id` (para pre-rellenar).
- Carga catálogos: `DifferenceType[]`, `DifferenceReason[]`, `DifferenceDirection[]`.

#### `request(Request $request): Response`
Procesa la solicitud:
1. Valida CSRF.
2. Resuelve `PatientAccount` si viene `patient_account_id`.
3. Llama `DifferenceService::requestDiscount()`.
4. Intenta `DifferenceService::autoApproveIfEligible()`.
5. Si queda `'solicitada'` → retorna frame con componente Stimulus de polling (cada 3 s).
6. Si queda aprobada → retorna frame con confirmación de descuento aplicado.

#### `status(int $id): Response`
Retorna Turbo Frame `difference-status` con el estado actual. `Cache-Control: no-cache`.
- Consultado periódicamente por `difference-controller.js` (Stimulus).
- Cuando el estado es final (no `'solicitada'`), el Stimulus controller detiene el polling y emite evento `difference:resolved` hacia el orquestador.

#### `cancel(int $id, Request $request): Response`
Anula solicitud pendiente y re-renderiza frame con estado actualizado.

---

## 7. Handlers de Medios de Pago

**Interfaz:** `App\Service\Revenue\Payment\Handler\PaymentMethodHandlerInterface`

```php
interface PaymentMethodHandlerInterface {
    public function supports(string $methodCode): bool;
    public function normalizeAndValidate(int $rowIndex, array $payload): PaymentRowDTO;
}
```

Todos los handlers son servicios con tag `app.revenue_payment_handler` y son inyectados en `PaymentBatchProcessor` vía `!tagged_iterator`.

### Métodos soportados y validaciones:

| Código | Handler | Validaciones específicas | Datos en `referenceNumber` |
|---|---|---|---|
| `cash` | `CashPaymentHandler` | `amount > 0` | — |
| `credit_card` | `CreditCardPaymentHandler` | `amount > 0`, `installments >= 1` | `authorization_code` |
| `debit_card` | `DebitCardPaymentHandler` | `amount > 0` | `voucher` |
| `check` | `CheckPaymentHandler` | `amount > 0`, `check_number` | `check_number` |
| `bank_transfer` | `BankTransferPaymentHandler` | `amount > 0`, `transfer_number` | `transfer_number` |
| `electronic_voucher` | `ElectronicVoucherPaymentHandler` | `amount > 0`, `folio` | `folio` |
| `manual_voucher` | `ManualVoucherPaymentHandler` | `amount > 0`, `folio` | `folio` |
| `bonoweb` | `BonoWebPaymentHandler` | `amount >= 0` (copago puede ser 0) | `voucher_id` |
| `gratuity` | `GratuityPaymentHandler` | `amount >= 0` | — |

**Configuración en `PaymentMethodConfigRegistry`:**

```php
'cash'               → max_rows: 20 | CashPaymentType
'credit_card'        → max_rows: 20 | CreditCardPaymentType
'debit_card'         → max_rows: 20 | DebitCardPaymentType
'electronic_voucher' → max_rows: 20 | ElectronicVoucherPaymentType
'manual_voucher'     → max_rows: 20 | ManualVoucherPaymentType
'bonoweb'            → max_rows:  1 | BonoWebPaymentType
'check'              → max_rows: 20 | CheckPaymentType
'bank_transfer'      → max_rows: 20 | BankTransferPaymentType
'gratuity'           → max_rows:  5 | GratuityPaymentType
```

**Nota:** `bonoweb` tiene `max_rows: 1` porque un pago solo puede tener un voucher BonoWeb activo.

---

## 8. Integraciones Externas — BonoWeb (Snabb)

### BonoWebClient

**Archivo:** `src/Service/Revenue/BonoWeb/BonoWebClient.php`

Cliente HTTP que encapsula todas las llamadas a la API REST de Snabb.

**Configuración (`.env`):**
```
BONOWEB_API_URL=https://api.snabb.cl
BONOWEB_API_KEY=
BONOWEB_TIMEOUT=30
```

**Configuración en `services.yaml`:**
```yaml
App\Service\Revenue\BonoWeb\BonoWebClient:
    arguments:
        $apiUrl:  '%env(BONOWEB_API_URL)%'
        $apiKey:  '%env(BONOWEB_API_KEY)%'
        $timeout: '%env(int:BONOWEB_TIMEOUT)%'
```

**Endpoints Snabb consumidos:**

| Método | Endpoint | Acción |
|---|---|---|
| POST | `{apiUrl}/vouchers` | Crear voucher |
| GET | `{apiUrl}/voucher/{id}` | Consultar estado |
| POST | `{apiUrl}/voucher/{id}/update-status` | Marcar Done \| Canceled |

**Autenticación:** `Authorization: Bearer {BONOWEB_API_KEY}` en todos los requests.

**Manejo de errores:** Lanza `BonoWebException` (extends `\RuntimeException`) en HTTP >= 400 o error de parseo JSON.

### BonoWebService

**Archivo:** `src/Service/Revenue/BonoWeb/BonoWebService.php`

Orquesta el ciclo de vida de vouchers BonoWeb: creación, sincronización, confirmación, cancelación.

**Estructura del payload enviado a Snabb (`createAndPersist`):**
```json
{
  "beneficiario": {
    "run": "12345678-9"
  },
  "codigoSucursal": "CL001",
  "prestaciones": [
    { "codigo": "P0301" }
  ],
  "callback_url": "https://app.hospital.cl/revenue/bonoweb/webhook",
  "redirect_url": "",
  "fechaExpiracion": "2026-02-24T15:30:00-03:00",
  "practitioner": {
    "nombre": "Dr. Juan García",
    "run": "98765432-1"
  }
}
```

**Mapeo de datos:**
- `beneficiario.run` ← `Patient.person.identification` (RUT del paciente)
- `codigoSucursal` ← `CashRegisterLocation.branch.getCode()`
- `prestaciones[].codigo` ← `prestacion['code']` del formulario
- `callback_url` ← Generada con `UrlGeneratorInterface::ABSOLUTE_URL` apuntando a `app_revenue_bonoweb_webhook`
- `fechaExpiracion` ← `now() + 24 horas` en formato ISO 8601 (`DateTimeInterface::ATOM`)
- `practitioner.run` ← `Member.username` (en sistemas chilenos el username es el RUT)

### BonoWebController

**Archivo:** `src/Controller/Revenue/BonoWeb/BonoWebController.php`
**Ruta base:** `/revenue/bonoweb` | `app_revenue_bonoweb_`

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/revenue/bonoweb/panel` | GET | `panel()` | `app_revenue_bonoweb_panel` |
| `/revenue/bonoweb/generate` | POST | `generate()` | `app_revenue_bonoweb_generate` |
| `/revenue/bonoweb/{voucherId}/status` | GET | `status(string)` | `app_revenue_bonoweb_status` |
| `/revenue/bonoweb/{voucherId}/confirm` | POST | `confirm(string)` | `app_revenue_bonoweb_confirm` |
| `/revenue/bonoweb/webhook` | POST | `webhook()` | `app_revenue_bonoweb_webhook` |

**Flujo completo:**

```
1. Cajero abre panel BonoWeb
   GET /revenue/bonoweb/panel?paymentAccountId={id}&cashRegisterLocationId={id}
   → Carga formulario con prestaciones disponibles

2. Cajero selecciona prestaciones y genera voucher
   POST /revenue/bonoweb/generate
   body: paymentAccountId, cashRegisterLocationId?, prestaciones[]
   → BonoWebService::createAndPersist()
   → Snabb responde con UUID y URL de pago
   → Frame actualizado con URL de pago + polling activado

3. Polling de estado (cada 3 segundos)
   GET /revenue/bonoweb/{voucherId}/status
   → BonoWebService::syncStatus()
   → Turbo Frame bonoweb-status se reemplaza
   → Si status='Paid': botón de confirmación habilitado

4. Cajero confirma que el paciente pagó
   POST /revenue/bonoweb/{voucherId}/confirm
   → BonoWebService::confirmDone() → Snabb marca 'Done'
   → Frame de confirmación con copago y bonificación
   → Stimulus controller auto-rellena fila de pago BonoWeb

5. Snabb notifica cambio de estado (callback asíncrono)
   POST /revenue/bonoweb/webhook
   body JSON: {"id": "uuid-...", "status": "Paid"}
   → BonoWebService::syncStatus()
   → Responde siempre {"ok": true} (HTTP 200)
```

**Nota sobre webhook:** La ruta webhook no requiere CSRF ni autenticación de sesión. Es llamada por el servidor de Snabb, no por el navegador.

---

## 9. Integraciones Externas — DTE (Aces/SII)

### AcesClient

**Archivo:** `src/Service/Revenue/Dte/AcesClient.php`

Cliente HTTP para el webservice Aces de emisión de DTEs al Servicio de Impuestos Internos (SII) de Chile.

**Configuración (`.env`):**
```
ACES_WS_URL=
ACES_USER=
ACES_PASSWORD=
ACES_API_KEY=
ACES_ID_EMPRESA=
ACES_PREVIEW=true
```

**Configuración en `services.yaml`:**
```yaml
App\Service\Revenue\Dte\AcesClient:
    arguments:
        $wsUrl:     '%env(ACES_WS_URL)%'
        $user:      '%env(ACES_USER)%'
        $password:  '%env(ACES_PASSWORD)%'
        $apiKey:    '%env(ACES_API_KEY)%'
        $idEmpresa: '%env(ACES_ID_EMPRESA)%'
        $preview:   '%env(bool:ACES_PREVIEW)%'
```

**Protocolo:**
- POST form-encoded (no JSON) al endpoint del webservice Aces.
- Credenciales se agregan internamente en `buildPayload()`: `u`, `p`, `apikey`, `preview`, `idempresa`.
- El llamador solo provee datos de negocio.
- **Reintento automático:** si la respuesta indica error semántico (`isErrorResponse()`), reintenta una vez. Si la segunda también falla, lanza `AcesException`.
- Retorna `stdClass` parseado del JSON de respuesta.

**Campos de credenciales inyectados:**
```php
['u', 'p', 'apikey', 'preview' => $preview ? '1' : '0', 'idempresa']
```

### DteService

**Archivo:** `src/Service/Revenue/Dte/DteService.php`

Determina qué boletas emitir, construye los payloads y registra los resultados.

**Clasificación afecta/exenta:**
```
BillingItem.taxAffectationType === null
  → tipodte = '41' (Exenta)

BillingItem.taxAffectationType.name contiene 'afect' (case-insensitive)
  → tipodte = '39' (Afecta)

Cualquier otro valor de taxAffectationType.name
  → tipodte = '41' (Exenta)
```

**Cálculo de IVA:**
```
tipodte='39' (Afecta):
  mntneto = round(mnttotal / 1.19)
  iva     = mnttotal - mntneto

tipodte='41' (Exenta):
  mntneto = mnttotal
  iva     = 0
```

**Payload enviado a Aces (por cada grupo):**
```
nrocaja    → paymentAccount.cashRegister.id
nrotienda  → subCompany.id
rutemisor  → subCompany.taxId
tipodte    → '39' | '41'
folio      → VoucherEntry.folioNumber
fchemis    → fecha de hoy (Y-m-d)
tasaiva    → '19.00'
rutrecep   → person.identification
correorecep → person.email
dirrecep   → ''  (pendiente)
cmnarecep  → ''  (pendiente)
rznsocrecep → person.name + ' ' + person.lastName
mnttotal   → suma de montoitem
mntneto    → calculado según tipodte
iva        → calculado según tipodte
Detalle    → array de líneas de prestación
```

**Estructura de una línea de detalle:**
```
nmbitem        → BillingItem.name
qtyitem        → ClinicalActionPatient.quantity
prcitem        → ClinicalActionPatient.unitPrice
montoitem      → ClinicalActionPatient.totalAmount
descuentomonto → ClinicalActionPatient.discountAmount
descuentopct   → round(discountAmount / brutoLinea * 100, 2)
```

**DteController (Reintento):**

**Archivo:** `src/Controller/Revenue/Dte/DteController.php`
**Ruta:** `POST /revenue/dte/{id}/retry` | `app_revenue_dte_retry`

Permite reintentar manualmente un `DteDocument` con `status='error'` o `'retry_pending'`:
1. Valida CSRF (`dte_retry_{id}`).
2. Verifica `isRetryable()`.
3. Extrae `retryData` del `DteDocument`.
4. Llama `AcesClient::send($retryData)` — las credenciales se agregan automáticamente.
5. Si éxito: `status='sent'`, `sentAt=now()`. Flush.
6. Si error: `status='retry_pending'`. Flush.
7. Retorna `JsonResponse` `{success, status, message}`.

---

## 10. Templates y Turbo Frames

### Árbol de templates

```
templates/revenue/cash-register/
├── index.html.twig                      — Shell principal
├── opening/
│   └── _open_panel.html.twig            — Panel apertura (Turbo Frame)
├── closing/
│   ├── _close_form.html.twig            — Formulario cierre
│   ├── _close_report.html.twig          — Reporte post-cierre
│   └── _voucher_pdf.html.twig           — Template PDF de boleta
├── difference/
│   ├── _form.html.twig                  — Formulario de diferencia
│   └── _status.html.twig                — Estado (polling)
├── _patient_search.html.twig            — Búsqueda paciente (Turbo Frame)
├── _patient_context.html.twig           — Contexto paciente (Turbo Frame)
├── _services_panel.html.twig            — Panel prestaciones (Turbo Frame)
├── _services_row.html.twig              — Fila de prestación
├── _services_stream.html.twig           — Turbo Stream append/remove
├── _status_bar.html.twig                — Estado caja (Turbo Frame)
├── _payment_confirm_stream.html.twig    — Resultado confirmación (Turbo Stream)
├── _post_payment.html.twig              — Resumen post-pago (Turbo Frame)
└── _post_payment_history.html.twig      — Historial (Turbo Frame)

templates/revenue/bonoweb/
├── _panel.html.twig                     — Panel BonoWeb (Turbo Frame)
├── _status.html.twig                    — Estado voucher (Turbo Frame)
└── _confirmed.html.twig                 — Confirmación BonoWeb (Turbo Frame)

templates/revenue/payment/
├── _batch.html.twig                     — Builder de medios de pago
└── method/
    ├── _cash_row.html.twig
    ├── _credit_card_row.html.twig
    ├── _debit_card_row.html.twig
    ├── _check_row.html.twig
    ├── _bank_transfer_row.html.twig
    ├── _electronic_voucher_row.html.twig
    ├── _manual_voucher_row.html.twig
    ├── _bonoweb_row.html.twig
    └── _gratuity_row.html.twig
```

### Turbo Frames y sus responsabilidades

| ID del Frame | Cargado por | Contenido |
|---|---|---|
| `cash-register-status-bar` | lazy, `statusBarUrl` | Badge: abierta / sin talonario / sin cerrar / cerrada |
| `patient-search` | lazy, `patientSearchUrl` | Input de búsqueda con autocomplete |
| `patient-context` | al seleccionar paciente | Datos demográficos + campos hidden |
| `services-panel` | al seleccionar paciente | Tabla de prestaciones + buscador |
| `payment-panel` | cuando hay servicios | Formulario de medios de pago |
| `difference-panel` | al pulsar "Solicitar descuento" | Formulario de diferencia |
| `difference-status` | polling cada 3 s | Estado de autorización |
| `bonoweb-panel` | manual | Panel BonoWeb (3 estados) |
| `bonoweb-status` | polling cada 3 s | Estado del voucher Snabb |
| `post-payment-summary` | post-confirmación | Resumen de pago |
| `post-payment-history` | al cambiar de tab | Historial de pagos |

---

## 11. Stimulus Controllers (JavaScript)

### revenue--cash-register (`assets/controllers/revenue/cash-register-controller.js`)

Orquestador principal. Coordina los eventos entre frames.

**Targets:** `servicesPanelWrapper`, `actionsBar`, `totalDisplay`, `discountBadge`, `paymentSection`, `confirmButton`
**Values:** `servicesUrl`, `differenceFormUrl`, `admissionRecordId`, `appointmentId`

**Actions:**
- `patient:selected` → `onPatientSelected()`: carga el panel de servicios con el paciente seleccionado.
- `services:total-changed` → `onServicesTotalChanged(event)`: muestra/oculta barra de acciones y sección de pago según `event.detail.total`.
- `difference:approved` → `onDifferenceApproved()`: aplica descuento al total visible.
- `submit` del formulario → `onPaymentFormSubmit(event)`: inyecta `patient_id` y `services_payload` desde el DOM antes de enviar.

### revenue--services-selector (`assets/controllers/revenue/services-selector-controller.js`)

Gestiona el autocomplete de prestaciones y las acciones de agregar/eliminar filas.

**Targets:** `searchInput`, `dropdown`, `table`
**Values:** `searchUrl`, `addUrl`, `removeUrl`

**Actions:**
- `input` en searchInput → debounce 300ms → fetch `searchUrl?q=` → renderiza dropdown.
- Click en resultado → POST a `addUrl` → Turbo Stream append.
- Click en botón eliminar → DELETE a `removeUrl` → Turbo Stream remove.
- Emite `services:total-changed` con el nuevo total al modificar filas.

### revenue--bonoweb (`assets/controllers/revenue/bonoweb-controller.js`)

Gestiona el flujo BonoWeb: polling de estado y auto-relleno del campo de pago.

**Targets:** `statusFrame`
**Values:** `statusUrl`, `voucherId`, `copago`, `mode` (default: `'polling'`), `pollInterval` (default: 3000)

**Modos:**
- `polling`: `statusFrameTargetConnected()` → recarga el frame `statusFrame` cada `pollInterval` ms. Si el estado es final, detiene el polling. Si status='Done', emite `bonoweb:confirmed`.
- `confirmed`: `_fillPaymentRow()` → busca `[data-method-code="bonoweb"]` en el documento, rellena `[data-payment-amount-input]` con el copago y `[data-bonoweb-voucher-input]` con el `voucherId`. Emite `bonoweb:confirmed` (bubbles: true).

### revenue--difference (`assets/controllers/revenue/difference-controller.js`)

Polling del estado de autorización de descuentos.

**Values:** `statusUrl`, `pollInterval` (default: 3000)

**Acciones:**
- Al conectar: inicia polling GET a `statusUrl`.
- Si estado final (no `'solicitada'`): detiene polling, emite `difference:resolved` con el status.
- El orquestador escucha `difference:resolved` para actualizar el total.

### revenue--patient-search (`assets/controllers/revenue/patient-search-controller.js`)

Autocomplete de búsqueda de paciente.

- Debounce 400ms en input.
- Fetch a `findUrl?q=` → renderiza lista de resultados.
- Al seleccionar resultado → carga `contextUrl` en Turbo Frame `patient-context`.
- Emite `patient:selected` con `{patientId, patientName}`.

### revenue--post-payment (`assets/controllers/revenue/post-payment-controller.js`)

Gestiona las pestañas del resumen post-pago.

- Al cambiar de tab → carga el frame correspondiente (historial, etc.).
- Gestiona la visibilidad de los paneles.

---

## 12. Flujo de Negocio Completo

### Escenario: Cobro directo con tarjeta de crédito

```
1. Cajero navega a /revenue/cash-register
   → Shell cargado. Status bar: "Caja abierta · Caja Norte · desde 09:00"
   → Patient search vacío

2. Cajero escribe "García" en buscador
   → GET /patient/find?q=García → JSON con 5 resultados
   → Selecciona "Ana García (12.345.678-9)"
   → GET /patient/123/context → Turbo Frame patient-context cargado
   → Stimulus emite patient:selected → services panel cargado

3. Cajero busca prestación "Consulta"
   → GET /services/search?q=Consulta → JSON
   → Selecciona "Consulta Médica General ($25.000)"
   → POST /services/add → Turbo Stream append fila a tabla
   → Stimulus emite services:total-changed(25000)
   → Barra de acciones visible: "Total a cobrar: $25.000"
   → Sección de pago visible

4. Cajero selecciona "Tarjeta de Crédito" y llena datos
   → amount: 25000, cardLastDigits: 4242, installments: 1

5. Cajero pulsa "Confirmar pago"
   → Stimulus inyecta patient_id=123, services_payload=[...]
   → POST /payment/confirm
   → Transacción Doctrine:
      - PatientAccount 456 (existente)
      - PaymentAccount 789 (nueva)
      - ClinicalActionPatient {billingItem=1, quantity=1, unitPrice=25000, ...}
      - PaymentAccountDetail {paymentMethod=credit_card, amount=25000, cardLastDigits=4242}
      - VoucherEntry {folioNumber=1042} [PESSIMISTIC_WRITE]
      - flush() / commit()
   → DteService::emitirBoletas()
      - Prestación sin taxAffectationType → exenta (tipodte=41)
      - Payload Aces: mnttotal=25000, mntneto=25000, iva=0
      - AcesClient::send() → DteDocument status='sent'
   → Turbo Stream → redirect a /post-payment/789

6. Cajero ve resumen:
   - Paciente: Ana García
   - Prestación: Consulta Médica General × 1 → $25.000
   - Forma de pago: Tarjeta de Crédito $25.000 (****4242, 1 cuota)
   - Folio de boleta: N° 1042
   - Botones: Imprimir | Historial | Anular
```

---

## 13. Migraciones de Base de Datos

### Version20260222050302
`ALTER TABLE account_status ADD code VARCHAR(60) DEFAULT '' NOT NULL`
- Agrega columna `code` para identificación machine-readable de estados de cuenta.

### Version20260222050303
Data migration: puebla los 12 códigos en `account_status` (cerrada_pagada, anulada, abierta_en_garantia, etc.).

### Version20260222052433 — Tablas core del módulo
Crea en una sola migración:
- `cash_register` (con todas las FKs: member, location, branch, reopenedByMember)
- `cash_register_check_detail`
- `cash_register_detail` (FK → cash_register, payment_method, bank)
- `cashier_assignment` (FK → member, cash_register_location)
- `clinical_action_patient` (FK → payment_account, billing_item, difference, professional)
- `difference` (FK → member×3, reason, type, direction, patient_account)
- `payment_account_detail` (FK → payment_account, payment_method, voucher_entry)
- `voucher` (FK → cash_register_location, sub_company)
- `voucher_entry` (FK → voucher, payment_account, member)
- Todas las secuencias correspondientes e índices.

### Version20260223023720 — BonoWeb + DTE + FKs
- Crea `bono_web_voucher` (FK → payment_account_detail)
- Crea `bono_web_voucher_detail` (FK → bono_web_voucher, billing_item)
- Crea `dte_document` (FK → payment_account)
- Altera `billing_item`: agrega `tax_affectation_type_id` FK → `tax_affectation_type`
- Altera `payment_account`: agrega `cash_register_id` FK → `cash_register`

**Todas las migraciones aplicadas a tenant ID 5 (melisalacolina).**

---

## 14. Validación y Seguridad

### Protección CSRF

| Formulario | Token ID | Controller |
|---|---|---|
| Apertura de caja | `cash_open` | `CashOpeningController::openSubmit()` |
| Cierre de caja | `cash_close_{id}` | `CashOpeningController::closeSubmit()` |
| Confirmación de pago | `payment_confirm` | `PaymentConfirmController::confirm()` |
| Anulación de pago | `payment_void_{id}` | `PostPaymentController::void()` |
| Solicitud de diferencia | `difference_request` | `DifferenceController::request()` |
| Cancelar diferencia | `difference_cancel_{id}` | `DifferenceController::cancel()` |
| Generar voucher BonoWeb | `bonoweb_generate` | `BonoWebController::generate()` |
| Confirmar BonoWeb | `bonoweb_confirm_{voucherId}` | `BonoWebController::confirm()` |
| Reintento DTE | `dte_retry_{id}` | `DteController::retry()` |

### Precisión monetaria
Todos los cálculos de montos usan `bcmath` con 2 decimales:
```php
bcadd($a, $b, 2)
bcsub($a, $b, 2)
```

### Control de acceso a caja
- El cajero solo puede cerrar su propia caja (verificado en `CashOpeningController::close()`).
- `resolveCurrentCashRegister()` valida que `cashRegister.member.id === member.id`.
- Si no coincide: acceso denegado.

### Lock pesimista en VoucherService
`PESSIMISTIC_WRITE` garantiza unicidad de folios bajo concurrencia. Sin este lock, dos cajeros procesando cobros simultáneamente podrían obtener el mismo folio.

---

## 15. Pendientes y Limitaciones Conocidas

| Ítem | Prioridad | Descripción |
|---|---|---|
| `calculateExpectedTotal()` stub | Media | Actualmente retorna `'0.00'`. Debe calcular suma de `PaymentAccount.amount` de la jornada para dar surplus/deficit real. |
| DifferenceType auto-aprobación | Media | Usa campo legacy `idEstado === 2`. Pendiente migrar a campo booleano dedicado `allows_auto_approve`. |
| `dirrecep` y `cmnarecep` en DTE | Baja | Enviados como strings vacíos. Pendiente agregar dirección y comuna a `Person`. |
| PDF de cierre de caja | Media | `report()` retorna HTML. Pendiente implementar generación PDF. |
| IMED integration | Baja | PostPagoController legacy tenía referencias a `IMED_URL_INTERFAZ_PROD`. No migrado. |
| Submodulo Recaudación completo | — | Este documento cubre la implementación MVP. Los submódulos Supervisor y Pago Cuenta están en documentos separados. |
