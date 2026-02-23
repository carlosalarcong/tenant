# Migración Caja → Tenant: Submodulo PagoCuenta

## Propósito
Detalle técnico del submodulo PagoCuenta para que Claude Code en Tenant
lo use como referencia al diseñar el módulo `Revenue/PatientAccount/`.

**Fuente:** `src/Rebsol/RecaudacionBundle/Controller/Api/Unab/PagoCuenta/CuentaPacienteController.php`
**Destino:** `src/Controller/Revenue/PatientAccount/` (a crear en Tenant)

---

## Contexto de uso

PagoCuenta no es un flujo independiente. Es un **submodulo de soporte** que provee
información de cuenta al flujo de Recaudacion y al módulo de Admisión.

Se accede desde:
- `http://melisaupgrade.prod/app_dev.php/Hermes/Recaudacion/Recaudacion/Recaudacion?tipoPago=PagoCuenta`
  → flujo de caja iniciado desde Admisión, donde el paciente ya tiene un ingreso/cuenta abierta.

Su rol es mostrar el estado de cuenta del paciente (deudas, garantías, pagos pendientes)
para que el cajero/admisionista tome decisiones antes de cobrar.

---

## Mapa de rutas activas (las 4 que realmente funcionan)

| Nombre de ruta | URL completa | Método HTTP | Retorna |
|---|---|---|---|
| `Caja_PagoCuenta_ConsultarDatos_CuentaPaciente` | `/Hermes/Recaudacion/PagoCuenta/CuentaPaciente` | GET | HTML (Twig) |
| `Caja_PagoCuenta_Prevision_CuentaPaciente` | `/Hermes/Recaudacion/PagoCuenta/PrevisionPaciente` | GET | HTML (Twig) |
| `Caja_PagoCuenta_ConsultarDatos_CuentaPaciente_InsertarReguardoFinanciero` | `/Hermes/Recaudacion/PagoCuenta/insertarReguardoFinanciero` | GET | HTML (Twig) |
| `Caja_PagoCuenta_VerificarPagosPendientes_CuentaPaciente` | `/Hermes/Recaudacion/PagoCuenta/VerificarPagosPendientes` | GET | Text (true/false) |

**Ruta activa pero SIN callable (riesgo conocido):**
- `Caja_Recaudacion` → `/Hermes/Recaudacion/PagoCuenta/Recaudacion`
  Matchea en el router pero no tiene `RecaudacionController.php` en `Api/Unab/PagoCuenta/`.
  **No migrar.**

---

## Detalle de cada endpoint

### 1. `mostrarCuentaAction` — Consulta cuenta paciente

**Archivo:** `Controller/Api/Unab/PagoCuenta/CuentaPacienteController.php:20`
**URL:** `GET /Hermes/Recaudacion/PagoCuenta/CuentaPaciente`

**Parámetros de entrada (GET):**
```
idPaciente      → ID del paciente (opcional, si viene de admisión)
idPnatural      → ID de la persona natural (opcional)
idPnaturalTutor → ID del tutor (opcional, para menores)
```
Si ninguno viene por GET, lo lee desde sesión: `$this->get('session')->get('Pnatural')`.

**Lógica:**
1. Resuelve `idPnatural` final: prioridad `idPnaturalTutor` > `idPaciente` > sesión.
2. Consulta cuenta del paciente: `rCajaCuentaPaciente()->obtenerCuentaPaciente($idPnatural)`.
3. Consulta cuenta del tutor: `rCajaCuentaPaciente()->obtenerCuentaPacienteTutor($idPnatural)`.
4. Lee `VarCajaHoy` de sesión (ID de la caja abierta del día).
5. Renderiza vista con ambas cuentas.

**Template origen:**
```
@Recaudacion/Recaudacion/DatosCuentaPaciente/mostrarDatos.html.twig
```

**Servicios usados:**
```php
$this->get('recaudacion.CuentaPaciente')  // → obtenerCuentaPaciente(), obtenerCuentaPacienteTutor()
```

