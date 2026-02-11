# Sistema de Permisos Multi-Tenant

Sistema híbrido de permisos que combina perfiles predefinidos con configuración personalizada por tenant en base de datos.

## 📋 Tabla de Contenidos
- [Arquitectura](#arquitectura)
- [Base de Datos](#base-de-datos)
- [Componentes](#componentes)
- [Flujo de Ejecución](#flujo-de-ejecución)
- [Estrategias de Permisos](#estrategias-de-permisos)
- [Uso y Configuración](#uso-y-configuración)
- [Ejemplos](#ejemplos)

---

## Arquitectura

El sistema utiliza el **patrón Strategy** para determinar qué items del menú son visibles según:
1. **Perfil del tenant** (collaborative, restrictive, custom)
2. **Roles del usuario** (ROLE_ADMIN, ROLE_DOCTOR, ROLE_SECRETARY, etc.)
3. **Configuración en BD** (para perfil custom)

### Componentes Principales

```
src/
├── Entity/Tenant/
│   ├── TenantPermissionProfile.php          # Perfil del tenant
│   └── TenantModulePermissionOverride.php   # Overrides personalizados
├── Repository/Tenant/
│   ├── TenantPermissionProfileRepository.php
│   └── TenantModulePermissionOverrideRepository.php
├── Service/Menu/
│   ├── PermissionStrategyInterface.php      # Interfaz de estrategias
│   ├── CollaborativePermissionStrategy.php  # Estrategia colaborativa
│   ├── RestrictivePermissionStrategy.php    # Estrategia restrictiva
│   ├── CustomPermissionStrategy.php         # Estrategia personalizada
│   ├── PermissionStrategyFactory.php        # Factory de estrategias
│   ├── MenuItem.php                         # Modelo de item del menú
│   └── NavbarBuilder.php                    # Constructor del menú
├── Command/
│   └── TenantPermissionProfileCommand.php   # CLI para gestionar perfiles
└── Controller/Dashboard/Default/
    └── DefaultController.php                # Inyecta NavbarBuilder

migrations/Tenant/
└── Version20260121124317.php                # Migración de tablas
```

---

## Base de Datos

### Tabla: `tenant_permission_profile`

Almacena el tipo de perfil de permisos del tenant.

```sql
CREATE TABLE tenant_permission_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_type VARCHAR(50) NOT NULL,  -- 'collaborative', 'restrictive', 'custom'
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL
);
```

**Valores de `profile_type`:**
- `collaborative`: Múltiples roles pueden acceder (clínicas grandes)
- `restrictive`: Solo ROLE_ADMIN accede a mayoría de módulos (clínicas pequeñas)
- `custom`: Configuración personalizada desde BD

### Tabla: `tenant_module_permission_override`

Define permisos personalizados por módulo (usado con perfil `custom`).

```sql
CREATE TABLE tenant_module_permission_override (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_name VARCHAR(100) NOT NULL,
    required_roles JSON NOT NULL,           -- ["ROLE_ADMIN", "ROLE_DOCTOR"]
    description VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME DEFAULT NULL,
    UNIQUE KEY unique_module_permission (module_name),
    INDEX idx_module_name (module_name)
);
```

**Ejemplo de datos:**
```sql
INSERT INTO tenant_module_permission_override 
    (module_name, required_roles, description, is_active, created_at)
VALUES 
    ('patients', '["ROLE_ADMIN", "ROLE_DOCTOR", "ROLE_SECRETARY"]', 'Acceso a gestión de pacientes', 1, NOW()),
    ('appointments', '["ROLE_ADMIN", "ROLE_SECRETARY"]', 'Gestión de citas médicas', 1, NOW()),
    ('reports', '["ROLE_ADMIN"]', 'Solo administradores pueden ver reportes', 1, NOW());
```

---

## Componentes

### 1. NavbarBuilder

Servicio principal que construye el menú filtrado por permisos.

**Responsabilidades:**
- Obtener la estrategia de permisos según perfil del tenant
- Definir estructura completa del menú
- Filtrar items según permisos del usuario
- Retornar array de items visibles

**Uso en Controller:**
```php
public function __construct(
    private NavbarBuilder $navbarBuilder
) {}

public function index(Request $request): Response
{
    $userRoles = ['ROLE_DOCTOR'];
    $menuItems = $this->navbarBuilder->buildMenu($userRoles);
    
    return $this->render('dashboard/index.html.twig', [
        'menu_items' => $menuItems,
    ]);
}
```

### 2. PermissionStrategyFactory

Factory que crea la estrategia apropiada según el perfil del tenant.

**Lógica:**
```php
public function createStrategy(): PermissionStrategyInterface
{
    $profile = $this->profileRepository->getCurrentProfile();
    
    return match ($profile->getProfileType()) {
        'collaborative' => $this->collaborativeStrategy,
        'restrictive' => $this->restrictiveStrategy,
        'custom' => $this->customStrategy,
        default => $this->collaborativeStrategy,
    };
}
```

### 3. Estrategias de Permisos

#### CollaborativePermissionStrategy

Configuración para clínicas grandes con múltiples roles.

**Permisos por defecto:**
- `dashboard`: ADMIN, DOCTOR, SECRETARY, USER
- `patients`: ADMIN, DOCTOR, SECRETARY
- `appointments`: ADMIN, DOCTOR, SECRETARY
- `medical_records`: ADMIN, DOCTOR
- `reports`: ADMIN, DOCTOR
- `billing`: ADMIN, SECRETARY
- `settings`: ADMIN
- `maintenance_*`: ADMIN

#### RestrictivePermissionStrategy

Configuración para clínicas pequeñas, solo ADMIN tiene acceso.

**Permisos por defecto:**
- `dashboard`: ADMIN, USER (solo lectura)
- Todo lo demás: ADMIN

#### CustomPermissionStrategy

Lee configuración desde `tenant_module_permission_override`.

**Lógica:**
```php
public function canAccess(string $moduleName, array $userRoles): bool
{
    $requiredRoles = $this->overrideRepository->getRequiredRolesForModule($moduleName);
    
    if (empty($requiredRoles)) {
        return in_array('ROLE_ADMIN', $userRoles);
    }
    
    return !empty(array_intersect($userRoles, $requiredRoles));
}
```

---

## Flujo de Ejecución

### Request Completo: Usuario ROLE_DOCTOR accede al Dashboard

```
1. HTTP Request
   GET http://melisalacolina.com/dashboard
   
2. DefaultController::index()
   - Obtiene roles de sesión: ['ROLE_DOCTOR']
   - Llama: $navbarBuilder->buildMenu(['ROLE_DOCTOR'])
   
3. NavbarBuilder::buildMenu()
   ├─ Paso 1: Obtener estrategia
   │  └─ PermissionStrategyFactory::createStrategy()
   │     └─ Consulta: tenant_permission_profile
   │        └─ profile_type = 'collaborative'
   │           └─ Retorna: CollaborativePermissionStrategy
   │
   ├─ Paso 2: Definir menú completo
   │  └─ getFullMenuStructure()
   │     └─ [Dashboard, Patients, Appointments, Mantenedores, Reports, Settings]
   │
   ├─ Paso 3: Filtrar items
   │  └─ filterMenuItems($fullMenu, ['ROLE_DOCTOR'], $strategy)
   │     │
   │     ├─ Dashboard
   │     │  └─ strategy->canAccess('dashboard', ['ROLE_DOCTOR'])
   │     │     └─ Busca en defaults: ['ROLE_ADMIN', 'ROLE_DOCTOR', ...]
   │     │        └─ ✅ VISIBLE
   │     │
   │     ├─ Patients
   │     │  └─ strategy->canAccess('patients', ['ROLE_DOCTOR'])
   │     │     └─ Busca en defaults: ['ROLE_ADMIN', 'ROLE_DOCTOR', 'ROLE_SECRETARY']
   │     │        └─ ✅ VISIBLE
   │     │
   │     ├─ Appointments
   │     │  └─ ✅ VISIBLE (misma lógica)
   │     │
   │     ├─ Mantenedores (tiene hijos)
   │     │  └─ Filtrar recursivamente cada hijo
   │     │     ├─ País: ['ROLE_ADMIN'] → ❌ OCULTO
   │     │     ├─ Región: ['ROLE_ADMIN'] → ❌ OCULTO
   │     │     └─ Todos ocultos → ❌ OCULTA PADRE
   │     │
   │     ├─ Reports
   │     │  └─ strategy->canAccess('reports', ['ROLE_DOCTOR'])
   │     │     └─ Busca en defaults: ['ROLE_ADMIN', 'ROLE_DOCTOR']
   │     │        └─ ✅ VISIBLE
   │     │
   │     └─ Settings
   │        └─ strategy->canAccess('settings', ['ROLE_DOCTOR'])
   │           └─ Busca en defaults: ['ROLE_ADMIN']
   │              └─ ❌ OCULTO
   │
   └─ Paso 4: Retornar array filtrado
      └─ [Dashboard, Patients, Appointments, Reports]

4. Controller retorna response
   return $this->render('dashboard/index.html.twig', [
       'menu_items' => [Dashboard, Patients, Appointments, Reports]
   ]);

5. Template renderiza sidebar
   templates/partials/_sidebar.html.twig
   {% for item in menu_items %}
       <a href="{{ path(item.route) }}">{{ item.label }}</a>
   {% endfor %}
   
6. Usuario ve en pantalla:
   ✅ Dashboard
   ✅ Patients
   ✅ Appointments
   ✅ Reports
   ❌ Mantenedores (oculto)
   ❌ Settings (oculto)
```

---

## Estrategias de Permisos

### Comparación de Estrategias

| Módulo | Collaborative | Restrictive | Custom (ejemplo) |
|--------|--------------|-------------|------------------|
| Dashboard | ADMIN, DOCTOR, SECRETARY, USER | ADMIN, USER | Configurable |
| Patients | ADMIN, DOCTOR, SECRETARY | ADMIN | ["ROLE_ADMIN", "ROLE_DOCTOR"] |
| Appointments | ADMIN, DOCTOR, SECRETARY | ADMIN | ["ROLE_ADMIN", "ROLE_SECRETARY"] |
| Reports | ADMIN, DOCTOR | ADMIN | ["ROLE_ADMIN"] |
| Settings | ADMIN | ADMIN | ["ROLE_ADMIN"] |

### Caso de Uso: Clínica Grande (Wiclinic)

**Perfil: `collaborative`**
- Múltiples doctores necesitan acceso a pacientes
- Secretarias gestionan citas
- Administradores controlan todo

**Resultado:**
- ROLE_DOCTOR → Ve: Dashboard, Patients, Appointments, Reports
- ROLE_SECRETARY → Ve: Dashboard, Patients, Appointments, Billing
- ROLE_ADMIN → Ve: Todo

### Caso de Uso: Clínica Pequeña (La Colina)

**Perfil: `restrictive`**
- Solo el dueño (admin) gestiona todo
- Usuarios básicos solo ven dashboard

**Resultado:**
- ROLE_ADMIN → Ve: Todo
- ROLE_USER → Ve: Dashboard (solo lectura)

### Caso de Uso: Configuración Personalizada

**Perfil: `custom`**
- Wiclinic: Patients requiere ADMIN o DOCTOR
- La Colina: Patients requiere solo ADMIN

**Configuración en BD:**

```sql
-- Wiclinic (tenant_id=4)
INSERT INTO wiclinic.tenant_module_permission_override 
    (module_name, required_roles, is_active, created_at)
VALUES 
    ('patients', '["ROLE_ADMIN", "ROLE_DOCTOR"]', 1, NOW());

-- La Colina (tenant_id=5)
INSERT INTO melisalacolina.tenant_module_permission_override 
    (module_name, required_roles, is_active, created_at)
VALUES 
    ('patients', '["ROLE_ADMIN"]', 1, NOW());
```

**Resultado:**
- Wiclinic: ROLE_DOCTOR puede ver Patients
- La Colina: ROLE_DOCTOR NO puede ver Patients

---

## Uso y Configuración

### Ver Configuración Actual

```bash
php bin/console app:tenant:permission-profile melisalacolina show
```

**Salida:**
```
Configuración de Permisos del Tenant: melisalacolina
====================================================

Perfil Actual
-------------
 Campo            Valor                
 Tipo de Perfil   collaborative        
 Creado           2026-01-21 09:48:04  
 Actualizado      N/A                  

 ✓ Múltiples roles pueden acceder a diferentes módulos (clínicas grandes)
```

### Cambiar Perfil

```bash
# Cambiar a collaborative
php bin/console app:tenant:permission-profile melisalacolina set collaborative

# Cambiar a restrictive
php bin/console app:tenant:permission-profile melisalacolina set restrictive

# Cambiar a custom
php bin/console app:tenant:permission-profile melisalacolina set custom
```

### Configurar Overrides (Custom)

```sql
-- Ver overrides actuales
SELECT * FROM melisalacolina.tenant_module_permission_override WHERE is_active = 1;

-- Agregar override
INSERT INTO melisalacolina.tenant_module_permission_override 
    (module_name, required_roles, description, is_active, created_at)
VALUES 
    ('medical_records', '["ROLE_ADMIN", "ROLE_DOCTOR"]', 'Solo médicos y admin', 1, NOW());

-- Actualizar override
UPDATE melisalacolina.tenant_module_permission_override 
SET required_roles = '["ROLE_ADMIN"]', updated_at = NOW()
WHERE module_name = 'medical_records';

-- Desactivar override
UPDATE melisalacolina.tenant_module_permission_override 
SET is_active = 0, updated_at = NOW()
WHERE module_name = 'medical_records';
```

---

## Ejemplos

### Ejemplo 1: Agregar Nuevo Módulo al Menú

**1. Agregar item en NavbarBuilder:**

```php
// src/Service/Menu/NavbarBuilder.php
private function getFullMenuStructure(): array
{
    return [
        // ... items existentes
        new MenuItem(
            name: 'pharmacy',
            label: 'Farmacia',
            route: 'app_pharmacy',
            icon: 'bx bx-capsule',
            module: 'pharmacy'
        ),
    ];
}
```

**2. Configurar permisos en estrategias:**

```php
// src/Service/Menu/CollaborativePermissionStrategy.php
private const DEFAULT_MODULE_PERMISSIONS = [
    // ... módulos existentes
    'pharmacy' => ['ROLE_ADMIN', 'ROLE_PHARMACIST'],
];
```

**3. (Opcional) Configurar override custom:**

```sql
INSERT INTO melisalacolina.tenant_module_permission_override 
    (module_name, required_roles, is_active, created_at)
VALUES 
    ('pharmacy', '["ROLE_ADMIN", "ROLE_PHARMACIST", "ROLE_DOCTOR"]', 1, NOW());
```

### Ejemplo 2: Usuario con Múltiples Roles

```php
$userRoles = ['ROLE_DOCTOR', 'ROLE_PHARMACIST'];
$menuItems = $navbarBuilder->buildMenu($userRoles);

// Si strategy->canAccess('pharmacy', $userRoles)
// Busca: ['ROLE_ADMIN', 'ROLE_PHARMACIST']
// Intersección: ['ROLE_PHARMACIST'] → ✅ VISIBLE
```

### Ejemplo 3: Testing de Estrategias

```php
// tests/Unit/Service/Menu/CollaborativePermissionStrategyTest.php
public function testDoctorCanAccessPatients(): void
{
    $strategy = new CollaborativePermissionStrategy();
    
    $result = $strategy->canAccess('patients', ['ROLE_DOCTOR']);
    
    $this->assertTrue($result);
}

public function testDoctorCannotAccessSettings(): void
{
    $strategy = new CollaborativePermissionStrategy();
    
    $result = $strategy->canAccess('settings', ['ROLE_DOCTOR']);
    
    $this->assertFalse($result);
}
```

---

## Ventajas del Sistema

✅ **Flexible**: Cambias perfil sin modificar código  
✅ **Escalable**: Agregar módulos solo requiere configurar BD  
✅ **Tenant-specific**: Cada tenant tiene su configuración independiente  
✅ **Zero downtime**: Cambios toman efecto inmediatamente  
✅ **Testeable**: Estrategias aisladas, fáciles de probar  
✅ **Mantenible**: Lógica centralizada en NavbarBuilder y estrategias  
✅ **Extensible**: Fácil agregar nuevas estrategias o módulos  

---

## Migración

**Ejecutar migración:**
```bash
# Generar migración
php bin/console tenant:migrations:diff 5

# Aplicar migración
php bin/console tenant:migrations:migrate migrate 5 --no-interaction
```

**Insertar datos iniciales:**
```sql
-- Perfil por defecto
INSERT INTO tenant_permission_profile (profile_type, created_at) 
VALUES ('collaborative', NOW());

-- Ejemplos de overrides
INSERT INTO tenant_module_permission_override (module_name, required_roles, description, is_active, created_at)
VALUES 
    ('patients', '["ROLE_ADMIN", "ROLE_DOCTOR", "ROLE_SECRETARY"]', 'Acceso a gestión de pacientes', 1, NOW()),
    ('appointments', '["ROLE_ADMIN", "ROLE_SECRETARY"]', 'Gestión de citas médicas', 1, NOW()),
    ('reports', '["ROLE_ADMIN"]', 'Solo administradores pueden ver reportes', 1, NOW());
```

---

## Troubleshooting

### Problema: Menú no se filtra correctamente

**Verificar:**
1. Perfil del tenant: `php bin/console app:tenant:permission-profile <db> show`
2. Roles del usuario en sesión: `$session->get('user_roles')`
3. Cache de Symfony: `php bin/console cache:clear`

### Problema: Todos los módulos ocultos

**Causa probable:** Usuario no tiene roles o perfil restrictive sin ROLE_ADMIN

**Solución:**
```php
// Verificar en controller
$userRoles = $session->get('user_roles', []);
if (empty($userRoles)) {
    $userRoles = ['ROLE_USER']; // Asignar rol por defecto
}
```

### Problema: Custom strategy no lee overrides

**Verificar BD:**
```sql
SELECT * FROM tenant_module_permission_override WHERE is_active = 1;
```

**Asegurar EntityManager usa BD correcta:**
- Verificar que el contexto del tenant esté activo
- Consultar directamente con nombre de BD: `melisalacolina.tenant_module_permission_override`

---

**Fecha de creación:** 2026-01-21  
**Última actualización:** 2026-01-21  
**Versión:** 1.0.0
