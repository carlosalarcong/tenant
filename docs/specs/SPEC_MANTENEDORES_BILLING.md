# SPEC: Mantenedores Billing

**Categoría**: Billing  
**Total Mantenedores**: 1  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

El mantenedor de facturación extiende `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/billing/{mantenedor}`

---

## 🗂️ Mantenedor Implementado

### Billing Item (Items de Facturación)

**Controlador**: `App\Controller\Maintainers\Billing\BillingItemController`  
**Entidad**: `App\Entity\Tenant\BillingItem`  
**Form**: `App\Form\Maintainers\Billing\BillingItemType`  
**Template**: `templates/maintainers/billing/billing_item/index.html.twig`

**Endpoints**:
- `GET /maintainers/billing/billing-item` → `app_maintainers_billing_billing_item_index`
- `GET /maintainers/billing/billing-item/create` → `app_maintainers_billing_billing_item_create`
- `GET /maintainers/billing/billing-item/{id}/edit` → `app_maintainers_billing_billing_item_edit`
- `POST /maintainers/billing/billing-item/{id}/delete` → `app_maintainers_billing_billing_item_delete`
- `GET /maintainers/billing/billing-item/export` → `app_maintainers_billing_billing_item_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Turbo Frame**: ✅ Modal para create/edit  
**Features**: CRUD completo + Export CSV

**Export**:
- Columnas: `name`, `isActive`
- Headers: Traducciones de `name`, `is_active`
- Filename: `items_facturacion_YYYY-MM-DD.csv`

---

## 🔄 Patrón de Implementación

```php
<?php

namespace App\Controller\Maintainers\Billing;

use App\Controller\AbstractMantenedorController;
use App\Entity\Tenant\BillingItem;
use App\Form\Maintainers\Billing\BillingItemType;
use App\Repository\Tenant\BillingItemRepository;
use App\Service\Export\ExportService;
use Doctrine\ORM\QueryBuilder;
use Hakam\MultiTenancyBundle\Doctrine\ORM\TenantEntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/maintainers/billing/billing-item')]
class BillingItemController extends AbstractMantenedorController
{
    public function __construct(
        private BillingItemRepository $billingItemRepository,
        TenantEntityManager $tenantEntityManager,
        ExportService $exportService,
        TranslatorInterface $translator
    ) {
        parent::__construct($tenantEntityManager, $translator);
        $this->setExportService($exportService);
    }

    protected function getData(Request $request): array|QueryBuilder
    {
        return $this->billingItemRepository->createQueryBuilder('bi')
            ->orderBy('bi.id', 'DESC');
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
        return 'maintainers/billing/billing_item/index.html.twig';
    }

    protected function getEntityClass(): string
    {
        return BillingItem::class;
    }

    protected function getFormType(): string
    {
        return BillingItemType::class;
    }
}
```

---

## 📊 Resumen

| Mantenedor | Entidad | Columnas | Relaciones |
|------------|---------|----------|------------|
| Billing Item | BillingItem | name, isActive | - |

**Características**:
- ✅ Paginación automática
- ✅ Exportación CSV
- ✅ Turbo Frames
- ✅ Multi-tenancy
- ✅ Traducciones i18n
- ✅ Validación de formularios
- ✅ Soft deletes (isActive)

**Uso**:
Los Items de Facturación se utilizan para definir conceptos factorables en el sistema hospitalario. Son elementos básicos que pueden ser referenciados desde otros módulos del sistema para generar facturas y cobros.
