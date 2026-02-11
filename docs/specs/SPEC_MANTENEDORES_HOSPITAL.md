# SPEC: Mantenedores Hospital

**Categoría**: Hospital  
**Total Mantenedores**: 24  
**Estado**: ✅ Implementado  
**Última actualización**: 2026-02-09

---

## 📐 Arquitectura

Todos los mantenedores hospitalarios extienden `AbstractMantenedorController` que implementa:
- ✅ CRUD completo con Template Method Pattern
- ✅ Paginación automática vía QueryBuilder
- ✅ Turbo Frames para modales
- ✅ Multi-tenancy con TenantEntityManager
- ✅ Exportación CSV

**Ruta base**: `/maintainers/hospital/{mantenedor}`

---

## 🗂️ Mantenedores Implementados

### 1. Care Category (Categorías de Cuidado)

**Controlador**: `App\Controller\Maintainers\Hospital\CareCategoryController`  
**Entidad**: `App\Entity\Tenant\CareCategory`  
**Form**: `App\Form\Maintainers\Hospital\CareCategoryType`  
**Template**: `templates/maintainers/hospital/care_category/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/care-category` → `app_maintainers_hospital_care_category_index`
- `GET /maintainers/hospital/care-category/create` → `app_maintainers_hospital_care_category_create`
- `GET /maintainers/hospital/care-category/{id}/edit` → `app_maintainers_hospital_care_category_edit`
- `POST /maintainers/hospital/care-category/{id}/delete` → `app_maintainers_hospital_care_category_delete`
- `GET /maintainers/hospital/care-category/export` → `app_maintainers_hospital_care_category_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `categorias_cuidados_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 2. Care Closure Destination (Destinos de Cierre de Atención)

**Controlador**: `App\Controller\Maintainers\Hospital\CareClosureDestinationController`  
**Entidad**: `App\Entity\Tenant\CareClosureDestination`  
**Form**: `App\Form\Maintainers\Hospital\CareClosureDestinationType`  
**Template**: `templates/maintainers/hospital/care_closure_destination/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/care-closure-destination` → `app_maintainers_hospital_care_closure_destination_index`
- `GET /maintainers/hospital/care-closure-destination/create` → `app_maintainers_hospital_care_closure_destination_create`
- `GET /maintainers/hospital/care-closure-destination/{id}/edit` → `app_maintainers_hospital_care_closure_destination_edit`
- `POST /maintainers/hospital/care-closure-destination/{id}/delete` → `app_maintainers_hospital_care_closure_destination_delete`
- `GET /maintainers/hospital/care-closure-destination/export` → `app_maintainers_hospital_care_closure_destination_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `destinos_cierre_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 3. Care Intervention (Intervenciones de Cuidado)

**Controlador**: `App\Controller\Maintainers\Hospital\CareInterventionController`  
**Entidad**: `App\Entity\Tenant\CareIntervention`  
**Form**: `App\Form\Maintainers\Hospital\CareInterventionType`  
**Template**: `templates/maintainers/hospital/care_intervention/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/care-intervention` → `app_maintainers_hospital_care_intervention_index`
- `GET /maintainers/hospital/care-intervention/create` → `app_maintainers_hospital_care_intervention_create`
- `GET /maintainers/hospital/care-intervention/{id}/edit` → `app_maintainers_hospital_care_intervention_edit`
- `POST /maintainers/hospital/care-intervention/{id}/delete` → `app_maintainers_hospital_care_intervention_delete`
- `GET /maintainers/hospital/care-intervention/export` → `app_maintainers_hospital_care_intervention_export`

**Columnas**: description, careCategory.name (Categoria), isActive  
**Paginación**: ✅ QueryBuilder con JOIN a careCategory (DESC por ID)  
**Features**: CRUD completo + Export CSV  
**Relación**: ManyToOne con CareCategory

---

### 4. Clinical Action Answer (Respuestas de Acciones Clínicas)

