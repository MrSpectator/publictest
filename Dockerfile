# Build stage
FROM composer:2.7 as build

WORKDIR /app

# Copy composer files and install dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs

# Copy the rest of the app
COPY . .

# Install node dependencies and build assets (if present)
RUN if [ -f package.json ]; then npm install && npm run build || true; fi

# Runtime stage
FROM php:8.2-cli

WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev zip unzip git curl libzip-dev \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=build /usr/bin/composer /usr/bin/composer

# Copy app files
COPY --from=build /app /app

# Expose the port Render expects
EXPOSE 10000

# Entrypoint: generate key, cache config, migrate, generate swagger, serve
CMD php artisan key:generate --force && \
    php artisan config:cache && \
    php artisan migrate --force && \
    php artisan l5-swagger:generate && \
    php artisan serve --host=0.0.0.0 --port=10000 