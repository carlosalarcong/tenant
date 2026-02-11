# 🤖 COPILOT PROMPT - PARTE 1: BACKEND (Controller y AJAX Endpoints)

## 📋 CONTEXTO DEL PROYECTO

**Sistema**: Aplicación Symfony 7.4 multi-tenant con PostgreSQL
**Objetivo**: Mejorar interfaz de administración de menús con Tree View + Drag & Drop
**Ubicación**: `/admin/menu-config`
**Entidad**: `App\Entity\Tenant\MenuItem`
**Controlador**: `src/Controller/Admin/MenuConfigController.php`

### Estado Actual del Sistema

```php
// Entity: MenuItem (src/Entity/Tenant/MenuItem.php)
// Campos principales:
- id (int)
- parent_id (MenuItem, nullable)
- name (string) - identificador único
- label (string) - texto visible
- route (string, nullable)
- icon (string, nullable)
- module (string, nullable)
- position (int) - orden
- enabled (boolean)
- visibleInSidebar (boolean)
- requiresAuth (boolean)
- requiredRoles (json)
- children (Collection<MenuItem>)

// Repository: MenuItemRepository
// Métodos existentes:
- getAllForAdmin(): array
- getMenuWithChildren(): array
- reorderAfterDelete(int $position, ?MenuItem $parent)
```

---

## 🎯 PARTE 1: MODIFICAR CONTROLLER

### PROMPT 1: Agregar Endpoint para Actualizar Jerarquía vía AJAX

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar un endpoint AJAX al controlador MenuConfigController.php ubicado en src/Controller/Admin/MenuConfigController.php

CONTEXTO:
- Es un sistema Symfony 7.4
- La entidad MenuItem tiene parent_id y position
- Necesito actualizar la jerarquía cuando el usuario hace drag & drop en el frontend
- El endpoint debe recibir JSON con: item_id, new_parent_id (puede ser null), new_position
- Debe validar que no se cree una referencia circular (un item no puede ser hijo de sí mismo ni de sus descendientes)
- Debe invalidar el cache del menú después de actualizar

REQUISITOS:
1. Ruta: #[Route('/{id}/update-hierarchy', name: 'update_hierarchy', methods: ['POST'])]
2. Recibir JSON: {parent_id: int|null, position: int}
3. Validar que el nuevo padre no sea el propio item ni un descendiente
4. Actualizar parent_id y position del MenuItem
5. Actualizar updated_at
6. Reordenar automáticamente los hermanos (items con mismo padre)
7. Invalidar cache con $this->menuDefinition->invalidateCache()
8. Retornar JsonResponse con {success: true} o {success: false, error: "mensaje"}
9. Usar try-catch para manejar errores

CÓDIGO BASE DEL CONTROLLER:
- Tiene: EntityManagerInterface $entityManager
- Tiene: MenuItemRepository $menuRepository
- Tiene: MenuDefinition $menuDefinition
- Tiene método privado: getTenantId(Request $request): string

Por favor genera el método updateHierarchy completo con todas las validaciones.
```

**Resultado esperado**: Método `updateHierarchy()` completo

---

### PROMPT 2: Agregar Endpoint para Obtener Árbol como JSON

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar un endpoint API al controlador MenuConfigController.php para obtener la estructura completa del menú como JSON.

CONTEXTO:
- Controlador: src/Controller/Admin/MenuConfigController.php
- Necesito esto para cargar el árbol completo en JavaScript
- La estructura debe ser jerárquica (padres con sus hijos anidados)

REQUISITOS:
1. Ruta: #[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
2. Obtener todos los items con $this->menuRepository->getMenuWithChildren()
3. Convertir a array con estructura:
   {
     id: int,
     name: string,
     label: string,
     route: string|null,
     icon: string|null,
     position: int,
     enabled: boolean,
     level: int (calculado: 1=raíz, 2=hijo de raíz, etc.),
     hasChildren: boolean,
     childrenCount: int,
     children: [...] (recursivo)
   }
4. Retornar JsonResponse con el array
5. Los hijos deben estar ordenados por position ASC
6. Usar método privado recursivo para serializar

ESTRUCTURA ESPERADA:
[
  {
    "id": 4,
    "name": "mantenedores",
    "label": "Mantenedores",
    "level": 1,
    "hasChildren": true,
    "childrenCount": 5,
    "children": [
      {
        "id": 27,
        "name": "maintenance_comercial",
        "label": "Comercial",
        "level": 2,
        "hasChildren": true,
        "childrenCount": 4,
        "children": [...]
      }
    ]
  }
]

Por favor genera el método getMenuTree() y el método privado serializeMenuItem() recursivo.
```

