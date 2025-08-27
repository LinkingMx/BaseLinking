#!/bin/bash

# 🔍 Script de Validación para Deployment
# Ejecutar ANTES del deployment para verificar el estado actual

echo "🔍 Validando estado del sistema..."

# Check if we can connect to database
echo "📊 Verificando conexión a base de datos..."
php artisan tinker --execute="try { DB::connection()->getPdo(); echo 'Database connection: OK'; } catch(Exception \$e) { echo 'Database connection: FAILED - ' . \$e->getMessage(); }"

# Check if settings table exists
echo "⚙️ Verificando tabla settings..."
php artisan tinker --execute="try { \$exists = \Illuminate\Support\Facades\Schema::hasTable('settings'); echo 'Settings table exists: ' . (\$exists ? 'YES' : 'NO'); } catch(Exception \$e) { echo 'Settings check: ERROR - ' . \$e->getMessage(); }"

# Check if settings are working
echo "🔧 Verificando funcionalidad de settings..."
php artisan tinker --execute="try { \$settings = \App\Helpers\SettingsHelper::general(); echo 'Settings working: YES - App name: ' . \$settings->app_name; } catch(Exception \$e) { echo 'Settings working: NO - ' . \$e->getMessage(); }"

# Check Laravel migrations status
echo "📦 Verificando estado de migraciones Laravel..."
php artisan migrate:status | head -10

# Check settings migrations if table exists
php artisan tinker --execute="if(\Illuminate\Support\Facades\Schema::hasTable('settings')) { echo 'Settings data count: ' . \DB::table('settings')->count(); } else { echo 'Settings table not found - migrations needed'; }"

echo "✅ Validación completada"
