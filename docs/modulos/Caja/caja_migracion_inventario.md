# Migración Caja → Tenant: Inventario General

## Propósito de este documento
Guía para la instancia Claude Code en `/var/www/html/tenant` que debe diseñar y construir
el módulo Caja en Symfony 7.4 + Stimulus + Turbo.

Este documento describe:
1. Qué existe en la fuente (`RecaudacionBundle`) y cuál es su estado real.
2. Qué ya existe en Tenant que mapea a conceptos de Caja.
3. Qué hay que crear nuevo.
4. Qué se descarta definitivamente.

Documentos complementarios:
- `docs/caja_migracion_recaudacion.md` — Submodulo Recaudacion (el más complejo)
- `docs/caja_migracion_pagocuenta.md` — Submodulo PagoCuenta (4 endpoints de consulta)
- `docs/caja_migracion_supervisor.md` — Submodulo Supervisor

---

## URLs de producción (melisaupgrade.prod)

| Módulo | URL real | Nota |
|---|---|---|
| Entrada Caja (dashboard) | `http://melisaupgrade.prod/app_dev.php/Hermes/Recaudacion/` | Punto de entrada general |
| Recaudacion (caja directa) | `http://melisaupgrade.prod/app_dev.php/Hermes/Recaudacion/Recaudacion/Recaudacion?tipoPago=Recaudacion` | Flujo sin reserva previa — SIN BonoWeb |
| PagoCuenta (desde admisión) | `http://melisaupgrade.prod/app_dev.php/Hermes/Recaudacion/Recaudacion/Recaudacion?tipoPago=PagoCuenta` | Flujo desde cuenta paciente — CON BonoWeb |
| Supervisor | `http://melisaupgrade.prod/app_dev.php/Hermes/Recaudacion/Supervisor/Inicio` | Panel de administración |

### Observación crítica sobre `tipoPago`
El parámetro `tipoPago` en la URL es el discriminador principal del flujo:
- `tipoPago=Recaudacion` → cajero inicia pago directo. **Sin BonoWeb** (no hay datos de profesional disponibles).
- `tipoPago=PagoCuenta` → viene desde Admisión con datos de ingreso. **Con BonoWeb** habilitado.
- Sin `tipoPago` → flujo desde agenda (vía `indexPagoAction` con `$id` de reserva). **Con BonoWeb**.

Esto está codificado en `DefaultController::indexAction:154`:
```php
$permitirBonoWeb = ($tipoPago === 'PagoCuenta');
```

En Tenant, esta distinción debe expresarse claramente en la arquitectura,
no como un parámetro de URL sino como rutas/contextos distintos.

---

## Fuente: RecaudacionBundle

**Ubicación:** `/var/www/html/melisa_prod/src/Rebsol/RecaudacionBundle`
**Ruta base en prod:** `/Hermes/Recaudacion`

### Los tres submodulos activos

| Submodulo | Ruta base | Controllers activos | Estado |
|---|---|---|---|
| Recaudacion | `/Hermes/Recaudacion/Recaudacion/` | `_Default/Recaudacion/` (13 controllers) | ✅ Activo — nucleo del negocio |
| Supervisor | `/Hermes/Recaudacion/Supervisor/` | `_Default/Supervisor/` (38 controllers) | ✅ Activo — administracion |
| PagoCuenta | `/Hermes/Recaudacion/PagoCuenta/` | `Api/Unab/PagoCuenta/CuentaPacienteController` | ⚠️ Parcial — 4 endpoints activos |

### Código descartable (no migrar)

| Componente | Ubicación | Por qué se descarta |
|---|---|---|
| `Controller/` raíz (6 archivos) | `Controller/*.php` | Legacy/delegados. El de 43.8KB es la implementación original pre-_Default |
| `Controller/PagoCuenta/` (3 archivos) | `Controller/PagoCuenta/` | Duplicado legacy de `Api/Unab/PagoCuenta/` |
| `Controller/Api/Caja/Recaudacion/` | `Controller/Api/Caja/` | Routing no lo carga como principal |
| `Controller/Api/Unab/PagoCuenta/PagoCuentaController.php` | mismo | Rutas comentadas, sin callable real |
| Supervisor/PeopleSoft | routing comentado | Referencia `CajaBundle`, desactivado |
| `_default/farmacia/` JS | `public/js/_default/farmacia/` | Farmacia routing comentado |
| 40+ rutas comentadas en routing.yml raíz | `routing.yml` | Todo bloque de rutas `#comentado` |
| 30+ rutas comentadas en pagoCuenta.yml | `Api/Unab/PagoCuenta/pagoCuenta.yml` | Todo lo comentado |

