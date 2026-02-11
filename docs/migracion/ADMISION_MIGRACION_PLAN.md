# Plan de Migración: Módulo Admisión (AdmisionBundle → Symfony 7.4)

> **Para GitHub Copilot**: Este documento es la fuente de verdad para implementar el módulo de Admisión en el nuevo sistema Symfony 7.4. Seguir el orden de las etapas. No inventar funcionalidad nueva. No usar jQuery. Solo Stimulus/ESM.

---

## Contexto del proyecto

| Item | Valor |
|---|---|
| Legacy (fuente) | `/var/www/html/melisa_prod/src/Rebsol/AdmisionBundle` |
| Nuevo sistema (destino) | `/var/www/html/melisa_tenant` |
| Referencia de patrones | `/var/www/html/melisa_tenant/src/Controller/Maintainers/` |
| Framework nuevo | Symfony 7.4, PHP 8.3 |
| UI | Turbo Frames + Stimulus (sin jQuery, sin JS suelto) |
| Multi-tenancy | `TenantEntityManager` (Hakam bundle) |

---

## Arquitectura de archivos a crear

```
src/
├── Controller/
│   └── Admission/
│       ├── AdmissionController.php           ← index + búsqueda paciente
│       ├── AdmissionWizardController.php     ← pasos 1-4 wizard admisión
│       ├── EmergencyAdmissionController.php  ← flujo urgencia
│       ├── PatientRegistrationController.php ← agregar/editar paciente
│       └── AdmissionPrintController.php      ← impresiones PDF (data-turbo="false")
├── Form/
│   └── Admission/
│       ├── PatientSearchType.php
│       ├── PatientRegistrationType.php
│       ├── PatientRegistrationUrgencyType.php
│       ├── AdmissionStep2Type.php            ← financiador/convenio
│       ├── AdmissionStep3Type.php            ← cama/servicio
│       └── ForeignPatientType.php            ← extranjero sin RUT
├── Service/
│   └── Admission/
│       ├── AdmissionService.php              ← lógica de negocio
│       ├── PatientSearchService.php          ← búsqueda por RUT/nombre
│       └── AdmissionPrintService.php         ← generación PDF

templates/
└── admission/
    ├── index.html.twig                       ← listado + búsqueda
    ├── emergency/
    │   ├── index.html.twig
    │   └── _form.html.twig
    ├── wizard/
    │   ├── step1.html.twig                   ← datos paciente
    │   ├── step2.html.twig                   ← financiador/convenio
    │   ├── step3.html.twig                   ← cama/servicio
    │   └── complete.html.twig
    ├── view.html.twig                        ← ver admisión creada
    └── print/
        ├── admission_pdf.html.twig           ← full-page, sin Turbo
        └── urgency_pdf.html.twig

assets/controllers/admission/
└── wizard_controller.js                      ← Stimulus para pasos wizard
```

---

## ETAPA 1 — Menú "Admisión" en el sidebar

### 1.1 Archivo a modificar: `src/Service/Menu/MenuDefinition.php`

**Método:** `getDefaultMenuStructure()`

**Qué hacer:** Agregar el bloque siguiente DESPUÉS del ítem `pacientes` y ANTES del ítem `mantenedores`.

```php
[
    'name' => 'admision',
    'label' => 'Admisión',
    'icon' => 'bx bx-clinic',
    'module' => null,
    'children' => [
        [
            'name' => 'admission_general',
            'label' => 'Admisión Hospitalaria',
            'icon' => 'bx bx-user-check',
            'route' => 'app_admission_hospitalization_index',
            'module' => 'admission',
            'children' => []
        ],
        [
            'name' => 'admission_emergency',
            'label' => 'Admisión Urgencia',
            'icon' => 'bx bx-first-aid',
            'route' => 'app_admission_emergency_index',
            'module' => 'admission',
            'children' => []
        ],
        [
            'name' => 'admission_pre',
            'label' => 'Pre-Admisión',
            'icon' => 'bx bx-time',
            'route' => 'app_admission_pre_index',
            'module' => 'admission',
            'children' => []
        ],
    ]
],
```

### 1.2 Archivo a modificar: `templates/partials/_sidebar.html.twig`

