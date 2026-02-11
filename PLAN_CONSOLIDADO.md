# MELISA – Plan Consolidado
Separado por Arquitectura, Negocio y Roadmap

---

# 1. ARQUITECTURA

## 1.1 Principios
- Alta disponibilidad desde el inicio
- Simplicidad operativa (1 desarrollador)
- Cumplimiento normativo chileno (datos de salud)
- Diseño portable (sin amarrarse a un proveedor)
- Preparado para escalar (Kubernetes más adelante)

---

## 1.2 Decisión sobre Kubernetes
- ❌ No usar Kubernetes en etapa inicial
- ✅ Usarlo solo cuando:
  - el sistema esté estable
  - haya clientes activos
  - se requiera escalado y despliegue más sofisticado

**Motivo:** Kubernetes no entrega valor inmediato y aumenta la carga operativa.

---

## 1.3 Infraestructura inicial (AWS – clásica)

### Componentes
- **ALB (Application Load Balancer)**
  - Reparte tráfico
  - Saca nodos caídos automáticamente

- **2 EC2 (App servers)**
  - Nginx + PHP-FPM
  - MELISA corre en ambos
  - Alta disponibilidad real

- **RDS (MySQL/PostgreSQL)**
  - Base de datos administrada
  - Backups automáticos
  - Alta confiabilidad

- **ElastiCache Redis**
  - Sesiones compartidas
  - Cola de trabajos (Symfony Messenger)
  - Locks e idempotencia

- **S3**
  - PDFs
  - Reportes
  - Archivos adjuntos

---

## 1.4 Diseño de la aplicación

### App stateless
- Nada crítico en disco local
- Sesiones en Redis
- Archivos en S3

### Asincronía
- PDF
- Reportes
- Integraciones (Webpay, IMED, SNABB)
→ Todo vía cola + workers

### Idempotencia
- Cada pago / bono tiene un ID único
- Evita duplicados por reintentos o callbacks repetidos

### Máquina de estados
- Estados claros:
  - INICIADO
  - PENDIENTE
  - CONFIRMADO
  - ERROR

---

## 1.5 Copiloto inteligente (arquitectura)

### Función
- Explica lo que pasa
- Traduce estados técnicos a lenguaje humano
- Reduce errores y soporte

### Base técnica
- Estados + eventos + reglas
- Timeline de atención
- Panel lateral siempre visible

---

## 1.6 IA (LLM)

### Decisión
- **AWS + Bedrock**

### Uso responsable
- El LLM:
  - NO decide
  - NO inventa
  - NO ve datos clínicos crudos
- Solo recibe:
  - estados
  - eventos
  - mensajes sanitizados

---

## 1.7 Cumplimiento normativo chileno
- Ley 19.628
- Datos sensibles de salud

### Cumplimiento
- Cifrado en tránsito y reposo
- Control de accesos
- Auditoría
- No uso de datos para entrenamiento IA
- Procesamiento dentro de AWS

---

## 1.8 Observabilidad

### Sentry
- Errores automáticos
- Stacktrace
- Usuario / endpoint
- Alertas

### Health checks
- App
- DB
- Redis

### Alertas
- App down
- DB down
- Redis down
- Error rate alto
- Latencia excesiva

---

## 1.9 CI/CD (calidad y despliegue)

### CI – Calidad automática
- Composer validate + audit
- PHP-CS-Fixer
- PHPStan / Psalm
- PHPUnit
- Lint Twig/YAML
- Tests JS si aplica

### Tests E2E críticos
- Tomar hora
- Crear atención
- Flujo pago (mock)
- Bono (mock)
- PDF
- Cierre atención

### CD
- Build Docker
- Deploy rolling
- Rollback fácil

---

# 2. NEGOCIO

## 2.1 Propuesta de valor
- Sistema clínico **estable y confiable**
- El sistema **se explica solo**
- Menos errores humanos
- Menos soporte
- Más confianza operativa

---

## 2.2 Feature diferenciador
### Copiloto clínico-operativo
- Explica qué pasa
- Recomienda acciones
- Muestra línea de tiempo de la atención

Ejemplo:
- "El bono está pendiente porque IMED no respondió"
- "No intentes reemitir, el sistema está reintentando"

---

## 2.3 Costos operativos estimados (AWS)

| Componente | USD / mes |
|-----------|-----------|
| EC2 (2) | 30–60 |
| RDS | 40–60 |
| Redis | 10–30 |
| S3 | 5–15 |
| ALB | 10–20 |
| **Total** | **~100–180** |

### IA (copiloto)
- ~USD 5–30 / mes para ~100 usuarios

---

## 2.4 Modelo de precios sugerido

### Opciones
- **USD 15–30 / usuario / mes**
- **USD 250–500 / clínica / mes**

