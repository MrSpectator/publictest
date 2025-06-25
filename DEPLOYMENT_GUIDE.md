# Railway Deployment Guide for isalesbookv2

This guide will help you deploy your Laravel API to Railway.

## Prerequisites

1. A Railway account (free tier available)
2. Your Laravel project pushed to GitHub
3. A MySQL database (Railway provides this)

## Step 1: Connect to Railway

1. Go to [Railway.app](https://railway.app)
2. Sign in with your GitHub account
3. Click "New Project"
4. Select "Deploy from GitHub repo"
5. Choose your repository

## Step 2: Add Database

1. In your Railway project, click "New"
2. Select "Database" → "MySQL"
3. Wait for the database to be provisioned
4. Copy the database connection details

## Step 3: Configure Environment Variables

In your Railway project, go to the "Variables" tab and add the following environment variables:

### Required Variables

```bash
# Application
APP_NAME=isalesbookv2
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app-name.railway.app

# Database (use Railway's MySQL connection details)
DB_CONNECTION=mysql
DB_HOST=your-mysql-host.railway.app
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=your-mysql-password

# Mail Configuration (for email module)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="isalesbookv2"

# Swagger Documentation
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_UI_DOC_EXPANSION=list
L5_SWAGGER_UI_OPERATIONS_SORTER=alpha
L5_SWAGGER_UI_TAGS_SORTER=alpha
L5_SWAGGER_UI_DISPLAY_REQUEST_DURATION=true
L5_SWAGGER_UI_PERSIST_AUTHORIZATION=true
L5_SWAGGER_UI_LAYOUT=BaseLayout
L5_SWAGGER_UI_DEEP_LINKING=true
L5_SWAGGER_UI_DISPLAY_OPERATION_ID=false
L5_SWAGGER_UI_DEFAULT_MODELS_EXPAND_DEPTH=1
L5_SWAGGER_UI_DEFAULT_MODEL_EXPAND_DEPTH=1
L5_SWAGGER_UI_SHOW_EXTENSIONS=true
L5_SWAGGER_UI_SHOW_COMMON_EXTENSIONS=true
L5_SWAGGER_UI_TRY_IT_OUT_ENABLED=true
L5_SWAGGER_UI_FILTERS_ENABLED=true
L5_SWAGGER_UI_APIS_SORTER=alpha
L5_SWAGGER_UI_MODELS_SORTER=alpha
L5_SWAGGER_UI_VALIDATOR_URL=
L5_SWAGGER_UI_WITH_CREDENTIALS=false

# Other Laravel Settings
LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug
BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
```

### Optional Variables

```bash
# Redis (if you want to use Redis)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# AWS S3 (if you want to use S3 for file storage)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

## Step 4: Deploy

1. Railway will automatically detect your Laravel project
2. The deployment will use the `railway-start.sh` script
3. This script will:
   - Generate an application key if not set
   - Run database migrations
   - Generate Swagger documentation
   - Cache configurations
   - Start the web server

## Step 5: Verify Deployment

Once deployed, you can test your API endpoints:

- **Health Check**: `https://your-app-name.railway.app/api/health`
- **API Info**: `https://your-app-name.railway.app/api/info`
- **Swagger Docs**: `https://your-app-name.railway.app/api/documentation`

## API Endpoints

### Email Module
- `POST /api/email/send` - Send email
- `GET /api/email/logs` - Get email logs

### Logger Module
- `POST /api/logger/log` - Create log entry
- `GET /api/logger/logs` - Get logs
- `GET /api/logger/logs/{id}` - Get specific log
- `DELETE /api/logger/logs/{id}` - Delete log
- `GET /api/logger/statistics` - Get statistics

### Registration Module
- `POST /api/registration/register` - Register user
- `POST /api/registration/verify-email` - Verify email
- `POST /api/registration/resend-verification` - Resend verification
- `POST /api/registration/forgot-password` - Forgot password
- `POST /api/registration/reset-password` - Reset password
- `GET /api/registration/profile` - Get profile
- `PUT /api/registration/profile` - Update profile

## Troubleshooting

### Common Issues

1. **500 Error**: Check if APP_KEY is set
2. **Database Connection Error**: Verify database credentials
3. **Migration Errors**: Check database permissions
4. **Swagger Not Loading**: Ensure L5_SWAGGER_GENERATE_ALWAYS=true

### Logs

Check Railway logs in the "Deployments" tab for any errors.

### Manual Commands

You can run manual commands in Railway's terminal:

```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Generate Swagger docs
php artisan l5-swagger:generate

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Environment Variables Reference

### Database Variables
- `DB_CONNECTION`: Database driver (mysql, pgsql, sqlite)
- `DB_HOST`: Database host
- `DB_PORT`: Database port
- `DB_DATABASE`: Database name
- `DB_USERNAME`: Database username
- `DB_PASSWORD`: Database password

### Mail Variables
- `MAIL_MAILER`: Mail driver (smtp, mailgun, ses, etc.)
- `MAIL_HOST`: SMTP host
- `MAIL_PORT`: SMTP port
- `MAIL_USERNAME`: SMTP username
- `MAIL_PASSWORD`: SMTP password
- `MAIL_ENCRYPTION`: Encryption type (tls, ssl)
- `MAIL_FROM_ADDRESS`: From email address
- `MAIL_FROM_NAME`: From name

### Swagger Variables
- `L5_SWAGGER_GENERATE_ALWAYS`: Always generate docs
- `L5_SWAGGER_UI_DOC_EXPANSION`: Doc expansion mode
- `L5_SWAGGER_UI_OPERATIONS_SORTER`: Sort operations
- `L5_SWAGGER_UI_TAGS_SORTER`: Sort tags
- `L5_SWAGGER_UI_DISPLAY_REQUEST_DURATION`: Show request duration
- `L5_SWAGGER_UI_PERSIST_AUTHORIZATION`: Persist auth
- `L5_SWAGGER_UI_LAYOUT`: UI layout
- `L5_SWAGGER_UI_DEEP_LINKING`: Enable deep linking
- `L5_SWAGGER_UI_DISPLAY_OPERATION_ID`: Show operation ID
- `L5_SWAGGER_UI_DEFAULT_MODELS_EXPAND_DEPTH`: Models expand depth
- `L5_SWAGGER_UI_DEFAULT_MODEL_EXPAND_DEPTH`: Model expand depth
- `L5_SWAGGER_UI_SHOW_EXTENSIONS`: Show extensions
- `L5_SWAGGER_UI_SHOW_COMMON_EXTENSIONS`: Show common extensions
- `L5_SWAGGER_UI_TRY_IT_OUT_ENABLED`: Enable try it out
- `L5_SWAGGER_UI_FILTERS_ENABLED`: Enable filters
- `L5_SWAGGER_UI_APIS_SORTER`: Sort APIs
- `L5_SWAGGER_UI_MODELS_SORTER`: Sort models
- `L5_SWAGGER_UI_VALIDATOR_URL`: Validator URL
- `L5_SWAGGER_UI_WITH_CREDENTIALS`: With credentials

## Support

If you encounter any issues:

1. Check Railway logs
2. Verify environment variables
3. Test locally first
4. Check Laravel logs in Railway terminal 