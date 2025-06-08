#!/bin/sh

# Thiết lập port động cho Apache
echo "Listen $PORT" > /etc/apache2/ports.conf

# Cập nhật virtual host dùng port động
sed -i "s/\${PORT:-8080}/$PORT/g" /etc/apache2/sites-available/000-default.conf

php artisan migrate --force || true
# Khởi động Apache
exec apache2-foreground
