# Propuesta UX — Módulo de Admisión (Rediseño Navegación)

> **Basado en análisis del sistema legacy** `/var/www/html/melisa_prod/src/Rebsol/AdmisionBundle`
> **Rol:** UX Lead + Product Designer Senior — Flujos Clínicos Hospitalarios
> **Restricción:** Sin eliminar funcionalidad. Sin romper usuarios actuales. Velocidad > estética.

---

## RESUMEN EJECUTIVO

El módulo actual expone **8 pestañas horizontales** en una sola barra de navegación, todas al mismo nivel visual y cognitivo, sin jerarquía de uso real. Las pestañas 1–3 son **flujos de trabajo operativos** (alta frecuencia), las pestañas 4–8 son **consultas y reportes** (media-baja frecuencia). Esta mezcla fuerza al usuario a escanear 8 opciones en cada sesión para ejecutar tareas que en la práctica son 1–2 acciones repetitivas por turno. La búsqueda avanzada (tab 4) es una sub-función de las tabs 1–3, no un destino independiente. Los informes duplicados (tabs 7 y 8) son conceptualmente un solo reporte con un flag de variante. La solución propuesta consolida en **3 zonas funcionales**: Operación (tabs 1–3 unificadas), Consulta rápida, y Panel de Informes/Especiales, reduciendo la carga cognitiva sin eliminar ni una sola función.

---

## AUDITORÍA DEL ESTADO ACTUAL

### Pestañas confirmadas (desde `appUserTienePerfil.hmtl.twig`)

| # | Pestaña | Perfil requerido | Tipo | Frecuencia real |
|---|---|---|---|---|
| 1 | **Admisión Hospitalaria** | `ADMISION_ADMISION` | Flujo operativo | ⬛⬛⬛⬛⬛ Muy alta |
| 2 | **Pre Admisión** | `ADMISION_PRE_ADMISION` | Flujo operativo | ⬛⬛⬛⬛⬜ Alta |
| 3 | **Admisión Urgencia** | `ADMISION_URGENCIA` | Flujo operativo | ⬛⬛⬛⬛⬜ Alta |
| 4 | **Búsqueda Avanzada** | `ADMISION_BUSQUEDA_AVANZADA` | Sub-función de 1–3 | ⬛⬛⬛⬜⬜ Media |
| 5 | **Garantías** | `ADMISION_INFORME_GARANTIAS` | Reporte/Consulta | ⬛⬛⬜⬜⬜ Media-baja |
| 6 | **Tabla Quirúrgica** | `ADMISION_TABLA_QUIRURGICA` | Consulta específica | ⬛⬜⬜⬜⬜ Baja |
| 7 | **Informe Admisión** | `ADMISION_INFORME_ADMISION` | Reporte | ⬛⬜⬜⬜⬜ Baja |
| 8 | **Informe Admisión Custom** | `ADMISION_INFORME_ADMISION_CUSTOM` | Reporte variante | ⬛⬜⬜⬜⬜ Baja |

### Problemas UX identificados con evidencia

| Problema | Evidencia en código |
|---|---|
| 8 pestañas al mismo nivel visual | `<ul class="nav nav-tabs">` con 8 `<li>` en `appUserTienePerfil.hmtl.twig` |
| Búsqueda Avanzada es sub-función, no destino | `busquedaAvanzada.html.twig` incluida dentro del `Default/index.html.twig` vía `{% include %}` |
| Tabs 7 y 8 son el mismo endpoint con flag | Ambas apuntan a `Admision_PacientesAdmitidos_Ver` con `{ esCustom: 0 }` y `{ esCustom: 1 }` |
| Tabs 5 y 7/8 cargan con AJAX al hacer click | `$('a[href="#tabGarantias"]').on('shown.bs.tab')` → carga lazy, si no se hace click nunca cargan |
| Búsqueda por RUT requiere 4+ interacciones para admitir | Campo RUT → validar → buscar → elegir → comenzar admisión |
| Tabla Quirúrgica requiere perfil especial y es contexto diferente | `ADMISION_TABLA_QUIRURGICA` es del módulo Pabellón, incidentally expuesto aquí |

