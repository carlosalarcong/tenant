# CAJA — Catálogos y Mantenedores

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23

---

## Índice

1. [Visión general](#1-visión-general)
2. [PaymentMethod (Medio de Pago)](#2-paymentmethod-medio-de-pago)
3. [PaymentMethodType (Tipo de Medio de Pago)](#3-paymentmethodtype-tipo-de-medio-de-pago)
4. [PaymentStatus (Estado de Pago)](#4-paymentstatus-estado-de-pago)
5. [AccountStatus (Estado de Cuenta)](#5-accountstatus-estado-de-cuenta)
6. [AccountType (Tipo de Cuenta)](#6-accounttype-tipo-de-cuenta)
7. [BillingItem (Ítem de Facturación)](#7-billingitem-ítem-de-facturación)
8. [BillingPaymentMethod (Medio de Pago Facturación)](#8-billingpaymentmethod-medio-de-pago-facturación)
9. [PaymentCondition (Condición de Pago)](#9-paymentcondition-condición-de-pago)
10. [BankAccount (Cuenta Bancaria)](#10-bankaccount-cuenta-bancaria)
11. [BankAccountType (Tipo de Cuenta Bancaria)](#11-bankaccounttype-tipo-de-cuenta-bancaria)
12. [CashRegisterLocation (Ubicación de Caja)](#12-cashregisterlocation-ubicación-de-caja)
13. [CashierAssignment (Asignación de Cajero)](#13-cashierassignment-asignación-de-cajero)
14. [Voucher (Talonario)](#14-voucher-talonario)
15. [VoucherEntry (Folio Emitido)](#15-voucherentry-folio-emitido)
16. [TaxAffectationType (Tipo de Afectación Tributaria)](#16-taxaffectationtype-tipo-de-afectación-tributaria)
17. [Tabla resumen de catálogos](#17-tabla-resumen-de-catálogos)

---

## 1. Visión general

El módulo de Caja depende de numerosos catálogos configurables que controlan el comportamiento del sistema sin necesidad de cambiar código. Estos catálogos son:

- **Operativos**: qué métodos de pago están disponibles, cómo se clasifican, qué campos requieren
- **Financieros**: condiciones de pago para cheques, tipos de cuentas bancarias
- **Tributarios**: clasificación de ítems para emisión de DTEs (afectos vs. exentos)
- **Infraestructura**: ubicaciones físicas de caja, asignaciones de cajeros, talonarios de folios
- **Estados**: estados de cuentas y pagos que controlan el ciclo de vida financiero

---

## 2. PaymentMethod (Medio de Pago)

**Archivo:** `src/Entity/Tenant/PaymentMethod.php`
**Tabla:** `payment_method`
**Legado:** `medio_pago`
**Repository:** `PaymentMethodRepository`

El catálogo más importante del módulo. Soporta estructura **padre-hijo** para agrupar métodos similares.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `code` | `code` | `varchar(10)` (nullable) | Código técnico (ej. `cash`, `credit_card`, `bank_transfer`, `bonoweb`) |
| `name` | `name` | `varchar(255)` | Nombre visible al cajero |
| `parent` | `parent_id` | FK → `PaymentMethod` (self-referencing, nullable) | Método padre |
| `children` | *(inverso)* | `Collection<PaymentMethod>` | Métodos hijos |
| `paymentMethodType` | `payment_method_type_id` | FK → `PaymentMethodType` (nullable) | Tipo clasificador |
| `issuesReceipt` | `issues_receipt` | `boolean` (default: false) | ¿Genera boleta? |
| `isGuarantee` | `is_guarantee` | `boolean` (default: false) | ¿Puede usarse como resguardo financiero? |
| `isProfessionalPayment` | `is_professional_payment` | `boolean` (default: false) | ¿Es pago profesional? |
| `isWebPayment` | `is_web_payment` | `boolean` (default: false) | ¿Es pago online? |
| `documentTypeCode` | `document_type_code` | `varchar(10)` (nullable) | Código de tipo de documento |
| `accountingCode` | `accounting_code` | `varchar(50)` (nullable) | Código contable |
| `visibleInCashRegister` | `visible_in_cash_register` | `boolean` (default: true) | ¿Aparece en el formulario de caja? |
| `creditCardPayment` | `credit_card_payment` | `boolean` (default: false) | ¿Requiere datos de tarjeta? |
| `isActive` | `is_active` | `boolean` (default: true) | Activo/inactivo |

### Códigos técnicos usados en el sistema

| Código | Handler | Template de fila |
|---|---|---|
| `cash` | `CashPaymentHandler` | `_cash_row.html.twig` |
| `credit_card` | `CreditCardPaymentHandler` | `_credit_card_row.html.twig` |
| `debit_card` | `DebitCardPaymentHandler` | `_debit_card_row.html.twig` |
| `bank_transfer` | `BankTransferPaymentHandler` | `_bank_transfer_row.html.twig` |
| `check` | `CheckPaymentHandler` | `_check_row.html.twig` |
| `electronic_voucher` | `ElectronicVoucherPaymentHandler` | `_electronic_voucher_row.html.twig` |
| `manual_voucher` | `ManualVoucherPaymentHandler` | `_manual_voucher_row.html.twig` |
| `bonoweb` | `BonoWebPaymentHandler` | `_bonoweb_row.html.twig` |
| `gratuity` | `GratuityPaymentHandler` | `_gratuity_row.html.twig` |

### Métodos del repositorio

#### `findAllActive(): array`
Todos los métodos activos ordenados por `name ASC`.

#### `findByCode(string $code): ?PaymentMethod`
Busca por código técnico. Usado por `PaymentConfirmController` para resolver el `PaymentMethod` al persistir `PaymentAccountDetail`.

#### `findParentMethods(): array`
Métodos raíz (sin padre) activos. Para la UI del panel de pagos.

#### `findForAdmissionFinancialSafeguard(): array`
Métodos válidos para resguardo financiero en Admisión. Criterios:
- `isActive = true`
- `visibleInCashRegister = true`
- `isGuarantee = true`
- `paymentMethodType.id != 3` (excluye tipo 3, presumiblemente "Gratuidad")

Retorna `array<{id, name, paymentMethodTypeId}>`.

---

## 3. PaymentMethodType (Tipo de Medio de Pago)

**Archivo:** `src/Entity/Tenant/PaymentMethodType.php`
**Tabla:** `payment_method_type`
**Legado:** `tipo_medio_pago`

Clasificador de métodos de pago para reportes y lógica de negocio.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(255)` | Nombre del tipo (ej. "Efectivo", "Electrónico", "Gratuidad") |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

**Nota:** El ID 3 está excluido de los resguardos financieros (ver `findForAdmissionFinancialSafeguard`).

---

## 4. PaymentStatus (Estado de Pago)

**Archivo:** `src/Entity/Tenant/PaymentStatus.php`
**Tabla:** `payment_status`
**Legado:** `estado_pago`

Catálogo de estados de un `PaymentAccount` individual.

### Campos

| Campo PHP | Columna BD | Tipo |
|---|---|---|
| `id` | `id` | `integer` |
| `name` | `name` | `varchar(255)` |

### Valores conocidos usados en el sistema

| Nombre | Uso |
|---|---|
| `Pendiente` | Asignado al crear `PaymentAccount` en `PaymentConfirmController` |
| `Anulado` | Asignado al anular en `PostPaymentController::void()` |
| `Regularizado` | Asignado por el Supervisor al regularizar (pendiente de implementar) |

---

## 5. AccountStatus (Estado de Cuenta)

**Archivo:** `src/Entity/Tenant/AccountStatus.php`
**Tabla:** `account_status`
**Legado:** `estado_cuenta`

Estados de la cuenta maestra del paciente (`PatientAccount`). Tiene campo `code` para lógica de negocio sin depender del nombre legible.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(60)` | Nombre legible (ej. "Abierta pendiente de pago") |
| `code` | `code` | `varchar(60)` (default: '') | Slug de negocio |

### Migración del campo `code`

El campo `code` fue agregado en `Version20260222050302.php` y populado con 12 slugs en `Version20260222050303.php`.

Ver tabla completa de códigos en `CAJA_PAGOCUENTA.md §8`.

---

## 6. AccountType (Tipo de Cuenta)

**Archivo:** `src/Entity/Tenant/AccountType.php`
**Tabla:** `account_type`
**Legado:** `tipo_cuenta`

Clasificador del tipo de cuenta del ingreso (hospitalización, urgencia, ambulatorio, etc.).

### Campos

| Campo PHP | Columna BD | Tipo |
|---|---|---|
| `id` | `id` | `integer` |
| `name` | `name` | `varchar(255)` |
| `isActive` | `is_active` | `boolean` |

Referenciado desde `AdmissionRecord.accountType`.

---

## 7. BillingItem (Ítem de Facturación)

**Archivo:** `src/Entity/Tenant/BillingItem.php`
**Tabla:** `billing_item`
**Legado:** `item_facturacion`
**Repository:** `BillingItemRepository`

Catálogo de prestaciones/servicios que el cajero puede agregar al cobro.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(255)` | Nombre de la prestación (ej. "Consulta médica", "Hospitalización") |
| `taxAffectationType` | `tax_affectation_type_id` | FK → `TaxAffectationType` (nullable) | Tipo de afectación tributaria para DTE |
| `isActive` | `is_active` | `boolean` (default: true) | Disponible para cobro |
| `idEstado` | `id_estado` | `integer` (default: 1) | Campo legado de estado |

### Relación con DTE

`taxAffectationType` determina el tipo de boleta DTE:
- Si `taxAffectationType.name` contiene "afect" (case-insensitive) → `tipodte=39` (afecta con IVA 19%)
- Si es null o no contiene "afect" → `tipodte=41` (exenta, sin IVA)

Esta lógica está en `DteService::emitirBoletas()`.

---

## 8. BillingPaymentMethod (Medio de Pago Facturación)

**Archivo:** `src/Entity/Tenant/BillingPaymentMethod.php`
**Tabla:** `billing_payment_method`
**Legado:** `medio_pago_facturacion`

Catálogo de medios de pago para el sistema de **facturación** (distinto de los `PaymentMethod` usados en caja). Usa un código de 3 caracteres y distingue si es equivalente a efectivo.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `code` | `code` | `varchar(3)` | Código corto (ej. "EFE", "TAR", "CHQ") |
| `name` | `name` | `varchar(100)` | Nombre del medio |
| `isCash` | `is_cash` | `boolean` | ¿Equivale a efectivo para efectos contables? |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

**Propósito:** Se usa para la integración con sistemas de facturación/contabilidad externos que no usan los mismos códigos que la caja.

---

## 9. PaymentCondition (Condición de Pago)

**Archivo:** `src/Entity/Tenant/PaymentCondition.php`
**Tabla:** `payment_condition`
**Legado:** `condicion_pago`

Condiciones aplicables a **cheques**: si es al día, a fecha, con plazo máximo de días.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(255)` | Nombre de la condición (ej. "Al día", "A 30 días") |
| `interfaceCode` | `interface_code` | `varchar(10)` | Código para interfaz/integración |
| `maxTerm` | `max_term` | `integer` (default: 0) | Plazo máximo en días |
| `isUpToDate` | `is_up_to_date` | `boolean` | ¿Es condición "al día"? |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

Usada en el template `_check_row.html.twig` como select `condition_id`.

---

## 10. BankAccount (Cuenta Bancaria)

**Archivo:** `src/Entity/Tenant/BankAccount.php`
**Tabla:** `bank_account`
**Legado:** `cuenta_bancaria`

Cuentas bancarias registradas, usadas para transferencias bancarias.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `accountNumber` | `account_number` | `varchar(255)` | Número de cuenta |
| `bank` | `bank_id` | FK → `Bank` | Banco de la cuenta |
| `bankAccountType` | `bank_account_type_id` | FK → `BankAccountType` (nullable) | Tipo de cuenta bancaria |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

La entidad `Bank` (no listada aquí) es el catálogo de bancos del sistema.

---

## 11. BankAccountType (Tipo de Cuenta Bancaria)

**Archivo:** `src/Entity/Tenant/BankAccountType.php`
**Tabla:** `bank_account_type`
**Legado:** `tipo_cuenta_bancaria`

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(255)` | Nombre (ej. "Cuenta corriente", "Cuenta vista", "Cuenta de ahorro") |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

---

## 12. CashRegisterLocation (Ubicación de Caja)

**Archivo:** `src/Entity/Tenant/CashRegisterLocation.php`
**Tabla:** `cash_register_location`
**Legado:** `caja` (UbicacionCaja)
**Repository:** `CashRegisterLocationRepository`

Representa un **punto de cobro físico** dentro de una sucursal. Es la entidad estable que no cambia cuando el cajero abre/cierra su sesión.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(50)` | Nombre (ej. "Caja 1", "Caja Urgencia") |
| `description` | `description` | `text` (nullable) | Descripción |
| `branch` | `branch_id` | FK → `Branch` (nullable) | Sucursal a la que pertenece |
| `isActive` | `is_active` | `boolean` (default: true) | Activo/inactivo |
| `createdAt` / `updatedAt` | — | `datetime` | Auto via `@PrePersist` / `@PreUpdate` |

### Relación con Voucher

Cada `CashRegisterLocation` tiene uno o más `Voucher` (talonarios). Para procesar pagos, debe existir un `Voucher` activo con folios disponibles.

### Relación con CashRegister

Una `CashRegisterLocation` puede tener múltiples `CashRegister` a lo largo del tiempo (uno por sesión de cajero). Solo puede haber un `CashRegister` abierto por cajero en un momento dado.

### Repositorio

#### `findAllActive(): array`
Retorna todas las ubicaciones activas ordenadas por `name ASC`. Usada en el formulario de apertura de caja.

---

## 13. CashierAssignment (Asignación de Cajero)

**Archivo:** `src/Entity/Tenant/CashierAssignment.php`
**Tabla:** `cashier_assignment`
**Legado:** `rel_ubicacion_cajero`

Vincula un cajero (`Member`) con las ubicaciones de caja que puede operar.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `member` | `member_id` | FK → `Member` (NOT NULL) | Cajero |
| `cashRegisterLocation` | `cash_register_location_id` | FK → `CashRegisterLocation` (NOT NULL) | Ubicación asignada |
| `isActive` | `is_active` | `boolean` (default: true) | Asignación activa |
| `createdAt` / `updatedAt` | — | `datetime` | Auditoría |

### Uso en el flujo

Al abrir caja, se verifica que el `Member` tenga una `CashierAssignment` activa para la `CashRegisterLocation` seleccionada. Sin asignación, no puede abrir caja.

**Gestión:** A través del `VoucherManagementController` en el sub-módulo Supervisor (ver `CAJA_SUPERVISOR.md`).

---

## 14. Voucher (Talonario)

**Archivo:** `src/Entity/Tenant/Voucher.php`
**Tabla:** `voucher`
**Legado:** `talonario`
**Repository:** `VoucherRepository`

Stock de folios/boletas asignado a una ubicación de caja. Define el rango de folios disponibles.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `cashRegisterLocation` | `cash_register_location_id` | FK → `CashRegisterLocation` (NOT NULL) | Ubicación a la que pertenece |
| `subCompany` | `sub_company_id` | FK → `SubCompany` (nullable) | Sub-empresa emisora |
| `folioFrom` | `folio_from` | `integer` | Folio inicial del rango |
| `folioTo` | `folio_to` | `integer` | Folio final del rango |
| `currentFolio` | `current_folio` | `integer` | Próximo folio a usar |
| `isActive` | `is_active` | `boolean` (default: true) | Talonario activo |
| `createdAt` / `updatedAt` | — | `datetime` | Auditoría |

### Método de negocio

```php
public function hasAvailableFolios(): bool
{
    return $this->isActive && $this->currentFolio <= $this->folioTo;
}
```

### Repositorio — `findActiveByLocation()`

```php
public function findActiveByLocation(CashRegisterLocation $location): ?Voucher
```

Usa `PESSIMISTIC_WRITE` (lock exclusivo en BD) para evitar que dos transacciones concurrentes consuman el mismo folio.

**DEBE llamarse dentro de una transacción activa** (iniciada por `VoucherService` que a su vez es llamada desde `PaymentConfirmController` dentro de su `em->beginTransaction()`).

---

## 15. VoucherEntry (Folio Emitido)

**Archivo:** `src/Entity/Tenant/VoucherEntry.php`
**Tabla:** `voucher_entry`
**Legado:** `detalle_talonario`
**Repository:** `VoucherEntryRepository`

Registro de consumo de un folio: un `VoucherEntry` por cada `PaymentAccount` confirmado.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `voucher` | `voucher_id` | FK → `Voucher` (NOT NULL) | Talonario del que se consumió el folio |
| `paymentAccount` | `payment_account_id` | FK → `PaymentAccount` (NOT NULL) | Pago al que se emitió el folio |
| `member` | `member_id` | FK → `Member` (nullable) | Cajero que emitió el documento |
| `folioNumber` | `folio_number` | `integer` | Número de folio asignado |
| `issuedAt` | `issued_at` | `datetime` | Fecha y hora de emisión |
| `isCancelled` | `is_cancelled` | `boolean` (default: false) | Marcado al anular el pago |

### Repositorio — `findOneByPaymentAccount()`

```php
public function findOneByPaymentAccount(PaymentAccount $paymentAccount): ?VoucherEntry
```

Retorna el `VoucherEntry` activo (no anulado) del pago, con `Voucher` cargado. Usado en `PostPaymentController::summary()` para mostrar el folio en el resumen.

---

## 16. TaxAffectationType (Tipo de Afectación Tributaria)

**Archivo:** `src/Entity/Tenant/TaxAffectationType.php`
**Tabla:** `tax_affectation_type`

Catálogo de tipos de afectación tributaria para la emisión de DTEs.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(255)` | Nombre (ej. "Afecto IVA", "Exento IVA") |
| `isActive` | `is_active` | `boolean` | Activo/inactivo |

### Lógica de detección en DteService

```php
// En DteService::emitirBoletas()
$taxType   = $item->getTaxAffectationType();
$isAfecto  = $taxType !== null &&
             str_contains(strtolower($taxType->getName()), 'afect');
$tipodte   = $isAfecto ? '39' : '41';
```

| tipodte | Tipo de DTE | Cálculo IVA |
|---|---|---|
| `39` | Boleta afecta | `mntneto = round(total/1.19)`, `iva = total - mntneto` |
| `41` | Boleta exenta | `mntneto = total`, `iva = 0` |

### FK en billing_item

La FK `billing_item.tax_affectation_type_id` fue agregada en `Version20260223023720.php`:
```sql
ALTER TABLE billing_item ADD tax_affectation_type_id INT DEFAULT NULL;
ALTER TABLE billing_item ADD CONSTRAINT FK_60691BD9588DA015
    FOREIGN KEY (tax_affectation_type_id) REFERENCES "tax_affectation_type" (id);
```

---

## 17. Tabla resumen de catálogos

| Entidad | Tabla BD | Legado | Modificable por | Usado en |
|---|---|---|---|---|
| `PaymentMethod` | `payment_method` | `medio_pago` | Admin | Formulario de cobro, resguardo financiero, DTE |
| `PaymentMethodType` | `payment_method_type` | `tipo_medio_pago` | Admin | Clasificación de métodos |
| `PaymentStatus` | `payment_status` | `estado_pago` | Sistema | Estado de PaymentAccount |
| `AccountStatus` | `account_status` | `estado_cuenta` | Sistema | Estado de PatientAccount |
| `AccountType` | `account_type` | `tipo_cuenta` | Admin | AdmissionRecord |
| `BillingItem` | `billing_item` | `item_facturacion` | Admin | Panel de prestaciones, DTE |
| `BillingPaymentMethod` | `billing_payment_method` | `medio_pago_facturacion` | Admin | Integración contable |
| `PaymentCondition` | `payment_condition` | `condicion_pago` | Admin | Formulario de cheque |
| `BankAccount` | `bank_account` | `cuenta_bancaria` | Admin | Transferencias bancarias |
| `BankAccountType` | `bank_account_type` | `tipo_cuenta_bancaria` | Admin | Tipos de cuenta |
| `CashRegisterLocation` | `cash_register_location` | `caja` | Admin | Apertura, cobro, DTE (nrocaja) |
| `CashierAssignment` | `cashier_assignment` | `rel_ubicacion_cajero` | Supervisor | Control de acceso a cajas |
| `Voucher` | `voucher` | `talonario` | Supervisor | Emisión de folios |
| `VoucherEntry` | `voucher_entry` | `detalle_talonario` | Sistema | Auditoría de folios |
| `TaxAffectationType` | `tax_affectation_type` | `tipo_afectacion_tributaria` | Admin | DTE (tipodte 39 vs 41) |
| `DifferenceType` | `difference_type` | `tipo_diferencia` | Admin | Formulario de descuento |
| `DifferenceReason` | `difference_reason` | `motivo_diferencia` | Admin | Formulario de descuento |
| `DifferenceDirection` | `difference_direction` | `direccion_diferencia` | Admin | Formulario de descuento |
