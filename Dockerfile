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

# Install production composer dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions & database
RUN touch database/database.sqlite
RUN chmod -R 777 storage bootstrap/cache database

# Run migrations, seeders, storage link & start server
EXPOSE 8080
CMD php artisan migrate --force --seed && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}
