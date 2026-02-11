# SPEC: Mantenedores de Tesorería

**Categoría**: Treasury  
**Total Mantenedores**: 17  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores de tesorería extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/treasury/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Bank Account Type (Tipos de Cuenta Bancaria)

**Controlador**: `App\Controller\Maintainers\Treasury\BankAccountTypeController`  
**Entidad**: `App\Entity\Tenant\BankAccountType`  
**Form**: `App\Form\Maintainers\Treasury\BankAccountTypeType`  
**Template**: `templates/maintainers/treasury/bank_account_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/bank-account-type` → `app_maintainers_treasury_bank_account_type_index`
- `GET /maintainers/treasury/bank-account-type/create` → `app_maintainers_treasury_bank_account_type_create`
- `GET /maintainers/treasury/bank-account-type/{id}/edit` → `app_maintainers_treasury_bank_account_type_edit`
- `POST /maintainers/treasury/bank-account-type/{id}/delete` → `app_maintainers_treasury_bank_account_type_delete`
- `GET /maintainers/treasury/bank-account-type/export` → `app_maintainers_treasury_bank_account_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tipos_cuenta_banco_YYYY-MM-DD.csv`

---

### 2. Bank (Bancos)

**Controlador**: `App\Controller\Maintainers\Treasury\BankController`  
**Entidad**: `App\Entity\Tenant\Bank`  
**Form**: `App\Form\Maintainers\Treasury\BankType`  
**Template**: `templates/maintainers/treasury/bank/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/bank` → `app_maintainers_treasury_bank_index`
- `GET /maintainers/treasury/bank/create` → `app_maintainers_treasury_bank_create`
- `GET /maintainers/treasury/bank/{id}/edit` → `app_maintainers_treasury_bank_edit`
- `POST /maintainers/treasury/bank/{id}/delete` → `app_maintainers_treasury_bank_delete`
- `GET /maintainers/treasury/bank/export` → `app_maintainers_treasury_bank_export`

**Columnas**: rut, name, currentAccount, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `bancos_YYYY-MM-DD.csv`

---

### 3. Billing Payment Method (Formas de Pago Facturación)

**Controlador**: `App\Controller\Maintainers\Treasury\BillingPaymentMethodController`  
**Entidad**: `App\Entity\Tenant\BillingPaymentMethod`  
**Form**: `App\Form\Maintainers\Treasury\BillingPaymentMethodType`  
**Template**: `templates/maintainers/treasury/billing_payment_method/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/billing-payment-method` → `app_maintainers_treasury_billing_payment_method_index`
- `GET /maintainers/treasury/billing-payment-method/create` → `app_maintainers_treasury_billing_payment_method_create`
- `GET /maintainers/treasury/billing-payment-method/{id}/edit` → `app_maintainers_treasury_billing_payment_method_edit`
- `POST /maintainers/treasury/billing-payment-method/{id}/delete` → `app_maintainers_treasury_billing_payment_method_delete`
- `GET /maintainers/treasury/billing-payment-method/export` → `app_maintainers_treasury_billing_payment_method_export`

**Columnas**: code, name, isCash, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `formas_pago_facturacion_YYYY-MM-DD.csv`  
**Hook beforeSave**: Convierte código a mayúsculas

---

### 4. Cash Register Location (Ubicaciones de Caja)

**Controlador**: `App\Controller\Maintainers\Treasury\CashRegisterLocationController`  
**Entidad**: `App\Entity\Tenant\CashRegisterLocation`  
**Form**: `App\Form\Maintainers\Treasury\CashRegisterLocationType`  
**Template**: `templates/maintainers/treasury/cash_register_location/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/cash-register-location/` → `app_maintainers_treasury_cash_register_location_index`
- `GET /maintainers/treasury/cash-register-location/create` → `app_maintainers_treasury_cash_register_location_create`
- `GET /maintainers/treasury/cash-register-location/{id}/edit` → `app_maintainers_treasury_cash_register_location_edit`
- `POST /maintainers/treasury/cash-register-location/{id}/delete` → `app_maintainers_treasury_cash_register_location_delete`
- `GET /maintainers/treasury/cash-register-location/export` → `app_maintainers_treasury_cash_register_location_export`

