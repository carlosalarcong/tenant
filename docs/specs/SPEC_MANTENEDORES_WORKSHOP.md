# SPEC: Mantenedores Workshop

**Categoría**: Workshop  
**Total Mantenedores**: 1  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

El mantenedor de talleres extiende `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/workshop/{mantenedor}`

---

## 🗂️ Mantenedor Implementado

### Workshop (Talleres)

**Controlador**: `App\Controller\Maintainers\Workshop\WorkshopController`  
**Entidad**: `App\Entity\Tenant\Workshop`  
**Form**: `App\Form\Maintainers\Workshop\WorkshopType`  
**Template**: `templates/maintainers/workshop/workshop/index.html.twig`

**Endpoints**:
- `GET /maintainers/workshop/workshop` → `app_maintainers_workshop_workshop_index`
- `GET /maintainers/workshop/workshop/create` → `app_maintainers_workshop_workshop_create`
- `GET /maintainers/workshop/workshop/{id}/edit` → `app_maintainers_workshop_workshop_edit`
- `POST /maintainers/workshop/workshop/{id}/delete` → `app_maintainers_workshop_workshop_delete`
- `GET /maintainers/workshop/workshop/export` → `app_maintainers_workshop_workshop_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `talleres_YYYY-MM-DD.csv`

---

## 🔄 Patrón de Implementación

```php
<?php

namespace App\Controller\Maintainers\Workshop;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\Workshop;
use App\Form\Maintainers\Workshop\WorkshopType;
use App\Repository\Tenant\WorkshopRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/workshop/workshop')]
class WorkshopController extends AbstractMantenedorController
{
    public function __construct(
        private WorkshopRepository $workshopRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->workshopRepository->createQueryBuilder('w')
            ->orderBy('w.id', 'DESC');
    }

    protected function getColumns(): array
    {
        return [
            'name' => $this->translator->trans('maintainers.columns.name', [], 'maintainers'),
            'isActive' => $this->translator->trans('maintainers.columns.is_active', [], 'maintainers')
        ];
    }

    protected function getTemplatePath(): string
    {
        return 'maintainers/workshop/workshop/index.html.twig';
    }

    protected function getEntityClass(): string
    {
        return Workshop::class;
    }

    protected function getFormType(): string
    {
        return WorkshopType::class;
    }
}
```

---

## 📊 Resumen

| Mantenedor | Entidad | Columnas | Relaciones |
|------------|---------|----------|------------|
| Workshop | Workshop | name, isActive | - |

**Características**:
- ✅ Paginación automática
- ✅ Exportación CSV
- ✅ Turbo Frames
- ✅ Multi-tenancy
- ✅ Traducciones i18n
- ✅ Validación de formularios
- ✅ Soft deletes (isActive)

**Uso**:
Los Talleres se utilizan para gestionar espacios físicos o virtuales donde se realizan actividades de mantenimiento, reparación o servicios técnicos dentro del hospital. Pueden incluir talleres de:
- Mantenimiento de equipamiento médico
- Reparación de mobiliario hospitalario
- Servicios técnicos especializados
- Calibración de instrumentos

**Relación con otros módulos**:
- Se integra con el módulo de Activos y Equipamiento
- Referenciado desde el módulo de Mantenimiento
- Puede relacionarse con Órdenes de Trabajo
