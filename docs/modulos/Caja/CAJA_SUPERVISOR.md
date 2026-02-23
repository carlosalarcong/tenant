# Módulo Caja — Submódulo Supervisor

**Rama:** `feature/caja`
**Namespace base:** `App\Controller\Revenue\Supervisor\`, `App\Controller\Maintainers\Treasury\`
**Ruta HTTP base:** `/revenue/supervisor/`, `/maintainers/treasury/`
**Fecha de implementación:** Febrero 2026

---

## Índice

1. [Visión General](#1-visión-general)
2. [Responsabilidades del Supervisor](#2-responsabilidades-del-supervisor)
3. [Componente 1 — Autorización de Diferencias](#3-componente-1--autorización-de-diferencias)
4. [Componente 2 — Gestión de Folios](#4-componente-2--gestión-de-folios)
5. [Componente 3 — Reintento de DTE Fallidos](#5-componente-3--reintento-de-dte-fallidos)
6. [Componente 4 — Mantenedor de Talonarios](#6-componente-4--mantenedor-de-talonarios)
7. [Componente 5 — Mantenedor de Asignación de Cajeros](#7-componente-5--mantenedor-de-asignación-de-cajeros)
8. [Entidades Relacionadas](#8-entidades-relacionadas)
9. [Templates](#9-templates)
10. [Flujo Completo de Autorización de Diferencias](#10-flujo-completo-de-autorización-de-diferencias)
11. [Seguridad y Control de Acceso](#11-seguridad-y-control-de-acceso)
12. [Pendientes y Limitaciones Conocidas](#12-pendientes-y-limitaciones-conocidas)

---

## 1. Visión General

El submódulo **Supervisor** agrupa todas las funciones que requieren privilegios elevados dentro del módulo de Caja. Mientras el submódulo Recaudación es operado por cajeros, Supervisor es usado por jefes de caja, administradores de tesorería y auditores.

**Equivalente legacy:** `RecaudacionBundle/_Default/Supervisor/` (38 controllers en Symfony 3). Esta implementación cubre los flujos activos del MVP; los informes complejos (ConsolidadoCaja, AsientoContable, ReporteProduccion) quedan como Fase 2.

**Funciones implementadas en MVP:**

| Función | Implementación | Estado |
|---|---|---|
| Autorización de descuentos | `DifferenceAuthorizationController` | ✅ Implementado |
| Gestión de folios (VoucherEntry) | `VoucherManagementController` | ✅ Implementado |
| Reintento de DTE fallidos | `DteController` | ✅ Implementado |
| Mantenedor de Talonarios | `VoucherController` | ✅ Implementado |
| Mantenedor de Asignación de Cajeros | `CashierAssignmentController` | ✅ Implementado |
| UbicacionCaja mantenedor | `CashRegisterLocationController` | ✅ Pre-existente |
| ConsolidadoCaja (informe de cierre) | — | ❌ Fase 2 |
| AsientoContable | — | ❌ Fase 2 |
| ReporteProduccion | — | ❌ Fase 2 |
| ApoyoFacturacion | — | ❌ Fase 2 |

---

## 2. Responsabilidades del Supervisor

El supervisor de caja tiene tres grandes áreas de responsabilidad:

### A. Control operativo en tiempo real
- **Aprobar o rechazar** solicitudes de descuento generadas por cajeros durante el cobro.
- Esta función es **bloqueante** para el cajero: hasta que el supervisor resuelva, el pago queda en espera.

### B. Auditoría y corrección post-operativa
- **Anular folios** emitidos incorrectamente o asociados a pagos anulados.
- **Reintentar emisión DTE** cuando el webservice Aces falló durante el cobro.

### C. Configuración del entorno de caja
- **Gestionar talonarios** (Voucher): crear rangos de folios para las cajas.
- **Asignar cajeros** a ubicaciones de caja (CashierAssignment).
- Estos son mantenedores CRUD bajo `/maintainers/treasury/`.

---

## 3. Componente 1 — Autorización de Diferencias

### DifferenceAuthorizationController

**Archivo:** `src/Controller/Revenue/Supervisor/DifferenceAuthorizationController.php`
**Ruta base:** `/revenue/supervisor/differences` | `app_revenue_supervisor_differences_`
**Seguridad:** `#[IsGranted('ROLE_CASH_SUPERVISOR')]`

