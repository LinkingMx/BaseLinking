# 🔧 Setup Script para Desarrollo Local
# Ejecutar después de clonar el repositorio

echo "🚀 Configurando entorno local..."

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
touch database/database.sqlite

# Run standard migrations
php artisan migrate

# ⚙️ SETTINGS SETUP (CRÍTICO)
echo "⚙️ Configurando Spatie Settings..."
php artisan migrate --path=database/settings

# Seed database
php artisan db:seed

# Build frontend
npm run build

echo "✅ Entorno local configurado correctamente!"
echo "💡 Para desarrollar usar: composer dev"
