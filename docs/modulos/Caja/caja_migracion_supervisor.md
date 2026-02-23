# Migración Caja → Tenant: Submodulo Supervisor

## Propósito
Detalle técnico del submodulo Supervisor para que Claude Code en Tenant
lo use como referencia al diseñar el módulo `Revenue/Supervisor/` y los mantenedores
Treasury relacionados.

**Fuente:** `src/Rebsol/RecaudacionBundle/Controller/_Default/Supervisor/`
**Destino:** `src/Controller/Revenue/Supervisor/` + `src/Controller/Maintainers/Treasury/`

---

## Entrada principal

### `SupervisorController::indexAction`
**Archivo fuente:** `Controller/_Default/Supervisor/SupervisorController.php:28`
**Ruta origen:** `/Hermes/Recaudacion/Supervisor/Inicio`
**Ruta destino propuesta:** `#[Route('/revenue/supervisor', name: 'app_revenue_supervisor_index')]`

**Qué hace:**
1. Verifica `EstadoApi` — si no es `core`, permite ApiPV.
2. Valida existencia de datos mínimos en la BD:
   - `Sucursal` activa para la empresa
   - `RelEmpresaTipoDocumento` activa
   - `UsuariosRebsol` activos
3. Si hay exactamente 1 sucursal, la preselecciona. Si hay varias, queda en null.
4. Renderiza `RecaudacionBundle:Supervisor:index.html.twig` con mensajes de error de configuración.

**Funciones helper del SupervisorController:**
- `obtenerAuditoriaDeEntidadAction($entidad, $id)` — usa Gedmo Loggable para audit trail
- `obtenerAuditoriaDeDiferenciaAction($diferencia)` — auditoría específica de diferencias
- `obtenerAuditoriaDeFoliosAction($folios)` — auditoría específica de folios

⚠️ **Gedmo Loggable** no está en Tenant. El `AuditableTrait` con `createdAt/updatedAt`
puede ser suficiente para la mayoría de casos. Para diferencias/folios puede requerirse
un log entry más detallado (quién, cuándo, qué cambió).

---

## Los 9 dominios del Supervisor

### 1. AutorizacionDescuentos ← PRIORITARIO

**Entrada:** `/Hermes/Recaudacion/Supervisor/AutorizacionDescuentos/index`
**Controllers origen:**
- `AutorizacionDescuentos/Controller.php` — listado
- `AutorizacionDescuentos/ApruebaController.php` — aprobar
- `AutorizacionDescuentos/RechazaController.php` — rechazar
- `AutorizacionDescuentos/VerController.php` — ver detalle

**Flujo de negocio:**
El cajero solicita una diferencia desde el módulo Recaudacion.
Si supera `MONTO_MAXIMO_DIFERENCIA`, queda en estado `cajeroPideAutorizacion`.
El supervisor ve un listado de diferencias pendientes y puede aprobar/rechazar.

**Rutas completas origen:**
```
/Supervisor/AutorizacionDescuentos/index      → listado (en espera, rechazadas, autorizadas)
/Supervisor/AutorizacionDescuentos/aprueba/{idDiferencia}
/Supervisor/AutorizacionDescuentos/rechaza/{idDiferencia}
/Supervisor/AutorizacionDescuentos/ver/{idDiferencia}
```

**Entidades involucradas:**
```
HermesBundle:Diferencia          → App\Entity\Tenant\Difference (a crear)
HermesBundle:MotivoDiferencia    → App\Entity\Tenant\DifferenceReason (ya existe como mantenedor Treasury)
HermesBundle:TipoDiferencia      → App\Entity\Tenant\DifferenceType (ya existe como mantenedor Treasury)
HermesBundle:EstadoDiferencia    → App\Entity\Tenant\DifferenceStatus
```

**Estados de diferencia relevantes para el supervisor:**
```
cajeroPideAutorizacion           → listado principal (pendientes)
descuentoNoRequiereAutorizacion  → ya aprobadas automáticamente
superVisorAprueba                → aprobadas por supervisor
superVisorRechaza                → rechazadas
cajeroCancelaSolicitud           → anuladas por cajero
```

