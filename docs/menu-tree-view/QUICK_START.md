# ⚡ QUICK START - Tree View Admin Menús

## 🎯 LO QUE VAS A HACER

Convertir esto:

```
ANTES (Tabla plana):
┌──────────────────────────────────┐
│ Pos | Nombre      | Padre | ✏️ 🗑️│
├──────────────────────────────────┤
│ 1   | Dashboard   | -     | ✏️ 🗑️│
│ 4   | Mantenedor  | -     | ✏️ 🗑️│
│ 27  | Comercial   | 4     | ✏️ 🗑️│
│ 28  | Tipos       | 27    | ✏️ 🗑️│
│ 29  | Tipos Cama  | 28    | ✏️ 🗑️│
│ ... 53 items más (difícil de ver)│
└──────────────────────────────────┘
```

En esto:

```
DESPUÉS (Tree View con Drag & Drop):
┌───────────────────────────────────────────┐
│ [🔍] [▼ Expandir] [🔄] [+ Nuevo]         │
├───────────────────────────────────────────┤
│ 📂 Dashboard             [✓][✏️][➕][🗑️]│
│                                            │
│ ▼ 📂 Mantenedores       [✓][✏️][➕][🗑️]│
│   └─ ▼ 📂 Comercial (27) [✓][✏️][➕][🗑️]│
│       ├─ 📁 Tipos (6)    [✓][✏️][➕][🗑️]│
│       │   ├─ 📄 Tipos Cama              │
│       │   └─ ...                        │
│       └─ ...                             │
│                                            │
│ ✨ Arrastra para reorganizar              │
└───────────────────────────────────────────┘
```

---

## ⏱️ TIEMPO: 2-3 HORAS

- **Backend**: 30-45 min
- **Frontend**: 20-30 min
- **JavaScript**: 40-60 min
- **Testing**: 30-40 min

---

## 📋 ANTES DE EMPEZAR

### 1. **Hacer Backups** (OBLIGATORIO)

```bash
cd /var/www/html/melisa_tenant

# Backup del controller
cp src/Controller/Admin/MenuConfigController.php \
   src/Controller/Admin/MenuConfigController.php.backup

# Backup del template
cp templates/admin/menu_config/index.html.twig \
   templates/admin/menu_config/index.html.twig.backup

echo "✅ Backups creados"
```

### 2. **Verificar Requisitos**

```bash
# Symfony funcionando
php bin/console --version
# Debe mostrar: Symfony 7.4.x

# BD con datos
psql -U melisa -d melisalacolina -c "SELECT COUNT(*) FROM menu_items;"
# Debe mostrar: 53

# Servidor corriendo
curl -I http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config
# Debe retornar: HTTP/1.1 200 OK

echo "✅ Todo listo para empezar"
```

---

## 🚀 INICIO RÁPIDO (3 PASOS)

### PASO 1: Abrir VS Code

```bash
code /var/www/html/melisa_tenant
```

**Verificar que tienes GitHub Copilot:**
- Ver ícono de Copilot en la barra inferior
- Si no lo tienes: Extensiones → Buscar "GitHub Copilot" → Instalar

---

### PASO 2: Abrir las 5 Guías

En VS Code, abrir estos 5 archivos en tabs:

1. `docs/COPILOT_PROMPTS_INDEX.md` ← **ÍNDICE**
2. `docs/COPILOT_PROMPTS_PART_1_BACKEND.md`
3. `docs/COPILOT_PROMPTS_PART_2_FRONTEND.md`
4. `docs/COPILOT_PROMPTS_PART_3_JAVASCRIPT.md`
5. `docs/COPILOT_PROMPTS_PART_4_TESTING.md`

---

### PASO 3: Seguir las Guías en Orden

**No saltar pasos. Ejecutar en orden:**

1. ✅ **PARTE 1**: Backend (30 min)
   - Abrir: `src/Controller/Admin/MenuConfigController.php`
   - Ejecutar: 4 prompts
   - Verificar: `php bin/console debug:router | grep admin_menu_config`

2. ✅ **PARTE 2**: Frontend (25 min)
   - Abrir: `templates/admin/menu_config/index.html.twig`
   - Ejecutar: 2 prompts
   - Verificar: Abrir en navegador

3. ✅ **PARTE 3**: JavaScript (50 min)
   - Mismo archivo: `index.html.twig`
   - Ejecutar: 4 prompts
   - Verificar: Drag & drop funciona

4. ✅ **PARTE 4**: Testing (40 min)
   - Ejecutar: 10 tests
   - Verificar: Todos pasan ✅

---

## 📖 CÓMO USAR LOS PROMPTS CON COPILOT

### Método 1: Copilot Chat (Recomendado)

```
1. Abrir archivo a modificar
2. Abrir Copilot Chat (Ctrl+Shift+I o Cmd+Shift+I)
3. Copiar el prompt COMPLETO del documento
4. Pegar en el chat de Copilot
5. Esperar a que genere el código
6. Revisar el código
7. Hacer click en "Insert at Cursor" o "Apply"
```

### Método 2: Copilot Inline

```
1. Abrir archivo a modificar
2. Posicionar cursor donde va el código
3. Escribir un comentario con el prompt:
   // PROMPT: [pegar el prompt aquí]
4. Presionar Enter y Copilot sugerirá código
5. Tab para aceptar
```

---

## 🎯 EJEMPLO PRÁCTICO: PARTE 1 - PROMPT 1

**1. Abrir archivo:**
```
src/Controller/Admin/MenuConfigController.php
```