---

## Destino: Tenant

**Ubicación:** `/var/www/html/tenant`
**Namespace:** `App\`
**Stack:** Symfony 7.4, Stimulus, Turbo, Webpack Encore, Bootstrap 5.3.6
**Routing:** PHP Attributes `#[Route()]` — NO YAML
**Entity Managers:** `main` (BD central) y `tenant` (BD del cliente activo)
**Todas las entidades de negocio** → EM `tenant` (`App\Entity\Tenant\`)

### Módulo Revenue ya iniciado

El namespace destino para Caja es `Revenue/`:

```
src/Controller/Revenue/Payment/RevenuePaymentFragmentController.php  ← ya existe
src/Service/Revenue/Payment/PaymentMethodConfigRegistry.php           ← ya existe
src/Service/Revenue/Payment/PaymentBatchProcessor.php                 ← ya existe
src/Service/Revenue/Payment/Handler/PaymentMethodHandlerInterface.php ← ya existe
src/Service/Revenue/Payment/Handler/CashPaymentHandler.php            ← ya existe
src/Service/Revenue/Payment/Handler/CheckPaymentHandler.php           ← ya existe
src/Service/Revenue/Payment/Handler/CreditCardPaymentHandler.php      ← ya existe
src/Form/Revenue/Payment/Method/CashPaymentType.php                   ← ya existe
src/Form/Revenue/Payment/Method/CheckPaymentType.php                  ← ya existe
src/Form/Revenue/Payment/Method/CreditCardPaymentType.php             ← ya existe
src/DTO/Revenue/Payment/PaymentBatchDTO.php                           ← ya existe
src/DTO/Revenue/Payment/PaymentRowDTO.php                             ← ya existe
templates/revenue/payment/method/_cash_row.html.twig                  ← ya existe
templates/revenue/payment/method/_credit_card_row.html.twig           ← ya existe
templates/revenue/payment/method/_check_row.html.twig                 ← ya existe
```

El patrón ya definido: **Registry → Config → FormType → Template → Handler**
- `PaymentMethodConfigRegistry` mapea código → `{label, form_type, row_template, max_rows}`
- MVP actual cubre: `cash`, `credit_card`, `check`
- Hay comentario explícito: "Gratuity queda fuera por ahora porque no existe la entidad GratuityCause en tenant"

### Entidades de Tenant que ya mapean a conceptos de Caja

| Entidad Tenant | Equivalente en HermesBundle | Notas |
|---|---|---|
| `App\Entity\Tenant\CashRegisterLocation` | `UbicacionCaja` | Ya existe como mantenedor Treasury |
| `App\Entity\Tenant\PatientAccount` | `CuentaPaciente` | Cuenta maestra de facturación por ingreso |
| `App\Entity\Tenant\PaymentAccount` | `PagoCuenta` | Pago individual por cuenta |
| `App\Entity\Tenant\PaymentMethod` | `FormaPago` | Métodos de pago |
| `App\Entity\Tenant\PaymentMethodType` | `FormaPagoTipo` | Tipos de métodos |
| `App\Entity\Tenant\PaymentStatus` | `EstadoPago` | Estados de pago |
| `App\Entity\Tenant\AccountStatus` | `EstadoCuenta` | Estados de cuenta |
| `App\Entity\Tenant\Bank` | `Banco` | Bancos |
| `App\Entity\Tenant\BankAccount` | `BankAccount` | Cuentas bancarias |
| `App\Entity\Tenant\CreditCard` | `TarjetaCredito` | Tarjetas |
| `App\Entity\Tenant\AdmissionRecord` | `DatoIngreso` / `ReservaAtencion` | Registro de ingreso |
| `App\Entity\Tenant\Patient` | `Paciente` + `Pnatural` | Paciente |
| `App\Entity\Tenant\Member` | `UsuariosRebsol` | Usuario/cajero |

### Patrones a seguir en Tenant

**Para mantenedores CRUD simples** → extender `AbstractMantenedorController`:
```
src/Controller/Maintainers/Treasury/CashRegisterLocationController.php  ← ejemplo ya hecho
```

**Para módulos operacionales** → patrón Registry + Handler + DTO:
```
src/Controller/Revenue/Payment/RevenuePaymentFragmentController.php  ← ejemplo ya hecho
```

**Base de controllers operacionales** → extender `AbstractTenantAwareController`.

---

## Mapa de migración por submodulo

### Submodulo Recaudacion → `Revenue/CashRegister/`

**Ver:** `docs/caja_migracion_recaudacion.md`

Flujo de negocio a implementar:
1. Apertura de caja (`GestionCaja.gestionAbrirCaja`)
2. Búsqueda/identificación de paciente (`Pago.*`)
3. Selección de prestaciones + monto
4. Selección de medio(s) de pago (`MedioPago.*`)
5. Confirmar pago (`Pagar.*`)
6. Post-pago: boleta/DTE, historial (`PostPago.*`)
7. Diferencias/descuentos (`Diferencia.*`)
8. Cierre de caja (`GestionCaja.gestionCerrarCaja`)

Entidades nuevas a crear en Tenant (no existen aún):
- `Caja` (apertura/cierre por usuario-fecha)
- `DetalleCaja` (desglose por forma de pago al cierre)
- `DetalleCajaCheque` (cheques en cierre)
- `Talonario` / `DetalleTalonario` (folios de boletas)
- `Diferencia` (descuentos solicitados/autorizados)
- `AccionClinica` / `AccionClinicaPaciente` (prestaciones cobradas)

### Submodulo PagoCuenta → `Revenue/PatientAccount/`

4 endpoints activos, todos de consulta/soporte:

| Ruta origen | URL | Controller origen | Función |
|---|---|---|---|
| `Caja_PagoCuenta_ConsultarDatos_CuentaPaciente` | `/PagoCuenta/CuentaPaciente` | `CuentaPacienteController::mostrarCuentaAction` | Muestra cuenta titular + tutor |
| `Caja_PagoCuenta_Prevision_CuentaPaciente` | `/PagoCuenta/PrevisionPaciente` | `CuentaPacienteController::mostrarFinanciadorPacienteAction` | Carga financiador para pago |
| `Caja_PagoCuenta_ConsultarDatos_CuentaPaciente_InsertarReguardoFinanciero` | `/PagoCuenta/insertarReguardoFinanciero` | `CuentaPacienteController::insertarReguardoFinancieroAction` | Vista resguardo financiero |
| `Caja_PagoCuenta_VerificarPagosPendientes_CuentaPaciente` | `/PagoCuenta/VerificarPagosPendientes` | `CuentaPacienteController::verificarPagosPendientesAction` | Devuelve `true/false` texto |

Estos 4 endpoints son soporte para el flujo de Recaudacion y Admision.
En Tenant deben ser rutas bajo `Revenue/PatientAccount/` o `Admission/`.
Los datos vienen de `PatientAccount` + `PaymentAccount` + estados de cuenta.

Servicios origen que consumen:
- `recaudacion.CuentaPaciente` → obtiene cuenta por idPnatural
- `cuentaPaciente.cuentaPaciente` → obtiene datos de admisión
- `cuentaPaciente.DatoIngreso` → obtiene admisiones por idPersona

### Submodulo Supervisor → `Revenue/Supervisor/` + `Maintainers/Treasury/`

**Ver:** `docs/caja_migracion_supervisor.md`

| Dominio Supervisor | Tipo | Estado en Tenant |
|---|---|---|
| UbicacionCaja | Mantenedor CRUD | ✅ `CashRegisterLocationController` ya existe |
| UbicacionCajero | Mantenedor CRUD | ⚠️ Parcial — entidad existe, falta RelUbicacionCajero |
| CorrelativoBoletas | Mantenedor CRUD | ❌ No existe — depende de `Talonario` |
| MantenedorFolios | CRUD + ciclo vida | ❌ No existe |
| AutorizacionDescuentos | Flujo aprobación | ❌ No existe — depende de `Diferencia` |
| ConsolidadoCaja | Informe complejo | ❌ No existe — el más complejo |
| AsientoContable | Informe + descarga | ❌ No existe |
| ReporteProduccion | Consulta + descarga | ❌ No existe |
| ApoyoFacturacion | Consulta + informe | ❌ No existe |
| ConsolidadoPorProfesional (ApiPV) | Informe PDF | ❌ No existe |

---

## Dependencias técnicas críticas a resolver en Tenant

### 1. Estado de sesión vs estado explícito
El sistema origen usa **sesión PHP extensivamente** para pasar estado entre pasos:
```php
// Ejemplo real de DefaultController
$this->setSession('sucursal', $sucursal);
$this->setSession('financiador', $financiador);
$this->setSession('ListaPrestacion', $arrPrestaciones);
$this->setSession('caja', $oCaja);
// ... ~20 variables de sesión más
```
En Tenant con Turbo, esto debe reemplazarse por:
- Turbo Frames con datos embebidos en el DOM
- O estado explícito en URLs/formularios
- O minimal session solo para `idCaja` activa del usuario

### 2. PDF generation
El sistema origen usa `knp_snappy.pdf` (wkhtmltopdf):
```php
return new Response($this->get('knp_snappy.pdf')->getOutputFromHtml($html, [...]), 200, [...]);
```
En Tenant: verificar si hay alternativa disponible (`dompdf`, `mpdf`, `Gotenberg`).

### 3. Auditoría (Gedmo Loggable)
El Supervisor usa Gedmo Loggable para trazabilidad:
```php
$em->getRepository('Gedmo\Loggable\Entity\LogEntry');
```
Tenant tiene `AuditableTrait` con `createdAt`/`updatedAt`. Verificar si alcanza o si se necesita log entry completo.

### 4. DTE (Documentos Tributarios Electrónicos)
`PagarController` importa `DteBundle` y constantes `ConexionAces`/`ConexionAdecom`.
```php
use Rebsol\DteBundle\Services\Dte\ConexionAdecom\Constant\ConexionAdecomConstant;
use Rebsol\DteBundle\Services\DteAces\ConexionAces\Constant\ConexionAcesConstant;
```
Este bundle no existe en Tenant. Es la integración de facturación electrónica SII.
**Decisión requerida:** ¿se migra DteBundle al nuevo proyecto o se conecta via API externa?

### 5. BonoWeb/FONASA
7 servicios específicos en `Services/BonoWebFonasa/`:
- Integración con SNABB (sistema FONASA bonos web)
- Cliente HTTP Guzzle + emails
- Dependencias externas concretas
**Decisión requerida:** ¿se incluye en MVP o se deja para fase 2?

### 6. IMED
`DefaultController` y `PostPagoController` tienen referencias a URL `IMED_URL_INTERFAZ_PROD`.
Es una integración con sistema de imágenes médicas externo.
**Decisión requerida:** igual que BonoWeb.

### 7. Multi-empresa / SubEmpresa
El sistema origen maneja `SubEmpresa` y `SubEmpresaTalonario`.
En Tenant la arquitectura es multi-tenant (un cliente = una BD).
Verificar si el concepto de SubEmpresa existe en las entidades de Tenant.

---

## Resumen ejecutivo para el arquitecto de Tenant

**Lo que ya está listo en Tenant para Caja:**
- Patrón de mantenedores (`AbstractMantenedorController`)
- CashRegisterLocation (UbicacionCaja) como mantenedor
- PaymentMethod, Bank, CreditCard como mantenedores Treasury
- Patrón Registry+Handler para medios de pago
- 3 handlers base: cash, credit_card, check
- Entidades PatientAccount, PaymentAccount ya mapeadas

**Lo que hay que diseñar/construir:**
1. Entidades: `Caja`, `DetalleCaja`, `Talonario`, `DetalleTalonario`, `Diferencia`, `AccionClinicaPaciente`
2. Flujo operativo de caja (apertura → pago → cierre)
3. Ampliar Registry con: debit_card, electronic_voucher, manual_voucher, bank_transfer, etc.
4. Supervisor: AutorizacionDescuentos, ConsolidadoCaja, MantenedorFolios
5. 31 archivos JS → refactorizar a controllers Stimulus

**Orden sugerido para el bosquejo:**
1. Entidades nuevas (Caja, Talonario, Diferencia) — sin esto nada funciona
2. GestionCaja (apertura/cierre) — es la puerta de entrada
3. PatientAccount endpoints (son simples, 4 rutas)
4. MedioPago: ampliar Registry con los tipos que faltan
5. Flujo completo Pago → Pagar → PostPago
6. Supervisor CRUD (en paralelo, son independientes)
7. Supervisor informes complejos (ConsolidadoCaja)
8. PDF, DTE, BonoWeb (fase 2 o según decisión)