**Entidades involucradas (origen → Tenant):**
```
HermesBundle:Pnatural        → App\Entity\Tenant\Patient
HermesBundle:CuentaPaciente  → App\Entity\Tenant\PatientAccount
HermesBundle:Caja            → App\Entity\Tenant\CashRegister (sesión)
```

**Ruta destino propuesta en Tenant:**
```php
#[Route('/revenue/patient-account/summary', name: 'app_revenue_patient_account_summary')]
```

---

### 2. `mostrarFinanciadorPacienteAction` — Cargar financiador para pago

**Archivo:** `Controller/Api/Unab/PagoCuenta/CuentaPacienteController.php:53`
**URL:** `GET /Hermes/Recaudacion/PagoCuenta/PrevisionPaciente`

**Parámetros de entrada:** ninguno en GET. Lee datos del usuario autenticado.

**Lógica:**
1. Obtiene `idSucursal` del usuario autenticado.
2. Crea formulario `PrestacionType` con empresa + estado activo + sucursal.
3. Renderiza vista con formulario de financiador (previsión + plan).

**Template origen:**
```
RecaudacionBundle:Api/Unab/DatosCuentaPaciente:DatosFinanciador.html.twig
```

**FormType usado:**
```php
Rebsol\RecaudacionBundle\Form\Type\Recaudacion\Pago\PrestacionType
// Opciones: iEmpresa, estado_activado, sucursal, database_default
```

**Servicios/repos usados:**
```php
$em->getRepository('RebsolHermesBundle:UsuariosRebsol')->obtenerIdSucursalPorIdUsuario($idUser)
```

**Entidades involucradas:**
```
HermesBundle:UsuariosRebsol  → App\Entity\Tenant\Member
HermesBundle:Prevision       → financiador (FONASA, ISAPRE, particular)
HermesBundle:PrPlan          → plan del financiador
```

**Ruta destino propuesta en Tenant:**
```php
#[Route('/revenue/patient-account/financier', name: 'app_revenue_patient_account_financier')]
```

---

### 3. `insertarReguardoFinancieroAction` — Ver resguardo financiero

**Archivo:** `Controller/Api/Unab/PagoCuenta/CuentaPacienteController.php:80`
**URL:** `GET /Hermes/Recaudacion/PagoCuenta/insertarReguardoFinanciero`

**Parámetros de entrada (GET):**
```
idDatoIngreso → ID de la admisión/dato de ingreso
```

**Lógica:**
1. Obtiene datos de la admisión por ID: `rCuentaPaciente()->obtieneDatosAdmisionPorId($idDatoIngreso)`.
2. Si la admisión tiene `arrPagoCuenta`, extrae los IDs.
3. Obtiene garantías asociadas: `rCuentaPaciente()->obtieneGarantiasCuentaPaciente($arrIdPagoCuenta)`.
4. Renderiza vista de resguardo financiero con las garantías.

**Template origen:**
```
CuentaPacienteBundle:_Default/CuentaPaciente/DetalleCuentaPaciente:ResguardoFinanciero.html.twig
```
⚠️ Este template referencia `CuentaPacienteBundle`, no `RecaudacionBundle`.
Indica que el origen real de esta funcionalidad es otro bundle (legacy parcial).

**Servicios usados:**
```php
$this->get('cuentaPaciente.cuentaPaciente')
// → obtieneDatosAdmisionPorId()
// → obtieneGarantiasCuentaPaciente()
```

**Entidades involucradas:**
```
HermesBundle:DatoIngreso / ReservaAtencion → App\Entity\Tenant\AdmissionRecord
HermesBundle:PagoCuenta                    → App\Entity\Tenant\PaymentAccount
HermesBundle:Garantia                      → garantías financieras (¿existe en Tenant?)
```

**Ruta destino propuesta en Tenant:**
```php
#[Route('/revenue/patient-account/financial-guarantee', name: 'app_revenue_patient_account_guarantee')]
```

---

### 4. `verificarPagosPendientesAction` — Verificar pagos pendientes

