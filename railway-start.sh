#!/bin/bash

# Exit on any error
set -e

echo "🚀 Starting Laravel application deployment..."

# Generate application key if not set
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Run database migrations
echo "🗄️ Running database migrations..."
php artisan migrate --force

# Generate Swagger documentation
echo "📚 Generating Swagger documentation..."
php artisan l5-swagger:generate

# Clear and cache configuration
echo "⚡ Optimizing application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start the application
echo "🌐 Starting web server..."
php artisan serve --host=0.0.0.0 --port=$PORT 