**Controlador**: `App\Controller\Maintainers\Hospital\ClinicalActionAnswerController`  
**Entidad**: `App\Entity\Tenant\ClinicalActionAnswer`  
**Form**: `App\Form\Maintainers\Hospital\ClinicalActionAnswerType`  
**Template**: `templates/maintainers/hospital/clinical_action_answer/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/clinical-action-answer` → `app_maintainers_hospital_clinical_action_answer_index`
- `GET /maintainers/hospital/clinical-action-answer/create` → `app_maintainers_hospital_clinical_action_answer_create`
- `GET /maintainers/hospital/clinical-action-answer/{id}/edit` → `app_maintainers_hospital_clinical_action_answer_edit`
- `POST /maintainers/hospital/clinical-action-answer/{id}/delete` → `app_maintainers_hospital_clinical_action_answer_delete`
- `GET /maintainers/hospital/clinical-action-answer/export` → `app_maintainers_hospital_clinical_action_answer_export`

**Columnas**: sortOrder (Orden), preText (Texto Previo), clinicalActionQuestion.name (Pregunta), isChecked (Seleccionado), isActive  
**Paginación**: ✅ QueryBuilder con JOIN a clinicalActionQuestion (ASC por sortOrder de pregunta y respuesta)  
**Features**: CRUD completo + Export CSV  
**Relación**: ManyToOne con ClinicalActionQuestion

---

### 5. Clinical Action Category (Categorías de Acciones Clínicas)

**Controlador**: `App\Controller\Maintainers\Hospital\ClinicalActionCategoryController`  
**Entidad**: `App\Entity\Tenant\ClinicalActionCategory`  
**Form**: `App\Form\Maintainers\Hospital\ClinicalActionCategoryType`  
**Template**: `templates/maintainers/hospital/clinical_action_category/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/clinical-action-category` → `app_maintainers_hospital_clinical_action_category_index`
- `GET /maintainers/hospital/clinical-action-category/create` → `app_maintainers_hospital_clinical_action_category_create`
- `GET /maintainers/hospital/clinical-action-category/{id}/edit` → `app_maintainers_hospital_clinical_action_category_edit`
- `POST /maintainers/hospital/clinical-action-category/{id}/delete` → `app_maintainers_hospital_clinical_action_category_delete`
- `GET /maintainers/hospital/clinical-action-category/export` → `app_maintainers_hospital_clinical_action_category_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `categorias_accion_clinica_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 6. Clinical Action Question (Preguntas de Acciones Clínicas)

**Controlador**: `App\Controller\Maintainers\Hospital\ClinicalActionQuestionController`  
**Entidad**: `App\Entity\Tenant\ClinicalActionQuestion`  
**Form**: `App\Form\Maintainers\Hospital\ClinicalActionQuestionType`  
**Template**: `templates/maintainers/hospital/clinical_action_question/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/clinical-action-question` → `app_maintainers_hospital_clinical_action_question_index`
- `GET /maintainers/hospital/clinical-action-question/create` → `app_maintainers_hospital_clinical_action_question_create`
- `GET /maintainers/hospital/clinical-action-question/{id}/edit` → `app_maintainers_hospital_clinical_action_question_edit`
- `POST /maintainers/hospital/clinical-action-question/{id}/delete` → `app_maintainers_hospital_clinical_action_question_delete`
- `GET /maintainers/hospital/clinical-action-question/export` → `app_maintainers_hospital_clinical_action_question_export`

**Columnas**: name, sortOrder (Orden), clinicalActionCategory.name (Categoria), fieldType (Tipo Campo), isRequired (Obligatorio), isActive  
**Paginación**: ✅ QueryBuilder con JOIN a clinicalActionCategory (ASC por sortOrder)  
**Features**: CRUD completo + Export CSV  
**Relación**: ManyToOne con ClinicalActionCategory

---

### 7. Dosage Type (Tipos de Posología)

