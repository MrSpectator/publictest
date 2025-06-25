# Laravel Modular Monolith (No Docker)

This is a standard Laravel project, ready for deployment on any PHP host.

## Local Development

1. Install PHP (8.1+), Composer, and a database (e.g., MySQL/Postgres).
2. Clone the repo and run:
   ```sh
   composer install
   cp .env.example .env
   php artisan key:generate
   # Set up your DB credentials in .env
   php artisan migrate --seed
   php artisan serve
   ```
3. Visit http://localhost:8000

## Free Deployment Options

### Railway (Recommended)
1. Push your code to GitHub.
2. Go to [railway.app](https://railway.app/), create a project, and link your repo.
3. Set environment variables in the Railway dashboard (copy from your .env, but do not commit .env).
4. Set the start command: `php artisan serve --host=0.0.0.0 --port=8080`
5. Deploy and access your app at the provided Railway URL.

### Render
1. Push your code to GitHub.
2. Go to [render.com](https://render.com/), create a new Web Service, and link your repo.
3. Set build command: `composer install --no-dev --optimize-autoloader`
4. Set start command: `php artisan serve --host=0.0.0.0 --port=10000`
5. Set environment variables in the Render dashboard.
6. Deploy and access your app at the provided Render URL.

---

For questions, see the [Laravel documentation](https://laravel.com/docs/10.x) or your chosen platform's docs. 