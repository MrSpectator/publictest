#!/bin/bash

# Run database migrations and cache clear (optional, adjust as needed)
php artisan migrate --force || true
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start Supervisor (which runs both PHP-FPM and nginx)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf