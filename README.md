# Employee Self-Assessment System (MVP)

A lightweight, enterprise-ready Employee Self-Assessment web application built with **Laravel 12** and **PHP 8.4**. Designed for streamlined performance evaluations with direct submission workflows and protected management oversight.

## Key Features

- **Passwordless Employee Form Access:** Direct URL form submission (`/assessment/{slug}`) with auto-locking upon submission.
- **Dynamic Questions CRUD:** Admins can manage custom evaluation questions and guide texts dynamically, per assessment period.
- **Managed Dropdowns:** Admins maintain Department and Position lists (English + Myanmar names) that feed the employee form.
- **Dual Language Support:** Seamless toggling between **Myanmar (မြန်မာ)** and **English**.
- **Admin Portal:** Sidebar layout with a live metrics dashboard, breadcrumbs, profile modal, logout confirmation, and a responsive mobile drawer.
- **Role-Based Access Control:** Manage Users, Roles and Permissions in the UI (Spatie `laravel-permission`); every admin route is permission-protected and the sidebar only shows what a user may open.
- **Multi-format Exports:** Export assessment data to **Excel (.xlsx)**, **CSV**, and printable **PDF**.

## Tech Stack

| Component        | Version / Package                    |
|------------------|--------------------------------------|
| PHP              | 8.4                                  |
| Framework        | Laravel 12.x                         |
| Database         | MySQL 8.0 (local) / PostgreSQL via Supabase (production) |
| Frontend         | Blade + Tailwind CSS 4 (Vite 7) + Alpine.js 3 |
| RBAC             | `spatie/laravel-permission` ^8.3     |
| Excel / CSV      | `maatwebsite/excel` ^4.0             |
| PDF              | `barryvdh/laravel-dompdf` ^3.1       |

## Requirements

- PHP 8.4 with extensions: `pdo_mysql`, `mbstring`, `xml`, `gd`, `zip`
- Composer 2
- Node.js 20+ and npm
- MySQL 8.0 (or PostgreSQL with the `pdo_pgsql` extension)

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/YOUR_USERNAME/employee-self-assessment.git
cd employee-self-assessment

# 2. Install dependencies
composer install
npm install

# 3. Environment file and app key
cp .env.example .env
php artisan key:generate
```

### Database setup

Log in to MySQL and create a database and a dedicated user:

```sql
CREATE DATABASE employee_self_assessment CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'esa_user'@'localhost' IDENTIFIED BY 'your-strong-password';
GRANT ALL PRIVILEGES ON employee_self_assessment.* TO 'esa_user'@'localhost';
FLUSH PRIVILEGES;
```

Then set the credentials in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=employee_self_assessment
DB_USERNAME=esa_user
DB_PASSWORD=your-strong-password
```

### Migrate, seed and run

```bash
php artisan migrate --seed   # also seeds sample departments and positions
npm run build        # or `npm run dev` during development
php artisan serve
```

The app is now available at <http://localhost:8000>.

`APP_TIMEZONE` defaults to `Asia/Yangon` (set it in `.env` to change it).

## Default Seed Data

| Item            | Value                                         |
|-----------------|-----------------------------------------------|
| Admin login     | `admin@example.com` / `password`              |
| Roles           | `admin`, `ceo`, `gm` and 10 built-in permissions |
| Sample period   | `2026-q1` with 3 sample questions             |
| Employee form   | <http://localhost:8000/assessment/2026-q1>    |

> **Change the seeded admin password immediately** and never deploy with the default credentials.
>
> Roles and permissions come from the seeder, so run `php artisan migrate --seed` (or `php artisan db:seed --class=RbacSeeder` on an existing database). Without them nobody can sign in to the admin portal.

## Usage

### Employees
1. Open the assessment link shared by HR, e.g. `/assessment/2026-q1`.
2. Fill in Name, Employee ID, Position and Department (Email is optional), then answer every question.
3. Submit. The form locks and a read-only copy of the answers is shown. Submitting again for the same period shows the banner *"You have already submitted this assessment form."*

### Admin portal
Log in at `/admin/login`. Any account holding at least one role can sign in; what it sees depends on that role's permissions.

