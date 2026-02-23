# Supervisor Caja — Fase 2: Controladores Pendientes

Estos controladores requieren dependencias que aún no están listas
(lógica de negocio compleja, datos reales de producción, integraciones externas).

---

## CashConsolidationController

**Destino:** `src/Controller/Revenue/Supervisor/CashConsolidationController.php`

**Dependencias faltantes:**
- Lógica de consolidación contable y datos reales de producción pendientes
- ExportService configurado para Excel multi-hoja

**Rutas planeadas:**
```
GET  /revenue/supervisor/cash-consolidation               → listado cajas
GET  /revenue/supervisor/cash-consolidation/{id}/report   → informe detallado
POST /revenue/supervisor/cash-consolidation/{id}/reopen   → reabrir caja cerrada
GET  /revenue/supervisor/cash-consolidation/{id}/export   → exportar Excel
```

**Referencia legacy:**
- `Controller/_Default/Supervisor/ConsolidadoCaja/Controller.php`
- `Controller/_Default/Supervisor/ConsolidadoCaja/InformeController.php`

---

## AccountingEntryController

**Destino:** `src/Controller/Revenue/Supervisor/AccountingEntryController.php`

**Dependencias faltantes:**
- Definición de estructura de asiento contable en el tenant
- Formato de exportación contable (CSV o XML según norma)

**Rutas planeadas:**
```
GET  /revenue/supervisor/accounting-entry                                  → filtro por mes/año/sucursal
GET  /revenue/supervisor/accounting-entry/report                           → informe
GET  /revenue/supervisor/accounting-entry/download/{year}/{month}          → descarga
GET  /revenue/supervisor/accounting-entry/user-by-branch (AJAX)            → usuario por sucursal
```

**Referencia legacy:**
- `Controller/_Default/Supervisor/AsientoContable/`

---

## ProductionReportController

**Destino:** `src/Controller/Revenue/Supervisor/ProductionReportController.php`

**Dependencias faltantes:**
- Definición de qué constituye "producción" en el tenant
- Acceso a PaymentAccount con rango de fechas + joins de prestaciones

**Rutas planeadas:**
```
GET /revenue/supervisor/production-report                    → filtro por rango fechas
GET /revenue/supervisor/production-report/download/{from}/{to} → descarga
```

**Referencia legacy:**
- `Controller/_Default/Supervisor/ReporteProduccion/`

---

## BillingAidController

**Destino:** `src/Controller/Revenue/Supervisor/BillingAidController.php`

**Dependencias faltantes:**
- Integración DTE (facturación electrónica) — módulo separado
- Definición del formato de apoyo de facturación mensual

**Rutas planeadas:**
```
GET /revenue/supervisor/billing-aid           → filtro por mes
GET /revenue/supervisor/billing-aid/report    → informe
GET /revenue/supervisor/billing-aid/download/{month} → descarga mensual
```

**Referencia legacy:**
- `Controller/_Default/Supervisor/ApoyoFacturacion/`
- ⚠️ Puede tener dependencia de DTE (ver `caja_migracion_supervisor.md`)

---

## Notas generales

- Permisos ⚠️ **pendiente inmediato**: agregar `ROLE_CASH_SUPERVISOR` en `security.yaml` y proteger con `is_granted()` (o `access_control`) los endpoints de `DifferenceAuthorizationController` y `VoucherManagementController`. Mientras no se haga, cualquier usuario autenticado puede aprobar diferencias.
- Auditoría: `CashRegister` y `Difference` usan `createdAt/updatedAt`. Para trazabilidad más granular evaluar EventListener o tabla de log propia.
