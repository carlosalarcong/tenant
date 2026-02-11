# 📖 ÍNDICE COMPLETO - PROMPTS PARA COPILOT: Tree View Admin Menús

## 🎯 OBJETIVO

Implementar una interfaz moderna de administración de menús con **Tree View + Drag & Drop** para gestionar jerarquías de hasta 4 niveles.

---

## 📚 ESTRUCTURA DE LA GUÍA

Esta guía está dividida en **4 partes** secuenciales:

### **PARTE 1: BACKEND** (Controller y Endpoints AJAX)
📄 `COPILOT_PROMPTS_PART_1_BACKEND.md`

**Contenido:**
- ✅ Endpoint para actualizar jerarquía (drag & drop)
- ✅ Endpoint para obtener árbol como JSON
- ✅ Endpoint para agregar hijo directo
- ✅ Validación de referencias circulares

**Tiempo estimado**: 30-45 minutos
**Archivos modificados**: `src/Controller/Admin/MenuConfigController.php`

---

### **PARTE 2: FRONTEND** (HTML/Twig Tree View)
📄 `COPILOT_PROMPTS_PART_2_FRONTEND.md`

**Contenido:**
- ✅ Reemplazo completo de `index.html.twig`
- ✅ Tree View con macro recursivo
- ✅ Toolbar con búsqueda y botones
- ✅ Estilos CSS embebidos
- ✅ Data-attributes para JavaScript

**Tiempo estimado**: 20-30 minutos
**Archivos modificados**: `templates/admin/menu_config/index.html.twig`

---

### **PARTE 3: JAVASCRIPT** (Drag & Drop + Interactividad)
📄 `COPILOT_PROMPTS_PART_3_JAVASCRIPT.md`

**Contenido:**
- ✅ SortableJS para drag & drop
- ✅ Colapsar/Expandir árbol
- ✅ Búsqueda con debounce
- ✅ Botón "Agregar Hijo"
- ✅ AJAX para actualizar sin recargar

**Tiempo estimado**: 40-60 minutos
**Archivos modificados**: `templates/admin/menu_config/index.html.twig` (bloque javascripts)

---

### **PARTE 4: TESTING Y VALIDACIÓN**
📄 `COPILOT_PROMPTS_PART_4_TESTING.md`

**Contenido:**
- ✅ 10 tests funcionales completos
- ✅ Checklist de validación
- ✅ Troubleshooting común
- ✅ Métricas de éxito
- ✅ Documentación de usuario

**Tiempo estimado**: 30-40 minutos

---

## 🚀 GUÍA DE INICIO RÁPIDO

### 1. **Preparación** (5 minutos)

**Verificar requisitos:**
```bash
# Symfony 7.4+
php bin/console --version

# PostgreSQL con tabla menu_items
psql -d melisalacolina -c "\d menu_items"

# Bootstrap 5 y Boxicons cargados
# Verificar en templates/app_layout.html.twig
```

---

### 2. **Abrir Visual Studio Code**

```bash
# Abrir el proyecto
code /var/www/html/melisa_tenant

# Instalar Copilot si no lo tienes:
# Extensiones → Buscar "GitHub Copilot" → Instalar
```

---

### 3. **Ejecutar PARTE 1: Backend** (30 min)

1. Abrir `docs/COPILOT_PROMPTS_PART_1_BACKEND.md`
2. Abrir `src/Controller/Admin/MenuConfigController.php` en VS Code
3. Copiar **PROMPT 1** del documento
4. Pegar en el chat de Copilot (o usar Copilot Inline)
5. Copiar el código generado y pegarlo en el controller
6. Repetir con PROMPT 2, 3 y 4

**Verificar:**
```bash
php bin/console debug:router | grep admin_menu_config
# Debe mostrar 9 rutas (6 existentes + 3 nuevas)
```

---

### 4. **Ejecutar PARTE 2: Frontend** (25 min)

1. Abrir `docs/COPILOT_PROMPTS_PART_2_FRONTEND.md`
2. Abrir `templates/admin/menu_config/index.html.twig`
3. **IMPORTANTE**: Hacer backup del archivo actual:
   ```bash
   cp templates/admin/menu_config/index.html.twig templates/admin/menu_config/index.html.twig.backup
   ```
4. Copiar el **PROMPT COMPLETO** del documento
5. Pegar en Copilot
6. **REEMPLAZAR COMPLETAMENTE** el contenido del archivo
7. Ejecutar el segundo prompt para agregar CSS