> ⚠️ **IMPORTANTE**: El archivo correcto es `_sidebar.html.twig` (con guión bajo). El archivo `sidebar.html.twig` sin guión bajo es la plantilla demo Velzon — NO modificar ese.

**Línea a cambiar (~46):**
```twig
{# ANTES: #}
{% if item.module|default(false) and 'maintenance_' in item.module %}

{# DESPUÉS: #}
{% if item.module|default(false) and ('maintenance_' in item.module or item.module == 'admission') %}
```

---

## ETAPA 2 — Controller esqueleto (AdmissionController)

### 2.1 Archivo a crear: `src/Controller/Admission/AdmissionController.php`

**Patrón de referencia:** `src/Controller/Maintainers/Admission/EmergencyConsultationTypeController.php`

**Reglas:**
- Extender `AbstractTenantAwareController` (NO `AbstractMantenedorController` — ese es para CRUD de catálogos)
- Usar `#[Route]` PHP Attributes
- Inyectar `TenantEntityManager` vía constructor
- Turbo Frame `maintainer-content` para la página principal de admisión
- Sub-frame `admission-wizard-content` para los pasos del wizard

```php
<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission', name: 'app_admission_')]
class AdmissionController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admission/index.html.twig', [
            'page_title' => 'Admisión',
        ]);
    }
}
```

### 2.2 Template base: `templates/admission/index.html.twig`

**Patrón de referencia:** `templates/maintainers/basic/gender/index.html.twig`

```twig
{% extends 'app_layout.html.twig' %}

{% block title %}Admisión - Melisa{% endblock %}

{% block content %}
    {# El turbo-frame "maintainer-content" ya envuelve este bloque en app_layout.html.twig #}
    <div class="container-fluid px-4 py-4">
        <h1>{{ page_title }}</h1>
        {# Contenido del módulo de admisión #}
    </div>
{% endblock %}
```

---

## ETAPA 3 — Wizard de Admisión (Pasos 1-4)

### Flujo legacy a replicar (sin jQuery)

```
Legacy (Symfony 3):
  GET  /Admision           → AdmisionController:admision        → AdmisionPaso2.html.twig
  POST /Admision           → guarda paso 1, redirect paso 2
  GET  /Admision/Paso2     → AdmisionController:admisionPaso2   → AdmisionPaso2.html.twig
  POST /Admision/Paso2     → guarda financiador/convenio
  GET  /Admision/Paso3     → AdmisionController:admisionPaso3   → nuevaAdmisionPaso3.html.twig
  POST /Admision/Paso3     → guarda cama/servicio
  GET  /Admision/Finalizada → AdmisionController:admisionPaso4  → finalizada.html.twig

Nuevo (Symfony 7.4 con Turbo):
  GET  /admission/wizard/step1                → render step1 en turbo-frame "admission-wizard-content"
  POST /admission/wizard/step1                → si válido → Turbo Stream reemplaza frame con step2
  POST /admission/wizard/step2                → si válido → Turbo Stream reemplaza frame con step3
  POST /admission/wizard/step3                → si válido → crea admisión → redirect a /admission/{id}/view
```

### 3.1 Archivo a crear: `src/Controller/Admission/AdmissionWizardController.php`

```php
<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Form\Admission\AdmissionStep2Type;
use App\Form\Admission\AdmissionStep3Type;
use App\Service\Admission\AdmissionService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboStreamResponse;

#[Route('/admission/wizard', name: 'app_admission_wizard_')]
class AdmissionWizardController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private AdmissionService $admissionService
    ) {}

    /**
     * Paso 1: Confirmar datos del paciente seleccionado
     * El paciente ya fue seleccionado en la pantalla de búsqueda
     */
    #[Route('/step1/{patientId}', name: 'step1', methods: ['GET', 'POST'])]
    public function step1(Request $request, int $patientId): Response
    {
        // TODO: Buscar paciente por $patientId desde TenantEntityManager
        // TODO: Si POST y válido → responder con Turbo Stream que reemplaza
        //       frame "admission-wizard-content" con step2
        // TODO: Si POST e inválido → re-render step1 con errores en el frame

        return $this->render('admission/wizard/step1.html.twig', [
            'patient_id' => $patientId,
        ]);
    }

    /**
     * Paso 2: Financiador / Convenio
     * Los selects de financiador y convenio se cargan dinámicamente
     * vía endpoints JSON + Stimulus controller (sin jQuery)
     */
    #[Route('/step2', name: 'step2', methods: ['GET', 'POST'])]
    public function step2(Request $request): Response
    {
        $form = $this->createForm(AdmissionStep2Type::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Guardar datos paso 2 en sesión o entidad temporal
            // Responder con Turbo Stream → reemplazar frame con step3
        }

        return $this->render('admission/wizard/step2.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * Paso 3: Cama / Servicio
     */
    #[Route('/step3', name: 'step3', methods: ['GET', 'POST'])]
    public function step3(Request $request): Response
    {
        $form = $this->createForm(AdmissionStep3Type::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Crear entidad DatoIngreso en TenantEntityManager
            // TODO: Redirect a /admission/{id}/view
        }

        return $this->render('admission/wizard/step3.html.twig', [
            'form' => $form,
        ]);
    }
}
```

