# Tech Stack

## Framework & Language
- PHP 8.2+ with strict types
- Laravel 11.x (MVC + Service Layer + Domain Layer)
- MySQL (default database, `ipm_penggajian`)

## Key Libraries
- **laravel/sanctum** — API/session authentication
- **barryvdh/laravel-dompdf** — PDF generation (payslips, invoices, reports)
- **maatwebsite/excel** — Excel import/export (attendance, reports)
- **laravel/pint** — Code style (PSR-12)
- **pestphp/pest** — Testing framework (with Laravel plugin)

## Frontend
- Blade templates with layouts (`app.blade.php`, `guest.blade.php`)
- Tailwind CSS (via CDN or build)
- Alpine.js for interactivity

## Common Commands
```bash
# Run tests
php artisan test
# or
./vendor/bin/pest

# Code formatting
./vendor/bin/pint

# Database
php artisan migrate
php artisan db:seed

# Clear caches
php artisan optimize:clear

# Run queue worker (for async jobs)
php artisan queue:work
```

## Configuration
- Environment: `.env` file (copy from `.env.example`)
- Rate limiting: 10 login attempts/minute per IP
- Global middleware: SanitizeInput (XSS/CSV injection prevention)
- Custom middleware aliases: `role` (CheckRole), `throttle.login`
