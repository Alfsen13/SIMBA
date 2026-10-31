#!/bin/sh
set -e

echo "=== Starting SIMBA LMS ==="

# Generate Laravel key
echo "Generating app key..."
php artisan key:generate --force 2>&1 || true

# Storage link
echo "Creating storage link..."
php artisan storage:link 2>&1 || true

# Run migrations if needed
echo "Running migrations..."
php artisan migrate --force 2>&1 || echo "Migration warning (continuing...)"

# Clear caches
echo "Clearing caches..."
php artisan config:clear 2>&1 || echo "Config clear warning"
php artisan route:clear 2>&1 || echo "Route clear warning"
php artisan view:clear 2>&1 || echo "View clear warning"

# Run package:discover to register packages
echo "Discovering packages..."
php artisan package:discover --ansi 2>&1 || echo "Package discover warning"

# Test if Laravel boots correctly
echo "Testing Laravel boot..."
php artisan --version 2>&1

# Start FrankenPHP with Caddyfile (uses php_server directive with workers)
echo "Starting FrankenPHP with Caddy..."
exec frankenphp run --config /etc/caddy/Caddyfile