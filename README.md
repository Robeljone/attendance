# Attendance HR

Internal HR and attendance platform for small-to-medium teams. Employees clock in/out (including QR station scanning), request leave, and view payslips. Admins and HR manage people, schedules, payroll periods, reports, and company settings.

Built with **Laravel 13**, **PHP 8.3**, **Blade**, **Alpine.js**, **Tailwind CSS**, and **Vite**. The UI is mobile-friendly (collapsible sidebar, responsive layouts).

---

## Features

| Area | What you get |
| --- | --- |
| **Employee portal** | Clock in/out, QR scan attendance, leave requests (with attachments), payslips, personal profile |
| **Admin / HR** | Employees, departments, work schedules, attendance review, leave approvals, advanced payroll (components, tax/pension, proration, approval→paid, bank CSV), reports, company settings |
| **QR station** | Rotating QR tokens for desk/kiosk check-in |
| **Network gate** | Optional IP/CIDR allowlist so clock actions only work on the company network |
| **Employee records** | Profile fields, education history, document uploads |
| **Branding** | Super Admin can customize company branding |
| **Auth** | Separate employee (`/login`) and staff (`/admin/login`) portals, email verification, forced password change for new accounts |
| **PWA** | Installable progressive web app (manifest + service worker + offline fallback) |

---

## Roles

| Role | Access |
| --- | --- |
| `superadmin` | Full admin + branding |
| `admin` / `hr` | HR admin area (employees, leaves, payroll, reports, settings, QR station) |
| `manager` | Staff portal (and manager-oriented flows as configured) |
| `employee` | Employee portal only |

---

## Requirements

- PHP **8.3+** with common Laravel extensions (mbstring, openssl, pdo, tokenizer, xml, ctype, json, fileinfo)
- Composer 2
- Node.js **18+** and npm
- SQLite (default) or MySQL/PostgreSQL

---

## Quick start

```bash
# 1. Install PHP & JS dependencies, create .env, generate key, migrate, build assets
composer setup

# 2. Seed demo data (departments, schedules, leave types, sample users)
php artisan db:seed

# 3. Run the app (HTTP + Vite + queue/logs as configured)
composer run dev
```

Then open the app URL from your `.env` (`APP_URL`, default `http://localhost:8000`).

### Login URLs

| Portal | URL | Who |
| --- | --- | --- |
| Chooser | `/` | Pick employee or staff |
| Employee | `/login` | `employee` |
| Staff | `/admin/login` | `superadmin`, `admin`, `hr`, `manager` |

On a phone (HTTPS in production), use the browser’s **Add to Home Screen / Install** to install the PWA.

### Manual setup (equivalent)

```bash
composer install
cp .env.example .env   # Windows: copy .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan db:seed
php artisan serve      # or: composer run dev
```

---

## Demo accounts

After `php artisan db:seed`, all of these use password **`password`**:

| Email | Role |
| --- | --- |
| `superadmin@company.test` | Super Admin |
| `admin@company.test` | Admin |
| `manager@company.test` | Manager |
| `employee@company.test` | Employee |

New employees created in admin get the default password from `AUTH_DEFAULT_EMPLOYEE_PASSWORD` (see `.env.example`) and may be required to change it on first login.

---

## Attendance & network settings

Configured via `.env` (and mirrored in company settings where applicable):

| Variable | Purpose |
| --- | --- |
| `ATTENDANCE_ENFORCE_NETWORK` | When `true`, clock-in/out and QR scan require an allowed client IP |
| `ATTENDANCE_ALLOWED_IP_CIDRS` | Comma-separated CIDRs (e.g. `127.0.0.1/32,192.168.0.0/16,10.0.0.0/8`) |
| `ATTENDANCE_QR_TTL` | QR token lifetime in seconds (default `60`) |

See `config/attendance.php` for defaults.

**QR flow (typical):** open **Admin → QR station** on a kiosk display; employees scan from **Portal → Attendance** on their phone while on the company network.

---

## Useful commands

```bash
composer run dev          # local development
composer test             # clear config + run PHPUnit
php artisan test --compact
npm run build             # production assets
npm run dev               # Vite only
vendor/bin/pint --dirty   # format changed PHP files
```

---

## Project layout (high level)

```
app/
  Enums/                 # Roles, leave status, employment status, etc.
  Http/Controllers/
    Admin/               # HR back office
    Portal/              # Employee self-service
  Models/                # Employee, AttendanceRecord, LeaveRequest, Payslip, …
  Http/Middleware/       # Role + company network checks
config/attendance.php    # Network allowlist + QR TTL
database/migrations/
database/seeders/        # HrDemoSeeder and related seeders
resources/views/
  admin/                 # Admin Blade screens
  portal/                # Employee portal
routes/web.php           # App routes
```

---

## Testing

```bash
php artisan test --compact
```

Prefer factories and feature tests under `tests/`. When adding models, keep factories/seeders in sync with existing patterns.

---

## License

Application code follows the project’s license terms. Laravel framework components remain under the [MIT license](https://opensource.org/licenses/MIT).