**Controlador**: `App\Controller\Maintainers\Hospital\DosageTypeController`  
**Entidad**: `App\Entity\Tenant\DosageType`  
**Form**: `App\Form\Maintainers\Hospital\DosageTypeType`  
**Template**: `templates/maintainers/hospital/dosage_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/dosage-type` → `app_maintainers_hospital_dosage_type_index`
- `GET /maintainers/hospital/dosage-type/create` → `app_maintainers_hospital_dosage_type_create`
- `GET /maintainers/hospital/dosage-type/{id}/edit` → `app_maintainers_hospital_dosage_type_edit`
- `POST /maintainers/hospital/dosage-type/{id}/delete` → `app_maintainers_hospital_dosage_type_delete`
- `GET /maintainers/hospital/dosage-type/export` → `app_maintainers_hospital_dosage_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `tipos_posologia_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 8. Eating Disorder History (Antecedentes de Trastornos Alimentarios)

**Controlador**: `App\Controller\Maintainers\Hospital\EatingDisorderHistoryController`  
**Entidad**: `App\Entity\Tenant\EatingDisorderHistory`  
**Form**: `App\Form\Maintainers\Hospital\EatingDisorderHistoryType`  
**Template**: `templates/maintainers/hospital/eating_disorder_history/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/eating-disorder-history` → `app_maintainers_hospital_eating_disorder_history_index`
- `GET /maintainers/hospital/eating-disorder-history/create` → `app_maintainers_hospital_eating_disorder_history_create`
- `GET /maintainers/hospital/eating-disorder-history/{id}/edit` → `app_maintainers_hospital_eating_disorder_history_edit`
- `POST /maintainers/hospital/eating-disorder-history/{id}/delete` → `app_maintainers_hospital_eating_disorder_history_delete`
- `GET /maintainers/hospital/eating-disorder-history/export` → `app_maintainers_hospital_eating_disorder_history_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `antecedentes_tca_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 9. Intoxication State (Estados de Ebriedad)

**Controlador**: `App\Controller\Maintainers\Hospital\IntoxicationStateController`  
**Entidad**: `App\Entity\Tenant\IntoxicationState`  
**Form**: `App\Form\Maintainers\Hospital\IntoxicationStateType`  
**Template**: `templates/maintainers/hospital/intoxication_state/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/intoxication-state` → `app_maintainers_hospital_intoxication_state_index`
- `GET /maintainers/hospital/intoxication-state/create` → `app_maintainers_hospital_intoxication_state_create`
- `GET /maintainers/hospital/intoxication-state/{id}/edit` → `app_maintainers_hospital_intoxication_state_edit`
- `POST /maintainers/hospital/intoxication-state/{id}/delete` → `app_maintainers_hospital_intoxication_state_delete`
- `GET /maintainers/hospital/intoxication-state/export` → `app_maintainers_hospital_intoxication_state_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID, alias 'is2')  
**Export filename**: `estados_ebriedad_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 10. Medical Device (Dispositivos Médicos)

**Controlador**: `App\Controller\Maintainers\Hospital\MedicalDeviceController`  
**Entidad**: `App\Entity\Tenant\MedicalDevice`  
**Form**: `App\Form\Maintainers\Hospital\MedicalDeviceType`  
**Template**: `templates/maintainers/hospital/medical_device/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/medical-device` → `app_maintainers_hospital_medical_device_index`
- `GET /maintainers/hospital/medical-device/create` → `app_maintainers_hospital_medical_device_create`
- `GET /maintainers/hospital/medical-device/{id}/edit` → `app_maintainers_hospital_medical_device_edit`
- `POST /maintainers/hospital/medical-device/{id}/delete` → `app_maintainers_hospital_medical_device_delete`
- `GET /maintainers/hospital/medical-device/export` → `app_maintainers_hospital_medical_device_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `dispositivos_medicos_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 11. Nutritional Diagnosis (Diagnósticos Nutricionales)