#### Rutas

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/revenue/supervisor/differences` | GET | `index()` | `app_revenue_supervisor_differences_index` |
| `/revenue/supervisor/differences/{id}` | GET | `show(int)` | `app_revenue_supervisor_differences_show` |
| `/revenue/supervisor/differences/{id}/approve` | POST | `approve(int)` | `app_revenue_supervisor_differences_approve` |
| `/revenue/supervisor/differences/{id}/reject` | POST | `reject(int)` | `app_revenue_supervisor_differences_reject` |

---

#### `index(Request $request): Response`

Pantalla principal del supervisor de diferencias.

**Layout:**
- Tab activo: `pending` (pendientes) | `history` (historial)
- La sección de pendientes usa Turbo Frame `pending-differences` para refrescar sin recargar la página.

**Datos cargados:**
- `pendingDifferences`: todas las `Difference` con `status='solicitada'`, ordenadas por `requestedAt ASC` (las más antiguas primero).
- `recentHistory`: últimas 50 `Difference` con `status != 'solicitada'`, ordenadas por `authorizedAt DESC`.

**Comportamiento:**
- El cajero que solicitó el descuento está en polling a `DifferenceController::status()` cada 3 segundos.
- Al aprobar o rechazar desde esta pantalla, el Turbo Frame `pending-differences` se refresca (redirect al index regenera el frame).
- El cajero detecta el cambio en su próximo poll y el Stimulus controller emite `difference:resolved`.

---

#### `show(int $id): Response`

Vista de detalle de una solicitud de diferencia.

**Datos mostrados:**
- Cajero solicitante (nombre, username).
- Fecha/hora de solicitud.
- Tipo de diferencia (catálogo).
- Motivo (catálogo).
- Dirección (cargo/abono).
- PatientAccount vinculada (si aplica).
- Monto total original.
- Monto del descuento solicitado.
- Monto resultante tras el descuento.
- Estado actual.
- Si está resuelta: quién autorizó/rechazó y cuándo.

---

#### `approve(int $id, Request $request): Response`

Aprueba la diferencia.

**Validaciones:**
1. CSRF token `difference_approve_{id}`.
2. La diferencia existe (`findWithDetailsById()`).
3. La diferencia está en estado pendiente (`isPending()`). Si no, retorna error 422.
4. **El supervisor no puede aprobar su propia solicitud** (`authorizedBy.id !== requestedBy.id`). Si coinciden, retorna error 422 con mensaje "No puedes autorizar tu propia solicitud."

**Procesamiento:**
1. `DifferenceService::approve($difference, $supervisor)`.
2. `status='autorizada'`, `authorizedByMember=$supervisor`, `authorizedAt=now()`. Flush.
3. Redirect a `index` → Turbo Frame `pending-differences` se refresca.

**Efecto en el cajero:**
- El siguiente poll de `DifferenceController::status()` retorna status='autorizada'.
- El Stimulus `difference-controller.js` detiene el polling y emite `difference:resolved` con `detail={status:'autorizada', discountAmount: ...}`.
- El orquestador `cash-register-controller.js` escucha el evento y actualiza el total en la UI.

---

#### `reject(int $id, Request $request): Response`

Rechaza la diferencia.

**Validaciones:** Idénticas a `approve()`.

**Procesamiento:**
1. `DifferenceService::reject($difference, $supervisor)`.
2. `status='rechazada'`, `authorizedByMember=$supervisor`, `authorizedAt=now()`. Flush.
3. Redirect a `index`.

**Efecto en el cajero:**
- Stimulus detecta status='rechazada' en próximo poll.
- El cajero ve mensaje "Descuento rechazado por el supervisor."
- El total vuelve al valor original.

---

### DifferenceService (repaso de métodos de Supervisor)

**Archivo:** `src/Service/Revenue/CashRegister/DifferenceService.php`

Los métodos `approve()` y `reject()` son los que utiliza el Supervisor. Ver descripción completa en `CAJA_RECAUDACION.md § 5.3`.

**Resumen:**
```php
// Aprobación
$difference->setStatus('autorizada');
$difference->setAuthorizedByMember($supervisor);
$difference->setAuthorizedAt(new \DateTimeImmutable());
$this->em->flush();

// Rechazo
$difference->setStatus('rechazada');
$difference->setAuthorizedByMember($supervisor);
$difference->setAuthorizedAt(new \DateTimeImmutable());
$this->em->flush();
```

---

### Entidad Difference (ciclo desde perspectiva del Supervisor)

**Archivo:** `src/Entity/Tenant/Difference.php`

Desde la perspectiva del supervisor, los estados relevantes son:

```
ENTRADA AL SUPERVISOR:
  solicitada  (cajero ha enviado la solicitud)

