# Sử dụng image Debian 12 (Bookworm) để có PHP 8.2 mới nhất
FROM php:8.2-apache-bookworm

# Cài các extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libmagickwand-dev \
    imagemagick \
    zip \
    unzip \
    git \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && pecl install imagick \
    && docker-php-ext-enable imagick

# Cài Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Thiết lập múi giờ
ENV TZ=Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /var/www/html

# Copy source code vào container
COPY . .

# Cài đặt package Cloudinary cho Laravel
RUN composer require cloudinary-labs/cloudinary-laravel

# Cài đặt các package PHP
RUN composer install --no-dev --optimize-autoloader

# Phân quyền thư mục
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache public/uploads

# Tạo symlink storage:link
RUN php artisan storage:link || true

# Bật rewrite module cho Apache
RUN a2enmod rewrite

# Cấu hình Apache
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Expose port 8080
EXPOSE 8080

# Copy script start.sh
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Chạy start.sh
CMD ["/usr/local/bin/start.sh"]