**Resultado esperado**: Método `getMenuTree()` y `serializeMenuItem()` completos

---

### PROMPT 3: Agregar Endpoint para Agregar Hijo Directamente

**Copiar y pegar este prompt en Copilot:**

```
Necesito agregar un endpoint AJAX para crear un hijo directo desde un item padre en el árbol.

CONTEXTO:
- Controlador: src/Controller/Admin/MenuConfigController.php
- Cuando el usuario hace click en el botón "➕ Agregar Hijo" en un item
- Se debe crear un nuevo MenuItem hijo con valores por defecto

REQUISITOS:
1. Ruta: #[Route('/{id}/add-child', name: 'add_child', methods: ['POST'])]
2. Parámetro {id} es el parent_id (el item padre)
3. Crear nuevo MenuItem con:
   - name: "nuevo_item_" + timestamp
   - label: "Nuevo Item"
   - parent: el MenuItem con id={id}
   - position: siguiente posición disponible (obtener con $this->menuRepository->getNextPosition($parent))
   - enabled: true
   - visibleInSidebar: true
   - requiresAuth: true
   - route, icon, module, requiredRoles: null
4. Persistir y flush
5. Invalidar cache
6. Retornar JsonResponse con:
   {
     success: true,
     item: {
       id: nuevo_id,
       name: "nuevo_item_...",
       label: "Nuevo Item",
       position: X
     }
   }

Por favor genera el método addChild() completo.
```

**Resultado esperado**: Método `addChild()` completo

---

### PROMPT 4: Agregar Método Helper para Validar Jerarquía Circular

**Copiar y pegar este prompt en Copilot:**

```
Necesito un método privado helper en MenuConfigController.php para validar que no se cree una referencia circular al cambiar el padre de un item.

CONTEXTO:
- Un item no puede ser su propio padre
- Un item no puede ser hijo de ninguno de sus descendientes (nietos, bisnietos, etc.)
- Por ejemplo: Si "Comercial" (id=27) tiene hijo "Tipos" (id=28), entonces "Comercial" NO puede ser hijo de "Tipos"

REQUISITOS:
1. Método: private function isCircularReference(MenuItem $item, ?MenuItem $newParent): bool
2. Retornar true si hay referencia circular, false si está OK
3. Validar:
   - Si $newParent es null, retornar false (OK, se está moviendo a raíz)
   - Si $newParent->getId() === $item->getId(), retornar true (ERROR: intentando ser padre de sí mismo)
   - Si $newParent es descendiente de $item, retornar true (ERROR: circular)
4. Para validar descendientes, recorrer recursivamente todos los hijos de $item
5. Usar método recursivo: private function isDescendantOf(MenuItem $item, MenuItem $ancestor): bool

EJEMPLO DE USO:
if ($this->isCircularReference($menuItem, $newParent)) {
    return new JsonResponse(['success' => false, 'error' => 'No se puede crear una referencia circular'], 400);
}

Por favor genera isCircularReference() y isDescendantOf() completos.
```

**Resultado esperado**: Métodos `isCircularReference()` y `isDescendantOf()`

---

## 📝 CÓDIGO COMPLETO A AGREGAR AL CONTROLLER