**Destino propuesto en Tenant:**
```
src/Controller/Revenue/Supervisor/DifferenceAuthorizationController.php
```

**Patrón:** No es un mantenedor CRUD simple. Tiene flujo de aprobación.
No debe extender `AbstractMantenedorController`.
Usar `AbstractTenantAwareController` directamente.

---

### 2. ConsolidadoCaja ← EL MÁS COMPLEJO

**Entrada:** `/Hermes/Recaudacion/Supervisor/ConsolidadoCaja/index`
**Controllers origen:**
- `ConsolidadoCaja/Controller.php` — listado cajas
- `ConsolidadoCaja/InformeController.php` — informe detallado
- `ConsolidadoCaja/AbrirController.php` — reabrir caja
- `ConsolidadoCaja/EditarController.php` — editar datos caja
- `ConsolidadoCaja/ExcelController.php` — exportar a Excel
- `ConsolidadoCaja/EditarBonoController.php` — editar bonos

**Rutas completas origen:**
```
/Supervisor/ConsolidadoCaja/index
/Supervisor/ConsolidadoCaja/informe
/Supervisor/ConsolidadoCaja/Informer/
/Supervisor/ConsolidadoCaja/{id}/AbrirCaja
/Supervisor/ConsolidadoCaja/{id}/GestionCerrarCaja
/Supervisor/ConsolidadoCaja/{id}/GestionCajaExcel
/Supervisor/ConsolidadoCaja/{id}/GestionCajaExcel2
/Supervisor/ConsolidadoCaja/DetalleCajaPrinter    (POST)
/Supervisor/ConsolidadoCaja/{id}/Editar
/Supervisor/ConsolidadoCaja/EditaNumeroDeposito
/Supervisor/ConsolidadoCaja/EditarBono/{idPago}/
/Supervisor/ConsolidadoCaja/ActualizarBono/{idPago}/
```

**Qué hace:**
- Lista todas las cajas (por fecha, cajero, estado)
- Permite al supervisor ver el detalle de cada caja (medios de pago, montos)
- Permite **reabrir** una caja cerrada (cambio de estado)
- Permite **editar** número de depósito después del cierre
- Permite editar/corregir datos de bonos en pagos ya realizados
- Exporta a Excel (2 versiones de formato)
- Genera informe PDF imprimible

**Entidades involucradas:**
```
HermesBundle:Caja              → App\Entity\Tenant\CashRegister (a crear)
HermesBundle:DetalleCaja       → App\Entity\Tenant\CashRegisterDetail (a crear)
HermesBundle:DetalleCajaCheque → App\Entity\Tenant\CashRegisterCheckDetail (a crear)
HermesBundle:PagoCuenta        → App\Entity\Tenant\PaymentAccount (ya existe)
HermesBundle:FormaPago         → App\Entity\Tenant\PaymentMethod (ya existe)
HermesBundle:EstadoReapertura  → estado de la caja
```

**Integración ApiPV — ConsolidadoCajaPorProfesional:**
Rutas adicionales bajo `ApiPV`:
```
/Hermes/Recaudacion/Supervisor/ConsolidadoCajaPorProfesional/index
/Hermes/Recaudacion/Supervisor/ConsolidadoCajaPorProfesional/informe
/Hermes/Recaudacion/Supervisor/ConsolidadoCajaPorProfesional/Informer/
/Hermes/Recaudacion/Supervisor/ConsolidadoCajaPorProfesional/InformeCajaPorProfesional/{id}
/Hermes/Recaudacion/Supervisor/ConsolidadoCajaPorProfesional/ImprimirCajaPorProfesionalPDF
```
Controllers origen: `ApiPV/Supervisor/ConsolidadoCajaPorProfesional/`
Es el consolidado agrupado por profesional, incluye salida PDF (`knp_snappy`).

**Destino propuesto en Tenant:**
```
src/Controller/Revenue/Supervisor/CashConsolidationController.php
src/Controller/Revenue/Supervisor/CashConsolidationByProfessionalController.php
```

