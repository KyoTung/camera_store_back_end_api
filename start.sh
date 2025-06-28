#!/bin/bash
set -e

# Thiết lập port động
PORT=${PORT:-8080}

# Cấu hình Apache
echo "Listen $PORT" > /etc/apache2/ports.conf
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/*.conf

# Cấu hình Laravel
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || echo "Storage link already exists"
php artisan optimize

# Sửa quyền (quan trọng cho Fly.io)
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Khởi động Apache
exec apache2-foreground
