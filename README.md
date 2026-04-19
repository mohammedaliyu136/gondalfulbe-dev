# Gondal ERP

A multi-module ERP built on Laravel 11 covering HRM, Accounting, Milk Collection, Logistics, Cooperatives, OSS, Extension, Center Operations, and more.

---

## Requirements

- PHP 8.2+
- MySQL 8+
- Composer
- Node.js & NPM

---

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/mohammedaliyu136/gondalfulbe-dev.git
cd gondalfulbe-dev

# 2. Install PHP dependencies
composer install

# 3. Copy environment file and set your DB credentials
cp .env.example .env
php artisan key:generate

# 4. Run migrations
php artisan migrate

# 5. Install and build frontend assets
npm install && npm run build
```

---

## After Pulling Updates

> **Important:** This project uses Laravel Modules. The module registry is cached locally and is not tracked by git.
> After every `git pull`, run the following to regenerate the cache so all modules are visible:

```bash
php artisan optimize:clear
```

### One-time setup — auto-run on pull

To have this run automatically after every `git pull`:

```bash
git config core.hooksPath .githooks
```

After that, `php artisan optimize:clear` will run automatically whenever you pull.

---

## Modules

| Module | Description |
|---|---|
| Accounting | Finance dashboard, budgets, bank reconciliation, expense claims |
| HRM | Employees, payroll, leaves, attendance, appraisals |
| Project | Projects, tasks, milestones, timesheets |
| Milk Collection | MCC operations, payments to farmers and officers |
| Logistics | Riders, trips, deliveries |
| Cooperatives | Cooperative management |
| Center Operations | MCC cost tracking and approvals |
| OSS | Agricultural input sales and distribution |
| Extension | Field agent activity tracking |
| Requisitions | Internal procurement requisitions |
| Reports | Financial and operational reports |

---

## Useful Artisan Commands

```bash
# List all modules and their status
php artisan module:list

# Run only module migrations
php artisan migrate --path=Modules/{ModuleName}/Database/Migrations

# Clear all caches (routes, views, config, modules)
php artisan optimize:clear
```

---

## License

Proprietary — Gondal Group.
