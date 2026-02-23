# Migración Caja → Tenant: Submodulo Recaudacion

## Propósito
Detalle técnico del submodulo Recaudacion para que Claude Code en Tenant
lo use como referencia al diseñar el módulo `Revenue/CashRegister/`.

**Fuente:** `src/Rebsol/RecaudacionBundle/Controller/_Default/Recaudacion/`
**Destino:** `src/Controller/Revenue/CashRegister/` (a crear en Tenant)

---

## Entrada principal del submodulo

### `DefaultController::indexAction`
**Archivo fuente:** `Controller/_Default/Recaudacion/DefaultController.php:65`
**Ruta origen:** `/Hermes/Recaudacion/Recaudacion/Recaudacion`
**Ruta destino propuesta:** `#[Route('/revenue/cash-register', name: 'app_revenue_cash_register_index')]`

**Qué hace:**
1. Verifica `EstadoApi` — si no es `core`, redirige al dashboard.
2. Valida que el usuario tenga `RelUbicacionCajero` asignado y permiso `getVerCaja()`.
3. Verifica que exista al menos un `Talonario` activo para la caja.
4. Carga medios de pago, filtrando BonoWeb según contexto (`tipoPago`).
5. Instancia 5 formularios: `BusquedaAvanzadaDirectorioPacienteType`, `MediosPagoType`, `PagoType`, `DiferenciaType`, `PrestacionType`.
6. Valida estado operativo de la caja (abierta/cerrada/sin talonario).
7. Renderiza `RecaudacionBundle:Recaudacion:Base.html.twig`.

**Parámetros de configuración que consulta:**
```
FOLIO_GLOBAL              → ¿folio global o por ubicación?
HABILITAR_PAIS_NACIONALIDAD_EXTRANJERO → extranjeros
APLICAR_DIFERENCIA_INDIVIDUAL
APLICAR_DIFERENCIA_SALDO
HABILITAR_RESTRICCIONES_DE_PAGO
IMED_URL_INTERFAZ_PROD
TIPO_FORMAS_PAGO_BONOS    → IDs de formas de pago tipo bono
```

**Entidades que consulta (HermesBundle → equivalente Tenant):**
```
RebsolHermesBundle:UsuariosRebsol        → App\Entity\Tenant\Member
RebsolHermesBundle:RelUbicacionCajero   → [no existe en Tenant aún — crear]
RebsolHermesBundle:Parametro            → parámetros de configuración del sistema
RebsolHermesBundle:Talonario            → [no existe en Tenant aún — crear]
RebsolHermesBundle:FormaPago            → App\Entity\Tenant\PaymentMethod
RebsolHermesBundle:Caja                 → [no existe en Tenant aún — crear]
RebsolHermesBundle:Origen               → origen de atención
RebsolHermesBundle:RolProfesional       → rol médico
RebsolHermesBundle:Prevision            → App\Entity\Tenant\HealthInsurance (o similar)
RebsolHermesBundle:TipoPrevision        → tipo de previsión
RebsolHermesBundle:RelSucursalPrevision → relación sucursal-financiador
RebsolHermesBundle:PrPlan               → planes de financiador
```

**Variables de sesión que inicializa (a reemplazar en Tenant):**
```php
// DefaultController arranca limpiando TODAS estas variables de sesión:
'idPacienteGarantia', 'idPnaturalMascota', 'idPnaturalCliente',
'api', 'pacienteApi', 'persona', 'garantia', 'idReservaAtencion',
'sucursal', 'financiador', 'convenio', 'plan', 'origen',
'derivadoInt', 'derivadoExt', 'ListaPrestacion', 'caja',
'vSumaCantidad', 'countPrestacionesArticulos', 'idDiferencia',
'idDiferenciaSaldo', 'idSubEmpresaItem', 'esTratamiento',
'ListaDiferenciaSaldo', 'ListaDiferencia'
```
⚠️ **Crítico para Tenant:** Este estado distribuido en sesión debe convertirse en
estado explícito manejado por Turbo Frames o datos en el formulario.

**Segunda entrada (desde Agenda):**
`DefaultController::indexPagoAction($id, $tipoPago)` — mismo archivo, línea 281.
Recibe `$id` = idReservaAtencion, precarga datos del paciente desde la reserva.

---

## Flujo completo de pago

