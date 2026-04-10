# Use official PHP 8.1 image with FPM
FROM php:8.1-fpm

# Install system dependencies, Nginx, and PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . .

# Install PHP dependencies (PHPMailer)
RUN composer install --no-dev --optimize-autoloader

# Set permissions for uploads and cache
RUN mkdir -p /var/www/html/uploads && chmod -R 775 /var/www/html/uploads
RUN mkdir -p /var/www/html/cache && chmod -R 775 /var/www/html/cache

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/sites-enabled/default

# Expose port 80
EXPOSE 80

# Start PHP-FPM and Nginx
CMD service php8.1-fpm start && nginx -g "daemon off;"
