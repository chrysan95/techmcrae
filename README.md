<p align="center"><img src="logo.svg" width="350" alt="Tech Mcrae Logo"></a></p>
<p align="center">Employee Information System (Sistem Informasi Kepegawaian) that took care of employees' attendance and leave requests</p>

## Overview

**Tech McRae** is a Laravel-based HR / employee information system with two workspaces:

| Workspace | Who | Entry route |
|---|---|---|
| **Admin Workspace** | Users with `role = admin` | `/dashboard` |
| **Employee Portal** | Any authenticated employee | `/portal` |

Unauthenticated users land on `/` (login overlay). After login, users are redirected by role: admins to `/dashboard`, employees to `/portal`.

---

## Features

### Authentication
- Login with **email** or **Employee ID** (`TMCXXXX`), auto-detected via `FILTER_VALIDATE_EMAIL`.
- Session-based auth (`Auth::login`) with "remember me" support.
- Login form rendered as a **CSS overlay** above the dashboard; it fades out after a successful login (`login_success` flash + `@session` check).
- Role-based access via custom `RoleMiddleware` registered as the `role` alias in `bootstrap/app.php`.

### Admin Workspace
| Module | Route | Controller | View |
|---|---|---|---|
| Dashboard | `GET /dashboard` | `AdminDashboardController@index` | `admin/dashboard.blade.php` |
| Leave Status | `GET /leave/status` | `LeaveStatusController@index` | `admin/leavestatus.blade.php` |
| Leave Requests | `GET /leave/requests` | `LeaveRequestController@index` | `admin/leavereq.blade.php` |
| Approve one | `POST /leave/requests/{id}/approve` | `LeaveRequestController@approve` | — |
| Reject one | `POST /leave/requests/{id}/reject` | `LeaveRequestController@reject` | — |
| Approve all pending | `POST /leave/requests/approve-all` | `LeaveRequestController@approveAllPending` | — |
| Employees | `GET /employees` | `EmployeeController@index` | `admin/employee.blade.php` |
| Attendance Records | `GET /attendance/records` | `AttendanceController@index` | `admin/attendance.blade.php` |

- **Dashboard**: stat cards (Employees, WFO, WFH, Absent, On Leave), yearly attendance line chart (this year vs last year), attendance donut chart, period toggle (Today / Week / Month), notifications.
- **Leave Requests**: three-layer modal system (detail → confirm → result), AJAX approve/reject, leave consumption summary, six color-coded leave types (annual, sick, emergency, maternity, paternity, unpaid). Request IDs use `LR-YYYY-NNNN`.
- **Leave Status**: 28-day Gantt chart, team on-leave rate bars, striped on-leave table, "returning soon" section.

### Employee Portal
| Action | Route | Controller |
|---|---|---|
| Portal page | `GET /portal` | `EmpPortalController@index` |
| Clock in | `POST /portal/clock-in` | `EmpPortalController@clockIn` |
| Clock out | `POST /portal/clock-out` | `EmpPortalController@clockOut` |
| Request leave | `POST /portal/leave-request` | `EmpPortalController@requestLeave` |
| Withdraw leave | `POST /portal/leave/{id}/withdraw` | `EmpPortalController@withdrawLeave` |

- Clock-in/out state machine: `ready_to_clock_in` → `clocked_in` → `day_complete` (or `leave_active`).
- Work location selection (Office / Home).
- Live clock, live worked-hours timer, expected clock-out indicator.
- Leave request card selector, leave balance per type (`employee_leave_balances`), request history with withdraw.
- Standalone layout (does not extend `layouts.app`).

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.2+, Laravel 11/12 (uses `bootstrap/app.php` `Application::configure()` style) |
| Database | MySQL 8 (Eloquent ORM + Query Builder) |
| Templating | Blade |
| Styling | Bootstrap 5.3.8 (local, `public/bootstrap/`), custom CSS with CSS custom properties |
| Fonts / Icons | Google Fonts — **Alfa Slab One** (headings), **Poppins** (body); **Material Symbols Rounded** |
| Charts | **Chart.js** (via jsDelivr CDN) |
| Client JS | Vanilla JavaScript (`fetch()`, `Intl.DateTimeFormat`, `setInterval`) |
| Timezone | `Asia/Jakarta` (WIB) |