### Paso 1: Identificación del Paciente

**Controller:** `Pago/PagoController`
**Archivo:** `Controller/_Default/Recaudacion/Pago/PagoController.php`

**Acciones AJAX (todas retornan JSON/Response):**

| Método | Descripción | Entidades |
|---|---|---|
| `consultaPacienteAction` | Busca persona por tipoIdentificacion+identificacion | `Persona`, `Pnatural` |
| `consulaPlanesAction` | Lista planes por financiador+sucursal | `Prevision`, `PrPlan` (via servicio `Caja_valida`) |
| `consulaProfesionalesAction` | Lista profesionales por sucursal | `UsuariosRebsol`, `RolProfesional` |
| `consulaOrigenAction` | Lista orígenes por sucursal | `Origen` |
| `actualizaPacienteApi1Action` | Actualiza contacto/domicilio paciente | `Persona`, `PersonaDomicilio` |
| `ActualizaPacienteAction` | Igual, versión alternativa | `Persona`, `PersonaDomicilio`, `ReservaAtencion` |
| `actualizaPacienteSinRutAction` | Crea/actualiza paciente extranjero | `Persona`, `Pnatural`, `PersonaDomicilio`, `TipoIdentificacionExtranjero` |
| `creaPacienteAction` | Crea persona nueva | via `recaudacion.RegistroPersona` service |
| `diferenciaAction` | Renderiza formulario diferencia inline | `AccionClinica`, `DiferenciaType` |
| `consultaMotivoPorTipoAction` | Listado motivos diferencia | `MotivoDiferencia` |
| `busquedaExternoAction` | Busca derivador externo por RUT | `DerivadorExterno` |
| `obtieneExternoAction` | Datos de un derivador externo | `DerivadorExterno` |

⚠️ **Para Tenant:** Estos endpoints AJAX se convierten en acciones de Stimulus controllers
que hacen fetch a rutas de la API. El estado del paciente identificado se mantiene
en el formulario/Turbo Frame, no en sesión.

### Paso 2: Selección de Medio de Pago

**Controller:** `Pago/MedioPagoController`
**Archivo:** `Controller/_Default/Recaudacion/Pago/MedioPagoController.php`

**Lógica central — `nuevoFormDinamicoAction`:**
Recibe `idMedioPago` y `cantidad` por AJAX.
Consulta `FormaPagoTipo.id` del medio seleccionado.
Renderiza template específico según tipo:

| `FormaPagoTipo.id` | Template renderizado |
|---|---|
| 3 | `RecaudacionBundle:FormasDePago:FormaDePago_BonoElectronico.html.twig` |
| 5 | `RecaudacionBundle:FormasDePago:FormaDePago_BonoManual.html.twig` |
| (otros) | Templates específicos por tipo de pago |

**Tipos de FormasDePago conocidos (del directorio de vistas):**
```
FormaDePago_Efectivo.html.twig
FormaDePago_TarjetaCredito.html.twig
FormaDePago_TarjetaDebito.html.twig
FormaDePago_Transbank.html.twig
FormaDePago_ChequeDia.html.twig
FormaDePago_ChequeFecha.html.twig
FormaDePago_BonoElectronico.html.twig
FormaDePago_BonoManual.html.twig
FormaDePago_BonoWeb.html.twig
FormaDePago_Gratuidad.html.twig
OtrosMedios_*.html.twig (varios)
```

**Mapeo con Tenant:**
Este switch es exactamente el patrón `PaymentMethodConfigRegistry` ya implementado en Tenant.
En Tenant: `Registry.get('credit_card')` → `{form_type, row_template}`.
La migración implica ampliar el registry con todos los tipos de la tabla anterior.

**Otros métodos importantes de MedioPagoController:**
- `asignarMediosPagoAction` — guarda medios de pago en sesión con montos
- `cancelarMediosPagoAction` — elimina un medio de pago de la lista
- `recalcularAction` — recalcula totales
- `voucherBonoWebAction` — genera voucher BonoWeb (integración SNABB)
- `reenviarEmailBonoWebAction` — reenvío email voucher BonoWeb

### Paso 3: Confirmar Pago

**Controller:** `Pago/PagarController`
**Archivo:** `Controller/_Default/Recaudacion/Pago/PagarController.php`

**Sesiones que recibe del paso anterior:**
```php
$this->getSession('persona')      // idPnatural del paciente
$this->getSession('sucursal')
$this->getSession('financiador')
$this->getSession('convenio')
$this->getSession('plan')
$this->getSession('origen')
$this->getSession('derivadoInt')
$this->getSession('ListaPrestacion')
$this->getSession('caja')
$this->getSession('vSumaCantidad')
$this->getSession('idDiferencia')
$this->getSession('idDiferenciaSaldo')
$this->getSession('tipoPago')
```

**Método `setSessionCajaPagarAction`** (AJAX, guarda sesión antes del pago):
Recibe via AJAX: `idPaciente`, `idPrevision`, `idConvenio`, `tipoPago`,
`inputIdDatoIngreso`, `inputMonto`, `formulariotypeCantidadArticulos`, `idPrePagoCuenta`.

**Entidades que crea al pagar:**
```
HermesBundle:CuentaPaciente           → PatientAccount (ya existe en Tenant)
HermesBundle:PagoCuenta               → PaymentAccount (ya existe en Tenant)
HermesBundle:PagoCuentaDetalle        → detalle por medio de pago
HermesBundle:DetallePagoCuenta        → detalle adicional
HermesBundle:DetalleTalonario         → consume folio del talonario
HermesBundle:DocumentoPago            → documento asociado al pago
HermesBundle:DetalleDocumentoPago     → items del documento
HermesBundle:AccionClinicaPaciente    → prestaciones cobradas al paciente
HermesBundle:Paciente                 → crea/actualiza registro paciente
HermesBundle:PrevisionPnatural        → financiador del paciente
HermesBundle:ReservaAtencionLog       → log de cambio de estado en reserva
HermesBundle:CuentaPacienteLog        → log de cambios en cuenta
```

**Integración DTE (facturación electrónica):**
```php
use Rebsol\DteBundle\Services\Dte\ConexionAdecom\Constant\ConexionAdecomConstant;
use Rebsol\DteBundle\Services\DteAces\ConexionAces\Constant\ConexionAcesConstant;
```
⚠️ `DteBundle` no existe en Tenant. Esta integración debe evaluarse por separado.

**Método `personaAction`** — busca PNatural por RUT (deprecated, pero aún en rutas).

### Paso 4: Post-pago

**Controller:** `Pago/PostPagoController`
**Archivo:** `Controller/_Default/Recaudacion/Pago/PostPagoController.php`

**Método `indexAction`:**
- Lee `Pnatural` de sesión.
- Carga historial de pagos del paciente.
- Renderiza `RecaudacionBundle:Recaudacion/PostPago:Base.html.twig`.

**Método `historialPacienteAction`:**
- Carga 4 tipos de histórico en paralelo:
  - Pagos realizados (`GetPagosHistoricos`)
  - Garantías (`historicoDesdeListadoPacienteGarantia`)
  - Reservas impagas (`GetReservasInpagoHistoricos`)
  - Tratamientos (`GetTratamientosHistoricos`)
- Renderiza `RecaudacionBundle:Recaudacion/PostPago:Base.html.twig` con todo.

**Otros métodos del PostPago (de la firma y los templates conocidos):**
- Generar/imprimir boleta
- Anular pago/boleta
- Regularizar pago
- Reimprimir documentos
- Historial de garantías
- Ver detalle de pago específico

**Entidades que lee:**
```
HermesBundle:Pnatural              → App\Entity\Tenant\Patient (persona natural)
HermesBundle:AccionClinicaPacienteLog
HermesBundle:ArticuloPacienteLog
HermesBundle:CuentaPacienteLog
HermesBundle:DetalleTalonario
HermesBundle:ReservaAtencionLog
```

**Templates PostPago:**
```
RecaudacionBundle:Recaudacion/PostPago:Base.html.twig
RecaudacionBundle:Recaudacion/PostPago:Exitoso.html.twig
RecaudacionBundle:Recaudacion/PostPago:ExitosoInfoAnterior.html.twig
```

### Paso especial: Gestión de Caja

**Controller:** `GestionCaja/GestionCajaController`
**Archivo:** `Controller/_Default/Recaudacion/GestionCaja/GestionCajaController.php`

**Métodos:**