| Page                                       | Permission            | Purpose                                              |
|--------------------------------------------|-----------------------|------------------------------------------------------|
| `/admin`                                   | `dashboard.view`      | Live counts, recent submissions, active periods      |
| `/admin/assessment-periods`                | `periods.manage`      | Create assessment periods, copy the employee link    |
| `/admin/assessment-periods/{period}/questions` | `questions.manage` | Add, edit, delete dynamic questions (EN / MM)        |
| `/admin/assessments`, `/admin/assessments/{id}` | `submissions.view` | List (filter by period / department) and view        |
| `/admin/assessments/export/excel`, `/csv`, `/{id}/pdf` | `submissions.export` | Excel, CSV and PDF downloads                |
| `/admin/departments`, `/admin/positions`   | `departments.manage`, `positions.manage` | Dropdown options for the employee form |
| `/admin/users`                             | `users.manage`        | Users CRUD (name, email, password, department, position, role) |
| `/admin/roles`                             | `roles.manage`        | Roles CRUD with a permission checkbox grid           |
| `/admin/permissions`                       | `permissions.manage`  | Permissions CRUD (name, guard name)                  |

When a single period is selected in the submissions filter, exports include one column per question.

### Roles and permissions

| Role    | Access                                                                 |
|---------|------------------------------------------------------------------------|
| `admin` | Everything, including permissions created later (via `Gate::before`)   |
| `ceo`, `gm` | Everything except Users, Roles and Permissions                    |
| custom  | Whatever you tick when creating the role                               |

Safeguards: you cannot delete yourself or change your own role; the last admin cannot be demoted or deleted; only an admin can assign the `admin` role or edit admin accounts; the built-in roles and permissions cannot be renamed or deleted (routes depend on their names); a role that still has users cannot be deleted. A role with no permissions cannot sign in.

The `users.role` column mirrors the user's assigned role (or `employee` when none) so the employee form and dashboard keep working; Spatie is the source of truth for access.

## Deployment (Vercel + Supabase)

