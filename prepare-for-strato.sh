#!/usr/bin/env bash

# ==============================================================================
# 🎲 CorRol — Preparación Local para Producción (Hosting Strato sin SSH)
# ==============================================================================

set -e

echo "📦 1. Limpiando cachés con rutas locales..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "⚡ 2. Instalando dependencias PHP de producción..."
composer install --optimize-autoloader --no-dev

echo "🎨 3. Compilando assets frontend (CSS/JS) con Vite..."
npm install
npm run build

echo "🧹 4. Asegurando que las vistas compiladas locales no se suban..."
rm -rf storage/framework/views/*

echo "------------------------------------------------------------------------------"
echo "🎉 ¡Proyecto listo para subir a GitHub / Strato!"
echo "------------------------------------------------------------------------------"