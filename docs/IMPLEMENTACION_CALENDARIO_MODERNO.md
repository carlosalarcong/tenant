# 🗓️ Implementación Calendario Moderno - Symfony 7

## 📋 Resumen del Proyecto

Migración del sistema de agenda médica desde **FullCalendar v1.6.2 (2013)** a una solución moderna con:

- ✅ **FullCalendar v6.x** (última versión, gratis con MIT License)
- ✅ **Symfony 7** + **PHP 8.3+**
- ✅ **Stimulus** (controladores JavaScript modernos)
- ✅ **Turbo** (actualizaciones sin recargar página)
- ✅ **Mercure** (tiempo real para múltiples usuarios)
- ✅ **Animaciones fluidas** con CSS moderno
- ✅ **Drag & Drop** avanzado con validaciones
- ✅ **Sistema de sobrecupos** inteligente

---

## 🎯 Funcionalidades Principales

### 1. **Vista de Calendario Compacta (Opción B+C)**
- Bloques visuales con contador de cupos: `👥 3/5 pacientes`
- Colores dinámicos según disponibilidad:
  - 🟢 **Verde**: Cupos disponibles
  - 🟡 **Amarillo**: Pocos cupos (≤2)
  - 🔴 **Rojo**: Lleno o sobrecupo
- Click en bloque → Modal con detalle completo

### 2. **Drag & Drop Fluido**
- ✅ Arrastrar pacientes desde lista de espera → calendario
- ✅ Arrastrar citas existentes entre días/horas
- ✅ Animaciones suaves con CSS transitions
- ✅ Feedback visual (sombras, opacidad, indicadores)
- ✅ Validaciones en tiempo real

### 3. **Sistema de Sobrecupos**
- ⚠️ Detección automática de cupos llenos
- ✅ Confirmación antes de crear sobrecupo
- 🎨 Estilo visual distintivo (patrón rayado rojo)
- 📊 Badge "SOBRECUPO" visible

### 4. **Modal Detallado**
- Lista de pacientes confirmados (✅)
- Cupos disponibles (⭕)
- Botón "Agregar Paciente"
- Opciones de eliminar/mover pacientes

### 5. **Tiempo Real con Mercure**
- Actualizaciones instantáneas entre usuarios
- Notificaciones de cambios (agendas, movimientos, sobrecupos)
- Sin necesidad de recargar la página

---

## 🏗️ Arquitectura Técnica

```
┌─────────────────────────────────────────────────────────────┐
│                    FRONTEND (Assets)                        │
├─────────────────────────────────────────────────────────────┤
│  assets/                                                    │
│  ├── controllers/                                           │
│  │   └── calendar_controller.js    (Stimulus)              │
│  ├── styles/                                                │
│  │   └── calendar.css              (Animaciones)           │
│  └── app.js                         (Bootstrap)            │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    BACKEND (Symfony 7)                      │
├─────────────────────────────────────────────────────────────┤
│  src/                                                       │
│  ├── Controller/                                            │
│  │   └── CalendarController.php    (API Endpoints)         │
│  ├── Entity/                                                │
│  │   ├── Slot.php                  (Bloques de tiempo)     │
│  │   ├── Appointment.php           (Citas)                 │
│  │   └── Patient.php               (Pacientes)             │
│  └── Service/                                               │
│      ├── CalendarService.php       (Lógica de negocio)     │
│      └── MercureNotifier.php       (Tiempo real)           │
└─────────────────────────────────────────────────────────────┘
                            ↕
┌─────────────────────────────────────────────────────────────┐
│                    MERCURE HUB                              │
│  Notificaciones en Tiempo Real                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 📦 Dependencias a Instalar

### NPM (Frontend)
```bash
npm install @hotwired/stimulus @hotwired/turbo
npm install @fullcalendar/core \
  @fullcalendar/daygrid \
  @fullcalendar/timegrid \
  @fullcalendar/interaction \
  @fullcalendar/list
