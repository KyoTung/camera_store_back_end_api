#!/bin/sh
set -e

# Thiết lập port động cho Apache
export PORT=${PORT:-8080}
echo "Listen $PORT" > /etc/apache2/ports.conf

# Sửa VirtualHost để Apache lắng nghe đúng PORT
sed -i "s/80>/$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Chạy migrate (bỏ qua lỗi nếu có)
php artisan migrate --force || true
# Tạo symlink storage nếu chưa có
php artisan storage:link || true
# Cache cấu hình và route (tăng hiệu năng)
php artisan config:clear
php artisan config:cache
php artisan route:cache

# Khởi động Apache ở foreground
exec apache2-foreground
