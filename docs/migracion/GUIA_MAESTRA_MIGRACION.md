# 🧠 GUÍA MAESTRA DE MIGRACIÓN MELISA
## Claude Code Pro como Cerebro + GitHub Copilot Pro como Manos

**Versión:** 3.0 Definitiva  
**Fecha:** Enero 2026  
**Sistema:** MELISA (Salud/Multi-tenant)  
**Stack:** Symfony 3 Legacy → Symfony 7.4 + Turbo/Stimulus  

---

## 📑 ÍNDICE

1. [Contexto y Estado Actual](#1-contexto)
2. [Arquitectura: Qué Cambió](#2-arquitectura)
3. [Roles Exactos: Claude vs Copilot vs Tú](#3-roles)
4. [Flujo de Trabajo Maestro](#4-flujo-maestro)
5. [Prompts Completos por Fase](#5-prompts)
6. [Estándares Técnicos](#6-estandares)
7. [Gestión de Bundles y Código Duplicado](#7-bundles)
8. [Sistema de Testing](#8-testing)
9. [Checklists Operativos](#9-checklists)
10. [Troubleshooting](#10-troubleshooting)

---

## 1. CONTEXTO {#1-contexto}

### ¿Qué es MELISA?

Sistema clínico/administrativo legacy con:
- **Criticidad:** ALTA (salud, datos sensibles)
- **Multi-tenancy:** Hakam Bundle (aislamiento obligatorio)
- **Stack legacy:** Symfony 3 + bundles + Twig + jQuery
- **Complejidad:** Controllers 800+ líneas, queries históricas, DOM frágil

### Estado Actual

```
Progreso: 3 categorías migradas de ~110 total (2.7%)
```

**Una "categoría" =** agrupación de mantenedores relacionados
- Ejemplo: Categoría "Comercial" → Clientes, Convenios, Productos, Glosas, etc.
- Cada mantenedor = CRUD completo (listar, crear, editar, eliminar)

### Cambios Ya Aplicados (NO existían en Symfony 3)

✅ **Turbo + Stimulus** (render parcial, no full page)  
✅ **Paginación global** reutilizable  
✅ **Export global** (copy-paste entre mantenedores)  
✅ **Modales** para Agregar/Editar  
✅ **SweetAlert2** para confirmaciones de Eliminar  

### Objetivos de Migración

**NO hacer:**
- ❌ Detener desarrollo
- ❌ Romper comportamiento funcional
- ❌ Copiar código legacy tal cual
- ❌ Optimizar queries sin aprobación
- ❌ Cambiar DOM sin documentar
- ❌ Romper multi-tenancy

**SÍ hacer:**
- ✅ Migración incremental (por bundles/categorías)
- ✅ Comportamiento equivalente, arquitectura moderna
- ✅ Orden: compatibilidad → refactor seguro → modernización
- ✅ Symfony 7.4 sin bundles (módulos por dominio)
- ✅ PHP 8.2+, Turbo/Stimulus estándar

---

## 2. ARQUITECTURA {#2-arquitectura}

### Symfony 3 Legacy (LO QUE DEJAMOS)

```
src/
├── AppBundle/
└── <Modulo>Bundle/
    ├── Controller/ (fat, 800+ líneas)
    ├── Entity/
    ├── Repository/
    └── Resources/
        ├── config/ (routing.yml, services.yml)
        ├── views/ (Twig monolítico)
        └── public/js/ (jQuery acoplado)
```

**Características:**
- Render completo por request
- jQuery para comportamiento
- Lógica mezclada en Controllers
- Sin autowiring

### Symfony 7.4 Moderno (LO QUE CONSTRUIMOS)

```
src/Module/<Modulo>/
├── Controller/ (delgados, delegan)
├── Application/ (casos uso, DTOs)
├── Domain/ (entidades, reglas)
├── Infrastructure/ (repos, integraciones)
└── Form/

templates/<modulo>/
├── index.html.twig (shell)
├── _list.html.twig (parcial)
├── _form.html.twig (parcial)
└── _modal.html.twig (estructura)

assets/controllers/ (Stimulus)
├── <modulo>_modal_controller.js
└── confirm_delete_controller.js
```

**Características:**
- Turbo Frames (listados sin recarga)
- Turbo Streams (actualizaciones parciales)
- Stimulus (JS desacoplado)
- Autowiring + Autoconfigure
- Atributos PHP para config

### Tabla Comparativa Crítica

| Aspecto | Symfony 3 | Symfony 7.4 |
|---------|-----------|-------------|
| **UI Updates** | Full page reload | Turbo Frames/Streams |
| **JavaScript** | jQuery acoplado | Stimulus controllers |
| **Modales** | Render completo o Bootstrap | Turbo Frame en modal |
| **Form Submit** | POST → redirect → render | POST → Stream → update |
| **Paginación** | Por mantenedor | Global reutilizable |
| **Estructura** | Bundles | Módulos por dominio |

---

## 3. ROLES EXACTOS {#3-roles}

### TÚ (Decisor Final)

**Rol:** Tomas TODAS las decisiones de negocio y arquitectura.

**Responsabilidades:**
- [ ] Decidir qué migrar y en qué orden
- [ ] Validar multi-tenant manualmente
- [ ] Revisar código de Claude Code
- [ ] Aprobar cambios críticos
- [ ] Hacer commits controlados
- [ ] Documentar decisiones

**Herramientas:**
- Copilot Pro (80% tiempo) - escritura
- Claude Code Pro (20% tiempo) - análisis/diseño
- Testing manual - validación crítica

---

### CLAUDE CODE PRO (Cerebro/Arquitecto)

**Rol:** Analizar, diseñar, proponer, documentar.

**CUÁNDO USAR:**

```
✅ USAR Claude Code cuando:
├─ Analizar bundle/categoría completa
├─ Detectar código duplicado/muerto
├─ Proponer arquitectura Symfony 7.4
├─ Generar prompts para Copilot
├─ Crear documentación (specs, patrones)
├─ Auditar código migrado
└─ Casos especiales/complejos

❌ NO USAR Claude Code para:
├─ Escribir código línea a línea
├─ Refactors finos dentro de método
├─ Cambios mecánicos repetitivos
└─ Autocompletado en IDE
```

**QUÉ DEBE ENTREGARTE:**

Por cada bundle/categoría:

1. **Inventario completo**
   - Rutas → Controllers → Servicios → Templates → JS
   - Código muerto probable (con evidencia)
   - Duplicados detectados (+ canónico sugerido)

2. **Arquitectura propuesta**
   - Estructura Symfony 7.4 (sin bundles)
   - Namespaces y organización
   - Separación de capas

3. **Plan de migración**
   - Fases: compatibilidad → refactor → modernización
   - Orden de archivos
   - Riesgos detectados

4. **Prompts Copilot accionables**
   - Por tipo: Controller/Twig/Stimulus/Tests
   - Copy-paste ready
   - Con restricciones explícitas

5. **Checklist de validación**
   - Multi-tenant (crítico)
   - Funcional
   - DoD específico

---

### GITHUB COPILOT PRO (Manos/Ejecutor)

**Rol:** Escribir código obediente dentro del IDE.

**CUÁNDO USAR:**

```
✅ USAR Copilot cuando:
├─ Editando archivo específico
├─ Siguiendo prompts de Claude
├─ Refactors mecánicos
├─ Autocompletado inteligente
├─ Cambios repetitivos
└─ Aplicando patrón ya definido

❌ NO USAR Copilot para:
├─ Análisis de múltiples archivos
├─ Diseño de arquitectura
├─ Decidir qué migrar
├─ Detectar duplicados
└─ Copilot Chat largo (gasta cuota)
```

**CÓMO GUIARLO:**

Copilot lee **comentarios locales**. Pégalos SIEMPRE antes del código:

```php
/**
 * MIGRATION RULES (COPILOT):
 * - NO cambiar comportamiento funcional
 * - NO renombrar rutas/servicios públicos
 * - NO optimizar queries
 * - Mantener multi-tenant
 * - Solo refactor estructural
 * - Compatible PHP 8.2
 */
class MantenedorController {
```

```twig
{#
 COPILOT RULES:
 - NO cambiar ids/classes (jQuery depende)
 - NO cambiar estructura HTML
 - Solo ordenar y extraer includes
#}
```

---

### 💡 REGLA DE ORO

```
┌─────────────────────────────────────┐
│  Claude PIENSA                      │
│  Copilot ESCRIBE                    │
│  TÚ DECIDES                         │
│                                     │
│  NUNCA mezclar roles                │
└─────────────────────────────────────┘
```

---

## 4. FLUJO DE TRABAJO MAESTRO {#4-flujo-maestro}

### Ciclo por Bundle/Categoría

```
┌─ FASE 0: Preparación ─────────────────────┐
│  Herramienta: Manual                      │
│  Tiempo: 30-60 min                        │
│                                           │
│  □ Identificar bundle/categoría           │
│  □ Listar mantenedores incluidos          │
│  □ Marcar zonas críticas                  │
│  □ Definir objetivo (compatibilidad/      │
│    refactor/modernización)                │
└───────────────────────────────────────────┘
           ↓
┌─ FASE 1: Análisis (Claude Code) ─────────┐
│  Herramienta: Claude Code Pro             │
│  Tiempo: 30-60 min                        │
│                                           │
│  □ Claude analiza bundle completo         │
│  □ Detecta duplicados/código muerto       │
│  □ Propone arquitectura Symfony 7.4       │
│  □ Genera prompts Copilot                 │
│  □ Crea checklist validación              │
│                                           │
│  Entregable: Paquete de migración         │
└───────────────────────────────────────────┘
           ↓
┌─ FASE 2: Implementación (Copilot) ───────┐
│  Herramienta: GitHub Copilot Pro          │
│  Tiempo: 2-8 hrs (según bundle)           │
│                                           │
│  □ Abres archivo en IDE                   │
│  □ Pegas prompt de Claude                 │
│  □ Copilot autocompleta                   │
│  □ Revisas y aceptas/corriges             │
│  □ Commit pequeño                         │
│  □ Repites por archivo                    │
└───────────────────────────────────────────┘
           ↓
┌─ FASE 3: Validación ──────────────────────┐
│  Herramienta: Tests + Manual              │
│  Tiempo: 1-2 hrs                          │
│                                           │
│  □ Ejecutar smoke test                    │
│  □ Ejecutar tests caracterización         │
│  □ Validación manual multi-tenant         │
│  □ Checklist funcional                    │
│  □ Sin errores consola                    │
└───────────────────────────────────────────┘
           ↓
┌─ FASE 4: Revisión (Claude Code) ─────────┐
│  Herramienta: Claude Code Pro             │
│  Tiempo: 15-30 min                        │
│                                           │
│  □ Claude revisa diff/cambios             │
│  □ Detecta efectos secundarios            │
│  □ Valida equivalencia funcional          │
│  □ Aprueba o sugiere correcciones         │
└───────────────────────────────────────────┘
           ↓
┌─ FASE 5: Commit y Siguiente ─────────────┐
│  □ Commit descriptivo                     │
│  □ Documentar cambios importantes         │
│  □ Siguiente mantenedor/categoría         │
└───────────────────────────────────────────┘
```

---

## 5. PROMPTS COMPLETOS {#5-prompts}

### 5.1 Prompt Análisis Inicial de Bundle/Categoría

**Usar cuando:** Vas a migrar un bundle/categoría nuevo

**Dónde:** Claude Code Pro (terminal)

```
CONTEXTO:
Sistema MELISA (salud, multi-tenant Hakam).
Migración Symfony 3 → Symfony 7.4 + Turbo/Stimulus.
Disponibilidad: 3-4 hrs/día.

BUNDLE/CATEGORÍA A ANALIZAR:
Nombre: [NOMBRE]
Ubicación: [ruta, ej: src/ComercialBundle]
Descripción: [breve, qué hace]

SITUACIÓN:
- Puede tener código duplicado
- Puede tener código muerto (rutas no usadas, controllers antiguos)
- Puede tener múltiples versiones del mismo mantenedor

OBJETIVO:
Migrar SOLO lo vigente/actual con arquitectura Symfony 7.4 moderna.

TAREA - ENTREGA OBLIGATORIA:

A) INVENTARIO DE USO (CON EVIDENCIA):
   - Rutas activas → controller real → template
   - Servicios usados
   - Código muerto probable (no referenciado)

B) DUPLICADOS Y VERSIÓN CANÓNICA:
   - Tabla: archivo A vs archivo B → cuál es el canónico y por qué
   - Criterios: más referenciado, más reciente, usado por rutas actuales

C) ARQUITECTURA PROPUESTA SYMFONY 7.4:
   - Estructura sin bundles (src/Module/<Modulo>)
   - Namespaces
   - Separación Controller/Application/Domain/Infrastructure
   - Convenciones: rutas, templates, forms

D) PLAN DE MIGRACIÓN SEGURO:
   - Paso 1: Estructura nueva + mover canónicos
   - Paso 2: Adaptar rutas
   - Paso 3: Adaptar templates
   - Paso 4: Deprecate duplicados (NO borrar)
   - Paso 5: Remoción definitiva (con evidencia)

E) PROMPTS COPILOT ACCIONABLES:
   - Por tipo de archivo (Controller/Twig/Stimulus)
   - Lista de archivos a tocar por paso
   - Restricciones explícitas

F) CHECKLIST DE VALIDACIÓN:
   - Smoke tests
   - Validación multi-tenant
   - Flujos críticos

RESTRICCIONES CRÍTICAS:
- NO borrar archivos sin evidencia
- NO cambiar comportamiento funcional
- Mantener multi-tenant
- Evitar refactors estéticos
- Usar Turbo/Stimulus (NO jQuery)

INFORMACIÓN ADICIONAL:
- Tenant: identificación por [subdominio/header/sesión]
- Paginación global: [ruta/clase]
- Export global: [ruta/clase]
```

---

### 5.2 Prompt Análisis de Categoría con Mantenedores Heterogéneos

**Usar cuando:** Categoría tiene mantenedores diferentes (texto simple, combos, checkboxes, etc.)

```
CONTEXTO:
Categoría: [NOMBRE, ej: Comercial]
Mantenedores incluidos: [lista, ej: Clientes, Convenios, Productos]

SITUACIÓN:
Los mantenedores NO son homogéneos:
- Algunos: solo campos texto
- Algunos: combos/selects dependientes
- Algunos: checkboxes/flags
- Algunos: mixtos + validaciones complejas

OBJETIVO:
Definir patrón base Turbo/Stimulus común, pero adaptar por mantenedor.

TAREA:

1) DEFINIR PATRÓN BASE (COMÚN A TODOS):
   - Shell + Turbo Frame
   - Paginación global
   - Endpoints estándar (GET/POST/PUT/DELETE)
   - Turbo Streams para create/update/delete
   - Modales para new/edit
   - SweetAlert2 para delete
   - Reglas multi-tenant

2) ANALIZAR CADA MANTENEDOR INDIVIDUALMENTE:
   Para cada uno entregar:
   - Clasificación: Simple/Medio/Especial
   - Tipo de inputs: texto/combo/checkbox/mixto
   - Validaciones específicas
   - Dependencias (catálogos, otras tablas)
   - Complejidad UX

3) ADAPTAR PATRÓN POR MANTENEDOR:
   - Qué cambia vs patrón base
   - Qué se mantiene igual
   - Stimulus controllers necesarios
   - Flujo específico de guardado

4) PROMPTS COPILOT ESPECÍFICOS:
   Por cada mantenedor, prompt copy-paste ajustado

RESTRICCIONES:
- Mantener comportamiento Symfony 3
- Reusar paginación global
- NO copiar jQuery
- NO render completo

ENTREGA:
- Patrón base documentado
- Análisis individual por mantenedor
- Tabla comparativa
- Prompts Copilot por mantenedor
```

---

### 5.3 Prompt Estándar UX (Modales + SweetAlert2)

**Usar cuando:** Definir o validar patrón de UI

```
ESTÁNDAR UX PARA MANTENEDORES:

AGREGAR/EDITAR (Modal):
- Modal carga formulario vía Turbo Frame (GET /new o /edit)
- POST/PUT responde con Turbo Stream:
  * Cierra modal
  * Actualiza listado (frame)
  * Muestra mensaje flash/toast
- Si hay errores validación:
  * Modal permanece abierto
  * Form se re-renderiza con errores en frame

ELIMINAR (SweetAlert2):
- Click "Eliminar" → SweetAlert2 confirma
- Configuración:
  * Título: "¿Eliminar?"
  * Texto: "Esta acción no se puede deshacer"
  * Botones: Confirmar/Cancelar
- Si confirma:
  * DELETE → Turbo Stream
  * Remueve fila o refresca frame
  * Muestra mensaje

RESTRICCIONES:
- NO usar jQuery
- NO recargar página completa
- Reusar paginación global
- Mantener comportamiento funcional Symfony 3

ENTREGA REQUERIDA:
1) Diseño flujo Turbo/Stimulus (modal + delete)
2) Estructura templates: index + _list + _form + _modal
3) Stimulus controllers: modal_controller, confirm_delete_controller
4) Contrato endpoints y respuestas (full/frame/stream)
5) Prompts Copilot para implementar
```

---

### 5.4 Prompt Auditoría de Código Ya Migrado

**Usar cuando:** Validar categorías ya migradas

```
CONTEXTO:
Tengo [N] categorías ya migradas a Symfony 7.4 + Turbo/Stimulus.
Necesito auditoría vs comportamiento Symfony 3.

CATEGORÍAS A AUDITAR:
1. [Nombre] - [ruta]
2. [Nombre] - [ruta]
...

OBJETIVO:
Validar equivalencia funcional (NO similitud de código).
Detectar desviaciones y proponer correcciones mínimas.

CRITERIOS DE EQUIVALENCIA:
- Mismos filtros, orden, paginación
- Mismos defaults (checkbox, selects, textos)
- Mismas validaciones y mensajes
- Mismos permisos por rol
- Mismo scope multi-tenant
- Mismo comportamiento ante errores

MANTENER:
- Patrón moderno Turbo/Stimulus
- NO volver a jQuery
- NO volver a render full page

ENTREGA POR CATEGORÍA:

1) CHECKLIST EQUIVALENCIA:
   □ Filtros: iguales/diferentes
   □ Defaults: iguales/diferentes
   □ Validaciones: iguales/diferentes
   □ Permisos: iguales/diferentes
   □ Multi-tenant: OK/con issues

2) DIFERENCIAS DETECTADAS:
   - Ubicación exacta en código
   - Qué difiere
   - Severidad (crítico/alto/medio/bajo)

3) PROPUESTA CORRECCIÓN:
   - Cambio mínimo necesario
   - Sin refactors innecesarios
   - Solo equivalencia funcional

4) PROMPTS COPILOT:
   - Qué archivo tocar
   - Qué cambiar exactamente
   - Qué NO cambiar

5) PLAN DE PRUEBAS:
   - Multi-tenant (A/B + cross-tenant)
   - Modales (new/edit)
   - Delete con SweetAlert2
   - Paginación y filtros

IMPORTANTE:
Corrige SOLO lo necesario para equivalencia.
NO hagas refactors estéticos.
```

---

### 5.5 Prompts para Copilot (Templates)

Estos van como **comentarios en el código**, NO en Claude Code.

#### Para Controllers (PHP)

```php
/**
 * MIGRATION RULES (COPILOT):
 * 
 * CONTEXTO:
 * - Symfony 3 legacy → Symfony 7.4
 * - Patrón: Turbo/Stimulus (NO jQuery)
 * - Multi-tenant: Hakam (filtrar SIEMPRE)
 * 
 * RESTRICCIONES:
 * - NO cambiar comportamiento funcional
 * - NO renombrar rutas ni firmas públicas
 * - NO optimizar queries (mantener tal cual)
 * - NO cambiar lazy/eager loading
 * - Solo refactor estructural (extraer métodos privados)
 * 
 * ENDPOINTS:
 * - GET /index → full page (shell)
 * - GET /list → partial (Turbo Frame)
 * - GET /new → partial (modal frame)
 * - POST / → Turbo Stream (close modal + refresh list)
 * - GET /{id}/edit → partial (modal frame)
 * - PUT /{id} → Turbo Stream
 * - DELETE /{id} → Turbo Stream
 * 
 * VALIDACIONES:
 * - Multi-tenant en TODAS las queries
 * - Permisos por rol
 * - Si error validación → re-render form en frame
 * 
 * Compatible: PHP 8.2+
 */
```

#### Para Twig

```twig
{#
 MIGRATION RULES (COPILOT):
 
 CONTEXTO:
 - Template Symfony 3 → Symfony 7.4
 - Patrón: Turbo Frames/Streams
 
 RESTRICCIONES CRÍTICAS:
 - NO cambiar estructura HTML
 - NO cambiar ids/classes (JavaScript depende)
 - NO cambiar data-attributes
 - Solo ordenar y extraer includes/macros
 - Mantener compatibilidad visual exacta
 
 ESTRUCTURA:
 - index.html.twig = shell (filtros + turbo-frame)
 - _list.html.twig = parcial (tabla + paginación)
 - _form.html.twig = parcial (formulario)
 - _modal.html.twig = estructura modal
 
 TURBO:
 - <turbo-frame id="..."> para listado
 - <turbo-frame id="..."> para modal
 - data-turbo-stream para updates
#}
```

#### Para JavaScript/Stimulus

```javascript
/**
 * MIGRATION RULES (COPILOT):
 * 
 * CONTEXTO:
 * - Migrar de jQuery legacy a Stimulus
 * 
 * RESTRICCIONES:
 * - NO usar jQuery
 * - NO replicar comportamiento acoplado al DOM
 * - Usar Stimulus controllers
 * - Desacoplar lógica de presentación
 * 
 * PATRONES:
 * - modal_controller: abrir/cerrar/cargar
 * - confirm_delete_controller: SweetAlert2
 * - [específico]_controller: comportamiento local
 * 
 * EVENTOS TURBO:
 * - turbo:submit-end
 * - turbo:frame-load
 * - turbo:before-stream-render
 */
```

---

## 6. ESTÁNDARES TÉCNICOS {#6-estandares}

### 6.1 Patrón Turbo/Stimulus Estándar

#### Estructura de Templates

```
templates/<modulo>/
├── index.html.twig          # Shell (página completa)
├── _list.html.twig           # Parcial (listado en frame)
├── _form.html.twig           # Parcial (formulario)
└── _modal.html.twig          # Estructura modal
```

#### index.html.twig (Shell)

```twig
{% extends 'base.html.twig' %}

{% block content %}
<div class="container">
    <h1>{{ title }}</h1>
    
    {# Filtros #}
    <form data-controller="filter">
        {# filtros aquí #}
    </form>
    
    {# Botón Nuevo (abre modal) #}
    <button data-action="modal#open" 
            data-modal-url-value="{{ path('modulo_new') }}">
        Nuevo
    </button>
    
    {# Frame del listado #}
    <turbo-frame id="modulo-list">
        {{ include('modulo/_list.html.twig') }}
    </turbo-frame>
    
    {# Modal (estructura) #}
    {{ include('modulo/_modal.html.twig') }}
</div>
{% endblock %}
```

#### _list.html.twig (Parcial)

```twig
{# Solo contenido del frame, NO layout #}
<table>
    <thead>
        <tr>
            <th>Columna 1</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        {% for item in items %}
        <tr id="row-{{ item.id }}">
            <td>{{ item.nombre }}</td>
            <td>
                {# Editar (modal) #}
                <button data-action="modal#open"
                        data-modal-url-value="{{ path('modulo_edit', {id: item.id}) }}">
                    Editar
                </button>
                
                {# Eliminar (SweetAlert2) #}
                <button data-controller="confirm-delete"
                        data-confirm-delete-url-value="{{ path('modulo_delete', {id: item.id}) }}"
                        data-action="confirm-delete#confirm">
                    Eliminar
                </button>
            </td>
        </tr>
        {% endfor %}
    </tbody>
</table>

{# Paginación global #}
{{ paginacion_global(items) }}
```

#### Controller (Symfony 7.4)

```php
<?php
namespace App\Module\Modulo\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/modulo')]
class ModuloController extends AbstractController
{
    // GET /modulo → full page
    #[Route('/', name: 'modulo_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('modulo/index.html.twig');
    }
    
    // GET /modulo/list → partial (Turbo Frame)
    #[Route('/list', name: 'modulo_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        // Aplicar filtros, paginación
        $items = /* ... */;
        
        return $this->render('modulo/_list.html.twig', [
            'items' => $items
        ]);
    }
    
    // GET /modulo/new → partial (modal)
    #[Route('/new', name: 'modulo_new', methods: ['GET'])]
    public function new(): Response
    {
        $form = /* ... */;
        
        return $this->render('modulo/_form.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    // POST /modulo → Turbo Stream
    #[Route('/', name: 'modulo_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $form = /* ... */;
        
        if ($form->isSubmitted() && $form->isValid()) {
            // Guardar
            
            // Responder con Turbo Stream
            return $this->render('modulo/_create_stream.html.twig', [
                'item' => $item
            ]);
        }
        
        // Si hay errores, re-render form en frame
        return $this->render('modulo/_form.html.twig', [
            'form' => $form->createView()
        ], new Response('', 422));
    }
}
```

#### Stimulus Controller (modal)

```javascript
// assets/controllers/modulo_modal_controller.js
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['modal', 'content'];
    static values = { url: String };
    
    open(event) {
        event.preventDefault();
        const url = event.currentTarget.dataset.modalUrlValue;
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                this.contentTarget.innerHTML = html;
                this.modalTarget.classList.add('show');
            });
    }
    
    close() {
        this.modalTarget.classList.remove('show');
    }
}
```

---

### 6.2 Multi-Tenancy (Hakam)

**REGLAS NO NEGOCIABLES:**

```php
// ❌ NUNCA hacer esto:
$repository->findAll();
$repository->findBy(['activo' => true]);

// ✅ SIEMPRE filtrar por tenant:
$repository->findByTenant($tenantId);
$repository->createQueryBuilder('e')
    ->where('e.tenant = :tenant')
    ->setParameter('tenant', $tenantId);
```

**Validación en cada endpoint:**

```php
public function edit(int $id, TenantService $tenantService): Response
{
    $item = $repository->find($id);
    
    // CRÍTICO: Validar tenant
    if ($item->getTenant() !== $tenantService->getCurrentTenant()) {
        throw $this->createNotFoundException();
        // o throw new AccessDeniedException();
    }
    
    // ...
}
```

**En tests:**

```php
public function testCrossTenantAccessDenied()
{
    // Login como tenant A
    $itemA = $this->createItem(tenantA);
    
    // Login como tenant B
    $this->switchTenant(tenantB);
    
    // Intentar acceder a item de tenant A
    $this->client->request('GET', "/modulo/{$itemA->getId()}/edit");
    
    $this->assertResponseStatusCodeSame(404);
}
```

---

## 7. GESTIÓN DE BUNDLES Y CÓDIGO DUPLICADO {#7-bundles}

### 7.1 Identificar Código Duplicado

Claude Code debe responder:

```
DUPLICADOS DETECTADOS:

1. src/CajaBundle/Controller/CierreController.php (v1)
   vs
   src/CajaBundle/Controller/CierreControllerNuevo.php (v2)
   
   Análisis:
   - v1: referenciado en routing antiguo (deprecated)
   - v2: usado por rutas actuales, más reciente
   - Diferencias: v2 tiene validaciones adicionales
   
   CANÓNICO: CierreControllerNuevo.php
   ACCIÓN: Migrar v2, deprecate v1

2. templates/caja/cierre.html.twig (antigua)
   vs
   templates/caja/cierre_v2.html.twig (actual)
   
   CANÓNICO: cierre_v2.html.twig
```

### 7.2 Decisión de Eliminación

**NUNCA eliminar sin evidencia:**

```
Archivo: CierreController.php (v1)

Evidencia de desuso:
✅ No referenciado en routing.yml actual
✅ No referenciado en services.yml
✅ No usado en ningún template
✅ Última modificación: 2 años atrás
✅ Existe versión más nueva con misma funcionalidad

Acción: SAFE TO DELETE
```

### 7.3 Arquitectura sin Bundles

```
ANTES (Symfony 3):
src/CajaBundle/

DESPUÉS (Symfony 7.4):
src/Module/Caja/
├── Controller/
│   ├── CierreController.php
│   └── PagoController.php
├── Application/
│   ├── CerrarCaja/
│   │   ├── CerrarCajaHandler.php
│   │   └── CerrarCajaDTO.php
│   └── RegistrarPago/
│       └── ...
├── Domain/
│   ├── Caja.php (entidad)
│   ├── CajaRepository.php (interface)
│   └── CajaService.php (lógica dominio)
└── Infrastructure/
    ├── Doctrine/
    │   └── CajaRepository.php (implementación)
    └── External/
        └── FontarasaClient.php
```

---

## 8. SISTEMA DE TESTING {#8-testing}

### 8.1 Tests de Caracterización (Snapshots)

**Propósito:** Capturar comportamiento ACTUAL antes de migrar.

**Qué snapshottear:**
- HTML renderizado (estructura y contenido)
- JSON de APIs/listados
- Status HTTP
- Conteos (número de registros)

**Qué NO snapshottear:**
- IDs autogenerados (normalizar a `{{ID}}`)
- Fechas/timestamps (normalizar a `{{DATE}}`)
- Tokens/hashes (normalizar a `{{TOKEN}}`)

**Ejemplo:**

```php
use Spatie\Snapshots\MatchesSnapshots;

class MantenedorTest extends WebTestCase
{
    use MatchesSnapshots;
    
    public function testListadoTenantA()
    {
        $client = $this->requestAsTenant('tenant_a', 'GET', '/modulo/list');
        
        $html = $client->getResponse()->getContent();
        
        // Normalizar IDs variables
        $html = preg_replace('/id="row-\d+"/', 'id="row-{{ID}}"', $html);
        
        // Snapshot
        $this->assertMatchesSnapshot($html);
    }
}
```

### 8.2 Smoke Test

```bash
#!/bin/bash
# tests/smoke_test.sh

echo "🔥 Smoke Test Multi-Tenancy"

BASE_URL="http://localhost"
TENANT_A="clinica_norte"
TENANT_B="clinica_sur"

test_endpoint() {
    local tenant=$1
    local endpoint=$2
    local expected=$3
    
    status=$(curl -s -o /dev/null -w "%{http_code}" \
        -H "X-Tenant: $tenant" \
        "$BASE_URL$endpoint")
    
    if [ "$status" -eq "$expected" ]; then
        echo "✅ [$tenant] $endpoint → HTTP $status"
    else
        echo "❌ [$tenant] $endpoint → HTTP $status (esperado: $expected)"
        exit 1
    fi
}

# Tests
test_endpoint "$TENANT_A" "/modulo/list" 200
test_endpoint "$TENANT_B" "/modulo/list" 200

echo "✅ Smoke test OK"
```

### 8.3 DoD (Definition of Done)

**Por cada mantenedor migrado:**

```
□ Multi-tenancy validado (A/B + cross-tenant)
□ Smoke test ejecutado y OK
□ Tests caracterización ejecutados (baseline o actualizado)
□ Validación manual completada:
  □ CRUD completo funciona
  □ Modales abren/cierran/guardan
  □ Delete con SweetAlert2 funciona
  □ Paginación dentro de frame
  □ Filtros se mantienen al paginar
□ Sin errores en consola browser
□ DOM no cambió (ids/classes intactos)
□ Commit con descripción + evidencia
```

---

## 9. CHECKLISTS OPERATIVOS {#9-checklists}

### 9.1 Checklist Diario

```
LUNES - Análisis
□ Identificar bundle/categoría a migrar
□ Claude Code: análisis completo
□ Revisar paquete de migración
□ Aprobar plan

MARTES-JUEVES - Implementación
□ Por cada archivo:
  □ Abrir en IDE
  □ Pegar prompt Copilot
  □ Implementar cambios
  □ Commit pequeño
□ Ejecutar smoke test
□ Validación manual rápida

VIERNES - Validación
□ Tests completos
□ Validación multi-tenant exhaustiva
□ Claude Code: revisión final
□ Documentar cambios
□ Planificar siguiente semana
```

### 9.2 Checklist Multi-Tenant (CRÍTICO)

```
AISLAMIENTO DE DATOS
□ Tenant A solo ve sus datos
□ Tenant B solo ve sus datos
□ Cross-tenant access bloqueado (403/404)
□ Crear en A → B no lo ve
□ Editar en A → B no afectado
□ Eliminar en A → B no afectado

SEGURIDAD
□ URLs con IDs ajenos → 403/404
□ Búsquedas filtran por tenant
□ Reportes solo datos propios
□ Exportaciones solo datos propios

INTEGRIDAD
□ Relaciones respetan tenant
□ Cascade deletes respetan tenant
□ Triggers respetan tenant
```

### 9.3 Checklist Pre-Commit

```
□ Tests automáticos ejecutados
□ Smoke test pasa
□ Tests caracterización pasan
□ Validación manual OK
□ Sin errores consola
□ Multi-tenant verificado
□ Comportamiento equivalente a Symfony 3
□ Código comentado/documentado
□ Commit message descriptivo
□ Branch actualizado
```

---

## 10. TROUBLESHOOTING {#10-troubleshooting}

### 10.1 Copilot gasta cuota muy rápido

**Síntomas:**
- Llegas al 90%+ mensual en días

**Solución:**
```
□ Evitar Copilot Chat (usa mucha cuota)
□ Usar solo autocompletado inline
□ Preguntas largas → Claude Code (no Copilot)
□ Análisis → Claude Code
□ Explicaciones → Claude Code
```

### 10.2 Copilot hace cambios incorrectos

**Síntomas:**
- Rompe multi-tenancy
- Cambia IDs/clases
- Optimiza queries sin permiso
- Moderniza demasiado

**Solución:**
```
□ Revisar prompt (debe estar como comentario)
□ Prompt debe ser EXPLÍCITO en restricciones
□ Rechazar sugerencias incorrectas
□ Editar manualmente si persiste
□ Reportar a Claude Code para ajustar prompts
```

### 10.3 Tests de caracterización fallan sin razón

**Síntomas:**
- Snapshot difiere pero lógicamente es igual

**Causa:**
- IDs/fechas/tokens variables no normalizados

**Solución:**
```php
// Normalizar antes de snapshot
$html = preg_replace('/id="row-\d+"/', 'id="row-{{ID}}"', $html);
$html = preg_replace('/\d{4}-\d{2}-\d{2}/', '{{DATE}}', $html);
$html = preg_replace('/token=[a-f0-9]{32}/', 'token={{TOKEN}}', $html);

$this->assertMatchesSnapshot($html);
```

### 10.4 Modal no cierra después de guardar

**Síntomas:**
- Form se guarda pero modal permanece abierto

**Causa:**
- Controller no devuelve Turbo Stream correcto

**Solución:**
```php
// Controller debe devolver stream, no redirect
return $this->render('modulo/_create_stream.html.twig', [
    'item' => $item
]);
```

```twig
{# _create_stream.html.twig #}
<turbo-stream action="append" target="modulo-list">
    <template>
        {{ include('modulo/_list_item.html.twig', {item: item}) }}
    </template>
</turbo-stream>

<turbo-stream action="update" target="modal-content">
    <template></template>
</turbo-stream>

<turbo-stream action="append" target="flash-messages">
    <template>
        <div class="alert alert-success">Guardado correctamente</div>
    </template>
</turbo-stream>
```

### 10.5 Cross-tenant access no bloqueado

**Síntomas:**
- Tenant B puede ver datos de Tenant A

**Causa:**
- Falta validación en controller

**Solución:**
```php
public function edit(int $id, TenantService $tenant): Response
{
    $item = $repository->find($id);
    
    // AGREGAR ESTO:
    if ($item->getTenant() !== $tenant->getCurrent()) {
        throw $this->createNotFoundException();
    }
    
    // ...
}
```

---

## ANEXOS

### A. Template Spec de Mantenedor

Ver archivo separado: `specs/TEMPLATE_SPEC_MANTENEDOR.md`

### B. Prompts Completos Adicionales

Ver archivo separado: `prompts/PROMPTS_COMPLETOS.md`

### C. Estimaciones de Tiempo

| Tipo Bundle | Análisis (Claude) | Implementación (Copilot) | Validación | Total |
|-------------|-------------------|---------------------------|------------|-------|
| Simple (2-3 mantenedores) | 30 min | 2-4 hrs | 1 hr | 4-6 hrs |
| Medio (5-8 mantenedores) | 1 hr | 6-10 hrs | 2 hrs | 9-13 hrs |
| Grande (10+ mantenedores) | 2 hrs | 12-20 hrs | 3-4 hrs | 17-26 hrs |
| Crítico (Caja, Facturación) | 3 hrs | 20-30 hrs | 6-8 hrs | 29-41 hrs |

**Ritmo sostenible:** 3-4 hrs/día = 1-2 categorías/semana

---

**FIN DE GUÍA MAESTRA**

*Este documento es la fuente de verdad para la migración MELISA.*  
*Actualizar cuando haya cambios en el proceso.*

**Versión:** 3.0  
**Última actualización:** Enero 2026  
**Siguiente revisión:** Al completar 10 categorías