### 3.2 Template wizard: `templates/admission/wizard/step1.html.twig`

```twig
{#
  IMPORTANTE: Este template NO extiende app_layout.html.twig.
  Es un fragmento que vive dentro del turbo-frame "admission-wizard-content".
  El frame padre está en admission/index.html.twig.
#}
<turbo-frame id="admission-wizard-content">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <h4>Paso 1 de 3: Datos del Paciente</h4>

            {# Indicador de progreso #}
            <div data-controller="admission-wizard" data-admission-wizard-step-value="1">
                {# Formulario paso 1 #}
                <form method="post"
                      action="{{ path('app_admission_wizard_step1', {patientId: patient_id}) }}"
                      data-admission-wizard-target="form">

                    {# Campos del paciente #}

                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <a href="{{ path('app_admission_hospitalization_index') }}"
                           class="btn btn-outline-secondary"
                           data-turbo-frame="maintainer-content">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Siguiente →
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</turbo-frame>
```

---

## ETAPA 4 — Flujo Urgencia

### Legacy a replicar:
```
GET  /Urgencia          → Default:indexUrgencia        → búsqueda paciente urgencia
POST /busquedaPacienteUrgencia → Default:buscaPersonaUrgencia
GET  /AdmisionUrgencia  → Admision:AdmisionUrgencia    → formulario urgencia (1 paso)
POST /AdmisionUrgencia  → guarda y finaliza
GET  /verUrgencia       → Admision:verUrgencia         → ver admisión urgencia
```

### 4.1 Archivo a crear: `src/Controller/Admission/EmergencyAdmissionController.php`

```php
<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Form\Admission\PatientRegistrationUrgencyType;
use App\Service\Admission\AdmissionService;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/emergency', name: 'app_admission_emergency_')]
class EmergencyAdmissionController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager,
        private AdmissionService $admissionService
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        return $this->render('admission/emergency/index.html.twig', [
            'page_title' => 'Admisión Urgencia',
        ]);
    }

    #[Route('/create/{patientId}', name: 'create', methods: ['GET', 'POST'])]
    public function create(Request $request, int $patientId): Response
    {
        $form = $this->createForm(PatientRegistrationUrgencyType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // TODO: Crear admisión urgencia (DatoIngreso con tipo urgencia)
            // TODO: Redirect a view
        }

        return $this->render('admission/emergency/_form.html.twig', [
            'form' => $form,
            'patient_id' => $patientId,
        ]);
    }

    #[Route('/{id}/view', name: 'view', methods: ['GET'])]
    public function view(int $id): Response
    {
        // TODO: Buscar admisión urgencia por ID
        return $this->render('admission/view.html.twig', [
            'admission_id' => $id,
        ]);
    }
}
```

---

## ETAPA 5 — Endpoints JSON internos (reemplazo de jQuery AJAX)

### Legacy (jQuery AJAX):
```javascript
// Legacy: jQuery AJAX para cargar selects dinámicos
$.ajax({ url: '/ServiciosPorSucursal', data: {sucursal: id}, success: function(data) { ... } });
$.ajax({ url: '/FinanciadoresPorSucursal', ... });
$.ajax({ url: '/ConveniosPorSucursal', ... });
$.ajax({ url: '/CamasPorServicio', ... });
```

### Nuevo (Stimulus + fetch):

#### 5.1 Crear endpoints JSON: `src/Controller/Admission/AdmissionApiController.php`