ACCIONES DEL SUPERVISOR:
  solicitada → autorizada     (approve)
  solicitada → rechazada      (reject)

ACCIÓN DEL CAJERO (antes de que el supervisor actúe):
  solicitada → anulada        (cancel — solo el cajero puede hacerlo)

ACCIÓN AUTOMÁTICA (sin supervisor):
  solicitada → auto_aprobada  (si DifferenceType.idEstado === 2)
```

**Campos de auditoría del Supervisor:**
- `authorized_by_member_id` → quién resolvió
- `authorized_at` → cuándo resolvió
- Nota: el campo se llama `authorizedAt` pero también se usa para registrar el rechazo (fecha de resolución, en cualquier dirección).

---

## 4. Componente 2 — Gestión de Folios

### VoucherManagementController

**Archivo:** `src/Controller/Revenue/Supervisor/VoucherManagementController.php`
**Ruta base:** `/revenue/supervisor/vouchers` | `app_revenue_supervisor_vouchers_`
**Seguridad:** `#[IsGranted('ROLE_CASH_SUPERVISOR')]`

#### Rutas

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/revenue/supervisor/vouchers` | GET | `index()` | `app_revenue_supervisor_vouchers_index` |
| `/revenue/supervisor/vouchers/{id}/void` | POST | `void(int)` | `app_revenue_supervisor_vouchers_void` |

---

#### `index(Request $request): Response`

Listado de folios emitidos con filtros.

**Filtros disponibles (query params):**

| Param | Tipo | Descripción |
|---|---|---|
| `folio` | int | Número de folio exacto |
| `status` | string | `all` (default) \| `active` \| `cancelled` |
| `date_from` | date (Y-m-d) | Fecha de inicio del rango |
| `date_to` | date (Y-m-d) | Fecha de fin del rango |

**Datos mostrados por cada VoucherEntry:**
- Número de folio (`folioNumber`).
- Fecha de emisión (`issuedAt`).
- Cajero que emitió (Member).
- Ubicación de caja (CashRegisterLocation).
- Pago vinculado (PaymentAccount ID, monto, paciente).
- Estado: Activo / Anulado.
- Botón "Anular" si no está anulado.

**Paginado:** Máximo 50 registros por página.

**Eager loading:** VoucherEntry → voucher → cashRegisterLocation; VoucherEntry → member; VoucherEntry → paymentAccount → patient.

---

#### `void(int $id, Request $request): Response`

Anula un VoucherEntry (folio).

**Validaciones:**
1. CSRF token `voucher_void_{id}`.
2. El VoucherEntry existe.
3. No está ya anulado (`isCancelled === false`).

**Procesamiento:**
```php
$voucherEntry->setIsCancelled(true);
$this->em->flush();
```

**Consideración de auditoría:**
- La entidad `VoucherEntry` no tiene campos `voidedAt` ni `voidedBy`.
- Pendiente: agregar estos campos en una migración posterior si se requiere trazabilidad completa de anulaciones.

**Efectos:**
- El folio queda inutilizable para cualquier consulta.
- No revierte el `PaymentAccount` asociado (eso es responsabilidad de `PostPaymentController::void()`).
- No regenera el folio en el talonario (el número queda "quemado").

---

### Entidad VoucherEntry (perspectiva Supervisor)

**Archivo:** `src/Entity/Tenant/VoucherEntry.php`

Desde la perspectiva del Supervisor, el campo de interés es:

```
is_cancelled (boolean, default=false)
```

Un folio puede anularse:
1. **Automáticamente** al anular el pago (`PostPaymentController::void()`).
2. **Manualmente** por el supervisor (`VoucherManagementController::void()`).

La diferencia: la anulación del pago anula el folio como efecto colateral. La anulación manual del supervisor es una corrección operativa sin necesariamente anular el pago.

---

## 5. Componente 3 — Reintento de DTE Fallidos

### DteController

**Archivo:** `src/Controller/Revenue/Dte/DteController.php`
**Ruta base:** `/revenue/dte` | `app_revenue_dte_`

Aunque este controller no está bajo `/revenue/supervisor/`, operativamente es una función de supervisión: permite al supervisor reintentar el envío de boletas DTE que fallaron durante el cobro.

#### Ruta

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/revenue/dte/{id}/retry` | POST | `retry(int)` | `app_revenue_dte_retry` |

---

#### `retry(int $id, Request $request): JsonResponse`

Reintenta el envío de un DteDocument con estado `'error'` o `'retry_pending'`.

**Precondición:** El DTE debe tener `retryData` no vacío (se guardan al fallar en `DteService`).

