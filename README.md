# iSalesBook v2

A comprehensive modular sales management system built with Laravel 12, featuring Email, Logger, Registration, and Authentication modules.

## Features

### Email Module
- Robust email handling system with queue support
- Email logging and tracking with status management
- Support for multiple attachments (jpg, jpeg, png, pdf, doc, docx, txt)
- CC and BCC functionality with comma-separated email addresses
- Email status tracking (pending, sent, failed)
- Retry mechanism for failed emails
- File size validation (max 10MB per attachment)

### Logger Module
- Comprehensive system logging with multiple log levels
- Support for emergency, alert, critical, error, warning, notice, info, debug levels
- Context-aware logging with JSON support
- IP address and user agent tracking
- Source identification for better debugging
- Structured logging for production environments

### Registration Module
- User registration with email verification
- Password reset functionality
- Profile management (view and update)
- Secure password confirmation
- Date of birth validation
- Email verification with token-based system

### Authentication Module
- User login and logout functionality
- Sanctum-based authentication
- Current user profile retrieval
- Secure session management

## Installation

1. Clone the repository:
```bash
git clone https://github.com/RDAS-SOLUTIONS-LIMITED/isalesbookV2.git
cd isalesbookv2
```

2. Install dependencies:
```bash
composer install
```

3. Copy the environment file:
```bash
cp .env.example .env
```

4. Generate application key:
```bash
php artisan key:generate
```

5. Configure your database in `.env` file:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=isalesbookv2_db
DB_USERNAME=root
DB_PASSWORD=
```

6. Run migrations:
```bash
php artisan migrate
```

7. Start the queue worker:
```bash
php artisan queue:work
```

## Email Configuration

Configure your email settings in `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=......
MAIL_PORT=465
MAIL_USERNAME=info@isalesbook.com
MAIL_PASSWORD=......
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@isalesbook.com
MAIL_FROM_NAME="iSalesBook"
FRONTEND_URL=http://localhost:3000
```

## API Documentation

The API documentation is available in OpenAPI format at `public/swagger/openapi.yaml`. You can view it using Swagger UI at `/swagger` endpoint.

### API Endpoints

#### System
- `GET /api/info` - Get API information and module status

#### Email Module
- `POST /api/email/send` - Send an email with attachments
- `GET /api/email/logs` - Get email logs with filtering options

#### Logger Module
- `POST /api/logger/log` - Create a log entry
- `POST /api/logger/{level}` - Log by specific level
- `GET /api/logger/logs` - Get system logs

#### Registration Module
- `POST /api/registration/register` - Register new user
- `POST /api/registration/verify-email` - Verify email address
- `POST /api/registration/forgot-password` - Send password reset email
- `POST /api/registration/reset-password` - Reset password
- `GET /api/registration/profile` - Get user profile
- `PUT /api/registration/profile` - Update user profile

#### Authentication Module
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user information

## Queue Configuration

The application uses Laravel's queue system for processing emails. Configure your queue driver in `.env`:

```
QUEUE_CONNECTION=database
```

## Development

For development, you can use the provided composer script:

```bash
composer run dev
```

This will start:
- Laravel development server
- Queue listener
- Log viewer (Pail)
- Vite development server

## Testing

Run the test suite:

```bash
composer test
```

Or use the artisan command:

```bash
php artisan test
```

## Production Deployment

The application is configured for production deployment at `https://backend-v2.isalesbook.com`.

### Environment Variables for Production

Make sure to set the following environment variables in production:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://backend-v2.isalesbook.com
FRONTEND_URL=https://frontend-domain.com
```

## Project Structure

```
app/
├── Modules/
│   ├── Email/          # Email handling module
│   ├── Logger/         # System logging module
│   ├── Registration/   # User registration module
│   └── Auth/          # Authentication module
├── Http/Controllers/
├── Models/
└── Providers/
```

## Dependencies

- **Laravel 12** - PHP framework
- **Laravel Sanctum** - API authentication
- **Laravel Socialite** - Social authentication
- **PHPMailer** - Email handling
- **L5-Swagger** - API documentation
- **Laravel Pail** - Log viewing

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please contact the development team or create an issue in the repository. 