**Archivo:** `Controller/Api/Unab/PagoCuenta/CuentaPacienteController.php:308`
**URL:** `GET /Hermes/Recaudacion/PagoCuenta/VerificarPagosPendientes`

**Parámetros de entrada (GET):**
```
idPaciente  → ID del paciente (opcional)
idPnatural  → ID de la persona natural (opcional)
```
Si `idPnatural` es null, lee desde sesión.

**Lógica:**
1. Resuelve `idPnatural` final.
2. Obtiene `Pnatural` → `Persona`.
3. Obtiene **todas las admisiones** del paciente (titular + tutor).
4. Para cada admisión, verifica si tiene estado de cuenta "abierto/pendiente".
5. Retorna `'true'` o `'false'` como texto plano (no JSON).

**Estados de cuenta que considera "pendientes de pago":**
```
EstadoCuenta.cerradaRevisionInterna
EstadoCuenta.cerradaRevisionFinanciador
EstadoCuenta.cerradaPendientePago
EstadoCuenta.cerradaCobranzaInterna
EstadoCuenta.cerradaCobranzaJudicial
EstadoCuenta.cerradaPagadaConSaldoPendiente
EstadoCuenta.abiertaPendientePago
```
**Excluido:** `EstadoIngreso.Preadmision` — las preadmisiones no cuentan como deuda real.

**Retorno:**
```php
return new Response($esPacienteAbiertoDePago); // 'true' o 'false' como texto
```
⚠️ Retorna texto plano, no JSON. En Tenant cambiar a `JsonResponse`.

**Servicios usados:**
```php
$this->get('cuentaPaciente.DatoIngreso')
// → obtieneDatoIngresoAdmision($idPersona)        ← admisiones del titular
// → obtieneDatoIngresoAdmisionTutor($idPersona, null) ← admisiones como tutor
```

**Entidades involucradas:**
```
HermesBundle:Pnatural        → App\Entity\Tenant\Patient
HermesBundle:AdmisionDatos   → App\Entity\Tenant\AdmissionRecord
HermesBundle:EstadoCuenta    → App\Entity\Tenant\AccountStatus (ya existe)
HermesBundle:EstadoIngreso   → App\Entity\Tenant\AdmissionStatus
```

**Ruta destino propuesta en Tenant:**
```php
#[Route('/revenue/patient-account/has-pending-payments', name: 'app_revenue_patient_account_pending')]
```

---

## Métodos adicionales en el controller (sin ruta activa, no migrar)

El `CuentaPacienteController` tiene métodos que **no están en routing activo**
pero existen en el archivo. Se listan para no confundirlos con los 4 activos:

| Método | Estado | Motivo de exclusión |
|---|---|---|
| `gestionAbrirCajaFarmaciaAction` | ❌ No migrar | Routing comentado (Farmacia desactivada) |
| `gestionCerrarCajaFarmaciaAction` | ❌ No migrar | Mismo motivo |
| `insertarListadoPagoCuentaPorPacienteAction` | ❌ No migrar | Routing comentado en `pagoCuenta.yml` |
| `resultadoBusquedaAvanzadaPacienteCuentaPacienteAction` | ❌ No migrar | Routing comentado |
| `mostrarCuentaNoEncontradoAction` | ❌ No migrar | Routing comentado |

---

## Herencia del controller

```php
CuentaPacienteController extends PagoCuentaController
PagoCuentaController extends RecaudacionController
```

`PagoCuentaController` agrega métodos helper que usa `CuentaPacienteController`:
```php
protected function rCuentaPaciente() {
    return $this->get('cuentaPaciente.cuentaPaciente');
}

protected function rCajaCuentaPaciente() {
    return $this->get('recaudacion.CuentaPaciente');
}

protected function cuentaPacienteDatoIngreso() {
    return $this->get('cuentaPaciente.DatoIngreso');
}
```

En Tenant, estos servicios se inyectan por constructor en el controller.

---

## Servicios origen y su equivalente en Tenant

