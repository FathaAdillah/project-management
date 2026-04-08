# ==============================================
# Laravel Octane + Docker Deployment Script (PowerShell)
# ==============================================
# This script handles cache clearing and Octane restart
# after rebuilding Docker container with new features

$ErrorActionPreference = "Stop"

$CONTAINER_NAME = "laravel_app"

Write-Host "🚀 Starting Laravel Octane Refresh..." -ForegroundColor Green
Write-Host ""

# Check if container is running
$containerRunning = docker ps --format "{{.Names}}" | Select-String -Pattern $CONTAINER_NAME

if (-not $containerRunning) {
    Write-Host "❌ Error: Container '$CONTAINER_NAME' is not running!" -ForegroundColor Red
    Write-Host "   Start it with: docker-compose up -d" -ForegroundColor Yellow
    exit 1
}

Write-Host "📦 Step 1: Clearing all Laravel caches..." -ForegroundColor Cyan
docker exec $CONTAINER_NAME php artisan optimize:clear
Write-Host "   ✅ Config cache cleared" -ForegroundColor Green
Write-Host "   ✅ Route cache cleared" -ForegroundColor Green
Write-Host "   ✅ View cache cleared" -ForegroundColor Green
Write-Host "   ✅ Event cache cleared" -ForegroundColor Green
Write-Host "   ✅ Compiled services cleared" -ForegroundColor Green
Write-Host ""

Write-Host "🔄 Step 2: Rebuilding optimized caches..." -ForegroundColor Cyan
docker exec $CONTAINER_NAME php artisan config:cache
docker exec $CONTAINER_NAME php artisan route:cache
docker exec $CONTAINER_NAME php artisan view:cache
Write-Host "   ✅ Caches rebuilt" -ForegroundColor Green
Write-Host ""

Write-Host "🔥 Step 3: Reloading Laravel Octane workers..." -ForegroundColor Cyan
docker exec $CONTAINER_NAME php artisan octane:reload
Write-Host "   ✅ Octane workers reloaded (new code loaded to memory)" -ForegroundColor Green
Write-Host ""

Write-Host "✨ Done! Your application is now updated with latest changes." -ForegroundColor Green
Write-Host ""
Write-Host "📝 Notes:" -ForegroundColor Yellow
Write-Host "   - Octane workers have been reloaded with new code"
Write-Host "   - All caches have been cleared and rebuilt"
Write-Host "   - No downtime during reload"
Write-Host ""
Write-Host "🌐 Access your app at: http://localhost:8000/admin" -ForegroundColor Cyan
