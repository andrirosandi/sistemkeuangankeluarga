# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About This Project

**Sistem Keuangan Keluarga** — A family finance management system built with Laravel 12. It manages household income, expense requests, approvals, realizations, reporting, and notifications with role-based access control (Admin/User roles).

## Development Commands

### Installation & Setup
```bash
composer install
php artisan key:generate
php artisan migrate --force
npm install
```

### Local Development
```bash
composer run dev   # Runs: php artisan serve, queue listener, pail logs, npm run dev concurrently
```

Or individually:
```bash
php artisan serve           # Start dev server on port 8000
php artisan queue:listen    # Process queued jobs
npm run dev                 # Start Vite dev server
```

### Database
```bash
php artisan migrate          # Run migrations
php artisan db:seed          # Run seeders (creates roles, permissions, categories)
```

The app includes a **Setup Wizard** at `/setup` for creating the first admin user. It is protected by `RedirectIfSetupComplete` / `RedirectToSetupIfNeeded` middleware. When no users exist, all traffic redirects to `/setup`. When users exist, the setup route is disabled.

### Frontend
```bash
npm run dev     # Vite dev server (HMR)
npm run build   # Production build
```

Vite has 4 entry points: `resources/css/app.css`, `resources/js/app.js`, `resources/css/admin.css`, `resources/js/admin.js`.

### Testing
```bash
vendor/bin/phpunit                  # Run all tests
vendor/bin/phpunit --filter name    # Run specific test
php artisan test                    # Via artisan
```

Test coverage is currently minimal (only Breeze scaffolding tests exist). New features should include tests.

### Docker (Production)
```bash
docker compose up -d     # Start app + MySQL
docker compose down      # Stop
```

### Deployment
CI/CD via GitHub Actions (`.github/workflows/deploy.yml`): SSH deploy → git pull → docker compose rebuild → artisan migrate.

## Deployment Workflow Rules

**IMPORTANT:** Follow this sequence when deploying changes:

1. **Work on `dev` branch** — All development happens here
2. **Commit changes** — Create descriptive commit messages
3. **Push to `dev`** — Push changes to dev branch
4. **Switch to `main`** — `git checkout main`
5. **Pull latest main** — `git pull origin main`
6. **Merge dev to main** — `git merge dev` (or use merge commit)
7. **Push to main** — `git push` (triggers CI/CD deployment)
8. **Switch back to dev** — `git checkout dev`
9. **Check for missed changes** — Verify all changes are merged correctly

### Checklist Before Push

- [ ] Run `git status` — ensure no unwanted files are staged
- [ ] Check `.claude/settings.local.json` — should NOT be committed
- [ ] Review commit message — ensure it's descriptive and follows convention
- [ ] Verify all changes are in correct branch
- [ ] Run tests (if applicable) — `php artisan test`

### After Deployment

- [ ] Verify website is online — check at production URL
- [ ] Test critical paths — login, dashboard, request, transaction
- [ ] Check GitHub Actions — ensure deployment completed successfully
- [ ] Verify database migrations ran without errors

## Architecture

### Tech Stack
- **Backend**: Laravel 12 (PHP 8.2+), Laravel Breeze
- **Frontend**: Tabler (Bootstrap 5 admin template), Alpine.js, HTMX, Tailwind (Breeze pages only), Blade templates
- **Database**: MySQL 8.0 (currently), with 21 migrations and 3 seeders
- **Key Packages**: Spatie Permission (RBAC), Spatie MediaLibrary, Maatwebsite Excel, DOMPDF

### Pattern: MVC + Service Layer

The application follows MVC with a dedicated **Service Layer** for business logic:

```
Controllers → Services → Models → Database
```

**Service Classes** (`app/Services/`):
- `FinanceRequestService` — request lifecycle (create, update, approve, reject, submit)
- `TransactionService` — realization lifecycle (create, update, complete, cancel, delete)
- `BalanceService` — monthly balance calculation with pessimistic locking
- `NotificationService` — notification creation helpers
- `ScopeService` — data visibility scoping (self/group/all)

Controllers delegate business logic to these services. Controllers handle HTTP concerns only (validation, response formatting).

