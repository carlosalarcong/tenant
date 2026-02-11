# 🔐 Sistema de Permisos y Roles - Dashboard

## 📋 Roles Implementados

### **Jerarquía de Roles:**
```
ROLE_ADMIN          → Acceso Total (100 puntos)
ROLE_ACCOUNTANT     → Finanzas y Reportes (50 puntos)
ROLE_DOCTOR         → Módulos Clínicos (40 puntos)
ROLE_NURSE          → Apoyo Clínico (30 puntos)
ROLE_RECEPTIONIST   → Operaciones Básicas (20 puntos)
ROLE_USER           → Acceso Limitado (10 puntos)
```

---

## 🎯 Permisos por Rol

### **👑 ROLE_ADMIN (Administrador)**
**Ve TODO el sistema**

✅ **Módulos:**
- Administración de Usuarios
- Directorio de Pacientes
- Agenda
- Registro Clínico Electrónico
- Caja/Facturación
- Informes y Reportes
- Mantenedores
- Configuraciones
- Farmacia
- Laboratorio

✅ **Métricas:**
- Usuarios activos
- Citas del día
- Ingresos
- Todas las estadísticas

✅ **Acciones Rápidas:**
- Nuevo Usuario
- Nuevo Paciente
- Ver Reportes
- Configuración

✅ **Alertas:**
- Licencias por agotarse
- Notificaciones del sistema
- Alertas críticas

---

### **👨‍⚕️ ROLE_DOCTOR (Médico)**
**Enfoque clínico + agenda personal**

✅ **Módulos:**
- Directorio de Pacientes
- Agenda (solo su agenda)
- Registro Clínico Electrónico
- Reportes (solo sus pacientes)
- Farmacia
- Laboratorio

❌ **NO ve:**
- Administración de Usuarios
- Caja/Facturación
- Mantenedores
- Configuraciones

✅ **Métricas:**
- Citas del día
- Pacientes atendidos

❌ **NO ve métricas de:**
- Usuarios del sistema
- Ingresos totales

✅ **Acciones Rápidas:**
- Nueva Consulta
- Mi Agenda
- Buscar Paciente
- Resultados Pendientes

---

### **👥 ROLE_RECEPTIONIST (Recepcionista)**
**Operaciones front-desk**

✅ **Módulos:**
- Directorio de Pacientes
- Agenda (todas las agendas)
- Caja/Facturación

❌ **NO ve:**
- Administración de Usuarios
- Registro Clínico Electrónico
- Reportes avanzados
- Mantenedores
- Configuraciones

✅ **Métricas:**
- Citas del día
- Pacientes registrados

❌ **NO ve métricas de:**
- Usuarios del sistema
- Ingresos detallados

✅ **Acciones Rápidas:**
- Agendar Cita
- Nuevo Paciente
- Buscar Paciente
- Caja

---

### **💰 ROLE_ACCOUNTANT (Contador)**
**Finanzas y reportes**

✅ **Módulos:**
- Caja/Facturación
- Informes y Reportes

❌ **NO ve:**
- Administración de Usuarios
- Directorio de Pacientes
- Agenda
- Registro Clínico
- Mantenedores

✅ **Métricas:**
- Ingresos totales
- Estadísticas financieras

❌ **NO ve métricas de:**
- Usuarios del sistema
- Citas médicas

✅ **Acciones Rápidas:**
- Reporte Diario
- Pagos Pendientes
- Facturación
- Gastos

---

### **👩‍⚕️ ROLE_NURSE (Enfermera)**
**Apoyo clínico**

✅ **Módulos:**
- Directorio de Pacientes
- Registro Clínico Electrónico (lectura + signos vitales)
- Farmacia
- Laboratorio

❌ **NO ve:**
- Administración de Usuarios
- Agenda (solo consulta)
- Caja/Facturación
- Reportes
- Mantenedores
- Configuraciones

✅ **Métricas:**
- Pacientes del día

✅ **Acciones Rápidas:**
- Tomar Signos Vitales
- Lista de Citas
- Medicación
- Órdenes de Laboratorio

---

## 🔧 Implementación Técnica

### **1. Servicio de Permisos**
`src/Service/Dashboard/DashboardPermissionService.php`

```php
// Verificar acceso a módulo
$canAccess = $permissionService->canAccessModule('admin_users', $userRoles);

// Verificar acceso a métrica
$canView = $permissionService->canViewMetric('revenue', $userRoles);

// Obtener módulos accesibles
$modules = $permissionService->getAccessibleModules($userRoles);

// Obtener rol principal
$primaryRole = $permissionService->getPrimaryRole($userRoles);

// Acciones rápidas según rol
$actions = $permissionService->getQuickActionsByRole($userRoles);
```

### **2. Controlador Dashboard**
`src/Controller/Dashboard/Default/DefaultController.php`

```php
// Obtener roles del usuario desde sesión
$userRoles = $session->get('user_roles', ['ROLE_USER']);

// Filtrar métricas y módulos según permisos
$metrics = $this->metricsService->getDashboardMetrics($tenant, $userRoles);
$modules = $this->metricsService->getAvailableModules($userRoles);
```

### **3. Template Twig**
`templates/dashboard/index.html.twig`

