#!/bin/bash

echo "🚀 Setting up isalesbookv2 for Railway deployment..."

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo "❌ Error: artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

# Install NPM dependencies if package.json exists
if [ -f "package.json" ]; then
    echo "📦 Installing NPM dependencies..."
    npm install
    echo "🔨 Building assets..."
    npm run build
fi

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Generate application key if not exists
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Run migrations
echo "🗄️ Running migrations..."
php artisan migrate --force

# Generate Swagger documentation
echo "📚 Generating Swagger documentation..."
php artisan l5-swagger:generate

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Setup complete! Your Laravel application is ready for Railway deployment."
echo ""
echo "📋 Next steps:"
echo "1. Push your code to GitHub"
echo "2. Connect your repository to Railway"
echo "3. Add a MySQL database in Railway"
echo "4. Set the environment variables from railway-env-vars.txt"
echo "5. Deploy!"
echo ""
echo "🌐 Your API will be available at: https://your-app-name.railway.app"
echo "📖 Swagger documentation: https://your-app-name.railway.app/api/documentation" 