---

## ALTERNATIVAS DE DISEÑO

---

### Alternativa A — "Dos Zonas + Overflow"

**Concepto:** Separar visualmente en 2 grupos. Las 3 primeras pestañas (flujos operativos) son tabs grandes y prominentes. Las 5 restantes colapsan en un menú dropdown "Más ▾".

```
┌─────────────────────────────────────────────────────────────────────┐
│  [Admisión Hospitalaria]  [Pre Admisión]  [Admisión Urgencia]  │  Más ▾                  │
│   ──────────────────────────────────     └────────────────────┐     │
│                                               Búsqueda Avanzada│     │
│              ÁREA DE TRABAJO                  Garantías        │     │
│                                               Tabla Quirúrgica │     │
│                                               Informe Admisión  │     │
│                                               Informe Custom   │     │
│                                           └────────────────────┘     │
└─────────────────────────────────────────────────────────────────────┘
```

**Flujo principal:**
1. Usuario llega → ve 3 tabs operativas limpias
2. Busca paciente (RUT) en pestaña activa → 1 clic
3. Para informes → "Más ▾" → 2 clics

**Pros:** Mínimo cambio. Compatible con usuarios actuales. Implementación en 2 días.
**Contras:** Dropdown "Más" es ocultamiento parcial, no resuelve el problema conceptual. Usuarios avanzados seguirán haciendo los mismos clics.

---

### Alternativa B — "Operación + Panel Lateral Deslizante"

**Concepto:** Las 3 pestañas operativas permanecen. Los informes y herramientas especiales (Garantías, Quirúrgica, Informes) se mueven a un **panel lateral derecho deslizante** accesible con un botón flotante "📊 Reportes".

```
┌────────────────────────────────────────────────────────┬──────────┐
│  [Admisión Hospitalaria]  [Pre Admisión]  [Admisión Urgencia]                │📊 Reportes│
│                                                        └──────────┘
│  ┌─────────────────────────────────────────────┐                   │
│  │  Búsqueda por RUT:  [____________] [Buscar] │                   │
│  │  ○ RUT  ○ Pasaporte  ○ DNI                  │                   │
│  │  ─────────────────────────────────────────  │                   │
│  │  Búsqueda avanzada: [Nombre] [Ap. Pat.]     │                   │
│  │                     [Ap. Mat.] [Buscar]     │                   │
│  └─────────────────────────────────────────────┘                   │
└─────────────────────────────────────────────────────────────────────┘
                                    │ Al hacer clic en 📊 Reportes:
                                    ▼
┌────────────────────────────────────────────────────────┬─────────────┐
│  [Admisión Hospitalaria]  [Pre Admisión]  [Admisión Urgencia]                │  ✕ Reportes │
│  (contenido principal)                                 │─────────────│
│                                                        │ Garantías   │
│                                                        │─────────────│
│                                                        │ T. Quirúrg. │
│                                                        │─────────────│
│                                                        │ Inf. Admis. │
│                                                        │─────────────│
│                                                        │ Inf. Custom │
└────────────────────────────────────────────────────────┴─────────────┘
```

**Pros:** Operación diaria sin distracciones. Reportes accesibles pero fuera del flujo principal.
**Contras:** Dos zonas visuales pueden confundir a usuarios que usaban las tabs directamente. Requiere Stimulus drawer. Curva media.

---

### Alternativa C — "Centro de Operaciones Unificado" ✅ RECOMENDADA

