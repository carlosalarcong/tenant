# CAJA — Controladores JavaScript (Stimulus)

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23

---

## Índice

1. [Arquitectura Stimulus en el módulo de Caja](#1-arquitectura-stimulus)
2. [revenue--cash-register](#2-revenue--cash-register-cash-register-controllerjs)
3. [revenue--payment-builder](#3-revenue--payment-builder-payment_builder_controllerjs)
4. [revenue--post-payment](#4-revenue--post-payment-post-payment-controllerjs)
5. [Flujo de eventos entre controladores](#5-flujo-de-eventos-entre-controladores)
6. [Convenciones de naming](#6-convenciones-de-naming)

---

## 1. Arquitectura Stimulus

El módulo de caja usa **tres controladores Stimulus** especializados que se comunican mediante eventos DOM personalizados (`CustomEvent` con `bubbles: true`).

```
Árbol DOM (simplificado)
└── [data-controller="revenue--cash-register"]   ← ORQUESTADOR
    ├── turbo-frame#patient-search
    │     └── [data-controller="revenue--patient-search"]  (no cubierto aquí)
    ├── turbo-frame#patient-context
    ├── turbo-frame#services-panel
    │     └── [data-controller="revenue--services-selector"]  (no cubierto aquí)
    ├── turbo-frame#difference-panel
    ├── turbo-frame#payment-panel
    │     └── form#payment-confirm-form
    │           └── [data-controller="revenue--payment-builder"]  ← MEDIOS DE PAGO
    └── turbo-frame#post-payment-{id}
          └── [data-controller="revenue--post-payment"]  ← POST-COBRO
```

### Comunicación entre controladores

| Evento | Emisor | Receptor | Datos |
|---|---|---|---|
| `patient:selected` | `revenue--patient-search` | `revenue--cash-register` | `{ patientId }` |
| `services:total-changed` | `revenue--services-selector` | `revenue--cash-register` | `{ total }` |
| `difference:approved` | `revenue--difference` (Supervisor) | `revenue--cash-register` | `{ discountAmount, totalAfterDiscount }` |

Todos los eventos burbujean (`bubbles: true`) y son capturados en el elemento raíz del orquestador mediante `data-action`.

---

## 2. revenue--cash-register (`cash-register-controller.js`)

**Ruta:** `assets/controllers/revenue/cash-register-controller.js`
**Identificador Stimulus:** `revenue--cash-register`

### Propósito

Orquestador principal del módulo de caja. Coordina la interacción entre los paneles de paciente, prestaciones, diferencias y medios de pago.

### Targets

| Target | Elemento | Descripción |
|---|---|---|
| `servicesPanelWrapper` | `div` que envuelve `turbo-frame#services-panel` | Se muestra al seleccionar paciente |
| `actionsBar` | Barra total + botón descuento | Oculta hasta que `total > 0` |
| `totalDisplay` | `<span>` con monto a cobrar | Actualizado por `_updateTotalDisplay()` |
| `discountBadge` | Badge "con descuento" | Mostrado si hay diferencia aprobada |
| `paymentSection` | Div que envuelve `turbo-frame#payment-panel` | Oculto hasta que `total > 0` |
| `confirmButton` | Botón "Confirmar pago" | Referenciado para validaciones futuras |

### Values

| Value | Tipo | Descripción |
|---|---|---|
| `servicesUrl` | `String` | URL GET del panel de prestaciones (lazy load) |
| `differenceFormUrl` | `String` | URL GET del formulario de diferencia |
| `admissionRecordId` | `Number` | ID admisión si `context = from_admission` |
| `appointmentId` | `Number` | ID cita si `context = from_appointment` |

### Estado interno

```javascript
this._patientId        = null;    // ID del paciente seleccionado
this._patientAccountId = null;    // ID de PatientAccount (leído de DOM)
this._currentTotal     = 0;       // Total de prestaciones
this._discountApplied  = false;   // ¿Hay diferencia aprobada?
this._discountedTotal  = null;    // Total con descuento (si aplica)
```

### Métodos públicos

#### `onPatientSelected(event)`
Disparado por `patient:selected`. Registra el `patientId` y carga el panel de prestaciones via Turbo Frame (lazy: solo si `frame.src` no está seteado).

```javascript
frame.src = this.servicesUrlValue;  // Carga lazy
```

#### `onServicesTotalChanged(event)`
Disparado por `services:total-changed`. Actualiza el total visible y controla la visibilidad de `actionsBar` y `paymentSection`.

Lógica:
- `total > 0` → muestra actionsBar y paymentSection
- `total === 0` → oculta ambos
- Resetea cualquier descuento previo (`_discountApplied = false`)

#### `onDifferenceApproved(event)`
Disparado por `difference:approved`. Actualiza el total visible con `totalAfterDiscount` y muestra el badge "con descuento". Hace scroll suave hacia `paymentSection`.

#### `requestDifference(event)`
Carga el formulario de solicitud de diferencia en `turbo-frame#difference-panel`.

Construye la URL con parámetros:
- `total_account` — total actual (con o sin descuento previo)
- `patient_account_id` — leído de `#ctx-patient-account-id`

```javascript
const url = new URL(this.differenceFormUrlValue, window.location.origin);
url.searchParams.set('total_account', String(Math.round(total)));
frame.src = url.toString();
```

#### `onPaymentFormSubmit(event)`
Intercepta el submit del `form#payment-confirm-form`. Inyecta en el formulario:

| Campo | ID fuente | Descripción |
|---|---|---|
| `patient_id` | `#ctx-patient-id` | ID del paciente (del Turbo Frame patient-context) |
| `services_payload` | `#services-payload-hidden` | JSON de prestaciones (del Turbo Frame services-panel) |

Si `patient_id` está vacío, llama a `event.preventDefault()` y muestra `alert()`.

### Métodos privados

| Método | Descripción |
|---|---|
| `_updateTotalDisplay(amount, withDiscount)` | Actualiza el span del total formateado en pesos chilenos |
| `_syncPanelsVisibility()` | Añade/quita `d-none` según `_currentTotal > 0` |
| `_readPatientAccountId()` | Lee `#ctx-patient-account-id` del DOM |
| `_formatChilean(value)` | `Math.round(value).toLocaleString('es-CL')` |

### Uso en template

```html
<div class="container-fluid"
     data-controller="revenue--cash-register"
     data-revenue--cash-register-services-url-value="{{ servicesUrl }}"
     data-revenue--cash-register-difference-form-url-value="{{ differenceFormUrl }}"
     data-revenue--cash-register-admission-record-id-value="{{ admissionRecordId }}"
     data-revenue--cash-register-appointment-id-value="{{ appointmentId }}"
     data-action="patient:selected->revenue--cash-register#onPatientSelected
                  services:total-changed->revenue--cash-register#onServicesTotalChanged
                  difference:approved->revenue--cash-register#onDifferenceApproved">
```

---

## 3. revenue--payment-builder (`payment_builder_controller.js`)

**Ruta:** `assets/controllers/revenue/payment_builder_controller.js`
**Identificador Stimulus:** `revenue--payment-builder`

### Propósito

Gestiona el panel de **medios de pago** dentro del formulario de cobro. Controla los toggles por método, la habilitación/deshabilitación de campos, el cálculo del total ingresado y la validación del botón "Confirmar pago".

### Targets

| Target | Elemento | Descripción |
|---|---|---|
| `total` | `<span>` con total ingresado | Actualizado por `recalculateTotal()` |
| `statusAlert` | Alerta de estado | Texto y estilo gestionados por Stimulus |
| `statusAlertText` | `<span>` dentro de la alerta | Texto descriptivo del estado |

### Ciclo de vida (`connect`)

Al conectar:
1. Inicializa el estado visual de cada switch (`syncMethodState`)
2. Registra `focusout` delegado para formatear montos al salir de inputs
3. Llama a `recalculateTotal()` y `validateContinueButton()`

### Métodos principales

#### `toggleMethod(event)`
Disparado cuando el usuario activa/desactiva un switch de método de pago.

```javascript
syncMethodState(event.currentTarget, true);  // true = limpiar campos al desactivar
recalculateTotal();
validateContinueButton();
```

#### `recalculateTotal()`
Suma todos los inputs `[data-payment-amount-input]` que no estén deshabilitados. Actualiza el target `total` formateado en pesos chilenos.

#### `validateContinueButton()`
Evalúa si el botón "Confirmar pago" debe estar habilitado:

| Condición | Resultado |
|---|---|
| Sin métodos activos | Permitir (puede cobrar sin resguardo) |
| Métodos activos + todos con monto | Permitir |
| Métodos activos + alguno sin monto | Bloquear, `button.disabled = true` |

El botón se busca con `[data-payment-continue-btn]` en el form padre (no como Stimulus target, porque está fuera del elemento del controlador).

#### `updateStatusAlert(anyActive, allHaveAmount)`
Gestiona la alerta informativa:

| Estado | Clase CSS | Texto |
|---|---|---|
| Sin métodos activos | `pgm-alert-info` | "Puede continuar sin resguardo..." |
| Activos sin monto | `pgm-alert-warning` | "Complete el monto en todos..." |
| Activos con monto | `d-none` | (oculta) |

#### `syncMethodState(switchElement, clearOnDisable)`
Activa/desactiva la card y sus campos al cambiar un switch:
- Añade/quita clase `is-active` en el elemento `[data-method-card]`
- `field.disabled = !enabled` para todos los inputs/selects del panel
- Si `clearOnDisable = true`, limpia los valores (excepto hidden fields)

#### `formatAmountOnFocusOut(input)`
Al salir de un input de monto, formatea con separador de miles chileno:
- `"150000"` → `"150.000"`
- `"1250000"` → `"1.250.000"`

#### `parseAmount(raw)` / `formatChilean(value)`
Conversión bidireccional entre representación visual chilena y número:

```javascript
parseAmount("150.000") → 150000
formatChilean(1250000) → "1.250.000"
```

### Atributos `data-*` usados en templates

| Atributo | Elemento | Descripción |
|---|---|---|
| `data-payment-method-switch="{code}"` | `<input type="checkbox">` | Toggle del método |
| `data-method-card="{code}"` | Card contenedor | Animación CSS activo/inactivo |
| `data-method-panel="{code}"` | Panel con campos | Habilitar/deshabilitar campos |
| `data-payment-amount-input` | Inputs de monto | Detectados para recalcular total |
| `data-payment-continue-btn` | Botón confirmar | Referenciado fuera del elemento del controller |

---

## 4. revenue--post-payment (`post-payment-controller.js`)

**Ruta:** `assets/controllers/revenue/post-payment-controller.js`
**Identificador Stimulus:** `revenue--post-payment`

### Propósito

Gestiona la pantalla de **resumen post-cobro**: navegación entre tabs (Detalle / Historial) y confirmación de anulación.

### Targets

| Target | Elemento | Descripción |
|---|---|---|
| `tabList` | `<ul>` contenedor de botones de tab | Para actualizar clase `active` |
| `tabDetail` | Panel de detalle del pago | Mostrar/ocultar |
| `tabHistory` | Panel de historial del paciente | Mostrar/ocultar + lazy load |
| `voidForm` | `<form>` de anulación | Enviado tras confirmación |

### Values

| Value | Tipo | Descripción |
|---|---|---|
| `historyUrl` | `String` | URL de `/{id}/history` para lazy load del frame |
| `voidUrl` | `String` | URL de `/{id}/void` (solo referencia) |

### Estado interno

```javascript
this._historyLoaded = false;  // Evita recargar el frame si ya se cargó
```

### Métodos

#### `switchTab(event)`
Alterna entre los paneles "detail" e "history".

```javascript
// data-action: "click->revenue--post-payment#switchTab"
// data-revenue--post-payment-tab-param: "detail" | "history"
```

Lógica:
1. Actualiza clase `active` en los botones del `tabList`
2. Muestra/oculta `tabDetail` y `tabHistory` con `style.display`
3. Si es la primera vez que se muestra `history` → lazy load del Turbo Frame

#### `_loadHistory()`
Busca `turbo-frame#post-payment-history` dentro de `tabHistoryTarget` o en el documento. Si no tiene `src`, lo asigna con `historyUrlValue`. Solo se ejecuta una vez por sesión (`_historyLoaded = true`).

#### `confirmVoid(event)`
Muestra `window.confirm()` antes de enviar el formulario de anulación.

```javascript
// data-action: "click->revenue--post-payment#confirmVoid"
```

Si el usuario confirma → `this.voidFormTarget.submit()`. Si cancela → no hace nada.

Texto del confirm:
> "¿Estás seguro de que deseas anular este pago?\n\nEsta acción marcará el pago y el folio como anulados y no se puede deshacer."

### Uso en template

```html
<div data-controller="revenue--post-payment"
     data-revenue--post-payment-history-url-value="{{ path('...history', {id: pa.id}) }}"
     data-revenue--post-payment-void-url-value="{{ path('...void', {id: pa.id}) }}">
```

---

## 5. Flujo de eventos entre controladores

```
Cajero selecciona paciente
  └─ revenue--patient-search emite: patient:selected { patientId }
       └─ revenue--cash-register#onPatientSelected
            └─ turbo-frame#services-panel.src = servicesUrl (lazy load)

Cajero agrega prestación
  └─ revenue--services-selector emite: services:total-changed { total: 15000 }
       └─ revenue--cash-register#onServicesTotalChanged
            ├─ _currentTotal = 15000
            ├─ actionsBar: removeClass('d-none')
            └─ paymentSection: removeClass('d-none')

Cajero activa "Efectivo"
  └─ revenue--payment-builder#toggleMethod
       ├─ syncMethodState (activa card + habilita inputs)
       └─ validateContinueButton → si monto > 0 → habilita botón Confirmar

Cajero solicita descuento
  └─ revenue--cash-register#requestDifference
       └─ turbo-frame#difference-panel.src = differenceFormUrl?total_account=15000

Supervisor aprueba diferencia
  └─ revenue--difference emite: difference:approved { discountAmount: 2000, totalAfterDiscount: 13000 }
       └─ revenue--cash-register#onDifferenceApproved
            ├─ totalDisplay: "$ 13.000"
            └─ discountBadge: visible

Cajero confirma pago
  └─ form#payment-confirm-form submit
       └─ revenue--cash-register#onPaymentFormSubmit
            ├─ Inyecta patient_id desde #ctx-patient-id
            ├─ Inyecta services_payload desde #services-payload-hidden
            └─ Turbo envía POST → PaymentConfirmController::confirm()
                 └─ Turbo Stream reemplaza payment-panel con frame → post-payment summary

Post-pago: Cajero abre tab historial
  └─ revenue--post-payment#switchTab(tab='history')
       └─ _loadHistory() → turbo-frame#post-payment-history.src = historyUrl

Post-pago: Cajero anula el pago
  └─ revenue--post-payment#confirmVoid
       └─ window.confirm() → usuario acepta
            └─ voidForm.submit() → POST /{id}/void → redirect summary
```

---

## 6. Convenciones de naming

### Identificadores de controladores Stimulus

| Archivo | Identificador |
|---|---|
| `cash-register-controller.js` | `revenue--cash-register` |
| `payment_builder_controller.js` | `revenue--payment-builder` |
| `post-payment-controller.js` | `revenue--post-payment` |

El prefijo `revenue--` indica que el controlador pertenece al módulo Revenue/Caja.

### Atributos `data-*` en templates Twig

- **Values**: `data-{controller-id}-{value-name}-value="{value}"`
  - Ejemplo: `data-revenue--cash-register-services-url-value="{{ servicesUrl }}"`
- **Targets**: `data-{controller-id}-target="{targetName}"`
  - Ejemplo: `data-revenue--payment-builder-target="total"`
- **Actions**: `data-action="{event}->{controller-id}#{method}"`
  - Ejemplo: `data-action="click->revenue--post-payment#switchTab"`
- **Params**: `data-{controller-id}-{param-name}-param="{value}"`
  - Ejemplo: `data-revenue--post-payment-tab-param="history"`

### Campos hidden para comunicación entre frames

El módulo usa campos `<input type="hidden">` con IDs globales para transferir datos entre Turbo Frames:

| ID | Origen (frame) | Destino | Contenido |
|---|---|---|---|
| `#ctx-patient-id` | `patient-context` | `payment-confirm-form` | `Patient.id` |
| `#ctx-patient-account-id` | `patient-context` | `cash-register-controller` | `PatientAccount.id` |
| `#ctx-payer-id` | `patient-context` | Form / JS | `Payer.id` |
| `#services-payload-hidden` | `services-panel` | `payment-confirm-form` | JSON de prestaciones |
| `#payment-patient-id` | `payment-panel` (form) | POST backend | Inyectado por Stimulus |
| `#payment-services-payload` | `payment-panel` (form) | POST backend | Inyectado por Stimulus |