**Controlador**: `App\Controller\Maintainers\Hospital\NutritionalDiagnosisController`  
**Entidad**: `App\Entity\Tenant\NutritionalDiagnosis`  
**Form**: `App\Form\Maintainers\Hospital\NutritionalDiagnosisType`  
**Template**: `templates/maintainers/hospital/nutritional_diagnosis/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/nutritional-diagnosis` → `app_maintainers_hospital_nutritional_diagnosis_index`
- `GET /maintainers/hospital/nutritional-diagnosis/create` → `app_maintainers_hospital_nutritional_diagnosis_create`
- `GET /maintainers/hospital/nutritional-diagnosis/{id}/edit` → `app_maintainers_hospital_nutritional_diagnosis_edit`
- `POST /maintainers/hospital/nutritional-diagnosis/{id}/delete` → `app_maintainers_hospital_nutritional_diagnosis_delete`
- `GET /maintainers/hospital/nutritional-diagnosis/export` → `app_maintainers_hospital_nutritional_diagnosis_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `diagnosticos_nutricionales_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 12. Nutritionist BMI Index (Índices IMC Nutricionista)

**Controlador**: `App\Controller\Maintainers\Hospital\NutritionistBmiIndexController`  
**Entidad**: `App\Entity\Tenant\NutritionistBmiIndex`  
**Form**: `App\Form\Maintainers\Hospital\NutritionistBmiIndexType`  
**Template**: `templates/maintainers/hospital/nutritionist_bmi_index/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/nutritionist-bmi-index` → `app_maintainers_hospital_nutritionist_bmi_index_index`
- `GET /maintainers/hospital/nutritionist-bmi-index/create` → `app_maintainers_hospital_nutritionist_bmi_index_create`
- `GET /maintainers/hospital/nutritionist-bmi-index/{id}/edit` → `app_maintainers_hospital_nutritionist_bmi_index_edit`
- `POST /maintainers/hospital/nutritionist-bmi-index/{id}/delete` → `app_maintainers_hospital_nutritionist_bmi_index_delete`
- `GET /maintainers/hospital/nutritionist-bmi-index/export` → `app_maintainers_hospital_nutritionist_bmi_index_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `indices_imc_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 13. Nutritionist Index Classification (Clasificación de Índices Nutricionista)

**Controlador**: `App\Controller\Maintainers\Hospital\NutritionistIndexClassificationController`  
**Entidad**: `App\Entity\Tenant\NutritionistIndexClassification`  
**Form**: `App\Form\Maintainers\Hospital\NutritionistIndexClassificationType`  
**Template**: `templates/maintainers/hospital/nutritionist_index_classification/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/nutritionist-index-classification` → `app_maintainers_hospital_nutritionist_index_classification_index`
- `GET /maintainers/hospital/nutritionist-index-classification/create` → `app_maintainers_hospital_nutritionist_index_classification_create`
- `GET /maintainers/hospital/nutritionist-index-classification/{id}/edit` → `app_maintainers_hospital_nutritionist_index_classification_edit`
- `POST /maintainers/hospital/nutritionist-index-classification/{id}/delete` → `app_maintainers_hospital_nutritionist_index_classification_delete`
- `GET /maintainers/hospital/nutritionist-index-classification/export` → `app_maintainers_hospital_nutritionist_index_classification_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `clasificaciones_indices_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 14. Nutritionist TE Index (Índices TE Nutricionista)

