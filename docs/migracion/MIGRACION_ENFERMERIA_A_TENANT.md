# Plan de Migración: EnfermeriaBundle → Módulo Enfermería (Tenant)

> **Rama objetivo:** `feature/nursing`
> **Origen legacy:** `/var/www/html/melisa_prod/src/Rebsol/EnfermeriaBundle`
> **Destino:** `/var/www/html/tenant`
> **Fecha de análisis:** 2026-02-19

---

## 1. Resumen Ejecutivo

El módulo de Enfermería es el corazón operativo del sistema clínico hospitalario. Provee a las enfermeras el **tablero de camas por servicio**, la **gestión del estado de camas**, la **vista de paciente hospitalizado** con 15 pestañas de acción clínica (recetas, indicaciones, cargos, traslados, alta, devoluciones, formularios, etc.) y la **integración con Admisión** para ingresar pacientes solicitados desde otros servicios.

El legacy contiene **43 controladores** organizados en tres carpetas: `_Default` (clínica general, 20 activos), `ApiC` (urgencia/Cruz Roja, 20 activos), y `Api1` (3 archivos obsoletos con `var_dump(); exit();`). La migración cubrirá únicamente las carpetas `_Default` y `ApiC`, unificándolas en un único controlador moderno con selección de vista por tenant.

La complejidad es **Alta** debido al número de acciones, los modelos de concurrencia (tabla `ConcurrenciaPacienteEnfermeria`), la gestión de cargos clínicos, y las dependencias con otros módulos (RegistroClínico, CuentaPaciente, Pabellón).

---

## 2. Alcance del Módulo

### 2.1 Funcionalidades incluidas en esta migración

| # | Funcionalidad | Origen Legacy |
|---|---------------|---------------|
| 1 | Selección de servicio (por API del módulo) | `_Default/BuscadorServicioController` + `ApiC/BuscadorServicioController` |
| 2 | Tablero de camas por servicio | `_Default/VerServicioController` + `ApiC/VerServicioController` |
| 3 | Listado de camas (vista alternativa) | `_Default/VerServicioController::listado` |
| 4 | Cambio de estado de cama | `_Default/CamaController` + `ApiC/CamaController` |
| 5 | Asignación de cama (urgencia) | `ApiC/CamaController::enfermeriaAsignarCama` |
| 6 | Vista de paciente (orquestador 15 pestañas) | `_Default/VerPacienteController` |
| 7 | Resumen del paciente | `VerPaciente/ResumenController` |
| 8 | Solicitudes de admisión / egreso / traslado entre servicios | `_Default/SolicitudesController` |
| 9 | Ingresar paciente al servicio | `SolicitudesController::ingresarPacienteAlServicio` |
| 10 | Cambio de cama (dentro del servicio) | `VerPaciente/CambiarCamaController` |
| 11 | Solicitar / anular traslado | `VerPaciente/TraspasarController` |
| 12 | Dar alta / anular alta | `VerPaciente/DarAltaController` |
| 13 | Recetas médicas (ver / crear / editar / anular / complementar) | `VerPaciente/RecetaController` |
| 14 | Indicaciones farmacológicas (ver / crear / editar / anular) | `VerPaciente/IndicacionController` |
| 15 | Receta de indicación | `VerPaciente/RecetaIndicacionController` |
| 16 | Cargos paciente (resumen) | `VerPaciente/CargosPacienteController` |
| 17 | Cargos prestaciones | `VerPaciente/CargosPacientePrestacionesController` |
| 18 | Cargos artículos (bodega) | `VerPaciente/CargosPacienteArticulosController` |
| 19 | Cargos paquetes | `VerPaciente/CargosPacientePaquetesController` |
| 20 | Cargos profesional | `VerPaciente/CargosProfesionalController` |
| 21 | Devoluciones | `VerPaciente/DevolucionController` |
| 22 | Formularios de atención (RCHA/RCH) | `VerPaciente/VerFormularios/VerFormulariosController` |
| 23 | Hospitalizaciones previas | `VerPaciente/HospitalizacionesController` |
| 24 | Impresión de egreso (PDF) | `VerPaciente/ImprimirEgresoController` |
| 25 | Endpoints JSON utilitarios | `DefaultController` (búsqueda artículos, prestaciones, paquetes) |
| 26 | Menú "Enfermería" en sidebar | `src/Service/Menu/MenuDefinition.php` |

### 2.2 Fuera de alcance

| Funcionalidad | Razón |
|---------------|-------|
| `Api1` completo | Código con `var_dump(); exit();`, sin routing activo (`VerServicioController.php:20`, `SolicitudesController.php:21`, `VerPacienteController.php:23`) |
| Signos vitales | Pertenece a RegistroClínico; se leerán vía API interna |
| Formularios RCH/RCHU dinámicos | Dependen del módulo RegistroClínicoHospitalario; migrar en fase separada |
| Visita médica | Responsabilidad del módulo Clínico |

---

## 3. Situación AS-IS (Legacy)

### 3.1 Estructura de directorios

```
EnfermeriaBundle/
├── Controller/
│   ├── _Default/                    # 20 controladores activos (clínica)
│   │   ├── BuscadorServicioController.php   # Pantalla 1: selección servicio
│   │   ├── CamaController.php               # CRUD estado cama
│   │   ├── SolicitudesController.php        # Admisión/Egreso entre servicios
│   │   ├── VerServicioController.php        # Tablero camas
│   │   ├── VerPacienteController.php        # Orquestador 15 pestañas
│   │   └── VerPaciente/
│   │       ├── CambiarCamaController.php
│   │       ├── CargosPacienteController.php
│   │       ├── CargosPacienteArticulosController.php
│   │       ├── CargosPacientePaquetesController.php
│   │       ├── CargosPacientePrestacionesController.php
│   │       ├── CargosProfesionalController.php
│   │       ├── DarAltaController.php
│   │       ├── DevolucionController.php
│   │       ├── HospitalizacionesController.php
│   │       ├── IndicacionController.php
│   │       ├── RecetaController.php
│   │       ├── RecetaIndicacionController.php
│   │       ├── ResumenController.php
│   │       ├── TraspasarController.php
│   │       └── VerFormularios/
│   │           └── VerFormulariosController.php
│   ├── ApiC/                        # 20 controladores activos (urgencia)
│   │   └── [misma estructura que _Default]
│   ├── Api1/                        # OBSOLETO — no migrar
│   └── DefaultController.php        # Utilidades JSON + obtenerRutaApiModulo()
├── Repository/                      # 21 repositorios
├── Services/                        # 4 servicios de cargos
├── Form/Type/                       # 19 form types
└── Resources/
    ├── config/
    │   ├── routing.yml              # Importa Default/ y ApiC/
    │   ├── Default/routing.yml      # 70+ rutas
    │   └── ApiC/routing.yml        # 4 rutas propias
    └── views/
        ├── _Default/               # ~45 plantillas Twig
        └── ApiC/                   # ~30 plantillas Twig
```

