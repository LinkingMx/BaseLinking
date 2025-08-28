#!/bin/bash

# 🔍 VERIFICACIÓN RÁPIDA EN PRODUCCIÓN
# Ejecutar este script después del fix para verificar que todo funciona

echo "🔍 Verificando configuración de settings en producción..."

# Test 1: Verificar que settings funciona
echo "📋 Test 1: Verificando functionality de settings..."
php artisan settings:fix

echo ""
echo "📦 Test 2: Verificando compatibilidad con cache..."
php artisan config:cache
php artisan route:cache

echo ""
echo "🧪 Test 3: Test final de settings..."
php artisan tinker --execute="try { echo 'Settings working: ' . \App\Helpers\SettingsHelper::general()->app_name; } catch(Exception \$e) { echo 'ERROR: ' . \$e->getMessage(); }"

echo ""
echo "✅ Verificación completada!"
echo "💡 Si no hay errores arriba, el fix fue exitoso."
