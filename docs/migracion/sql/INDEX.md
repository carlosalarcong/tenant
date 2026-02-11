# Índice de Archivos - Scripts SQL de Menús

## 📂 Directorio
`/var/www/html/melisa_tenant/docs/migracion/sql/`

## 📋 Lista de Archivos

### 🎯 Inicio Rápido
1. **[QUICK_START.md](QUICK_START.md)** (3K)
   - Guía rápida de 3 pasos
   - Comandos listos para ejecutar
   - Comenzar aquí

### 📊 Resumen Ejecutivo
2. **[RESUMEN_EJECUTIVO.md](RESUMEN_EJECUTIVO.md)** (8.3K)
   - Resumen para management
   - Estadísticas y métricas
   - Vista general del proyecto

### 📚 Documentación Completa
3. **[README.md](README.md)** (11K)
   - Documentación detallada
   - Instrucciones paso a paso
   - Troubleshooting completo
   - Casos de uso

4. **[MENU_STRUCTURE.md](MENU_STRUCTURE.md)** (12K)
   - Estructura jerárquica visual
   - Tablas detalladas por categoría
   - Convenciones de nomenclatura
   - Iconografía completa

### 🔧 Scripts SQL
5. **[00_install_all_menus.sql](00_install_all_menus.sql)** (6.9K)
   - **SCRIPT MAESTRO**
   - Ejecuta los 4 scripts siguientes
   - Muestra resumen al final

6. **[06_menu_hospitalaria.sql](06_menu_hospitalaria.sql)** (14K)
   - Categoría: Hospitalaria
   - 24 mantenedores
   - Atención, nutrición, prescripciones

7. **[07_menu_liquidaciones.sql](07_menu_liquidaciones.sql)** (8.2K)
   - Categoría: Liquidaciones
   - 5 mantenedores
   - Cuentas, UF, participación profesional

8. **[08_menu_presupuesto.sql](08_menu_presupuesto.sql)** (7.8K)
   - Categoría: Presupuesto
   - 3 mantenedores
   - Pies de presupuesto por financiador

9. **[09_menu_taller.sql](09_menu_taller.sql)** (8.2K)
   - Categoría: Taller
   - 1 mantenedor
   - Gestión de talleres grupales

### 🛠 Herramientas
10. **[install_all_menus.sh](install_all_menus.sh)** (6.4K)
    - Script bash automático
    - Validaciones de prerequisitos
    - Instalación completa
    - Limpieza de caché

### 📄 Este Archivo
11. **INDEX.md** (este archivo)
    - Índice navegable
    - Guía de archivos

## 🚀 Flujo de Trabajo Recomendado

### Para Usuarios Nuevos
```
1. Leer QUICK_START.md          (2 minutos)
2. Ejecutar install_all_menus.sh (1 minuto)
3. Verificar instalación         (2 minutos)
```

### Para Administradores
```
1. Leer RESUMEN_EJECUTIVO.md    (5 minutos)
2. Revisar MENU_STRUCTURE.md    (5 minutos)
3. Ejecutar instalación          (5 minutos)
4. Leer README.md si hay problemas
```

### Para Desarrolladores
```
1. Leer README.md completo       (15 minutos)
2. Revisar scripts SQL           (15 minutos)
3. Entender MENU_STRUCTURE.md    (10 minutos)
4. Ejecutar y validar            (10 minutos)
```

## 📁 Organización por Propósito

### Empezar Rápido
- `QUICK_START.md` - 3 pasos para instalar

### Entender el Proyecto
- `RESUMEN_EJECUTIVO.md` - Vista 30,000 pies
- `MENU_STRUCTURE.md` - Estructura detallada
- `README.md` - Guía completa

### Ejecutar Instalación
- `install_all_menus.sh` - Automático (recomendado)
- `00_install_all_menus.sql` - SQL maestro
- `06_menu_hospitalaria.sql` - Individual
- `07_menu_liquidaciones.sql` - Individual
- `08_menu_presupuesto.sql` - Individual
- `09_menu_taller.sql` - Individual