### 3.2 Flujo principal de navegación

```
GET /Hermes/Enfermeria/Inicio
  → BuscadorServicioController::indexAction
    · valida rol clínico
    · obtenerRutaApiModulo() → '_Default' | 'ApiC'
    · Si ApiC: carga servicios de urgencia + vista ApiC/buscadorServicios.html.twig
    · Si _Default: carga servicios clínicos + vista _Default/buscadorServicios.html.twig
    · Ref: _Default/BuscadorServicioController.php:57,76

POST /Hermes/Enfermeria/ValidarServicio
  → BuscadorServicioController::validarServicioAction
    · lee enfermeria_buscadorServicios[idServicio]
    · forward() → EnfermeriaBundle:_Default/VerServicio:index  ← URL no cambia
    · Ref: _Default/BuscadorServicioController.php:93,109

  → VerServicioController::indexAction
    · carga camas, salas, pacientes, solicitudes, leyenda, estados
    · renderiza @Enfermeria/_Default/verServicio.html.twig
    · Ref: _Default/VerServicioController.php:30,82

click en cama ocupada
  → GET /Hermes/Enfermeria/VerPaciente?idRelCamaPaciente=X
    · VerPacienteController::indexAction → orquesta 15 pestañas
    · Concurrencia: INSERT/SELECT ConcurrenciaPacienteEnfermeria
```

### 3.3 Mecanismo de selección de API

- Método central: `DefaultController::obtenerRutaApiModulo()` (`DefaultController.php:309`)
- Lee parámetro de BD: `enfermeria.idModulo` a través de `HermesBundle/Controller/DefaultController.php:156`
- Resultado: string `'_Default'` o `'ApiC'`
- En la práctica se traduce a: selección de vista y delegación de lógica

### 3.4 Control de concurrencia

- Tabla: `ConcurrenciaPacienteEnfermeria`
- Función: previene ediciones simultáneas del mismo paciente por distintos usuarios
- Operaciones: INSERT al abrir VerPaciente; DELETE al salir o timeout
- **Impacto en migración:** Requiere crear entidad `NursingConcurrency` o tabla raw en tenant

### 3.5 Roles relevantes

| Rol Legacy | Descripción |
|------------|-------------|
| `ENFERMERIA_CAMBIAR_ESTADO_CAMA` | Modificar estado de cama |
| `ENFERMERIA_VER_INGRESOS` | Ver solicitudes de admisión |
| `ENFERMERIA_VER_EGRESOS` | Ver solicitudes de egreso |
| `ENFERMERIA_PACIENTE_CARGOS` | Ver/cargar cargos al paciente |
| `ENFERMERIA_PACIENTE_RECETAS` | Crear/anular recetas |
| `ENFERMERIA_PACIENTE_INDICACIONES` | Crear/anular indicaciones |
| `ENFERMERIA_PACIENTE_ALTA` | Dar alta médica |
| `ENFERMERIA_PACIENTE_TRASLADO` | Solicitar/anular traslado |

### 3.6 Repositorios legacy y sus equivalentes tenant

| Repositorio Legacy | Entidad Legacy | Equivalente Tenant |
|-------------------|----------------|-------------------|
| `CamaRepository` | `Cama` | `BedRepository` → `Bed` |
| `RelCamaPacienteRepository` | `RelCamaPaciente` | `AdmissionRecordRepository` → `AdmissionRecord` |
| `ServicioRepository` | `Servicio` | `ServiceRepository` → `Service` / `MedicalServiceRepository` |
| `TrasladoRepository` | `Traslado` | Nuevo: `NursingTransferRepository` → `NursingTransfer` |
| `PacienteRepository` | `Paciente` | `PersonRepository` → `Person` |
| `CuentaPacienteRepository` | `CuentaPaciente` | `PatientAccountRepository` → `PatientAccount` |
| `AccionClinicaRepository` | `AccionClinica` | `CareInterventionRepository` → `CareIntervention` |
| `ArticuloRepository` | `Articulo` | Externo (Logística) — vía API interna |
| `RchRecetaRepository` | `RchReceta` | Nuevo: `NursingPrescriptionRepository` → `NursingPrescription` |
| `RchIndicacionFarmacologicaRepository` | `RchIndicacionFarmacologica` | Nuevo: `NursingOrderRepository` → `NursingOrder` |
| `DefaultRepository` | Utilidades | Métodos en repositorios específicos |

### 3.7 Servicios legacy

| Servicio | Responsabilidad | Estrategia tenant |
|----------|-----------------|-------------------|
| `CargosArticulosService` | Carga de artículos al paciente | Nuevo `NursingChargeService::chargeArticles()` |
| `CargosPacientesPaquetesService` | Carga de paquetes | Nuevo `NursingChargeService::chargePackages()` |
| `CargosAccionesClinicasService` | Carga de prestaciones clínicas | Nuevo `NursingChargeService::chargeInterventions()` |
| `PrestacionesExtrasService` | Prestaciones adicionales | Fusionar en `NursingChargeService` |

---

## 4. Situación TO-BE (Tenant)

### 4.1 Estructura de destino

