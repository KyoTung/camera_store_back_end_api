# Sử dụng image Debian Slim làm nền tảng
FROM debian:bookworm-slim

# Cài đặt các phụ thuộc hệ thống
RUN apt-get update && apt-get install -y \
    ca-certificates \
    curl \
    apache2 \
    libapache2-mod-php \
    php \
    php-cli \
    php-common \
    php-curl \
    php-mbstring \
    php-xml \
    php-zip \
    php-mysql \
    php-gd \
    php-bcmath \
    php-intl \
    php-imagick \
    imagemagick \
    libmagickwand-dev \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Cấu hình PHP
RUN echo "memory_limit = 512M" >> /etc/php/8.2/apache2/php.ini && \
    echo "upload_max_filesize = 100M" >> /etc/php/8.2/apache2/php.ini && \
    echo "post_max_size = 100M" >> /etc/php/8.2/apache2/php.ini && \
    echo "max_execution_time = 300" >> /etc/php/8.2/apache2/php.ini

# Thiết lập múi giờ Việt Nam
ENV TZ=Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Cài đặt Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Thiết lập Apache
RUN a2enmod rewrite && \
    a2enmod headers && \
    rm /etc/apache2/sites-enabled/000-default.conf

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Copy mã nguồn
COPY . .

# Cài đặt các gói Composer
RUN composer require cloudinary-labs/cloudinary-laravel --no-interaction && \
    composer install --no-dev --optimize-autoloader

# Thiết lập quyền
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 storage bootstrap/cache public/uploads
COPY vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2ensite 000-default
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh
# Expose port
EXPOSE 8080
CMD ["/usr/local/bin/start.sh"]