```php
<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admission', name: 'app_api_admission_')]
class AdmissionApiController extends AbstractTenantAwareController
{
    public function __construct(
        private TenantEntityManager $entityManager
    ) {}

    /** Servicios disponibles por sucursal (para select dinámico) */
    #[Route('/services', name: 'services', methods: ['GET'])]
    public function services(Request $request): JsonResponse
    {
        $branchId = $request->query->getInt('branch');
        // TODO: Consultar servicios por sucursal desde TenantEntityManager
        // Equivalente legacy: Admision:serviciosPorSucursal
        return $this->json([]);
    }

    /** Financiadores/pagadores por sucursal */
    #[Route('/payers', name: 'payers', methods: ['GET'])]
    public function payers(Request $request): JsonResponse
    {
        $branchId = $request->query->getInt('branch');
        // TODO: Consultar financiadores por sucursal
        // Equivalente legacy: Admision:financiadoresPorSucursal
        return $this->json([]);
    }

    /** Convenios por sucursal y financiador */
    #[Route('/agreements', name: 'agreements', methods: ['GET'])]
    public function agreements(Request $request): JsonResponse
    {
        $branchId = $request->query->getInt('branch');
        $payerId  = $request->query->getInt('payer');
        // TODO: Consultar convenios
        // Equivalente legacy: Admision:conveniosPorSucursal
        return $this->json([]);
    }

    /** Camas disponibles por servicio */
    #[Route('/beds', name: 'beds', methods: ['GET'])]
    public function beds(Request $request): JsonResponse
    {
        $serviceId = $request->query->getInt('service');
        // TODO: Consultar camas por servicio
        // Equivalente legacy: Admision:camasPorServicio
        return $this->json([]);
    }

    /** Profesionales por sucursal */
    #[Route('/professionals', name: 'professionals', methods: ['GET'])]
    public function professionals(Request $request): JsonResponse
    {
        $branchId = $request->query->getInt('branch');
        // TODO: Consultar profesionales
        // Equivalente legacy: Admision:obtenerProfesionalesPorSucursal
        return $this->json([]);
    }
}
```

#### 5.2 Stimulus controller: `assets/controllers/admission/wizard_controller.js`

**Patrón de referencia:** `assets/controllers/mantenedores/list_controller.js`

```javascript
// assets/controllers/admission/wizard_controller.js
// Reemplaza TODO el jQuery del legacy admisión.
// No usar document.ready, no usar $, no usar JS suelto.

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['form', 'serviceSelect', 'payerSelect', 'agreementSelect', 'bedSelect'];
    static values  = { step: Number, branchId: Number };

    connect() {
        // Cargar datos iniciales si ya hay un branch seleccionado
        if (this.branchIdValue) {
            this.loadServices();
            this.loadPayers();
        }
    }

    /**
     * Se dispara cuando cambia el select de sucursal
     * data-action="change->admission-wizard#onBranchChange"
     */
    async onBranchChange(event) {
        const branchId = event.target.value;
        if (!branchId) return;
        this.branchIdValue = branchId;

        await Promise.all([
            this.loadServices(),
            this.loadPayers(),
        ]);
        this.clearSelect(this.bedSelectTarget);
        this.clearSelect(this.agreementSelectTarget);
    }

    /**
     * Se dispara cuando cambia el select de servicio
     * data-action="change->admission-wizard#onServiceChange"
     */
    async onServiceChange(event) {
        const serviceId = event.target.value;
        if (!serviceId) return;
        await this.loadBeds(serviceId);
    }

    /**
     * Se dispara cuando cambia el select de financiador
     * data-action="change->admission-wizard#onPayerChange"
     */
    async onPayerChange(event) {
        const payerId = event.target.value;
        if (!payerId) return;
        await this.loadAgreements(payerId);
    }

    // ── Métodos privados de carga ────────────────────────────────────────────

    async loadServices() {
        const data = await this.fetchJson(`/api/admission/services?branch=${this.branchIdValue}`);
        this.populateSelect(this.serviceSelectTarget, data, 'id', 'name');
    }

    async loadPayers() {
        const data = await this.fetchJson(`/api/admission/payers?branch=${this.branchIdValue}`);
        this.populateSelect(this.payerSelectTarget, data, 'id', 'name');
    }

    async loadAgreements(payerId) {
        const data = await this.fetchJson(`/api/admission/agreements?branch=${this.branchIdValue}&payer=${payerId}`);
        this.populateSelect(this.agreementSelectTarget, data, 'id', 'name');
    }

    async loadBeds(serviceId) {
        const data = await this.fetchJson(`/api/admission/beds?service=${serviceId}`);
        this.populateSelect(this.bedSelectTarget, data, 'id', 'name');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    async fetchJson(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json' }
        });
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.json();
    }

    populateSelect(selectElement, items, valueKey, labelKey) {
        const currentValue = selectElement.value;
        selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';
        items.forEach(item => {
            const option = document.createElement('option');
            option.value  = item[valueKey];
            option.text   = item[labelKey];
            if (String(item[valueKey]) === String(currentValue)) {
                option.selected = true;
            }
            selectElement.add(option);
        });
    }

    clearSelect(selectElement) {
        selectElement.innerHTML = '<option value="">-- Seleccionar --</option>';
    }
}
```