## 🎯 Por Rol

### Project Manager
```
RESUMEN_EJECUTIVO.md → Métricas y estadísticas
```

### DBA/SysAdmin
```
README.md → install_all_menus.sh → Verificar
```

### Developer
```
README.md → MENU_STRUCTURE.md → Scripts SQL individuales
```

### QA/Tester
```
QUICK_START.md → MENU_STRUCTURE.md → Verificación
```

## 📊 Contenido por Archivo

| Archivo | Propósito | Audiencia | Tiempo Lectura |
|---------|-----------|-----------|----------------|
| QUICK_START.md | Inicio rápido | Todos | 2 min |
| RESUMEN_EJECUTIVO.md | Overview ejecutivo | Managers | 5 min |
| README.md | Documentación completa | Técnicos | 15 min |
| MENU_STRUCTURE.md | Estructura detallada | Developers | 10 min |
| 00_install_all_menus.sql | Instalador maestro | DBAs | - |
| 06-09_menu_*.sql | Scripts individuales | DBAs | - |
| install_all_menus.sh | Instalador automático | SysAdmins | - |
| INDEX.md | Este índice | Todos | 3 min |

## 🔍 Buscar Información

### ¿Cómo instalar?
→ `QUICK_START.md` o `install_all_menus.sh`

### ¿Qué mantenedores se instalan?
→ `MENU_STRUCTURE.md` o `RESUMEN_EJECUTIVO.md`

### ¿Cómo funciona internamente?
→ `README.md` o Scripts SQL

### ¿Hay problemas?
→ `README.md` (sección Troubleshooting)

### ¿Necesito estadísticas?
→ `RESUMEN_EJECUTIVO.md`

### ¿Cuál es la estructura final?
→ `MENU_STRUCTURE.md`

### ¿Cómo deshacer (rollback)?
→ Scripts SQL individuales (sección comentada)

## 📝 Notas

- Todos los scripts son idempotentes
- La documentación está en español
- Los scripts incluyen validaciones
- Hay opciones para todos los niveles técnicos

## 🔗 Enlaces Relacionados

### Documentación de Migración
- `/docs/migracion/hospitalaria/PLAN_MIGRACION_HOSPITALARIA.md`
- `/docs/migracion/liquidaciones/PLAN_MIGRACION_LIQUIDACIONES.md`
- `/docs/migracion/presupuesto/PLAN_MIGRACION_PRESUPUESTO.md`
- `/docs/migracion/taller/PLAN_MIGRACION_TALLER.md`

### Documentación General
- `/docs/migracion/GUIA_MAESTRA_MIGRACION.md`
- `/docs/migracion/ESTADO_MIGRACION_CATEGORIAS.md`

## ✅ Checklist de Uso

### Primera Vez
- [ ] Leer QUICK_START.md
- [ ] Verificar prerequisitos
- [ ] Ejecutar install_all_menus.sh
- [ ] Limpiar caché Symfony
- [ ] Verificar en web

### Troubleshooting
- [ ] Consultar README.md
- [ ] Revisar logs de instalación
- [ ] Verificar conexión a BD
- [ ] Comprobar prerequisitos

### Validación
- [ ] Verificar 33 mantenedores insertados
- [ ] Comprobar rutas con debug:router
- [ ] Probar acceso en interfaz web
- [ ] Validar permisos de usuario

## 📞 Soporte

Para dudas o problemas:
1. Consultar README.md (Troubleshooting)
2. Revisar MENU_STRUCTURE.md (Estructura)
3. Verificar RESUMEN_EJECUTIVO.md (Overview)

---

**Última actualización:** 2026-02-04
**Total de archivos:** 11
**Total de páginas de documentación:** 4
**Total de scripts SQL:** 5
**Total de herramientas:** 1