**2. Ir al final de la clase (antes del último `}`)**

**3. Abrir Copilot Chat y pegar:**

```
Necesito agregar un endpoint AJAX al controlador MenuConfigController.php ubicado en src/Controller/Admin/MenuConfigController.php

CONTEXTO:
- Es un sistema Symfony 7.4
- La entidad MenuItem tiene parent_id y position
- Necesito actualizar la jerarquía cuando el usuario hace drag & drop en el frontend
- El endpoint debe recibir JSON con: item_id, new_parent_id (puede ser null), new_position
- Debe validar que no se cree una referencia circular
- Debe invalidar el cache del menú después de actualizar

[... resto del prompt ...]
```

**4. Copilot generará el código**

**5. Hacer click en "Insert at Cursor"**

**6. Guardar archivo (Ctrl+S)**

**7. Verificar:**
```bash
php bin/console debug:router | grep update_hierarchy
# Debe aparecer: admin_menu_config_update_hierarchy
```

✅ **LISTO**, continuar con PROMPT 2

---

## ⚠️ ERRORES COMUNES

### Error 1: "No encuentro el archivo"

```bash
# Verificar que estás en el directorio correcto
pwd
# Debe mostrar: /var/www/html/melisa_tenant

# Si no:
cd /var/www/html/melisa_tenant
```

### Error 2: "Copilot no genera código"

- ✅ Verificar que el prompt está COMPLETO (no cortado)
- ✅ Verificar que Copilot está activo (ícono verde)
- ✅ Intentar con un prompt más simple primero
- ✅ Recargar VS Code

### Error 3: "El código generado tiene errores"

- ✅ Leer el código antes de insertarlo
- ✅ Verificar que el contexto sea correcto
- ✅ Pedir a Copilot que corrija: "Fix the syntax errors"

### Error 4: "Las rutas no aparecen"

```bash
# Limpiar cache
php bin/console cache:clear

# Verificar sintaxis
php -l src/Controller/Admin/MenuConfigController.php

# Ver errores
tail -f var/log/dev.log
```

---

## 📊 PROGRESO ESPERADO

```
Hora 0:00 → Inicio
          ↓
Hora 0:45 → ✅ PARTE 1 completada (Backend funcionando)
          ↓
Hora 1:15 → ✅ PARTE 2 completada (Tree View visible)
          ↓
Hora 2:15 → ✅ PARTE 3 completada (Drag & drop funcionando)
          ↓
Hora 3:00 → ✅ PARTE 4 completada (Todo testeado)
          ↓
          🎉 ¡TERMINADO!
```

---

## 🔍 VERIFICACIONES RÁPIDAS

Después de cada parte:

### ✅ PARTE 1 - Backend

```bash
php bin/console debug:router | grep admin_menu_config | wc -l
# Resultado esperado: 9 (6 existentes + 3 nuevas)
```

### ✅ PARTE 2 - Frontend

```
Abrir: http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config
Verificar: ¿Se ve el árbol jerárquico? → SÍ ✅
```

### ✅ PARTE 3 - JavaScript

```
En el navegador:
1. Abrir consola (F12)
2. Escribir: typeof Sortable
3. Debe mostrar: "function" ✅
4. Arrastra un item → ¿Se mueve? → SÍ ✅
```

### ✅ PARTE 4 - Testing

```
Ejecutar cada test de la lista
Marcar los que pasan
Al final: ¿Todos pasan? → SÍ ✅
```

---

## 🆘 SI ALGO SALE MAL

### OPCIÓN 1: Restaurar Backup

```bash
# Restaurar controller
cp src/Controller/Admin/MenuConfigController.php.backup \
   src/Controller/Admin/MenuConfigController.php

# Restaurar template
cp templates/admin/menu_config/index.html.twig.backup \
   templates/admin/menu_config/index.html.twig

# Limpiar cache
php bin/console cache:clear

# Empezar de nuevo
```

### OPCIÓN 2: Ver Logs

```bash
# Errores de PHP
tail -f var/log/dev.log | grep ERROR

# Errores de Symfony
php bin/console server:log

# Errores de BD
tail -f /var/log/postgresql/postgresql-*.log
```

### OPCIÓN 3: Debugging Paso a Paso

```javascript
// En JavaScript, agregar console.logs:
console.log('Iniciando drag & drop');
console.log('Item ID:', itemId);
console.log('Parent ID:', parentId);
```

---

## 📞 CHECKLIST ANTES DE EMPEZAR

- [ ] Leí este Quick Start completo
- [ ] Hice backups de los archivos
- [ ] Verifiqué que Symfony funciona
- [ ] Verifiqué que la BD tiene 53 items
- [ ] Tengo GitHub Copilot activo en VS Code
- [ ] Tengo 2-3 horas disponibles
- [ ] Abrí los 5 archivos de guías en VS Code

**¿Todos marcados?** → ✅ **¡EMPEZAR!**

---

## 🎯 PRIMER PASO

**Ir a:**
👉 `docs/COPILOT_PROMPTS_PART_1_BACKEND.md`

**Y ejecutar el PROMPT 1**

---

## 🎉 AL TERMINAR

Tendrás una interfaz profesional con:

✅ Drag & Drop fluido
✅ 4 niveles de jerarquía visibles
✅ Búsqueda en tiempo real
✅ Reorganización automática
✅ Sin recargar la página
✅ Validaciones robustas

**Tiempo invertido**: 2-3 horas
**Resultado**: Interfaz de administración profesional 🚀

---

**¿Listo?** 👉 **Empezar con PARTE 1** 🚀
