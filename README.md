# PinSA Portfolio — Internal Website Portfolio Management System

Through **Milestone 2** (Projects & credentials). Later milestones are not built yet.

## Requirements

- PHP 8.2+ (8.3+ recommended; Laravel 13 targets ^8.3)
- Composer
- Node.js 20+ (local asset builds only; not required on Hostinger)
- SQLite (local) or MySQL 8 (production)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate

# SQLite (default local)
touch database/database.sqlite

# Or configure MySQL in .env, then:
php artisan migrate --seed

npm install
npm run build

php artisan serve
```

Open http://127.0.0.1:8000 and sign in.

## Default accounts (seed)

| Role       | Email                   | Password |
|------------|-------------------------|----------|
| Admin      | admin@example.com       | password |
| Partner    | partner@example.com     | password |
| Supervisor | supervisor@example.com  | password |
| Staff      | staff@example.com       | password |

Demo projects: `alpha-demo.test`, `beta-demo.test` (with vault credentials + expiring SSL).

Change seed passwords after first login in any shared environment.

## Scheduled jobs (cPanel cron)

```bash
php artisan schedule:run
# daily: credentials:check-expiry --notify
php artisan queue:work --stop-when-empty
```

## Tests

```bash
php artisan test
```

## Drivers (Hostinger-safe)

`.env.example` sets SESSION/CACHE/QUEUE to file or database — never Redis for production on shared hosting.