The app runs on [Vercel](https://vercel.com) (community `vercel-php` runtime) with a [Supabase](https://supabase.com) PostgreSQL database.

### Files that make it work

| File | Purpose |
|------|---------|
| `vercel.json` | PHP runtime, build command (`npm run build`), routing, and production defaults (`APP_ENV`, cookie sessions, `/tmp` storage paths) |
| `api/index.php` | Vercel entry point. Creates Laravel's writable folders in `/tmp` (Vercel's disk is read-only), then loads `public/index.php`. Nothing may be output before `<?php`. |
| `.vercelignore` | Keeps `vendor`, `node_modules` and `.env` out of the upload |
| `AppServiceProvider` | Forces `https` URLs in production (Vercel's proxy makes requests look like `http`, which blocks CSS/JS as mixed content) |

### 1. Create the Supabase database

1. Create a project at supabase.com and set a database password (letters and numbers only is safest).
2. Click **Connect** and choose **Session pooler** (not *Direct connection*; Vercel has no IPv6 and cannot reach the direct host).
3. Note the pooler host (`aws-0-<region>.pooler.supabase.com`), port `5432`, database `postgres`, and user `postgres.<project-ref>`.

### 2. Migrate and seed the database (from your computer)

Install the PostgreSQL PHP driver once (`sudo apt install php8.4-pgsql`; check with `php -m | grep pgsql`). Temporarily point your local `.env` at Supabase:

```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-<region>.pooler.supabase.com
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres.<project-ref>
DB_PASSWORD=<your-supabase-password>
```

```bash
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

Check the tables in Supabase → **Table Editor**. Switch your `.env` back to your local database afterwards.

### 3. Deploy on Vercel

1. Push the repository to GitHub.
2. In Vercel choose **Add New → Project**, install the GitHub app, and import the repository.
3. Set **Application Preset** to **Other** (Vercel guesses *Vite*; that is wrong for Laravel). Leave Build and Output Settings alone; `vercel.json` sets them.
4. Add these **Environment Variables** (Production). Get `APP_KEY` with `php artisan key:generate --show`:

   | Key | Value |
   |-----|-------|
   | `APP_KEY` | `base64:...` from the command above |
   | `APP_URL` | `https://<your-project>.vercel.app` |
   | `DB_HOST` | Supabase pooler host |
   | `DB_PORT` | `5432` |
   | `DB_DATABASE` | `postgres` |
   | `DB_USERNAME` | `postgres.<project-ref>` |
   | `DB_PASSWORD` | your Supabase password |

   Environment variables only apply to **new** deployments, so redeploy after changing any of them (**Deployments → ⋯ → Redeploy**).
5. Click **Deploy**. Every later `git push` to `main` redeploys automatically.

### Troubleshooting

| Symptom | Cause / fix |
|---------|-------------|
| Browser downloads a file instead of showing the page | `public/index.php` served as a static file. Keep the `"/"` and `"/index.php"` routes in `vercel.json` ahead of `"handle": "filesystem"`. |
| `500 SERVER ERROR` | Open Vercel → **Logs**. *No application encryption key* means `APP_KEY` is missing or the project was not redeployed after adding it. |
| Page has no CSS or JS | Assets are linked as `http://` on an `https://` page. Check the https redirect in `AppServiceProvider` and that `APP_URL` starts with `https://`. |
| Stray text at the top of every page | Something is output before `<?php` in `api/index.php`. |
| `could not find driver` | Install `php8.4-pgsql` locally. |
| Cannot connect to the database | Use the **pooler** host and the `postgres.<project-ref>` username, not `postgres`. |

### Limits on Vercel

- The filesystem is read-only except `/tmp`, which is wiped between requests. Do not store uploads on local disk; use object storage (for example Supabase Storage) if you add uploads.
- Sessions use cookies, the cache uses `array`, and queues run `sync`. Queue workers do not run on Vercel.
- Never commit `.env`, and never paste database passwords or `APP_KEY` into chats or issues. If one leaks, rotate it (Supabase → **Reset database password**) and update Vercel.

## Localization

- Language is stored in the session and switched via `GET /lang/{locale}` (`en` or `mm`).
- Static UI text lives in `lang/en.json` and `lang/mm.json`; validation, pagination and auth messages live in `lang/mm/*.php`.
- Adding UI text: wrap it in `__('...')` and add the key to **both** JSON files. `LocalizationTest` fails if a key is missing or blank.
- Dynamic questions show `question_mm` / `guide_mm` when the locale is `mm`, otherwise the English fields.

## Project Structure (key files)

```
app/Http/Controllers/AssessmentController.php        Employee form + submission
app/Http/Controllers/Admin/                          Auth, Dashboard, Periods, Questions, Assessments,
                                                     Lookups (departments/positions), Users, Roles, Permissions
app/Http/Middleware/SetLocaleMiddleware.php          Session-based locale
app/Http/Middleware/EnsureStaff.php                  Only accounts with a role may use /admin
app/Support/Rbac.php                                 Built-in roles and permissions catalogue
database/seeders/RbacSeeder.php                      Idempotent roles/permissions seeder
app/Exports/AssessmentsExport.php                    Excel / CSV export
database/migrations/                                 users, periods, questions, assessments, answers
resources/views/layouts/admin.blade.php              Sidebar layout (Alpine.js modals)
resources/views/components/breadcrumbs.blade.php     <x-breadcrumbs :items="$breadcrumbs" />
resources/views/                                     Blade templates (Tailwind)
routes/web.php                                       All routes
```

## Security Notes

- Admin routes are protected by `auth`, `staff` and a per-route `permission:` middleware; login is rate-limited.
- Employee submissions never match or modify staff accounts, and a mismatched Employee ID / email pair is rejected. Because email is optional, seeing an existing submission also requires the typed name to match.
- A submitted assessment can only be viewed read-only from the browser session that submitted it.
- Excel/CSV exports neutralise spreadsheet formula injection in free-text cells.
- Keep `.env` out of version control (already in `.gitignore`).

## Testing

```bash
DB_CONNECTION=mysql DB_DATABASE=employee_self_assessment php artisan test
```

The suites (assessment flow, RBAC, localization) run inside a rolled-back transaction, so they are safe against your dev database. The override is needed because `phpunit.xml` defaults to an in-memory SQLite database.

## Known Limitations

- **PDF and Myanmar text:** the PDF uses DomPDF's built-in DejaVu Sans font, which has no Myanmar glyphs. Burmese text in PDFs may not render correctly; the on-screen print view (`Print` button) renders it properly.
- The `draft` assessment status exists in the schema but the MVP only creates `submitted` records.

## Versioning

This project follows [Semantic Versioning](https://semver.org/).

| Version | Notes                 |
|---------|-----------------------|
| 0.1.0   | Initial MVP release   |
| 0.2.0   | Admin sidebar portal, Assessment Periods rename, RBAC (users / roles / permissions), full MM/EN localization, dashboard metrics |

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).
