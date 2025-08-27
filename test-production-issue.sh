#!/bin/bash

# 🧪 Simular problema de producción
# Este script simula el error que occurs en producción

echo "🧪 Simulando problema de producción..."

# Verificar el estado actual
echo "📋 Estado actual de la tabla settings:"
php artisan tinker --execute="try { \$exists = \Illuminate\Support\Facades\Schema::hasTable('settings'); echo 'Settings table exists: ' . (\$exists ? 'YES' : 'NO'); } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "🔄 Probando acceso a settings con SettingsHelper:"
php artisan tinker --execute="try { \$settings = \App\Helpers\SettingsHelper::general(); echo 'SUCCESS: ' . \$settings->app_name; } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "🚨 Simulando comando migrate (el que falla en producción):"
echo "   Esto debería funcionar ahora sin errores..."

# Probar el comando que falla en producción
php artisan migrate --force

echo ""
echo "✅ Si llegaste aquí sin errores, el problema está resuelto!"