```twig
{# Mostrar badge según rol #}
{% if 'ROLE_ADMIN' in user_roles %}
    <span class="badge bg-danger">
        <i class="fas fa-crown me-1"></i>Administrador
    </span>
{% elseif 'ROLE_DOCTOR' in user_roles %}
    <span class="badge bg-primary">
        <i class="fas fa-user-md me-1"></i>Médico
    </span>
{% endif %}

{# Solo mostrar métrica si existe (filtrada por permisos) #}
{% if metrics.users is defined %}
    <!-- Mostrar métrica de usuarios -->
{% endif %}
```

---

## 🔄 Flujo de Permisos

```
1. Usuario hace login
   ↓
2. Sistema guarda roles en sesión (user_roles)
   ↓
3. Usuario accede a /dashboard
   ↓
4. Controller obtiene roles de sesión
   ↓
5. DashboardMetricsService filtra métricas
   ↓
6. DashboardPermissionService filtra módulos
   ↓
7. Template muestra solo lo permitido
```

---

## 📝 Cómo Agregar un Nuevo Rol

### **Paso 1: Definir el Rol**
En `DashboardPermissionService.php`:
```php
public const ROLE_MI_NUEVO_ROL = 'ROLE_MI_NUEVO_ROL';
```

### **Paso 2: Asignar Permisos de Módulos**
```php
private array $modulePermissions = [
    'patients' => [
        self::ROLE_ADMIN,
        self::ROLE_MI_NUEVO_ROL, // ← Agregar aquí
    ],
    // ...
];
```

### **Paso 3: Asignar Permisos de Métricas**
```php
private array $metricsPermissions = [
    'appointments' => [
        self::ROLE_ADMIN,
        self::ROLE_MI_NUEVO_ROL, // ← Agregar aquí
    ],
];
```

### **Paso 4: Configurar Acciones Rápidas**
```php
$actionsByRole = [
    self::ROLE_MI_NUEVO_ROL => [
        ['id' => 'action1', 'label' => 'Mi Acción', 'icon' => 'fa-icon', 'color' => 'primary'],
    ],
];
```

### **Paso 5: Actualizar Jerarquía**
```php
$hierarchy = [
    self::ROLE_MI_NUEVO_ROL => 45, // Entre ROLE_DOCTOR (40) y ROLE_ACCOUNTANT (50)
];
```

---

## ⚠️ Consideraciones de Seguridad

### **Backend (Recomendado)**
```php
// ✅ CORRECTO: Verificar en controlador
if (!$this->permissionService->canAccessModule('admin_users', $userRoles)) {
    throw $this->createAccessDeniedException();
}
```

### **Frontend (UI solamente)**
```twig
{# ⚠️ ADVERTENCIA: Esto solo oculta en UI, NO es seguridad #}
{% if 'ROLE_ADMIN' in user_roles %}
    <a href="/admin/users">Admin</a>
{% endif %}
```

### **Voters de Symfony (Próximo paso)**
Para seguridad real, implementar Voters:
```php
// src/Security/Voter/ModuleVoter.php
if (!$this->security->isGranted('VIEW', $module)) {
    throw new AccessDeniedException();
}
```

---

## 📊 Dashboard por Rol - Ejemplos

### **Admin ve:**
```
✅ 4 Métricas (Usuarios, Citas, Ingresos, Semanal)
✅ 10 Módulos (todos)
✅ Alertas de licencias
✅ Acciones: Usuario, Paciente, Reportes, Config
```

### **Médico ve:**
```
✅ 2 Métricas (Citas, Pacientes)
✅ 6 Módulos (Pacientes, Agenda, RCE, Farmacia, Lab, Reportes)
❌ NO ve métricas de usuarios ni ingresos
❌ NO ve módulos de Admin, Caja, Mantenedores
✅ Acciones: Consulta, Mi Agenda, Buscar, Labs
```

### **Recepcionista ve:**
```
✅ 2 Métricas (Citas, Pacientes)
✅ 3 Módulos (Pacientes, Agenda, Caja)
❌ NO ve métricas de usuarios ni ingresos
❌ NO ve módulos clínicos
✅ Acciones: Agendar, Nuevo Paciente, Buscar, Caja
```

---

## 🧪 Testing

### **Probar diferentes roles:**
```php
// Simular rol en sesión
$session->set('user_roles', ['ROLE_DOCTOR']);

// Verificar permisos
$canAccess = $permissionService->canAccessModule('admin_users', ['ROLE_DOCTOR']);
// Resultado: false

$canAccess = $permissionService->canAccessModule('patients', ['ROLE_DOCTOR']);
// Resultado: true
```

### **En desarrollo:**
Para probar roles fácilmente, puedes crear un endpoint temporal:
```php
#[Route('/dev/set-role/{role}', name: 'dev_set_role')]
public function setRole(Request $request, string $role): Response
{
    $request->getSession()->set('user_roles', [$role]);
    return $this->redirectToRoute('app_dashboard_default');
}
```

---

## 📈 Próximas Mejoras

1. ✅ **Implementado**: Sistema básico de permisos
2. 🔄 **En progreso**: Filtrado de métricas por rol
3. ⏳ **Pendiente**: Voters de Symfony para seguridad backend
4. ⏳ **Pendiente**: Permisos granulares por usuario (no solo por rol)
5. ⏳ **Pendiente**: Log de accesos por rol
6. ⏳ **Pendiente**: Personalización de dashboard por usuario

---

**Creado**: 14 de Enero, 2026  
**Versión**: 1.0.0  
**Estado**: ✅ Funcional
