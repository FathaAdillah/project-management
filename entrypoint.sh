#!/bin/sh

echo "🚀 Starting Laravel application..."

# Wait for database to be ready
echo "⏳ Waiting for database connection..."
php artisan db:show --database=mysql 2>/dev/null || sleep 3

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force --no-interaction || true

# Cache optimization
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Octane server with Swoole
echo "✅ Starting Laravel Octane server on http://0.0.0.0:8000"
php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
