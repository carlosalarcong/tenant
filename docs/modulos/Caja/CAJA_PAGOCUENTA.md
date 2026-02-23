# CAJA — Sub-módulo: Pago Cuenta (PagoCuenta)

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23
> **Legado de referencia:** `RecaudacionBundle/Controller/Api/Unab/PagoCuenta/CuentaPacienteController`
>   y `PagoCuentaBundle` en `/var/www/html/melisa_prod`

---

## Índice

1. [Propósito y alcance](#1-propósito-y-alcance)
2. [Arquitectura general](#2-arquitectura-general)
3. [Entidades del dominio](#3-entidades-del-dominio)
   - 3.1 PatientAccount (CuentaPaciente)
   - 3.2 PaymentAccount (PagoCuenta)
   - 3.3 AccountStatus (EstadoCuenta)
   - 3.4 ClinicalActionPatient (AccionClinicaPaciente)
   - 3.5 PaymentAccountDetail (DetallePagoCuenta)
4. [Repositorios](#4-repositorios)
   - 4.1 PatientAccountRepository
   - 4.2 PaymentAccountRepository
   - 4.3 AdmissionRecordRepository (métodos relevantes)
5. [Controladores](#5-controladores)
   - 5.1 PatientAccountController — endpoints de soporte
   - 5.2 PostPaymentController — resumen post-pago
   - 5.3 PaymentConfirmController — núcleo transaccional (referencia)
6. [Templates Twig](#6-templates-twig)
   - 6.1 `_account_summary.html.twig`
   - 6.2 `_financier_form.html.twig`
   - 6.3 `_financial_guarantee.html.twig`
   - 6.4 `_post_payment.html.twig`
   - 6.5 `_post_payment_history.html.twig`
   - 6.6 `_payment_confirm_stream.html.twig`
7. [Flujos de negocio](#7-flujos-de-negocio)
   - 7.1 Creación automática de PatientAccount durante cobro
   - 7.2 Ciclo de vida de PaymentAccount
   - 7.3 Comprobación de pagos pendientes
   - 7.4 Resguardo financiero de un ingreso
   - 7.5 Anulación de un pago
8. [Códigos de estado de cuenta (AccountStatus.code)](#8-códigos-de-estado-de-cuenta)
9. [Mapeo Legacy → Tenant](#9-mapeo-legacy--tenant)
10. [Relaciones entre entidades (diagrama)](#10-relaciones-entre-entidades-diagrama)
11. [Rutas HTTP completas](#11-rutas-http-completas)
12. [Seguridad y validaciones](#12-seguridad-y-validaciones)
13. [Consideraciones técnicas](#13-consideraciones-técnicas)

---

## 1. Propósito y alcance

El sub-módulo **Pago Cuenta** es la capa de **gestión de cuentas de facturación** del sistema. Su función es:

- **Mantener la cuenta maestra de facturación** de cada paciente (`PatientAccount`), que agrupa todos los registros de pago individuales.
- **Exponer fragmentos de información de cuenta** al flujo de caja mediante Turbo Frames, para que el cajero visualice el estado del paciente antes, durante y después del cobro.
- **Permitir la consulta del resguardo financiero** de un ingreso hospitalario, mostrando todos los `PaymentAccount` asociados a un `AdmissionRecord`.
- **Verificar si un paciente tiene deudas pendientes**, incluyendo cuentas en las que la persona actúa como tutor/garante legal de otros pacientes.
- **Gestionar el ciclo post-pago**: resumen de cobro, historial, impresión de boleta y anulación.

Este sub-módulo no genera pagos directamente (eso lo hace `PaymentConfirmController` en el sub-módulo Recaudación), sino que **expone y gestiona la visibilidad** de las cuentas resultantes.

---

## 2. Arquitectura general

```
PatientAccountController      ← endpoints de soporte / Turbo Frames
    ├── GET /summary           → _account_summary.html.twig
    ├── GET /financier         → _financier_form.html.twig
    ├── GET /financial-guarantee → _financial_guarantee.html.twig
    └── GET /has-pending-payments → JSON { hasPending: bool }

PostPaymentController         ← ciclo de vida post-cobro
    ├── GET  /{id}             → _post_payment.html.twig  (resumen)
    ├── GET  /{id}/history     → _post_payment_history.html.twig
    ├── GET  /{id}/print       → inline PDF (PdfService)
    └── POST /{id}/void        → anula PaymentAccount + VoucherEntry + Details

PaymentConfirmController      ← crea PatientAccount/PaymentAccount (ver CAJA_RECAUDACION.md)
```

Todas las respuestas HTML son **Turbo Frames** (sin layout completo), excepto la impresión PDF que usa `data-turbo="false"` para que el navegador la gestione directamente.

---

## 3. Entidades del dominio

### 3.1 PatientAccount (CuentaPaciente)

**Archivo:** `src/Entity/Tenant/PatientAccount.php`
**Tabla:** `patient_account`
**Legado:** `cuenta_paciente`

Cuenta **maestra** de facturación de un paciente. Una `PatientAccount` agrupa todos los `PaymentAccount` (pagos individuales) del paciente. Permanece abierta mientras alguno de sus pagos esté pendiente de regularización.

#### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK auto-incremental |
| `patient` | `patient_id` | FK → `Patient` | Paciente al que pertenece la cuenta |
| `accountStatus` | `account_status_id` | FK → `AccountStatus` (nullable) | Estado actual de la cuenta (abierta/cerrada/pagada) |
| `modifiedByMember` | `modified_by_member_id` | FK → `Member` (nullable) | Miembro que realizó la última modificación |
| `paymentAccounts` | *(OneToMany inverso)* | `Collection<PaymentAccount>` | Pagos individuales de esta cuenta |
| `totalAccount` | `total_account` | `decimal(12,2)` (nullable) | Total de la cuenta |
| `affectedAccount` | `affected_account` | `decimal(12,2)` (nullable) | Monto afecto a honorarios (AFECTO_CUENTA) |
| `questionOne` | `question_one` | `integer` (nullable) | Respuesta interna legado PREGUNTA_UNO |
| `questionTwo` | `question_two` | `integer` (nullable) | Respuesta interna legado PREGUNTA_DOS |
| `totalPreAccount` | `total_pre_account` | `decimal(12,2)` (nullable) | Total de pre-cuenta |
| `preAccountNumber` | `pre_account_number` | `integer` (nullable) | Número de pre-cuenta |
| `totalDiscount` | `total_discount` | `decimal(12,2)` (nullable) | Total de descuentos aplicados |
| `balanceCorrectionRut` | `balance_correction_rut` | `integer` (nullable) | RUT del funcionario que corrigió saldo |
| `accountBalance` | `account_balance` | `decimal(10,2)` (nullable) | Saldo pendiente de la cuenta |
| `totalPackagedAccount` | `total_packaged_account` | `decimal(12,2)` (nullable) | Total de prestaciones paquetizadas |
| `modifiedAt` | `modified_at` | `datetime` (nullable) | Fecha de última modificación |

#### Relación OneToMany con PaymentAccount

```php
#[ORM\OneToMany(
    targetEntity: PaymentAccount::class,
    mappedBy: 'patientAccount',
    cascade: ['persist'],
    orphanRemoval: true
)]
private Collection $paymentAccounts;
```

Los `PaymentAccount` se crean por cascada al hacer `persist` en `PatientAccount`. La eliminación huérfana garantiza que si un `PaymentAccount` se desvincula, se elimina de BD.

---

### 3.2 PaymentAccount (PagoCuenta)

**Archivo:** `src/Entity/Tenant/PaymentAccount.php`
**Tabla:** `payment_account`
**Legado:** `pago_cuenta`

Registro de **pago individual** dentro de una `PatientAccount`. Una cuenta puede tener N pagos (cuotas, regularizaciones, cobros distintos).

#### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK auto-incremental |
| `patientAccount` | `patient_account_id` | FK → `PatientAccount` (NOT NULL) | Cuenta maestra a la que pertenece |
| `patient` | `patient_id` | FK → `Patient` (nullable) | Paciente directo del pago |
| `paymentStatus` | `payment_status_id` | FK → `PaymentStatus` (nullable) | Estado del pago (Pendiente/Regularizado/Anulado) |
| `createdByMember` | `created_by_member_id` | FK → `Member` (nullable) | Cajero que registró el pago |
| `cancelledByMember` | `cancelled_by_member_id` | FK → `Member` (nullable) | Cajero que anuló el pago |
| `cashRegisterLocation` | `cash_register_location_id` | FK → `CashRegisterLocation` (nullable) | Caja física donde se procesó |
| `cashRegister` | `cash_register_id` | FK → `CashRegister` (nullable) | **Sesión de caja** (para `nrocaja` en DTE) |
| `subCompany` | `sub_company_id` | FK → `SubCompany` (nullable) | Sub-empresa para estructuras multi-empresa |
| `differenceReason` | `difference_reason_id` | FK → `DifferenceReason` (nullable) | Motivo de diferencia |
| `paymentDate` | `payment_date` | `datetime` (NOT NULL) | Fecha/hora del pago |
| `documentNumber` | `document_number` | `bigint` (nullable) | Número de documento |
| `tax` | `tax` | `integer` (nullable) | Impuesto aplicado |
| `amount` | `amount` | `decimal(12,2)` (nullable) | Monto del pago |
| `documentStatusId` | `document_status_id` | `integer` (nullable) | Estado del documento (sin FK, crudo) |
| `installment` | `installment` | `varchar(11)` (nullable) | Cuota (ej. "1/3") |
| `scheduledPaymentDate` | `scheduled_payment_date` | `datetime` (nullable) | Fecha programada de pago |
| `cancellationDate` | `cancellation_date` | `datetime` (nullable) | Fecha de anulación |
| `cancellationReason` | `cancellation_reason` | `text` (nullable) | Motivo de anulación |
| `regularizationDate` | `regularization_date` | `datetime` (nullable) | Fecha de regularización |
| `regularizationNotes` | `regularization_notes` | `text` (nullable) | Notas de regularización |
| `differenceAmount` | `difference_amount` | `decimal(10,2)` (nullable) | Monto de diferencia |
| `priceVariance` | `price_variance` | `decimal(10,2)` (nullable) | Diferencia de precio |
| `isCollection` | `is_collection` | `boolean` (default: false) | Indica si es cobro/cobranza |

#### Nota sobre `cashRegister` vs `cashRegisterLocation`

- **`cashRegisterLocation`**: Ubicación física de la caja (ej. "Caja 1 – Piso 2"). Es estable, no cambia.
- **`cashRegister`**: Sesión de apertura de caja del cajero en ese día/turno. Es la FK usada para `nrocaja` en el DTE.

Esta distinción se corrigió en la migración `Version20260223023720.php`, que agrega `cash_register_id` a `payment_account`.

---

### 3.3 AccountStatus (EstadoCuenta)

**Archivo:** `src/Entity/Tenant/AccountStatus.php`
**Tabla:** `account_status`
**Legado:** `estado_cuenta`

Catálogo de estados de la cuenta maestra del paciente.

#### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(60)` | Nombre legible (ej. "Abierta pendiente de pago") |
| `code` | `code` | `varchar(60)` | Slug de negocio (ej. `abierta_pendiente_pago`) |

#### Códigos con deuda activa (usados en `hasPendingPayments`)

Los siguientes códigos se consideran **deuda pendiente** en el sistema:

| Código | Significado |
|---|---|
| `cerrada_revision_interna` | Cuenta cerrada, en revisión interna |
| `cerrada_revision_financiador` | Cuenta cerrada, en revisión del financiador |
| `cerrada_pendiente_pago` | Cuenta cerrada pero con pago pendiente |
| `cerrada_cobranza_interna` | Enviada a cobranza interna |
| `cerrada_cobranza_judicial` | Enviada a cobranza judicial |
| `cerrada_pagada_con_saldo_pendiente` | Pagada parcialmente con saldo remanente |
| `abierta_pendiente_pago` | Cuenta abierta y pendiente de pago |

---

### 3.4 ClinicalActionPatient (AccionClinicaPaciente)

**Archivo:** `src/Entity/Tenant/ClinicalActionPatient.php`
**Tabla:** `clinical_action_patient`
**Legado:** `accion_clinica_paciente`

Representa una **prestación clínica cobrada** dentro de un `PaymentAccount`. Por cada ítem del `services_payload` enviado al confirmar el pago, se crea un `ClinicalActionPatient`.

#### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `paymentAccount` | `payment_account_id` | FK → `PaymentAccount` (NOT NULL) | Pago al que pertenece |
| `billingItem` | `billing_item_id` | FK → `BillingItem` (nullable) | Ítem de facturación cobrado |
| `difference` | `difference_id` | FK → `Difference` (nullable) | Diferencia/descuento aplicado |
| `professional` | `professional_id` | FK → `Professional` (nullable) | Profesional que realizó la prestación |
| `quantity` | `quantity` | `integer` (default: 1) | Cantidad de unidades |
| `unitPrice` | `unit_price` | `decimal(12,2)` (default: 0.00) | Precio unitario |
| `totalAmount` | `total_amount` | `decimal(12,2)` (default: 0.00) | Monto total (quantity × unitPrice) |
| `discountAmount` | `discount_amount` | `decimal(12,2)` (default: 0.00) | Descuento aplicado |
| `isCancelled` | `is_cancelled` | `boolean` (default: false) | ¿Prestación anulada? |
| `notes` | `notes` | `text` (nullable) | Observaciones |
| `createdAt` | `created_at` | `datetime` | Fecha de creación (auto en constructor) |
| `updatedAt` | `updated_at` | `datetime` (nullable) | Fecha de última actualización |

#### Relación con BillingItem y TaxAffectationType

`BillingItem` tiene un FK nullable hacia `TaxAffectationType`. Cuando `DteService` emite los DTEs, examina `billingItem.taxAffectationType.name`:
- Si contiene "afect" (case-insensitive) → `tipodte=39` (boleta afecta con IVA 19%)
- Si no → `tipodte=41` (boleta exenta, sin IVA)

---

### 3.5 PaymentAccountDetail (DetallePagoCuenta)

**Archivo:** `src/Entity/Tenant/PaymentAccountDetail.php`
**Tabla:** `payment_account_detail`

Detalla el **medio de pago** utilizado para un `PaymentAccount`. Un pago puede dividirse en múltiples medios (efectivo + tarjeta, por ejemplo).

#### Campos clave

| Campo PHP | Columna BD | Descripción |
|---|---|---|
| `paymentAccount` | `payment_account_id` | FK → PaymentAccount |
| `paymentMethod` | `payment_method_id` | FK → PaymentMethod (efectivo, tarjeta, etc.) |
| `amount` | `amount` | Monto pagado con este medio |
| `referenceNumber` | `reference_number` | Nro. de referencia (transferencia, folio, etc.) |
| `cardLastDigits` | `card_last_digits` | Últimos 4 dígitos de tarjeta |
| `installments` | `installments` | Cuotas (solo tarjeta crédito) |
| `isCancelled` | `is_cancelled` | Se marca `true` al anular el PaymentAccount |

---

## 4. Repositorios

### 4.1 PatientAccountRepository

**Archivo:** `src/Repository/Tenant/PatientAccountRepository.php`

#### `findByPatientId(int $patientId): ?PatientAccount`

Carga la `PatientAccount` de un paciente con todas sus relaciones necesarias para el resumen de cuenta:
- `accountStatus`, `patient`, `patient.person`, `patient.payer`, `patient.agreement`, `patient.insurancePlan`

```php
return $this->createQueryBuilder('pa')
    ->leftJoin('pa.accountStatus', 'acst')
    ->leftJoin('pa.patient', 'p')
    ->leftJoin('p.person', 'per')
    ->leftJoin('p.payer', 'payer')
    ->leftJoin('p.agreement', 'agr')
    ->leftJoin('p.insurancePlan', 'plan')
    ->addSelect('acst', 'p', 'per', 'payer', 'agr', 'plan')
    ->where('pa.patient = :patientId')
    ->setParameter('patientId', $patientId)
    ->getQuery()
    ->getOneOrNullResult();
```

**Uso:** `PatientAccountController::summary()` y `PaymentConfirmController::confirm()` (para verificar si ya existe la cuenta antes de crearla).

#### `findByTutorPersonId(int $tutorPersonId): array`

Retorna `PatientAccount[]` de todos los pacientes cuyo tutor/garante legal es la `Person` indicada. Carga `accountStatus`, `patient`, `patient.person`, `patient.tutor`.

**Uso:** `PatientAccountController::summary()` para mostrar las cuentas a cargo del mismo tutor.

---

### 4.2 PaymentAccountRepository

**Archivo:** `src/Repository/Tenant/PaymentAccountRepository.php`

#### `findWithDetailsById(int $id): ?PaymentAccount`

Carga un `PaymentAccount` con sus relaciones para el resumen post-pago:
- `patient`, `patient.person`, `patient.payer`, `paymentStatus`, `cashRegisterLocation`, `createdByMember`, `cancelledByMember`

**Uso:** `PostPaymentController::summary()` y `PostPaymentController::print()`.

#### `findHistoryByPatientId(int $patientId, int $limit = 20): array`

Historial de los últimos N pagos de un paciente, ordenados por fecha descendente. Carga `paymentStatus` y `cashRegisterLocation`.

**Uso:** `PostPaymentController::history()` — tab de historial en el resumen post-pago.

---

### 4.3 AdmissionRecordRepository (métodos relevantes)

**Archivo:** `src/Repository/Tenant/AdmissionRecordRepository.php`

#### `findWithAccountById(int $admissionRecordId): ?AdmissionRecord`

Carga un `AdmissionRecord` con:
- `patient`, `patient.person`, `admissionStatus`, `patientAccount`, `patientAccount.accountStatus`, `patientAccount.paymentAccounts`, `paymentAccounts.paymentStatus`

Carga completa de la jerarquía financiera de un ingreso en una sola query.

**Uso:** `PatientAccountController::financialGuarantee()`.

#### `findForPaymentCheckByPersonId(int $personId): array`

Busca `AdmissionRecord[]` donde la `Person` es **el paciente o el tutor legal** de otro paciente, excluyendo pre-admisiones (aquellas con estado que contenga "pre" en el nombre, usando la constante `PENDING_STATUS_CANDIDATES`).

Carga: `patient`, `person`, `tutor`, `admissionStatus`, `patientAccount`, `patientAccount.accountStatus`.

**Uso:** `PatientAccountController::hasPendingPayments()` — comprueba deuda activa incluyendo garantías de terceros.

---

## 5. Controladores

### 5.1 PatientAccountController

**Archivo:** `src/Controller/Revenue/PatientAccount/PatientAccountController.php`
**Prefijo de ruta:** `#[Route('/revenue/patient-account', name: 'app_revenue_patient_account_')]`

Expone **4 endpoints de soporte** consumidos principalmente por el panel de caja y el módulo de Admisión.

#### Constructor

```php
public function __construct(
    private readonly PatientAccountRepository   $patientAccountRepository,
    private readonly AdmissionRecordRepository  $admissionRecordRepository,
    private readonly PatientRepository          $patientRepository,
) {}
```

---

#### `GET /revenue/patient-account/summary` → `summary`

| Parámetro query | Tipo | Descripción |
|---|---|---|
| `patientId` | `int` | ID del paciente |

**Turbo Frame:** `patient-account-summary`
**Template:** `revenue/patient-account/_account_summary.html.twig`

Lógica:
1. Si `patientId ≤ 0`, renderiza el frame vacío (estado "seleccione un paciente").
2. Carga `Patient` y su `PatientAccount` vía `findByPatientId()`.
3. Si el paciente tiene tutor (`patient.getTutor() !== null`), carga las cuentas de los pacientes que comparten ese tutor via `findByTutorPersonId()`.
4. Renderiza con `patient`, `account` (nullable), `guardianAccounts` (array, puede estar vacío).

**Datos mostrados en template:**
- Nombre, identificación, financiador/plan del paciente
- Total cuenta, saldo, descuento, número de pagos registrados
- Lista de cuentas a cargo del mismo tutor (si aplica)

---

#### `GET /revenue/patient-account/financier` → `financier`

| Parámetro query | Tipo | Descripción |
|---|---|---|
| `patientId` | `int` | ID del paciente |

**Turbo Frame:** `patient-account-financier`
**Template:** `revenue/patient-account/_financier_form.html.twig`

Muestra el **financiador activo** del paciente (previsión, convenio, plan de salud, tipo de atención). El cajero confirma esta información antes de facturar.

**Nota MVP:** El template actual solo muestra información de solo lectura. En versiones futuras permitirá cambiar el financiador antes del cobro.

Datos mostrados:
- `patient.payer.name` (Previsión)
- `patient.agreement.name` (Convenio, si aplica)
- `patient.insurancePlan.name` (Plan, si aplica)
- `patient.careType.name` (Tipo de atención, si aplica)

---

#### `GET /revenue/patient-account/financial-guarantee` → `guarantee`

| Parámetro query | Tipo | Descripción |
|---|---|---|
| `admissionRecordId` | `int` | ID del registro de admisión |

**Turbo Frame:** `patient-account-guarantee`
**Template:** `revenue/patient-account/_financial_guarantee.html.twig`

Muestra el **resguardo financiero** de un ingreso: la lista de todos los `PaymentAccount` asociados a la `PatientAccount` de esa admisión.

Lógica:
1. Si `admissionRecordId ≤ 0`, renderiza vacío.
2. Carga `AdmissionRecord` con toda la jerarquía financiera vía `findWithAccountById()`.
3. Extrae `patientAccount` y sus `paymentAccounts`.

**Datos mostrados en template:**
- Tabla de PaymentAccounts: N° documento, fecha pago, monto, estado (badge), cuota
- Fila extra de anulación si `cancellationDate` está presente
- Total resguardado (suma de montos no anulados)
- Saldo de la cuenta maestra vs total resguardado

---

#### `GET /revenue/patient-account/has-pending-payments` → `pending`

| Parámetro query | Tipo | Descripción |
|---|---|---|
| `patientId` | `int` | ID del paciente |

**Retorna JSON:** `{ "hasPending": true|false }`
**Legado:** `verificarPagosPendientesAction` (cambiado de plain-text a JSON).

Algoritmo:
1. Obtiene `Person.id` del paciente.
2. Consulta `findForPaymentCheckByPersonId()` → obtiene admisiones donde la persona es paciente O tutor, excluyendo pre-admisiones.
3. Para cada admisión, revisa `patientAccount.accountStatus.code`.
4. Si algún código coincide con `PENDING_ACCOUNT_STATUS_CODES`, retorna `{ "hasPending": true }`.

**Constante PENDING_ACCOUNT_STATUS_CODES:**
```php
private const PENDING_ACCOUNT_STATUS_CODES = [
    'cerrada_revision_interna',
    'cerrada_revision_financiador',
    'cerrada_pendiente_pago',
    'cerrada_cobranza_interna',
    'cerrada_cobranza_judicial',
    'cerrada_pagada_con_saldo_pendiente',
    'abierta_pendiente_pago',
];
```

---

### 5.2 PostPaymentController

**Archivo:** `src/Controller/Revenue/CashRegister/PostPaymentController.php`
**Prefijo de ruta:** `#[Route('/revenue/cash-register/post-payment', name: 'app_revenue_cash_register_post_payment_')]`
**Legado:** `ResumenPagoController` + `ImprimirBoletaController` + `AnularPagoController`

#### Constructor

```php
public function __construct(
    private readonly TenantEntityManager             $em,
    private readonly PaymentAccountRepository        $paymentAccountRepository,
    private readonly PaymentAccountDetailRepository  $detailRepository,
    private readonly ClinicalActionPatientRepository $clinicalActionRepository,
    private readonly VoucherEntryRepository          $voucherEntryRepository,
    private readonly PaymentStatusRepository         $paymentStatusRepository,
    private readonly PdfService                      $pdfService,
) {}
```

---

#### `GET /revenue/cash-register/post-payment/{id}` → `summary`

**Turbo Frame:** `payment-panel`
**Template:** `revenue/cash-register/_post_payment.html.twig`

**Destino final del flujo de cobro.** Recibe el redireccionamiento del Turbo Stream de éxito de `PaymentConfirmController`.

Carga:
- `paymentAccount` (con detalles completos vía `findWithDetailsById`)
- `details` (PaymentAccountDetails del pago, via `detailRepository.findByPaymentAccount`)
- `clinicalActions` (ClinicalActionPatient del pago, via `clinicalActionRepository.findByPaymentAccount`)
- `voucherEntry` (folio de boleta asignado, via `voucherEntryRepository.findOneByPaymentAccount`)

**Template muestra:**
- Badge de estado (ANULADO en rojo / Procesado en verde)
- Datos del paciente (nombre, RUT, financiador)
- Pestañas:
  - **"Detalle del pago"**: tabla de prestaciones clínicas + tabla de medios de pago
  - **"Historial del paciente"**: Turbo Frame lazy `post-payment-history` (carga `/history`)
- Acciones:
  - Imprimir (`data-turbo="false"`, `target="_blank"`) → `/print`
  - Anular (formulario POST con confirmación Stimulus, CSRF) → `/void`

---

#### `GET /revenue/cash-register/post-payment/{id}/history` → `history`

**Turbo Frame:** `post-payment-history`
**Template:** `revenue/cash-register/_post_payment_history.html.twig`

Muestra los últimos 20 pagos del mismo paciente, ordenados por fecha DESC.

Columnas: ID pago, Fecha, Caja, Monto, Estado (badge "Anulado" si `cancellationDate`), enlace "Ver" (`data-turbo-frame="payment-panel"`).

---

#### `GET /revenue/cash-register/post-payment/{id}/print` → `print`

Genera el **PDF de la boleta** (sin layout HTML), retornando directamente `application/pdf` con `Content-Disposition: inline`.

El enlace desde el template usa `data-turbo="false"` y `target="_blank"` para que el navegador abra el PDF en una nueva pestaña sin que Turbo intercepte la respuesta.

```php
return new Response(
    $pdf,
    Response::HTTP_OK,
    [
        'Content-Type'        => 'application/pdf',
        'Content-Disposition' => sprintf('inline; filename="boleta-%d.pdf"', $id),
        'Cache-Control'       => 'private, no-store',
    ]
);
```

---

#### `POST /revenue/cash-register/post-payment/{id}/void` → `void`

**Anula** un `PaymentAccount` y sus registros asociados.

Validaciones pre-anulación:
1. CSRF token `payment_void_{id}` válido.
2. `paymentAccount` existe.
3. `paymentAccount.cancellationDate === null` (no anulado previamente).
4. Usuario autenticado como `Member`.

Operaciones (sin transacción explícita — flush único):
1. Marca `paymentAccount.cancellationDate = now()`.
2. Asigna `paymentAccount.cancelledByMember = $member`.
3. Asigna `paymentAccount.cancellationReason` (del form, default: "Anulado por cajero").
4. Si existe `PaymentStatus` con `name='Anulado'`, lo asigna a `paymentAccount`.
5. Busca `VoucherEntry` asociado y lo marca `isCancelled = true`.
6. Busca todos los `PaymentAccountDetail` del pago y los marca `isCancelled = true`.
7. `em->flush()`.
8. Redirige a `summary` con flash de éxito.

**Nota:** La anulación **no genera un DTE de anulación** en la implementación actual. El folio queda marcado como cancelado en `VoucherEntry` pero sin reversa en ACES.

---

### 5.3 PaymentConfirmController (referencia al crear PatientAccount/PaymentAccount)

**Archivo:** `src/Controller/Revenue/CashRegister/PaymentConfirmController.php`

Aunque este controlador pertenece al sub-módulo de Recaudación, es el punto de **creación** de `PatientAccount` y `PaymentAccount`. Ver `CAJA_RECAUDACION.md` para el flujo completo.

Aspecto relevante para Pago Cuenta: la lógica de crear o recuperar una `PatientAccount` existente:

```php
// Paso 1: PatientAccount (crear o recuperar)
$patientAccount = $this->patientAccountRepository->findByPatientId($patientId);
$isNewAccount   = false;
if ($patientAccount === null) {
    $patientAccount = new PatientAccount();
    $patientAccount->setPatient($patient);
    $isNewAccount = true;
}
if ($isNewAccount) {
    $this->em->persist($patientAccount);
}
```

La `PatientAccount` se **crea sólo si no existe**. Si el paciente ya tiene una cuenta de ingresos previos, se reutiliza la misma, acumulando los nuevos `PaymentAccount` dentro.

---

## 6. Templates Twig

### 6.1 `_account_summary.html.twig`

**Ruta:** `templates/revenue/patient-account/_account_summary.html.twig`
**Turbo Frame:** `patient-account-summary`

**Secciones:**
- **Si `patient` es null:** Alerta gris "Seleccione un paciente..."
- **Cuenta propia del paciente:**
  - Card con header: icono + "Cuenta del paciente" + badge de estado (coloreado por nombre)
  - Nombre, RUT, financiador/convenio/plan del paciente
  - Grid 4 columnas: Total cuenta / Saldo / Descuento / Nro. pagos registrados
  - Fecha de última modificación (si existe)
  - Si `account === null`: alerta info "No tiene cuenta registrada"
- **Cuentas a cargo del tutor** (si `guardianAccounts` no está vacío):
  - Card con lista: nombre del paciente tutelado, RUT, estado, saldo pendiente (badge naranja si > 0)

**Lógica de coloración de badge:**
```twig
{% if 'pendiente' in account.accountStatus.name|lower or 'abierta' in account.accountStatus.name|lower %}
    bg-warning text-dark
{% elseif 'pagada' in account.accountStatus.name|lower %}
    bg-success
{% else %}
    bg-secondary
{% endif %}
```

---

### 6.2 `_financier_form.html.twig`

**Ruta:** `templates/revenue/patient-account/_financier_form.html.twig`
**Turbo Frame:** `patient-account-financier`

**Secciones:**
- **Si `patient` es null:** Alerta gris "Sin contexto de paciente"
- **Si `patient.payer` es null:** Alerta amarilla "Sin financiador registrado"
- **Si hay financiador:**
  - Card con filas para: Previsión / Convenio (si existe) / Plan (si existe)
  - Separador y línea de Tipo de atención (si `patient.careType` existe)

---

### 6.3 `_financial_guarantee.html.twig`

**Ruta:** `templates/revenue/patient-account/_financial_guarantee.html.twig`
**Turbo Frame:** `patient-account-guarantee`

**Secciones:**
- **Si `admission` es null:** Alerta gris "Seleccione un ingreso"
- **Si hay admisión:**
  - Cabecera: nombre/RUT del paciente + fecha de ingreso + badge de estado de cuenta
  - **Si `paymentAccounts` está vacío:** alerta info "Sin resguardos financieros"
  - **Si hay pagos:**
    - Tabla: N° documento / Fecha pago / Monto / Estado (badge) / Cuota
    - Fila adicional roja si `pa.cancellationDate` presente
    - Footer: Total resguardado (suma de pagos no anulados)
    - Comparativa: Total cuenta vs Saldo

**Lógica de badge de estado:**
```twig
{% set sn = pa.paymentStatus.name|lower %}
{% if 'anulad' in sn or 'cancel' in sn %}      bg-danger
{% elseif 'regulariz' in sn or 'pagad' in sn %} bg-success
{% elseif 'pendiente' in sn %}                  bg-warning text-dark
{% else %}                                       bg-secondary
{% endif %}
```

---

### 6.4 `_post_payment.html.twig`

**Ruta:** `templates/revenue/cash-register/_post_payment.html.twig`
**Turbo Frame:** `payment-panel`

Vista de resumen post-cobro. **Destino final del flujo de pago**.

**Secciones:**
1. **Header**: Badge "ANULADO" (rojo) o "Procesado" (verde) según `cancellationDate`
2. **Card datos del paciente**: nombre, RUT, financiador
3. **Pestaña "Detalle del pago":**
   - Tabla prestaciones: `billingItem.name`, cantidad, precio unitario, descuento, total
   - Tabla medios de pago: `paymentMethod.name`, referencia, últimos dígitos tarjeta, monto
   - Folio de boleta si `voucherEntry` existe
4. **Pestaña "Historial del paciente":** Turbo Frame lazy → carga `/history`
5. **Botones de acción:**
   - **Imprimir** → `data-turbo="false"` + `target="_blank"` → `/print`
   - **Anular** → formulario POST con Stimulus controller `confirm-void` (pide confirmación `window.confirm`)

---

### 6.5 `_post_payment_history.html.twig`

**Ruta:** `templates/revenue/cash-register/_post_payment_history.html.twig`
**Turbo Frame:** `post-payment-history`

Tabla de los últimos 20 pagos del paciente. Columnas: ID, Fecha, Caja, Monto, Estado, Acciones.

Enlace "Ver" a otros pagos del historial usa `data-turbo-frame="payment-panel"` para reemplazar el resumen actual sin recargar la página.

---

### 6.6 `_payment_confirm_stream.html.twig`

**Ruta:** `templates/revenue/cash-register/_payment_confirm_stream.html.twig`

Template de **Turbo Stream** que se retorna desde `PaymentConfirmController::confirm()`.

**Caso éxito** (HTTP 200):
```twig
<turbo-stream action="replace" target="payment-panel">
  <template>
    <turbo-frame id="payment-panel" src="{{ summaryUrl }}">
      <div>Cargando resumen…</div>
    </turbo-frame>
  </template>
</turbo-stream>
```

**Caso error** (HTTP 422):
- Lista de mensajes de error
- Botón "Volver y corregir" para no bloquear el flujo al cajero

---

## 7. Flujos de negocio

### 7.1 Creación automática de PatientAccount durante cobro

```
PaymentConfirmController::confirm()
  │
  ├─ patientAccountRepository.findByPatientId(patientId)
  │    └─ Si NULL → new PatientAccount(); persist()
  │    └─ Si existe → reutilizar (acumula PaymentAccounts)
  │
  └─ new PaymentAccount()
       ├─ setPatientAccount($patientAccount)
       ├─ setPatient($patient)
       ├─ setCreatedByMember($member)
       ├─ setCashRegisterLocation($cashRegisterLocation)
       ├─ setCashRegister($cashRegister)       ← nrocaja DTE
       ├─ setPaymentDate(new \DateTime())
       └─ setAmount(suma de prestaciones)
```

**Regla clave:** la `PatientAccount` es la cuenta **permanente** del paciente. Al segundo pago, se reutiliza la misma cuenta. Solo el `PaymentAccount` (y sus detalles) es nuevo cada vez.

---

### 7.2 Ciclo de vida de PaymentAccount

```
Estado inicial: "Pendiente"
      │
      ├── [Cajero anula] → PostPaymentController::void()
      │       ├── paymentAccount.cancellationDate = now
      │       ├── paymentAccount.paymentStatus = "Anulado"
      │       ├── voucherEntry.isCancelled = true
      │       └── paymentAccountDetails[*].isCancelled = true
      │
      └── [Supervisor regulariza] → (pendiente de implementar en sub-módulo Supervisor)
              └── paymentAccount.paymentStatus = "Regularizado"
                  paymentAccount.regularizationDate = now
```

---

### 7.3 Comprobación de pagos pendientes

Utilizado por el módulo de Admisión para alertar si el paciente tiene deudas:

```
GET /revenue/patient-account/has-pending-payments?patientId=123
  │
  ├─ patient = patientRepository.find(123)
  ├─ personId = patient.person.id
  ├─ admissions = admissionRecordRepository.findForPaymentCheckByPersonId(personId)
  │    └─ WHERE person.id = personId OR tutor.id = personId
  │       AND admissionStatus.name NOT IN (pre-admisiones)
  │
  └─ Para cada admission:
       └─ Si account.accountStatus.code IN PENDING_CODES → return { hasPending: true }
```

**Cobertura de garantías:** Si Juan actúa como tutor de María (que tiene deuda), al admitir a Juan también se detecta esa deuda.

---

### 7.4 Resguardo financiero de un ingreso

Vista consultada desde el módulo de Admisión o Caja al seleccionar un ingreso hospitalario:

```
GET /revenue/patient-account/financial-guarantee?admissionRecordId=456
  │
  ├─ admission = admissionRecordRepository.findWithAccountById(456)
  │    └─ Carga: patient, admissionStatus, patientAccount,
  │              patientAccount.paymentAccounts, paymentAccounts.paymentStatus
  │
  ├─ account = admission.patientAccount
  └─ paymentAccounts = account.paymentAccounts.toArray()
```

Muestra una tabla completa de todos los pagos y el saldo pendiente, para que el equipo de Admisión evalúe el estado financiero del ingreso antes de darle alta.

---

### 7.5 Anulación de un pago

```
POST /revenue/cash-register/post-payment/{id}/void
  │
  ├─ Validar CSRF token 'payment_void_{id}'
  ├─ Cargar paymentAccount
  ├─ Guard: si cancellationDate !== null → flash warning, redirect
  ├─ Marcar paymentAccount: cancellationDate, cancelledByMember, cancellationReason
  ├─ Asignar PaymentStatus "Anulado" (si existe en catálogo)
  ├─ Marcar VoucherEntry.isCancelled = true
  ├─ Marcar PaymentAccountDetail[*].isCancelled = true
  └─ em->flush() → redirect a summary con flash success
```

**Importante:** La anulación **no genera DTE de reversa** (nota de crédito). Este proceso queda fuera del alcance del MVP y debe gestionarse manualmente en el SII/ACES si aplica.

---

## 8. Códigos de estado de cuenta

La entidad `AccountStatus` usa el campo `code` (slug) para que la lógica de negocio no dependa de strings legibles del campo `name`. Códigos definidos hasta el momento:

| Código | Estado visible | Implica deuda |
|---|---|---|
| `abierta_pendiente_pago` | Abierta - Pendiente de pago | ✅ Sí |
| `cerrada_pendiente_pago` | Cerrada - Pendiente de pago | ✅ Sí |
| `cerrada_revision_interna` | Cerrada - En revisión interna | ✅ Sí |
| `cerrada_revision_financiador` | Cerrada - En revisión del financiador | ✅ Sí |
| `cerrada_cobranza_interna` | Cerrada - Cobranza interna | ✅ Sí |
| `cerrada_cobranza_judicial` | Cerrada - Cobranza judicial | ✅ Sí |
| `cerrada_pagada_con_saldo_pendiente` | Cerrada - Pagada con saldo pendiente | ✅ Sí |
| `cerrada_pagada` | Cerrada - Pagada completa | ❌ No |
| `cerrada_regularizada` | Cerrada - Regularizada | ❌ No |

---

## 9. Mapeo Legacy → Tenant

| Entidad Legacy | Entidad Tenant | Tabla BD |
|---|---|---|
| `CuentaPaciente` | `PatientAccount` | `patient_account` |
| `PagoCuenta` | `PaymentAccount` | `payment_account` |
| `EstadoCuenta` | `AccountStatus` | `account_status` |
| `AccionClinicaPaciente` | `ClinicalActionPatient` | `clinical_action_patient` |
| `DetallePagoCuenta` | `PaymentAccountDetail` | `payment_account_detail` |
| `ItemFacturacion` | `BillingItem` | `billing_item` |
| `TipoAfectacionTributaria` | `TaxAffectationType` | `tax_affectation_type` |
| `UsuariosRebsol` | `Member` | `member` |
| `Paciente` | `Patient` | `patient` |
| `UbicacionCaja` | `CashRegisterLocation` | `cash_register_location` |
| `CajaAbierta` (sesión) | `CashRegister` | `cash_register` |

### Cambios relevantes en la migración

**Migración `Version20260223023720.php`** agrega a `payment_account`:
```sql
ALTER TABLE payment_account ADD cash_register_id INT DEFAULT NULL;
ALTER TABLE payment_account ADD CONSTRAINT FK_PA_CASH_REGISTER
    FOREIGN KEY (cash_register_id) REFERENCES cash_register (id)
    NOT DEFERRABLE INITIALLY IMMEDIATE;
CREATE INDEX IDX_PA_CASH_REGISTER ON payment_account (cash_register_id);
```

Esta columna vincula cada pago con la **sesión de caja específica** (no solo la ubicación física), necesaria para el campo `nrocaja` en los documentos tributarios DTE.

---

## 10. Relaciones entre entidades (diagrama)

```
AdmissionRecord ──────────── PatientAccount ──┐
  │                               │            │ OneToMany (cascade persist)
  │ FK patientAccount             │ FK patient  │
  │                              Patient       PaymentAccount ──┐
  │                               │                │            │ OneToMany
  │                               Person           ClinicalActionPatient
  │                               │                │
  │                               tutor FK         BillingItem
  │                          (Person, nullable)     │
  │                                                TaxAffectationType
  │
  └─── PatientAccount ─── AccountStatus (FK account_status_id)

PaymentAccount ──── PaymentStatus
       │       ──── CashRegisterLocation
       │       ──── CashRegister (sesión) ← nuevo
       │       ──── Member (createdBy)
       │       ──── Member (cancelledBy)
       │       ──── DifferenceReason
       │
       └── PaymentAccountDetail ──── PaymentMethod
                                ──── BonoWebVoucher (OneToOne, nullable)

CashRegister ─────────────────────── VoucherEntry ──── Voucher (talonario)
```

---

## 11. Rutas HTTP completas

### PatientAccountController

| Método | URL | Nombre de ruta | Descripción |
|---|---|---|---|
| GET | `/revenue/patient-account/summary?patientId={id}` | `app_revenue_patient_account_summary` | Resumen de cuenta del paciente (Turbo Frame) |
| GET | `/revenue/patient-account/financier?patientId={id}` | `app_revenue_patient_account_financier` | Financiador activo del paciente (Turbo Frame) |
| GET | `/revenue/patient-account/financial-guarantee?admissionRecordId={id}` | `app_revenue_patient_account_guarantee` | Resguardo financiero del ingreso (Turbo Frame) |
| GET | `/revenue/patient-account/has-pending-payments?patientId={id}` | `app_revenue_patient_account_pending` | ¿Tiene deuda pendiente? (JSON) |

### PostPaymentController

| Método | URL | Nombre de ruta | Descripción |
|---|---|---|---|
| GET | `/revenue/cash-register/post-payment/{id}` | `app_revenue_cash_register_post_payment_summary` | Resumen post-cobro (Turbo Frame payment-panel) |
| GET | `/revenue/cash-register/post-payment/{id}/history` | `app_revenue_cash_register_post_payment_history` | Historial del paciente (Turbo Frame lazy) |
| GET | `/revenue/cash-register/post-payment/{id}/print` | `app_revenue_cash_register_post_payment_print` | Boleta en PDF inline |
| POST | `/revenue/cash-register/post-payment/{id}/void` | `app_revenue_cash_register_post_payment_void` | Anular pago (redirect) |

---

## 12. Seguridad y validaciones

### CSRF

- **Anulación de pago:** Token `payment_void_{id}` validado en `PostPaymentController::void()`. El token se incluye en el formulario del template `_post_payment.html.twig`.
- **Confirmación de pago:** Token `payment_confirm` validado en `PaymentConfirmController::confirm()`.

### Autenticación como cajero

Todos los endpoints que modifican datos verifican que el usuario sea `Member`:

```php
private function resolveCurrentMember(): Member
{
    $user = $this->getUser();
    if (!$user instanceof Member) {
        throw $this->createAccessDeniedException('Usuario no autenticado como cajero.');
    }
    return $user;
}
```

### Guard de doble anulación

```php
if ($paymentAccount->getCancellationDate() !== null) {
    $this->addFlash('warning', 'Este pago ya fue anulado anteriormente.');
    return $this->redirectToRoute('...summary', ['id' => $id]);
}
```

### Seguridad en `has-pending-payments`

El endpoint JSON retorna `{ hasPending: false }` para `patientId ≤ 0` o paciente inexistente, **sin lanzar excepciones**. Esto evita filtrar la existencia de pacientes por enumeration.

---

## 13. Consideraciones técnicas

### Aritmética monetaria con bcmath

Toda suma de montos usa `bcmath` para evitar errores de punto flotante IEEE 754:

```php
// En PaymentConfirmController::sumServicesTotal()
$total = '0.00';
foreach ($servicesRows as $row) {
    $total = bcadd($total, $this->toDecimal($row['totalAmount'] ?? '0'), 2);
}
```

Los montos se almacenan como `decimal(12,2)` en PostgreSQL y se trabajan como strings en PHP.

### Eager loading vs lazy loading

Los repositorios de Pago Cuenta usan **JOIN explícito** para cargar todas las relaciones necesarias en una sola query, evitando el problema N+1 que sería crítico en la vista de resguardo financiero (donde puede haber muchos `PaymentAccount`).

### Turbo Frame lazy loading

El historial de pagos usa **lazy loading** mediante el atributo `src` del Turbo Frame:

```html
<turbo-frame id="post-payment-history"
             src="{{ path('app_revenue_cash_register_post_payment_history', {id: paymentAccount.id}) }}">
  <div class="text-center py-3">
    <div class="spinner-border spinner-border-sm"></div>
    Cargando historial…
  </div>
</turbo-frame>
```

La pestaña de historial solo se carga cuando el usuario la activa, reduciendo la carga inicial del resumen post-pago.

### Impresión de boleta fuera de Turbo

La ruta `/print` retorna `application/pdf`. Los links hacia esta ruta deben incluir `data-turbo="false"` para que Turbo no intercepte la respuesta y el navegador la maneje nativamnente (abrir PDF inline o descargar).

### Relación PatientAccount es 1:1 por paciente, no por ingreso

A diferencia del sistema legacy donde existía una ambigüedad entre `CuentaPaciente` y `DatoIngreso` (AdmissionRecord), en el sistema Tenant la `PatientAccount` es **una por paciente** (no por ingreso). Los `AdmissionRecord` pueden vincularse a la misma `PatientAccount` a través de la relación `admissionRecord.patientAccount`.

Esto simplifica el flujo de cobro directo (sin ingreso asociado), donde se puede cobrar a cualquier paciente sin necesidad de que tenga un `AdmissionRecord` activo.

### Cache de menú

El menú del sidebar usa caché de 1 hora (`cache.app`). Si se realizan cambios en `MenuDefinition::getDefaultMenuStructure()` (por ejemplo, agregar sub-menús de Caja), se debe limpiar el caché:

```bash
php8.3 bin/console cache:pool:clear cache.app
```
