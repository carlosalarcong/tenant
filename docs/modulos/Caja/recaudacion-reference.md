# MELISA — Referencia Visual y de Flujo: Módulo Recaudación (feature/caja)

> **USO:** Este documento es la fuente de verdad para implementar o modificar vistas del módulo Recaudación.  
> Antes de tocar cualquier template Twig de este módulo, leer este archivo completo con `cat docs/recaudacion-reference.md`.

---

## 1. Estructura General de la Vista

La vista principal es `/recaudacion` y contiene **4 pestañas** en la barra superior:

```
[ 💳 Pago Paciente ] [ 📊 Gestión Caja ] [ 🖥 Transbank ] [ 🖨 Informes ▾ ]
```

- **Pago Paciente** — pestaña activa por defecto cuando la caja está abierta
- **Gestión Caja** — apertura y cierre de caja
- **Transbank** — integración tarjetas (⚠️ sin documentación de capturas aún)
- **Informes** — dropdown con sub-opciones (ver sección 5)

**Regla crítica de negocio:** Todas las pestañas operativas (Pago Paciente, Transbank) deben estar bloqueadas o mostrar aviso cuando la caja está cerrada. El cajero solo puede operar con caja abierta.

---

## 2. Pestaña: Gestión Caja

### 2a. Estado: Caja Abierta (sin cerrar)

Encabezado de sección: `Apertura Cierre Caja` (fondo azul `#337ab7`)

**Banner de advertencia** (fondo amarillo claro `#fcf8e3`, texto naranja):
```
⚠ Caja Abierta sin Cerrar
Debe Cerrar Caja anterior que se encuentra actualmente sin Cerrar.
```

**Botón principal:** `🔒 Cerrar Caja` (rojo/naranja, centrado)

**Sección Información Cajero** (header gris oscuro `#555`):
| Campo | Valor |
|-------|-------|
| Rut | xxxxxxx-x |
| Nombre | [nombre cajero] |
| Apellidos | [apellidos] |

**Sección Información Caja** (header gris oscuro):
Subtítulo: `Caja Actualmente Abierta`
| Campo | Valor |
|-------|-------|
| Fecha Apertura | DD-MM-YYYY HH:MM |
| Fecha Cierre | "Caja aun no ha sido Cerrada" |
| Ubicación Caja | [nombre ubicación] |
| Último Documento Emitido | BOLETA N° Documento XXXX |
| Monto Inicial | CLP0 |

---

### 2b. Estado: Caja Cerrada (lista para abrir)

**Banner de aviso** (fondo amarillo claro, texto naranja):
```
Caja Cerrada
Debe Abrir una Nueva Caja.
```

**Botón principal:** `🔒 Abrir Caja` (verde, centrado)

**Sección Información Cajero:** igual que 2a

**Sección Información Caja:**
Subtítulo: `Caja Anterior`
| Campo | Valor |
|-------|-------|
| Fecha Apertura | DD-MM-YYYY HH:MM |
| Fecha Cierre | DD-MM-YYYY HH:MM |
| Ubicación Caja | [nombre] |
| Último Documento Emitido | BOLETA N° Documento XXXX |
| Monto Inicial | CLP0 |
| Monto de Cierre | CLP14.870 |

---

### 2c. Modal: Cerrar Caja

Se abre al hacer clic en "Cerrar Caja". Es un modal (no una página separada).

**Título modal:** `Cierre Caja` (fondo azul)

**Sección 1 — Ingreso Número de Depósito:**
| Campo | Descripción |
|-------|-------------|
| Monto EFECTIVO | Muestra el monto en efectivo (ej: CLP14.770), solo lectura |
| Número de Depósito | Input texto, campo requerido |

**Sección 2 — Ingreso Superávit / Déficit Caja:**
Radio buttons (selección excluyente):
- `○ Superávit` + input monto (deshabilitado si no seleccionado)
- `○ Déficit` + input monto (deshabilitado si no seleccionado)
- `○ No`

**Botón footer:** `← Volver` (gris, alineado derecha)

**Post-cierre:** Aparece alerta modal:
```
✓ Caja Cerrada   Caja Cerrada correctamente.
[Ok]
```

---

## 3. Pestaña: Pago Paciente

### 3a. Búsqueda de Paciente

La pestaña tiene **dos sub-vistas** en el panel izquierdo (sidebar angosto):
- `🔍 Busqueda Simple` (activa por defecto)
- `🔍 Busqueda Avanzada`

**Búsqueda Simple:**
```
Tipo de Identificación: [Rut ▾]   [Campo Identificación *]   [🔍 Buscar]
```
- El campo de identificación tiene borde naranja cuando está enfocado
- `*` indica campo obligatorio