```
src/
├── Controller/
│   └── Nursing/
│       ├── NursingController.php                # Tablero principal (selección + tablero)
│       ├── NursingBedController.php             # Estado de camas
│       ├── NursingRequestController.php         # Solicitudes entre servicios
│       ├── NursingPatientController.php         # Vista paciente (orquestador)
│       ├── NursingPatientBedController.php      # Cambio de cama
│       ├── NursingPatientTransferController.php # Traslado
│       ├── NursingPatientDischargeController.php# Alta
│       ├── NursingPrescriptionController.php    # Recetas
│       ├── NursingOrderController.php           # Indicaciones
│       ├── NursingChargeController.php          # Cargos (artículos/prest./paquetes/prof.)
│       ├── NursingReturnController.php          # Devoluciones
│       ├── NursingFormController.php            # Formularios RCH/RCHU
│       └── NursingApiController.php             # JSON endpoints (búsquedas)
├── Entity/Tenant/
│   ├── NursingConcurrency.php                  # Control concurrencia
│   ├── NursingTransfer.php                     # Traslado entre servicios
│   ├── NursingPrescription.php                 # Receta médica
│   ├── NursingPrescriptionItem.php             # Ítem de receta
│   ├── NursingOrder.php                        # Indicación farmacológica
│   └── NursingDischarge.php                    # Alta médica
├── Repository/Tenant/
│   ├── NursingConcurrencyRepository.php
│   ├── NursingTransferRepository.php
│   ├── NursingPrescriptionRepository.php
│   ├── NursingOrderRepository.php
│   └── NursingDischargeRepository.php
├── Form/Nursing/
│   ├── NursingBedStatusType.php
│   ├── NursingPrescriptionType.php
│   ├── NursingOrderType.php
│   ├── NursingTransferType.php
│   ├── NursingDischargeType.php
│   └── NursingChargeType.php
└── Service/Nursing/
    └── NursingChargeService.php

templates/
└── nursing/
    ├── index.html.twig                          # Layout principal módulo
    ├── service/
    │   ├── _selector.html.twig                  # Selector de servicio (Turbo Frame)
    │   └── _board.html.twig                     # Tablero de camas
    ├── patient/
    │   ├── _info.html.twig                      # Info resumen paciente
    │   ├── _tabs.html.twig                      # Navegación pestañas
    │   └── tabs/
    │       ├── _summary.html.twig
    │       ├── _prescriptions.html.twig
    │       ├── _orders.html.twig
    │       ├── _charges.html.twig
    │       ├── _returns.html.twig
    │       ├── _transfer.html.twig
    │       ├── _discharge.html.twig
    │       └── _forms.html.twig
    └── print/
        └── discharge.html.twig                  # PDF egreso

assets/
└── controllers/
    └── nursing/
        ├── board_controller.js                  # Tablero camas (Stimulus)
        ├── patient_controller.js                # Vista paciente / pestañas
        └── charge_controller.js                 # Lógica cargos
```

### 4.2 Convenciones de rutas

```
/nursing/                                        → app_nursing_index
/nursing/service/{id}/board                      → app_nursing_service_board
/nursing/service/{id}/board/list                 → app_nursing_service_board_list
/nursing/bed/{id}/status                         → app_nursing_bed_status
/nursing/bed/{id}/assign                         → app_nursing_bed_assign
/nursing/request/admissions/{serviceId}          → app_nursing_request_admissions
/nursing/request/transfers/{serviceId}           → app_nursing_request_transfers
/nursing/request/discharges/{serviceId}          → app_nursing_request_discharges
/nursing/request/admit/{admissionId}             → app_nursing_request_admit
/nursing/patient/{id}                            → app_nursing_patient_show
/nursing/patient/{id}/bed/change                 → app_nursing_patient_bed_change
/nursing/patient/{id}/transfer/create            → app_nursing_patient_transfer_create
/nursing/patient/{id}/transfer/cancel            → app_nursing_patient_transfer_cancel
/nursing/patient/{id}/discharge/create           → app_nursing_patient_discharge_create
/nursing/patient/{id}/discharge/cancel           → app_nursing_patient_discharge_cancel
/nursing/patient/{id}/prescriptions              → app_nursing_patient_prescriptions
/nursing/patient/{id}/prescription/create        → app_nursing_patient_prescription_create
/nursing/patient/{id}/prescription/{pid}/edit    → app_nursing_patient_prescription_edit
/nursing/patient/{id}/orders                     → app_nursing_patient_orders
/nursing/patient/{id}/order/create               → app_nursing_patient_order_create
/nursing/patient/{id}/charges                    → app_nursing_patient_charges
/nursing/patient/{id}/charge/articles            → app_nursing_patient_charge_articles
/nursing/patient/{id}/charge/interventions       → app_nursing_patient_charge_interventions
/nursing/patient/{id}/charge/packages            → app_nursing_patient_charge_packages
/nursing/patient/{id}/returns                    → app_nursing_patient_returns
/nursing/patient/{id}/return/create              → app_nursing_patient_return_create
/nursing/patient/{id}/forms                      → app_nursing_patient_forms
/nursing/print/{id}/discharge                    → app_nursing_print_discharge
/api/nursing/articles                            → app_nursing_api_articles
/api/nursing/interventions                       → app_nursing_api_interventions
/api/nursing/packages                            → app_nursing_api_packages
/api/nursing/bed-count/{serviceId}               → app_nursing_api_bed_count
```

### 4.3 Turbo Frame strategy

| Área | Frame ID | Razón |
|------|----------|-------|
| Selector de servicio | `nursing-service-selector` | Se reemplaza solo al seleccionar |
| Tablero de camas | `nursing-board` | Reload parcial al cambiar estado |
| Vista paciente | `nursing-patient-content` | Modal/panel lateral |
| Pestaña activa | `nursing-patient-tab` | Cada pestaña carga independiente |
| Impresión egreso | Sin frame — `data-turbo="false"` `target="_blank"` | PDF nueva ventana |

---

## 5. Análisis de Brechas (GAP)

### 5.1 Entidades existentes en tenant (reutilizables)

| Entidad Tenant | Cubre legacy |
|----------------|-------------|
| `Bed` | `Cama` — estructura física |
| `Room` | `Sala` |
| `Service` | `Servicio` — servicio médico |
| `MedicalService` | `Servicio` — variante con tarifas |
| `AdmissionRecord` | `RelCamaPaciente` — vinculación cama-paciente |
| `Person` | `Paciente` — datos demográficos |
| `CareIntervention` | `AccionClinica` — prestaciones clínicas |
| `ServicePackage` / `ServicePackageDetail` | `Paquete` |

### 5.2 Entidades nuevas requeridas

| Entidad Nueva | Tabla | Prioridad | Complejidad |
|---------------|-------|-----------|-------------|
| `NursingConcurrency` | `nursing_concurrency` | Alta | Baja |
| `NursingTransfer` | `nursing_transfer` | Alta | Media |
| `NursingDischarge` | `nursing_discharge` | Alta | Media |
| `NursingPrescription` | `nursing_prescription` | Alta | Alta |
| `NursingPrescriptionItem` | `nursing_prescription_item` | Alta | Media |
| `NursingOrder` | `nursing_order` | Alta | Alta |
| `NursingOrderPrescription` | `nursing_order_prescription` | Media | Media |

### 5.3 Funcionalidades con dependencia externa

