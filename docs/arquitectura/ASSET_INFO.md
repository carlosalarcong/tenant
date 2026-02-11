# ⚠️ IMPORTANTE: Asset Management

## 🎯 **Este proyecto NO usa npm/webpack**

### ✅ **Qué SÍ usar:**
```bash
# Compilar assets con Asset Mapper
php bin/console asset-map:compile

# Verificar mapeo de assets
php bin/console debug:asset-map

# Limpiar caché de assets
php bin/console cache:clear
```

### ❌ **Qué NO hacer:**
```bash
# ESTOS COMANDOS FALLARÁN:
npm install          # ❌ No hay package.json
npm run build        # ❌ No hay scripts npm
yarn install         # ❌ No hay yarn.lock
webpack              # ❌ No usa webpack
```

## 🔧 **Arquitectura de Assets**

**Symfony 6.4+ Asset Mapper:**
- ✅ Stimulus ya está configurado en `assets/controllers/`
- ✅ CSS en `assets/styles/`
- ✅ JavaScript moderno sin build step
- ✅ Import maps automáticos

**Archivos clave:**
- `importmap.php` - Configuración de imports
- `assets/app.js` - Entry point principal
- `assets/controllers.json` - Controllers Stimulus

## 🚀 **Para tus compañeros:**

**Si ven errores de npm/yarn:**
1. **IGNORAR** - Es normal, no se necesita
2. **Usar solo:** `php bin/console asset-map:compile`
3. **Los assets ya están listos** para desarrollo