**Búsqueda Avanzada:**
```
Nombre [input **]   Apellido Paterno [input **]   Apellido Materno [input **]

Parámetros de Búsqueda:
○ Que contenga   ○ Que inicie   ● Exacta

[🔍 Buscar]   [✏ Limpiar]
```
- `**` = mínimo un campo debe ser completado
- Default: "Exacta" seleccionada

---

### 3b. Paciente no encontrado → Crear Person

Si el RUT buscado no existe en el sistema, aparecen botones para agregar person.

**Modal / formulario crear person** (imagen 11):
Campos básicos de identificación para registrar el paciente en el sistema.  
⚠️ *Detalle completo de campos pendiente de documentar.*

---

### 3c. Paciente encontrado — Vista completa

Una vez encontrado el paciente, la vista muestra las siguientes **secciones colapsables** (todas expandibles con `▲/▼`):

#### Encabezado — Información Paciente
Header: `👤 Información Paciente` + botón `✏ Editar` (naranja, alineado derecha)

| Campo | Valor |
|-------|-------|
| Rut | 11.111.111-1 |
| Nombre | [nombre] |
| Fecha Nacimiento | DD-MM-YYYY |
| Edad | [número] |
| Apellidos | [apellido paterno] [apellido materno] |
| Empresa Solicitante | [empresa o vacío] |

#### Sección colapsable 1 — Listado de Pagos Efectuados por Paciente
Header gris con `▲` toggle. Contiene DataTable con:
- Selector "Mostrar [10 ▾] registros" + buscador derecha
- Columnas: `Atención ↕` | `Fecha Atención ↕` | `Financiador ↕` | `Estado Cuenta ↕` | `Tipo Atención ↕` | `Adjunto ↕`
- Paginación: `«` `»` con info "Mostrando registros X al Y de Z"

#### Sección colapsable 2 — Listado de Solicitudes de Imagenología y/o Laboratorio
Header gris con `▲` toggle. Columnas:
`Atención` | `Fecha Atención` | `Observación` | `Código` | `Examen` | `Tipo Prestación` | `Tipo Atención`

#### Sección colapsable 3 — Listado Tratamientos en Proceso de Paciente
Header gris con `▲` toggle. Columnas:
`Nombre Tratamiento ↕` | `Fecha Creación ↕` | `Estado Del Tratamiento ↕`

---

### 3d. Datos Prestación (formulario editable)

Header: `📋 Datos Prestación` + botón `✏ Editar` (naranja) cuando ya está completado.

**En modo edición (formulario):**
| Columna izquierda | Columna derecha |
|-------------------|-----------------|
| Financiador [select *] | Origen: [select] |
| Convenio: [select] | Derivado por: [autocomplete profesional] |
| Plan: [select *] | Derivado Externo: [checkbox] |

**En modo lectura (post-guardar):**
Muestra valores como texto plano en tabla de dos columnas.

**Botones footer (cuando en formulario):**
- `$ Seleccionar Medio de Pago` (verde, derecha)
- `← Cancelar` (gris, derecha)

---

### 3e. Sección Agregar Prestaciones (Pago Normal)

Aparece debajo de Datos Prestación al hacer clic en modo edición.

Header: `➕ Agregar`

```
Prestación  [input búsqueda *]   [🔍 Buscar]
```

Al buscar y seleccionar una prestación, aparece la sección:

#### Listado Servicios Pago
Header: `☰ Listado Servicios Pago` + botón `✏ Editar` (naranja)

| Código | Pabellón | Nombre | Cantidad | Valor | Total |
|--------|----------|--------|----------|-------|-------|
| 101001-WI-CON-0001 | No Aplica | CONSULTA MEDICINA GENERAL | 1 | $14,870 | $14,870 |
| 108001-WI-CON-0003 | No Aplica | TELECONSULTA MEDICINA GENERAL | 1 | $12,630 | $12,630 |
| | | | | **Total Cuenta:** | **$27,500** |

El total aparece en la última fila alineado a la derecha y en negrita.

---

### 3f. Medios de Pago

Se accede al hacer clic en `$ Seleccionar Medio de Pago`.

Header sección: `$ Medios de Pago` (gris oscuro)

Contiene **dos sub-pestañas internas:**
- `$ Medios de Pago` (activa por defecto)
- `$ Otros Medios`

#### Panel de totales (fondo verde claro):
```
Total Cuenta     $27,500
Saldo Cuenta     $27,500  →  se actualiza en tiempo real restando montos ingresados
```
Cuando saldo llega a $0, la transacción está completa.

#### Banner amarillo:
```
⇅ Aplicar Diferencia Prestaciones
```

#### Grid de Medios de Pago (2 columnas):
Cada medio es un **panel azul oscuro** con toggle on/off y botón X:

```
[ EFECTIVO                    ○ ✕ ]    [ TARJETA CREDITO              ○ ✕ ]
[ TARJETA DEBITO              ○ ✕ ]    [ TRANSFERENCIA BANCARIA       ○ ✕ ]
[ BONO ELECTRONICO            ○ ✕ ]    [ BONO MANUAL                  ○ ✕ ]
[ CONVENIOS                   ○ ✕ ]
```

**Al activar un toggle** (toggle se vuelve naranja con check ✓):
- El panel se expande mostrando:
  ```
  Monto  [input número *]
  ```
- El Saldo Cuenta disminuye automáticamente al ingresar el monto

**Botones footer:**
- `$ Efectuar Pago` (verde, derecha) — se habilita cuando Saldo Cuenta = $0
- `← Cancelar` (gris, derecha)

⚠️ **Flujo post-pago pendiente de documentar** (qué documento se genera, qué vista aparece).

---

## 4. Pestaña: Transbank

⚠️ **Sin capturas disponibles aún.** No implementar hasta tener referencia visual.

---

## 5. Pestaña: Informes (Dropdown)

Al hacer clic despliega un menú con las siguientes opciones:

```
📄 Informe Caja Actual
📄 Informe General Garantías
📄 Informe Medios de Pago por Profesional
────────────────────────────────
☰  Listado Cajas
```

### 5a. Listado Cajas

Vista separada con:

**Header:** `HISTORIAL DE CAJAS:` (fondo azul)

**Sección Información Cajero** (igual que Gestión Caja)

**Sección Listado Cajas:**
- Selector "Mostrar [10 ▾] registros" + buscador derecha
- Columnas: `Sucursal ↕` | `Ubicación ↕` | `Monto Inicial ↕` | `Fecha Apertura ↕` | `Fecha Cierre ↕` | `[Ver Detalle]`
- Botón `Ver Detalle` (azul) por fila → abre Informe de Caja
- Paginación numérica: `« 1 2 3 4 5 »`

### 5b. Informe de Caja (detalle)

**Header:** `INFORME DE CAJA:` (fondo azul)

**Sección Información Cajero:**
| Campo | Valor |
|-------|-------|
| Rut | xxxxxxx-x |
| Cajero | [nombre completo] |
| Fecha Caja | DD-MM-YYYY |
| Estado Cajas | Cerrada / Abierta |

**Si sin transacciones** — banner advertencia (fondo amarillo):
```
NO SE REGISTRAN TRANSACCIONES PARA ÉSTA CAJA
No se han generados pagos y/o garantías relacionados con ésta Caja.
```

**Botón footer:** `Volver` (gris, alineado derecha)

---

## 6. Convenciones Visuales del Legacy

Estos estilos son los que usa el sistema legacy como referencia base:

| Elemento | Color / Estilo |
|----------|---------------|
| Header secciones principal | Azul `#337ab7` texto blanco |
| Header subsecciones | Gris oscuro `#555` texto blanco |
| Filas tabla (par) | Blanco |
| Filas tabla (impar) | Gris muy claro `#f9f9f9` |
| Botón primario | Azul `#337ab7` |
| Botón acción | Verde `#5cb85c` |
| Botón peligro/cerrar | Rojo/naranja `#d9534f` |
| Botón secundario/cancelar | Gris `#777` |
| Botón editar | Naranja `#f0ad4e` |
| Panel medios de pago | Azul oscuro `#1a3a5c` texto blanco |
| Toggle activo | Naranja con check |
| Banner advertencia | Fondo `#fcf8e3`, texto naranja |
| Banner éxito | Fondo verde claro |
| Campo obligatorio | Asterisco `*` naranja al lado del input |
| Campo mínimo requerido | Doble asterisco `**` |

---

## 7. Flujos Pendientes de Documentar

Los siguientes flujos **no tienen capturas** en el documento fuente y deben consultarse antes de implementar:

- [ ] Pestaña **Transbank** completa
- [ ] **Flujo post-pago**: qué pantalla/documento aparece al hacer clic en "Efectuar Pago"
- [ ] **Otros Medios** sub-pestaña en Medios de Pago
- [ ] **Formulario crear Person** completo (campos y validaciones)
- [ ] Estado inicial Gestión Caja cuando **nunca se ha abierto una caja** para este cajero
- [ ] **Informes específicos**: Informe Caja Actual, Informe General Garantías, Informe Medios de Pago por Profesional

---

## 8. Notas de Implementación para MELISA Symfony 6.4

- Los medios de pago usan la misma arquitectura de **Turbo Frame** ya implementada en Admisión
- El `Saldo Cuenta` se actualiza en tiempo real con **Stimulus JS** (no reload de página)
- Las secciones colapsables (Pagos, Laboratorio, Tratamientos) se manejan con toggle JS simple
- La búsqueda de prestaciones es un **autocomplete** con llamada AJAX
- El listado de cajas usa **DataTables** con paginación server-side
- Formato de montos: CLP con puntos de miles, sin decimales (ej: `$14.870`)