| Método | Descripción | Entidades |
|---|---|---|
| `gestionAbrirCajaAction` | Crea registro `Caja` con fecha actual | `Caja` (nueva), `RelUbicacionCajero` |
| `gestionCerrarCajaAction($id)` | Muestra formulario cierre con montos | `Caja`, `FormaPagoTipo`, `CerrarCajaType` |
| `gestionCerrarCajaCerradoAction($id)` | Persiste cierre | `Caja`, `DetalleCaja`, `DetalleCajaCheque` |
| `gestionInformeCajaAction($id)` | Informe HTML caja | `rCaja().GetCajasInforme()` |
| `gestionInformeCajaImprimirAction` | PDF del informe | `knp_snappy.pdf` |
| `gestionInformeCajaPagosWebAction` | Informe pagos web | específico |
| `traeCorrelativoAction` | Carga talonarios en sesión | `Caja`, `Talonario` |

**Estructura de la entidad `Caja` (origen):**
```
idUsuario             → FK Member
idUbicacionCajero     → FK RelUbicacionCajero
idSucursal            → FK Branch
fechaApertura         → DateTime
fechaCierre           → DateTime (nullable)
montoInicial          → decimal
montoReal             → decimal (al cierre)
superavit             → decimal
deficit               → decimal
idEstadoReapertura    → FK EstadoReapertura
fechaReapertura       → DateTime (nullable)
```

**Estructura de `DetalleCaja` (origen):**
```
idCaja     → FK Caja
idFormaPago → FK FormaPago
idBanco    → FK Banco (nullable, para cheques)
monto      → decimal
numeroDeposito → string
idEstado   → FK Estado
```

### Paso especial: Diferencias / Descuentos

**Controller:** `Diferencia/DiferenciaController`
**Archivo:** `Controller/_Default/Recaudacion/Diferencia/DiferenciaController.php`

**Flujo de negocio:**
1. Cajero detecta discrepancia → `generarDiferenciaAction` (muestra formulario)
2. Cajero solicita diferencia → `solicitarDiferenciaAction`
3. Si monto > `MONTO_MAXIMO_DIFERENCIA` → requiere autorización supervisor
4. Supervisor aprueba/rechaza desde su módulo
5. Cajero consulta estado → `respuestaSupervisorDiferenciaAction` (polling)
6. Cajero puede cancelar → `anularDiferenciaAction`

**Métodos:**

| Método | Descripción |
|---|---|
| `generarDiferenciaAction` | Renderiza form diferencia con lista prestaciones |
| `generarDiferenciaSaldoAction` | Diferencia sobre saldo (no sobre prestaciones) |
| `getMotivosDiferenciaAction` | AJAX: motivos por tipo diferencia |
| `getTipoSentidoAction` | AJAX: sentido (cargo/abono) |
| `getTipoSentidoPorMotivoDiferenciaAction` | AJAX: sentido por motivo |
| `countPrestacionesArticulosAction` | Guarda lista en sesión |
| `solicitarDiferenciaAction` | Crea entidad `Diferencia` |
| `solicitarDiferenciaSaldoAction` | Crea diferencia sobre saldo |
| `anularDiferenciaAction` | Cancela diferencia |
| `respuestaSupervisorDiferenciaAction` | Polling del estado (sync supervisor) |

**Estructura entidad `Diferencia` (origen):**
```
idUsuarioSolicitud     → FK Member (cajero)
idMotivoDiferencia     → FK MotivoDiferencia
fechaSolicitud         → DateTime
totalCuenta            → decimal
totalDescuento         → decimal
totalCuentaConDescuento → decimal
idEstadoDiferencia     → FK EstadoDiferencia
idUsuarioAutorizacion  → FK Member (supervisor) nullable
fechaAutorizacion      → DateTime nullable
idUsuarioAnulacion     → FK Member nullable
fechaAnulacion         → DateTime nullable
```

**Estados de diferencia (parámetros):**
```
EstadoDiferencia.cajeroPideAutorizacion          → espera supervisor
EstadoDiferencia.descuentoNoRequiereAutorizacion → auto-aprobada
DiferenciacajeroCancelaSolicitud                 → anulada por cajero
```

---

## JS Assets del submodulo Recaudacion

Los archivos JS en `Resources/public/js/` se organizan por dominio.
En Tenant todos estos deben convertirse en **Stimulus controllers**.