**Patrón:** No CRUD. Es un informe complejo + acciones administrativas.
Necesita ExportService (ya existe en Tenant) para Excel.
PDF: requiere decisión sobre librería (`dompdf`/`mpdf`/`Gotenberg`).

---

### 3. UbicacionCaja — Mantenedor CRUD

**Entrada:** `/Hermes/Recaudacion/Supervisor/UbicacionCaja/listado`
**Controllers origen:** 5 controllers CRUD (listado, nuevo, crear, ver, editar, actualizar, eliminar)

**Estado en Tenant:**
✅ **YA EXISTE** como `CashRegisterLocationController` en `Maintainers/Treasury/`.
No hay nada que hacer aquí excepto verificar que tiene todas las acciones necesarias.

**Entidad origen:**
```
HermesBundle:UbicacionCaja → App\Entity\Tenant\CashRegisterLocation (ya existe)
```

---

### 4. UbicacionCajero — Mantenedor CRUD + Asignación

**Entrada:** `/Hermes/Recaudacion/Supervisor/UbicacionCajero/listado`
**Controllers origen:** 4+ controllers

**Qué hace:**
- Asigna un cajero (usuario) a una ubicación de caja.
- Relación `RelUbicacionCajero`: un usuario ↔ una ubicación de caja + monto inicial.
- Esta relación es la que valida en `DefaultController::indexAction` si el usuario puede operar caja.

**Entidades:**
```
HermesBundle:RelUbicacionCajero → [no existe en Tenant aún]
  - idUsuario     → FK Member
  - idUbicacionCaja → FK CashRegisterLocation
  - montoInicial  → decimal
  - idEstado      → estado activo/inactivo
```

**Estado en Tenant:** ❌ No existe. Debe crearse entidad + mantenedor.
**Destino propuesto:**
```
src/Entity/Tenant/CashierAssignment.php                         # RelUbicacionCajero
src/Controller/Maintainers/Treasury/CashierAssignmentController.php
src/Form/Maintainers/Treasury/CashierAssignmentType.php
```
Extender `AbstractMantenedorController`.

---

### 5. CorrelativoBoletas — Mantenedor + Utilitarios

**Entrada:** `/Hermes/Recaudacion/Supervisor/CorrelativoBoletas/listado`
**Controllers origen:** 6 controllers (listado, nuevo, crear, ver, editar, actualizar, eliminar, info + utilidades JS)

**Qué hace:**
Administra los talonarios de boletas: número inicio, número fin, número actual.
Incluye rutas especiales para JS:
- `numeroPila` — consulta si la pila está activa
- `numeroInicio` — consulta el número de inicio

**Entidad origen (`Talonario`):**
```
HermesBundle:Talonario
  - idUbicacionCaja          → FK CashRegisterLocation
  - idSubEmpresa             → FK SubEmpresa
  - idRelEmpresaTipoDocumento → FK (TipoDocumento)
  - numeroInicio             → int
  - numeroTermino            → int
  - numeroActual             → int
  - idEstado                 → activo/inactivo
  - idEstadoPila             → activo/inactivo
```

**Estado en Tenant:** ❌ No existe. Entidad + mantenedor a crear.
**Nota:** `SubEmpresa` puede no existir en Tenant. Evaluar si se simplifica.

**Destino propuesto:**
```
src/Entity/Tenant/Voucher.php                                    # Talonario
src/Controller/Maintainers/Treasury/VoucherController.php
src/Form/Maintainers/Treasury/VoucherType.php
```

---

### 6. MantenedorFolios — CRUD + Ciclo de Vida

**Entrada:** `/Hermes/Recaudacion/Supervisor/MantenedorFolios/index`
**Controllers origen:** 6 controllers

**Qué hace:**
Controla el ciclo de vida de los folios/talonarios individuales:
- Ver detalle de un folio
- Anular folio (+ `anularPorEmitir`)
- Habilitar folio anulado
- Auditoría del folio
- Editar/actualizar datos del folio