---

## ETAPA 6 — Impresión PDF

### Regla clave:
Los PDFs son **full-page** (no Turbo Frame). Usar `data-turbo="false"` en el link.

### 6.1 Archivo a crear: `src/Controller/Admission/AdmissionPrintController.php`

```php
<?php

namespace App\Controller\Admission;

use App\Controller\AbstractTenantAwareController;
use App\Service\Admission\AdmissionPrintService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admission/print', name: 'app_admission_print_')]
class AdmissionPrintController extends AbstractTenantAwareController
{
    public function __construct(
        private AdmissionPrintService $printService
    ) {}

    /** Equivalente legacy: Admision:imprimirAdmision */
    #[Route('/{id}/{type}', name: 'admission', methods: ['GET'])]
    public function admission(int $id, string $type): Response
    {
        // TODO: Buscar admisión, generar HTML para PDF
        return $this->render('admission/print/admission_pdf.html.twig', [
            'admission' => null, // TODO
        ]);
    }

    /** Equivalente legacy: Admision:imprimirUrgencia */
    #[Route('/urgency/{id}/{type}', name: 'urgency', methods: ['GET'])]
    public function urgency(int $id, string $type): Response
    {
        return $this->render('admission/print/urgency_pdf.html.twig', [
            'admission' => null, // TODO
        ]);
    }
}
```

### 6.2 Template PDF: `templates/admission/print/admission_pdf.html.twig`

```twig
{#
  IMPORTANTE: Este template extiende 'base.html.twig' (NO app_layout.html.twig).
  Los PDFs son full-page, sin sidebar, sin Turbo Frame.
  Los links a este template deben llevar data-turbo="false".
#}
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Admisión</title>
    <style>
        /* Estilos para impresión */
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">Imprimir</button>
        <button onclick="window.close()">Cerrar</button>
    </div>

    <h1>Comprobante de Admisión</h1>
    {# TODO: Contenido del comprobante #}
</body>
</html>
```

**Link en templates (cómo linkear a PDF):**
```twig
<a href="{{ path('app_admission_print_admission', {id: admission.id, type: 'pdf'}) }}"
   target="_blank"
   data-turbo="false"
   class="btn btn-outline-secondary">
    <i class="bx bx-printer"></i> Imprimir
</a>
```

---

## ETAPA 7 — Menú de Mantenedores de Admisión (ya migrados)

Los 3 mantenedores de catálogo ya están migrados. Agregarlos al menú bajo "Admisión":

### Modificar `src/Service/Menu/MenuDefinition.php`

Agregar sub-sección de mantenedores dentro del ítem `admision`:

```php
[
    'name' => 'admission_maintainers',
    'label' => 'Mantenedores Admisión',
    'icon' => 'bx bx-cog',
    'module' => null,
    'children' => [
        [
            'name' => 'emergency_consultation_type',
            'label' => 'Tipos Consulta Urgencia',
            'icon' => 'bx bx-list-ul',
            'route' => 'app_maintainers_admission_emergency_consultation_type_index',
            'module' => 'maintenance_admission',
            'children' => []
        ],
        [
            'name' => 'company_agreement',
            'label' => 'Convenios Empresa',
            'icon' => 'bx bx-buildings',
            'route' => 'app_maintainers_admission_company_agreement_index',
            'module' => 'maintenance_admission',
            'children' => []
        ],
        [
            'name' => 'cancellation_reason',
            'label' => 'Motivos de Anulación',
            'icon' => 'bx bx-x-circle',
            'route' => 'app_maintainers_admission_cancellation_reason_index',
            'module' => 'maintenance_admission',
            'children' => []
        ],
    ]
],
```