```

### Composer (Backend)
```bash
composer require symfony/ux-turbo-mercure
composer require symfony/stimulus-bundle
composer require symfony/asset-mapper
```

---

## 🎨 Animaciones CSS Modernas

### Drag & Drop Suave
```css
/* Transiciones fluidas */
.fc-event {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.fc-event:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Mientras se arrastra */
.fc-event-dragging {
  opacity: 0.7;
  transform: scale(1.05) rotate(2deg);
  box-shadow: 0 8px 24px rgba(0,0,0,0.25);
  transition: transform 0.2s ease;
}

/* Drop animation */
@keyframes dropSuccess {
  0% { transform: scale(1.1); }
  50% { transform: scale(0.95); }
  100% { transform: scale(1); }
}

.fc-event-dropped {
  animation: dropSuccess 0.4s ease;
}
```

### Indicadores Visuales Durante Drag
```css
/* Slot disponible */
.fc-timegrid-slot.available {
  background: rgba(40, 167, 69, 0.08);
  border: 2px dashed #28a745;
  transition: all 0.2s ease;
}

/* Slot lleno (sobrecupo) */
.fc-timegrid-slot.full {
  background: rgba(220, 53, 69, 0.08);
  border: 2px dashed #dc3545;
  animation: pulse 1.5s ease infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}
```

### Sobrecupos con Estilo Visual
```css
.fc-event.sobrecupo {
  background: repeating-linear-gradient(
    45deg,
    #dc3545,
    #dc3545 10px,
    #ff6b7a 10px,
    #ff6b7a 20px
  ) !important;
  border: 2px solid #dc3545 !important;
  animation: warningPulse 2s ease infinite;
}

@keyframes warningPulse {
  0%, 100% { 
    box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
  }
  50% { 
    box-shadow: 0 0 20px rgba(220, 53, 69, 0.8);
  }
}

.fc-event.sobrecupo::after {
  content: '⚠️ SOBRECUPO';
  position: absolute;
  top: 2px;
  right: 2px;
  font-size: 9px;
  font-weight: bold;
  background: #fff;
  padding: 2px 4px;
  border-radius: 3px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}
```

### Modal con Animación de Entrada
```css
.modal-backdrop {
  animation: fadeIn 0.3s ease;
}

.modal-dialog {
  animation: slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideUp {
  from { 
    opacity: 0;
    transform: translateY(50px);
  }
  to { 
    opacity: 1;
    transform: translateY(0);
  }
}
```

---

## 🔧 Configuración FullCalendar v6

### Stimulus Controller Completo
```javascript
// assets/controllers/calendar_controller.js
import { Controller } from '@hotwired/stimulus';
import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import { Draggable } from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

export default class extends Controller {
  static targets = ['calendar', 'patientList'];
  static values = {
    eventsUrl: String,
    doctorId: Number,
    especialidadId: Number
  };
  
  connect() {
    this.initCalendar();
    this.initDraggablePatients();
    this.initMercureListener();
  }
  
  initCalendar() {
    this.calendar = new Calendar(this.calendarTarget, {
      plugins: [timeGridPlugin, dayGridPlugin, interactionPlugin],
      initialView: 'timeGridWeek',
      locale: esLocale,
      
      // Configuración visual
      headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      
      // Horarios
      slotMinutes: 15,
      slotDuration: '00:15:00',
      snapDuration: '00:15:00',
      firstDay: 1, // Lunes
      allDaySlot: false,
      slotMinTime: '08:00:00',
      slotMaxTime: '20:00:00',
      
      // Interactividad
      editable: true,
      droppable: true,
      selectable: true,
      
      // Fuente de eventos
      events: this.eventsUrlValue,
      
      // Personalización visual
      eventContent: (arg) => this.renderEventContent(arg),
      
      // Eventos de interacción
      eventClick: (info) => this.handleEventClick(info),
      eventDrop: (info) => this.handleEventDrop(info),
      eventReceive: (info) => this.handleEventReceive(info),
      select: (info) => this.handleSelect(info),
      
      // Animaciones
      eventDragStart: (info) => this.handleDragStart(info),
      eventDragStop: (info) => this.handleDragStop(info),
      
      // Altura responsive
      height: 'auto',
      contentHeight: 650,
      
      // Configuración de drag
      eventStartEditable: true,
      eventDurationEditable: false,
      dragRevertDuration: 300,
      dragScroll: true,
      
      // Callbacks adicionales
      loading: (isLoading) => this.handleLoading(isLoading),
      eventDidMount: (info) => this.enhanceEventAppearance(info)
    });
    
    this.calendar.render();
  }
  
  renderEventContent(arg) {
    const { cuposOcupados, cuposTotal, doctorNombre } = arg.event.extendedProps;
    const disponibles = cuposTotal - cuposOcupados;
    
    // Determinar color
    let colorClass = 'disponible';
    if (disponibles === 0) colorClass = 'lleno';
    else if (disponibles <= 2) colorClass = 'poco';
    
    return {
      html: `
        <div class="slot-content ${colorClass}">
          <div class="slot-header">
            <strong>${doctorNombre || arg.event.title}</strong>
          </div>
          <div class="slot-info">
            <span class="badge">👥 ${cuposOcupados}/${cuposTotal}</span>
            ${disponibles > 0 ? 
              `<span class="badge-success">✅ ${disponibles}</span>` : 
              `<span class="badge-danger">🔴 LLENO</span>`
            }
          </div>
        </div>
      `
    };
  }
  
  enhanceEventAppearance(info) {
    const { cuposOcupados, cuposTotal, esSobrecupo } = info.event.extendedProps;
    
    // Agregar clase de sobrecupo
    if (esSobrecupo || cuposOcupados > cuposTotal) {
      info.el.classList.add('sobrecupo');
    }
    
    // Configurar atributo para CSS
    info.el.setAttribute('data-cupos-disponibles', cuposTotal - cuposOcupados);
  }
  
  async handleEventDrop(info) {
    const targetSlot = this.getSlotAtTime(info.event.start);
    
    if (!targetSlot) {
      this.showAlert('No hay bloque disponible en ese horario', 'error');
      info.revert();
      return;
    }
    
    const { cuposOcupados, cuposTotal } = targetSlot.extendedProps;
    let isSobrecupo = false;
    
    if (cuposOcupados >= cuposTotal) {
      const confirmar = await this.confirmSobrecupo(
        info.event.title, 
        targetSlot.title,
        cuposOcupados,
        cuposTotal
      );
      
      if (!confirmar) {
        info.revert();
        return;
      }
      
      isSobrecupo = true;
    }
    
    try {
      const response = await fetch('/api/calendar/move-patient', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          appointmentId: info.event.id,
          newSlotId: targetSlot.id,
          newStartTime: info.event.start.toISOString(),
          isSobrecupo: isSobrecupo
        })
      });
      
      const data = await response.json();
      
      if (data.success) {
        // Animación de éxito
        info.el.classList.add('fc-event-dropped');
        
        this.showNotification(
          isSobrecupo ? '⚠️ Sobrecupo creado' : '✅ Cita movida correctamente',
          'success'
        );
        
        // Refrescar calendario
        this.calendar.refetchEvents();
      } else {
        info.revert();
        this.showAlert(data.message, 'error');
      }
    } catch (error) {
      info.revert();
      this.showAlert('Error al mover la cita', 'error');
      console.error(error);
    }
  }
  
  handleDragStart(info) {
    // Agregar clase durante drag
    info.el.classList.add('fc-event-dragging');
    
    // Resaltar slots disponibles
    this.highlightAvailableSlots();
  }
  
  handleDragStop(info) {
    // Remover clase
    info.el.classList.remove('fc-event-dragging');
    
    // Limpiar resaltados
    this.clearSlotHighlights();
  }
  
  highlightAvailableSlots() {
    const allEvents = this.calendar.getEvents();
    
    document.querySelectorAll('.fc-timegrid-slot').forEach(slotEl => {
      const slotTime = this.getSlotTime(slotEl);
      const slot = allEvents.find(e => e.start.getTime() === slotTime);
      
      if (slot) {
        const { cuposOcupados, cuposTotal } = slot.extendedProps;
        slotEl.classList.add(cuposOcupados >= cuposTotal ? 'full' : 'available');
      }
    });
  }
  
  clearSlotHighlights() {
    document.querySelectorAll('.fc-timegrid-slot').forEach(slotEl => {
      slotEl.classList.remove('available', 'full');
    });
  }
  
  async confirmSobrecupo(paciente, bloque, ocupados, total) {
    return new Promise((resolve) => {
      const modal = this.createConfirmModal(
        '⚠️ Confirmar Sobrecupo',
        `
          <p><strong>Bloque:</strong> ${bloque}</p>
          <p><strong>Ocupación:</strong> ${ocupados}/${total}</p>
          <p><strong>Paciente:</strong> ${paciente}</p>
          <hr>
          <p>¿Confirmar sobrecupo?</p>
        `,
        () => resolve(true),
        () => resolve(false)
      );
      modal.show();
    });
  }
  
  initMercureListener() {
    if (!window.mercureUrl) return;
    
    const url = new URL(window.mercureUrl);
    url.searchParams.append('topic', 'calendar/appointments');
    
    const eventSource = new EventSource(url);
    
    eventSource.onmessage = (e) => {
      const data = JSON.parse(e.data);
      
      // Refrescar eventos automáticamente
      this.calendar.refetchEvents();
      
      // Mostrar notificación
      if (data.action === 'sobrecupo_added') {
        this.showNotification('⚠️ Nuevo sobrecupo creado por otro usuario', 'warning');
      } else if (data.action === 'patient_moved') {
        this.showNotification('📅 Una cita fue modificada', 'info');
      }
    };
  }
  
  disconnect() {
    this.calendar?.destroy();
  }
}
```

---

## 🗄️ Estructura de Base de Datos

```sql
-- Tabla de Slots (Bloques de tiempo)
CREATE TABLE slots (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    doctor_id BIGINT NOT NULL,
    especialidad_id BIGINT NOT NULL,
    sucursal_id BIGINT NOT NULL,
    fecha DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    cupos_total INT DEFAULT 5,
    permite_sobrecupo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doctor_fecha (doctor_id, fecha),
    INDEX idx_especialidad_fecha (especialidad_id, fecha)
);

