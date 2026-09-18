#!/bin/bash
# ==============================================================================
# Script de Despliegue y Optimización para Producción en aaPanel
# Proyecto: PICHO Roms / ROMHUB
# ==============================================================================

set -e

echo "🚀 Iniciando despliegue y optimización para producción en aaPanel..."

# 1. Poner la aplicación en modo mantenimiento temporalmente
echo "⏸️  Activando modo mantenimiento..."
php artisan down --retry=60 || true

# 2. Instalar / actualizar dependencias de Composer sin paquetes de desarrollo
echo "📦 Instalando dependencias de PHP optimizadas..."
composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 3. Compilar assets de Vite para producción (CSS y JS minificados)
if command -v npm &> /dev/null; then
    echo "⚡ Compilando assets de Vite para producción..."
    npm run build
fi

# 4. Ejecutar migraciones de base de datos de forma segura
echo "🗄️  Ejecutando migraciones de base de datos..."
php artisan migrate --force

# 5. Asegurar enlace simbólico de almacenamiento público (storage:link)
echo "🔗 Verificando enlace simbólico storage:link..."
php artisan storage:link || true

# 6. Limpiar y cachear configuraciones, rutas, vistas y eventos
echo "⚡ Optimizando caches de Laravel..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 7. Ajustar permisos recomendados para el usuario de aaPanel (www:www)
echo "🔒 Ajustando permisos para usuario www en aaPanel..."
if id "www" &>/dev/null; then
    chown -R www:www storage bootstrap/cache
    chmod -R 775 storage bootstrap/cache
fi

# 8. Reactivar la aplicación
echo "▶️  Desactivando modo mantenimiento..."
php artisan up

echo "✅ ¡Despliegue y optimización completados exitosamente en aaPanel!"
