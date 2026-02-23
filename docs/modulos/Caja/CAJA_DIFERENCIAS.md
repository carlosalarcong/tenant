# CAJA — Sistema de Diferencias (Descuentos con Autorización)

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23

---

## Índice

1. [Propósito y alcance](#1-propósito-y-alcance)
2. [Entidad Difference](#2-entidad-difference)
3. [Catálogos de diferencias](#3-catálogos-de-diferencias)
4. [DifferenceService](#4-differenceservice)
5. [DifferenceController](#5-differencecontroller)
6. [DifferenceAuthorizationController (Supervisor)](#6-differenceauthorizationcontroller-supervisor)
7. [Repositorios](#7-repositorios)
8. [Templates](#8-templates)
9. [Flujo completo de una diferencia](#9-flujo-completo-de-una-diferencia)
10. [Estados y transiciones](#10-estados-y-transiciones)
11. [Polling y auto-aprobación](#11-polling-y-auto-aprobación)
12. [Integración con BonoWebController](#12-integración-con-bonowebcontroller)
13. [Rutas HTTP](#13-rutas-http)

---

## 1. Propósito y alcance

El sistema de **diferencias** permite al cajero solicitar un descuento sobre el monto total de una cuenta, sujeto a autorización de un supervisor. El flujo es:

1. Cajero pulsa "Solicitar descuento" en la pantalla de caja
2. Se crea una `Difference` con estado `solicitada`
3. **Si el tipo lo permite**: se auto-aprueba sin intervención del supervisor
4. **Si requiere autorización**: el cajero espera mientras el supervisor aprueba/rechaza desde el panel de Supervisor
5. Al aprobarse, se emite un evento DOM `difference:approved` que actualiza el total en la UI

El sistema es **asíncrono**: cajero y supervisor trabajan en sesiones distintas, y el polling del cajero consulta periódicamente el estado.

---

## 2. Entidad Difference

**Archivo:** `src/Entity/Tenant/Difference.php`
**Tabla:** `difference`
**Legado:** `diferencia`

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK auto-incremental |
| `requestedByMember` | `requested_by_member_id` | FK → `Member` (nullable) | Cajero que solicitó el descuento |
| `authorizedByMember` | `authorized_by_member_id` | FK → `Member` (nullable) | Supervisor que resolvió (aprobó/rechazó) |
| `cancelledByMember` | `cancelled_by_member_id` | FK → `Member` (nullable) | Miembro que anuló la solicitud |
| `differenceType` | `difference_type_id` | FK → `DifferenceType` (nullable) | Tipo de diferencia |
| `differenceReason` | `difference_reason_id` | FK → `DifferenceReason` (nullable) | Motivo de la diferencia |
| `differenceDirection` | `difference_direction_id` | FK → `DifferenceDirection` (nullable) | Dirección (positiva/negativa) |
| `patientAccount` | `patient_account_id` | FK → `PatientAccount` (nullable) | Cuenta del paciente afectada |
| `requestedAt` | `requested_at` | `datetime` (NOT NULL) | Fecha/hora de la solicitud |
| `totalAccount` | `total_account` | `decimal(12,2)` (NOT NULL) | Monto original de la cuenta |
| `totalDiscount` | `total_discount` | `decimal(12,2)` (NOT NULL) | Monto del descuento solicitado |
| `totalAfterDiscount` | `total_after_discount` | `decimal(12,2)` (NOT NULL) | Monto resultante tras descuento |
| `status` | `status` | `varchar(30)` (default: `solicitada`) | Estado actual (ver tabla abajo) |
| `authorizedAt` | `authorized_at` | `datetime` (nullable) | Fecha de autorización/rechazo |
| `cancelledAt` | `cancelled_at` | `datetime` (nullable) | Fecha de anulación |
| `createdAt` | `created_at` | `datetime` | Creación auto en constructor |
| `updatedAt` | `updated_at` | `datetime` (nullable) | Última actualización |

### Métodos de negocio

```php
public function isPending(): bool
{
    return $this->status === 'solicitada';
}

public function isApproved(): bool
{
    return in_array($this->status, ['autorizada', 'auto_aprobada'], true);
}
```

---

## 3. Catálogos de diferencias

### DifferenceType (Tipo de Diferencia)

**Archivo:** `src/Entity/Tenant/DifferenceType.php`
**Tabla:** `difference_type`
**Legado:** `tipo_diferencia`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | `integer` | PK |
| `name` | `varchar(255)` | Nombre del tipo |
| `description` | `text` (nullable) | Descripción detallada |
| `differenceDirection` | FK → `DifferenceDirection` (nullable) | Dirección asociada |
| `isActive` | `boolean` | Activo/inactivo |
| `idEstado` | `integer` | Campo legado de estado |

**Nota importante:** `idEstado === 2` habilita la **auto-aprobación** de diferencias de este tipo. Es una convención provisional documentada en `DifferenceService::autoApproveIfEligible()`. Reemplazar por un campo dedicado `allowsAutoApprove` cuando se agregue la migración correspondiente.

---

### DifferenceReason (Motivo de Diferencia)

**Archivo:** `src/Entity/Tenant/DifferenceReason.php`
**Tabla:** `difference_reason`
**Legado:** `motivo_diferencia`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | `integer` | PK |
| `name` | `varchar(50)` | Motivo (ej. "Error de precio", "Convenio especial") |
| `differenceDirection` | FK → `DifferenceDirection` (nullable) | Dirección por defecto para este motivo |
| `isActive` | `boolean` | Activo/inactivo |

---

### DifferenceDirection (Dirección de Diferencia)

**Archivo:** `src/Entity/Tenant/DifferenceDirection.php`
**Tabla:** `difference_direction`
**Legado:** `direccion_diferencia`

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | `integer` | PK |
| `name` | `varchar(255)` | Nombre (ej. "A favor del paciente", "A favor de la institución") |
| `isActive` | `boolean` | Activo/inactivo |

Indica el sentido económico del ajuste: si el descuento beneficia al paciente (reduce lo que paga) o es una regularización interna.

---

## 4. DifferenceService

**Archivo:** `src/Service/Revenue/CashRegister/DifferenceService.php`

Gestiona el **ciclo de vida completo** de las diferencias.

### Constructor

```php
public function __construct(
    private readonly TenantEntityManager           $em,
    private readonly DifferenceTypeRepository      $differenceTypeRepository,
    private readonly DifferenceReasonRepository    $differenceReasonRepository,
    private readonly DifferenceDirectionRepository $differenceDirectionRepository,
) {}
```

### Métodos

#### `requestDiscount(Member $requestedBy, array $data, ?PatientAccount $patientAccount): Difference`

Crea una nueva solicitud con estado `solicitada`.

Datos esperados en `$data`:
```php
[
    'difference_type_id'      => int,
    'difference_reason_id'    => int,
    'difference_direction_id' => int,
    'total_account'           => '15000.00',
    'total_discount'          => '2000.00',
    'total_after_discount'    => '13000.00',
]
```

Resuelve FKs opcionales (`DifferenceType`, `DifferenceReason`, `DifferenceDirection`) y persiste. Retorna la `Difference` persistida con ID asignado.

---

#### `approve(Difference $difference, Member $authorizedBy): void`

Aprueba la diferencia.
```php
$difference->setStatus('autorizada');
$difference->setAuthorizedByMember($authorizedBy);
$difference->setAuthorizedAt(new \DateTime());
$this->em->flush();
```

---

#### `reject(Difference $difference, Member $authorizedBy): void`

Rechaza la diferencia.
```php
$difference->setStatus('rechazada');
$difference->setAuthorizedByMember($authorizedBy);
$difference->setAuthorizedAt(new \DateTime());
$this->em->flush();
```

---

#### `cancel(Difference $difference): void`

Anula la solicitud (solo si está pendiente). El `cancelledByMember` queda como el mismo cajero que la solicitó.

```php
$difference->setStatus('anulada');
$difference->setCancelledByMember($difference->getRequestedByMember());
$difference->setCancelledAt(new \DateTime());
```

---

#### `autoApproveIfEligible(Difference $difference): bool`

Verifica si la diferencia puede aprobarse automáticamente.

**Criterio actual:** `DifferenceType.idEstado === 2`

```php
$type = $difference->getDifferenceType();
if ($type === null || $type->getIdEstado() !== 2) {
    return false;
}
$difference->setStatus('auto_aprobada');
$difference->setAuthorizedAt(new \DateTime());
$this->em->flush();
return true;
```

Si retorna `true`, el cajero no necesita esperar al supervisor.

---

## 5. DifferenceController

**Archivo:** `src/Controller/Revenue/CashRegister/DifferenceController.php`
**Prefijo:** `#[Route('/revenue/cash-register/difference', name: 'app_revenue_cash_register_difference_')]`
**Legado:** `DiferenciaController` / `AutorizarDiferenciaAction`

### Constructor

```php
public function __construct(
    private readonly DifferenceService             $differenceService,
    private readonly DifferenceRepository          $differenceRepository,
    private readonly DifferenceTypeRepository      $differenceTypeRepository,
    private readonly DifferenceReasonRepository    $differenceReasonRepository,
    private readonly DifferenceDirectionRepository $differenceDirectionRepository,
    private readonly PatientAccountRepository      $patientAccountRepository,
) {}
```

### Endpoints

#### `GET /form` → `form`

Muestra el formulario inicial de solicitud en `turbo-frame#difference-panel`.

Query params opcionales:
- `total_account` — monto de la cuenta (pre-rellena el campo oculto)
- `patient_account_id` — ID de `PatientAccount` para asociar la diferencia

Renderiza: `revenue/cash-register/difference/_form.html.twig` con:
- `types` — `DifferenceType[]` activos
- `reasons` — `DifferenceReason[]` activos
- `directions` — `DifferenceDirection[]` activos

---

#### `POST /request` → `request`

Crea la solicitud y, si es elegible, la auto-aprueba.

Validación CSRF: `difference_request`.

Flujo:
1. Resuelve `PatientAccount` si `patient_account_id > 0`
2. Llama `DifferenceService::requestDiscount()`
3. Llama `DifferenceService::autoApproveIfEligible()`
4. Genera URLs de `status` y `cancel`
5. Re-renderiza `_form.html.twig` con la `Difference` creada (ahora muestra el panel de estado)

---

#### `GET /{id}/status` → `status`

Polling del estado de la diferencia. Retorna `turbo-frame#difference-status`.

**Cache-Control: no-cache** para evitar respuestas cacheadas durante el polling.

Carga la diferencia con `findWithDetailsById()` (eager loading de relaciones).

Renderiza: `revenue/cash-register/difference/_status.html.twig`

---

#### `POST /{id}/cancel` → `cancel`

Anula la solicitud pendiente.

Validación CSRF: `difference_cancel_{id}`.

Si `isPending()` → llama `DifferenceService::cancel()`.

Re-renderiza `_form.html.twig` con la diferencia actualizada.

---

## 6. DifferenceAuthorizationController (Supervisor)

**Archivo:** `src/Controller/Revenue/Supervisor/DifferenceAuthorizationController.php`

Permite al supervisor ver todas las diferencias pendientes y aprobarlas/rechazarlas.

### Endpoints

| Método | URL | Acción |
|---|---|---|
| GET | `/revenue/supervisor/difference-authorization` | Lista diferencias pendientes |
| POST | `/revenue/supervisor/difference-authorization/{id}/approve` | Aprueba la diferencia |
| POST | `/revenue/supervisor/difference-authorization/{id}/reject` | Rechaza la diferencia |

**Regla anti-autorizacion-propia:** Un supervisor no puede autorizar/rechazar una diferencia que él mismo solicitó:
```php
if ($difference->getRequestedByMember()?->getId() === $supervisor->getId()) {
    throw $this->createAccessDeniedException('No puedes autorizar tu propia diferencia.');
}
```

---

## 7. Repositorios

### DifferenceRepository

**Archivo:** `src/Repository/Tenant/DifferenceRepository.php`

#### `findWithDetailsById(int $id): ?Difference`

Carga la diferencia con eager loading de:
- `requestedByMember`
- `differenceType`
- `differenceReason`
- `differenceDirection`
- `patientAccount`

Usado por el polling (`status`) y el supervisor para ver el detalle completo.

---

### DifferenceReasonRepository / DifferenceTypeRepository / DifferenceDirectionRepository

Cada uno tiene `findAllActive(): array` que retorna los registros con `isActive = true`, ordenados por `name ASC`. Usados para poblar los selects del formulario.

---

## 8. Templates

### `difference/_form.html.twig`

**Turbo Frame:** `difference-panel`

Tiene **dos vistas dentro del mismo template**:

**Vista 1: formulario de solicitud** (cuando `difference` es null):
- Select: Tipo de diferencia, Motivo, Dirección
- Input: Monto descuento (`name="total_discount"`, con `data-action` para calcular el total resultante)
- Input: Total con descuento (`readonly`, calculado)
- Hidden: `total_account`, `patient_account_id`
- Botón: "Solicitar autorización" → POST `/request`

**Vista 2: estado post-solicitud** (cuando `difference` no es null):
- Resumen de montos: Total original / Descuento / Total final
- `turbo-frame#difference-status` (datos de estado, recargado por polling)
- Botón "Anular solicitud" (solo si `isPending()`)

---

### `difference/_status.html.twig`

**Turbo Frame:** `difference-status`

Muestra el estado de la diferencia con atributos `data-*` para que el Stimulus controller controle el polling:

```html
<turbo-frame id="difference-status"
             data-difference-status="{{ difference.status }}"
             data-discount-amount="{{ difference.totalDiscount }}"
             data-total-after-discount="{{ difference.totalAfterDiscount }}">
```

**Casos:**
- `solicitada` → spinner + "Esperando autorización de supervisor..."
- `autorizada` / `auto_aprobada` → alerta verde con montos y nombre del supervisor
- `rechazada` → alerta roja con monto original y nombre del supervisor
- `anulada` → alerta gris "Solicitud anulada"

---

## 9. Flujo completo de una diferencia

```
Cajero: total = $15.000
  │
  ├─ Pulsa "Solicitar descuento"
  │     └─ revenue--cash-register#requestDifference()
  │          └─ turbo-frame#difference-panel.src = /form?total_account=15000
  │
  ├─ Turbo carga el formulario
  │     └─ Cajero ingresa: descuento=$2.000, tipo="Precio", dirección="A favor paciente"
  │
  ├─ Cajero envía el formulario (POST /request)
  │     └─ DifferenceService::requestDiscount() → Difference{status:'solicitada'}
  │          └─ DifferenceService::autoApproveIfEligible()
  │               ├─ [Si tipo.idEstado==2] → status='auto_aprobada'
  │               │       └─ Respuesta: _form.html.twig con diferencia aprobada
  │               │            └─ Stimulus detecta data-difference-status='auto_aprobada'
  │               │                 └─ Emite event: difference:approved { discountAmount:2000, totalAfterDiscount:13000 }
  │               │                      └─ revenue--cash-register#onDifferenceApproved
  │               │                           └─ totalDisplay: "$13.000" + badge "con descuento"
  │               │
  │               └─ [Si requiere supervisor] → status='solicitada'
  │                       └─ Respuesta: _form.html.twig con spinner + polling
  │
  └─ [Mientras spinner] Polling cada 3s: GET /{id}/status
        │
        └─ Supervisor abre panel de diferencias (/supervisor/difference-authorization)
              ├─ Ve diferencia #N pendiente del cajero X
              ├─ POST /{id}/approve → DifferenceService::approve()
              │       └─ status='autorizada'
              └─ En el próximo polling del cajero:
                    └─ _status.html.twig con data-difference-status='autorizada'
                         └─ Stimulus detecta estado final → emite difference:approved
                              └─ revenue--cash-register actualiza total
```

---

## 10. Estados y transiciones

```
                    ┌──────────────────────┐
                    │      solicitada       │
                    └──────┬───────────┬───┘
                           │           │
              [autoApprove]│           │[cajero anula]
                           ▼           ▼
                    ┌──────────┐  ┌──────────┐
                    │auto_apro-│  │ anulada  │ (estado terminal)
                    │  bada    │  └──────────┘
                    └──────────┘
                           │
              [supervisor]  │
                    ┌───────┴──────┐
                    ▼              ▼
             ┌──────────┐  ┌──────────┐
             │autorizada│  │rechazada │ (ambos: estado terminal)
             └──────────┘  └──────────┘
```

**Estados terminales** (no pueden transicionar): `auto_aprobada`, `autorizada`, `rechazada`, `anulada`.

**Estados válidos en BD:**

| Estado | Descripción |
|---|---|
| `solicitada` | Pendiente de autorización del supervisor |
| `autorizada` | Aprobada por supervisor |
| `auto_aprobada` | Aprobada automáticamente sin supervisor |
| `rechazada` | Rechazada por supervisor |
| `anulada` | Cancelada por el cajero antes de resolución |

---

## 11. Polling y auto-aprobación

### Mecanismo de polling

El polling está implementado en el **Stimulus controller** `revenue--difference` (del sub-módulo Supervisor, no documentado en este archivo). El controlador:

1. Al conectar, inicia un `setInterval` de 3 segundos
2. Carga `GET /{id}/status` dentro de `turbo-frame#difference-status`
3. Lee el atributo `data-difference-status` del frame recibido
4. Si el estado es terminal (`autorizada`, `auto_aprobada`, `rechazada`, `anulada`):
   - Limpia el interval
   - Si aprobado: emite `difference:approved` con los montos
   - Si rechazado: muestra el mensaje de rechazo sin emitir el evento

### Auto-aprobación

La auto-aprobación evita la necesidad de supervisor para tipos de diferencia de bajo impacto. El criterio actual (`idEstado === 2`) es provisional y está documentado con un comentario en `DifferenceService`:

```php
/**
 * Convención provisional: DifferenceType.idEstado === 2 habilita la
 * auto-aprobación. Reemplazar por un campo dedicado cuando se agregue la
 * migración correspondiente (DifferenceType.allowsAutoApprove boolean).
 */
```

---

## 12. Integración con BonoWebController

`BonoWebController` también está dentro del módulo Revenue, aunque no forma parte del sistema de diferencias. Es un sub-sistema paralelo para el **bono FONASA electrónico** (BonoWeb/Snabb).

**Archivo:** `src/Controller/Revenue/BonoWeb/BonoWebController.php`
**Prefijo:** `#[Route('/revenue/bonoweb', name: 'app_revenue_bonoweb_')]`

### Endpoints

| Método | URL | Nombre | Descripción |
|---|---|---|---|
| GET | `/revenue/bonoweb/panel?paymentAccountId={id}` | `panel` | Carga el panel BonoWeb en `turbo-frame#bonoweb-panel` |
| POST | `/revenue/bonoweb/generate` | `generate` | Crea voucher en Snabb via `BonoWebService::createAndPersist()` |
| GET | `/revenue/bonoweb/{voucherId}/status` | `status` | Polling del estado (Cache-Control: no-cache) |
| POST | `/revenue/bonoweb/{voucherId}/confirm` | `confirm` | Cajero confirma pago del copago → `confirmDone()` |
| POST | `/revenue/bonoweb/webhook` | `webhook` | Callback de Snabb → `syncStatus()` |

### Flujo BonoWeb

```
GET /panel?paymentAccountId=123
  └─ Muestra prestaciones del PaymentAccount para seleccionar en el bono

POST /generate
  ├─ Extrae prestaciones del POST (array `prestaciones[]`)
  ├─ Resuelve CashRegisterLocation (para codigoSucursal)
  ├─ Resuelve practitioner (Member autenticado)
  └─ BonoWebService::createAndPersist() → BonoWebVoucher{status:'Created'}
       └─ Responde con _panel.html.twig + voucher + statusUrl para polling

GET /{voucherId}/status (polling cada N segundos)
  └─ BonoWebService::syncStatus() → consulta Snabb → actualiza BD
       └─ Renderiza _status.html.twig (sin cache)
            └─ Si status='Paid' → muestra botón "Confirmar pago del copago"

POST /{voucherId}/confirm
  └─ BonoWebService::confirmDone() → marca Done en Snabb
       └─ Renderiza _confirmed.html.twig

POST /webhook (callback Snabb, sin sesión ni CSRF)
  └─ Sync estado en BD → responde { "ok": true } siempre
```

---

### RevenuePaymentFragmentController

**Archivo:** `src/Controller/Revenue/Payment/RevenuePaymentFragmentController.php`
**Prefijo:** `#[Route('/revenue/payment', name: 'app_revenue_payment_')]`

Controlador auxiliar que carga dinámicamente **una fila** de un método de pago específico.

#### `GET /revenue/payment/row/{methodCode}?index={n}` → `row`

Renderiza el partial Twig del método de pago indicado por `methodCode`:

```php
$config = $this->configRegistry->get($methodCode);
$form   = $this->formFactory->createNamed(
    sprintf('payment_batch_rows_%s_%d', $methodCode, $index),
    $config['form_type'],
    null,
    ['csrf_protection' => false]
);
return $this->render($config['row_template'], [
    'row_form'       => $form->createView(),
    'method_code'    => $methodCode,
    'index'          => $index,
    'row_name_prefix'=> sprintf('payment_batch[rows][%s][%d]', $methodCode, $index),
]);
```

Usado cuando se agrega una fila adicional de un mismo método (si `max_rows > 1`).

---

## 13. Rutas HTTP

### DifferenceController

| Método | URL | Nombre | Descripción |
|---|---|---|---|
| GET | `/revenue/cash-register/difference/form` | `app_revenue_cash_register_difference_form` | Formulario de solicitud |
| POST | `/revenue/cash-register/difference/request` | `app_revenue_cash_register_difference_request` | Crear solicitud |
| GET | `/revenue/cash-register/difference/{id}/status` | `app_revenue_cash_register_difference_status` | Polling de estado |
| POST | `/revenue/cash-register/difference/{id}/cancel` | `app_revenue_cash_register_difference_cancel` | Anular solicitud |

### BonoWebController

| Método | URL | Nombre | Descripción |
|---|---|---|---|
| GET | `/revenue/bonoweb/panel` | `app_revenue_bonoweb_panel` | Panel BonoWeb |
| POST | `/revenue/bonoweb/generate` | `app_revenue_bonoweb_generate` | Crear voucher |
| GET | `/revenue/bonoweb/{voucherId}/status` | `app_revenue_bonoweb_status` | Polling status |
| POST | `/revenue/bonoweb/{voucherId}/confirm` | `app_revenue_bonoweb_confirm` | Confirmar pago |
| POST | `/revenue/bonoweb/webhook` | `app_revenue_bonoweb_webhook` | Callback Snabb |

### RevenuePaymentFragmentController

| Método | URL | Nombre | Descripción |
|---|---|---|---|
| GET | `/revenue/payment/row/{methodCode}` | `app_revenue_payment_row` | Fila dinámica de método |
