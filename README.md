# Employee Self-Assessment System (MVP)

A lightweight, enterprise-ready Employee Self-Assessment web application built with **Laravel 12** and **PHP 8.4**. Designed for streamlined performance evaluations with direct submission workflows and protected management oversight.

## Key Features

- **Passwordless Employee Form Access:** Direct URL form submission (`/assessment/{slug}`) with auto-locking upon submission.
- **Dynamic Questions CRUD:** Admins can manage custom evaluation questions and guide texts dynamically, per assessment cycle.
- **Dual Language Support:** Seamless toggling between **Myanmar (မြန်မာ)** and **English**.
- **Admin / CEO / GM Dashboard:** Secure Email/Password authentication for executive oversight and response viewing.
- **Multi-format Exports:** Export assessment data to **Excel (.xlsx)**, **CSV**, and printable **PDF**.

## Tech Stack

| Component        | Version / Package                    |
|------------------|--------------------------------------|
| PHP              | 8.4                                  |
| Framework        | Laravel 12.x                         |
| Database         | MySQL 8.0                            |
| Frontend         | Blade + Tailwind CSS 4 (via Vite 7)  |
| Excel / CSV      | `maatwebsite/excel` ^4.0             |
| PDF              | `barryvdh/laravel-dompdf` ^3.1       |

## Requirements

- PHP 8.4 with extensions: `pdo_mysql`, `mbstring`, `xml`, `gd`, `zip`
- Composer 2
- Node.js 20+ and npm
- MySQL 8.0

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
php artisan migrate --seed
npm run build        # or `npm run dev` during development
php artisan serve
```

The app is now available at <http://localhost:8000>.

## Default Seed Data

| Item            | Value                                         |
|-----------------|-----------------------------------------------|
| Admin login     | `admin@example.com` / `password`              |
| Sample cycle    | `2026-q1` with 3 sample questions             |
| Employee form   | <http://localhost:8000/assessment/2026-q1>    |

> **Change the seeded admin password immediately** and never deploy with the default credentials.

## Usage

### Employees
1. Open the assessment link shared by HR, e.g. `/assessment/2026-q1`.
2. Fill in Name, Employee ID, Email, Position and Department, then answer every question.
3. Submit. The form locks and a read-only copy of the answers is shown. Submitting again for the same cycle shows the banner *"You have already submitted this assessment form."*

### Admin / CEO / GM
Log in at `/admin/login` (roles `admin`, `ceo`, `gm` only).

| Page                                   | Purpose                                              |
|----------------------------------------|------------------------------------------------------|
| `/admin/cycles`                        | Create assessment cycles, get employee links         |
| `/admin/cycles/{cycle}/questions`      | Add, edit and delete dynamic questions (EN / MM)     |
| `/admin/assessments`                   | List submissions, filter by cycle / department       |
| `/admin/assessments/{id}`              | Printable detailed view                              |
| `/admin/assessments/{id}/pdf`          | Download PDF                                         |
| `/admin/assessments/export/excel`      | Export list to `.xlsx`                               |
| `/admin/assessments/export/csv`        | Export list to `.csv`                                |

When a single cycle is selected in the filter, exports include one column per question.

### Creating additional admin / CEO / GM users

There is no registration UI. Create users with Tinker:

```bash
php artisan tinker
>>> App\Models\User::create(['name' => 'CEO', 'email' => 'ceo@company.com', 'password' => 'a-strong-password', 'role' => 'ceo']);
```

## Localization

- Language is stored in the session and switched via `GET /lang/{locale}` (`en` or `mm`).
- Static UI text lives in `lang/en.json` and `lang/mm.json`.
- Dynamic questions show `question_mm` / `guide_mm` when the locale is `mm`, otherwise the English fields.

## Project Structure (key files)

```
app/Http/Controllers/AssessmentController.php        Employee form + submission
app/Http/Controllers/Admin/                          Auth, Cycles, Questions, Assessments
app/Http/Middleware/SetLocaleMiddleware.php          Session-based locale
app/Http/Middleware/RoleMiddleware.php               role:ceo,gm,admin guard
app/Exports/AssessmentsExport.php                    Excel / CSV export
database/migrations/                                 users, cycles, questions, assessments, answers
resources/views/                                     Blade templates (Tailwind)
routes/web.php                                       All routes
```

## Security Notes

- Admin routes are protected by `auth` and `role:ceo,gm,admin` middleware; login is rate-limited.
- Employee submissions never match or modify admin/CEO/GM accounts, and a mismatched Employee ID / email pair is rejected.
- A submitted assessment can only be viewed read-only from the browser session that submitted it.
- Excel/CSV exports neutralise spreadsheet formula injection in free-text cells.
- Keep `.env` out of version control (already in `.gitignore`).

## Known Limitations

- **PDF and Myanmar text:** the PDF uses DomPDF's built-in DejaVu Sans font, which has no Myanmar glyphs. Burmese text in PDFs may not render correctly; the on-screen print view (`Print` button) renders it properly.
- The `draft` assessment status exists in the schema but the MVP only creates `submitted` records.

## Versioning

This project follows [Semantic Versioning](https://semver.org/).

| Version | Notes                 |
|---------|-----------------------|
| 0.1.0   | Initial MVP release   |

## License

Released under the [MIT License](https://opensource.org/licenses/MIT).
