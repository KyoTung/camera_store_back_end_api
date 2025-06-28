#!/bin/sh
set -e

# Thiết lập port động cho Apache (mặc định 8080)
export PORT=${PORT:-8080}

# Tạo file ports.conf mới với PORT động
echo "Listen $PORT" > /etc/apache2/ports.conf

# Sửa VirtualHost để lắng nghe đúng PORT
sed -i "s/:80/:$PORT/g" /etc/apache2/sites-available/000-default.conf

# Chạy các lệnh Laravel
php artisan migrate --force || echo "Migration skipped or failed"
php artisan storage:link || echo "Storage link already exists"
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Khởi động Apache
exec apache2-foreground
