#!/bin/bash

# Script para instalar dependencias de Laravel
# Ejecutar: bash install-laravel-deps.sh

echo "🚀 Instalando dependencias de Laravel..."

# Instalar paquetes principales
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require spatie/laravel-query-builder
composer require barryvdh/laravel-dompdf
composer require milon/barcode

# Instalar paquetes de desarrollo
composer require --dev laravel/telescope
composer require --dev barryvdh/laravel-ide-helper
composer require --dev pestphp/pest
composer require --dev pestphp/pest-plugin-laravel
composer require --dev phpstan/phpstan
composer require --dev friendsofphp/php-cs-fixer
composer require --dev nunomaduro/larastan

# Publicar configuraciones
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"

# Instalar Telescope (solo en desarrollo)
php artisan telescope:install

# Generar key de aplicación
php artisan key:generate

# Crear symlink de storage
php artisan storage:link

echo "✅ Dependencias instaladas correctamente!"
echo ""
echo "Próximos pasos:"
echo "1. Configurar .env con tus credenciales"
echo "2. Ejecutar: php artisan migrate"
echo "3. Ejecutar: php artisan db:seed"