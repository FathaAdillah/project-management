#!/bin/bash

# ==============================================
# Laravel Octane + Docker Deployment Script
# ==============================================
# This script handles cache clearing and Octane restart
# after rebuilding Docker container with new features

set -e  # Exit on error

CONTAINER_NAME="laravel_app"

echo "🚀 Starting Laravel Octane Refresh..."
echo ""

# Check if container is running
if ! docker ps | grep -q $CONTAINER_NAME; then
    echo "❌ Error: Container '$CONTAINER_NAME' is not running!"
    echo "   Start it with: docker-compose up -d"
    exit 1
fi

echo "📦 Step 1: Clearing all Laravel caches..."
docker exec $CONTAINER_NAME php artisan optimize:clear
echo "   ✅ Config cache cleared"
echo "   ✅ Route cache cleared"
echo "   ✅ View cache cleared"
echo "   ✅ Event cache cleared"
echo "   ✅ Compiled services cleared"
echo ""

echo "🔄 Step 2: Rebuilding optimized caches..."
docker exec $CONTAINER_NAME php artisan config:cache
docker exec $CONTAINER_NAME php artisan route:cache
docker exec $CONTAINER_NAME php artisan view:cache
echo "   ✅ Caches rebuilt"
echo ""

echo "🔥 Step 3: Reloading Laravel Octane workers..."
docker exec $CONTAINER_NAME php artisan octane:reload
echo "   ✅ Octane workers reloaded (new code loaded to memory)"
echo ""

echo "✨ Done! Your application is now updated with latest changes."
echo ""
echo "📝 Notes:"
echo "   - Octane workers have been reloaded with new code"
echo "   - All caches have been cleared and rebuilt"
echo "   - No downtime during reload"
echo ""
echo "🌐 Access your app at: http://localhost:8000/admin"