-- Tabla de Citas
CREATE TABLE appointments (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    slot_id BIGINT NOT NULL,
    patient_id BIGINT NOT NULL,
    es_sobrecupo BOOLEAN DEFAULT FALSE,
    estado VARCHAR(50) DEFAULT 'confirmada',
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (slot_id) REFERENCES slots(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id),
    INDEX idx_slot (slot_id),
    INDEX idx_patient (patient_id)
);
```

---

## 🚀 Endpoints API

```php
// src/Controller/CalendarController.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/calendar')]
class CalendarController extends AbstractController
{
    // GET /api/calendar/events
    #[Route('/events', methods: ['GET'])]
    public function getEvents(Request $request): JsonResponse
    {
        // Retornar eventos en formato FullCalendar
    }
    
    // POST /api/calendar/add-patient
    #[Route('/add-patient', methods: ['POST'])]
    public function addPatient(Request $request): JsonResponse
    {
        // Agregar paciente a un slot
    }
    
    // POST /api/calendar/move-patient
    #[Route('/move-patient', methods: ['POST'])]
    public function movePatient(Request $request): JsonResponse
    {
        // Mover cita entre slots (drag & drop)
    }
    
    // DELETE /api/calendar/appointment/{id}
    #[Route('/appointment/{id}', methods: ['DELETE'])]
    public function deleteAppointment(int $id): JsonResponse
    {
        // Eliminar cita
    }
    