Después de ejecutar los 4 prompts anteriores, el controlador debe tener estos nuevos métodos:

```php
// src/Controller/Admin/MenuConfigController.php

/**
 * Actualiza la jerarquía del menú vía AJAX (drag & drop)
 */
#[Route('/{id}/update-hierarchy', name: 'update_hierarchy', methods: ['POST'])]
public function updateHierarchy(Request $request, MenuItem $menuItem): JsonResponse
{
    // CÓDIGO GENERADO POR PROMPT 1
}

/**
 * Obtiene la estructura completa del menú como JSON
 */
#[Route('/api/tree', name: 'api_tree', methods: ['GET'])]
public function getMenuTree(): JsonResponse
{
    // CÓDIGO GENERADO POR PROMPT 2
}

/**
 * Agrega un hijo directo a un item
 */
#[Route('/{id}/add-child', name: 'add_child', methods: ['POST'])]
public function addChild(Request $request, MenuItem $menuItem): JsonResponse
{
    // CÓDIGO GENERADO POR PROMPT 3
}

/**
 * Valida referencias circulares
 */
private function isCircularReference(MenuItem $item, ?MenuItem $newParent): bool
{
    // CÓDIGO GENERADO POR PROMPT 4
}

/**
 * Verifica si un item es descendiente de otro (recursivo)
 */
private function isDescendantOf(MenuItem $item, MenuItem $ancestor): bool
{
    // CÓDIGO GENERADO POR PROMPT 4
}

/**
 * Serializa un MenuItem a array (recursivo)
 */
private function serializeMenuItem(MenuItem $item, int $level = 1): array
{
    // CÓDIGO GENERADO POR PROMPT 2
}
```

---

## ✅ VALIDACIÓN DE LA PARTE 1

Después de implementar, verificar que existan estas rutas:

```bash
# Ejecutar en terminal:
php bin/console debug:router | grep admin_menu_config

# Deben aparecer:
admin_menu_config_update_hierarchy  POST  /admin/menu-config/{id}/update-hierarchy
admin_menu_config_api_tree          GET   /admin/menu-config/api/tree
admin_menu_config_add_child         POST  /admin/menu-config/{id}/add-child
```

---

## 🧪 TESTING RÁPIDO DE ENDPOINTS

### Test 1: GET /admin/menu-config/api/tree

```bash
curl http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config/api/tree
# Debe retornar JSON con estructura jerárquica
```

### Test 2: POST /admin/menu-config/{id}/update-hierarchy

```bash
curl -X POST http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config/29/update-hierarchy \
  -H "Content-Type: application/json" \
  -d '{"parent_id": 28, "position": 0}'
# Debe retornar: {"success": true}
```

### Test 3: POST /admin/menu-config/{id}/add-child

```bash
curl -X POST http://melisalacolina.melisaupgrade.prod:8081/admin/menu-config/27/add-child
# Debe retornar: {"success": true, "item": {...}}
```

---

## 📌 NOTAS IMPORTANTES

1. **CSRF Protection**: Los endpoints AJAX pueden necesitar CSRF token. Si falla, agregar en el controlador:
   ```php
   use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
   ```

2. **Autorización**: Verificar que solo ROLE_ADMIN pueda acceder:
   ```php
   $this->denyAccessUnlessGranted('ROLE_ADMIN');
   ```

3. **Logs**: Para debugging, agregar:
   ```php
   use Psr\Log\LoggerInterface;

   $this->logger->info('Actualizando jerarquía', ['item_id' => $menuItem->getId()]);
   ```

---

## ➡️ SIGUIENTE PASO

Una vez completada esta parte, continuar con:
**📄 COPILOT_PROMPTS_PART_2_FRONTEND.md** (HTML/Twig)

---

**Fecha**: 2026-02-01
**Versión**: 1.0
**Estado**: ✅ Listo para usar con Copilot
