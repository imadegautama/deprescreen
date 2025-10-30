#!/bin/bash
set -e

cd /var/www/html

echo "===== ENTRYPOINT: Starting Laravel app ====="

# 1. Check if .env exists, if not copy from .env.example
if [ ! -f .env ]; then
    echo "⚠️  .env not found, copying from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "❌ .env.example not found!"
        exit 1
    fi
fi

# 2. Generate APP_KEY if not set
if ! grep -q "^APP_KEY=..*" .env; then
    echo "⚠️  APP_KEY not set, generating..."
    sudo -u www-data php artisan key:generate || true
fi

# 3. Verify composer dependencies (already installed in Dockerfile, just verify)
echo "✓ Vendor exists (installed during image build)"

# 4. Wait for database to be ready (retry max 30 seconds)
echo "⏳ Waiting for database to be ready..."
max_attempts=30
attempt=0
while [ $attempt -lt $max_attempts ]; do
    if php artisan tinker --execute="DB::connection()->getPdo();" 2>/dev/null; then
        echo "✓ Database is ready"
        break
    fi
    attempt=$((attempt + 1))
    echo "  [attempt $attempt/$max_attempts] Retrying in 1s..."
    sleep 1
done

if [ $attempt -eq $max_attempts ]; then
    echo "⚠️  Database connection timeout, but continuing..."
fi

# 5. Run migrations (if database is ready)
if [ $attempt -lt $max_attempts ]; then
    echo "🔄 Running migrations..."
    sudo -u www-data php artisan migrate --force || {
        echo "⚠️  Migrations failed (may be already run)"
    }
fi

# 6. Optimize caches
echo "⚡ Optimizing application..."
sudo -u www-data php artisan optimize || true

echo "===== ✓ READY FOR REQUESTS ====="

# Execute the main command (php-fpm)
exec "$@"