| Servicio origen | Responsabilidad | Equivalente en Tenant |
|---|---|---|
| `recaudacion.CuentaPaciente` | Consultas de cuenta por idPnatural | Crear `PatientAccountService` |
| `cuentaPaciente.cuentaPaciente` | Datos de admisión y garantías | Usar `PatientAccountRepository` |
| `cuentaPaciente.DatoIngreso` | Admisiones del paciente (titular + tutor) | Usar `AdmissionRecordRepository` |

---

## Vistas / Templates origen

```
@Recaudacion/Recaudacion/DatosCuentaPaciente/mostrarDatos.html.twig
    → muestra cuenta titular + tutor (tabla de deudas/pagos)

RecaudacionBundle:Api/Unab/DatosCuentaPaciente:DatosFinanciador.html.twig
    → formulario de financiador (previsión/plan)

CuentaPacienteBundle:_Default/CuentaPaciente/DetalleCuentaPaciente:ResguardoFinanciero.html.twig
    → resguardo financiero con garantías
    ⚠️ Pertenece a CuentaPacienteBundle, no a RecaudacionBundle
```

### Destino propuesto en Tenant:
```
templates/revenue/patient-account/
├── _account_summary.html.twig      # mostrarDatos
├── _financier_form.html.twig       # DatosFinanciador (Turbo Frame)
└── _financial_guarantee.html.twig  # ResguardoFinanciero (Turbo Frame)
```

Nota: el endpoint `verificarPagosPendientes` no renderiza vista, devuelve texto plano.
En Tenant puede ser una ruta `#[Route(..., methods: ['GET'])]` que retorna `JsonResponse`.

---

## JS asociado

```
_default/pago.cuenta/pago.cuenta.js
```
Un único archivo JS para el flujo de PagoCuenta.
En Tenant: `assets/controllers/revenue/patient-account-controller.js` (Stimulus).

---

## Estructura propuesta en Tenant

```php
// src/Controller/Revenue/PatientAccount/PatientAccountController.php

#[Route('/revenue/patient-account', name: 'app_revenue_patient_account_')]
class PatientAccountController extends AbstractTenantAwareController
{
    public function __construct(
        private readonly PatientAccountService $patientAccountService,
        private readonly AdmissionRecordRepository $admissionRepository,
        private readonly PatientAccountRepository $accountRepository,
    ) {}

    #[Route('/summary', name: 'summary')]
    public function summary(Request $request): Response
    { /* mostrarCuentaAction */ }

    #[Route('/financier', name: 'financier')]
    public function financier(Request $request): Response
    { /* mostrarFinanciadorPacienteAction */ }

    #[Route('/financial-guarantee', name: 'guarantee')]
    public function financialGuarantee(Request $request): Response
    { /* insertarReguardoFinancieroAction */ }

    #[Route('/has-pending-payments', name: 'pending')]
    public function hasPendingPayments(Request $request): JsonResponse
    { /* verificarPagosPendientesAction — cambiar a JsonResponse */ }
}
```

---

## Notas para el diseño en Tenant

### Los 4 endpoints son Turbo Frames
Todos renderizaban HTML parcial en el origen (sin layout completo).
En Tenant deben ser Turbo Frames que se cargan dentro del panel de caja:
```twig
<turbo-frame id="patient-account-summary" src="{{ path('app_revenue_patient_account_summary', {idPaciente: paciente.id}) }}">
    {# carga automáticamente al paciente identificarse #}
</turbo-frame>
```

### `verificarPagosPendientes` cambiar retorno
El origen retorna texto plano `'true'`/`'false'`.
En Tenant retornar `JsonResponse(['hasPending' => true/false])`.
El Stimulus controller leerá el JSON para mostrar/ocultar alertas.

### Dependencia de `AccountStatus` en Tenant
Los estados que determinan "pendiente de pago" deben existir como registros
en `App\Entity\Tenant\AccountStatus`. Verificar que estén en las migraciones.

### Garantías
El template `ResguardoFinanciero` consume garantías de `PagoCuenta`.
En Tenant verificar si existe entidad `Guarantee` o similar.
Si no existe, es parte del alcance a definir junto con `PatientAccount`.
