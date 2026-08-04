# PinSA Portfolio — Internal Website Portfolio Management System

Milestone 1 (Foundation) only.

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

## Default admin

| Field    | Value             |
|----------|-------------------|
| Email    | admin@example.com |
| Password | password          |

Change this after first login in any shared environment.

## Tests

```bash
php artisan test
```

## Drivers (Hostinger-safe)

`.env.example` sets:

- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`

Never use Redis on production shared hosting.

## Stack notes

- Laravel 13 · Livewire 4 · Tailwind 4 · Alpine (via Livewire) · Pest
- Roles are many-to-many (`role_user.project_id` null = global for M1)
- Effective permissions = union of all assigned roles