    // GET /api/calendar/slot/{id}/detail
    #[Route('/slot/{id}/detail', methods: ['GET'])]
    public function getSlotDetail(int $id): JsonResponse
    {
        // Detalle de un slot para el modal
    }
}
```

---

## 📝 Plan de Implementación

### **Fase 1: Setup Inicial** (Día 1)
- [ ] Instalar dependencias NPM y Composer
- [ ] Configurar Asset Mapper / Webpack Encore
- [ ] Crear estructura de controladores Stimulus
- [ ] Configurar Mercure Hub

### **Fase 2: Backend** (Días 2-3)
- [ ] Crear entidades (Slot, Appointment)
- [ ] Crear repositorios con queries optimizadas
- [ ] Implementar CalendarService
- [ ] Crear endpoints API
- [ ] Agregar validaciones de sobrecupos

### **Fase 3: Frontend Base** (Días 4-5)
- [ ] Implementar calendar_controller.js
- [ ] Configurar FullCalendar v6
- [ ] Crear estilos CSS modernos
- [ ] Implementar vista de bloques con contadores

### **Fase 4: Drag & Drop** (Día 6)
- [ ] Configurar drag & drop de pacientes
- [ ] Implementar validaciones visuales
- [ ] Agregar animaciones fluidas
- [ ] Testing de movimientos entre días

### **Fase 5: Modal y Detalles** (Día 7)
- [ ] Crear modal con Turbo Frame
- [ ] Implementar lista de pacientes
- [ ] Botones de acción (agregar, eliminar)
- [ ] Integrar búsqueda de pacientes

### **Fase 6: Tiempo Real** (Día 8)
- [ ] Configurar Mercure client
- [ ] Implementar notificaciones push
- [ ] Sincronización entre usuarios
- [ ] Testing multi-usuario

### **Fase 7: Optimización** (Día 9)
- [ ] Performance: lazy loading de eventos
- [ ] Caché con Redis
- [ ] Optimizar queries SQL
- [ ] Testing de carga

### **Fase 8: Testing y Deploy** (Día 10)
- [ ] Tests unitarios (PHPUnit)
- [ ] Tests E2E (Panther o Cypress)
- [ ] Documentación de usuario
- [ ] Deploy a producción

---

## 🎨 Mejoras Visuales vs FullCalendar v1.6.2

| Característica | v1.6.2 (Actual) | v6.x (Nuevo) |
|----------------|-----------------|--------------|
| **Diseño** | Anticuado, estilo 2013 | Moderno, Material Design |
| **Animaciones** | Ninguna | Transiciones CSS3 suaves |
| **Drag & Drop** | Básico, con bugs | Fluido, con validaciones |
| **Responsive** | Limitado | Totalmente responsive |
| **Touch** | No compatible | Soporte táctil completo |
| **Performance** | Problemas con muchos eventos | Optimizado, virtual scrolling |
| **API** | jQuery dependiente | Vanilla JS, modular |
| **Customización** | Difícil | API moderna y flexible |

---

## 📚 Recursos y Documentación

- [FullCalendar v6 Docs](https://fullcalendar.io/docs)
- [Stimulus Handbook](https://stimulus.hotwired.dev/handbook/introduction)
- [Turbo Reference](https://turbo.hotwired.dev/reference/drive)
- [Mercure Protocol](https://mercure.rocks/docs)
- [Symfony UX](https://ux.symfony.com/)

---

## ⚡ Optimizaciones de Performance

### Lazy Loading de Eventos
```javascript
events: {
  url: '/api/calendar/events',
  method: 'GET',
  extraParams: {
    doctorId: this.doctorIdValue
  },
  failure: () => {
    alert('Error al cargar eventos');
  }
}
```

### Caché en Backend
```php
use Symfony\Contracts\Cache\CacheInterface;

