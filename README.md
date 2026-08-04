# PinSA Portfolio — Internal Website Portfolio Management System

Through **Milestone 6** (Hostinger FTP packaging, deployment docs, ops route).

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

Production deploy: **[DEPLOYMENT.md](DEPLOYMENT.md)**.

## Default accounts (seed)

| Role         | Email                      | Password |
|--------------|----------------------------|----------|
| Admin        | admin@example.com          | password |
| Partner      | partner@example.com        | password |
| Supervisor   | supervisor@example.com     | password |
| Staff        | staff@example.com          | password |
| Accountant   | accountant@example.com     | password |

Demo projects: `alpha-demo.test`, `beta-demo.test` (credentials + work + people fixtures).  
Money demo: sample revenues (current + prior month), shared SaaS expense, direct hosting, partner capital, draft distribution for prior month.

Change seed passwords after first login in any shared environment.

## Scheduled jobs (cPanel cron)

```bash
php artisan schedule:run
```

Includes: credential expiry, recurring tasks, recurring expenses (`expenses:generate-recurring`). Pair with `queue:work --stop-when-empty` if using database queue. Full Hostinger cron examples are in DEPLOYMENT.md.

## Packaging (Milestone 6)

```bash
./deploy/package.sh          # → deploy/dist/app.zip + public.zip
# or: powershell -File deploy/package.ps1
```