| Funcionalidad | Bundle legacy dependiente | Estrategia tenant |
|---------------|---------------------------|-------------------|
| Cargos artículos | `BodegaRepository` (Logística) | API interna `/api/logistics/articles/search` |
| Cargos prestaciones | `AccionClinicaRepository` (RegistroClínico) | `CareInterventionRepository` (ya existe) |
| Cargos paquetes | Paquetes de servicios | `ServicePackageRepository` (ya existe) |
| Formularios RCH | RegistroClínicoHospitalario | Stub en Fase 4, implementar con módulo RCH |
| Signos vitales (lectura) | RegistroClínicoUrgencia | API interna `/api/clinical/vitals/{patientId}` |
| Recetas con stock | BodegaRepository + RchReceta | Nuevo `NursingPrescription` + búsqueda artículos |
| Folio retenido | `HermesBundle` | Adaptar a `TenantEntityManager` query directa |

### 5.4 Sesiones a eliminar

| Variable de sesión legacy | Reemplazo |
|---------------------------|-----------|
| `enfermeriaServicio` (id de servicio activo) | Parámetro de ruta `{serviceId}` |
| `ConcurrenciaPacienteEnfermeria` (lock usuario) | Entidad `NursingConcurrency` + UUID session |

### 5.5 Patrón `forward()` a eliminar

Legacy usa `$this->forward()` de `ValidarServicio` → `VerServicio`, dejando la URL como `/ValidarServicio`. En tenant: redirect estándar `302` a la ruta del tablero. No hay estado que conservar: el `serviceId` va en la URL.

---

## 6. Plan de Migración por Etapas

### Etapa 1 — Infraestructura base (Sprint 1)

**Objetivo:** Módulo navegable, menú activo, tablero de camas funcional sin acciones.

**Tareas:**

1. **Agregar "Enfermería" al menú** (`src/Service/Menu/MenuDefinition.php`)
   - Sección nueva entre Admisión y Clínico
   - Submenú: "Tablero" → `app_nursing_index`

2. **Crear `NursingController`** (`src/Controller/Nursing/NursingController.php`)
   ```
   #[Route('/nursing', name: 'app_nursing_')]
   class NursingController extends AbstractTenantAwareController
   GET  /nursing/               → index()         → selector de servicio
   GET  /nursing/service/{id}/board → board()     → tablero de camas
   GET  /nursing/service/{id}/board/list → boardList() → vista lista
   ```
   - `index()`: carga servicios disponibles para el usuario según rol
   - `board()`: carga camas + salas + pacientes + solicitudes pendientes
   - Elimina sesión `enfermeriaServicio`; usa `{id}` en URL

3. **Crear entidad `NursingConcurrency`**
   ```php
   // src/Entity/Tenant/NursingConcurrency.php
   #[ORM\Entity]
   #[ORM\Table(name: 'nursing_concurrency')]
   class NursingConcurrency {
       int $id, int $admissionRecordId, int $userId,
       string $sessionId, \DateTimeImmutable $lockedAt
   }
   ```
   - Migración Doctrine

4. **Templates base**
   - `templates/nursing/index.html.twig` — layout módulo con Turbo Frame `nursing-board`
   - `templates/nursing/service/_selector.html.twig` — lista servicios (tarjetas)
   - `templates/nursing/service/_board.html.twig` — grilla camas por sala
   - `assets/controllers/nursing/board_controller.js` — Stimulus vacío base

**Archivos de referencia legacy:**
- `_Default/VerServicioController.php:30–82`
- `Resources/views/_Default/verServicio.html.twig`
- `Resources/views/_Default/_camasPorServicio.html.twig`
- `Resources/views/_Default/_camasVirtualesPorServicio.html.twig`

---

### Etapa 2 — Gestión de camas (Sprint 1–2)

**Objetivo:** Cambio de estado de cama, asignación en urgencia.

**Tareas:**

1. **Crear `NursingBedController`** (`src/Controller/Nursing/NursingBedController.php`)
   ```
   GET+POST /nursing/bed/{id}/status  → status()   → form estado
   POST     /nursing/bed/{id}/assign  → assign()   → asignar cama (urgencia)
   ```
   - Ref legacy: `_Default/CamaController.php`, `ApiC/CamaController.php:enfermeriaAsignarCama`

2. **Crear `NursingBedStatusType`** (`src/Form/Nursing/NursingBedStatusType.php`)
   - Campos: estado (catalog), observación
   - Ref legacy: `Form/Type/_Default/EstadoCamaType.php`

3. **Template** `templates/nursing/bed/_status_form.html.twig`
   - Dentro de Turbo Frame `nursing-board` → respuesta parcial

**Entidades reutilizadas:** `Bed` (ya tiene campo `status`), `BedType`

---

### Etapa 3 — Vista de paciente y pestañas de lectura (Sprint 2)

**Objetivo:** Abrir la ficha del paciente con pestañas de solo lectura: resumen, recetas, indicaciones, cargos, devoluciones, hospitalizaciones.

**Tareas:**

1. **Crear `NursingPatientController`** (`src/Controller/Nursing/NursingPatientController.php`)
   ```
   GET /nursing/patient/{id}                → show()         → ficha completa
   GET /nursing/patient/{id}/prescriptions  → prescriptions()
   GET /nursing/patient/{id}/orders         → orders()
   GET /nursing/patient/{id}/charges        → charges()
   GET /nursing/patient/{id}/returns        → returns()
   GET /nursing/patient/{id}/forms          → forms()
   ```
   - `{id}` = `AdmissionRecord::id`
   - Gestionar `NursingConcurrency` (lock al abrir, unlock al cerrar)
   - Ref legacy: `_Default/VerPacienteController.php`

2. **Templates**
   - `templates/nursing/patient/_info.html.twig` — cabecera paciente
   - `templates/nursing/patient/_tabs.html.twig` — nav pestañas (Turbo)
   - `templates/nursing/patient/tabs/_summary.html.twig`
   - `templates/nursing/patient/tabs/_prescriptions.html.twig`
   - `templates/nursing/patient/tabs/_orders.html.twig`
   - `templates/nursing/patient/tabs/_charges.html.twig`
   - `templates/nursing/patient/tabs/_returns.html.twig`
   - Ref legacy: `Resources/views/_Default/Paciente/Pestanias/`

3. **Stimulus `patient_controller.js`**
   - Gestiona apertura/cierre del panel lateral
   - Envía DELETE a `/nursing/patient/{id}/unlock` al salir (concurrencia)

---

### Etapa 4 — Acciones de paciente (Sprint 3)

**Objetivo:** Cambio de cama, traslado, alta.

**Entidades nuevas:**