**Columnas**: id, name, branch.name, isActive  
**Paginación**: ✅ QueryBuilder (ASC por name)  
**Features**: CRUD + Export  
**Export filename**: `ubicaciones_caja_YYYY-MM-DD_HIS.csv`  
**Relación**: Left join con Branch (Sucursal)

---

### 5. Credit Card (Tarjetas de Crédito)

**Controlador**: `App\Controller\Maintainers\Treasury\CreditCardController`  
**Entidad**: `App\Entity\Tenant\CreditCard`  
**Form**: `App\Form\Maintainers\Treasury\CreditCardFormType`  
**Template**: `templates/maintainers/treasury/credit_card/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/credit-card` → `app_maintainers_treasury_credit_card_index`
- `GET /maintainers/treasury/credit-card/create` → `app_maintainers_treasury_credit_card_create`
- `GET /maintainers/treasury/credit-card/{id}/edit` → `app_maintainers_treasury_credit_card_edit`
- `POST /maintainers/treasury/credit-card/{id}/delete` → `app_maintainers_treasury_credit_card_delete`
- `GET /maintainers/treasury/credit-card/export` → `app_maintainers_treasury_credit_card_export`

**Columnas**: name, abbreviation, creditCardType.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tarjetas_credito_YYYY-MM-DD.csv`  
**Relación**: Left join con CreditCardType

---

### 6. Credit Card Type (Tipos de Tarjeta de Crédito)

**Controlador**: `App\Controller\Maintainers\Treasury\CreditCardTypeController`  
**Entidad**: `App\Entity\Tenant\CreditCardType`  
**Form**: `App\Form\Maintainers\Treasury\CreditCardTypeType`  
**Template**: `templates/maintainers/treasury/credit_card_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/credit-card-type` → `app_maintainers_treasury_credit_card_type_index`
- `GET /maintainers/treasury/credit-card-type/create` → `app_maintainers_treasury_credit_card_type_create`
- `GET /maintainers/treasury/credit-card-type/{id}/edit` → `app_maintainers_treasury_credit_card_type_edit`
- `POST /maintainers/treasury/credit-card-type/{id}/delete` → `app_maintainers_treasury_credit_card_type_delete`
- `GET /maintainers/treasury/credit-card-type/export` → `app_maintainers_treasury_credit_card_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tipos_tarjeta_credito_YYYY-MM-DD.csv`

---

### 7. Currency Type (Tipos de Moneda)

**Controlador**: `App\Controller\Maintainers\Treasury\CurrencyTypeController`  
**Entidad**: `App\Entity\Tenant\CurrencyType`  
**Form**: `App\Form\Maintainers\Treasury\CurrencyTypeType`  
**Template**: `templates/maintainers/treasury/currency_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/currency-type` → `app_maintainers_treasury_currency_type_index`
- `GET /maintainers/treasury/currency-type/create` → `app_maintainers_treasury_currency_type_create`
- `GET /maintainers/treasury/currency-type/{id}/edit` → `app_maintainers_treasury_currency_type_edit`
- `POST /maintainers/treasury/currency-type/{id}/delete` → `app_maintainers_treasury_currency_type_delete`
- `GET /maintainers/treasury/currency-type/export` → `app_maintainers_treasury_currency_type_export`

**Columnas**: name, isClp, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tipos_moneda_YYYY-MM-DD.csv`

---

### 8. Difference Direction (Sentidos de Diferencia)

