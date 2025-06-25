#!/bin/bash

# Exit on any error
set -e

echo "Starting isalesbookv2 deployment..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Clear and cache config
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Generate Swagger documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Create storage symlink
echo "Creating storage symlink..."
php artisan storage:link

# Set proper permissions
echo "Setting permissions..."
chmod -R 775 storage bootstrap/cache

echo "Starting Laravel application..."
exec php artisan serve --host=0.0.0.0 --port=$PORT 