# Sử dụng image chính thức PHP 8.2 Apache dựa trên Debian Bookworm
FROM php:8.2-apache-bookworm

# Cài đặt các extension PHP cần thiết cho Laravel và các thư viện hệ thống
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

# Thiết lập múi giờ Việt Nam
ENV TZ=Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Cài đặt Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn (sử dụng .dockerignore để loại trừ các file không cần thiết)
COPY . .

# Cài đặt các gói Composer (bao gồm cả cloudinary) và tối ưu hóa autoloader
RUN composer require cloudinary-labs/cloudinary-laravel --no-interaction \
    && composer install --no-dev --optimize-autoloader

# Thiết lập quyền cho các thư mục cần thiết
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache public/uploads

# Bật mod rewrite của Apache
RUN a2enmod rewrite

# Copy file cấu hình virtual host
COPY vhost.conf /etc/apache2/sites-available/000-default.conf

# Copy script khởi động
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

# Expose port 8080 (mặc định, nhưng có thể thay đổi bằng biến môi trường)
EXPOSE 8080

# Sử dụng start.sh để khởi động
CMD ["/usr/local/bin/start.sh"]