public function getEvents(Request $request, CacheInterface $cache): JsonResponse
{
    $doctorId = $request->query->get('doctorId');
    $start = $request->query->get('start');
    $end = $request->query->get('end');
    
    $cacheKey = "calendar_events_{$doctorId}_{$start}_{$end}";
    
    $events = $cache->get($cacheKey, function () use ($doctorId, $start, $end) {
        return $this->calendarService->getEvents($doctorId, $start, $end);
    });
    
    return $this->json($events);
}
```

### Debouncing de Actualizaciones
```javascript
// Evitar múltiples refetch seguidos
refetchWithDebounce() {
  clearTimeout(this.refetchTimeout);
  this.refetchTimeout = setTimeout(() => {
    this.calendar.refetchEvents();
  }, 500);
}
```

---

## 🔐 Seguridad

### Validación de Permisos
```php
#[IsGranted('ROLE_ADMIN')]
public function movePatient(Request $request): JsonResponse
{
    // Solo usuarios autorizados pueden mover citas
}
```

### CSRF Protection
```javascript
// Agregar token CSRF en requests
headers: {
  'Content-Type': 'application/json',
  'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').content
}
```

---

## 🎯 Resultado Final

Un sistema de agenda médica **moderno, fluido y profesional** que:

- ✅ Mejora la experiencia de usuario 10x
- ✅ Reduce errores de agendamiento
- ✅ Permite trabajo simultáneo de múltiples usuarios
- ✅ Detecta y previene sobrecupos accidentales
- ✅ Se adapta a cualquier dispositivo
- ✅ Mantiene a todos sincronizados en tiempo real

---

**Fecha de Documento:** 6 de Febrero de 2026  
**Versión:** 1.0  
**Stack:** Symfony 7 + PHP 8.3 + FullCalendar v6 + Stimulus + Turbo + Mercure