Y en `_sidebar.html.twig`, ampliar la condición de Turbo Frame:
```twig
{% if item.module|default(false) and (
    'maintenance_' in item.module or
    item.module == 'admission' or
    item.module == 'maintenance_admission'
) %}
```

---

## Rutas a NO MIGRAR (con evidencia)

| Ruta legacy | Razón | Evidencia |
|---|---|---|
| Todas `Api1/Unab/*` | Veterinario UNAB (mascotas) | `AdmisionMascotaType`, paths `/BuscarMascota*` |
| Todas `ApiC/*` | Cruz Nacional específico | Prefijo `ApiC`, solo 1 tenant |
| `admisionTablaQuirurgica` | Módulo Pabellón | `_Default\TablaQuirurgica` |
| `admisionValidarExtensionPDF` | Marcado para revisión | Comentario `@todo Revisar si se ocupa` |
| `AdmisionResultadoBusquedaAvanzadaPaciente` | Marcado para eliminar | Comentario `#ver si funciona en Servet` |
| `admisionImprimirBrazaleteAdulto` | Marcado para eliminar | Comentario `@todo Eliminar después de migrar` |

---

## Convenciones del proyecto (respetar siempre)

| Convención | Valor |
|---|---|
| Routing | PHP Attributes `#[Route]` — NO YAML |
| Nombres de rutas | `app_{módulo}_{entidad}_{acción}` |
| Prefijo rutas admisión | `/admission/` |
| Prefijo rutas mantendores admisión | `/maintainers/admission/` |
| TenantEntityManager | Inyectar siempre vía constructor |
| Turbo Frame principal | `maintainer-content` (definido en `app_layout.html.twig:12`) |
| Turbo Frame wizard | `admission-wizard-content` (nuevo, dentro del panel) |
| PDF / Impresión | `data-turbo="false"` + `target="_blank"` |
| JS | Solo Stimulus controllers en `assets/controllers/` |
| Sin jQuery | Cero. Usar `fetch()` + Stimulus |
| Confirmaciones eliminar | `confirm_delete_controller.js` + SweetAlert2 (ya existe) |
| Formularios | Symfony Form Types en `src/Form/Admission/` |
| Validación | `#[Assert\*]` en Entity, no en Form |

---

## Archivos de referencia clave (leer antes de implementar)

| Qué entender | Archivo a leer |
|---|---|
| Patrón controller | `src/Controller/Maintainers/Admission/EmergencyConsultationTypeController.php` |
| AbstractMantenedorController | `src/Controller/AbstractMantenedorController.php` |
| AbstractTenantAwareController | `src/Controller/AbstractTenantAwareController.php` |
| Turbo Frame en layout | `templates/app_layout.html.twig` (líneas 1-80) |
| Sidebar dinámico | `templates/partials/_sidebar.html.twig` |
| Sistema de menú | `src/Service/Menu/MenuDefinition.php` |
| Stimulus list | `assets/controllers/mantenedores/list_controller.js` |
| Stimulus confirm delete | `assets/controllers/confirm_delete_controller.js` |
| Template base maintainer | `templates/maintainers/modern_index.html.twig` |

---

## Checklist de smoke tests

```
[ ] GET /admission                             → 200, carga en turbo-frame "maintainer-content"
[ ] Click "Admisión" en sidebar                → contenido carga SIN recargar página entera
[ ] Link "Admisión" en sidebar queda activo/highlighted
[ ] GET /admission/emergency                   → 200
[ ] GET /admission/wizard/step1/{id}           → 200, muestra turbo-frame "admission-wizard-content"
[ ] POST /admission/wizard/step1 (válido)      → Turbo Stream reemplaza frame con step2
[ ] POST /admission/wizard/step1 (inválido)    → re-render step1 con errores
[ ] GET /api/admission/services?branch=1       → JSON array
[ ] GET /api/admission/payers?branch=1         → JSON array
[ ] GET /api/admission/beds?service=1          → JSON array
[ ] POST /admission/wizard/step3 (completo)    → redirect a /admission/{id}/view
[ ] GET /admission/print/{id}/pdf              → HTML full-page, SIN turbo-frame
[ ] Abrir PDF en nueva pestaña (data-turbo=false verificado)
[ ] Multi-tenant: admisión solo ve datos del tenant activo
[ ] Sin errores "turbo:frame-missing" en consola del navegador
[ ] Sin errores JS en consola
```

