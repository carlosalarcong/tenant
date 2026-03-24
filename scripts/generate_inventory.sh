#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

OUT_DIR="docs/inventario"
mkdir -p "$OUT_DIR"

CSV_FILE="$OUT_DIR/controladores_rutas.csv"
SUMMARY_FILE="$OUT_DIR/resumen_modulos.md"
REPORT_FILE="$OUT_DIR/INVENTARIO_FUNCIONAL.md"

echo 'controller_path,module,class_route_prefix,route_count,route_names' > "$CSV_FILE"

while IFS= read -r file; do
    rel="${file#src/Controller/}"

    if [[ "$rel" == */* ]]; then
        first="${rel%%/*}"
        if [[ "$first" == "Maintainers" ]]; then
            sub="${rel#Maintainers/}"
            area="${sub%%/*}"
            module="Maintainers/${area}"
        else
            module="$first"
        fi
    else
        module="Core"
    fi

    class_prefix="$(awk '
        BEGIN { in_class = 0 }
        /class[[:space:]]+[A-Za-z0-9_]+Controller/ { in_class = 1 }
        in_class == 0 {
            if (match($0, /#\[Route\('\''([^'\'']*)'\''/, m)) {
                print m[1]
                exit
            }
        }
    ' "$file")"

    mapfile -t route_names < <(grep -oE "name:[[:space:]]*'[^']+'" "$file" | sed -E "s/name:[[:space:]]*'([^']+)'/\\1/")
    route_count="${#route_names[@]}"
    routes_joined=""
    if (( route_count > 0 )); then
        routes_joined="$(printf '%s\n' "${route_names[@]}" | paste -sd';' -)"
    fi

    esc_prefix="${class_prefix//\"/\"\"}"
    esc_routes="${routes_joined//\"/\"\"}"

    printf '"%s","%s","%s",%d,"%s"\n' \
        "$rel" \
        "$module" \
        "$esc_prefix" \
        "$route_count" \
        "$esc_routes" >> "$CSV_FILE"
done < <(find src/Controller -type f -name '*Controller.php' | sort)

{
    echo '| Modulo | Controladores | Rutas |'
    echo '|---|---:|---:|'
    awk -F',' '
        NR > 1 {
            module = $2
            gsub(/^"|"$/, "", module)
            controllers[module]++
            routes[module] += $4
        }
        END {
            for (m in controllers) {
                printf "| %s | %d | %d |\n", m, controllers[m], routes[m]
            }
        }
    ' "$CSV_FILE" | sort
} > "$SUMMARY_FILE"

total_controllers="$(awk -F',' 'NR > 1 {n++} END {print n + 0}' "$CSV_FILE")"
total_routes="$(awk -F',' 'NR > 1 {s += $4} END {print s + 0}' "$CSV_FILE")"
branch_name="$(git branch --show-current)"

{
    echo '# Inventario Funcional del Sistema'
    echo
    echo "- Fecha: 2026-03-24"
    echo "- Branch: ${branch_name}"
    echo "- Controladores detectados: ${total_controllers}"
    echo "- Rutas por atributos detectadas: ${total_routes}"
    echo
    echo '## Resumen Por Modulo'
    cat "$SUMMARY_FILE"
    echo
    echo '## Archivos Exportables'
    echo "- CSV detalle: \`$CSV_FILE\`"
    echo "- Resumen módulos: \`$SUMMARY_FILE\`"
    echo
    echo '## Notas'
    echo '- `module=Core` corresponde a controladores en `src/Controller` sin subcarpeta.'
    echo '- En mantenedores, el módulo se agrupa como `Maintainers/<Area>`.'
    echo '- El conteo de rutas considera atributos con `name: '\''...'\''`.'
} > "$REPORT_FILE"

echo "OK: $CSV_FILE"
echo "OK: $SUMMARY_FILE"
echo "OK: $REPORT_FILE"
