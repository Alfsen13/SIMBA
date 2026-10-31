#!/bin/bash
# Development entrypoint untuk FrankenPHP + Vite HMR

set -e

echo "🚀 Starting SIMBA Development Environment..."

# Wait for database
echo "⏳ Waiting for database..."
until php artisan db:show --database=mysql 2>/dev/null | grep -q "Database"; do
    sleep 2
done
echo "✅ Database ready"

# Wait for Redis
echo "⏳ Waiting for Redis..."
until redis-cli -h redis ping 2>/dev/null | grep -q "PONG"; do
    sleep 1
done
echo "✅ Redis ready"

# Wait for Ollama
echo "⏳ Waiting for Ollama..."
until curl -sf http://ollama:11434/api/tags >/dev/null 2>&1; do
    sleep 3
done
echo "✅ Ollama ready"

# Run migrations if needed
echo "🔄 Running migrations..."
php artisan migrate --force || true

# Clear caches
php artisan optimize:clear

# Start Vite in background
echo "🎨 Starting Vite dev server..."
npm run dev -- --host 0.0.0.0 --port 5173 &
VITE_PID=$!

# Trap signals to cleanup Vite
trap "kill $VITE_PID 2>/dev/null; exit 0" SIGTERM SIGINT

# Start FrankenPHP
echo "🐘 Starting FrankenPHP..."
exec frankenphp run --config /etc/caddy/Caddyfile.dev