# CAJA — Sistema de Cuestionario Clínico (ClinicalAction)

> **Rama:** `feature/caja`
> **Fecha de análisis:** 2026-02-23

---

## Índice

1. [Propósito y alcance](#1-propósito-y-alcance)
2. [Modelo de datos](#2-modelo-de-datos)
3. [ClinicalActionCategory](#3-clinicalactioncategory)
4. [ClinicalActionQuestion](#4-clinicalactionquestion)
5. [ClinicalActionAnswer](#5-clinicalactionanswer)
6. [ClinicalActionPatient (entidad de resultados)](#6-clinicalactionpatient-entidad-de-resultados)
7. [Repositorios](#7-repositorios)
8. [Relaciones entre entidades](#8-relaciones-entre-entidades)
9. [Estado de implementación y uso actual](#9-estado-de-implementación-y-uso-actual)

---

## 1. Propósito y alcance

El sistema de **Cuestionario Clínico** es un conjunto de entidades que modela preguntas/respuestas estructuradas aplicables a las prestaciones clínicas cobradas. Permite registrar metadatos sobre las acciones clínicas más allá del monto: tipo de prestación, respuestas a preguntas parametrizadas por categoría, rango de valores, etc.

**Origen:** Migrado desde el legado `AccionClinica` (pregunta/respuesta estructurada por prestación hospitalaria).

**En el MVP actual (rama `feature/caja`):** Las entidades existen en BD pero **no están integradas en el flujo de cobro**. `ClinicalActionPatient` (las prestaciones cobradas) no tiene referencia a este sistema de preguntas. La integración con el cuestionario clínico completo es una funcionalidad pendiente.

---

## 2. Modelo de datos

```
ClinicalActionCategory
    │ (1:N)
    └── ClinicalActionQuestion
            │ (1:N)
            └── ClinicalActionAnswer
```

Un **Category** (categoría) agrupa **Questions** (preguntas) de un mismo dominio clínico. Cada pregunta puede tener múltiples **Answers** (opciones de respuesta o campos de entrada).

---

## 3. ClinicalActionCategory

**Archivo:** `src/Entity/Tenant/ClinicalActionCategory.php`
**Tabla:** `clinical_action_category`
**Legado:** `CategoriaAccionClinica`
**Repository:** `ClinicalActionCategoryRepository`

Agrupa preguntas clínicas de un mismo tipo de prestación.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK auto-incremental |
| `name` | `name` | `varchar(100)` | Nombre de la categoría (ej. "Consulta Médica", "Procedimiento Quirúrgico") |
| `isActive` | `is_active` | `boolean` (default: true) | Activo/inactivo |
| `idEstado` | `id_estado` | `integer` (default: 1) | Campo legado de estado |
| `createdAt` | `created_at` | `datetime` | Auto en constructor |
| `updatedAt` | `updated_at` | `datetime` (nullable) | Última actualización |

### Validaciones

```php
#[Assert\NotBlank(message: 'Name is required')]
#[Assert\Length(max: 100)]
private string $name;
```

---

## 4. ClinicalActionQuestion

**Archivo:** `src/Entity/Tenant/ClinicalActionQuestion.php`
**Tabla:** `clinical_action_question`
**Legado:** `PreguntaAccionClinica`
**Repository:** `ClinicalActionQuestionRepository`

Pregunta parametrizada dentro de una categoría clínica.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `name` | `name` | `varchar(100)` | Texto de la pregunta |
| `clinicalActionCategory` | `clinical_action_category_id` | FK → `ClinicalActionCategory` (nullable) | Categoría a la que pertenece |
| `sortOrder` | `sort_order` | `integer` (default: 0) | Orden de presentación |
| `rangeMin` | `range_min` | `integer` | Valor mínimo del rango (para respuestas numéricas) |
| `rangeMax` | `range_max` | `integer` | Valor máximo del rango |
| `ageMin` | `age_min` | `integer` (nullable) | Edad mínima del paciente para mostrar la pregunta |
| `ageMax` | `age_max` | `integer` (nullable) | Edad máxima del paciente |
| `helpText` | `help_text` | `varchar(100)` (nullable) | Texto de ayuda contextual |
| `isMultiple` | `is_multiple` | `boolean` (default: false) | ¿Permite múltiples respuestas? |
| `isExtended` | `is_extended` | `boolean` (default: false) | ¿Respuesta extendida (textarea)? |
| `isRequired` | `is_required` | `boolean` (default: false) | ¿Respuesta obligatoria? |
| `fieldType` | `field_type` | `varchar(50)` (nullable) | Tipo de campo HTML (ej. `text`, `number`, `select`, `date`) |
| `isActive` | `is_active` | `boolean` (default: true) | Activo/inactivo |
| `idEstado` | `id_estado` | `integer` (default: 1) | Campo legado |
| `createdAt` / `updatedAt` | — | `datetime` | Auditoría |

### Características de la pregunta

- **`rangeMin` / `rangeMax`**: Para inputs numéricos, define el rango válido de valores.
- **`ageMin` / `ageMax`**: Permite mostrar preguntas condicionalmente según la edad del paciente.
- **`isMultiple`**: Si `true`, el campo admite selección múltiple (checkboxes).
- **`isExtended`**: Si `true`, se usa un `<textarea>` en lugar de `<input>`.
- **`fieldType`**: Controla el tipo de campo HTML renderizado.
- **`sortOrder`**: Las preguntas se ordenan dentro de una categoría.

---

## 5. ClinicalActionAnswer

**Archivo:** `src/Entity/Tenant/ClinicalActionAnswer.php`
**Tabla:** `clinical_action_answer`
**Legado:** `RespuestaAccionClinica`
**Repository:** `ClinicalActionAnswerRepository`

Opción de respuesta o plantilla de campo de entrada para una pregunta clínica.

### Campos

| Campo PHP | Columna BD | Tipo | Descripción |
|---|---|---|---|
| `id` | `id` | `integer` | PK |
| `clinicalActionQuestion` | `clinical_action_question_id` | FK → `ClinicalActionQuestion` (NOT NULL) | Pregunta a la que pertenece |
| `sortOrder` | `sort_order` | `integer` (default: 0) | Orden de presentación |
| `preText` | `pre_text` | `varchar(255)` (nullable) | Texto antes del campo de entrada |
| `postText` | `post_text` | `varchar(255)` (nullable) | Texto después del campo |
| `placeholder` | `placeholder` | `varchar(100)` (nullable) | Placeholder del input |
| `defaultValue` | `default_value` | `varchar(100)` (nullable) | Valor por defecto |
| `entityResponse` | `entity_response` | `varchar(100)` (nullable) | Entidad fuente para respuestas dinámicas (ej. `Doctor`, `Service`) |
| `isChecked` | `is_checked` | `boolean` (default: false) | ¿Marcado por defecto? |
| `isActive` | `is_active` | `boolean` (default: true) | Activo/inactivo |
| `idEstado` | `id_estado` | `integer` (default: 1) | Campo legado |
| `createdAt` / `updatedAt` | — | `datetime` | Auditoría |

### Campos relevantes

- **`preText` / `postText`**: Permiten construir frases como `"El paciente pesa [input] kg"` donde `preText = "El paciente pesa"` y `postText = "kg"`.
- **`entityResponse`**: Indica que la respuesta debe provenir de una entidad del sistema (lista dinámica). Por ejemplo, si `entityResponse = 'Doctor'`, el campo sería un select con médicos.
- **`isChecked`**: Para respuestas de tipo checkbox, indica si está marcado por defecto.
- **`defaultValue`**: Valor pre-llenado al mostrar la pregunta.

---

## 6. ClinicalActionPatient (entidad de resultados)

**Archivo:** `src/Entity/Tenant/ClinicalActionPatient.php`
**Tabla:** `clinical_action_patient`

Esta entidad **no es parte del cuestionario en sí**, sino el **registro de una prestación cobrada**. Sin embargo, conceptualmente debería vincularse al sistema de preguntas/respuestas en el futuro.

En el MVP actual, `ClinicalActionPatient` almacena:
- `paymentAccount` — pago al que pertenece la prestación
- `billingItem` — ítem de facturación cobrado
- `quantity`, `unitPrice`, `totalAmount`, `discountAmount` — datos financieros
- `professional` — profesional que realizó la prestación (nullable)
- `difference` — descuento aplicado (nullable)

**Ausencias notables** respecto al sistema completo:
- No hay FK a `ClinicalActionCategory` ni a `ClinicalActionQuestion`
- No hay tabla de resultados de preguntas respondidas por paciente en este pago

---

## 7. Repositorios

### ClinicalActionCategoryRepository

**Archivo:** `src/Repository/Tenant/ClinicalActionCategoryRepository.php`

#### `findAllActive(): array`
```php
return $this->createQueryBuilder('c')
    ->where('c.isActive = :active')
    ->setParameter('active', true)
    ->orderBy('c.name', 'ASC')
    ->getQuery()
    ->getResult();
```

---

### ClinicalActionQuestionRepository

**Archivo:** `src/Repository/Tenant/ClinicalActionQuestionRepository.php`

#### `findAllActive(): array`
Preguntas activas ordenadas por `sortOrder ASC`.

#### `findByCategory(int $categoryId): array`

**Nota:** Este método tiene un posible bug en la implementación actual. El QueryBuilder usa `c.category` pero la propiedad en la entidad es `clinicalActionCategory`. Debe verificarse antes de usarlo.

```php
// Posible bug: 'c.category' debería ser 'c.clinicalActionCategory'
->where('c.category = :categoryId')
```

---

### ClinicalActionAnswerRepository

**Archivo:** `src/Repository/Tenant/ClinicalActionAnswerRepository.php`

#### `findAllActive(): array`
Respuestas activas ordenadas por `sortOrder ASC`.

#### `findByQuestion(int $questionId): array`

**Nota:** Similar al repositorio anterior, puede tener el mismo tipo de bug con el nombre de la propiedad (`c.question` vs `c.clinicalActionQuestion`). Verificar antes de usar en producción.

---

## 8. Relaciones entre entidades

```
ClinicalActionCategory
    id
    name
    isActive
    │
    │ (1:N via clinical_action_question.clinical_action_category_id)
    ▼
ClinicalActionQuestion
    id
    name
    clinicalActionCategory (FK)
    sortOrder
    rangeMin, rangeMax
    ageMin, ageMax
    isMultiple, isExtended, isRequired
    fieldType
    helpText
    │
    │ (1:N via clinical_action_answer.clinical_action_question_id)
    ▼
ClinicalActionAnswer
    id
    clinicalActionQuestion (FK, NOT NULL)
    sortOrder
    preText, postText, placeholder
    defaultValue
    entityResponse
    isChecked
```

---

## 9. Estado de implementación y uso actual

### Lo que existe en BD

Las tres tablas del sistema de cuestionario están creadas desde la migración `Version20260221030322.php`. Los datos de configuración deben cargarse manualmente (fixtures o seeders no están documentados).

### Lo que NO está implementado en el MVP

| Funcionalidad | Estado |
|---|---|
| Mostrar cuestionario al agregar una prestación | ❌ Pendiente |
| Guardar respuestas del cuestionario en BD | ❌ Pendiente (no hay tabla de resultados) |
| Filtrado de preguntas por edad del paciente | ❌ Pendiente |
| Selects dinámicos con `entityResponse` | ❌ Pendiente |
| Vinculación `ClinicalActionPatient` ↔ cuestionario | ❌ Pendiente |

### Uso actual

`ClinicalActionPatient` es la única entidad del grupo "ClinicalAction" activamente usada. Se crea en `PaymentConfirmController::confirm()` (Paso 3) para cada prestación cobrada:

```php
$clinicalAction = new ClinicalActionPatient();
$clinicalAction
    ->setPaymentAccount($paymentAccount)
    ->setBillingItem($billingItem)
    ->setQuantity($quantity)
    ->setUnitPrice($unitPrice)
    ->setDiscountAmount($discountAmount)
    ->setTotalAmount($totalAmount);
$this->em->persist($clinicalAction);
```

`ClinicalActionCategory`, `ClinicalActionQuestion` y `ClinicalActionAnswer` no se usan en ningún controller ni service del MVP actual.

### Plan de integración futura

Para integrar completamente el cuestionario clínico:

1. Crear una tabla `clinical_action_patient_response` que vincule `ClinicalActionPatient` con las respuestas dadas por el cajero/médico.
2. Agregar FK en `BillingItem` hacia `ClinicalActionCategory` para saber qué preguntas aplican por prestación.
3. Implementar un Stimulus controller que muestre el modal de preguntas al agregar una prestación al panel de cobro.
4. Corregir los bugs en los repositorios (`c.category` → `c.clinicalActionCategory`, `c.question` → `c.clinicalActionQuestion`).