**Entidad origen (`DetalleTalonario`):**
```
HermesBundle:DetalleTalonario
  - idTalonario    → FK Talonario/Voucher
  - numero         → int (número de folio)
  - idEstado       → activo/anulado/emitido
  - fechaAnulacion → DateTime nullable
  - idUsuarioAnulacion → FK Member nullable
```

**Estado en Tenant:** ❌ No existe. Se crea junto con Voucher.

**Destino propuesto:**
```
src/Entity/Tenant/VoucherEntry.php                               # DetalleTalonario
src/Controller/Revenue/Supervisor/VoucherManagementController.php
```
No usar `AbstractMantenedorController` porque tiene ciclo de vida complejo (anular/habilitar).

---

### 7. AsientoContable — Informe + Exportación

**Entrada:** `/Hermes/Recaudacion/Supervisor/AsientoContable/index`
**Controllers origen:** 4 controllers

**Rutas origen:**
```
/Supervisor/AsientoContable/index
/Supervisor/AsientoContable/informe
/Supervisor/AsientoContable/ubicacionusuariobysucursal
/Supervisor/AsientoContable/Descarga/{año}/{mes}/{sucursal}/{tipo}
```

**Qué hace:**
Genera asientos contables por período (mes/año/sucursal).
Permite descarga en formato contable.
Consulta de usuario por sucursal (AJAX).

**Estado en Tenant:** ❌ No existe.
**Destino propuesto:**
```
src/Controller/Revenue/Supervisor/AccountingEntryController.php
```
Usar `ExportService` de Tenant para descargas.

---

### 8. ReporteProduccion — Consulta + Descarga

**Entrada:** `/Hermes/Recaudacion/Supervisor/ReporteProduccion/index`
**Controllers origen:** 2 controllers

**Rutas origen:**
```
/Supervisor/ReporteProduccion/index
/Supervisor/ReporteProduccion/Descarga/{fechaInicio}/{fechaFin}
```

**Qué hace:**
Reporte de producción por rango de fechas.
Permite descarga del reporte.

**Estado en Tenant:** ❌ No existe.
**Destino propuesto:**
```
src/Controller/Revenue/Supervisor/ProductionReportController.php
```

---

### 9. ApoyoFacturacion — Consulta + Informe + Descarga

**Entrada:** `/Hermes/Recaudacion/Supervisor/ApoyoFacturacion/index`
**Controllers origen:** 3 controllers

**Rutas origen:**
```
/Supervisor/ApoyoFacturacion/index
/Supervisor/ApoyoFacturacion/Informe
/Supervisor/ApoyoFacturacion/Descarga/{mes}
```

**Qué hace:**
Soporte para el proceso de facturación.
Genera informe mensual y permite descarga.
Relacionado con DTE (facturación electrónica).

**Estado en Tenant:** ❌ No existe.
**Destino propuesto:**
```
src/Controller/Revenue/Supervisor/BillingAidController.php
```
⚠️ Puede tener dependencia de DTE (ver observación en inventario).

---

## Resumen de estado en Tenant para Supervisor

| Dominio | Tipo | Acción en Tenant |
|---|---|---|
| UbicacionCaja | Mantenedor | ✅ Ya existe como `CashRegisterLocationController` |
| UbicacionCajero | Mantenedor + Asignación | ❌ Crear `CashierAssignment` (entidad + controller) |
| CorrelativoBoletas | Mantenedor | ❌ Crear `Voucher` (entidad + controller) |
| MantenedorFolios | CRUD + ciclo vida | ❌ Crear `VoucherEntry` + `VoucherManagementController` |
| AutorizacionDescuentos | Flujo aprobación | ❌ Crear (depende de `Difference` en Recaudacion) |
| ConsolidadoCaja | Informe complejo | ❌ Crear `CashConsolidationController` |
| ConsolidadoPorProfesional | Informe PDF | ❌ Crear (depende de PDF lib) |
| AsientoContable | Informe + descarga | ❌ Crear `AccountingEntryController` |
| ReporteProduccion | Descarga rango | ❌ Crear `ProductionReportController` |
| ApoyoFacturacion | Informe mensual | ❌ Crear `BillingAidController` |

