#!/bin/bash

# Fix ImageMagick conflict
sudo apt-get purge php-imagick -y
sudo apt-get autoremove -y
sudo pecl uninstall imagick
sudo apt-get install -y libmagickwand-dev imagemagick
sudo pecl install imagick
sudo sed -i 's/^extension=imagick/;extension=imagick/' /etc/php/8.2/apache2/conf.d/20-imagick.ini

# Create temp directory
mkdir -p storage/framework/tmp
chmod 775 storage/framework/tmp
chown -R www-data:www-data storage/framework/tmp

# Update .env
echo "TMP_PATH=storage/framework/tmp" >> .env

# Patch Filesystem.php
sed -i "s/return tempnam(\$directory, \$prefix);/\
\$tmpDir = storage_path('framework\/tmp');\
if (!is_dir(\$tmpDir)) {\n    mkdir(\$tmpDir, 0777, true);\n}\n\
return tempnam(\$tmpDir, \$prefix);\
/" vendor/laravel/framework/src/Illuminate/Filesystem/Filesystem.php

# Clear caches
php artisan config:clear
php artisan cache:clear
composer dump-autoload

echo "Fixes applied successfully!"