**Verificar:**
- Abrir `http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config`
- Debe verse el árbol jerárquico (sin funcionalidad aún)

---

### 5. **Ejecutar PARTE 3: JavaScript** (50 min)

1. Abrir `docs/COPILOT_PROMPTS_PART_3_JAVASCRIPT.md`
2. El mismo archivo `templates/admin/menu_config/index.html.twig` está abierto
3. Ir al final del archivo (después del último `{% endblock %}`)
4. Ejecutar PROMPT 1 (Drag & Drop básico)
5. Ejecutar PROMPT 2 (Colapsar/Expandir)
6. Ejecutar PROMPT 3 (Búsqueda)
7. Ejecutar PROMPT 4 (Agregar hijo)

**Verificar:**
- Drag & drop debe funcionar
- Click en ▼ debe colapsar/expandir
- Búsqueda debe filtrar items

---

### 6. **Ejecutar PARTE 4: Testing** (40 min)

1. Abrir `docs/COPILOT_PROMPTS_PART_4_TESTING.md`
2. Ejecutar los 10 tests funcionales uno por uno
3. Marcar cada checklist cuando pase
4. Si algún test falla, ir a la sección de Troubleshooting

**Al finalizar:**
- [ ] Todos los tests pasan ✅
- [ ] No hay errores en consola ✅
- [ ] Los cambios persisten ✅

---

## 📊 TIEMPO TOTAL ESTIMADO

| Parte | Tiempo | Dificultad |
|-------|--------|------------|
| Preparación | 5 min | ⭐ |
| Parte 1 (Backend) | 30-45 min | ⭐⭐⭐ |
| Parte 2 (Frontend) | 20-30 min | ⭐⭐ |
| Parte 3 (JavaScript) | 40-60 min | ⭐⭐⭐⭐ |
| Parte 4 (Testing) | 30-40 min | ⭐⭐ |
| **TOTAL** | **2-3 horas** | ⭐⭐⭐ |

---

## 🎯 QUÉ ESPERAR EN CADA PARTE

### PARTE 1: Backend
**Entradas**: Prompts para Copilot
**Salidas**: 3 endpoints AJAX + validaciones

```php
// Nuevos métodos en MenuConfigController:
public function updateHierarchy()      // Drag & drop
public function getMenuTree()          // API JSON
public function addChild()             // Crear hijo
private function isCircularReference() // Validar
```

---

### PARTE 2: Frontend
**Entradas**: Prompt completo para Twig
**Salidas**: Tree View HTML + CSS

```twig
{# Nuevo archivo index.html.twig #}
- Toolbar con búsqueda
- Árbol con macro recursivo
- 4 niveles de profundidad
- Botones de acción
- Estilos CSS embebidos
```

---

### PARTE 3: JavaScript
**Entradas**: 4 prompts para funcionalidades
**Salidas**: JavaScript completo con SortableJS

```javascript
// Nuevas funciones:
initializeSortable()      // Drag & drop
initializeTreeToggles()   // Colapsar/Expandir
initializeSearch()        // Búsqueda
initializeAddChildButtons() // Agregar hijo
```

---

### PARTE 4: Testing
**Entradas**: Checklist de validación
**Salidas**: Aplicación funcionando al 100%

```
✅ 10 tests funcionales
✅ Sin errores
✅ Persistencia de cambios
✅ Validaciones funcionando
```

---

## 💡 CONSEJOS PARA USAR CON COPILOT

### ✅ **BUENAS PRÁCTICAS**

1. **Copiar prompts completos**: No editar los prompts, copiar tal cual
2. **Ejecutar en orden**: Las partes dependen unas de otras
3. **Verificar después de cada parte**: No avanzar si algo falla
4. **Hacer backups**: Guardar archivos originales antes de modificar
5. **Leer el código generado**: Entender qué hace antes de pegarlo

### ❌ **ERRORES COMUNES**

1. **Saltar partes**: No puedes hacer PARTE 3 sin PARTE 1
2. **No verificar rutas**: Siempre ejecutar `debug:router` después de PARTE 1
3. **Mezclar código**: No mezclar código generado con código viejo
4. **No invalidar cache**: Ejecutar `cache:clear` entre partes
5. **No probar en cada paso**: Probar inmediatamente después de cada prompt

---

## 🔧 COMANDOS ÚTILES

