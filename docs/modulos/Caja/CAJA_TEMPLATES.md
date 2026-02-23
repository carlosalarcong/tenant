# CAJA — Templates Twig

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23

---

## Índice

1. [Árbol de templates](#1-árbol-de-templates)
2. [Página principal — index.html.twig](#2-página-principal--indexhtmltwig)
3. [Apertura — opening/_open_panel.html.twig](#3-apertura--opening_open_panelhtmltwig)
4. [Cierre — closing/](#4-cierre--closing)
5. [Paciente — _patient_*.html.twig](#5-paciente--_patient_htmltwig)
6. [Estado de caja — _status_bar.html.twig](#6-estado-de-caja--_status_barhtmltwig)
7. [Prestaciones — _services_*.html.twig](#7-prestaciones--_services_htmltwig)
8. [Diferencias — difference/](#8-diferencias--difference)
9. [Medios de pago — payment/](#9-medios-de-pago--payment)
10. [BonoWeb — bonoweb/](#10-bonoweb--bonoweb)
11. [Convenciones y patrones transversales](#11-convenciones-y-patrones-transversales)

---

## 1. Árbol de templates

```
templates/revenue/
├── cash-register/
│   ├── index.html.twig                     ← Página raíz del módulo de caja
│   ├── _patient_context.html.twig          ← Contexto del paciente seleccionado
│   ├── _patient_search.html.twig           ← Búsqueda de paciente (autocomplete)
│   ├── _services_panel.html.twig           ← Panel de prestaciones
│   ├── _services_row.html.twig             ← Fila de una prestación en la tabla
│   ├── _services_stream.html.twig          ← Turbo Stream add/remove prestación
│   ├── _status_bar.html.twig               ← Barra de estado de caja (header)
│   ├── opening/
│   │   └── _open_panel.html.twig           ← Panel de apertura de caja
│   ├── closing/
│   │   ├── _close_form.html.twig           ← Formulario de cierre
│   │   ├── _close_report.html.twig         ← Resumen del cierre
│   │   └── _voucher_pdf.html.twig          ← Boleta en HTML/PDF
│   └── difference/
│       ├── _form.html.twig                 ← Formulario de solicitud de diferencia
│       └── _status.html.twig              ← Estado de la diferencia (polling)
├── payment/
│   ├── _batch.html.twig                    ← Contenedor de medios de pago
│   └── method/
│       ├── _cash_row.html.twig             ← Efectivo
│       ├── _credit_card_row.html.twig      ← Tarjeta de crédito
│       ├── _debit_card_row.html.twig       ← Tarjeta de débito
│       ├── _check_row.html.twig            ← Cheque
│       ├── _bank_transfer_row.html.twig    ← Transferencia bancaria
│       ├── _electronic_voucher_row.html.twig ← Bono electrónico
│       ├── _manual_voucher_row.html.twig   ← Bono manual
│       ├── _bonoweb_row.html.twig          ← BonoWeb FONASA
│       └── _gratuity_row.html.twig         ← Gratuidad
└── bonoweb/
    ├── _panel.html.twig                    ← Panel BonoWeb principal
    ├── _status.html.twig                   ← Estado voucher (polling)
    └── _confirmed.html.twig               ← Confirmación de copago
```

---

## 2. Página principal — index.html.twig

**Archivo:** `templates/revenue/cash-register/index.html.twig`
**Ruta:** `app_revenue_cash_register_index`
**Extiende:** `app_layout.html.twig`

### Propósito

Orquestador principal del módulo. Layout de dos columnas que coordina el flujo completo:
paciente → prestaciones → diferencia → pago.

### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `context` | `string` | `'direct'` \| `'from_admission'` \| `'from_appointment'` |
| `admissionRecordId` | `int` | ID de admisión (si context = from_admission) |
| `appointmentId` | `int` | ID de cita (si context = from_appointment) |
| `servicesUrl` | `string` | URL del panel de prestaciones (lazy) |
| `differenceFormUrl` | `string` | URL del formulario de diferencia |
| `statusBarUrl` | `string` | URL de la barra de estado |
| `patientSearchUrl` | `string` | URL del buscador de paciente |
| `confirmUrl` | `string` | URL POST de confirmación de pago |
| `payment_methods` | `array` | Configuración de medios de pago |
| `initial_rows` | `array` | Filas iniciales por método (vacío en flujo normal) |

### Stimulus Controller

```html
<div data-controller="revenue--cash-register"
     data-revenue--cash-register-services-url-value="{{ servicesUrl }}"
     data-revenue--cash-register-difference-form-url-value="{{ differenceFormUrl }}"
     data-revenue--cash-register-admission-record-id-value="{{ admissionRecordId }}"
     data-revenue--cash-register-appointment-id-value="{{ appointmentId }}"
     data-action="patient:selected->revenue--cash-register#onPatientSelected
                  services:total-changed->revenue--cash-register#onServicesTotalChanged
                  difference:approved->revenue--cash-register#onDifferenceApproved">
```

### Layout de Turbo Frames

```
[Encabezado: título + cash-register-status-bar (lazy)]
[Col izquierda xl-4]
  ├── turbo-frame#patient-search (src="{{ patientSearchUrl }}")
  └── turbo-frame#patient-context (vacío, se popula al seleccionar)
[Col derecha xl-8]
  ├── turbo-frame#services-panel (lazy al seleccionar paciente)
  ├── [actionsBar d-none → muestra total + botón "Solicitar descuento"]
  ├── turbo-frame#difference-panel (vacío, bajo demanda)
  └── [paymentSection d-none → form POST + payment/_batch.html.twig]
```

### Formulario de pago

```html
<form id="payment-confirm-form"
      method="POST"
      action="{{ confirmUrl }}"
      data-action="submit->revenue--cash-register#onPaymentFormSubmit">
    <input type="hidden" name="_csrf_token" value="{{ csrf_token('payment_confirm') }}">
    <input type="hidden" name="patient_id"         id="payment-patient-id"       value="">
    <input type="hidden" name="services_payload"   id="payment-services-payload" value="">
    <input type="hidden" name="admission_record_id" value="{{ admissionRecordId ?: '' }}">
    <input type="hidden" name="appointment_id"      value="{{ appointmentId ?: '' }}">
    ...
</form>
```

**Nota:** `patient_id` y `services_payload` se inyectan por JavaScript (`onPaymentFormSubmit`) justo antes del submit, leyendo desde `#ctx-patient-id` y `#services-payload-hidden`.

---

## 3. Apertura — opening/_open_panel.html.twig

**Archivo:** `templates/revenue/cash-register/opening/_open_panel.html.twig`
**Turbo Frame:** `cash-register-open`
**Ruta de carga:** `app_revenue_cash_register_open`

### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `status` | `string` | `'closed'` \| `'open'` \| `'unclosed'` \| `'no_voucher'` |
| `locations` | `CashRegisterLocation[]` | Cajas activas (solo si status = 'closed') |
| `openRegister` | `CashRegister\|null` | Caja abierta actualmente |

### Estados

| Status | Descripción | Contenido renderizado |
|---|---|---|
| `closed` | Flujo normal — caja sin abrir | Formulario con select de ubicación, POST a `app_revenue_cash_register_open_submit` |
| `open` | Ya tiene caja abierta hoy | Card verde con nombre, horario de apertura y enlace a cerrar caja |
| `unclosed` | Caja de día anterior sin cerrar | Card amarilla, botón para ir a cerrar la caja anterior |
| `no_voucher` | Sin talonario con folios | Card roja, aviso de contactar al administrador |

### Formulario de apertura

```html
<form method="post"
      action="{{ path('app_revenue_cash_register_open_submit') }}"
      data-turbo-frame="cash-register-open">
    <input type="hidden" name="_token" value="{{ csrf_token('cash_register_open') }}">
    <select id="location_id" name="location_id" class="form-select" required>
        {% for loc in locations %}
            <option value="{{ loc.id }}">{{ loc.name }}</option>
        {% endfor %}
    </select>
    <button type="submit">Abrir caja</button>
</form>
```

---

## 4. Cierre — closing/

### 4.1 _close_form.html.twig

**Archivo:** `templates/revenue/cash-register/closing/_close_form.html.twig`
**Turbo Frame:** `cash-register-close`
**Ruta de carga:** `app_revenue_cash_register_close`

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `cashRegister` | `CashRegister` | Caja a cerrar |
| `paymentMethods` | `PaymentMethod[]` | Formas de pago activas |

#### Contenido

- **Resumen de apertura:** fecha, monto inicial, total esperado (`$0` hasta Fase C), cajero.
- **Tabla de montos:** una fila por medio de pago con `<input name="amounts[{method.id}]">`.
- **Nota MVP:** "Total esperado" muestra $0 hasta que Fase C integre `PaymentAccountDetail`.

```html
<input type="number"
       name="amounts[{{ method.id }}]"
       min="0" step="1" value="0">
```

---

### 4.2 _close_report.html.twig

**Archivo:** `templates/revenue/cash-register/closing/_close_report.html.twig`
**Turbo Frame:** `cash-register-report`
**Ruta de carga:** `app_revenue_cash_register_close_report`

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `cashRegister` | `CashRegister` | Caja cerrada |
| `details` | `CashRegisterDetail[]` | Detalle por forma de pago |

#### Secciones

1. **Datos de sesión:** cajero, ubicación, apertura, cierre.
2. **Totales:** monto inicial, monto real, superávit, déficit.
3. **Detalle por forma de pago:** tabla con columnas forma de pago, banco, N° depósito, monto + fila total.
4. **Acciones:** enlace para abrir nueva caja. (PDF disponible en Fase G.)

---

### 4.3 _voucher_pdf.html.twig

**Archivo:** `templates/revenue/cash-register/closing/_voucher_pdf.html.twig`
**Tipo:** HTML standalone (sin extends) para impresión/PDF
**Ruta:** `app_revenue_payment_post_payment_print` con `data-turbo="false"` y `target="_blank"`

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `paymentAccount` | `PaymentAccount` | Pago a imprimir |
| `voucherEntry` | `VoucherEntry\|null` | Folio asignado |
| `clinicalActions` | `ClinicalActionPatient[]` | Prestaciones cobradas |
| `details` | `PaymentAccountDetail[]` | Medios de pago usados |

#### Estructura del documento

```
[Encabezado: nombre organización | Box documento]
  Box: BOLETA / Folio: {folioNumber} / Fecha
[Banner ANULADO si paymentAccount.cancellationDate]
[Sección: Datos del paciente]
  RUT · Nombre · Previsión · Convenio
[Sección: Prestaciones]
  Tabla: Prestación | Cant | P.Unitario | Descuento | Total
[Sección: Formas de pago]
  Tabla: Forma | Referencia | Monto
[Total cobrado]
[Información de caja + cajero]
[Pie de página: metadatos del documento]
```

#### Estilos

CSS inline con fuente Helvetica/Arial, 11pt. Sin dependencia de Bootstrap. Clase `.void-banner` para el sello rojo de ANULADO.

---

## 5. Paciente — _patient_*.html.twig

### 5.1 _patient_search.html.twig

**Archivo:** `templates/revenue/cash-register/_patient_search.html.twig`
**Turbo Frame:** `patient-search`
**Ruta de carga:** `app_revenue_cash_register_patient_search`

Contiene un Stimulus controller `revenue--patient-search` con un input de texto y un dropdown de resultados. Busca por RUT o nombre al escribir (debounce en el controller JS). Al seleccionar un paciente, emite el evento `patient:selected` que escucha el orquestador.

```html
<input type="text"
       data-revenue--patient-search-target="input"
       data-action="input->revenue--patient-search#onInput keydown->revenue--patient-search#onKeydown">
<div data-revenue--patient-search-target="dropdown" style="display:none">
</div>
```

---

### 5.2 _patient_context.html.twig

**Archivo:** `templates/revenue/cash-register/_patient_context.html.twig`
**Turbo Frame:** `patient-context`
**Ruta de carga:** `app_revenue_cash_register_patient_context` (GET `/{id}/context`)

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `patient` | `Patient` | Paciente seleccionado |
| `patientAccount` | `PatientAccount\|null` | Cuenta activa del paciente |

#### Campos hidden para el formulario de cobro

```html
<input type="hidden" id="ctx-patient-id"         value="{{ patient.id }}">
<input type="hidden" id="ctx-payer-id"            value="{{ patient.payer.id }}">
<input type="hidden" id="ctx-agreement-id"        value="{{ patient.agreement.id }}">
<input type="hidden" id="ctx-plan-id"             value="{{ patient.insurancePlan.id }}">
<input type="hidden" id="ctx-patient-account-id"  value="{{ patientAccount.id }}">
```

**Importante:** El orquestador `revenue--cash-register` lee `#ctx-patient-id` y `#ctx-patient-account-id` durante el submit del pago.

#### Sub-frames pendientes (Fase E)

```html
<turbo-frame id="patient-account-summary"></turbo-frame>
<turbo-frame id="patient-financier"></turbo-frame>
<turbo-frame id="patient-guarantee"></turbo-frame>
```

---

## 6. Estado de caja — _status_bar.html.twig

**Archivo:** `templates/revenue/cash-register/_status_bar.html.twig`
**Turbo Frame:** `cash-register-status-bar`
**Ruta de carga:** `app_revenue_cash_register_status_bar`
**Carga:** Lazy desde `index.html.twig` (`loading="lazy"` en el frame)

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `status` | `string` | `'open'` \| `'no_voucher'` \| `'unclosed'` \| `'closed'` |
| `openRegister` | `CashRegister\|null` | Caja abierta actualmente |

#### Estados y UI

| Status | Badge | Acción disponible |
|---|---|---|
| `open` | Verde "Caja abierta" | Botón "Cerrar caja" → `app_revenue_cash_register_close` |
| `no_voucher` | Amarillo "Sin talonario" | Solo aviso |
| `unclosed` | Rojo "Caja anterior sin cerrar" | Botón "Cerrar caja del {fecha}" |
| `closed` | Gris "Caja cerrada" | Botón "Abrir caja" → `app_revenue_cash_register_open` |

---

## 7. Prestaciones — _services_*.html.twig

### 7.1 _services_panel.html.twig

**Archivo:** `templates/revenue/cash-register/_services_panel.html.twig`
**Turbo Frame:** `services-panel`
**Ruta de carga:** `app_revenue_cash_register_services_panel`
**Carga:** Lazy al seleccionar paciente (orquestador pone `frame.src = servicesUrlValue`)

#### Variables de Twig

| Variable | Tipo | Descripción |
|---|---|---|
| `rows` | `array` | Prestaciones ya agregadas (vacío al inicio) |
| `total` | `int` | Total sumado de todas las filas |
| `payload` | `string` | JSON serializado del payload de prestaciones |

#### Stimulus controller

`revenue--services-selector` con valores:
- `searchUrl`: `app_revenue_cash_register_services_search`
- `addUrl`: `app_revenue_cash_register_services_add`
- `removeUrl`: `app_revenue_cash_register_services_remove`
- `csrfAdd` / `csrfRemove`: tokens CSRF

#### Controles

```
[Búsqueda autocomplete] [Cantidad] [Monto unit.] [Descuento] [Botón +]
[Tabla de prestaciones con filas _services_row.html.twig]
[Total prestaciones: $ {total}]
[#services-payload-hidden: JSON del payload]
```

El payload hidden tiene `id="services-payload-hidden"` — leído por el orquestador al enviar el formulario.

---

### 7.2 _services_row.html.twig

**Archivo:** `templates/revenue/cash-register/_services_row.html.twig`

Una `<tr>` con `id="service-row-{row.rowId}"`. Incluye:
- Nombre de prestación.
- Input de cantidad (dispara `onRowChange`).
- Monto unitario (readonly, en texto).
- Input de descuento (dispara `onRowChange`).
- Total de línea (`id="service-row-total-{row.rowId}"`).
- Botón eliminar con `data-revenue--services-selector-row-id-param`.

```html
<tr id="service-row-{{ row.rowId }}"
    data-service-row
    data-row-id="{{ row.rowId }}"
    data-billing-item-id="{{ row.billingItemId }}"
    data-unit-amount="{{ row.unitAmount }}">
```

---

### 7.3 _services_stream.html.twig

**Archivo:** `templates/revenue/cash-register/_services_stream.html.twig`
**Respuesta Turbo Stream** desde `app_revenue_cash_register_services_add` / `_remove`

```twig
{% if operation == 'add' %}
<turbo-stream action="remove" target="services-empty-row"></turbo-stream>
<turbo-stream action="append" target="services-table-body">
    <template>{% include '_services_row.html.twig' %}</template>
</turbo-stream>

{% elseif operation == 'remove' %}
<turbo-stream action="remove" target="service-row-{{ rowId }}"></turbo-stream>
{% endif %}
```

---

## 8. Diferencias — difference/

### 8.1 difference/_form.html.twig

**Archivo:** `templates/revenue/cash-register/difference/_form.html.twig`
**Turbo Frame:** `difference-panel`

#### Dos estados del mismo template

**Estado 1 — Formulario de solicitud** (`difference` no definida o null):

```html
<form method="POST"
      action="{{ path('app_revenue_cash_register_difference_request') }}"
      data-turbo-frame="difference-panel">
    <input type="hidden" name="total_account"      value="{{ totalAccount ?? '0' }}">
    <input type="hidden" name="patient_account_id" value="{{ patientAccountId ?? '' }}">
    <select name="difference_type_id">     <!-- tipos --></select>
    <select name="difference_reason_id">   <!-- motivos --></select>
    <select name="difference_direction_id"><!-- direcciones --></select>
    <input name="total_discount"       type="number">
    <input name="total_after_discount" type="number" readonly>
</form>
```

**Estado 2 — Post-solicitud con polling** (`difference` definida):

```html
<div data-controller="revenue--difference"
     data-revenue--difference-difference-id-value="{{ difference.id }}"
     data-revenue--difference-status-url-value="{{ statusUrl }}"
     data-revenue--difference-cancel-url-value="{{ cancelUrl }}">
    <!-- Card con resumen de montos -->
    <!-- turbo-frame#difference-status (recargado por Stimulus) -->
    <!-- Botón "Anular solicitud" si difference.isPending() -->
</div>
```

---

### 8.2 difference/_status.html.twig

**Archivo:** `templates/revenue/cash-register/difference/_status.html.twig`
**Turbo Frame:** `difference-status`
**Ruta de polling:** `app_revenue_cash_register_difference_status` (GET `/{id}/status`)

Lleva data attributes en el frame raíz para que el Stimulus controller los lea:
```html
<turbo-frame id="difference-status"
             data-difference-status="{{ difference.status }}"
             data-discount-amount="{{ difference.totalDiscount }}"
             data-total-after-discount="{{ difference.totalAfterDiscount }}">
```

#### Lógica de renderizado

| Estado | UI |
|---|---|
| `solicitada` | Spinner + aviso "Esperando autorización de supervisor" |
| `autorizada` / `auto_aprobada` | Alert verde con montos y datos del autorizador |
| `rechazada` | Alert rojo con monto original |
| `anulada` | Alert gris "Solicitud anulada" |

Cuando el estado es `autorizada` o `auto_aprobada`, el controller JS detecta el cambio y emite `difference:approved` con `{ discountAmount, totalAfterDiscount }` para que el orquestador actualice el total.

---

## 9. Medios de pago — payment/

### 9.1 payment/_batch.html.twig

**Archivo:** `templates/revenue/payment/_batch.html.twig`

Contenedor del Stimulus controller `revenue--payment-builder`. Itera sobre `payment_methods` y renderiza una card por método con:
- Header: ícono + nombre + switch toggle.
- Body (con slide CSS): incluye el row template del método (`method.row_template`).
- Barra de total al fondo.

```html
<div data-controller="revenue--payment-builder"
     data-action="input->revenue--payment-builder#recalculateTotal
                  change->revenue--payment-builder#recalculateTotal">
    {% for method_code, method in payment_methods %}
    <div class="pgm-card" data-method-card="{{ method_code }}">
        <div class="pgm-card-header">
            <input type="checkbox"
                   data-payment-method-switch="{{ method_code }}"
                   data-action="change->revenue--payment-builder#toggleMethod">
        </div>
        <div class="pgm-card-body" data-method-panel="{{ method_code }}">
            <!-- row_template incluido aquí -->
        </div>
    </div>
    {% endfor %}
    <div class="pgm-total-bar">
        <span data-revenue--payment-builder-target="total">$ 0</span>
    </div>
</div>
```

---

### 9.2 Filas de métodos de pago

Todos los templates de filas comparten:
- `data-payment-row`, `data-method-code`, `data-row-index`
- Inputs con `data-payment-amount-input="1"` (detectados por el Stimulus para sumar el total)
- Nombre de campo: `payment_batch[rows][{method_code}][{index}][campo]`

#### Tabla resumen

| Template | Campos de entrada |
|---|---|
| `_cash_row.html.twig` | `amount` |
| `_credit_card_row.html.twig` | `card_id` (select), `voucher`, `amount` |
| `_debit_card_row.html.twig` | `voucher`, `card_last_digits`, `amount` |
| `_check_row.html.twig` | `check_number`, `bank_id`, `amount`, `rut`, `name`, `condition_id`, `check_date` |
| `_bank_transfer_row.html.twig` | `bank_id`, `transfer_number`, `account_holder`, `amount` |
| `_electronic_voucher_row.html.twig` | `folio`, `authorization_code`, `amount` |
| `_manual_voucher_row.html.twig` | `folio`, `payer_name`, `amount` |
| `_bonoweb_row.html.twig` | `amount` (readonly), `voucher_id` (readonly) — auto-completado por JS |
| `_gratuity_row.html.twig` | `gratuity_type_id`, `gratuity_reason_id`, `amount`, `notes` |

#### Nota sobre _check_row.html.twig

Integra el Stimulus controller `admission--rut-validator` para formatear y validar RUT:

```html
<div data-controller="admission--rut-validator">
    <select data-admission--rut-validator-target="typeSelect" class="d-none">
        <option selected>RUT</option>
    </select>
    <input data-admission--rut-validator-target="queryInput"
           data-action="input->admission--rut-validator#onQueryInput
                        blur->admission--rut-validator#validateRutIfNeeded">
</div>
```

#### Nota sobre _bonoweb_row.html.twig

Los campos `amount` y `voucher_id` son readonly y se rellenan automáticamente cuando el cajero confirma el bono en el panel BonoWeb (evento `bonoweb:confirmed` emitido por `revenue--bonoweb` en modo `confirmed`).

---

## 10. BonoWeb — bonoweb/

### 10.1 bonoweb/_panel.html.twig

**Archivo:** `templates/revenue/bonoweb/_panel.html.twig`
**Turbo Frame:** `bonoweb-panel`

#### Tres estados

**Estado 1 — Sin cuenta de pago** (`paymentAccount` es null):
Mensaje informativo.

**Estado 2 — Formulario de generación** (`voucher` no definido):
```html
<form method="POST" action="{{ generateUrl }}" data-turbo-frame="bonoweb-panel">
    <input type="hidden" name="paymentAccountId" value="{{ paymentAccount.id }}">
    <table id="bonoweb-prestaciones-table">
        <!-- Filas: name, code FONASA, quantity -->
    </table>
    <button id="bonoweb-add-row">Agregar prestación</button>
    <!-- Script inline para agregar filas dinámicamente -->
    <button type="submit">Generar bono BonoWeb</button>
</form>
```

**Estado 3 — Voucher generado** (`voucher` definido):
```html
<div data-controller="revenue--bonoweb"
     data-revenue--bonoweb-status-url-value="{{ statusUrl }}">
    <!-- Montos estimados (copago + bonificación) -->
    <turbo-frame id="bonoweb-status"
                 src="{{ statusUrl }}"
                 data-revenue--bonoweb-target="statusFrame"
                 loading="lazy">
    </turbo-frame>
</div>
```

---

### 10.2 bonoweb/_status.html.twig

**Archivo:** `templates/revenue/bonoweb/_status.html.twig`
**Turbo Frame:** `bonoweb-status`
**Ruta de polling:** `app_revenue_bonoweb_status` (GET `/{voucherId}/status`)
**Cache-Control:** `no-cache` forzado en el controller PHP

Data attributes en el frame raíz para detección por JS:
```html
<turbo-frame id="bonoweb-status"
             data-bonoweb-status="{{ voucher.status }}"
             data-bonoweb-copago="{{ voucher.copagoTotal }}"
             data-bonoweb-voucher-id="{{ voucher.voucherId }}">
```

#### Estados del voucher

| Estado | UI |
|---|---|
| `isPending()` (Created/Verified/Waiting) | Spinner + aviso "Esperando validación en FONASA" + link al bono (si `voucher.voucherUrl`) |
| `isPaid()` | Alert verde con copago + bonificación + formulario POST "Confirmar pago" |
| `isDone()` | Alert verde "Bono confirmado" |
| `isCanceled()` | Alert gris "Bono cancelado o vencido" |

---

### 10.3 bonoweb/_confirmed.html.twig

**Archivo:** `templates/revenue/bonoweb/_confirmed.html.twig`
**Turbo Frame:** `bonoweb-panel`
**Ruta de carga:** respuesta a `app_revenue_bonoweb_confirm` (POST)

Card verde con resumen del copago confirmado. Incluye el Stimulus controller en modo `confirmed`:

```html
<div data-controller="revenue--bonoweb"
     data-revenue--bonoweb-mode-value="confirmed"
     data-revenue--bonoweb-copago-value="{{ voucher.copagoTotal }}"
     data-revenue--bonoweb-voucher-id-value="{{ voucher.voucherId }}">
```

Al conectar con `mode="confirmed"`, el controller emite `bonoweb:confirmed` con el copago y el `voucherId`, que el template `_bonoweb_row.html.twig` escucha para rellenar sus campos readonly.

---

## 11. Convenciones y patrones transversales

### Turbo Frames

Todos los componentes de la UI viven dentro de `<turbo-frame id="...">`. Los IDs relevantes:

| ID | Contenido |
|---|---|
| `patient-search` | Búsqueda de paciente |
| `patient-context` | Datos del paciente seleccionado |
| `services-panel` | Panel de prestaciones (lazy) |
| `difference-panel` | Formulario/estado de diferencia |
| `difference-status` | Estado del polling de diferencia |
| `payment-panel` | Builder de medios de pago |
| `cash-register-status-bar` | Barra de estado del header |
| `cash-register-open` | Panel de apertura |
| `cash-register-close` | Formulario de cierre |
| `cash-register-report` | Resumen del cierre |
| `bonoweb-panel` | Panel BonoWeb principal |
| `bonoweb-status` | Estado del voucher BonoWeb |

### Turbo Streams

Usados exclusivamente para las operaciones de prestaciones:
- **`add`**: `action="remove"` sobre `services-empty-row` + `action="append"` sobre `services-table-body`
- **`remove`**: `action="remove"` sobre `service-row-{rowId}`

### Data attributes de Stimulus

Los templates pasan datos a los controllers JS mediante `data-{controller}-{value}-value`. Todos los controllers del módulo son de la familia `revenue--*`.

### Icono libs

- **Bootstrap Icons** (`bi bi-*`) — usado en `cash-register/`, `difference/`, `bonoweb/`
- **Boxicons** (`bx bx-*`) — usado en `opening/` y `closing/` (panel de apertura/cierre)

### Flash messages

Los templates de apertura y cierre muestran flashes del sistema (`app.flashes`) dentro del Turbo Frame para que la sustitución no los pierda.

### PDF/Impresión

`_voucher_pdf.html.twig` es un HTML standalone (sin `extends`) con CSS inline. El enlace de impresión usa `data-turbo="false"` y `target="_blank"` para que Turbo no intercepte la navegación.