---

## Data Visualization

All charts on the admin dashboard use **Chart.js**, loaded in `resources/views/layouts/app.blade.php`:

```html
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
```

### 1. Attendance Line Chart (`#attendanceLineChart`)
- Type: `line`, two datasets — **this year** vs **last year** monthly attendance percentage.
- Data built in `AdminDashboardController@index` as `$chartData` and passed to Blade.
- Injected into JavaScript with `@json($chartData['this_year'])` / `@json($chartData['last_year'])`.
- Summary stats under the chart: **Avg This Year**, **Peak Month**, **YoY Change**.

### 2. Attendance Donut Chart (`#attendancePieChart`)
- Type: `doughnut`, segments for WFO / WFH / Absent / On Leave.
- Center label shows the total and current period (`TOTAL · TODAY`, etc.).
- Legend rendered from server HTML (`pieData.html_legend`).

### 3. Leave Status Visuals (HTML/CSS, no chart library)
- **28-day Gantt chart**: leave ranges drawn as colored bars per employee, color mapped per leave type.
- **Team on-leave rate bars** — percentage bars per department.

### Layout note
Charts sit in CSS grid panels using `grid-template-columns: minmax(0, 1fr)` and fixed-height `.chart-wrapper` containers with `maintainAspectRatio: false`, preventing canvas overflow on resize.

---

## Real-Time Behavior

The app does not use WebSockets; "real-time" is achieved client-side with timers and AJAX.

### 1. Live Clock (Employee Portal) — `public/portal.js`
- Formats current time in `Asia/Jakarta` using `Intl.DateTimeFormat` (`formatToParts`), independent of the user's device timezone.
- Updates the big clock, header date, and clock-in modal every second:

```js
setInterval(() => { tickClock(); tickWorked(); }, 1000);
```

### 2. Live Worked-Hours Timer
- Server passes `check_in_epoch_ms` and `expected_epoch_ms` through `window.PORTAL`:

```blade
<script>window.PORTAL = @json($portal);</script>
```

- While `state === 'clocked_in'`, `tickWorked()` renders `Date.now() - checkInEpochMs` as `HH:MM:SS` and flips the expected-out icon (`close` → `check`) once the expected time is reached.
- A separate `setInterval(tickClockOutModal, 1000)` keeps the clock-out modal time current.

### 3. Dashboard Period Toggle (AJAX hot-swap)
- Clicking **Today / Week / Month** calls:

```js
fetch(`${window.location.pathname}?period=${period}`, {
  headers: { 'X-Requested-With': 'XMLHttpRequest' }
});
```

- `AdminDashboardController` detects `$request->ajax()` and returns JSON (`stats`, `pieData`).
- Stat cards update in place, and the donut chart is updated without re-rendering:

```js
const chart = Chart.getChart('attendancePieChart');
chart.data.datasets[0].data = data.pieData.values;
chart.update();
```

### 4. Leave Approve / Reject (AJAX)
- Confirm modal submits via `fetch()` with an `X-CSRF-TOKEN` header; the result modal is shown without a full page reload.

---

## Project Structure

```
tech-mcrae/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php
│   │   │   ├── AuthController.php
│   │   │   ├── AdminDashboardController.php
│   │   │   ├── AttendanceController.php
│   │   │   ├── EmployeeController.php
│   │   │   ├── EmpPortalController.php
│   │   │   ├── LeaveRequestController.php
│   │   │   └── LeaveStatusController.php
│   │   └── Middleware/
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── Employee.php
│       ├── Attendance.php
│       ├── LeaveRequest.php
│       └── LeaveType.php
├── bootstrap/
│   └── app.php                  # middleware alias
├── public/
│   ├── bootstrap/bootstrap-5.3.8-dist/
│   ├── css/
│   │   ├── style.css
│   │   ├── style2.css
│   │   ├── style3.css
│   │   └── portal.css
│   ├── logo.svg
│   ├── script.js                # sidebar, dropdowns (admin layout)
│   └── portal.js                # live clock, timers, portal modals
├── resources/views/
│   ├── layouts/app.blade.php
│   ├── auth/guest_entry.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── leavereq.blade.php
│   │   ├── leavestatus.blade.php
│   │   ├── employee.blade.php
│   │   └── attendance.blade.php
│   └── portal.blade.php
└── routes/web.php
```