### PagoPaciente (el núcleo — 8 archivos JS)
```
PagoPaciente/mediosDePago.js              → Stimulus: medio-pago-controller
PagoPaciente/informacionPagoPaciente.js   → Stimulus: pago-info-controller
PagoPaciente/tablaListaServicioPago.js    → Stimulus: servicios-tabla-controller
PagoPaciente/botonesListaServicioPago.js  → Stimulus: servicios-botones-controller
PagoPaciente/tablaPagoCuentaPaciente.js   → Stimulus: cuenta-paciente-controller
PagoPaciente/tablaPagoCuentaTutor.js      → Stimulus: cuenta-tutor-controller
PagoPaciente/resguardoFinancieroPaciente.js → Stimulus: resguardo-controller
PagoPaciente/servicioPaquetizadoPaciente.js → Stimulus: paquete-controller
```

### BusquedaPaciente (3 archivos JS)
```
BusquedaPaciente/busquedaPaciente.js      → Stimulus: patient-search-controller
BusquedaPaciente/busquedaBasica.js        → parte del mismo controller
BusquedaPaciente/busquedaAvanzada.js      → parte del mismo controller (modal)
```

### InformacionHistoricaPaciente (6 archivos JS)
```
InformacionHistoricaPaciente/listadoPagosPaciente.js        → Stimulus: historial-pagos-controller
InformacionHistoricaPaciente/listadoGarantiasPaciente.js    → Stimulus: historial-garantias-controller
InformacionHistoricaPaciente/listadoReservasPendientesPaciente.js → Stimulus: reservas-pendientes-controller
InformacionHistoricaPaciente/listadoHistoricoPaciente.js    → Stimulus: historial-general-controller
InformacionHistoricaPaciente/listadoSolicitudesImgLabPaciente.js → Stimulus: solicitudes-controller
InformacionHistoricaPaciente/listadoTratamientosPaciente.js → Stimulus: tratamientos-controller
```

### TratamientoPaciente (4 archivos JS)
```
TratamientoPaciente/formularioCrearTratamientoPaciente.js         → Stimulus: crear-tratamiento-controller
TratamientoPaciente/formularioAgregarItemsTratamientoPaciente.js  → Stimulus: agregar-items-controller
TratamientoPaciente/formularioProfesionalTratamientoPaciente.js   → Stimulus: profesional-controller
TratamientoPaciente/tablaResultadosAgregarItemsTratamientoPaciente.js → Stimulus: resultados-tabla-controller
```

### GarantiaPaciente (2 archivos JS)
```
GarantiaPaciente/regularizarGarantiaPaciente.js → Stimulus: regularizar-garantia-controller
GarantiaPaciente/resumenGarantiaPaciente.js     → Stimulus: resumen-garantia-controller
```

### PagoCuenta específico (1 archivo JS)
```
_default/pago.cuenta/pago.cuenta.js → Stimulus: pago-cuenta-controller
```

### Descartados (Farmacia — routing comentado)
```
_default/farmacia/_default.farmacia.js                 → NO MIGRAR
_default/farmacia/pasos.pago.detalle.receta.articulo.js → NO MIGRAR
_default/farmacia/listado.detalle.receta.articulo.js    → NO MIGRAR
_default/farmacia/table.detalle.receta.articulo.js      → NO MIGRAR
```

---

## Estructura propuesta en Tenant para Revenue/CashRegister

