FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl \
    sqlite3 \
    libsqlite3-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions required by Laravel
RUN docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Configure Apache Document Root to /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# Set environment variables for production build
ENV PORT=80
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install PHP dependencies & build frontend assets
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# Create necessary storage directories
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache bootstrap/cache database

# Fix permissions for storage, cache, and database
RUN chmod -R 777 storage bootstrap/cache database

EXPOSE 80

# Run entrypoint script with proper ownership and permissions
CMD touch database/database.sqlite && chmod 777 database/database.sqlite && php artisan migrate --force --seed && php artisan config:clear && apache2-foreground