**Controlador**: `App\Controller\Maintainers\Treasury\DifferenceDirectionController`  
**Entidad**: `App\Entity\Tenant\DifferenceDirection`  
**Form**: `App\Form\Maintainers\Treasury\DifferenceDirectionType`  
**Template**: `templates/maintainers/treasury/difference_direction/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/difference-direction` → `app_maintainers_treasury_difference_direction_index`
- `GET /maintainers/treasury/difference-direction/create` → `app_maintainers_treasury_difference_direction_create`
- `GET /maintainers/treasury/difference-direction/{id}/edit` → `app_maintainers_treasury_difference_direction_edit`
- `POST /maintainers/treasury/difference-direction/{id}/delete` → `app_maintainers_treasury_difference_direction_delete`
- `GET /maintainers/treasury/difference-direction/export` → `app_maintainers_treasury_difference_direction_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `sentidos_diferencia_YYYY-MM-DD.csv`

---

### 9. Difference Reason (Motivos de Diferencia)

**Controlador**: `App\Controller\Maintainers\Treasury\DifferenceReasonController`  
**Entidad**: `App\Entity\Tenant\DifferenceReason`  
**Form**: `App\Form\Maintainers\Treasury\DifferenceReasonType`  
**Template**: `templates/maintainers/treasury/difference_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/difference-reason` → `app_maintainers_treasury_difference_reason_index`
- `GET /maintainers/treasury/difference-reason/create` → `app_maintainers_treasury_difference_reason_create`
- `GET /maintainers/treasury/difference-reason/{id}/edit` → `app_maintainers_treasury_difference_reason_edit`
- `POST /maintainers/treasury/difference-reason/{id}/delete` → `app_maintainers_treasury_difference_reason_delete`
- `GET /maintainers/treasury/difference-reason/export` → `app_maintainers_treasury_difference_reason_export`

**Columnas**: name, differenceDirection.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `motivos_diferencia_YYYY-MM-DD.csv`  
**Relación**: Left join con DifferenceDirection (Sentido)

---

### 10. Difference Type (Tipos de Diferencia)

**Controlador**: `App\Controller\Maintainers\Treasury\DifferenceTypeController`  
**Entidad**: `App\Entity\Tenant\DifferenceType`  
**Form**: `App\Form\Maintainers\Treasury\DifferenceTypeType`  
**Template**: `templates/maintainers/treasury/difference_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/difference-type` → `app_maintainers_treasury_difference_type_index`
- `GET /maintainers/treasury/difference-type/create` → `app_maintainers_treasury_difference_type_create`
- `GET /maintainers/treasury/difference-type/{id}/edit` → `app_maintainers_treasury_difference_type_edit`
- `POST /maintainers/treasury/difference-type/{id}/delete` → `app_maintainers_treasury_difference_type_delete`
- `GET /maintainers/treasury/difference-type/export` → `app_maintainers_treasury_difference_type_export`

**Columnas**: name, description, differenceDirection.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tipos_diferencia_YYYY-MM-DD.csv`  
**Relación**: Left join con DifferenceDirection (Sentido)

---

### 11. Document Type (Tipos de Documento)

**Controlador**: `App\Controller\Maintainers\Treasury\DocumentTypeController`  
**Entidad**: `App\Entity\Tenant\DocumentType`  
**Form**: `App\Form\Maintainers\Treasury\DocumentTypeType`  
**Template**: `templates/maintainers/treasury/document_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/document-type/` → `app_maintainers_treasury_document_type_index`
- `GET /maintainers/treasury/document-type/create` → `app_maintainers_treasury_document_type_create`
- `GET /maintainers/treasury/document-type/{id}/edit` → `app_maintainers_treasury_document_type_edit`
- `POST /maintainers/treasury/document-type/{id}/delete` → `app_maintainers_treasury_document_type_delete`
- `GET /maintainers/treasury/document-type/export` → `app_maintainers_treasury_document_type_export`

**Columnas**: id, siiCode, name, isDte, isLogistics, isActive  
**Paginación**: ✅ QueryBuilder (ASC por name)  
**Features**: CRUD + Export  
**Export filename**: `tipos_documento_YYYY-MM-DD_HIS.csv`

---

### 12. Gratuity Reason (Motivos de Gratuidad)

**Controlador**: `App\Controller\Maintainers\Treasury\GratuityReasonController`  
**Entidad**: `App\Entity\Tenant\GratuityReason`  
**Form**: `App\Form\Maintainers\Treasury\GratuityReasonType`  
**Template**: `templates/maintainers/treasury/gratuity_reason/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/gratuity-reason` → `app_maintainers_treasury_gratuity_reason_index`
- `GET /maintainers/treasury/gratuity-reason/create` → `app_maintainers_treasury_gratuity_reason_create`
- `GET /maintainers/treasury/gratuity-reason/{id}/edit` → `app_maintainers_treasury_gratuity_reason_edit`
- `POST /maintainers/treasury/gratuity-reason/{id}/delete` → `app_maintainers_treasury_gratuity_reason_delete`
- `GET /maintainers/treasury/gratuity-reason/export` → `app_maintainers_treasury_gratuity_reason_export`

**Columnas**: name, gratuityType.name, branch.name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `motivos_gratuidad_YYYY-MM-DD.csv`  
**Relaciones**: 
- Left join con GratuityType (Tipo de Gratuidad)
- Left join con Branch (Sucursal)

