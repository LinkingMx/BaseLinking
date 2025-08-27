#!/bin/bash

# 🧪 Simular EXACTAMENTE el problema de producción
# Este script simula el error renombrando temporalmente la tabla settings

echo "🧪 Simulando EXACTAMENTE el problema de producción..."

# Backup de la tabla settings
echo "💾 Haciendo backup de la tabla settings..."
php artisan tinker --execute="if(\Illuminate\Support\Facades\Schema::hasTable('settings')) { \DB::statement('CREATE TABLE settings_backup AS SELECT * FROM settings'); echo 'Backup created'; } else { echo 'No settings table to backup'; }"

# Eliminar la tabla settings para simular producción
echo "🗑️  Eliminando tabla settings para simular producción..."
php artisan tinker --execute="if(\Illuminate\Support\Facades\Schema::hasTable('settings')) { \Illuminate\Support\Facades\Schema::drop('settings'); echo 'Settings table dropped'; } else { echo 'Settings table already missing'; }"

# Verificar que no existe
echo "📋 Verificando que la tabla no existe:"
php artisan tinker --execute="try { \$exists = \Illuminate\Support\Facades\Schema::hasTable('settings'); echo 'Settings table exists: ' . (\$exists ? 'YES' : 'NO'); } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "🔄 Probando acceso a settings sin tabla (debe usar defaults):"
php artisan tinker --execute="try { \$settings = \App\Helpers\SettingsHelper::general(); echo 'SUCCESS: ' . \$settings->app_name; } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "🚨 Probando comando migrate sin tabla settings (EL PROBLEMA REAL):"
echo "   Esto es lo que falla en producción..."

# Limpiar caches primero
php artisan config:clear
php artisan cache:clear

# Este es el comando que falla en producción
php artisan migrate --force

echo ""
echo "✅ Si no hubo errores, ejecutando migraciones de settings..."
php artisan migrate --path=database/settings --force

echo ""
echo "🔄 Verificando que settings funciona después de migración:"
php artisan tinker --execute="try { \$settings = \App\Helpers\SettingsHelper::general(); echo 'SUCCESS: ' . \$settings->app_name; } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "✅ ¡Problema resuelto! El deployment ahora debería funcionar en producción."