```php
// NursingTransfer — src/Entity/Tenant/NursingTransfer.php
class NursingTransfer {
    int $id, AdmissionRecord $admissionRecord,
    Service $originService, Service $destinationService,
    User $requestedBy, \DateTimeImmutable $requestedAt,
    ?User $cancelledBy, ?\DateTimeImmutable $cancelledAt,
    string $status   // 'pending' | 'confirmed' | 'cancelled'
}

// NursingDischarge — src/Entity/Tenant/NursingDischarge.php
class NursingDischarge {
    int $id, AdmissionRecord $admissionRecord,
    string $dischargeType,  // 'alta' | 'fallecimiento' | 'traslado_externo'
    string $condition,      // 'bueno' | 'regular' | 'grave'
    \DateTimeImmutable $dischargedAt,
    ?User $cancelledBy, ?\DateTimeImmutable $cancelledAt
}
```

**Controladores:**

```
POST /nursing/patient/{id}/bed/change           → NursingPatientBedController::change()
POST /nursing/patient/{id}/transfer/create      → NursingPatientTransferController::create()
POST /nursing/patient/{id}/transfer/cancel      → NursingPatientTransferController::cancel()
POST /nursing/patient/{id}/discharge/create     → NursingPatientDischargeController::create()
POST /nursing/patient/{id}/discharge/cancel     → NursingPatientDischargeController::cancel()
```

Ref legacy:
- `_Default/VerPaciente/CambiarCamaController.php`
- `_Default/VerPaciente/TraspasarController.php`
- `_Default/VerPaciente/DarAltaController.php`

**Form types:**
- `NursingTransferType` (origen solo lectura, destino select de servicios)
- `NursingDischargeType` (tipo, condición, diagnóstico de egreso)

---

### Etapa 5 — Recetas e indicaciones (Sprint 3–4)

**Objetivo:** CRUD completo de recetas médicas e indicaciones farmacológicas.

**Entidades nuevas:**

```php
// NursingPrescription — src/Entity/Tenant/NursingPrescription.php
class NursingPrescription {
    int $id, AdmissionRecord $admissionRecord,
    Person $prescribedBy,   // médico tratante
    \DateTimeImmutable $prescribedAt,
    string $status,         // 'active' | 'completed' | 'cancelled'
    ?string $complement,
    NursingPrescriptionItem[] $items
}

// NursingPrescriptionItem — src/Entity/Tenant/NursingPrescriptionItem.php
class NursingPrescriptionItem {
    int $id, NursingPrescription $prescription,
    string $articleCode, string $articleName,
    string $dose, string $route, string $frequency,
    int $quantity
}

// NursingOrder — src/Entity/Tenant/NursingOrder.php
class NursingOrder {
    int $id, AdmissionRecord $admissionRecord,
    Person $orderedBy,
    \DateTimeImmutable $orderedAt,
    string $orderType,    // 'farmacologica' | 'procedimiento'
    string $description,
    string $status        // 'active' | 'completed' | 'cancelled'
}
```

**Controladores:**
```
GET+POST /nursing/patient/{id}/prescription/create    → NursingPrescriptionController::create()
GET+POST /nursing/patient/{id}/prescription/{pid}/edit → edit()
POST     /nursing/patient/{id}/prescription/{pid}/cancel → cancel()
POST     /nursing/patient/{id}/prescription/{pid}/complement → complement()
GET+POST /nursing/patient/{id}/order/create           → NursingOrderController::create()
GET+POST /nursing/patient/{id}/order/{oid}/edit       → edit()
POST     /nursing/patient/{id}/order/{oid}/cancel     → cancel()
```

Ref legacy:
- `_Default/VerPaciente/RecetaController.php` (crear/editar/anular/complementar)
- `_Default/VerPaciente/IndicacionController.php`
- `Form/Type/_Default/CrearRecetaMedicoType.php`
- `Form/Type/_Default/CrearIndicacionType.php`

**Búsqueda de artículos:** vía `NursingApiController::articles()` — JSON autocomplete
```
GET /api/nursing/articles?q=amoxicilina  → app_nursing_api_articles
```

---

### Etapa 6 — Cargos al paciente (Sprint 4)

**Objetivo:** Cargar prestaciones, artículos de bodega y paquetes al paciente hospitalizado.

**Servicio:**

```php
// src/Service/Nursing/NursingChargeService.php
class NursingChargeService {
    chargeInterventions(AdmissionRecord $record, array $items): void
    chargeArticles(AdmissionRecord $record, array $items): void
    chargePackages(AdmissionRecord $record, array $packages): void
    cancelCharge(int $chargeId, string $type): void
    rejectCharge(int $chargeId, string $type): void
}
```

Ref legacy:
- `Services/CargosArticulosService.php`
- `Services/CargosPacientesPaquetesService.php`
- `Services/CargosAccionesClinicasService.php`
- `Services/PrestacionesExtrasService.php`

**Controlador:**
```
GET      /nursing/patient/{id}/charges               → NursingChargeController::index()
GET+POST /nursing/patient/{id}/charge/articles       → chargeArticles()
GET+POST /nursing/patient/{id}/charge/interventions  → chargeInterventions()
GET+POST /nursing/patient/{id}/charge/packages       → chargePackages()
POST     /nursing/patient/{id}/charge/{cid}/cancel   → cancelCharge()
```

**API endpoints JSON:**
```
GET /api/nursing/articles?q=X           → app_nursing_api_articles
GET /api/nursing/interventions?q=X      → app_nursing_api_interventions
GET /api/nursing/packages?q=X           → app_nursing_api_packages
GET /api/nursing/packages/{id}/items    → app_nursing_api_package_items
```

Ref legacy: `DefaultController::busquedaArticulosCarga`, `busquedaPrestaciones`, `busquedaPaquetes`, `obtenerArticulosPorPaquete`

---

### Etapa 7 — Solicitudes entre servicios (Sprint 4–5)

**Objetivo:** Ver y gestionar solicitudes de admisión / egreso / traslado desde otros servicios.

```
GET  /nursing/request/admissions/{serviceId}      → NursingRequestController::admissions()
GET  /nursing/request/transfers/{serviceId}       → transfers()
GET  /nursing/request/discharges/{serviceId}      → discharges()
POST /nursing/request/admit/{admissionId}         → admit()
```

Ref legacy:
- `_Default/SolicitudesController::verSolicitudesAdmision` (`SolicitudesController.php`)
- `_Default/SolicitudesController::verEgresosPorAlta`
- `_Default/SolicitudesController::ingresarPacienteAlServicio`
- Form: `Form/Type/_Default/IngresoPacienteType.php`

Templates de referencia:
- `Resources/views/_Default/Solicitudes/verSolicitudesAdmision.html.twig`
- `Resources/views/_Default/Solicitudes/ingresarPacienteAlServicio.html.twig`

---

### Etapa 8 — Devoluciones e impresión (Sprint 5)

**Objetivo:** CRUD devoluciones + PDF de egreso.