```bash
# Verificar rutas
php bin/console debug:router | grep admin_menu_config

# Limpiar cache
php bin/console cache:clear

# Ver logs en tiempo real
tail -f var/log/dev.log | grep -i menu

# Verificar sintaxis PHP
php -l src/Controller/Admin/MenuConfigController.php

# Verificar sintaxis Twig
php bin/console lint:twig templates/admin/menu_config/

# Ver estructura de BD
psql -d melisalacolina -c "\d menu_items"

# Contar items en BD
psql -d melisalacolina -c "SELECT COUNT(*) FROM menu_items;"
```

---

## 📁 ARCHIVOS QUE SE VAN A MODIFICAR

| Archivo | Parte | Acción | Backup |
|---------|-------|--------|--------|
| `src/Controller/Admin/MenuConfigController.php` | 1 | Agregar métodos | ✅ Recomendado |
| `templates/admin/menu_config/index.html.twig` | 2, 3 | Reemplazar completo | ✅ OBLIGATORIO |

**Comando para backups:**
```bash
# Backup del controller
cp src/Controller/Admin/MenuConfigController.php src/Controller/Admin/MenuConfigController.php.backup

# Backup del template
cp templates/admin/menu_config/index.html.twig templates/admin/menu_config/index.html.twig.backup
```

---

## 🎓 APRENDIZAJE

Al completar esta guía, habrás aprendido:

1. ✅ Cómo estructurar endpoints AJAX en Symfony
2. ✅ Cómo crear interfaces jerárquicas con Twig
3. ✅ Cómo usar SortableJS para drag & drop
4. ✅ Cómo validar relaciones circulares
5. ✅ Cómo usar Copilot efectivamente para tareas complejas

---

## 📞 SOPORTE

**Si algo no funciona:**

1. Revisar la sección de **Troubleshooting** en PART_4
2. Verificar logs: `tail -f var/log/dev.log`
3. Verificar consola del navegador (F12)
4. Comparar con el código esperado en cada parte
5. Restaurar desde backup y volver a intentar

---

## 🎉 RESULTADO FINAL

Al completar las 4 partes tendrás:

```
┌────────────────────────────────────────────────┐
│ Configuración del Menú                         │
├────────────────────────────────────────────────┤
│ [🔍 Buscar] [▼] [▶] [🔄] [+ Nuevo]            │
├────────────────────────────────────────────────┤
│                                                 │
│ 📂 Dashboard                    [✓][✏️][➕][🗑️]│
│                                                 │
│ ▼ 📂 Mantenedores              [✓][✏️][➕][🗑️]│
│   ├─ 📁 Básico (12)            [✓][✏️][➕][🗑️]│
│   ├─ 📁 Estructura (3)         [✓][✏️][➕][🗑️]│
│   └─ ▼ 📂 Comercial (27)      [✓][✏️][➕][🗑️]│
│       ├─ 📁 Tipos (6)          [✓][✏️][➕][🗑️]│
│       ├─ 📁 Básicos (7)        [✓][✏️][➕][🗑️]│
│       ├─ 📁 Patologías (6)     [✓][✏️][➕][🗑️]│
│       └─ 📁 Complejos (3)      [✓][✏️][➕][🗑️]│
│                                                 │
│ ✨ Arrastra para reorganizar                   │
└────────────────────────────────────────────────┘
```

---

## ✅ CHECKLIST PRE-INICIO

Antes de empezar, verifica:

- [ ] Tengo Visual Studio Code instalado
- [ ] Tengo GitHub Copilot activo
- [ ] El proyecto está en `/var/www/html/melisa_tenant`
- [ ] Tengo acceso a la BD PostgreSQL
- [ ] El servidor web está corriendo (puerto 8081)
- [ ] He hecho backup de los archivos originales
- [ ] Tengo 2-3 horas disponibles
- [ ] He leído el índice completo

---

## 🚦 ¡LISTO PARA EMPEZAR!

**Orden de ejecución:**

1. ✅ Leer este índice (estás aquí)
2. ➡️ Ir a **COPILOT_PROMPTS_PART_1_BACKEND.md**
3. ➡️ Ir a **COPILOT_PROMPTS_PART_2_FRONTEND.md**
4. ➡️ Ir a **COPILOT_PROMPTS_PART_3_JAVASCRIPT.md**
5. ➡️ Ir a **COPILOT_PROMPTS_PART_4_TESTING.md**
6. 🎉 ¡Celebrar!

---

**Fecha de creación**: 2026-02-01
**Versión**: 1.0
**Estado**: ✅ Listo para usar
**Autor**: Claude Code
**Tiempo estimado total**: 2-3 horas
**Dificultad**: ⭐⭐⭐ (Intermedio)

---

¡Buena suerte! 🚀
