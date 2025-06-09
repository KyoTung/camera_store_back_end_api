#!/bin/sh
set -e

# Thiết lập port động cho Apache (Render sẽ truyền biến PORT)
export PORT=${PORT:-8080}
echo "Listen $PORT" > /etc/apache2/ports.conf

# Sửa VirtualHost để Apache lắng nghe đúng PORT
sed -i "s/80>/$PORT>/g" /etc/apache2/sites-available/000-default.conf

# Chạy migrate và storage:link (bỏ qua lỗi migrate nếu có)
php artisan migrate --force || true
php artisan storage:link || true

# Khởi động Apache (foreground)
exec apache2-foreground
