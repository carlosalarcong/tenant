# Inventario Funcional del Sistema

- Fecha: 2026-03-24
- Branch: feature/mantenedores
- Controladores detectados: 144
- Rutas por atributos detectadas: 786

## Resumen Por Modulo
| Modulo | Controladores | Rutas |
|---|---:|---:|
| Admin | 1 | 10 |
| Core | 10 | 25 |
| Dashboard | 1 | 1 |
| Maintainers/Admission | 3 | 18 |
| Maintainers/Basic | 14 | 84 |
| Maintainers/Billing | 1 | 6 |
| Maintainers/Budget | 3 | 18 |
| Maintainers/Clinical | 12 | 72 |
| Maintainers/ClinicalSupport | 1 | 6 |
| Maintainers/Commercial | 22 | 110 |
| Maintainers/Hospital | 24 | 141 |
| Maintainers/Logistics | 10 | 60 |
| Maintainers/Settlements | 5 | 30 |
| Maintainers/Structure | 6 | 36 |
| Maintainers/Surgery | 13 | 65 |
| Maintainers/Treasury | 17 | 98 |
| Maintainers/Workshop | 1 | 6 |

## Archivos Exportables
- CSV detalle: `docs/inventario/controladores_rutas.csv`
- Resumen módulos: `docs/inventario/resumen_modulos.md`

## Notas
- `module=Core` corresponde a controladores en `src/Controller` sin subcarpeta.
- En mantenedores, el módulo se agrupa como `Maintainers/<Area>`.
- El conteo de rutas considera atributos con `name: '...'`.