**Concepto:** Una sola pantalla que integra:
- **Selector de modo** (Hospitalaria / Pre-Admisión / Urgencia) como toggle de 3 opciones dentro del área de trabajo (no tabs separadas)
- **Búsqueda única** (RUT + nombre en una sola barra, sin pestañas de búsqueda)
- **Barra de accesos rápidos** contextual arriba a la derecha: íconos de Garantías, Tabla Quirúrgica, Informes (solo con permisos)

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Admisión                           [🔒 Garantías] [📅 Quirúrgica] [📊] │
│─────────────────────────────────────────────────────────────────────────│
│                                                                         │
│  ┌─── Tipo de atención ────────────────────────────────────────────┐   │
│  │  ( ●  Hospitalaria )    (    Pre-Admisión   )  (    Urgencia   ) │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ┌─── Buscar paciente ─────────────────────────────────────────────┐   │
│  │  🔍 RUT, nombre o apellido...           [Buscar] [+ Nuevo]      │   │
│  │     ○ RUT/DNI/Pasaporte   ○ Nombre completo                     │   │
│  └──────────────────────────────────────────────────────────────────┘   │
│                                                                         │
│  ─── Resultados de búsqueda ──────────────────────────────────────     │
│  │ Paciente          │ RUT        │ Estado     │ Acciones          │    │
│  │ Juan Pérez García │ 12.345.678 │ Sin admitir│ [Admitir] [Ver]   │    │
│  │ ...               │ ...        │ ...        │ ...               │    │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘
```

**Panel de Accesos Rápidos (top-right, visible solo con permisos):**

```
[🔒 Garantías]   → slide-over panel derecho con tabla garantías del día
[📅 Quirúrgica]  → modal/panel con selector fecha + calendario
[📊 Informes ▾]  → dropdown: Informe Admisión / Informe Custom
```

---

## TABLA COMPARATIVA

| Criterio | A — Dos Zonas + Overflow | B — Panel Lateral | C — Centro Unificado ✅ |
|---|---|---|---|
| **Clics promedio tarea principal** (admitir paciente) | 4–5 | 4–5 | 2–3 |
| **Clics para informes** | 2 (dropdown) | 2 (panel) | 1 (botón directo) |
| **Curva de aprendizaje** | Muy baja (igual al actual) | Media | Baja-media |
| **Riesgo errores navegación** | Bajo | Medio | Bajo |
| **Escaneabilidad** (¿el usuario sabe dónde está?) | Media | Media | Alta |
| **Carga cognitiva** | Igual al actual | Menor | Mínima |
| **Escalabilidad futura** | Mala (dropdown crece) | Media | Alta |
| **Compatibilidad usuarios actuales** | Total | Media | Alta |
| **Esfuerzo implementación** | 1–2 días | 3–5 días | 5–8 días |

---

## RECOMENDACIÓN FINAL: ALTERNATIVA C

**Por qué C:**

1. **Elimina la falsa jerarquía**: Pre-Admisión y Urgencia no son destinos distintos al módulo — son *modos* de la misma operación. Tratarlos como toggle en lugar de tabs elimina confusión conceptual.

2. **Búsqueda unificada en 1 línea**: En el sistema actual, la búsqueda por RUT (tab 1) y la búsqueda avanzada (tab 4) son dos flujos separados que el usuario debe elegir antes de saber qué necesita. Una sola barra que acepte RUT O nombre resolve esto sin clic extra.

3. **Reportes e informes como herramientas, no destinos**: Garantías, Quirúrgica e Informes no son parte del flujo de admisión — son consultas paralelas. Los accesos rápidos top-right los ponen a 1 clic sin contaminar el área de trabajo.

4. **Compatible con el sistema de perfiles**: Cada botón de acceso rápido se muestra/oculta según el perfil (`ADMISION_INFORME_GARANTIAS`, `ADMISION_TABLA_QUIRURGICA`, etc.) — exactamente igual que hoy.

5. **Sin romper nada**: Los 8 endpoints del backend siguen existiendo. Solo cambia cómo se enlazan desde el frontend.

---

## FLUJOS CRÍTICOS DETALLADOS

### Tarea 1: Admitir paciente hospitalario (flujo más frecuente)

**Hoy (Alternativa actual):**
1. Abrir módulo → Admisión Hospitalaria tab (activa por defecto) → 0 clics extras
2. Ingresar RUT → validar formato → clic Buscar → esperar AJAX
3. Ver resultado → clic "Admitir" → redirige a formulario paso 2
4. Completar formulario financiador/convenio → Siguiente
5. Completar cama/servicio → Finalizar
**Total: ~5 interacciones para llegar al formulario**

**Con Alternativa C:**
1. Abrir módulo → toggle "Hospitalaria" activo por defecto → 0 clics
2. Barra búsqueda activa → escribir RUT → Enter (o clic Buscar)
3. Ver resultado inline → clic "Admitir"
**Total: ~3 interacciones para llegar al formulario**

---

### Tarea 2: Admisión de urgencia

**Hoy:** Clic tab "Admisión Urgencia" → nueva página cargada → buscar paciente → flujo
**Con C:** Clic toggle "Urgencia" → mismo campo de búsqueda cambia de contexto → buscar → flujo
**Ganancia: -1 clic + sin recarga de página**

---

### Tarea 3: Consultar garantías del día

**Hoy:** Clic tab "Garantías" → esperar carga AJAX → ver tabla
**Con C:** Clic botón [🔒 Garantías] → slide-over panel se abre → tabla ya lista
**Ganancia: acceso desde cualquier estado del módulo, sin perder contexto**

---

### Tarea 4: Revisar tabla quirúrgica

**Hoy:** Clic tab "Tabla Quirúrgica" → selector fecha → clic Buscar → esperar AJAX
**Con C:** Clic [📅 Quirúrgica] → modal abre con fecha de hoy pre-cargada → calendario visible
**Ganancia: fecha de hoy por defecto (-1 clic), acceso desde cualquier estado**

---

### Tarea 5: Buscar paciente ya admitido (búsqueda avanzada)

**Hoy:** Clic tab "Búsqueda Avanzada" → formulario de nombre/apellidos → buscar
**Con C:** En barra de búsqueda unificada → selector "Nombre completo" → escribir → Enter
**Ganancia: -1 clic (sin cambio de pestaña)**

---

## MICROINTERACCIONES PROPUESTAS

### Atajos rápidos de teclado
```
Alt+H  → cambiar a modo Hospitalaria
Alt+P  → cambiar a modo Pre-Admisión
Alt+U  → cambiar a modo Urgencia
Alt+G  → abrir panel Garantías
Alt+Q  → abrir Tabla Quirúrgica
F2     → foco en barra de búsqueda (acceso desde cualquier parte)
Esc    → cerrar panel/modal lateral
```

### Filtros persistentes
- El modo seleccionado (Hospitalaria / Pre / Urgencia) persiste en `localStorage` por sesión
- Al volver al módulo, se restaura el último modo usado
- Los filtros de informes (fecha desde/hasta) se recuerdan durante la sesión

### Acciones frecuentes en 1 clic
- **Imprimir garantías**: botón "🖨️" dentro del panel de garantías, sin salir
- **Buscar por RUT del día anterior**: historial de últimas búsquedas (últimas 5) en dropdown del campo de búsqueda
- **Admitir con mismo financiador**: si el paciente ya tuvo admisión reciente, pre-llenar convenio/financiador con el último usado

### Estados vacíos útiles
```
[Sin resultados de búsqueda]
  → "No se encontró ningún paciente con este RUT"
  → Opción: [Buscar por nombre] [Registrar nuevo paciente]

