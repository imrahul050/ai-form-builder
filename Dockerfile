FROM php:8.2-cli

# Install system dependencies & PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev

RUN docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Set up environment file & sqlite file before composer install
RUN cp .env.example .env && \
    mkdir -p storage/framework/views storage/framework/cache storage/framework/sessions bootstrap/cache database && \
    touch database/database.sqlite && \
    chmod -R 777 storage bootstrap/cache database

# Install composer production dependencies cleanly
RUN composer install --no-dev --optimize-autoloader --no-scripts --ignore-platform-reqs

# Generate key, discover packages, and seed database inside container
RUN php artisan key:generate --force && \
    php artisan package:discover --ansi && \
    php artisan migrate --force --seed && \
    php artisan storage:link || true

# Expose port and startup command
EXPOSE 8080
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
