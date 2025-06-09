FROM php:8.2-apache

# Cài các extension cần thiết cho Laravel
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Cài Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy source code vào container
COPY . .

# Cài đặt các package PHP
RUN composer install --no-dev --optimize-autoloader

# Cấp quyền cho storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Tạo symlink storage:link (nếu cần)
RUN php artisan storage:link || true

# Bật rewrite module cho Apache
RUN a2enmod rewrite

# Sửa DocumentRoot cho Apache về public (Laravel)
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Expose port 8080 (Render sử dụng PORT env)
EXPOSE 8080

# Copy script start.sh vào container (nếu có dùng)
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Chạy start.sh làm CMD duy nhất
CMD ["/usr/local/bin/start.sh"]