**Traits** (`app/Models/Traits/`, `app/Http/Controllers/Concerns/`):
- `HasHeaderRelations` — shared relationship definitions on header models
- `TransactionType` — shared `getTransCode()` and `getTypeLabel()` for dual-mode (kas-masuk/kas-keluar) controllers
- `BulkDeletable` — configurable mass-delete operations

### Role-Based Access Control (Spatie Permission)

33 granular dot-notation permissions across 2 roles. Middleware aliases: `role`, `permission`, `role_or_permission`.

**Data Visibility** — The `RoleVisibility` model implements a "watcher/watched" system. Admin sees all data; non-admin users see only their own data plus data from roles configured as "watched".

### Balance Engine

Monthly balance stored in a dedicated `balance` table (NOT computed via SUM at runtime). Key rules:
- `BalanceService::recalculateFromMonth(string $month)` iterates months sequentially: `ending = begin + total_in - total_out`
- All balance mutations are wrapped in `DB::transaction()` with `lockForUpdate()` for consistency
- Only `completed` status transactions affect balance; `draft` and `canceled` do not

### File Upload Flow
1. Frontend compresses images via JS before upload
2. AJAX uploads to `/api/upload-media` → returns `media_id` from Spatie MediaLibrary
3. Main form submits with hidden `media_ids[]` array
4. Backend attaches temporary media to the official transaction
5. Submit button is disabled during upload/compression

### Routing Structure

Routes in `routes/web.php` follow a dual-type pattern for `Kas Masuk` (in) and `Kas Keluar` (out):
- `/kas-masuk/pengajuan/*` → request CRUD (type: in)
- `/kas-masuk/realisasi/*` → transaction CRUD (type: in)
- `/kas-keluar/pengajuan/*` → request CRUD (type: out)
- `/kas-keluar/realisasi/*` → transaction CRUD (type: out)

Other key routes: `/laporan/*` (reports), `/master/*` (categories/users/roles/templates), `/notifikasi/*`, `/api/dashboard/*` (widget endpoints)

### Naming Conventions
- Database: `snake_case` (tables, columns)
- PHP Classes/Models: `PascalCase`
- Methods/Variables: `camelCase`
- Currency: `DECIMAL(15,4)` — display with `@uang` Blade directive or `number_format()`

## Important Files & Directories

| Path | Description |
|---|---|
| `bootstrap/app.php` | Laravel 12 bootstrap with middleware registration |
| `routes/web.php` | All route definitions, permission-protected |
| `config/menu.php` | Sidebar menu definition with permissions |
| `app/Services/` | Business logic layer |
| `app/Http/Requests/` | Form Request validation classes |
| `resources/views/` | Blade templates |
| `resources/js/admin.js` | Admin JS (Alpine.js, HTMX, Tabler) |
| `database/seeders/` | Seeders for roles, permissions, categories |
| `docs/` | Project documentation (specs, reviews, module docs) |

## Project Documentation

See `docs/` for detailed specifications:
- `global_rules.md` — Development standards (naming, upload flow, security, balance engine)
- `techstack.md` — Technology stack decisions and rationale
- `tugas.md` — Original project brief
- `sidebar_menu.yml` — Sidebar menu structure
- `modules/` — Module-specific design docs (auth, template, settings, notification, request, transaction, dashboard, reporting, etc.)
- `final_review.md` — Requirements audit mapping features to specifications

## Guidelines for This Codebase

- **Always use DB transaction + pessimistic locking** for balance-affecting operations (`request_header`, `request_detail`, `transaction_header`, `transaction_detail`, `balance`)
- **Always validate input** via Form Request classes — never save unvalidated data
- **Use Spatie MediaLibrary** for file uploads — do not manually add `foto`/`image` columns to tables
- **Respect the Service Layer** — do not put business logic in controllers for operations that belong in services
- **Use HTMX or Alpine.js** for dynamic frontend interactions (not Vue/React)
- **Use Tabler Icons webfont** for icons: `<i class="ti ti-iconname"></i>` — do not use inline SVG or other icon libraries
- **Use Form Request classes** in `app/Http/Requests/` for validation — do not use inline `$request->validate()` in controllers
- **Ownership check** — users can only access their own requests (`created_by === auth()->id()`). Admin bypasses this.