**Validaciones:**
1. CSRF token `dte_retry_{id}`.
2. El DteDocument existe.
3. `isRetryable()` === true (status en ['error', 'retry_pending']).
4. `retryData` no está vacío.

**Procesamiento:**
```php
$retryData = $dte->getRetryData(); // Payload de negocio sin credenciales
$response  = $this->acesClient->send($retryData); // Credenciales se agregan en AcesClient::buildPayload()

$dte->setAcesResponse(json_decode(json_encode($response), true));
$dte->setStatus('sent');
$dte->setSentAt(new \DateTimeImmutable());
$this->em->flush();
```

**Por qué `retryData` no incluye credenciales:**
Las credenciales de Aces (`u`, `p`, `apikey`, `idempresa`) se agregan en `AcesClient::buildPayload()` en cada llamada. Esto permite:
- Cambiar credenciales sin invalidar los `retryData` almacenados.
- Evitar almacenar credenciales sensibles en la base de datos.

**Respuesta JSON:**

| Caso | HTTP | Respuesta |
|---|---|---|
| CSRF inválido | 403 | `{success: false, status: 'error', message: 'Token CSRF inválido.'}` |
| DTE no encontrado | 404 | `{success: false, status: 'error', message: 'Documento DTE no encontrado.'}` |
| Estado no retryable | 422 | `{success: false, status: '{status}', message: 'El documento tiene estado ... y no puede reintentarse.'}` |
| Sin retryData | 422 | `{success: false, status: 'error', message: 'No hay datos de reintento almacenados.'}` |
| Error Aces | 502 | `{success: false, status: 'retry_pending', message: 'Error al reenviar: ...'}` |
| Éxito | 200 | `{success: true, status: 'sent', message: 'Documento DTE reenviado correctamente.'}` |

---

### Entidad DteDocument (perspectiva Supervisor)

**Archivo:** `src/Entity/Tenant/DteDocument.php`

Campos relevantes para el supervisor:

| Campo | Descripción |
|---|---|
| `status` | Estado actual del DTE. Un supervisor monitorea los que tienen `error` o `retry_pending`. |
| `retry_data` | Payload de negocio completo para reenviar. Sin credenciales. |
| `aces_response` | Última respuesta de Aces (JSON completo). Útil para diagnóstico. |
| `sent_at` | Cuándo se envió exitosamente. `null` si nunca llegó a 'sent'. |

**Identificación de DTEs a reintentar:**
```php
// DteDocumentRepository
public function findRetryPending(): array
{
    return $this->createQueryBuilder('d')
        ->where("d.status IN ('error', 'retry_pending')")
        ->orderBy('d.createdAt', 'ASC')
        ->getQuery()
        ->getResult();
}
```

**Ciclo completo de un DTE fallido:**
```
Cobro confirmado
  ↓ DteService::emitirBoletas()
  ↓ AcesClient::send() → lanza AcesException
  ↓ DteDocument creado con status='error', retryData={payload}
  ↓ Pago NO se revierte

Supervisor detecta DTEs fallidos
  ↓ (futuro: listado en panel supervisor)
  ↓ POST /revenue/dte/{id}/retry
  ↓ AcesClient::send(retryData) → éxito
  ↓ DteDocument status='sent'
```

---

## 6. Componente 4 — Mantenedor de Talonarios

### VoucherController

**Archivo:** `src/Controller/Maintainers/Treasury/VoucherController.php`
**Ruta base:** `/maintainers/treasury/voucher` | `app_maintainers_treasury_voucher_`
**Hereda de:** `AbstractMantenedorController`

