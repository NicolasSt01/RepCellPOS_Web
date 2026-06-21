#!/bin/bash
set -euo pipefail

E2E_DB="/tmp/repcellpos_e2e.sqlite"
E2E_PORT=${E2E_PORT:-8080}
E2E_URL="http://localhost:${E2E_PORT}"

echo "=== Limpiando base de datos E2E anterior ==="
rm -f "$E2E_DB"

echo "=== Corriendo migraciones ==="
APP_ENV=e2e php artisan migrate --force --env=e2e

echo "=== Sembrando datos base (permisos, roles, planes) ==="
APP_ENV=e2e php artisan db:seed --force --env=e2e

echo "=== Construyendo assets Vite ==="
npm run build > /dev/null 2>&1

echo "=== Iniciando servidor Laravel en puerto ${E2E_PORT} ==="
APP_ENV=e2e php artisan serve --env=e2e --port="${E2E_PORT}" > /dev/null 2>&1 &
SERVER_PID=$!

# Esperar a que el servidor responda
for i in $(seq 1 20); do
    if curl -sf "${E2E_URL}/" > /dev/null 2>&1; then
        echo "=== Servidor listo ==="
        break
    fi
    if [ "$i" -eq 20 ]; then
        echo "ERROR: Servidor no respondió"
        kill "$SERVER_PID" 2>/dev/null || true
        exit 1
    fi
    sleep 0.5
done
echo "=== Servidor PID: ${SERVER_PID} ==="

echo "=== Ejecutando tests Playwright ==="
E2E_URL="${E2E_URL}" npx playwright test "$@"
EXIT_CODE=$?

echo "=== Limpiando ==="
kill "$SERVER_PID" 2>/dev/null || true
rm -f "$E2E_DB"

exit $EXIT_CODE