[Garantías sin registros hoy]
  → "No hay garantías registradas para hoy"
  → Opción: [Ver garantías de ayer] [Seleccionar otra fecha]

[Tabla Quirúrgica sin cirugías]
  → "No hay cirugías agendadas para el [fecha]"
  → Opción: [Ver siguiente día con cirugías] [Cambiar fecha]
```

---

## WIREFRAME TEXTUAL (LOW-FIDELITY) — ALTERNATIVA C

```
╔══════════════════════════════════════════════════════════════════════════════╗
║  MELISA HEALTH  >  Admisión                                                  ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                              [🔒 Garantías] [📅 Quirúrgica]  ║
║                                              [📊 Informes ▾]                 ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║  ┌── Tipo de Atención ──────────────────────────────────────────────────┐  ║
║  │                                                                       │  ║
║  │   ● Hospitalaria        ○ Pre-Admisión          ○ Urgencia           │  ║
║  │   ═════════════                                                       │  ║
║  │                                                                       │  ║
║  └───────────────────────────────────────────────────────────────────────┘  ║
║                                                                              ║
║  ┌── Buscar Paciente ───────────────────────────────────────────────────┐  ║
║  │  🔍 [___________________________________]  [Buscar]  [+ Nuevo]       │  ║
║  │      ● RUT / DNI / Pasaporte   ○ Nombre y Apellido                  │  ║
║  └───────────────────────────────────────────────────────────────────────┘  ║
║                                                                              ║
║  ── Resultados ──────────────────────────────────────────────────────────── ║
║                                                                              ║
║  ┌──────────────────────────┬─────────────┬────────────┬─────────────────┐  ║
║  │ Paciente                 │ RUT         │ Estado     │ Acciones        │  ║
║  ├──────────────────────────┼─────────────┼────────────┼─────────────────┤  ║
║  │ Juan Pérez García        │ 12.345.678-9│ Sin admitir│[Admitir] [Ver]  │  ║
║  │ María González López     │ 9.876.543-2 │ Urgencia   │[Ver Urgencia]   │  ║
║  └──────────────────────────┴─────────────┴────────────┴─────────────────┘  ║
║                                                                              ║
╠══════════════════════════════════════════════════════════════════════════════╣
║  Estado: Modo Hospitalaria  |  Sucursal: Central  |  Usuario: adm.central   ║
╚══════════════════════════════════════════════════════════════════════════════╝

  Al clic [🔒 Garantías]:
  ┌─────────────────────────────────────────────────────┐
  │ Garantías del Día        [🖨️ Imprimir]  [✕ Cerrar]  │
  │─────────────────────────────────────────────────────│
  │ Fecha: [hoy]  [◀ Ayer]  [▶ Mañana]                  │
  │─────────────────────────────────────────────────────│
  │ Paciente       │ Financiador  │ Monto   │ Hora       │
  │ Juan Pérez     │ Fonasa / A   │ $50.000 │ 09:30      │
  │ ...            │ ...          │ ...     │ ...        │
  └─────────────────────────────────────────────────────┘

  Al clic [📅 Quirúrgica]:
  ┌─────────────────────────────────────────────────────┐
  │ Tabla Quirúrgica                       [✕ Cerrar]   │
  │─────────────────────────────────────────────────────│
  │ Fecha: [10/02/2026 ▾]  [Buscar]                     │
  │─────────────────────────────────────────────────────│
  │  [Vista de calendarios/grilla de cirugías del día]  │
  └─────────────────────────────────────────────────────┘

  Al clic [📊 Informes ▾]:
  ┌──────────────────────────────┐
  │  Informe Admisión            │
  │  Informe Admisión Custom     │
  └──────────────────────────────┘