```
src/Controller/Revenue/CashRegister/
├── CashRegisterController.php          # Entrada principal (indexAction)
├── PatientSearchController.php         # Búsqueda paciente (AJAX/Turbo)
├── PaymentController.php               # Orquestación pago (setSession → datos form)
├── PaymentConfirmController.php        # Confirmar pago → persistir
├── PostPaymentController.php           # Post-pago: resumen, historial
├── CashOpeningController.php           # Apertura caja
├── CashClosingController.php           # Cierre caja + formulario
├── DifferenceController.php            # Diferencias/descuentos

src/Entity/Tenant/
├── CashRegister.php                    # Entidad Caja (apertura/cierre)
├── CashRegisterDetail.php              # DetalleCaja (por forma de pago al cierre)
├── CashRegisterCheckDetail.php         # DetalleCajaCheque
├── Voucher.php                         # Talonario (folio/boleta stock)
├── VoucherEntry.php                    # DetalleTalonario (consumo por pago)
├── Difference.php                      # Diferencia (descuento solicitado)
├── DifferenceReason.php                # MotivoDiferencia (ya existe como mantenedor Treasury)
├── ClinicalAction.php                  # AccionClinica (prestación)
├── ClinicalActionPatient.php           # AccionClinicaPaciente (prestación cobrada)

src/Service/Revenue/CashRegister/
├── CashOpeningService.php
├── CashClosingService.php
├── DifferenceService.php
├── PatientSearchService.php
├── PaymentProcessorService.php         # Orquesta el pago completo

assets/controllers/revenue/
├── cash-register-controller.js         # Controller Stimulus principal
├── patient-search-controller.js
├── payment-method-selector-controller.js
├── difference-controller.js
├── cash-close-controller.js

templates/revenue/cash-register/
├── index.html.twig                     # Vista principal de caja
├── _patient_search.html.twig           # Turbo Frame búsqueda
├── _payment_panel.html.twig            # Turbo Frame panel de pago
├── _post_payment.html.twig             # Turbo Frame post-pago
├── cash_open.html.twig
├── cash_close.html.twig
├── difference/
│   ├── _form.html.twig
│   └── _status.html.twig
```

---

## Datos críticos para el diseño de la entidad `Caja`

La entidad `Caja` en origen tiene una relación compleja con el ciclo de apertura/cierre:
- Un usuario puede tener múltiples `Caja` (una por día).
- Se busca la caja del día actual: `GetCajaByUser(idUser, Fecha)`.
- Si no existe para hoy pero existe una abierta de días anteriores → estado `sincerrar`.
- El estado `EstadoReapertura` permite al supervisor reabrir cajas cerradas.
- `ValidacionComplementariaCaja` (método ~150 líneas) concentra toda la lógica de estado.

**Estados de caja que deben existir:**
```
sin_cerrar     → caja de días anteriores nunca cerrada (error)
abierta        → caja de hoy abierta
cerrada        → caja de hoy cerrada
sin_talonario  → no tiene talonarios activos
```

---

## Flujo de medios de pago (mapeo tipos)

Los tipos de `FormaPagoTipo` en origen a los códigos del Registry en Tenant:

| id (origen) | Nombre | Código Registry Tenant | ¿Existe? |
|---|---|---|---|
| 1 | Efectivo | `cash` | ✅ ya existe |
| 2 | Tarjeta Débito | `debit_card` | ❌ crear |
| 3 | Bono Electrónico | `electronic_voucher` | ❌ crear |
| 4 | Tarjeta Crédito | `credit_card` | ✅ ya existe |
| 5 | Bono Manual | `manual_voucher` | ❌ crear |
| 6 | BonoWeb (FONASA) | `bonoweb` | ❌ crear (fase 2) |
| 7 | Transferencia | `bank_transfer` | ❌ crear |
| 8 | Cheque al Día | `check` | ✅ ya existe |
| 9 | Gratuidad | `gratuity` | ❌ comentario en código: "queda fuera por ahora" |
| (otros) | OtrosMedios | `other_*` | ❌ evaluar |

---

## Notas de arquitectura para Tenant

### Eliminación del estado en sesión
El patrón origen hace:
```php
// Paso 1: guarda en sesión
$this->setSession('financiador', $financiador);
// Paso N: lee de sesión
$financiador = $this->getSession('financiador');
```

En Tenant con Turbo Frames, el estado debe vivir en:
- El formulario (campos hidden) para datos del paciente/pago
- La URL para el estado de navegación (apertura/cierre caja)
- La BD para el estado real (Caja abierta/cerrada)
- Solo `id_caja_hoy` en sesión (mínimo necesario)

### Polling de diferencias
Actualmente `respuestaSupervisorDiferenciaAction` se llama periódicamente por JS.
En Tenant: usar Turbo Streams o Mercure para push en tiempo real.
O simplemente un botón "¿Ya está aprobado?" que hace un Turbo Frame refresh.

### Formulario de pago como SPA liviana
La vista `Base.html.twig` (origen) es básicamente una mini-SPA con jQuery.
En Tenant: la vista principal de caja debe ser un Turbo Frame que carga paneles
dinámicamente al seleccionar paciente, prestaciones, medios de pago.
Cada panel es un Turbo Frame independiente.
