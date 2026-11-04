#!/bin/sh
set -e

# Buat direktori storage & framework cache jika belum ada
mkdir -p /var/www/storage/framework/cache/data \
         /var/www/storage/framework/sessions \
         /var/www/storage/framework/views \
         /var/www/storage/logs \
         /var/www/bootstrap/cache \
         /var/www/storage/app/public

# Pastikan file .env tersedia (fallback ke .env.example jika tidak dimount)
if [ ! -f "/var/www/.env" ] && [ -f "/var/www/.env.example" ]; then
    cp /var/www/.env.example /var/www/.env
    php artisan key:generate --force || true
fi

# Pastikan vendor autoload tersedia
if [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "Composer vendor tidak ditemukan, menjalankan composer install..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Buat symlink storage jika belum ada
php artisan storage:link 2>/dev/null || true

# Atur permissions direktori penyimpanan dan cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true

exec "$@"