---

### 13. Gratuity Type (Tipos de Gratuidad)

**Controlador**: `App\Controller\Maintainers\Treasury\GratuityTypeController`  
**Entidad**: `App\Entity\Tenant\GratuityType`  
**Form**: `App\Form\Maintainers\Treasury\GratuityTypeType`  
**Template**: `templates/maintainers/treasury/gratuity_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/gratuity-type` → `app_maintainers_treasury_gratuity_type_index`
- `GET /maintainers/treasury/gratuity-type/create` → `app_maintainers_treasury_gratuity_type_create`
- `GET /maintainers/treasury/gratuity-type/{id}/edit` → `app_maintainers_treasury_gratuity_type_edit`
- `POST /maintainers/treasury/gratuity-type/{id}/delete` → `app_maintainers_treasury_gratuity_type_delete`
- `GET /maintainers/treasury/gratuity-type/export` → `app_maintainers_treasury_gratuity_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `tipos_gratuidad_YYYY-MM-DD.csv`

---

### 14. Payment Condition (Condiciones de Pago)

**Controlador**: `App\Controller\Maintainers\Treasury\PaymentConditionController`  
**Entidad**: `App\Entity\Tenant\PaymentCondition`  
**Form**: `App\Form\Maintainers\Treasury\PaymentConditionType`  
**Template**: `templates/maintainers/treasury/payment_condition/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/payment-condition` → `app_maintainers_treasury_payment_condition_index`
- `GET /maintainers/treasury/payment-condition/create` → `app_maintainers_treasury_payment_condition_create`
- `GET /maintainers/treasury/payment-condition/{id}/edit` → `app_maintainers_treasury_payment_condition_edit`
- `POST /maintainers/treasury/payment-condition/{id}/delete` → `app_maintainers_treasury_payment_condition_delete`
- `GET /maintainers/treasury/payment-condition/export` → `app_maintainers_treasury_payment_condition_export`

**Columnas**: name, interfaceCode, maxTerm, isUpToDate, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `condiciones_pago_YYYY-MM-DD.csv`

---

### 15. Payment Method (Métodos de Pago)

**Controlador**: `App\Controller\Maintainers\Treasury\PaymentMethodController`  
**Entidad**: `App\Entity\Tenant\PaymentMethod`  
**Form**: `App\Form\Maintainers\Treasury\PaymentMethodFormType`  
**Template**: `templates/maintainers/treasury/payment_method/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/payment-method/` → `app_maintainers_treasury_payment_method_index`
- `GET /maintainers/treasury/payment-method/create` → `app_maintainers_treasury_payment_method_create`
- `GET /maintainers/treasury/payment-method/{id}/edit` → `app_maintainers_treasury_payment_method_edit`
- `POST /maintainers/treasury/payment-method/{id}/delete` → `app_maintainers_treasury_payment_method_delete`
- `GET /maintainers/treasury/payment-method/export` → `app_maintainers_treasury_payment_method_export`

**Columnas**: id, code, name, parent.name, paymentMethodType.name, isActive  
**Paginación**: ✅ QueryBuilder (ASC por name)  
**Features**: CRUD + Export + Relación jerárquica  
**Export filename**: `metodos_pago_YYYY-MM-DD_HIS.csv`  
**Relaciones**: 
- Left join con parent (PaymentMethod - Padre)
- Left join con PaymentMethodType (Tipo)

**Columnas Export Completo**:
- id, code, name, parent.name, paymentMethodType.name
- issuesReceipt, isGuarantee, isProfessionalPayment, isWebPayment
- documentTypeCode, accountingCode, visibleInCashRegister
- creditCardPayment, isActive

---

### 16. Payment Method Type (Tipos de Método de Pago)

**Controlador**: `App\Controller\Maintainers\Treasury\PaymentMethodTypeController`  
**Entidad**: `App\Entity\Tenant\PaymentMethodType`  
**Form**: `App\Form\Maintainers\Treasury\PaymentMethodTypeType`  
**Template**: `templates/maintainers/treasury/payment_method_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/payment-method-type/` → `app_maintainers_treasury_payment_method_type_index`
- `GET /maintainers/treasury/payment-method-type/create` → `app_maintainers_treasury_payment_method_type_create`
- `GET /maintainers/treasury/payment-method-type/{id}/edit` → `app_maintainers_treasury_payment_method_type_edit`
- `POST /maintainers/treasury/payment-method-type/{id}/delete` → `app_maintainers_treasury_payment_method_type_delete`
- `GET /maintainers/treasury/payment-method-type/export` → `app_maintainers_treasury_payment_method_type_export`

**Columnas**: id, name, isActive  
**Paginación**: ✅ QueryBuilder (ASC por name)  
**Features**: CRUD + Export  
**Export filename**: `tipos_metodo_pago_YYYY-MM-DD_HIS.csv`

---

### 17. Transfer Indicator (Indicadores de Traslado)

**Controlador**: `App\Controller\Maintainers\Treasury\TransferIndicatorController`  
**Entidad**: `App\Entity\Tenant\TransferIndicator`  
**Form**: `App\Form\Maintainers\Treasury\TransferIndicatorType`  
**Template**: `templates/maintainers/treasury/transfer_indicator/index.html.twig`

**Endpoints**:
- `GET /maintainers/treasury/transfer-indicator` → `app_maintainers_treasury_transfer_indicator_index`
- `GET /maintainers/treasury/transfer-indicator/create` → `app_maintainers_treasury_transfer_indicator_create`
- `GET /maintainers/treasury/transfer-indicator/{id}/edit` → `app_maintainers_treasury_transfer_indicator_edit`
- `POST /maintainers/treasury/transfer-indicator/{id}/delete` → `app_maintainers_treasury_transfer_indicator_delete`
- `GET /maintainers/treasury/transfer-indicator/export` → `app_maintainers_treasury_transfer_indicator_export`

**Columnas**: code, name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Features**: CRUD + Export  
**Export filename**: `indicadores_traslado_YYYY-MM-DD.csv`

---

## 📊 Resumen de Características

| Mantenedor | Entidad | Form | Relaciones | Export |
|------------|---------|------|------------|--------|
| Bank Account Type | BankAccountType | BankAccountTypeType | - | ✅ |
| Bank | Bank | BankType | - | ✅ |
| Billing Payment Method | BillingPaymentMethod | BillingPaymentMethodType | - | ✅ |
| Cash Register Location | CashRegisterLocation | CashRegisterLocationType | Branch | ✅ |
| Credit Card | CreditCard | CreditCardFormType | CreditCardType | ✅ |
| Credit Card Type | CreditCardType | CreditCardTypeType | - | ✅ |
| Currency Type | CurrencyType | CurrencyTypeType | - | ✅ |
| Difference Direction | DifferenceDirection | DifferenceDirectionType | - | ✅ |
| Difference Reason | DifferenceReason | DifferenceReasonType | DifferenceDirection | ✅ |
| Difference Type | DifferenceType | DifferenceTypeType | DifferenceDirection | ✅ |
| Document Type | DocumentType | DocumentTypeType | - | ✅ |
| Gratuity Reason | GratuityReason | GratuityReasonType | GratuityType, Branch | ✅ |
| Gratuity Type | GratuityType | GratuityTypeType | - | ✅ |
| Payment Condition | PaymentCondition | PaymentConditionType | - | ✅ |
| Payment Method | PaymentMethod | PaymentMethodFormType | parent (self), PaymentMethodType | ✅ |
| Payment Method Type | PaymentMethodType | PaymentMethodTypeType | - | ✅ |
| Transfer Indicator | TransferIndicator | TransferIndicatorType | - | ✅ |

---

## 🔗 Relaciones entre Entidades

```
DifferenceDirection
    ├── DifferenceReason
    └── DifferenceType

