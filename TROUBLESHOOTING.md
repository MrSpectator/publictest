# Railway Deployment Troubleshooting

## Healthcheck Failure Issues

### Problem: Healthcheck fails during deployment

**Solution 1: Use Simple Healthcheck**
- The app now uses `/api/ping` for healthcheck (no database required)
- This endpoint responds immediately without database queries

**Solution 2: Check Environment Variables**
Make sure these are set in Railway:
```env
APP_NAME=isalesbookv2
APP_ENV=production
APP_KEY=base64:your-generated-key
APP_DEBUG=false
APP_URL=https://your-railway-app-url.railway.app
```

**Solution 3: Database Connection**
If using Railway MySQL:
1. Add MySQL service to your project
2. Railway will auto-set DB_* environment variables
3. The startup script will handle migrations

### Problem: App doesn't start

**Check Railway Logs:**
1. Go to your Railway project
2. Click on the service
3. Check the "Deployments" tab
4. View logs for error messages

**Common Issues:**
1. **Missing APP_KEY**: The startup script will generate one
2. **Database connection**: Ensure DB credentials are correct
3. **Port binding**: App uses `$PORT` environment variable

### Problem: 500 Errors

**Check Laravel Logs:**
1. SSH into your Railway container
2. Check `storage/logs/laravel.log`
3. Look for specific error messages

**Common Fixes:**
1. Clear caches: `php artisan config:clear`
2. Regenerate autoload: `composer dump-autoload`
3. Check file permissions: `chmod -R 775 storage bootstrap/cache`

## Manual Deployment Steps

If automatic deployment fails:

1. **SSH into Railway container:**
   ```bash
   railway shell
   ```

2. **Run setup manually:**
   ```bash
   php artisan key:generate
   php artisan config:cache
   php artisan migrate --force
   php artisan l5-swagger:generate
   php artisan storage:link
   chmod -R 775 storage bootstrap/cache
   ```

3. **Start the app:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=$PORT
   ```

## Environment Variables Checklist

**Required for Railway:**
- `APP_NAME=isalesbookv2`
- `APP_ENV=production`
- `APP_KEY` (auto-generated if missing)
- `APP_URL=https://your-app.railway.app`

**Database (if using Railway MySQL):**
- `DB_CONNECTION=mysql`
- `DB_HOST` (auto-set by Railway)
- `DB_PORT=3306`
- `DB_DATABASE` (auto-set by Railway)
- `DB_USERNAME` (auto-set by Railway)
- `DB_PASSWORD` (auto-set by Railway)

**Swagger:**
- `L5_SWAGGER_GENERATE_ALWAYS=true`

## Testing After Deployment

1. **Health Check:** `https://your-app.railway.app/api/ping`
2. **Full Health:** `https://your-app.railway.app/api/health`
3. **Swagger UI:** `https://your-app.railway.app/api/documentation`
4. **Landing Page:** `https://your-app.railway.app/` (redirects to Swagger)

## Contact Support

If issues persist:
1. Check Railway status: https://status.railway.app/
2. Review Railway documentation: https://docs.railway.app/
3. Check Laravel logs for specific error messages 