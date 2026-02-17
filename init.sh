#!/bin/sh

# Create uploads directory if not exists
mkdir -p /var/www/html/uploads
chown -R www-data:www-data /var/www/html/uploads
chmod -R 777 /var/www/html/uploads

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
nginx -g 'daemon off;'
