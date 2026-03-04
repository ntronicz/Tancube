# Stage 1: Build Frontend Assets
FROM node:20-alpine as frontend

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY . .
RUN npm run build

# Stage 2: Production Application
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    supervisor \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Configure Nginx
COPY .docker/nginx.conf /etc/nginx/http.d/default.conf
RUN mkdir -p /run/nginx

# Configure PHP-FPM
COPY .docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Set working directory
WORKDIR /var/www/html

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .
COPY --from=frontend /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Supervisor config
COPY .docker/supervisord.conf /etc/supervisord.conf

# Expose ports
EXPOSE 80

# Start Supervisor (which runs Nginx and PHP-FPM)
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