---

## Vistas del Supervisor

### Templates origen por dominio:
```
RecaudacionBundle:Supervisor:index.html.twig                      → panel principal
RecaudacionBundle:Supervisor:auditoria.html.twig                  → auditoría entidades
RecaudacionBundle:Supervisor/AutorizacionDescuentos:auditoria.html.twig
RecaudacionBundle:Supervisor/MantenedorFolios:auditoria.html.twig
RecaudacionBundle:Supervisor/ConsolidadoCaja:index.html.twig
RecaudacionBundle:Supervisor/ConsolidadoCaja:informe.html.twig
RecaudacionBundle:Supervisor/ConsolidadoCaja:editar.html.twig
RecaudacionBundle:Supervisor/MantenedorFolios:index.html.twig
RecaudacionBundle:Supervisor/MantenedorFolios:verDetalle.html.twig
RecaudacionBundle:Supervisor/UbicacionCaja:*.html.twig            (4 templates)
RecaudacionBundle:Supervisor/UbicacionCajero:*.html.twig          (4 templates)
RecaudacionBundle:Supervisor/CorrelativoBoletas:*.html.twig       (5 templates)
RecaudacionBundle:Supervisor/AsientoContable:*.html.twig          (3 templates)
RecaudacionBundle:Supervisor/AutorizacionDescuentos:*.html.twig   (3 templates)
RecaudacionBundle:Supervisor/ApoyoFacturacion:*.html.twig         (2 templates)
RecaudacionBundle:Supervisor/ReporteProduccion:*.html.twig        (1 template)
```

### Estructura destino propuesta en Tenant:
```
templates/revenue/supervisor/
├── index.html.twig
├── difference_authorization/
│   ├── index.html.twig
│   ├── view.html.twig
│   └── _audit.html.twig
├── cash_consolidation/
│   ├── index.html.twig
│   ├── informe.html.twig
│   └── editar.html.twig
├── voucher_management/
│   ├── index.html.twig
│   └── detail.html.twig
├── accounting_entry/
│   ├── index.html.twig
│   └── informe.html.twig
├── production_report/
│   └── index.html.twig
└── billing_aid/
    ├── index.html.twig
    └── informe.html.twig

templates/maintainers/treasury/
├── cash_register_location/    ← ya existe
│   └── ...
├── cashier_assignment/        ← crear
│   └── index.html.twig
└── voucher/                   ← crear
    └── index.html.twig
```

---

## Notas técnicas para el Supervisor en Tenant

### Patrón de autorización de diferencias
En el sistema origen, el cajero hace polling desde JS para saber si el supervisor aprobó:
```js
// polling cada N segundos
fetch('/Recaudacion/Diferencia/respuestaSupervisor')
```
En Tenant: reemplazar con Turbo Streams + Mercure o simplemente un botón de "verificar estado"
que hace un Turbo Frame refresh. El polling activo es un anti-patrón en Turbo.

### Exportaciones
El Supervisor tiene 4+ flujos de exportación (Excel + PDF).
En Tenant:
- Excel/CSV → usar `ExportService` que ya existe en Tenant
- PDF → decidir librería (`dompdf` recomendado para Symfony 7.4 sin dependencias del SO)

### Consolidado de caja — caso especial
`ConsolidadoCaja` no es un mantenedor sino un **informe operacional** con acciones
administrativas (reabrir, editar depósito, editar bono).
El diseño en Tenant debe contemplar:
- Vista tipo data table con filtros
- Turbo Frame para expandir detalle de cada caja
- Acciones inline (reabrir, editar) con Turbo Stream para actualizar la fila
- Modal para editar número de depósito (Turbo Frame modal)

### Gestión de permisos
El supervisor tiene acciones privilegiadas (aprobar diferencias, reabrir cajas).
En Tenant: asegurarse que `security.yaml` restrinja estas rutas a rol Supervisor.
El sistema origen verifica `getVerCaja()` para cajero y tiene un rol supervisor implícito.
En Tenant mapear a: `ROLE_CASH_SUPERVISOR` o similar.