### Ejemplo
- 5 clínicas
- 100 usuarios
- Ingreso: USD 1.500–3.000 / mes
- Infraestructura: ~USD 150 / mes

👉 Margen sano para soporte y evolución.

---

## 2.5 Argumento comercial clave
> "MELISA no solo registra atenciones,  
> las entiende y las explica."

---

# 3. ROADMAP

## 3.1 Corto plazo (ahora)
1. Terminar migración Symfony
2. Dockerizar MELISA
3. Infraestructura AWS clásica (EC2)
4. CI/CD básico pero sólido
5. Observabilidad completa
6. Copiloto determinista (reglas + eventos)

### Estimación detallada

#### 1. Terminar migración Symfony
- 7 mantenedores comerciales restantes: ~4h
- 180 mantenedores básicos (con generador): ~100h
- Módulos core (Admisión, Agenda, Facturación, Registro): ~400h
- **Total: ~500h = 25 semanas**

#### 2. Dockerizar MELISA
- Dockerfile multi-stage (PHP-FPM + Nginx)
- docker-compose.yml (app + db + redis)
- Variables de entorno + secrets
- **Total: ~10h = 2-3 días**

#### 3. Infraestructura AWS clásica
- Terraform/CloudFormation (ALB + EC2 + RDS + Redis + S3)
- Security groups + IAM + networking
- Scripts deploy + configuración
- **Total: ~20h = 5 días**

#### 4. CI/CD básico pero sólido
- GitHub Actions (lint + tests + build)
- PHPStan + PHP-CS-Fixer + PHPUnit
- Tests E2E críticos (5-6 flujos)
- Deploy rolling + rollback
- **Total: ~16h = 4 días**

#### 5. Observabilidad completa
- Sentry + health checks
- Alertas (email/Slack)
- Logging estructurado + métricas
- **Total: ~10h = 2-3 días**

#### 6. Copiloto determinista
- Máquina de estados + eventos
- Reglas de negocio + timeline
- UI panel lateral + integración
- **Total: ~50h = 12-13 días**

### Timeline consolidado
- **Infraestructura + DevOps (puntos 2-6):** ~106h = 5-6 semanas
- **Con capacidad 20h/semana:**
  - Puntos 2-6 completados: **Marzo 2026** (6 semanas)
  - Punto 1 (MVP funcional): **Julio 2026** (6 meses)

### Estrategia de ejecución
- **Semanas 1-2 (Feb):** Dockerizar + CI/CD básico
- **Semanas 3-4 (Feb):** AWS + Observabilidad
- **Semanas 5-6 (Mar):** Copiloto determinista
- **Mar-Jul 2026:** Migración paralela de módulos

👉 **MVP completo con infraestructura:** Julio 2026

---

## 3.2 Mediano plazo
7. Integrar IA con Bedrock
8. Feature flags
9. Automatización de reintentos y recuperación
10. Mejorar UX del copiloto

---

## 3.3 Largo plazo
11. Kubernetes managed (EKS)
12. Escalado automático
13. Analítica avanzada
14. Nuevas integraciones

---

## 3.4 Regla final
> "Primero estabilidad, después sofisticación."

Este plan es:
- Realista
- Escalable
- Vendible
- Sostenible para un solo desarrollador

---

# 4. ESTADO ACTUAL

## 4.1 Progreso de migración
- **Completado:** 2.77% (53/1,911 controllers)
- **Módulo Comercial:** 75.9% (22/29 mantenedores)
- **Reducción de código:** 82% (12,130 → 2,158 líneas)

## 4.2 Timeline proyectado
- **MVP (70%):** Julio 2026 (6-7 meses)
- **Sistema completo:** Marzo 2027 (12-15 meses)

### Fases
1. **Feb 2026:** Mantenedores básicos (180 mantenedores)
2. **Mar 2026:** Admisión (registro, seguros, camas)
3. **Abr 2026:** Agenda (citas, disponibilidad, notificaciones)
4. **May-Jun 2026:** Facturación (FONASA, Isapre, IMED)
5. **Jun-Jul 2026:** Registro Clínico (EHR, recetas, documentos)
6. **Jul 2026:** MVP + UAT + Deploy
7. **Ago 2026-Mar 2027:** Módulos restantes

## 4.3 Velocidad de desarrollo
- **Con IA:** 1.8 mantenedores/hora
- **Aceleración:** 5-10x en tareas repetitivas (CRUD, forms, views)
- **Capacidad:** 4 horas/día, 5 días/semana (20h/semana)

## 4.4 Foco corto plazo
- Completar 7 mantenedores comerciales restantes
- Crear comando `make:mantenedor` para automatizar
- Migrar 180 mantenedores básicos
- Dockerizar aplicación
- Configurar CI/CD básico