---

## Estado actual del módulo

| Componente | Estado |
|---|---|
| Mantenedor EmergencyConsultationType | ✅ MIGRADO |
| Mantenedor CompanyAgreement | ✅ MIGRADO |
| Mantenedor CancellationReason | ✅ MIGRADO |
| Menú "Admisión" en sidebar | ❌ PENDIENTE |
| AdmissionController (index) | ❌ PENDIENTE |
| AdmissionWizardController (pasos 1-4) | ❌ PENDIENTE |
| EmergencyAdmissionController | ❌ PENDIENTE |
| PatientRegistrationController | ❌ PENDIENTE |
| AdmissionPrintController | ❌ PENDIENTE |
| AdmissionApiController (JSON endpoints) | ❌ PENDIENTE |
| Stimulus wizard_controller.js | ❌ PENDIENTE |
| Templates admission/ | ❌ PENDIENTE |
| AdmissionService | ❌ PENDIENTE |
| PatientSearchService | ❌ PENDIENTE |

---

## Tablero de ejecución (Codex + Copilot)

> Usar este tablero para seguimiento diario.  
> Regla: no pasar a la siguiente etapa sin cumplir DoD de la etapa actual.

### Estado global por etapa

| Etapa | Nombre | Estado | Responsable principal | Bloquea a |
|---|---|---|---|---|
| 0 | Preparación técnica y baseline | ⬜ Pendiente | Codex | 1-7 |
| 1 | Menú "Admisión" en sidebar | ⬜ Pendiente | Codex | 2-7 |
| 2 | AdmissionController + index base | ⬜ Pendiente | Codex | 3-7 |
| 3 | Wizard de Admisión (step1-step3) | ⬜ Pendiente | Codex | 4-7 |
| 4 | Flujo Urgencia | ⬜ Pendiente | Codex | 6-7 |
| 5 | API JSON (sin jQuery) + Stimulus wizard | ⬜ Pendiente | Copilot (borrador) + Codex (integración) | 3-4-7 |
| 6 | Impresión PDF | ⬜ Pendiente | Copilot (borrador) + Codex (integración) | 7 |
| 7 | Mantenedores de admisión en menú + smoke tests | ⬜ Pendiente | Codex | Cierre |

### ETAPA 0 — Preparación técnica y baseline

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Confirmar convenciones y rutas | `docs/migracion/ADMISION_MIGRACION_PLAN.md`, `docs/migracion/GUIA_MAESTRA_MIGRACION.md` | Resume checklist | Valida y fija baseline | ⬜ |
| Verificar frame principal | `templates/app_layout.html.twig` | N/A | Validación manual | ⬜ |
| Verificar auto-discovery Stimulus | `assets/bootstrap.js`, `assets/app.js` | Sugiere ajustes | Valida runtime | ⬜ |

**DoD Etapa 0**
- Convenciones confirmadas (sin YAML routes, sin jQuery, TenantEntityManager por constructor).
- Baseline sin errores JS críticos en consola.

### ETAPA 1 — Menú "Admisión" en sidebar

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Agregar bloque `admision` entre `pacientes` y `mantenedores` | `src/Service/Menu/MenuDefinition.php` | Propone bloque exacto | Inserta y ajusta orden final | ⬜ |
| Habilitar carga en Turbo para módulo admisión | `templates/partials/_sidebar.html.twig` | Sugiere condición Twig | Corrige y valida colapsables | ⬜ |
| Validar activo/highlight del menú | `templates/partials/_sidebar.html.twig` | N/A | Prueba navegando | ⬜ |

**DoD Etapa 1**
- Sidebar muestra “Admisión”.
- Click en “Admisión” carga en `maintainer-content` sin recarga completa.
- Colapsables funcionan sin rebote visual.