CreditCardType
    └── CreditCard

GratuityType
    └── GratuityReason

Branch
    ├── CashRegisterLocation
    └── GratuityReason

PaymentMethodType
    └── PaymentMethod

PaymentMethod (parent)
    └── PaymentMethod (self-referencing)
```

---

## 🎯 Características Especiales

### Hooks beforeSave
- **Todos**: Actualización de `updatedAt` con fecha/hora actual
- **BillingPaymentMethod**: Conversión de código a mayúsculas (`strtoupper`)

### Ordenamiento
- **Por ID DESC**: BankAccountType, Bank, BillingPaymentMethod, CreditCard, CreditCardType, CurrencyType, DifferenceDirection, DifferenceReason, DifferenceType, GratuityReason, GratuityType, PaymentCondition, TransferIndicator
- **Por name ASC**: CashRegisterLocation, DocumentType, PaymentMethod, PaymentMethodType

### Mantenedores con Múltiples Relaciones
1. **PaymentMethod**: Tiene relación con parent (self) y PaymentMethodType
2. **GratuityReason**: Tiene relación con GratuityType y Branch
3. **DifferenceReason**: Tiene relación con DifferenceDirection
4. **DifferenceType**: Tiene relación con DifferenceDirection
5. **CashRegisterLocation**: Tiene relación con Branch
6. **CreditCard**: Tiene relación con CreditCardType

---

## 📁 Estructura de Archivos

```
src/Controller/Maintainers/Treasury/
├── BankAccountTypeController.php
├── BankController.php
├── BillingPaymentMethodController.php
├── CashRegisterLocationController.php
├── CreditCardController.php
├── CreditCardTypeController.php
├── CurrencyTypeController.php
├── DifferenceDirectionController.php
├── DifferenceReasonController.php
├── DifferenceTypeController.php
├── DocumentTypeController.php
├── GratuityReasonController.php
├── GratuityTypeController.php
├── PaymentConditionController.php
├── PaymentMethodController.php
├── PaymentMethodTypeController.php
└── TransferIndicatorController.php

