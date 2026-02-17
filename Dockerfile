# Use PHP 8.1 FPM Alpine as base image
FROM php:8.1-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    mysqli \
    gd \
    zip

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Copy nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 777 /var/www/html/uploads

# Create necessary directories for nginx and php-fpm
RUN mkdir -p /var/run/nginx && \
    mkdir -p /var/run/php-fpm && \
    chown -R www-data:www-data /var/run/nginx /var/run/php-fpm

# Expose port 8080 (Railway requirement)
EXPOSE 8080

# Make init script executable
RUN chmod +x /var/www/html/init.sh

# Start services
CMD ["/var/www/html/init.sh"]