#### Rutas

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/maintainers/treasury/voucher/` | GET | `index()` | `app_maintainers_treasury_voucher_index` |
| `/maintainers/treasury/voucher/create` | GET/POST | `create()` | `app_maintainers_treasury_voucher_create` |
| `/maintainers/treasury/voucher/{id}/edit` | GET/POST | `edit(int)` | `app_maintainers_treasury_voucher_edit` |
| `/maintainers/treasury/voucher/{id}/deactivate` | POST | `deactivate(int)` | `app_maintainers_treasury_voucher_delete` |
| `/maintainers/treasury/voucher/export` | GET | `export()` | `app_maintainers_treasury_voucher_export` |

---

#### `index(): Response`

Listado de talonarios ordenado por:
1. `cashRegisterLocation.name` (ASC)
2. `folioFrom` (ASC)

**Datos mostrados por fila:**
- Ubicación de caja.
- Sub-empresa (si aplica).
- Rango de folios (`folioFrom` – `folioTo`).
- Folio actual (`currentFolio`).
- Folios disponibles (`folioTo - currentFolio + 1` si activo).
- Estado: Activo / Inactivo.
- Acciones: Editar, Desactivar.

---

#### `create(Request $request): Response`

Crea un nuevo talonario.

**Formulario (`VoucherType`):**
- `cashRegisterLocation` (EntityType) — requerido.
- `subCompany` (EntityType, opcional) — para multi-empresa.
- `folioFrom` (IntegerType) — requerido, debe ser > 0.
- `folioTo` (IntegerType) — requerido, debe ser >= folioFrom.
- `isActive` (CheckboxType) — default true.

**Inicialización:**
- Al crear, `currentFolio = folioFrom` (lógica en el controller o en el preSave).

**Validaciones de negocio:**
- `folioTo >= folioFrom`.
- No puede haber dos talonarios activos para la misma ubicación de caja con rangos de folios superpuestos (pendiente: validación explícita; actualmente implícita por flujo operativo).

---

#### `edit(int $id, Request $request): Response`

Edita un talonario existente.

**Validación crítica:** Si `currentFolio > folioFrom`, significa que ya se consumieron folios de este talonario. En ese caso la edición queda **bloqueada**:
```php
if ($voucher->getCurrentFolio() > $voucher->getFolioFrom()) {
    $this->addFlash('error', 'No se puede editar un talonario con folios ya consumidos.');
    return $this->redirectToRoute('app_maintainers_treasury_voucher_index');
}
```

**Razonamiento:** Editar el rango de un talonario en uso podría generar inconsistencias en los folios ya emitidos.

---

#### `deactivate(int $id): Response`

Desactiva el talonario (`isActive = false`).

**Nota sobre el nombre de ruta:**
La ruta se llama `_delete` por compatibilidad con el sistema de botones del `_table_row.html.twig` (que espera el sufijo `_delete` para el botón de eliminación). Pero la operación es una **desactivación**, no una eliminación física.

**Efecto:**
- `VoucherService::hasAvailableVoucher()` dejará de encontrarlo.
- `VoucherService::consumeNextFolio()` lanzará `RuntimeException` si intenta usarlo.
- Los `VoucherEntry` existentes vinculados al talonario quedan intactos.

---

#### `export(): Response`

Exporta CSV con los siguientes campos:

| Columna | Campo | Descripción |
|---|---|---|
| ID | `id` | Identificador |
| Ubicación | `cashRegisterLocation.name` | Nombre de la caja |
| Sub-empresa | `subCompany.name` | Nombre (o vacío) |
| Folio inicial | `folioFrom` | Primer folio del rango |
| Folio final | `folioTo` | Último folio del rango |
| Folio actual | `currentFolio` | Próximo a usar |
| Activo | `isActive` | Sí / No |
| Creado | `createdAt` | Fecha de creación |

---

### Entidad Voucher (perspectiva Mantenedor)

**Archivo:** `src/Entity/Tenant/Voucher.php`

**Ciclo de vida completo desde el Mantenedor:**

```
1. Supervisor crea talonario (folioFrom=1000, folioTo=1999)
   → currentFolio = 1000
   → isActive = true

2. Sistema consume folios en cada cobro
   → currentFolio sube: 1000 → 1001 → ... → 1999 → 2000

3. Al consumir el último folio (currentFolio = folioTo + 1 = 2000):
   → VoucherService marca isActive = false automáticamente

4. Supervisor puede desactivar manualmente antes de que se agoten
   → isActive = false

5. Talonario agotado o desactivado:
   → validateOperatingStatus() retorna 'no_voucher'
   → Cajero no puede procesar cobros
   → Supervisor debe crear nuevo talonario o activar uno existente