**Controlador**: `App\Controller\Maintainers\Hospital\NutritionistTeIndexController`  
**Entidad**: `App\Entity\Tenant\NutritionistTeIndex`  
**Form**: `App\Form\Maintainers\Hospital\NutritionistTeIndexType`  
**Template**: `templates/maintainers/hospital/nutritionist_te_index/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/nutritionist-te-index` → `app_maintainers_hospital_nutritionist_te_index_index`
- `GET /maintainers/hospital/nutritionist-te-index/create` → `app_maintainers_hospital_nutritionist_te_index_create`
- `GET /maintainers/hospital/nutritionist-te-index/{id}/edit` → `app_maintainers_hospital_nutritionist_te_index_edit`
- `POST /maintainers/hospital/nutritionist-te-index/{id}/delete` → `app_maintainers_hospital_nutritionist_te_index_delete`
- `GET /maintainers/hospital/nutritionist-te-index/export` → `app_maintainers_hospital_nutritionist_te_index_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `indices_te_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 15. Physical Exam Base Field (Campos Base de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Hospital\PhysicalExamBaseFieldController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamBaseField`  
**Form**: `App\Form\Maintainers\Hospital\PhysicalExamBaseFieldType`  
**Template**: `templates/maintainers/hospital/physical_exam_base_field/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/physical-exam-base-field` → `app_maintainers_hospital_physical_exam_base_field_index`
- `GET /maintainers/hospital/physical-exam-base-field/create` → `app_maintainers_hospital_physical_exam_base_field_create`
- `GET /maintainers/hospital/physical-exam-base-field/{id}/edit` → `app_maintainers_hospital_physical_exam_base_field_edit`
- `POST /maintainers/hospital/physical-exam-base-field/{id}/delete` → `app_maintainers_hospital_physical_exam_base_field_delete`
- `GET /maintainers/hospital/physical-exam-base-field/export` → `app_maintainers_hospital_physical_exam_base_field_export`

**Columnas**: name, sortOrder (Orden), fieldType (Tipo Campo), isRequired (Obligatorio), isActive  
**Paginación**: ✅ QueryBuilder (ASC por sortOrder)  
**Export columns**: name, sortOrder, fieldType, isRequired, isActive  
**Export filename**: `campos_base_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 16. Physical Exam Field (Campos de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Hospital\PhysicalExamFieldController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamField`  
**Form**: `App\Form\Maintainers\Hospital\PhysicalExamFieldType`  
**Template**: `templates/maintainers/hospital/physical_exam_field/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/physical-exam-field` → `app_maintainers_hospital_physical_exam_field_index`
- `GET /maintainers/hospital/physical-exam-field/create` → `app_maintainers_hospital_physical_exam_field_create`
- `GET /maintainers/hospital/physical-exam-field/{id}/edit` → `app_maintainers_hospital_physical_exam_field_edit`
- `POST /maintainers/hospital/physical-exam-field/{id}/delete` → `app_maintainers_hospital_physical_exam_field_delete`
- `GET /maintainers/hospital/physical-exam-field/export` → `app_maintainers_hospital_physical_exam_field_export`

**Columnas**: name, sortOrder (Orden), unit (Unidad), grouping1.name (Agrupacion 1), isWeight (Peso), isTemperature (Temp), isActive  
**Paginación**: ✅ QueryBuilder con JOIN a grouping1 y grouping2 (ASC por sortOrder)  
**Export columns**: name, sortOrder, unit, grouping1.name, isWeight, isTemperature, isActive  
**Export filename**: `campos_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV  
**Relaciones**: ManyToOne con PhysicalExamGrouping (grouping1, grouping2)

---

### 17. Physical Exam Grouping (Agrupaciones de Examen Físico)

**Controlador**: `App\Controller\Maintainers\Hospital\PhysicalExamGroupingController`  
**Entidad**: `App\Entity\Tenant\PhysicalExamGrouping`  
**Form**: `App\Form\Maintainers\Hospital\PhysicalExamGroupingType`  
**Template**: `templates/maintainers/hospital/physical_exam_grouping/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/physical-exam-grouping` → `app_maintainers_hospital_physical_exam_grouping_index`
- `GET /maintainers/hospital/physical-exam-grouping/create` → `app_maintainers_hospital_physical_exam_grouping_create`
- `GET /maintainers/hospital/physical-exam-grouping/{id}/edit` → `app_maintainers_hospital_physical_exam_grouping_edit`
- `POST /maintainers/hospital/physical-exam-grouping/{id}/delete` → `app_maintainers_hospital_physical_exam_grouping_delete`
- `GET /maintainers/hospital/physical-exam-grouping/export` → `app_maintainers_hospital_physical_exam_grouping_export`

**Columnas**: name, sortOrder (Orden), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `agrupaciones_examen_fisico_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 18. Prescription Dispensation (Dispensaciones de Receta)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionDispensationController`  
**Entidad**: `App\Entity\Tenant\PrescriptionDispensation`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionDispensationType`  
**Template**: `templates/maintainers/hospital/prescription_dispensation/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-dispensation` → `app_maintainers_hospital_prescription_dispensation_index`
- `GET /maintainers/hospital/prescription-dispensation/create` → `app_maintainers_hospital_prescription_dispensation_create`
- `GET /maintainers/hospital/prescription-dispensation/{id}/edit` → `app_maintainers_hospital_prescription_dispensation_edit`
- `POST /maintainers/hospital/prescription-dispensation/{id}/delete` → `app_maintainers_hospital_prescription_dispensation_delete`
- `GET /maintainers/hospital/prescription-dispensation/export` → `app_maintainers_hospital_prescription_dispensation_export`

**Columnas**: name, sortOrder (Orden), quantity (Cantidad), timeUnit (Unidad Tiempo), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID, alias 'pd2')  
**Export filename**: `dispensaciones_receta_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 19. Prescription Dosage (Dosis de Receta)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionDosageController`  
**Entidad**: `App\Entity\Tenant\PrescriptionDosage`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionDosageType`  
**Template**: `templates/maintainers/hospital/prescription_dosage/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-dosage` → `app_maintainers_hospital_prescription_dosage_index`
- `GET /maintainers/hospital/prescription-dosage/create` → `app_maintainers_hospital_prescription_dosage_create`
- `GET /maintainers/hospital/prescription-dosage/{id}/edit` → `app_maintainers_hospital_prescription_dosage_edit`
- `POST /maintainers/hospital/prescription-dosage/{id}/delete` → `app_maintainers_hospital_prescription_dosage_delete`
- `GET /maintainers/hospital/prescription-dosage/export` → `app_maintainers_hospital_prescription_dosage_export`

**Columnas**: name, quantity (Cantidad), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `dosis_receta_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 20. Prescription Format (Formatos de Receta)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionFormatController`  
**Entidad**: `App\Entity\Tenant\PrescriptionFormat`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionFormatType`  
**Template**: `templates/maintainers/hospital/prescription_format/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-format` → `app_maintainers_hospital_prescription_format_index`
- `GET /maintainers/hospital/prescription-format/create` → `app_maintainers_hospital_prescription_format_create`
- `GET /maintainers/hospital/prescription-format/{id}/edit` → `app_maintainers_hospital_prescription_format_edit`
- `POST /maintainers/hospital/prescription-format/{id}/delete` → `app_maintainers_hospital_prescription_format_delete`
- `GET /maintainers/hospital/prescription-format/export` → `app_maintainers_hospital_prescription_format_export`

**Columnas**: name, sortOrder (Orden), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID, alias 'pf2')  
**Export filename**: `formatos_receta_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 21. Prescription Frequency (Frecuencias de Receta)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionFrequencyController`  
**Entidad**: `App\Entity\Tenant\PrescriptionFrequency`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionFrequencyType`  
**Template**: `templates/maintainers/hospital/prescription_frequency/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-frequency` → `app_maintainers_hospital_prescription_frequency_index`
- `GET /maintainers/hospital/prescription-frequency/create` → `app_maintainers_hospital_prescription_frequency_create`
- `GET /maintainers/hospital/prescription-frequency/{id}/edit` → `app_maintainers_hospital_prescription_frequency_edit`
- `POST /maintainers/hospital/prescription-frequency/{id}/delete` → `app_maintainers_hospital_prescription_frequency_delete`
- `GET /maintainers/hospital/prescription-frequency/export` → `app_maintainers_hospital_prescription_frequency_export`

**Columnas**: name, quantity (Cantidad), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `frecuencias_receta_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 22. Prescription Route (Vías de Administración)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionRouteController`  
**Entidad**: `App\Entity\Tenant\PrescriptionRoute`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionRouteType`  
**Template**: `templates/maintainers/hospital/prescription_route/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-route` → `app_maintainers_hospital_prescription_route_index`
- `GET /maintainers/hospital/prescription-route/create` → `app_maintainers_hospital_prescription_route_create`
- `GET /maintainers/hospital/prescription-route/{id}/edit` → `app_maintainers_hospital_prescription_route_edit`
- `POST /maintainers/hospital/prescription-route/{id}/delete` → `app_maintainers_hospital_prescription_route_delete`
- `GET /maintainers/hospital/prescription-route/export` → `app_maintainers_hospital_prescription_route_export`

**Columnas**: name, sortOrder (Orden), isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `vias_administracion_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 23. Prescription Rule Detail (Detalles de Reglas de Prescripción)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionRuleDetailController`  
**Entidad**: `App\Entity\Tenant\PrescriptionRuleDetail`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionRuleDetailType`  
**Template**: `templates/maintainers/hospital/prescription_rule_detail/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-rule-detail` → `app_maintainers_hospital_prescription_rule_detail_index`
- `GET /maintainers/hospital/prescription-rule-detail/create` → `app_maintainers_hospital_prescription_rule_detail_create`
- `GET /maintainers/hospital/prescription-rule-detail/{id}/edit` → `app_maintainers_hospital_prescription_rule_detail_edit`
- `POST /maintainers/hospital/prescription-rule-detail/{id}/delete` → `app_maintainers_hospital_prescription_rule_detail_delete`
- `GET /maintainers/hospital/prescription-rule-detail/export` → `app_maintainers_hospital_prescription_rule_detail_export`

**Columnas**: intervals (Intervalos), dailyQuantity (Cant/Dia), isActive  
**Paginación**: ✅ QueryBuilder (ASC por dailyQuantity)  
**Export filename**: `reglas_prescripcion_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

### 24. Prescription Type (Tipos de Receta)

**Controlador**: `App\Controller\Maintainers\Hospital\PrescriptionTypeController`  
**Entidad**: `App\Entity\Tenant\PrescriptionType`  
**Form**: `App\Form\Maintainers\Hospital\PrescriptionTypeType`  
**Template**: `templates/maintainers/hospital/prescription_type/index.html.twig`

**Endpoints**:
- `GET /maintainers/hospital/prescription-type` → `app_maintainers_hospital_prescription_type_index`
- `GET /maintainers/hospital/prescription-type/create` → `app_maintainers_hospital_prescription_type_create`
- `GET /maintainers/hospital/prescription-type/{id}/edit` → `app_maintainers_hospital_prescription_type_edit`
- `POST /maintainers/hospital/prescription-type/{id}/delete` → `app_maintainers_hospital_prescription_type_delete`
- `GET /maintainers/hospital/prescription-type/export` → `app_maintainers_hospital_prescription_type_export`

**Columnas**: name, isActive  
**Paginación**: ✅ QueryBuilder (DESC por ID)  
**Export filename**: `tipos_receta_YYYY-MM-DD.csv`  
**Features**: CRUD completo + Export CSV

---

## 🔧 Componentes Compartidos

### Templates Base
- `templates/maintainers/modern_index.html.twig` - Template maestro
- `templates/maintainers/_base_index.html.twig` - Base alternativa
- `templates/maintainers/_modal_form.html.twig` - Modal para forms

### AbstractMantenedorController
**Ubicación**: `src/Controller/AbstractMantenedorController.php`

**Métodos requeridos** (Template Method):
```php
protected function getData(Request $request): array|QueryBuilder;
protected function getColumns(): array;
protected function getTemplatePath(): string;
protected function getFormType(): string;
protected function createNewEntity(): object;
protected function getIndexRoute(): string;
protected function findEntity(int $id): ?object;
```

**Métodos implementados**:
- `handleIndex()` - Listado con paginación automática
- `handleCreate()` - Crear con Turbo Frame
- `handleEdit()` - Editar con Turbo Frame
- `handleDelete()` - Eliminar con confirmación
- `handleExport()` - Exportar CSV
- `paginate()` - Paginación con Doctrine Paginator
- `isTurboFrameRequest()` - Detección Turbo Frame

### Paginación Automática
**Detección por tipo de retorno**:
- `QueryBuilder` → Paginación activada ✅
- `Array` → Sin paginación ❌

**Parámetros URL**: `?page=1&limit=10`  
**Default**: 10 items por página

---

## 🎨 UI/UX

**Framework**: Bootstrap 5.3.0  
**Iconos**: BoxIcons (`bx-*`)  
**Modales**: Turbo Frames  
**Confirmación delete**: SweetAlert2  
**Responsive**: ✅ Mobile-first

**Breadcrumb típico**:
```
Dashboard > Mantenedores > Hospital > {Mantenedor}
```

---

## 🔐 Seguridad

- ✅ CSRF Protection en formularios
- ✅ Multi-tenancy aislamiento por TenantContext
- ✅ Validación Doctrine constraints
- ✅ Method-level security con `#[Route]` methods

---

## 📊 Estado de Implementación

| Característica | Estado |
|---------------|--------|
| CRUD Completo | ✅ 24/24 |
| Paginación | ✅ 24/24 |
| Exportación | ✅ 24/24 |
| Turbo Frames | ✅ 24/24 |
| Forms documentados | ✅ 24/24 |
| Tests unitarios | ❌ No implementado |
| Permisos/Roles | ❌ No implementado |

---

## 🏥 Agrupaciones Funcionales

### Cuidados y Atención (3)
- CareCategory
- CareClosureDestination
- CareIntervention

### Acciones Clínicas (3)
- ClinicalActionCategory
- ClinicalActionQuestion
- ClinicalActionAnswer

### Prescripciones (8)
- PrescriptionType
- PrescriptionDosage
- PrescriptionFrequency
- PrescriptionRoute
- PrescriptionFormat
- PrescriptionDispensation
- PrescriptionRuleDetail
- DosageType

### Examen Físico (3)
- PhysicalExamGrouping
- PhysicalExamBaseField
- PhysicalExamField

### Nutrición (5)
- NutritionalDiagnosis
- NutritionistBmiIndex
- NutritionistTeIndex
- NutritionistIndexClassification
- EatingDisorderHistory

### Otros (2)
- MedicalDevice
- IntoxicationState

---

## 📝 Notas Técnicas

1. **Multi-tenancy**: Todos los mantenedores usan `TenantEntityManager` para aislar datos por tenant
2. **Soft Delete**: No implementado - Delete es físico
3. **Audit Trail**: No implementado
4. **Ordenamiento**: 
   - Mayoría: `ORDER BY id DESC`
   - Con sortOrder: `ORDER BY sortOrder ASC`
   - PrescriptionRuleDetail: `ORDER BY dailyQuantity ASC`
5. **Búsqueda/Filtros**: No implementado en base
6. **Import masivo**: No implementado
7. **Relaciones**: 
   - CareIntervention → CareCategory
   - ClinicalActionQuestion → ClinicalActionCategory
   - ClinicalActionAnswer → ClinicalActionQuestion
   - PhysicalExamField → PhysicalExamGrouping (x2)

---

## 🚀 Próximos Pasos

1. ✅ Documentación completa de Forms (24/24 completado)
2. Implementar tests unitarios
3. Agregar sistema de permisos por rol
4. Implementar búsqueda/filtros por campos comunes
5. Agregar soft delete
6. Implementar import CSV masivo
7. Agregar validaciones de negocio avanzadas
8. Implementar audit trail para cambios

---

## 🔗 Referencias

- [SPEC_MANTENEDORES_BASIC.md](./SPEC_MANTENEDORES_BASIC.md)
- [AbstractMantenedorController](../../src/Controller/AbstractMantenedorController.php)
- [Documentación Multi-tenancy](../../MULTITENANCY.md)
