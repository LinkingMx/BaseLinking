#!/bin/bash

# 🚀 Deploy Script - Laravel con Spatie Settings
# Ejecutar en el servidor de producción

echo "🚀 Iniciando deployment..."

# Maintenance mode
php artisan down --retry=60

# Update dependencies
echo "📦 Instalando dependencias..."
composer install --optimize-autoloader --no-dev

# Clear caches to avoid settings conflicts
echo "🧹 Limpiando caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Database migrations (Laravel standard)
echo "📦 Ejecutando migraciones de Laravel..."
php artisan migrate --force

# Settings migrations (CRÍTICO - Spatie Settings)
echo "⚙️ Ejecutando migraciones de Settings..."
php artisan migrate --path=database/settings --force

# Verify settings table was created
echo "✅ Verificando tabla settings..."
php artisan tinker --execute="DB::select('SHOW TABLES LIKE \"settings\"'); echo 'Settings table verified';"

# Optimize for production
echo "🔄 Optimizando para producción..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Build frontend assets (if needed)
if [ -f "package.json" ]; then
    echo "🎨 Compilando assets frontend..."
    npm ci --production
    npm run build
fi

# Storage link (if needed)
php artisan storage:link

# End maintenance mode
php artisan up

echo "✅ Deployment completado exitosamente!"
echo "💡 Verifica que las settings estén funcionando en /admin"