```

**Nota:** No existe funcionalidad de "reactivar" un talonario desactivado manualmente desde la UI. Si se necesita reactivar, se debe hacer directamente en BD o agregar una action `reactivate` al controller.

---

### VoucherType (Form)

**Archivo:** `src/Form/Maintainers/Treasury/VoucherType.php`

| Campo | Tipo Symfony | Opciones | Validación |
|---|---|---|---|
| `cashRegisterLocation` | EntityType | class=CashRegisterLocation, required=true | NotNull |
| `subCompany` | EntityType | class=SubCompany, required=false | — |
| `folioFrom` | IntegerType | required=true | NotBlank, GreaterThan(0) |
| `folioTo` | IntegerType | required=true | NotBlank, GreaterThan(folioFrom) |
| `isActive` | CheckboxType | required=false | — |

---

## 7. Componente 5 — Mantenedor de Asignación de Cajeros

### CashierAssignmentController

**Archivo:** `src/Controller/Maintainers/Treasury/CashierAssignmentController.php`
**Ruta base:** `/maintainers/treasury/cashier-assignment` | `app_maintainers_treasury_cashier_assignment_`
**Hereda de:** `AbstractMantenedorController`

#### Rutas

| Ruta | Método | Action | Nombre |
|---|---|---|---|
| `/maintainers/treasury/cashier-assignment/` | GET | `index()` | `app_maintainers_treasury_cashier_assignment_index` |
| `/maintainers/treasury/cashier-assignment/create` | GET/POST | `create()` | `app_maintainers_treasury_cashier_assignment_create` |
| `/maintainers/treasury/cashier-assignment/{id}/edit` | GET/POST | `edit(int)` | `app_maintainers_treasury_cashier_assignment_edit` |
| `/maintainers/treasury/cashier-assignment/{id}/deactivate` | POST | `deactivate(int)` | `app_maintainers_treasury_cashier_assignment_delete` |
| `/maintainers/treasury/cashier-assignment/export` | GET | `export()` | `app_maintainers_treasury_cashier_assignment_export` |

---

#### `index(): Response`

Listado de asignaciones ordenado por:
1. `member.username` (ASC)
2. `cashRegisterLocation.name` (ASC)

**Datos mostrados:**
- Cajero (username + nombre).
- Ubicación de caja asignada.
- Estado: Activo / Inactivo.
- Fecha de creación.
- Acciones: Editar, Desactivar.

---

#### `create(Request $request): Response`

Crea nueva asignación cajero → ubicación de caja.

**Formulario (`CashierAssignmentType`):**
- `member` (EntityType) — Miembro con rol cajero.
- `cashRegisterLocation` (EntityType) — Ubicación de caja.
- `isActive` (CheckboxType) — Default true.

---

#### `edit(int $id, Request $request): Response`

Edita asignación existente (sin restricciones adicionales).

---

#### `deactivate(int $id): Response`

Desactiva la asignación (`isActive = false`).

**Efecto:**
- El cajero ya no aparece en la lista de cajeros asignados a esa caja.
- No impide que el cajero opere si ya tiene una caja abierta (el cierre sigue siendo posible).

---

#### `export(): Response`

Exporta CSV:

| Columna | Campo |
|---|---|
| ID | `id` |
| Cajero | `member.username` |
| Ubicación | `cashRegisterLocation.name` |
| Activo | `isActive` |
| Creado | `createdAt` |

---

### Entidad CashierAssignment

**Archivo:** `src/Entity/Tenant/CashierAssignment.php`

**Propósito:** Controla qué cajeros pueden operar en qué puntos de cobro.

**Campos:**

| Campo | Tipo | Nullable | Default | Descripción |
|---|---|---|---|---|
| `id` | int (PK) | no | — | Identificador |
| `member_id` | FK → Member | no | — | Cajero |
| `cash_register_location_id` | FK → CashRegisterLocation | no | — | Punto de cobro |
| `is_active` | boolean | no | true | Si la asignación está vigente |
| `created_at` | datetime | no | — | Creación |
| `updated_at` | datetime | sí | null | Modificación |

**Relación con el flujo operativo:**
- `CashierAssignment` define a nivel de configuración qué cajeros tienen acceso a qué cajas.
- `CashRegister` es la instancia operativa (apertura real de una caja en un momento dado).
- Un cajero puede tener asignación a múltiples ubicaciones pero solo puede tener **una caja abierta** a la vez.

---

## 8. Entidades Relacionadas

### Relación entre Supervisor y las entidades del submódulo Recaudación

El supervisor interactúa principalmente con entidades creadas por el cajero durante el flujo de cobro:

```
CashRegister
  ├─ Supervisado por: VoucherManagementController (folios)
  └─ No puede reabrirse desde UI (pendiente: endpoint de reapertura)

Difference (solicitudes de descuento)
  ├─ Creada por: cajero en DifferenceController::request()
  ├─ Resuelta por: supervisor en DifferenceAuthorizationController::approve()|reject()
  └─ Cancelada por: cajero en DifferenceController::cancel()

VoucherEntry (folios emitidos)
  ├─ Creado por: VoucherService::consumeNextFolio() durante cobro
  ├─ Anulado automáticamente: PostPaymentController::void() al anular pago
  └─ Anulado manualmente: VoucherManagementController::void()

