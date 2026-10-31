#!/bin/sh
set -e

# Run migrations if needed
php artisan migrate --force 2>/dev/null || true

# Clear caches
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true

# Start FrankenPHP directly with php-server
exec frankenphp php-server \
    --root /app/public \
    --listen :8000 \
    --worker /app/public/index.php,4 \
    --access-log