FROM php:8.2-apache

# Cài các extension cần thiết cho Laravel + Imagick
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libmagickwand-dev \
    imagemagick \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && pecl install imagick \
    && docker-php-ext-enable imagick
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
# Copy source code vào container
COPY . .
# Cài đặt package Cloudinary cho Laravel
RUN composer require cloudinary-labs/cloudinary-laravel
# Cài đặt các package PHP
RUN composer install --no-dev --optimize-autoloader

# Đảm bảo các thư mục cần thiết tồn tại và phân quyền đúng
RUN mkdir -p /var/www/html/public/uploads/temp \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/public/uploads

# Tạo symlink storage:link (nếu cần)
RUN php artisan storage:link || true

# Bật rewrite module cho Apache
RUN a2enmod rewrite

# Sửa DocumentRoot cho Apache về public (Laravel)
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf

# Đảm bảo Apache lắng nghe đúng PORT env của Fly.io
RUN sed -i "s/80/\${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Expose port 8080
EXPOSE 8080

# Copy script start.sh vào container (nếu có dùng)
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Chạy start.sh làm CMD duy nhất
CMD ["/usr/local/bin/start.sh"]