DteDocument (DTEs emitidos)
  ├─ Creado por: DteService::emitirBoletas() después de cobro
  ├─ Status 'error': Aces falló — pendiente de reintento
  └─ Reintenido por: DteController::retry()

Voucher (talonarios)
  ├─ Creado y configurado por: VoucherController (mantenedor)
  ├─ Consumido automáticamente: VoucherService::consumeNextFolio()
  └─ Desactivado por: VoucherController::deactivate() o automáticamente al agotar folios
```

---

## 9. Templates

### Árbol de templates

```
templates/revenue/supervisor/
├── difference_authorization/
│   └── index.html.twig           — Panel de diferencias pendientes + historial
└── voucher_management/
    └── index.html.twig           — Listado de VoucherEntry con filtros

templates/maintainers/treasury/
├── voucher/
│   └── index.html.twig           — Mantenedor CRUD de talonarios
└── cashier_assignment/
    └── index.html.twig           — Mantenedor CRUD de asignaciones
```

### `templates/revenue/supervisor/difference_authorization/index.html.twig`

**Estructura:**

```
[Header: "Autorización de Descuentos"]
[Tabs: Pendientes | Historial]

= Tab Pendientes =
<turbo-frame id="pending-differences">
  [Tabla de Difference con status='solicitada']
  [Columnas: ID | Cajero | Fecha | Tipo | Motivo | Total | Descuento | Resultado | Acciones]
  [Botones por fila: Aprobar (form POST /approve) | Rechazar (form POST /reject)]
</turbo-frame>

= Tab Historial =
  [Tabla de las últimas 50 resoluciones]
  [Columnas: ID | Cajero | Fecha Solicitud | Tipo | Estado | Supervisor | Fecha Resolución]
```

**Comportamiento del Turbo Frame `pending-differences`:**
- Cuando el supervisor aprueba/rechaza, se produce un `redirect` al `index`.
- Turbo intercepta el redirect y solo recarga el frame `pending-differences`.
- La fila desaparece de la tabla (ya no es 'solicitada').
- Las otras tabs y la cabecera de la página no se recargan.

---

### `templates/revenue/supervisor/voucher_management/index.html.twig`

**Estructura:**

```
[Header: "Gestión de Folios"]
[Filtros: folio | estado | fecha_desde | fecha_hasta] [Botón Filtrar]

[Tabla de VoucherEntry]
[Columnas: Folio | Fecha Emisión | Cajero | Ubicación | PaymentAccount | Monto | Estado | Anular]

[Paginación (si > 50 registros)]
```

---

### `templates/maintainers/treasury/voucher/index.html.twig`

Usa el patrón estándar `AbstractMantenedorController` (lista + modales CRUD).

**Columnas de tabla:**
- ID, Ubicación de Caja, Sub-empresa, Folio Desde, Folio Hasta, Folio Actual, Disponibles, Activo, Creado, Acciones

---

### `templates/maintainers/treasury/cashier_assignment/index.html.twig`

**Columnas de tabla:**
- ID, Cajero, Username, Ubicación de Caja, Activo, Creado, Acciones

---

## 10. Flujo Completo de Autorización de Diferencias

### Escenario: Cajero solicita 10% de descuento y supervisor lo aprueba

```
┌─────────────────────────────────────────────────────────────────┐
│  CAJERO                          SUPERVISOR                      │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  1. Cajero ve Total: $100.000                                     │
│     Pulsa "Solicitar descuento"                                   │
│     ↓                                                             │
│  2. GET /difference/form?total_account=100000                     │
│     → Frame difference-panel carga formulario                     │
│     ↓                                                             │
│  3. Cajero llena:                                                  │
│     - Tipo: Cortesía (idEstado=3)                                 │
│     - Motivo: Cliente frecuente                                    │
│     - Descuento: $10.000                                          │
│     - Total resultante: $90.000                                   │
│     ↓                                                             │
│  4. POST /difference/request                                       │
│     → DifferenceService::requestDiscount()                        │
│     → Difference creado, status='solicitada'                      │
│     → autoApproveIfEligible() → false (idEstado=3, no es 2)       │
│     → Frame muestra: "Esperando autorización..."                   │
│     → Stimulus inicia polling cada 3s                              │
│                                                                    │
│                   5. Supervisor en /revenue/supervisor/differences │
│                      Ve nueva fila en "Pendientes":               │
│                      [ID] [Cajero X] [ahora] [Cortesía]           │
│                      [Total: $100.000] [Descuento: $10.000]       │
│                      [Resultado: $90.000]                          │
│                      [Aprobar] [Rechazar]                          │
│                      ↓                                             │
│                   6. Supervisor pulsa [Aprobar]                    │
│                      POST /differences/{id}/approve                │
│                      → Validates: supervisor != cajero ✓           │
│                      → DifferenceService::approve()                │
│                      → status='autorizada'                          │
│                      → Redirect → Turbo Frame refresh              │
│                                                                    │
│  7. Polling del cajero (próxima consulta en < 3s)                 │
│     GET /difference/{id}/status                                   │
│     → status='autorizada'                                         │
│     → Frame muestra: "Descuento autorizado ✓"                    │
│     → Stimulus emite difference:resolved                          │
│     ↓                                                             │
│  8. Orquestador actualiza UI:                                     │
│     - Total a cobrar: $90.000 (actualizado)                       │
│     - Badge "con descuento" visible                               │
│     - Cajero puede confirmar el pago                              │
│                                                                   │
└───────────────────────────────────────────────────────────────────┘
```

### Escenario: Auto-aprobación

```
Cajero solicita descuento con DifferenceType.idEstado = 2 (auto-aprobable)
  ↓
