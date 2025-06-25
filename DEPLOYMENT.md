# isalesbookv2 Deployment Guide

## Railway Deployment

### Prerequisites
- GitHub repository with your code
- Railway account

### Step 1: Prepare Your Repository
1. Ensure all files are committed and pushed to GitHub
2. Make sure your repository is public or Railway has access

### Step 2: Deploy to Railway
1. Go to [Railway Dashboard](https://railway.app/dashboard)
2. Click "New Project"
3. Select "Deploy from GitHub repo"
4. Choose your repository
5. Railway will automatically detect it's a PHP project

### Step 3: Configure Environment Variables
Add these environment variables in Railway:

```env
APP_NAME=isalesbookv2
APP_ENV=production
APP_KEY=base64:your-app-key-here
APP_DEBUG=false
APP_URL=https://your-railway-app-url.railway.app

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=your-railway-mysql-host
DB_PORT=3306
DB_DATABASE=your-railway-mysql-database
DB_USERNAME=your-railway-mysql-username
DB_PASSWORD=your-railway-mysql-password

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MEMCACHED_HOST=127.0.0.1

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_APP_NAME="${APP_NAME}"
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"

# Swagger Configuration
L5_SWAGGER_GENERATE_ALWAYS=true
L5_SWAGGER_UI_DOC_EXPANSION=list
L5_SWAGGER_UI_OPERATIONS_SUFFIX=
L5_SWAGGER_UI_TAGS_SORTER=alpha
L5_SWAGGER_UI_FILTERS=true
```

### Step 4: Add MySQL Database (Optional)
1. In Railway, go to your project
2. Click "New" → "Database" → "MySQL"
3. Railway will automatically add the database environment variables
4. The migration will run automatically on deployment

### Step 5: Deploy
1. Railway will automatically deploy when you push to your main branch
2. Or manually trigger deployment from the Railway dashboard

### Step 6: Access Your Application
- **Main URL**: `https://your-railway-app-url.railway.app`
- **Swagger UI**: `https://your-railway-app-url.railway.app/api/documentation`
- **Health Check**: `https://your-railway-app-url.railway.app/api/health`
- **API Info**: `https://your-railway-app-url.railway.app/api/info`

## API Endpoints

### Email Module
- `POST /api/email/send` - Send email
- `GET /api/email/logs` - Get email logs
- `GET /api/email/logs/{id}` - Get specific email log

### Logger Module
- `POST /api/logger/log` - Create log entry
- `GET /api/logger/logs` - Get logs with filters
- `GET /api/logger/statistics` - Get log statistics
- `POST /api/logger/emergency` - Log emergency
- `POST /api/logger/error` - Log error
- `POST /api/logger/warning` - Log warning
- `POST /api/logger/info` - Log info

### Registration Module
- `POST /api/registration/register` - Register new user
- `POST /api/registration/verify-email` - Verify email
- `GET /api/registration/profile` - Get user profile (authenticated)
- `PUT /api/registration/profile` - Update profile (authenticated)
- `POST /api/registration/forgot-password` - Forgot password
- `POST /api/registration/reset-password` - Reset password

## Testing Your Deployment

1. **Health Check**: Visit `/api/health` to ensure the app is running
2. **Swagger UI**: Visit `/api/documentation` to test all APIs
3. **Landing Page**: Visit `/` - should redirect to Swagger UI

## Troubleshooting

### Common Issues
1. **500 Error**: Check environment variables and database connection
2. **Migration Failed**: Ensure database credentials are correct
3. **Swagger Not Loading**: Check if `L5_SWAGGER_GENERATE_ALWAYS=true` is set

### Logs
- Check Railway logs in the dashboard
- Laravel logs are in `storage/logs/laravel.log`

### Database
- If using Railway MySQL, the connection should be automatic
- If using external database, ensure the host is accessible

## Local Development

To run locally:
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan l5-swagger:generate
php artisan serve
```

Then visit `http://localhost:8000` (redirects to Swagger UI). 