```
GET  /nursing/patient/{id}/returns             → NursingReturnController::index()
GET+POST /nursing/patient/{id}/return/create   → create()
POST /nursing/patient/{id}/return/{rid}/cancel → cancel()
GET  /nursing/print/{id}/discharge             → NursingPrintController::discharge()
```

- Ref legacy: `_Default/VerPaciente/DevolucionController.php`
- PDF: `Resources/views/_Default/Paciente/imprimirEgresoPDF.html.twig`
- Usar `data-turbo="false"` + `target="_blank"` para impresión

---

## 7. Matriz de Riesgos

| # | Riesgo | Probabilidad | Impacto | Mitigación |
|---|--------|-------------|---------|------------|
| R1 | Modelo de concurrencia incompleto → paciente editado por dos usuarios | Media | Alto | Implementar `NursingConcurrency` en Etapa 1 antes de cualquier acción de escritura |
| R2 | `AdmissionRecord` no cubre todos los campos de `RelCamaPaciente` | Alta | Alto | Revisar `RelCamaPacienteRepository.php` campo por campo antes de Etapa 3 |
| R3 | API de artículos (Logística) no disponible al momento de migrar Cargos | Media | Medio | Implementar stub con datos de muestra; flag de configuración en `.env` |
| R4 | Formularios RCH/RCHU dependen de RegistroClínicoHospitalaroBundle | Alta | Bajo | Stub con mensaje "En desarrollo" en Fase 4; migrar con módulo RCH |
| R5 | Roles no mapeados 1:1 al modelo de permisos tenant | Media | Medio | Revisar `MaintainerRolePermissionController` y mapear antes de Etapa 1 |
| R6 | `obtenerRutaApiModulo()` se usa para bifurcar lógica AND vistas | Alta | Medio | Reemplazar por parámetro de tenant `nursing.mode` en `TenantEntityManager`; eliminar bifurcación de código |
| R7 | Migración de datos históricos (`rel_cama_paciente` → `admission_record`) | Media | Alto | Script de migración separado; no bloquea desarrollo funcional |
| R8 | Carga de artículos con stock requiere integración Bodega | Media | Medio | Validar disponibilidad en `NursingChargeService`; retornar error amigable si stock = 0 |

---

## 8. Plan de Pruebas

### 8.1 Pruebas unitarias

| Clase | Tests obligatorios |
|-------|--------------------|
| `NursingChargeService` | `chargeArticles` suma cantidades, `chargePackages` crea ítems por detail, `cancelCharge` lanza excepción si estado incorrecto |
| `NursingConcurrencyRepository` | `acquireLock` inserta, `releaseLock` elimina, `isLocked` devuelve true/false |
| `NursingTransfer` entidad | `cancel()` sólo si status=pending, transición de estados |
| `NursingDischarge` entidad | `cancel()` sólo si reciente (< 24h), campos obligatorios |

### 8.2 Pruebas funcionales (controlador)

| Escenario | Resultado esperado |
|-----------|--------------------|
| GET `/nursing/` sin rol clínico | Redirect a login o 403 |
| GET `/nursing/service/{id}/board` con servicio válido | HTTP 200 + frame `nursing-board` con camas |
| POST `/nursing/bed/{id}/status` con estado válido | Turbo Stream actualiza celda en tablero |
| GET `/nursing/patient/{id}` por primera vez | Crea `NursingConcurrency`; HTTP 200 |
| GET `/nursing/patient/{id}` con lock activo de otro usuario | HTTP 200 con aviso de bloqueo |
| POST `/nursing/patient/{id}/discharge/create` con campos completos | Actualiza `AdmissionRecord.status` = 'discharged' |
| GET `/nursing/print/{id}/discharge` | PDF generado (MIME `application/pdf`) |

### 8.3 Pruebas de integración

- Flujo completo: Admisión crea `AdmissionRecord` → Enfermería lo ve en tablero → ingresa al servicio → da de alta
- Flujo traslado: solicitud desde servicio A → enfermería servicio B acepta → cama liberada en A → ocupada en B

---

## 9. Plan de Despliegue

### 9.1 Pre-requisitos

- [ ] Rama `feature/nursing` creada desde `feature/admission` (depende de entidades Patient, CareType, etc.)
- [ ] Variables de entorno `NURSING_MODE` o parámetro de tenant configurado
- [ ] Migraciones de nuevas entidades ejecutadas en staging
- [ ] Seeds de datos: servicios, salas, camas de prueba en BD tenant

### 9.2 Orden de despliegue

```
1. doctrine:migrations:migrate                  # Nuevas tablas nursing_*
2. cache:clear --env=prod
3. asset-map:compile
4. Verificar /nursing/ accesible con rol clínico
5. Smoke test: cargar tablero de servicio con al menos 1 cama
```

### 9.3 Rollback

- Las tablas `nursing_*` son nuevas; no afectan datos existentes
- Revertir migración Doctrine basta para desactivar el módulo
- Desactivar ítem de menú en `MenuDefinition.php` como flag de emergencia

---

## 10. Estimación de Esfuerzo

| Etapa | Descripción | Complejidad | Días estimados |
|-------|-------------|-------------|----------------|
| 1 | Infraestructura base + tablero camas | Alta | 4–5 |
| 2 | Gestión de camas (estado + asignación) | Media | 2–3 |
| 3 | Vista paciente + pestañas lectura | Alta | 4–5 |
| 4 | Acciones: cambio cama / traslado / alta | Alta | 4–5 |
| 5 | Recetas e indicaciones | Alta | 5–6 |
| 6 | Cargos (prestaciones, artículos, paquetes) | Muy Alta | 6–8 |
| 7 | Solicitudes entre servicios | Media | 3–4 |
| 8 | Devoluciones + PDF egreso | Media | 2–3 |
| **Total** | | | **30–39 días** |

> Nota: Las estimaciones asumen un desarrollador familiarizado con el stack Symfony 7 / Turbo / Stimulus.

---

## 11. Definición de Done (DoD)

Un ítem se considera **completado** cuando cumple todos los siguientes criterios:

- [ ] Controlador con `#[Route]` PHP Attributes (sin YAML)
- [ ] Nombres de ruta con prefijo `app_nursing_*`
- [ ] `TenantEntityManager` inyectado vía constructor
- [ ] Cero jQuery, cero JS suelto — todo Turbo Frames + Stimulus
- [ ] Validaciones en entidad con `#[Assert\*]`, no en FormType
- [ ] Respuestas parciales vía Turbo Frames o Turbo Streams
- [ ] Impresión/PDF con `data-turbo="false"` + `target="_blank"`
- [ ] Prueba unitaria para lógica de negocio en Service/Repository
- [ ] Prueba funcional básica del controlador (happy path + error)
- [ ] No hay referencias a `HermesBundle` directas; se usan repositorios tenant
- [ ] No hay variables de sesión para contexto (usar parámetros de ruta)
- [ ] Sin `forward()` — usar redirect estándar
- [ ] `NursingConcurrency` gestionada correctamente (acquire + release)
- [ ] Template extiende `app_layout.html.twig` o usa `turbo_stream_response`
- [ ] Sin inline styles — estilos en `app.css` bajo sección `/* === ENFERMERIA === */`

---

## 12. Anexos

### A. Inventario completo de rutas legacy `_Default`

| Ruta legacy | Ruta tenant | Controlador legacy |
|-------------|-------------|-------------------|
| `/Inicio` | `/nursing/` | `_Default/BuscadorServicioController::indexAction` |
| `/ValidarServicio` | `/nursing/service/{id}/board` | `_Default/BuscadorServicioController::validarServicioAction` |
| `/VerServicio` | `/nursing/service/{id}/board` | `_Default/VerServicioController::indexAction` |
| `/VerServicio/Listado` | `/nursing/service/{id}/board/list` | `_Default/VerServicioController::listado` |
| `/VerPaciente` | `/nursing/patient/{id}` | `_Default/VerPacienteController::indexAction` |
| `/VerEstadoCama` | `/nursing/bed/{id}/status` | `_Default/CamaController::verEstado` |
| `/CambiarEstadoCama` | `/nursing/bed/{id}/status` (POST) | `_Default/CamaController::cambiarEstado` |
| `/VerSolicitudesAdmision` | `/nursing/request/admissions/{serviceId}` | `_Default/SolicitudesController::verSolicitudesAdmision` |
| `/VerEgresosOtrosServicios` | `/nursing/request/transfers/{serviceId}` | `_Default/SolicitudesController::verEgresosOtrosServicios` |
| `/VerEgresosPorAlta` | `/nursing/request/discharges/{serviceId}` | `_Default/SolicitudesController::verEgresosPorAlta` |
| `/VerSolicitudesOtrosServicios` | `/nursing/request/transfers/{serviceId}` | `_Default/SolicitudesController::verSolicitudesOtrosServicios` |
| `/verFormularioIngresarPacienteAlServicio` | `/nursing/request/admit/{id}/form` | `_Default/SolicitudesController::verFormularioIngresarPacienteAlServicio` |
| `/IngresarPacienteAlServicio` | `/nursing/request/admit/{id}` (POST) | `_Default/SolicitudesController::ingresarPacienteAlServicio` |
| `/ValidarCambiarCamaPaciente` | `/nursing/patient/{id}/bed/change` | `_Default/VerPaciente/CambiarCamaController::validarCambiarCamaPaciente` |
| `/ValidarSolicitarTraspasoPaciente` | `/nursing/patient/{id}/transfer/create` | `_Default/VerPaciente/TraspasarController::validarSolicitarTraspasoPaciente` |
| `/ValidarAnularTraspasoPaciente` | `/nursing/patient/{id}/transfer/cancel` | `_Default/VerPaciente/TraspasarController::validarAnularTraspasoPaciente` |
| `/ValidarDarAltaPaciente` | `/nursing/patient/{id}/discharge/create` | `_Default/VerPaciente/DarAltaController::guardarDarAlta` |
| `/ValidarAnularDarAltaPaciente` | `/nursing/patient/{id}/discharge/cancel` | `_Default/VerPaciente/DarAltaController::validarAnularDarAltaPaciente` |
| `/VerRecetasPaciente` | `/nursing/patient/{id}/prescriptions` | `_Default/VerPaciente/RecetaController::verRecetas` |
| `/VerDetalleReceta` | `/nursing/patient/{id}/prescription/{pid}` | `_Default/VerPaciente/RecetaController::verDetalleReceta` |
| `/VerCrearReceta` | `/nursing/patient/{id}/prescription/create` | `_Default/VerPaciente/RecetaController::verCrearReceta` |
| `/CrearReceta` | `/nursing/patient/{id}/prescription/create` (POST) | `_Default/VerPaciente/RecetaController::crearReceta` |
| `/verEditarReceta` | `/nursing/patient/{id}/prescription/{pid}/edit` | `_Default/VerPaciente/RecetaController::verEditarReceta` |
| `/editarReceta` | `/nursing/patient/{id}/prescription/{pid}/edit` (POST) | `_Default/VerPaciente/RecetaController::editarReceta` |
| `/anularReceta` | `/nursing/patient/{id}/prescription/{pid}/cancel` (POST) | `_Default/VerPaciente/RecetaController::anularReceta` |
| `/verComplementarReceta` | `/nursing/patient/{id}/prescription/{pid}/complement` | `_Default/VerPaciente/RecetaController::verComplementarReceta` |
| `/complementarReceta` | `/nursing/patient/{id}/prescription/{pid}/complement` (POST) | `_Default/VerPaciente/RecetaController::complementarReceta` |
| `/VerCargosPaciente` | `/nursing/patient/{id}/charges` | `_Default/VerPaciente/CargosPacienteController::verCargos` |
| `/verCargaPrestaciones` | `/nursing/patient/{id}/charge/interventions` | `_Default/VerPaciente/CargosPacientePrestacionesController::verCargaPrestaciones` |
| `/cargaPrestaciones` | `/nursing/patient/{id}/charge/interventions` (POST) | `_Default/VerPaciente/CargosPacientePrestacionesController::cargaPrestaciones` |
| `/anularPrestacion` | `/nursing/patient/{id}/charge/intervention/{cid}/cancel` | `_Default/VerPaciente/CargosPacientePrestacionesController::anularPrestacion` |
| `/anularPaquete` | `/nursing/patient/{id}/charge/package/{cid}/cancel` | `_Default/VerPaciente/CargosPacientePaquetesController::anularPaquete` |
| `/verCargaArticulos` | `/nursing/patient/{id}/charge/articles` | `_Default/VerPaciente/CargosPacienteArticulosController::verCargaArticulos` |
| `/cargaArticulos` | `/nursing/patient/{id}/charge/articles` (POST) | `_Default/VerPaciente/CargosPacienteArticulosController::cargaArticulos` |
| `/anularArticulo` | `/nursing/patient/{id}/charge/article/{cid}/cancel` | `_Default/VerPaciente/CargosPacienteArticulosController::anularArticulo` |
| `/verCargaPaquetes` | `/nursing/patient/{id}/charge/packages` | `_Default/VerPaciente/CargosPacientePaquetesController::verCargaPaquetes` |
| `/cargaPaquetes` | `/nursing/patient/{id}/charge/packages` (POST) | `_Default/VerPaciente/CargosPacientePaquetesController::cargaPaquetes` |
| `/VerIndicacionesPaciente` | `/nursing/patient/{id}/orders` | `_Default/VerPaciente/IndicacionController::verIndicaciones` |
| `/verCrearIndicacion` | `/nursing/patient/{id}/order/create` | `_Default/VerPaciente/IndicacionController::verCrearIndicacion` |
| `/crearIndicacion` | `/nursing/patient/{id}/order/create` (POST) | `_Default/VerPaciente/IndicacionController::crearIndicacion` |
| `/anularIndicacion` | `/nursing/patient/{id}/order/{oid}/cancel` (POST) | `_Default/VerPaciente/IndicacionController::anularIndicacion` |
| `/verCrearDevolucion` | `/nursing/patient/{id}/return/create` | `_Default/VerPaciente/DevolucionController::verCrearDevolucion` |
| `/crearDevolucion` | `/nursing/patient/{id}/return/create` (POST) | `_Default/VerPaciente/DevolucionController::crearDevolucion` |
| `/anularDevolucion` | `/nursing/patient/{id}/return/{rid}/cancel` (POST) | `_Default/VerPaciente/DevolucionController::anularDevolucion` |
| `/VerDevolucionesPaciente` | `/nursing/patient/{id}/returns` | `_Default/VerPaciente/DevolucionController::verDevoluciones` |
| `/ImprimirEgreso` | `/nursing/print/{id}/discharge` | `VerPaciente/ImprimirEgreso` |
| `/anularAsignacionCama` | `/nursing/patient/{id}/bed/unassign` | `_Default/VerPaciente::anularAsignacionCama` |
| `/obtenerFormularios` | `/nursing/patient/{id}/forms` | `_Default/VerPaciente/VerFormularios/VerFormularios::index` |
| `/obtenerFormularioSeleccionado` | `/nursing/patient/{id}/form/select` | `VerFormularios::buscarFormulario` |
| `/guardarFormularioSeleccionado` | `/nursing/patient/{id}/form/save` (POST) | `VerFormularios::guardarFormulario` |

