

FROM php:8.2-apache

# Cài các extension cần thiết cho Laravel (tùy nhu cầu, ví dụ: pdo, gd, zip, ...)
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

# Copy source vào container
COPY . /var/www/html

# Cấp quyền cho storage & bootstrap/cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Tạo symlink storage:link (nếu cần)
RUN cd /var/www/html && php artisan storage:link || true

# Copy file apache vhost nếu tùy chỉnh
# COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf

# Bật rewrite module cho Apache
RUN a2enmod rewrite

# Expose port 8080 (Render sử dụng PORT env)
EXPOSE 8080
COPY start.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/start.sh

CMD ["/usr/local/bin/start.sh"]
# Start Apache
CMD ["apache2-foreground"]