### ETAPA 2 — AdmissionController + index base

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Crear controller esqueleto | `src/Controller/Admission/AdmissionController.php` | Genera clase base | Revisa patrón + DI | ⬜ |
| Crear vista `index` | `templates/admission/index.html.twig` | Genera markup base | Ajusta a layout real | ⬜ |
| Verificar ruta principal | `/admission` | N/A | Smoke test manual | ⬜ |

**DoD Etapa 2**
- `GET /admission` responde 200.
- Render correcto dentro de `maintainer-content`.

### ETAPA 3 — Wizard de Admisión (step1-step3)

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Crear `AdmissionWizardController` | `src/Controller/Admission/AdmissionWizardController.php` | Genera esqueleto métodos | Integra flujo Turbo | ⬜ |
| Crear templates step1-step3 + complete | `templates/admission/wizard/*.html.twig` | Borrador de vistas | Ajuste final y navegación | ⬜ |
| Integrar submit + validación por paso | Controller + Forms | Sugiere condiciones | Implementa comportamiento real | ⬜ |

**DoD Etapa 3**
- Step1 renderiza en `admission-wizard-content`.
- POST válido avanza de paso por Turbo Stream.
- POST inválido re-renderiza con errores en el frame.

### ETAPA 4 — Flujo Urgencia

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Crear `EmergencyAdmissionController` | `src/Controller/Admission/EmergencyAdmissionController.php` | Esqueleto + rutas | Ajusta lógica tenant | ⬜ |
| Crear vistas urgencia | `templates/admission/emergency/index.html.twig`, `templates/admission/emergency/_form.html.twig` | Borrador | Integración final | ⬜ |
| Implementar vista detalle urgencia | `templates/admission/view.html.twig` | Borrador | Ajuste final | ⬜ |

**DoD Etapa 4**
- `GET /admission/emergency` responde 200.
- Flujo create/view operativo sin recarga completa innecesaria.

### ETAPA 5 — API JSON + Stimulus wizard (sin jQuery)

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Crear `AdmissionApiController` | `src/Controller/Admission/AdmissionApiController.php` | Genera endpoints base | Implementa consultas tenant | ⬜ |
| Crear controller Stimulus wizard | `assets/controllers/admission/wizard_controller.js` | Genera lógica fetch/populate | Ajusta targets/actions reales | ⬜ |
| Conectar selects dinámicos | templates wizard + form types | Sugiere `data-action` | Verifica comportamiento | ⬜ |

**DoD Etapa 5**
- `/api/admission/services|payers|agreements|beds` devuelven JSON válido.
- Carga dinámica funciona con `fetch()` y sin jQuery.

### ETAPA 6 — Impresión PDF

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Crear `AdmissionPrintController` | `src/Controller/Admission/AdmissionPrintController.php` | Esqueleto | Integración y rutas | ⬜ |
| Crear templates print | `templates/admission/print/admission_pdf.html.twig`, `templates/admission/print/urgency_pdf.html.twig` | Borrador HTML print | Ajuste final | ⬜ |
| Validar links print | templates de vista | Sugiere link | Verifica `data-turbo="false"` | ⬜ |

**DoD Etapa 6**
- PDF abre en nueva pestaña (`target="_blank"`).
- Render full-page sin Turbo frame.

### ETAPA 7 — Mantenedores en menú + smoke tests finales

| Tarea | Archivo(s) | Copilot | Codex | Estado |
|---|---|---|---|---|
| Agregar “Mantenedores Admisión” bajo menú admisión | `src/Service/Menu/MenuDefinition.php` | Sugiere bloque | Integra y ordena | ⬜ |
| Ajustar condición módulo mantenimiento admisión | `templates/partials/_sidebar.html.twig` | Sugiere condición | Valida navegación | ⬜ |
| Ejecutar checklist smoke tests | Sección checklist de este documento | Ayuda a marcar resultados | Ejecuta y documenta | ⬜ |

**DoD Etapa 7**
- Menú final completo y estable.
- Checklist smoke tests completado.
- Sin errores JS ni `turbo:frame-missing`.

### Registro de avances

| Fecha | Etapa | Cambio | Responsable | Evidencia |
|---|---|---|---|---|
| YYYY-MM-DD | Etapa X | Descripción breve | Codex/Copilot | commit / screenshot / ruta probada |