### B. Inventario de rutas legacy `ApiC` (adicionales)

| Ruta legacy ApiC | Ruta tenant | Observación |
|------------------|-------------|-------------|
| `/ValidarServicioC` | Unificado en `/nursing/service/{id}/board` | `ApiC/BuscadorServicioController::validarServicio` — guardaba servicio en sesión |
| `/VerServicioC` | Unificado en `/nursing/service/{id}/board` | `ApiC/VerServicioController::index` |
| `/VerEstadoCamaC` | Unificado en `/nursing/bed/{id}/status` | `ApiC/CamaController::verEstado` |
| `/enfermeriaAsignarCama` | `/nursing/bed/{id}/assign` | `ApiC/CamaController::enfermeriaAsignarCama` — solo urgencia |

### C. Endpoints JSON utilitarios (DefaultController legacy → NursingApiController tenant)

| Legacy route | Tenant route | Controller::action legacy |
|--------------|-------------|--------------------------|
| `/ObtenerCantidadCamasDisponiblesPorServicio` | `/api/nursing/bed-count/{serviceId}` | `DefaultController::obtenerCantidadCamasDisponiblesPorServicio` |
| `/busquedaArticulosReceta` | `/api/nursing/articles?context=prescription` | `DefaultController::busquedaArticulosReceta` |
| `/busquedaArticulosCarga` | `/api/nursing/articles?context=charge` | `DefaultController::busquedaArticulosCarga` |
| `/busquedaPrestaciones` | `/api/nursing/interventions` | `DefaultController::busquedaPrestaciones` |
| `/busquedaPaquetes` | `/api/nursing/packages` | `DefaultController::busquedaPaquetes` |
| `/obtenerArticulosPorPaquete` | `/api/nursing/packages/{id}/articles` | `DefaultController::obtenerArticulosPorPaquete` |
| `/obtenerPrestacionesPorPaquete` | `/api/nursing/packages/{id}/interventions` | `DefaultController::obtenerPrestacionesPorPaquete` |
| `/validarFolioRetenido` | `/api/nursing/folio/validate` | `DefaultController::validarFolioRetenido` |
| `/obtenerIdServicioSession` | Eliminado — usar parámetro de ruta | `DefaultController::obtenerIdServicioSession` |
| `/obtenerIndicaciones` | `/api/nursing/patient/{id}/orders/table` | `_Default/VerPaciente::dataTaleIndicacionesEnfermeria` |

### D. Servicios legacy → Tenant

| Servicio legacy | Clase tenant | Método equivalente |
|----------------|-------------|-------------------|
| `CargosArticulosService::cargarArticulos()` | `NursingChargeService::chargeArticles()` | — |
| `CargosArticulosService::anularArticulo()` | `NursingChargeService::cancelArticleCharge()` | — |
| `CargosPacientesPaquetesService::cargarPaquetes()` | `NursingChargeService::chargePackages()` | — |
| `CargosPacientesPaquetesService::anularPaquete()` | `NursingChargeService::cancelPackageCharge()` | — |
| `CargosAccionesClinicasService::cargarPrestaciones()` | `NursingChargeService::chargeInterventions()` | — |
| `CargosAccionesClinicasService::anularPrestacion()` | `NursingChargeService::cancelInterventionCharge()` | — |
| `PrestacionesExtrasService::cargaPrestacionesExtras()` | `NursingChargeService::chargeExtraInterventions()` | Fusionado |

### E. Decisiones de diseño

| Decisión | Rationale |
|----------|-----------|
| Unificar `_Default` + `ApiC` en un único controlador | La diferencia real es de vista (clínica vs urgencia), no de negocio. Un flag de tenant `nursing.apiMode` reemplaza `obtenerRutaApiModulo()`. |
| Eliminar `$this->forward()` | Anti-patrón; genera confusión de URL. Reemplazar por redirect. |
| Eliminar sesión `enfermeriaServicio` | Los parámetros de ruta son más seguros, predecibles y testeables. |
| `NursingConcurrency` como entidad, no como sesión PHP | Permite limpiar locks huérfanos con un cron; visible en auditoría. |
| Formularios RCH como stub en Fase 4 | Desbloquea el módulo sin bloquear RegistroClínico. Muestra mensaje "en desarrollo" en la pestaña. |
| `NursingChargeService` unificado | Los 4 servicios legacy son variantes del mismo patrón (ítem → carga → anular). Un servicio único reduce duplicación. |
