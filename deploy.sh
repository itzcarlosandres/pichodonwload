#!/bin/bash
# ==============================================================================
# Script de Despliegue y Optimización para Producción en aaPanel
# Proyecto: PICHO Roms / ROMHUB
# ==============================================================================

set -e

echo "🚀 Iniciando despliegue y optimización para producción en aaPanel..."

# Detectar binario de PHP 8.3 (en aaPanel se ubica en /www/server/php/83/bin/php)
if [ -f "/www/server/php/83/bin/php" ]; then
    PHP_BIN="/www/server/php/83/bin/php"
elif command -v php8.3 &> /dev/null; then
    PHP_BIN="php8.3"
else
    PHP_BIN="php"
fi

echo "Usando PHP: $($PHP_BIN -v | head -n 1)"

# 1. Poner la aplicación en modo mantenimiento temporalmente
echo "⏸️  Activando modo mantenimiento..."
$PHP_BIN artisan down --retry=60 || true

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
$PHP_BIN artisan migrate --force

# 5. Asegurar enlace simbólico de almacenamiento público (storage:link)
echo "🔗 Verificando enlace simbólico storage:link..."
$PHP_BIN artisan storage:link || true

# 6. Limpiar y cachear configuraciones, rutas, vistas y eventos
echo "⚡ Optimizando caches de Laravel..."
$PHP_BIN artisan optimize:clear
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache
$PHP_BIN artisan event:cache

# 7. Ajustar permisos recomendados para el usuario de aaPanel (www:www)
echo "🔒 Ajustando permisos para usuario www en aaPanel..."
mkdir -p public/uploads/screenshots public/uploads/covers public/uploads/banners
if id "www" &>/dev/null; then
    chown -R www:www storage bootstrap/cache public/uploads
    chmod -R 775 storage bootstrap/cache public/uploads
fi

# 8. Reactivar la aplicación
echo "▶️  Desactivando modo mantenimiento..."
$PHP_BIN artisan up

echo "✅ ¡Despliegue y optimización completados exitosamente en aaPanel!"