```

---

## PLAN DE IMPLEMENTACIÓN POR FASES

### Fase 1 — Rápida (1–2 días) | Bajo riesgo

**Objetivo:** Reducir sobrecarga visual sin cambiar funcionalidad.
**Cambios:**
1. Mover pestañas 5–8 (Garantías, Quirúrgica, Informes x2) a un menú dropdown "Más ▾" en la barra de tabs
2. Pestañas 1–3 quedan visibles y prominentes, igual que hoy
3. Búsqueda Avanzada se integra como toggle dentro de la tab activa (ya está incluida con `{% include %}`)

**Template a modificar:** `appUserTienePerfil.hmtl.twig` (solo HTML)
**Riesgo:** Mínimo. Reversible en 30 minutos.

---

### Fase 2 — Media (3–5 días) | Riesgo medio

**Objetivo:** Unificar búsqueda + modos como experiencia fluida.
**Cambios:**
1. Toggle de 3 modos (Hospitalaria / Pre / Urgencia) en lugar de 3 tabs separadas
2. Barra de búsqueda unificada (RUT + nombre) en lugar de dos secciones separadas
3. Accesos rápidos top-right para Garantías, Quirúrgica e Informes
4. Garantías y Tabla Quirúrgica como panels/modals (sin salir del módulo)
5. Persistencia de modo en `localStorage`

**Archivos nuevos en Symfony 7.4:**
- `templates/admission/index.html.twig` (nueva pantalla unificada)
- `assets/controllers/admission/mode_controller.js` (Stimulus para toggle de modo)
- `assets/controllers/admission/quick_access_controller.js` (Stimulus para accesos rápidos)

**Riesgo:** Medio. Usuarios deben re-aprender ubicación de pestañas 5–8, pero funcionalidad idéntica.

---

### Fase 3 — Completa (5–8 días) | Riesgo bajo-medio

**Objetivo:** Centro de Operaciones completo (Alternativa C).
**Cambios adicionales:**
1. Historial de búsquedas recientes (últimas 5) en dropdown del campo
2. Pre-llenado de financiador/convenio del último uso del paciente
3. Estados vacíos informativos con acciones sugeridas
4. Atajos de teclado (`F2` para foco, `Alt+H/P/U` para modo)
5. Filtros persistentes de informes por sesión
6. Fecha de hoy pre-cargada en Tabla Quirúrgica

**Archivos adicionales:**
- `assets/controllers/admission/search_history_controller.js`
- Lógica de pre-llenado en `AdmissionService`

---

## MÉTRICAS UX PARA VALIDAR EN PRODUCCIÓN

| Métrica | Método | Meta | Herramienta |
|---|---|---|---|
| **Tiempo a tarea: admitir paciente** | Cronometrar desde clic en módulo hasta formulario paso 1 | < 15 seg (hoy ~25 seg) | Observación directa |
| **Clics por tarea: admisión estándar** | Contar clics desde módulo hasta paso 1 | ≤ 3 (hoy 4–5) | Hotjar / logs |
| **Tasa de rebote entre pestañas** | % de usuarios que cambian de tab sin completar una acción | < 10% | Analytics |
| **Errores de navegación** | % de clicks en tab equivocada (ej: Informe cuando quería Garantías) | < 5% | Registro errores |
| **Tiempo en informes** | Tiempo promedio en tabs de informes (indicador de encontrabilidad) | Sin cambio o mejora | Logs de sesión |
| **Uso de búsqueda avanzada** | % de búsquedas por nombre vs RUT | Medir como baseline primero | Analytics |
| **Satisfaction score (CSAT)** | Encuesta post-turno: "¿Qué tan fácil fue operar hoy?" 1–5 | ≥ 4/5 | Forms internos |
| **Adopción de accesos rápidos** | % de accesos a Garantías/Quirúrgica via botón vs navegación manual | > 80% en 2 semanas | Logs |

### Baseline a medir ANTES de implementar

Registrar durante 1 semana en producción actual:
- Tiempo promedio admisión completa (desde módulo hasta finalizar)
- Cantidad de clics promedio por sesión de admisión
- Pestañas más visitadas por usuario (para confirmar frecuencia real)
- Horarios pico de uso (para no desplegar en peak operacional)

---

## NOTAS PARA IMPLEMENTACIÓN

### Lo que NO cambia (backend intacto)
- Todos los endpoints actuales funcionan igual
- Todos los perfiles de acceso (`tienePerfil`) funcionan igual
- La lógica de negocio (pasos de admisión, garantías, etc.) no se modifica

### Compatibilidad con usuarios actuales
- Fase 1 es casi invisible: solo agrega un "Más ▾"
- Fase 2 requiere una sesión de capacitación de 10 minutos para mostrar el nuevo toggle
- Los accesos por URL directa siguen funcionando (deep links no se rompen)

### Control de feature flags (recomendado)
```php
// Activar nuevo diseño por tenant o por perfil de usuario
// Permite rollback inmediato sin deploy
if ($this->featureFlags->isEnabled('admission_ux_v2')) {
    return $this->render('admission/index_v2.html.twig', ...);
}
return $this->render('admission/index.html.twig', ...); // legacy
```
