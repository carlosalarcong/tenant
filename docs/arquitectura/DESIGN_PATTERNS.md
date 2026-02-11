# 🎨 Patrones de Diseño Recomendados - Melisa Tenant

**Fecha:** Enero 2026  
**Versión:** 1.0  
**Proyecto:** Sistema Multi-Tenant de Gestión Médica

---

## 📋 Tabla de Contenidos

1. [Patrones Ya Implementados](#patrones-ya-implementados)
2. [Patrones Recomendados](#patrones-recomendados)
3. [Prioridad de Implementación](#prioridad-de-implementación)
4. [Patrones a Evitar](#patrones-a-evitar)
5. [Recomendaciones Finales](#recomendaciones-finales)

---

## ✅ Patrones Ya Implementados (Mantener)

### **Repository Pattern**
**Framework:** Doctrine ORM  
**Ubicación:** `src/Repository/`

**Uso actual:**
```php
class PatientRepository extends ServiceEntityRepository
{
    public function findAllForTenant(string $tenant): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.tenant = :tenant')
            ->setParameter('tenant', $tenant)
            ->getQuery()
            ->getResult();
    }
}
```

---

### **Voter Pattern**
**Ubicación:** `src/Security/Voter/PermissionVoter.php`

**Características:**
- Cascada de permisos de 4 niveles
- Prioridad: Usuario > Grupo > Denegar
- Atributos: VIEW, EDIT, DELETE

---

### **Observer/Event Pattern**
**Framework:** Symfony EventDispatcher  
**Ubicación:** `src/EventListener/`, `src/EventSubscriber/`

**Ejemplos:**
- `TenantDatabaseSwitchListener`
- `TenantTranslationListener`
- `AuthenticationListener`

---

### **Strategy Pattern**
**Ubicación:** `src/Service/TenantResolver.php`

**Uso actual:**
```php
class TenantResolver
{
    private array $tenantConfig = [
        'melisahospital' => [...],
        'melisalacolina' => [...],
        'melisawiclinic' => [...]
    ];
}
```

---

### **Dependency Injection**
**Framework:** Symfony DI Container  
**Uso:** Autowiring automático en todos los servicios

---

## 🚀 Patrones Recomendados para el Futuro

### **1. Factory Pattern** 🏭

**Prioridad:** ALTA - Corto Plazo (1-3 meses)  
**Para:** Crear recursos específicos por tenant

#### Implementación Propuesta

```php
namespace App\Factory;

interface DashboardFactory
{
    public function createDashboard(string $tenantType): Dashboard;
}

class TenantDashboardFactory implements DashboardFactory
{
    public function createDashboard(string $tenantType): Dashboard
    {
        return match($tenantType) {
            'hospital' => new EmergencyDashboard(),
            'clinic' => new ConsultationDashboard(),
            'techclinic' => new TelemedDashboard(),
            default => new DefaultDashboard()
        };
    }
}
```

#### Ubicación Sugerida
- `src/Factory/DashboardFactory.php`
- `src/Dashboard/EmergencyDashboard.php`
- `src/Dashboard/ConsultationDashboard.php`

#### Beneficios
- ✅ Creación dinámica de dashboards por tipo de tenant
- ✅ Fácil agregar nuevos tipos sin modificar código existente
- ✅ Centraliza lógica de creación

#### Casos de Uso
- Dashboards personalizados
- Reportes específicos por tenant
- Formularios dinámicos

---

### **2. Builder Pattern** 🏗️

**Prioridad:** MEDIA - Mediano Plazo (3-6 meses)  
**Para:** Construcción compleja de reportes médicos, formularios

#### Implementación Propuesta

```php
namespace App\Builder;

class MedicalReportBuilder
{
    private array $sections = [];
    private array $permissions = [];
    private string $template;
    
    public function withPatientInfo(): self
    {
        $this->sections[] = new PatientInfoSection();
        return $this;
    }
    
    public function withDiagnosis(): self
    {
        $this->sections[] = new DiagnosisSection();
        return $this;
    }
    
    public function withTreatment(): self
    {
        $this->sections[] = new TreatmentSection();
        return $this;
    }
    
    public function forTenant(string $tenant): self
    {
        $this->template = "report/$tenant/template.html.twig";
        return $this;
    }
    
    public function withPermissionCheck(Member $member): self
    {
        $this->permissions = $this->permissionChecker->getFor($member);
        return $this;
    }
    
    public function build(): MedicalReport
    {
        return new MedicalReport(
            $this->sections,
            $this->template,
            $this->permissions
        );
    }
}
```

#### Uso

```php
// En un Controller
$report = $reportBuilder
    ->withPatientInfo()
    ->withDiagnosis()
    ->withTreatment()
    ->forTenant('melisahospital')
    ->withPermissionCheck($currentUser)
    ->build();
```

#### Ubicación Sugerida
- `src/Builder/MedicalReportBuilder.php`
- `src/Report/Section/`

#### Beneficios
- ✅ Reportes personalizados por tenant
- ✅ Construcción fluida y legible
- ✅ Validación de permisos integrada

---

### **3. Specification Pattern** 📋

**Prioridad:** ALTA - Corto Plazo (1-3 meses)  
**Para:** Reglas de negocio complejas, validaciones médicas

#### Implementación Propuesta

```php
namespace App\Specification;

interface Specification
{
    public function isSatisfiedBy($candidate): bool;
}

class PatientCanBeDischargedSpec implements Specification
{
    public function isSatisfiedBy($patient): bool
    {
        return $patient->hasPendingTests() === false
            && $patient->hasApprovedDischarge()
            && $patient->hasCompletedTreatment();
    }
}

class AppointmentCanBeScheduledSpec implements Specification
{
    public function __construct(
        private DoctorAvailabilitySpec $doctorAvailable,
        private RoomAvailabilitySpec $roomAvailable,
        private InsuranceValidSpec $insuranceValid
    ) {}
    
    public function isSatisfiedBy($appointment): bool
    {
        return $this->doctorAvailable->isSatisfiedBy($appointment)
            && $this->roomAvailable->isSatisfiedBy($appointment)
            && $this->insuranceValid->isSatisfiedBy($appointment);
    }
}

// Composición (AND, OR, NOT)
class AndSpec implements Specification
{
    public function __construct(
        private Specification $spec1,
        private Specification $spec2
    ) {}
    
    public function isSatisfiedBy($candidate): bool
    {
        return $this->spec1->isSatisfiedBy($candidate) 
            && $this->spec2->isSatisfiedBy($candidate);
    }
}

class OrSpec implements Specification
{
    public function __construct(
        private Specification $spec1,
        private Specification $spec2
    ) {}
    
    public function isSatisfiedBy($candidate): bool
    {
        return $this->spec1->isSatisfiedBy($candidate) 
            || $this->spec2->isSatisfiedBy($candidate);
    }
}

class NotSpec implements Specification
{
    public function __construct(private Specification $spec) {}
    
    public function isSatisfiedBy($candidate): bool
    {
        return !$this->spec->isSatisfiedBy($candidate);
    }
}
```

#### Uso

```php
// En un Controller o Service
$canDischarge = $patientCanBeDischargedSpec->isSatisfiedBy($patient);

if ($canDischarge) {
    $this->dischargePatient($patient);
}

// Composición compleja
$emergencyAppointment = new AndSpec(
    new OrSpec(
        new HighPrioritySpec(),
        new CriticalConditionSpec()
    ),
    new DoctorAvailableSpec()
);

if ($emergencyAppointment->isSatisfiedBy($appointment)) {
    $this->scheduleEmergency($appointment);
}
```

#### Ubicación Sugerida
- `src/Specification/Patient/`
- `src/Specification/Appointment/`
- `src/Specification/Composite/`

#### Beneficios
- ✅ Reglas de negocio reutilizables
- ✅ Fácil testing unitario
- ✅ Composición de reglas complejas
- ✅ Código autodocumentado

---

### **4. State Pattern** 🔄

**Prioridad:** MEDIA - Mediano Plazo (3-6 meses)  
**Para:** Workflows de pacientes, citas, historiales médicos

#### Implementación Propuesta

```php
namespace App\State\Appointment;

interface AppointmentState
{
    public function schedule(Appointment $appointment): void;
    public function confirm(Appointment $appointment): void;
    public function cancel(Appointment $appointment): void;
    public function complete(Appointment $appointment): void;
    public function getStateName(): string;
}

class ScheduledState implements AppointmentState
{
    public function confirm(Appointment $appointment): void
    {
        $appointment->setState(new ConfirmedState());
        $appointment->sendConfirmationEmail();
        $appointment->notifyDoctor();
    }
    
    public function cancel(Appointment $appointment): void
    {
        $appointment->setState(new CancelledState());
        $appointment->freeResources();
        $appointment->notifyCancellation();
    }
    
    public function schedule(Appointment $appointment): void
    {
        throw new \LogicException('Appointment already scheduled');
    }
    
    public function complete(Appointment $appointment): void
    {
        throw new \LogicException('Must confirm before completing');
    }
    
    public function getStateName(): string
    {
        return 'scheduled';
    }
}

class ConfirmedState implements AppointmentState
{
    public function complete(Appointment $appointment): void
    {
        $appointment->setState(new CompletedState());
        $appointment->createMedicalRecord();
        $appointment->updatePatientHistory();
    }
    
    public function cancel(Appointment $appointment): void
    {
        $appointment->setState(new CancelledState());
        $appointment->freeResources();
        $appointment->applyLateCancellationFee();
    }
    
    public function confirm(Appointment $appointment): void
    {
        throw new \LogicException('Appointment already confirmed');
    }
    
    public function schedule(Appointment $appointment): void
    {
        throw new \LogicException('Cannot reschedule confirmed appointment');
    }
    
    public function getStateName(): string
    {
        return 'confirmed';
    }
}

class CompletedState implements AppointmentState
{
    public function schedule(Appointment $appointment): void
    {
        throw new \LogicException('Cannot modify completed appointment');
    }
    
    public function confirm(Appointment $appointment): void
    {
        throw new \LogicException('Cannot modify completed appointment');
    }
    
    public function cancel(Appointment $appointment): void
    {
        throw new \LogicException('Cannot cancel completed appointment');
    }
    
    public function complete(Appointment $appointment): void
    {
        throw new \LogicException('Appointment already completed');
    }
    
    public function getStateName(): string
    {
        return 'completed';
    }
}

class CancelledState implements AppointmentState
{
    public function schedule(Appointment $appointment): void
    {
        throw new \LogicException('Cannot reschedule cancelled appointment');
    }
    
    public function confirm(Appointment $appointment): void
    {
        throw new \LogicException('Cannot confirm cancelled appointment');
    }
    
    public function cancel(Appointment $appointment): void
    {
        throw new \LogicException('Appointment already cancelled');
    }
    
    public function complete(Appointment $appointment): void
    {
        throw new \LogicException('Cannot complete cancelled appointment');
    }
    
    public function getStateName(): string
    {
        return 'cancelled';
    }
}
```

#### Uso en Entidad

```php
namespace App\Entity;

use App\State\Appointment\AppointmentState;
use App\State\Appointment\ScheduledState;

class Appointment
{
    private AppointmentState $state;
    
    public function __construct()
    {
        $this->state = new ScheduledState();
    }
    
    public function confirm(): void
    {
        $this->state->confirm($this);
    }
    
    public function cancel(): void
    {
        $this->state->cancel($this);
    }
    
    public function complete(): void
    {
        $this->state->complete($this);
    }
    
    public function setState(AppointmentState $state): void
    {
        $this->state = $state;
    }
    
    public function getStateName(): string
    {
        return $this->state->getStateName();
    }
}
```

#### Ubicación Sugerida
- `src/State/Appointment/`
- `src/State/Patient/`
- `src/State/MedicalRecord/`

#### Beneficios
- ✅ Workflows médicos claros
- ✅ Transiciones de estado controladas
- ✅ Auditoría de cambios de estado
- ✅ Validaciones automáticas

---

### **5. Decorator Pattern** 🎨

**Prioridad:** MEDIA - Mediano Plazo (3-6 meses)  
**Para:** Agregar funcionalidad según tenant sin modificar entidades

#### Implementación Propuesta

```php
namespace App\Decorator;

interface PatientInterface
{
    public function getFullInfo(): array;
    public function getDisplayName(): string;
}

class Patient implements PatientInterface
{
    private string $firstName;
    private string $lastName;
    private int $age;
    
    public function getFullInfo(): array
    {
        return [
            'name' => $this->getDisplayName(),
            'age' => $this->age,
        ];
    }
    
    public function getDisplayName(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }
}

class HospitalPatientDecorator implements PatientInterface
{
    public function __construct(private Patient $patient) {}
    
    public function getFullInfo(): array
    {
        $info = $this->patient->getFullInfo();
        
        // Información adicional para hospitales
        $info['bed_number'] = $this->patient->getBedNumber();
        $info['admission_date'] = $this->patient->getAdmissionDate();
        $info['emergency_contact'] = $this->patient->getEmergencyContact();
        $info['medical_alerts'] = $this->patient->getMedicalAlerts();
        $info['attending_doctor'] = $this->patient->getAttendingDoctor();
        
        return $info;
    }
    
    public function getDisplayName(): string
    {
        $bedNumber = $this->patient->getBedNumber();
        return "{$this->patient->getDisplayName()} - Cama #{$bedNumber}";
    }
}

class ClinicPatientDecorator implements PatientInterface
{
    public function __construct(private Patient $patient) {}
    
    public function getFullInfo(): array
    {
        $info = $this->patient->getFullInfo();
        
        // Información adicional para clínicas privadas
        $info['insurance'] = $this->patient->getInsurance();
        $info['preferred_doctor'] = $this->patient->getPreferredDoctor();
        $info['membership_level'] = $this->patient->getMembershipLevel();
        $info['last_consultation'] = $this->patient->getLastConsultation();
        
        return $info;
    }
    
    public function getDisplayName(): string
    {
        $membership = $this->patient->getMembershipLevel();
        return "{$this->patient->getDisplayName()} ({$membership})";
    }
}

class TelemedPatientDecorator implements PatientInterface
{
    public function __construct(private Patient $patient) {}
    
    public function getFullInfo(): array
    {
        $info = $this->patient->getFullInfo();
        
        // Información adicional para telemedicina
        $info['device_compatibility'] = $this->patient->getDeviceCompatibility();
        $info['internet_quality'] = $this->patient->getInternetQuality();
        $info['telemedicine_history'] = $this->patient->getTelemedicineHistory();
        $info['preferred_platform'] = $this->patient->getPreferredPlatform();
        
        return $info;
    }
    
    public function getDisplayName(): string
    {
        return "{$this->patient->getDisplayName()} 💻";
    }
}
```

#### Uso

```php
// En un Controller o Service
public function getPatientView(Patient $patient, string $tenantType): array
{
    $decoratedPatient = match($tenantType) {
        'hospital' => new HospitalPatientDecorator($patient),
        'clinic' => new ClinicPatientDecorator($patient),
        'techclinic' => new TelemedPatientDecorator($patient),
        default => $patient
    };
    
    return $decoratedPatient->getFullInfo();
}
```

#### Ubicación Sugerida
- `src/Decorator/Patient/`
- `src/Decorator/Appointment/`

#### Beneficios
- ✅ Funcionalidad específica por tenant
- ✅ No contamina entidad base
- ✅ Composición flexible
- ✅ Fácil testing

---

### **6. Chain of Responsibility** ⛓️

**Prioridad:** BAJA - Largo Plazo (6-12 meses)  
**Para:** Mejorar la cascada de permisos actual

#### Implementación Propuesta

```php
namespace App\Security\Chain;

abstract class PermissionHandler
{
    protected ?PermissionHandler $next = null;
    
    public function setNext(PermissionHandler $handler): PermissionHandler
    {
        $this->next = $handler;
        return $handler;
    }
    
    abstract public function handle(PermissionRequest $request): ?bool;
}

class SpecificFieldPermissionHandler extends PermissionHandler
{
    public function __construct(private PermissionRepository $repository) {}
    
    public function handle(PermissionRequest $request): ?bool
    {
        // Nivel 1: domain + resourceId + fieldName (MÁS ESPECÍFICO)
        $permission = $this->repository->findOneBy([
            'domain' => $request->domain,
            'resourceId' => $request->resourceId,
            'fieldName' => $request->fieldName,
            'member' => $request->member
        ]);
        
        if ($permission !== null) {
            return $permission->isAllowed();
        }
        
        // Pasar al siguiente handler
        return $this->next?->handle($request);
    }
}

class ResourcePermissionHandler extends PermissionHandler
{
    public function __construct(private PermissionRepository $repository) {}
    
    public function handle(PermissionRequest $request): ?bool
    {
        // Nivel 2: domain + resourceId + NULL
        $permission = $this->repository->findOneBy([
            'domain' => $request->domain,
            'resourceId' => $request->resourceId,
            'fieldName' => null,
            'member' => $request->member
        ]);
        
        if ($permission !== null) {
            return $permission->isAllowed();
        }
        
        return $this->next?->handle($request);
    }
}

class DomainFieldPermissionHandler extends PermissionHandler
{
    public function __construct(private PermissionRepository $repository) {}
    
    public function handle(PermissionRequest $request): ?bool
    {
        // Nivel 3: domain + NULL + fieldName
        $permission = $this->repository->findOneBy([
            'domain' => $request->domain,
            'resourceId' => null,
            'fieldName' => $request->fieldName,
            'member' => $request->member
        ]);
        
        if ($permission !== null) {
            return $permission->isAllowed();
        }
        
        return $this->next?->handle($request);
    }
}

class DomainPermissionHandler extends PermissionHandler
{
    public function __construct(private PermissionRepository $repository) {}
    
    public function handle(PermissionRequest $request): ?bool
    {
        // Nivel 4: domain + NULL + NULL (MÁS GENERAL)
        $permission = $this->repository->findOneBy([
            'domain' => $request->domain,
            'resourceId' => null,
            'fieldName' => null,
            'member' => $request->member
        ]);
        
        if ($permission !== null) {
            return $permission->isAllowed();
        }
        
        return $this->next?->handle($request);
    }
}

class DenyByDefaultHandler extends PermissionHandler
{
    public function handle(PermissionRequest $request): ?bool
    {
        // Último recurso: denegar
        return false;
    }
}
```

#### Construcción de la Cadena

```php
namespace App\Security;

class PermissionChainFactory
{
    public function createChain(
        PermissionRepository $repository
    ): PermissionHandler {
        $chain = new SpecificFieldPermissionHandler($repository);
        
        $chain->setNext(new ResourcePermissionHandler($repository))
              ->setNext(new DomainFieldPermissionHandler($repository))
              ->setNext(new DomainPermissionHandler($repository))
              ->setNext(new DenyByDefaultHandler());
        
        return $chain;
    }
}
```

#### Uso

```php
// En PermissionVoter refactorizado
class PermissionVoter extends Voter
{
    public function __construct(
        private PermissionChainFactory $chainFactory,
        private PermissionRepository $repository
    ) {}
    
    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token
    ): bool {
        $member = $token->getUser();
        $chain = $this->chainFactory->createChain($this->repository);
        
        $request = new PermissionRequest(
            domain: $subject->getDomain(),
            resourceId: $subject->getId(),
            fieldName: $subject instanceof FieldAccess ? $subject->getFieldName() : null,
            attribute: $attribute,
            member: $member
        );
        
        return $chain->handle($request) ?? false;
    }
}
```

#### Ubicación Sugerida
- `src/Security/Chain/`

#### Beneficios
- ✅ Cascada explícita y ordenada
- ✅ Fácil agregar/quitar niveles
- ✅ Mejor debugging
- ✅ Separación de responsabilidades

---

### **7. Adapter Pattern** 🔌

**Prioridad:** BAJA - Largo Plazo (6-12 meses)  
**Para:** Integración con sistemas externos (FONASA, ISAPRES, otros hospitales)

#### Implementación Propuesta

```php
namespace App\Adapter\Insurance;

interface InsuranceProvider
{
    public function validateCoverage(Patient $patient, Procedure $procedure): bool;
    public function getCoveragePercentage(Patient $patient, Procedure $procedure): float;
    public function getAuthorization(Patient $patient, Procedure $procedure): AuthorizationResult;
}

class FonasaAdapter implements InsuranceProvider
{
    public function __construct(
        private FonasaApiClient $fonasaClient,
        private LoggerInterface $logger
    ) {}
    
    public function validateCoverage(Patient $patient, Procedure $procedure): bool
    {
        try {
            // Adapta la respuesta de FONASA al formato estándar
            $response = $this->fonasaClient->verificarCobertura(
                rut: $patient->getRut(),
                codigoPrestacion: $procedure->getCode()
            );
            
            $this->logger->info('FONASA coverage check', [
                'patient' => $patient->getRut(),
                'procedure' => $procedure->getCode(),
                'covered' => $response['cobertura'] === 'SI'
            ]);
            
            return $response['cobertura'] === 'SI';
            
        } catch (\Exception $e) {
            $this->logger->error('FONASA API error', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function getCoveragePercentage(Patient $patient, Procedure $procedure): float
    {
        $response = $this->fonasaClient->obtenerPorcentajeCobertura(
            $patient->getRut(),
            $procedure->getCode()
        );
        
        // FONASA retorna "80%" como string, convertir a float 0.80
        return (float) str_replace('%', '', $response['porcentaje']) / 100;
    }
    
    public function getAuthorization(Patient $patient, Procedure $procedure): AuthorizationResult
    {
        $response = $this->fonasaClient->solicitarAutorizacion([
            'rut_paciente' => $patient->getRut(),
            'codigo_prestacion' => $procedure->getCode(),
            'fecha_solicitud' => new \DateTime()
        ]);
        
        return new AuthorizationResult(
            approved: $response['estado'] === 'APROBADO',
            authorizationCode: $response['codigo_autorizacion'] ?? null,
            expirationDate: $response['fecha_vencimiento'] ?? null,
            message: $response['mensaje'] ?? ''
        );
    }
}

class IsapreAdapter implements InsuranceProvider
{
    public function __construct(
        private IsapreApiClient $isapreClient,
        private LoggerInterface $logger
    ) {}
    
    public function validateCoverage(Patient $patient, Procedure $procedure): bool
    {
        try {
            // ISAPRE tiene formato diferente
            $result = $this->isapreClient->checkBenefit(
                rut: $patient->getRut(),
                serviceCode: $procedure->getCode(),
                planId: $patient->getInsurancePlanId()
            );
            
            $this->logger->info('ISAPRE coverage check', [
                'patient' => $patient->getRut(),
                'procedure' => $procedure->getCode(),
                'covered' => $result->isCovered
            ]);
            
            return $result->isCovered;
            
        } catch (\Exception $e) {
            $this->logger->error('ISAPRE API error', ['error' => $e->getMessage()]);
            return false;
        }
    }
    
    public function getCoveragePercentage(Patient $patient, Procedure $procedure): float
    {
        $result = $this->isapreClient->getBenefitDetail(
            $patient->getRut(),
            $procedure->getCode()
        );
        
        // ISAPRE retorna decimal directamente
        return $result->coverageRate;
    }
    
    public function getAuthorization(Patient $patient, Procedure $procedure): AuthorizationResult
    {
        $result = $this->isapreClient->requestAuthorization([
            'patientRut' => $patient->getRut(),
            'procedureCode' => $procedure->getCode(),
            'planId' => $patient->getInsurancePlanId(),
            'requestDate' => date('Y-m-d')
        ]);
        
        return new AuthorizationResult(
            approved: $result->status === 'APPROVED',
            authorizationCode: $result->authCode,
            expirationDate: $result->validUntil,
            message: $result->notes
        );
    }
}

class PrivateInsuranceAdapter implements InsuranceProvider
{
    public function validateCoverage(Patient $patient, Procedure $procedure): bool
    {
        // Para seguros privados internacionales
        return true; // Asumen cobertura, verifican después
    }
    
    public function getCoveragePercentage(Patient $patient, Procedure $procedure): float
    {
        // Porcentaje estándar para seguros privados
        return 0.90; // 90% de cobertura
    }
    
    public function getAuthorization(Patient $patient, Procedure $procedure): AuthorizationResult
    {
        return new AuthorizationResult(
            approved: true,
            authorizationCode: 'PRIVATE-' . uniqid(),
            expirationDate: new \DateTime('+1 year'),
            message: 'Pre-authorized by private insurance'
        );
    }
}
```

#### Factory para Adapters

```php
namespace App\Factory;

class InsuranceProviderFactory
{
    public function create(string $insuranceType): InsuranceProvider
    {
        return match($insuranceType) {
            'FONASA' => new FonasaAdapter($this->fonasaClient, $this->logger),
            'ISAPRE' => new IsapreAdapter($this->isapreClient, $this->logger),
            'PRIVATE' => new PrivateInsuranceAdapter(),
            default => throw new \InvalidArgumentException("Unknown insurance type: $insuranceType")
        };
    }
}
```

#### Uso

```php
// En un Service
class CoverageValidationService
{
    public function __construct(
        private InsuranceProviderFactory $providerFactory
    ) {}
    
    public function validateProcedure(Patient $patient, Procedure $procedure): bool
    {
        $provider = $this->providerFactory->create($patient->getInsuranceType());
        
        return $provider->validateCoverage($patient, $procedure);
    }
    
    public function calculatePatientCost(Patient $patient, Procedure $procedure): float
    {
        $provider = $this->providerFactory->create($patient->getInsuranceType());
        
        $coverage = $provider->getCoveragePercentage($patient, $procedure);
        $totalCost = $procedure->getCost();
        
        return $totalCost * (1 - $coverage);
    }
}
```

#### Ubicación Sugerida
- `src/Adapter/Insurance/`
- `src/Adapter/Laboratory/`
- `src/Adapter/Pharmacy/`

#### Beneficios
- ✅ Interfaz unificada para múltiples proveedores
- ✅ Fácil cambiar/agregar proveedores
- ✅ Aísla lógica de integración
- ✅ Facilita testing con mocks

---

### **8. Template Method Pattern** 🧩

**Prioridad:** ALTA - Corto Plazo (1-3 meses)  
**Para:** Controllers con flujo común pero pasos específicos

#### Implementación Propuesta

```php
namespace App\Controller;

abstract class AbstractMantenedorController extends AbstractTenantAwareController
{
    // Template method (define el flujo)
    final public function index(): Response
    {
        $this->beforeIndex();
        
        $data = $this->getData();
        $processedData = $this->processData($data);
        
        $this->afterIndex();
        
        return $this->render($this->getTemplatePath(), [
            'data' => $processedData,
            'columns' => $this->getColumns(),
            'actions' => $this->getActions(),
            'tenant' => $this->getTenant(),
            'pagination' => $this->getPagination()
        ]);
    }
    
    final public function create(Request $request): Response
    {
        $this->beforeCreate();
        
        $entity = $this->createNewEntity();
        $form = $this->createForm($this->getFormType(), $entity);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->beforeSave($entity);
            $this->save($entity);
            $this->afterSave($entity);
            
            return $this->redirectToRoute($this->getIndexRoute());
        }
        
        return $this->render($this->getFormTemplatePath(), [
            'form' => $form->createView(),
            'entity' => $entity
        ]);
    }
    
    // Hooks que subclases pueden sobreescribir
    protected function beforeIndex(): void {}
    protected function afterIndex(): void {}
    protected function beforeCreate(): void {}
    protected function beforeSave($entity): void {}
    protected function afterSave($entity): void {}
    
    // Métodos abstractos que subclases DEBEN implementar
    abstract protected function getData(): array;
    abstract protected function getColumns(): array;
    abstract protected function getTemplatePath(): string;
    abstract protected function getFormType(): string;
    abstract protected function createNewEntity(): object;
    
    // Métodos con implementación por defecto (pueden sobreescribirse)
    protected function processData(array $data): array
    {
        return $data; // Por defecto no procesa
    }
    
    protected function getActions(): array
    {
        return ['view', 'edit', 'delete']; // Por defecto
    }
    
    protected function getPagination(): array
    {
        return [
            'page' => 1,
            'limit' => 20,
            'total' => count($this->getData())
        ];
    }
    
    protected function getFormTemplatePath(): string
    {
        return str_replace('index', 'form', $this->getTemplatePath());
    }
    
    protected function getIndexRoute(): string
    {
        $controller = (new \ReflectionClass($this))->getShortName();
        $controller = str_replace('Controller', '', $controller);
        return 'app_' . strtolower($controller) . '_index';
    }
    
    protected function save($entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
```

#### Implementación Concreta: CountryController (Mantenedor)

```php
namespace App\Controller\Mantenedores;

use App\Entity\Country;
use App\Form\CountryType;
use App\Repository\CountryRepository;

/**
 * Los mantenedores son datos maestros compartidos por todos los tenants
 * No requieren filtros específicos por tenant
 */
class CountryController extends AbstractMantenedorController
{
    public function __construct(
        private CountryRepository $countryRepository
    ) {}
    
    protected function getData(): array
    {
        // Todos los países - sin filtrar por tenant
        return $this->countryRepository->findAll();
    }
    
    protected function getColumns(): array
    {
        return ['name', 'iso_code', 'phone_code', 'active'];
    }
    
    protected function getTemplatePath(): string
    {
        return 'mantenedores/country/index.html.twig';
    }
    
    protected function getFormType(): string
    {
        return CountryType::class;
    }
    
    protected function createNewEntity(): Country
    {
        return new Country();
    }
    
    // Hook: Validación antes de guardar
    protected function beforeSave($entity): void
    {
        // Normalizar código ISO a mayúsculas
        if ($entity instanceof Country) {
            $entity->setIsoCode(strtoupper($entity->getIsoCode()));
        }
    }
    
    // Hook: Acciones después de guardar
    protected function afterSave($entity): void
    {
        $this->addFlash('success', 'País guardado correctamente');
    }
}
```

#### Implementación Concreta: PatientController (Con filtros por tenant)

```php
namespace App\Controller;

use App\Entity\Patient;
use App\Form\PatientType;
use App\Repository\PatientRepository;

/**
 * Los pacientes SÍ son específicos por tenant
 * Cada tenant solo ve sus propios pacientes
 */
class PatientController extends AbstractMantenedorController
{
    public function __construct(
        private PatientRepository $patientRepository
    ) {}
    
    protected function getData(): array
    {
        // Solo pacientes del tenant actual
        return $this->patientRepository->findAllForTenant($this->getTenant());
    }
    
    protected function getColumns(): array
    {
        return ['name', 'age', 'lastVisit', 'status'];
    }
    
    protected function getTemplatePath(): string
    {
        return "patient/{$this->getTenantSubdomain()}/index.html.twig";
    }
    
    protected function getFormType(): string
    {
        return PatientType::class;
    }
    
    protected function createNewEntity(): Patient
    {
        $patient = new Patient();
        $patient->setTenant($this->getTenant());
        return $patient;
    }
    
    // Hook: Procesar datos según tipo de tenant (opcional)
    protected function processData(array $data): array
    {
        // Hospital: Ordenar por urgencia
        if ($this->getTenantType() === 'hospital') {
            usort($data, fn($a, $b) => $b->getUrgencyLevel() <=> $a->getUrgencyLevel());
        }
        
        // Clínica: Ordenar por próxima cita
        if ($this->getTenantType() === 'clinic') {
            usort($data, fn($a, $b) => $a->getNextAppointment() <=> $b->getNextAppointment());
        }
        
        return $data;
    }
    
    // Hook: Acciones después de guardar
    protected function afterSave($entity): void
    {
        $this->addFlash('success', 'Paciente guardado correctamente');
    }
}
```

#### Ubicación Sugerida
- `src/Controller/AbstractMantenedorController.php`

#### Beneficios
- ✅ Código DRY (no repetir flujo común)
- ✅ Personalización fácil por tenant
- ✅ Estructura consistente
- ✅ Fácil mantenimiento

---

### **9. Command Pattern** 💼

**Prioridad:** BAJA - Largo Plazo (6-12 meses)  
**Para:** Operaciones complejas auditables

#### Implementación Propuesta

```php
namespace App\Command\Medical;

interface MedicalCommand
{
    public function execute(): void;
    public function undo(): void;
    public function getDescription(): string;
}

class AdmitPatientCommand implements MedicalCommand
{
    private ?string $previousStatus = null;
    private ?int $assignedBed = null;
    
    public function __construct(
        private Patient $patient,
        private int $bedNumber,
        private string $admissionReason,
        private Member $admittedBy,
        private EntityManagerInterface $entityManager
    ) {}
    
    public function execute(): void
    {
        $this->previousStatus = $this->patient->getStatus();
        
        $this->patient->setStatus('ADMITTED');
        $this->patient->setBedNumber($this->bedNumber);
        $this->patient->setAdmissionDate(new \DateTime());
        $this->patient->setAdmissionReason($this->admissionReason);
        $this->patient->setAdmittedBy($this->admittedBy);
        
        $this->assignedBed = $this->bedNumber;
        
        $this->entityManager->persist($this->patient);
        $this->entityManager->flush();
    }
    
    public function undo(): void
    {
        if ($this->previousStatus === null) {
            throw new \LogicException('Cannot undo: command not executed');
        }
        
        $this->patient->setStatus($this->previousStatus);
        $this->patient->setBedNumber(null);
        $this->patient->setAdmissionDate(null);
        $this->patient->setAdmissionReason(null);
        
        $this->entityManager->flush();
    }
    
    public function getDescription(): string
    {
        return "Admit patient {$this->patient->getFullName()} to bed #{$this->bedNumber}";
    }
}

class DischargePatientCommand implements MedicalCommand
{
    private ?array $previousData = null;
    
    public function __construct(
        private Patient $patient,
        private string $dischargeReason,
        private Member $dischargedBy,
        private EntityManagerInterface $entityManager
    ) {}
    
    public function execute(): void
    {
        $this->previousData = [
            'status' => $this->patient->getStatus(),
            'bed' => $this->patient->getBedNumber(),
            'admission_date' => $this->patient->getAdmissionDate()
        ];
        
        $this->patient->setStatus('DISCHARGED');
        $this->patient->setBedNumber(null);
        $this->patient->setDischargeDate(new \DateTime());
        $this->patient->setDischargeReason($this->dischargeReason);
        $this->patient->setDischargedBy($this->dischargedBy);
        
        $this->entityManager->flush();
    }
    
    public function undo(): void
    {
        if ($this->previousData === null) {
            throw new \LogicException('Cannot undo: command not executed');
        }
        
        $this->patient->setStatus($this->previousData['status']);
        $this->patient->setBedNumber($this->previousData['bed']);
        $this->patient->setDischargeDate(null);
        
        $this->entityManager->flush();
    }
    
    public function getDescription(): string
    {
        return "Discharge patient {$this->patient->getFullName()}";
    }
}
```

#### Command Invoker (con Auditoría)

```php
namespace App\Service;

class MedicalCommandInvoker
{
    private array $history = [];
    
    public function __construct(
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger
    ) {}
    
    public function execute(MedicalCommand $command): void
    {
        $description = $command->getDescription();
        
        $this->logger->info("Executing command: $description");
        
        try {
            $command->execute();
            $this->history[] = $command;
            
            // Auditoría
            $this->entityManager->persist(new AuditLog(
                action: $description,
                timestamp: new \DateTime(),
                user: $this->getUser()
            ));
            $this->entityManager->flush();
            
        } catch (\Exception $e) {
            $this->logger->error("Command failed: $description", [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    public function undo(): void
    {
        if (empty($this->history)) {
            throw new \LogicException('No commands to undo');
        }
        
        $command = array_pop($this->history);
        $command->undo();
        
        $this->logger->info("Undone command: {$command->getDescription()}");
    }
    
    public function getHistory(): array
    {
        return $this->history;
    }
}
```

#### Uso

```php
// En un Controller
public function admitPatient(Patient $patient, Request $request): Response
{
    $command = new AdmitPatientCommand(
        patient: $patient,
        bedNumber: $request->get('bed_number'),
        admissionReason: $request->get('reason'),
        admittedBy: $this->getUser(),
        entityManager: $this->entityManager
    );
    
    $this->commandInvoker->execute($command);
    
    return $this->redirectToRoute('patient_view', ['id' => $patient->getId()]);
}

// Deshacer última operación
public function undoLastOperation(): Response
{
    try {
        $this->commandInvoker->undo();
        $this->addFlash('success', 'Operación deshecha');
    } catch (\LogicException $e) {
        $this->addFlash('error', $e->getMessage());
    }
    
    return $this->redirectToRoute('dashboard');
}
```

#### Ubicación Sugerida
- `src/Command/Medical/`
- `src/Service/MedicalCommandInvoker.php`

#### Beneficios
- ✅ Operaciones auditables
- ✅ Undo/Redo de operaciones
- ✅ Logging automático
- ✅ Transacciones complejas

---

## 📊 Prioridad de Implementación

### **Corto Plazo (1-3 meses)** 🚀

| Patrón | Prioridad | Complejidad | Impacto | Casos de Uso |
|--------|-----------|-------------|---------|--------------|
| **Factory Pattern** | ⭐⭐⭐ | 🟢 Baja | 🔥 Alto | Dashboards, reportes por tenant |
| **Template Method** | ⭐⭐⭐ | 🟢 Baja | 🔥 Alto | Estandarizar controllers |
| **Specification Pattern** | ⭐⭐⭐ | 🟡 Media | 🔥 Alto | Reglas de negocio médicas |

**Justificación:**
- Factory y Template Method son simples de implementar y dan gran beneficio inmediato
- Specification resuelve validaciones complejas de forma elegante
- Todos mejoran la estructura actual sin refactorizar mucho código

---

### **Mediano Plazo (3-6 meses)** 📈

| Patrón | Prioridad | Complejidad | Impacto | Casos de Uso |
|--------|-----------|-------------|---------|--------------|
| **State Pattern** | ⭐⭐ | 🟡 Media | 🔥 Alto | Workflows de citas y pacientes |
| **Builder Pattern** | ⭐⭐ | 🟡 Media | 🟡 Medio | Reportes médicos complejos |
| **Decorator Pattern** | ⭐⭐ | 🟡 Media | 🟡 Medio | Extender funcionalidad por tenant |

**Justificación:**
- State Pattern fundamental para workflows médicos
- Builder útil cuando tengas reportes más complejos
- Decorator permite personalización sin contaminar entidades

---

### **Largo Plazo (6-12 meses)** 🎯

| Patrón | Prioridad | Complejidad | Impacto | Casos de Uso |
|--------|-----------|-------------|---------|--------------|
| **Adapter Pattern** | ⭐ | 🔴 Alta | 🔥 Alto | Integraciones FONASA, ISAPRE |
| **Chain of Responsibility** | ⭐ | 🟡 Media | 🟡 Medio | Refactorizar cascada permisos |
| **Command Pattern** | ⭐ | 🔴 Alta | 🟡 Medio | Operaciones auditables, undo/redo |

**Justificación:**
- Adapter necesario solo cuando integres sistemas externos
- Chain of Responsibility puede esperar (tu Voter actual funciona bien)
- Command útil para auditoría avanzada, pero no crítico

---

## ❌ Patrones a EVITAR

### **Singleton Pattern** 🚫
**Motivo:** Symfony DI Container ya gestiona instancias únicas

```php
// ❌ NO HACER ESTO
class DatabaseConnection
{
    private static ?DatabaseConnection $instance = null;
    
    private function __construct() {}
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

// ✅ HACER ESTO (Symfony DI)
services:
    App\Service\DatabaseConnection:
        shared: true  # Es singleton por defecto
```

---

### **Service Locator Pattern** 🚫
**Motivo:** Anti-pattern, usar Dependency Injection

```php
// ❌ NO HACER ESTO
class MyController
{
    public function index(ContainerInterface $container): Response
    {
        $service = $container->get('my_service');  // ❌ Service Locator
    }
}

// ✅ HACER ESTO
class MyController
{
    public function __construct(
        private MyService $myService  // ✅ Dependency Injection
    ) {}
    
    public function index(): Response
    {
        $this->myService->doSomething();
    }
}
```

---

### **God Object Pattern** 🚫
**Motivo:** Viola Single Responsibility Principle

```php
// ❌ NO HACER ESTO
class PatientManager
{
    public function createPatient() {}
    public function updatePatient() {}
    public function deletePatient() {}
    public function sendEmail() {}
    public function validateInsurance() {}
    public function generateReport() {}
    public function processPayment() {}
    public function scheduleAppointment() {}
    // ... 50 métodos más
}

// ✅ HACER ESTO (Separar responsabilidades)
class PatientRepository {}       // Persistencia
class EmailService {}            // Emails
class InsuranceValidator {}      // Validación seguros
class ReportGenerator {}         // Reportes
class PaymentProcessor {}        // Pagos
class AppointmentScheduler {}    // Citas
```

---

### **Anemic Domain Model** 🚫
**Motivo:** Entidades deben tener lógica de negocio

```php
// ❌ NO HACER ESTO (Entidad sin lógica)
class Patient
{
    private string $status;
    
    public function getStatus(): string { return $this->status; }
    public function setStatus(string $status): void { $this->status = $status; }
}

// Lógica fuera de la entidad
class PatientService
{
    public function discharge(Patient $patient): void
    {
        $patient->setStatus('DISCHARGED');  // ❌ Lógica fuera
    }
}

// ✅ HACER ESTO (Rich Domain Model)
class Patient
{
    private string $status;
    private ?\DateTime $dischargeDate = null;
    
    public function discharge(string $reason): void
    {
        if ($this->status !== 'ADMITTED') {
            throw new \LogicException('Cannot discharge non-admitted patient');
        }
        
        $this->status = 'DISCHARGED';
        $this->dischargeDate = new \DateTime();
        $this->dischargeReason = $reason;
    }
    
    public function isAdmitted(): bool
    {
        return $this->status === 'ADMITTED';
    }
}
```

---

## 💡 Recomendaciones Finales

### **1. No Sobre-ingeniería** 🎯
- Implementar patrones solo cuando resuelven un problema **real**
- YAGNI: "You Aren't Gonna Need It"
- Empezar simple, refactorizar cuando sea necesario

### **2. Comenzar Simple** 🌱
- Factory y Template Method son los más útiles **ahora**
- Dan gran beneficio con poca complejidad
- Fáciles de entender para todo el equipo

### **3. Documentar Decisiones** 📝
- Actualizar `ARCHITECTURE.md` con patrones implementados
- Documentar **por qué** se eligió cada patrón
- Incluir ejemplos de uso en comentarios

### **4. Tests Primero** ✅
- Cada patrón debe tener **tests unitarios**
- TDD: Write test → Implement pattern → Refactor
- Mínimo 80% de cobertura en código crítico

### **5. Revisar Periódicamente** 🔄
- Evaluar cada 3 meses si los patrones siguen siendo útiles
- Refactorizar patrones que no aporten valor
- Estar abierto a cambiar decisiones

### **6. Code Reviews** 👥
- Revisar implementación de patrones en Pull Requests
- Verificar que se siguen las mejores prácticas
- Compartir conocimiento en el equipo

### **7. Performance** ⚡
- Medir impacto de patrones en performance
- Usar Symfony Profiler para detectar cuellos de botella
- Optimizar patrones que afecten negativamente

### **8. Mantener Consistencia** 🎨
- Una vez elegido un patrón, usarlo consistentemente
- Evitar mezclar múltiples soluciones para el mismo problema
- Crear guías de estilo para el equipo

---

## 📚 Referencias

### **Libros Recomendados**
- "Design Patterns: Elements of Reusable Object-Oriented Software" - Gang of Four
- "Patterns of Enterprise Application Architecture" - Martin Fowler
- "Domain-Driven Design" - Eric Evans
- "Clean Architecture" - Robert C. Martin

### **Recursos Online**
- [Refactoring Guru - Design Patterns](https://refactoring.guru/design-patterns)
- [Symfony Best Practices](https://symfony.com/doc/current/best_practices.html)
- [PHP The Right Way](https://phptherightway.com/)

### **Comunidad**
- [Symfony Slack](https://symfony.com/slack)
- [Stack Overflow - Symfony Tag](https://stackoverflow.com/questions/tagged/symfony)

---

**Última actualización:** Enero 2026  
**Autor:** Equipo Melisa Tenant  
**Versión:** 1.0
