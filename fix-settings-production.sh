#!/bin/bash

# 🚀 PRODUCTION SETTINGS FIX SCRIPT
# Este script resuelve los problemas de configuración de Spatie Settings en producción

echo "🚀 Fixing Spatie Laravel Settings for Production..."

# Step 1: Clear all caches first
echo "🧹 Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Step 2: Run settings migrations
echo "⚙️ Running settings migrations..."
php artisan migrate --path=database/settings --force

# Step 3: Fix settings configuration
echo "🔧 Fixing settings configuration..."
php artisan settings:fix --clear-cache

# Step 4: Clear settings discovery cache
echo "🔄 Clearing settings discovery cache..."
php artisan settings:clear-discovered
php artisan settings:discover

# Step 5: Test settings functionality
echo "🧪 Testing settings functionality..."
php artisan settings:fix

# Step 6: Cache configuration for production
echo "📦 Caching configuration for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Step 7: Final verification
echo "✅ Final verification..."
php artisan settings:fix

echo ""
echo "✅ Spatie Settings configuration fixed for production!"
echo "💡 The application should now work with cached configuration."