DifferenceService::autoApproveIfEligible() → retorna true
  ↓
Difference.status = 'auto_aprobada'
  ↓
Frame muestra inmediatamente: "Descuento auto-aprobado ✓"
  ↓
No se necesita interacción del supervisor
```

---

## 11. Seguridad y Control de Acceso

### Roles requeridos

| Controller | Rol | Descripción |
|---|---|---|
| `DifferenceAuthorizationController` | `ROLE_CASH_SUPERVISOR` | Supervisor puede aprobar/rechazar diferencias |
| `VoucherManagementController` | `ROLE_CASH_SUPERVISOR` | Supervisor puede anular folios |
| `DteController` | Sin rol específico (pendiente) | Reintento DTE — revisar si debe restringirse |
| `VoucherController` | Sin rol específico (pendiente) | Mantenedor — revisar si debe restringirse a admin |
| `CashierAssignmentController` | Sin rol específico (pendiente) | Mantenedor — ídem |

### Validación anti-auto-autorización

En `DifferenceAuthorizationController::approve()` y `reject()`:
```php
if ($difference->getRequestedByMember()->getId() === $supervisor->getId()) {
    // Error: No puedes autorizar tu propia solicitud
    return HTTP 422;
}
```

Esta regla impide que un cajero con rol supervisor apruebe sus propios descuentos, preservando el principio de segregación de funciones (4-eyes principle).

### Protección CSRF

| Formulario | Token ID |
|---|---|
| Aprobar diferencia | `difference_approve_{id}` |
| Rechazar diferencia | `difference_reject_{id}` |
| Anular folio | `voucher_void_{id}` |
| Reintento DTE | `dte_retry_{id}` |

---

## 12. Pendientes y Limitaciones Conocidas

| Ítem | Prioridad | Descripción |
|---|---|---|
| **ConsolidadoCaja** | Alta | Informe del consolidado de caja del día. El más solicitado. Requiere calcular `expectedTotal` real (ver CAJA_RECAUDACION.md § 15). |
| **Panel de DTEs fallidos** | Alta | Actualmente el reintento DTE es por URL directa. Falta un listado visible en la UI del supervisor que muestre todos los `DteDocument` con `status='error'`. |
| **Reapertura de caja** | Media | No hay endpoint para que el supervisor reabra una caja sin cerrar de un cajero. En legacy: `gestionReabrirCaja`. |
| **Roles en mantenedores** | Media | `VoucherController` y `CashierAssignmentController` no tienen `#[IsGranted()]`. Pendiente agregar restricción de rol. |
| **Trazabilidad de anulaciones de folios** | Media | `VoucherEntry` no tiene `voidedAt` ni `voidedBy`. Agregar en migración posterior. |
| **Listado supervisor principal** | Media | No hay dashboard de resumen para el supervisor. Pendiente: página de inicio con accesos rápidos a pendientes, DTEs fallidos, resumen de cajas abiertas. |
| **AsientoContable** | Baja | Informe contable del cierre. Requiere integración con sistema contable. |
| **ReporteProduccion** | Baja | Informe de producción por prestaciones. Requiere agregación de ClinicalActionPatient. |
| **ApoyoFacturacion** | Baja | Consulta de soporte de facturación. |
| **DifferenceType.allows_auto_approve** | Baja | El flag de auto-aprobación usa campo legacy `idEstado === 2`. Pendiente migrar a campo booleano dedicado. |