src/Entity/Tenant/
├── BankAccountType.php
├── Bank.php
├── BillingPaymentMethod.php
├── CashRegisterLocation.php
├── CreditCard.php
├── CreditCardType.php
├── CurrencyType.php
├── DifferenceDirection.php
├── DifferenceReason.php
├── DifferenceType.php
├── DocumentType.php
├── GratuityReason.php
├── GratuityType.php
├── PaymentCondition.php
├── PaymentMethod.php
├── PaymentMethodType.php
└── TransferIndicator.php

src/Form/Maintainers/Treasury/
├── BankAccountTypeType.php
├── BankType.php
├── BillingPaymentMethodType.php
├── CashRegisterLocationType.php
├── CreditCardFormType.php
├── CreditCardTypeType.php
├── CurrencyTypeType.php
├── DifferenceDirectionType.php
├── DifferenceReasonType.php
├── DifferenceTypeType.php
├── DocumentTypeType.php
├── GratuityReasonType.php
├── GratuityTypeType.php
├── PaymentConditionType.php
├── PaymentMethodFormType.php
├── PaymentMethodTypeType.php
└── TransferIndicatorType.php

templates/maintainers/treasury/
├── bank_account_type/index.html.twig
├── bank/index.html.twig
├── billing_payment_method/index.html.twig
├── cash_register_location/index.html.twig
├── credit_card/index.html.twig
├── credit_card_type/index.html.twig
├── currency_type/index.html.twig
├── difference_direction/index.html.twig
├── difference_reason/index.html.twig
├── difference_type/index.html.twig
├── document_type/index.html.twig
├── gratuity_reason/index.html.twig
├── gratuity_type/index.html.twig
├── payment_condition/index.html.twig
├── payment_method/index.html.twig
├── payment_method_type/index.html.twig
└── transfer_indicator/index.html.twig
```

---

## ✅ Checklist de Implementación

- [x] 17 Controladores implementados extendiendo AbstractMantenedorController
- [x] 17 Entidades Tenant configuradas
- [x] 17 Form Types creados
- [x] 17 Templates Twig implementados
- [x] Rutas configuradas para todos los mantenedores
- [x] Paginación implementada con QueryBuilder
- [x] Exportación CSV funcional en todos
- [x] Multi-tenancy integrado
- [x] Turbo Frames para modales
- [x] Hooks beforeSave personalizados
- [x] Relaciones entre entidades correctamente mapeadas

---

## 🚀 Próximos Pasos

1. ✅ Validar forms con reglas de negocio específicas
2. ✅ Agregar tests unitarios para cada controlador
3. ✅ Documentar casos de uso de relaciones complejas (PaymentMethod)
4. ✅ Implementar soft delete si es necesario
5. ✅ Agregar filtros de búsqueda avanzados
6. ✅ Optimizar queries con índices de base de datos

---

**Última revisión**: 2026-02-09  
**Revisado por**: GitHub Copilot  
**Estado**: ✅ Documentado y Verificado