---

## Database

Main tables used by the controllers:

| Table | Purpose | Key columns |
|---|---|---|
| `employees` | Users (Authenticatable) | `employee_id` (`TMCXXXX`), `name`, `email`, `password`, `role` (`admin` / `employee`), department |
| `attendances` | Daily attendance | `employee_id`, `attendance_date`, `check_in`, `check_out`, `location` (`office` / `home`), `status` |
| `leave_types` | Leave type master | `id`, `name` (annual, sick, emergency, maternity, paternity, unpaid) |
| `leave_requests` | Leave submissions | `id` (`LR-YYYY-NNNN`), `employee_id`, `leave_type_id`, `leave_from`, `leave_to`, `reason`, `status` (`pending` / `approved` / `rejected`), `requested_at` |
| `employee_leave_balances` | Remaining quota per type | `employee_id`, `leave_type_id`, `remaining`, `used` |

`config/auth.php` must point the `users` provider to the `Employee` model:

```php
'providers' => [
    'users' => [
        'driver' => 'eloquent',
        'model'  => App\Models\Employee::class,
    ],
],
```

---

## Requirements

- PHP **8.2+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`
- Composer 2.x
- MySQL 8.x (or MariaDB 10.6+)
- Git
- Optional: XAMPP / Laragon (Windows) — bundles PHP + MySQL

---

## Installation

### 1. Clone the repository
```bash
gh repo clone chrysan95/techmcrae
cd tech-mcrae
```

### 2. Install PHP dependencies
```bash
composer install
```

### 3. Create the environment file
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure `.env`
```env
APP_NAME="Tech McRae"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=techmcrae
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
```

> If your Laravel version does not read `APP_TIMEZONE`, set `'timezone' => 'Asia/Jakarta'` in `config/app.php`.

### 5. Create the database
```sql
CREATE DATABASE techmcrae;
```

### 6. Run migrations and seeders
```bash
php artisan migrate
php artisan db:seed
```
Or import an existing SQL dump:
```bash
mysql -u root -p tech_mcrae < database/tech_mcrae.sql
```

### 7. Place front-end assets
Ensure these exist under `public/`:
```
public/bootstrap/bootstrap-5.3.8-dist/css/bootstrap.min.css
public/css/style.css
public/css/style2.css
public/css/style3.css
public/css/portal.css
public/script.js
public/portal.js
public/logo.svg
```

### 8. Clear caches
```bash
php artisan optimize:clear
```

### 9. Run the development server
```bash
php artisan serve
```
Open **http://localhost:8000**

---

## Default Login

Log in with either the **email** or **Employee ID** of a seeded record:

| Role | Login | Password | Redirects to |
|---|---|---|---|
| Admin | `TMC0001` / admin email | as seeded | `/dashboard` |
| Employee | `TMC0002` / employee email | as seeded | `/portal` |

---

## Useful Commands

```bash
php artisan route:list          # list all routes
php artisan migrate:fresh --seed # reset database
php artisan optimize:clear      # clear config/route/view cache
php artisan tinker              # interactive shell
```

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Charts not rendering | Browser needs internet access for the Chart.js CDN; check console for `Chart is not defined`. |
| Wrong clock / hours | Confirm `Asia/Jakarta` in `config/app.php`; portal JS formats in WIB regardless of device time. |
| CSS not loading | Confirm files are in `public/css/`; run `php artisan optimize:clear`. |
| `SQLSTATE[HY000] [2002]` | MySQL not running or wrong `DB_HOST` / `DB_PORT`. |
| 403 on admin pages | Logged-in user's `role` is not `admin`. |

---

## Known Limitations

- **Plaintext password check**: `AuthController@login` compares `password` directly in the query. Replace with `Hash::check()` and store hashed passwords before production.
- **No server-side pagination**: tables use `->get()` and filter client-side; large datasets will slow down.
- **Chart.js loaded unpinned from CDN**: pin a version (e.g. `chart.js@4.4.1`) for reproducible builds; requires internet access in the browser.
- **No WebSockets**: real-time updates are client-side timers/AJAX, not pushed from the server.

---

## License

For educational purposes. 

---

## Use of AI

AI was used to help with the writing of this README.
