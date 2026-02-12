# Formularios de Pago — Guía de Portabilidad a Symfony 7 + Stimulus + Turbo

> Basado en el análisis de `RecaudacionBundle` de **Melisa** (Symfony 2/3, jQuery, Twig legacy).
> Objetivo: reimplementar estos formularios de forma **independiente y global** en el proyecto **tenant** (Symfony 7, Stimulus, Turbo).

---

## Índice

1. [¿Qué son y cómo funcionan en Melisa?](#1-qué-son-y-cómo-funcionan-en-melisa)
2. [Formularios disponibles](#2-formularios-disponibles)
3. [Arquitectura original (Melisa)](#3-arquitectura-original-melisa)
4. [Flujo completo de carga y envío](#4-flujo-completo-de-carga-y-envío)
5. [Entidades y datos requeridos](#5-entidades-y-datos-requeridos)
6. [Campos por formulario](#6-campos-por-formulario)
7. [Lógica JavaScript crítica](#7-lógica-javascript-crítica)
8. [Diseño objetivo para Symfony 7 + Stimulus + Turbo](#8-diseño-objetivo-para-symfony-7--stimulus--turbo-actualizado)
9. [Estructura propuesta para tenant](#9-estructura-propuesta-para-tenant-final)
10. [Procesamiento del POST y validación](#10-procesamiento-del-post-y-validación)

---

## 1. ¿Qué son y cómo funcionan en Melisa?

Los **formularios de pago** son componentes Twig + PHP que representan cada **medio de pago** disponible en una caja (efectivo, tarjetas, cheques, bonos, etc.).

Su característica principal es que son **dinámicos**:
- Se cargan inicialmente con la vista principal.
- Pueden **clonarse** vía AJAX (botones `+/-`) para registrar múltiples pagos del mismo tipo.
- Son habilitados/deshabilitados por un **checkbox** que muestra u oculta la tabla correspondiente.
- Los campos tienen nombres únicos basados en `{idFormaDePago}_{indice}` para evitar colisiones.

**La forma de pago activa se determina desde base de datos**: la entidad `FormaPagoDato` almacena la ruta Twig de cada tipo de pago, lo que permite agregar nuevas formas sin tocar código.

---

## 2. Formularios disponibles

| # | Archivo Twig (Melisa) | Tipo de Pago | Campos principales |
|---|---|---|---|
| 1 | `FormaDePago_Efectivo` | Efectivo | monto |
| 2 | `FormaDePago_TarjetaCredito` | Tarjeta de Crédito | tarjeta (select), voucher, monto |
| 3 | `FormaDePago_TarjetaDebito` | Tarjeta de Débito | banco (select), voucher, monto |
| 4 | `FormaDePago_ChequeDia` | Cheque al Día | nº cheque, banco, monto, RUT, nombre, condición |
| 5 | `FormaDePago_ChequeFecha` | Cheque a Fecha | igual al anterior + fecha habilitada |
| 6 | `FormaDePago_BonoElectronico` | Bono Electrónico (IMED) | nº bono, bonificación, seguro complementario, copago, excedente |
| 7 | `FormaDePago_BonoElectronico_IMED` | Bono IMED (resumen) | igual anterior sin alerta |
| 8 | `FormaDePago_BonoManual` | Bono Manual | nº bono, bonificación, seguro complementario, copago |
| 9 | `FormaDePago_Gratuidad` | Gratuidad | tipo gratuidad (select), monto |
| 10 | `OtrosMedios_CartaConvenioImed` | Carta Convenio IMED | folio, monto |
| 11 | `OtrosMedios_CartaConvenioLasik` | Carta Convenio LASIK | folio, monto |

Archivos auxiliares relevantes:
- `DynamicControl.html.twig` — botones `+` / `-` para clonar filas
- `ImedMensajeError.html.twig` — mensaje de error IMED
- `IndexImed.html.twig` — iframe de integración IMED

---

## 3. Arquitectura original (Melisa)

```
RecaudacionBundle/
├── Controller/
│   └── Pago/
│       ├── DefaultController.php        ← carga inicial de la vista
│       └── MedioPagoController.php      ← genera form dinámico por AJAX
├── Form/Type/
│   └── MediosPagoType.php               ← FormType Symfony que define todos los campos
├── Resources/views/
│   ├── FormasDePago/                    ← 11 archivos Twig (uno por medio de pago)
│   └── PagoPaciente/
│       ├── _mediosDePagoNormales.html.twig  ← incluye los FormasDePago
│       └── _mediosDePagoOtros.html.twig
└── Resources/public/js/
    └── PagoPaciente/mediosDePago.js     ← funciones JS de interacción
```

### MediosPagoType — opciones de creación

```php
$form = $this->createForm(MediosPagoType::class, null, [
    'idFrom'         => $idFormaDePago,      // ID del tipo (ej: 3 = BonoElectronico)
    'idCantidad'     => $indice,             // 0, 1, 2... (para clonar)
    'clone'          => true,                // si es clonación vía AJAX
    'nuevo'          => false,
    'iEmpresa'       => $empresa->getId(),
    'sucursal'       => null,
    'idFromOtros'    => $arrayOtros,
    'estado_activado'=> $estadoActivo,
]);
```

Los **nombres de campo** usan el patrón:

```
{campoBase}_{idFormaDePago}_{indice}
```

Ejemplos:
- `monto_3_0`, `monto_3_1` — montos del bono electrónico (id=3), filas 0 y 1
- `voucher_2_0` — voucher de tarjeta de crédito (id=2), fila 0
- `banco_8_0` — banco del cheque al día (id=8), fila 0

Esto es **crítico**: permite tener múltiples medios de pago en un único `<form>` sin colisión de nombres.

---

## 4. Flujo completo de carga y envío

### Carga inicial

```
1. DefaultController::indexAction()
   │
   ├─ Consulta BD: listado de formas de pago habilitadas para la empresa
   ├─ Crea MediosPagoType para cada forma (con índice 0)
   └─ Pasa a Twig:
       - listadoMediosPagos   → formas normales (efectivo, tarjetas, bonos)
       - listadoOtrosMedios   → otras formas (cartas convenio)
       - mediospago_form      → form view del MediosPagoType

2. Vista _mediosDePagoNormales.html.twig
   │
   └─ {% for medio in listadoMediosPagos %}
          {% include medio.ruta %}  ← ruta viene de FormaPagoDato en BD
      {% endfor %}

3. Cada FormaDePago_*.html.twig
   └─ {{ form_widget(mediospago_form.campo_id_indice) }}
```

### Clonación dinámica (botón `+`)

```
1. Usuario hace click en botón "+"
   │
2. JavaScript llama a NuevoFormDinamico(idFormaDePago, version)
   │
3. AJAX GET → /Caja_getNuevoFormDinamico?idMedioPago=X&cantidad=Y
   │
4. MedioPagoController::nuevoFormDinamicoAction()
   ├─ Crea nuevo MediosPagoType con índice Y
   └─ Renderiza FormaDePago_*.html.twig → retorna HTML

5. JavaScript inserta HTML en el DOM
```

### Envío del formulario

```
1. Usuario llena campos y hace click en "Efectuar Pago"
   │
2. POST del form (formMedios) al controlador
   │
3. Controlador extrae campos del Request:
   ├─ Lee monto_{id}_{i}, voucher_{id}_{i}, etc.
   ├─ Valida cada medio de pago
   ├─ Crea entidades de pago
   └─ Persiste en BD → genera comprobante
```

---

## 5. Entidades y datos requeridos

Todas las entidades provienen de **HermesBundle**. Para el proyecto tenant necesitarás equivalentes:

| Entidad original | Datos que provee | Equivalente en tenant |
|---|---|---|
| `FormaPago` | Tipos de formas de pago disponibles | `PaymentMethod` o tabla `payment_methods` |
| `FormaPagoDato` | Ruta Twig asociada a cada forma | Puede reemplazarse con un Enum o config YAML |
| `Banco` | Listado de bancos (para cheques y débito) | `Bank` o tabla `banks` |
| `TarjetaCredito` | Tarjetas de crédito disponibles | `CreditCard` o tabla `credit_cards` |
| `CondicionPago` | Condición del cheque (AL DÍA / POSFECHADO) | `CheckCondition` o Enum |
| `MotivoGratuidad` | Tipos de gratuidad | `GratuityCause` o tabla `gratuity_causes` |
| `Estado` | Estado activo/inactivo | Puede ser un boolean `is_active` |

### Consultas clave que hace MediosPagoType

```php
// Bancos activos de la empresa
$em->getRepository(Banco::class)->findBy(['empresa' => $empresa, 'estado' => $estadoActivo]);

// Tarjetas de crédito activas (filtradas por tipo y empresa)
$em->getRepository(TarjetaCredito::class)->findByEmpresaAndTipo($empresa, $tipo, $estadoActivo);

// Motivos de gratuidad por sucursal
$em->getRepository(MotivoGratuidad::class)->findBySucursal($sucursal, $estadoActivo);

// Condiciones de pago activas
$em->getRepository(CondicionPago::class)->findBy(['empresa' => $empresa, 'estado' => $estadoActivo]);
```

---

## 6. Campos por formulario

Cada tabla muestra el campo legacy de Melisa y su equivalente en la nueva convención del tenant. El `methodCode` de cada sección es la clave que usa `PaymentMethodConfigRegistry` y `PaymentBatchProcessor` para identificar el tipo en el POST.

### Efectivo — `methodCode: cash`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `monto_{id}_0` | `payment_batch[rows][cash][0][amount]` | NumberType | |

### Tarjeta de Crédito — `methodCode: credit_card`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `TarjetaCredito_{id}_0` | `payment_batch[rows][credit_card][0][card_id]` | EntityType | Select |
| `voucher_{id}_0` | `payment_batch[rows][credit_card][0][voucher]` | TextType | maxlength 12 |
| `monto_{id}_0` | `payment_batch[rows][credit_card][0][amount]` | NumberType | readonly |

### Tarjeta de Débito — `methodCode: debit_card`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `TarjetaDebito__{id}_0` | `payment_batch[rows][debit_card][0][bank_id]` | EntityType | Select |
| `voucher_{id}_0` | `payment_batch[rows][debit_card][0][voucher]` | TextType | maxlength 10 |
| `monto_{id}_0` | `payment_batch[rows][debit_card][0][amount]` | NumberType | readonly |

### Cheque al Día / Cheque a Fecha — `methodCode: check`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `cheque_{id}_{i}` | `payment_batch[rows][check][{i}][check_number]` | NumberType | |
| `banco_{id}_{i}` | `payment_batch[rows][check][{i}][bank_id]` | EntityType | Select |
| `monto_{id}_{i}` | `payment_batch[rows][check][{i}][amount]` | NumberType | readonly |
| `rut_{id}_{i}` | `payment_batch[rows][check][{i}][rut]` | TextType | Validación RUT |
| `nombre_{id}_{i}` | `payment_batch[rows][check][{i}][name]` | TextType | |
| `condicion_{id}_{i}` | `payment_batch[rows][check][{i}][condition_id]` | EntityType | Select |
| `fecha_cheque_{id}_{i}` | `payment_batch[rows][check][{i}][check_date]` | TextType | DatePicker; solo editable en `check_date`, readonly en `check` |

### Bono Electrónico — `methodCode: electronic_voucher`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `validaBonoOculto_{id}_{i}` | `payment_batch[rows][electronic_voucher][{i}][validation_status]` | HiddenType | valor inicial 2 |
| `exedente_{id}` | `payment_batch[rows][electronic_voucher][0][surplus]` | NumberType | Solo este tipo |
| `bono_{id}_{i}` | `payment_batch[rows][electronic_voucher][{i}][voucher_number]` | NumberType | |
| `Bonificacion_{id}_{i}` | `payment_batch[rows][electronic_voucher][{i}][bonification]` | NumberType | readonly, lo devuelve IMED |
| `Seguro_{id}_{i}` | `payment_batch[rows][electronic_voucher][{i}][insurance]` | NumberType | readonly |
| `copago_{id}_{i}` | `payment_batch[rows][electronic_voucher][{i}][copay]` | NumberType | readonly |

### Bono Manual — `methodCode: manual_voucher`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `validaBonoOculto_{id}_{i}` | `payment_batch[rows][manual_voucher][{i}][validation_status]` | HiddenType | valor inicial 2 |
| `bono_{id}_{i}` | `payment_batch[rows][manual_voucher][{i}][voucher_number]` | NumberType | |
| `Bonificacion_{id}_{i}` | `payment_batch[rows][manual_voucher][{i}][bonification]` | NumberType | readonly |
| `Seguro_{id}_{i}` | `payment_batch[rows][manual_voucher][{i}][insurance]` | NumberType | readonly |
| `copago_{id}_{i}` | `payment_batch[rows][manual_voucher][{i}][copay]` | NumberType | readonly |

### Gratuidad — `methodCode: gratuity`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `idGratuidad_{id}` | `payment_batch[rows][gratuity][0][cause_id]` | EntityType | Select |
| `monto_{id}_0` | `payment_batch[rows][gratuity][0][amount]` | NumberType | readonly |

### Carta Convenio IMED — `methodCode: agreement_imed`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `folio_{id}` | `payment_batch[rows][agreement_imed][0][folio]` | NumberType | |
| `monto_{id}` | `payment_batch[rows][agreement_imed][0][amount]` | NumberType | |

### Carta Convenio LASIK — `methodCode: agreement_lasik`
| Campo legacy (Melisa) | Campo nuevo (tenant) | Tipo Symfony | Notas |
|---|---|---|---|
| `folio_{id}` | `payment_batch[rows][agreement_lasik][0][folio]` | NumberType | |
| `monto_{id}` | `payment_batch[rows][agreement_lasik][0][amount]` | NumberType | |

---

## 7. Lógica JavaScript crítica

Estas son las funciones JS que **debes reimplementar** en Stimulus. Son las que hacen que los formularios funcionen interactivamente.

### `showTablas(id, tipo)`
Muestra u oculta la tabla de un medio de pago al activar/desactivar el checkbox.
```javascript
// Lógica: si checkbox está checked → mostrar tabla; si no → ocultar y resetear campos
function showTablas(id, tipo) {
    const tabla = document.getElementById(`tabla_${id}_${tipo}`);
    const checkbox = document.getElementById(`check_${id}`);
    tabla.style.display = checkbox.checked ? 'block' : 'none';
    // Limpiar campos si se desactiva
}
```

### `NuevoFormDinamico(id, version)`
Clona una fila del formulario vía AJAX.
```javascript
// En Melisa usa jQuery + FOSJsRoutingBundle
// En Stimulus → fetch() a un endpoint que retorna un Turbo Stream o HTML parcial
function NuevoFormDinamico(id, version) {
    const url = `/payment-form/fragment?type=${id}&index=${version}`;
    fetch(url).then(r => r.text()).then(html => {
        document.getElementById(`container_${id}`).insertAdjacentHTML('beforeend', html);
    });
}
```

### `Eliminar(id, cantidad, version)`
Elimina una fila del formulario.
```javascript
function Eliminar(id, cantidad, version) {
    document.getElementById(`fila_${id}_${cantidad}`).remove();
    SumaSaldoVsMediosPago();
}
```

### `SumaSaldoVsMediosPago()`
Suma todos los montos de los medios de pago activos y actualiza el saldo restante.
```javascript
// Busca todos los inputs.saldoMedio visibles y suma sus valores
function SumaSaldoVsMediosPago() {
    let total = 0;
    document.querySelectorAll('.saldoMedio:not([disabled])').forEach(input => {
        const val = parseFloat(input.value) || 0;
        total += val;
    });
    document.getElementById('saldo-restante').textContent = totalCuenta - total;
}
```

### `ValidaRutDinamico(element)`
Valida un RUT chileno en tiempo real.
```javascript
// Al perder foco o presionar Enter → valida dígito verificador
// Muestra/oculta mensaje de error
function ValidaRutDinamico(element) {
    const rut = element.value;
    if (!validarRut(rut)) {
        element.closest('tr').querySelector('.error-rut').style.display = 'block';
    } else {
        element.closest('tr').querySelector('.error-rut').style.display = 'none';
    }
}
```

### `validaBonoInput(element)`
Valida que un número de bono no esté ya registrado en la BD.
```javascript
// AJAX a /validar-bono?numero=X → { existe: true/false }
function validaBonoInput(element) {
    fetch(`/validar-bono?numero=${element.value}`)
        .then(r => r.json())
        .then(data => {
            // Si existe → mostrar error
        });
}
```

---

## 8. Diseño objetivo para Symfony 7 + Stimulus + Turbo (actualizado)

Esta sección reemplaza la propuesta anterior y consolida el diseño acordado para el MVP.

### Principios de diseño (final)

| Decisión | Estado | Motivo |
|---|---|---|
| `PaymentMethodConfigRegistry` + handlers por método | Mantener | Evita un `MediosPagoType` monolítico y escala por estrategia |
| FormType por método (cash, credit_card, check, etc.) | Mantener | Los campos por medio son distintos |
| `PaymentBatchDTO` para lote completo | Mantener | El lote es dinámico en runtime |
| `PaymentBatchType` como FormType Symfony | Descartar | Choca con estructura variable al `handleRequest()` |
| `LegacyPaymentFieldMapper` en MVP | Descartar | YAGNI en tenant nuevo |

### Convención de nombres (obligatoria)

Todos los inputs deben publicar con esta estructura:

```txt
payment_batch[rows][{methodCode}][{index}][{field}]
```

Ejemplos:

```txt
payment_batch[rows][cash][0][amount]
payment_batch[rows][credit_card][0][voucher]
payment_batch[rows][check][2][bank_id]
```

### Punto crítico: `createNamed()` en fragmentos

Para que Symfony genere esos nombres jerárquicos (y no nombres con guiones bajos), el fragment controller debe crear el formulario con nombre explícito:

```php
$formName = sprintf('payment_batch[rows][%s][%d]', $methodCode, $index);
$form = $this->createNamed($formName, CashPaymentType::class, null, $options);
```

Sin esto, el POST no llegará con la estructura esperada para procesar el lote.

### Flujo final (MVP)

1. UI renderiza métodos habilitados con Turbo Frames por método.
2. Stimulus agrega/quita filas llamando endpoint de fragmento.
3. Endpoint crea form de fila con `createNamed(...)`.
4. POST completo llega en `payment_batch[rows]`.
5. `PaymentBatchProcessor` normaliza a DTOs y valida.
6. Admisión consume resultado normalizado, sin acoplarse a detalles de cada medio.

---

## 9. Estructura propuesta para tenant (final)

```txt
src/
├── DTO/Revenue/Payment/
│   ├── PaymentBatchDTO.php
│   └── PaymentRowDTO.php
├── Form/Revenue/Payment/Method/
│   ├── CashPaymentType.php
│   ├── CreditCardPaymentType.php
│   ├── DebitCardPaymentType.php
│   ├── CheckPaymentType.php
│   ├── ElectronicVoucherPaymentType.php
│   ├── ManualVoucherPaymentType.php
│   ├── GratuityPaymentType.php
│   └── AgreementLetterPaymentType.php
├── Service/Revenue/Payment/
│   ├── PaymentMethodConfigRegistry.php
│   ├── PaymentBatchProcessor.php
│   └── Handler/
│       ├── PaymentMethodHandlerInterface.php
│       ├── CashPaymentHandler.php
│       ├── CreditCardPaymentHandler.php
│       └── CheckPaymentHandler.php
└── Controller/Revenue/Payment/
    └── RevenuePaymentFragmentController.php

templates/revenue/payment/
├── _batch.html.twig
└── method/
    ├── _cash_row.html.twig
    ├── _credit_card_row.html.twig
    └── _check_row.html.twig

assets/controllers/revenue/
└── payment_builder_controller.js
```

Notas:
- El MVP parte con 3 métodos: `cash`, `credit_card`, `check`.
- El resto se agrega de forma mecánica usando el mismo patrón.

---

## 10. Procesamiento del POST y validación

### Lectura del request

```php
$batchData = $request->request->all('payment_batch');
$rowsByMethod = $batchData['rows'] ?? [];
```

`$rowsByMethod` tendrá la estructura:

```php
[
  'cash' => [
    0 => ['amount' => '10000'],
  ],
  'check' => [
    0 => ['check_number' => '123', 'bank_id' => '2', ...],
  ],
]
```

### Normalización

`PaymentBatchProcessor` debe:
1. Iterar por método (`cash`, `credit_card`, `check`).
2. Crear `PaymentRowDTO` por índice.
3. Aplicar normalización de montos/strings/IDs.
4. Delegar validación de negocio al handler del método.

### Validación

Dos niveles:
1. Constraints Symfony sobre DTO (campos requeridos, tipos, rangos).
2. Reglas de negocio en handlers (ej: validez de cheque, tarjeta habilitada, etc.).

---

## Resumen ejecutivo para implementar

1. Crear `PaymentMethodConfigRegistry`.
2. Implementar FormTypes de método (`cash`, `credit_card`, `check`).
3. Implementar `RevenuePaymentFragmentController` con `createNamed()`.
4. Implementar `payment_builder_controller.js` (toggle, add/remove, recalculate).
5. Implementar `PaymentBatchDTO` + `PaymentBatchProcessor`.
6. Integrar bloque `templates/revenue/payment/_batch.html.twig` en Admisión Paso 2.
