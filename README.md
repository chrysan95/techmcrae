<p align="center"><img src="logo.svg" width="400" alt="Tech Mcrae Logo"></a></p>
<p align="center">Employee Information System built with Laravel. This guide covers **local setup without Docker**.</p>

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
- Log in with **email** or **Employee ID** (`TMCXXXX`); the type is auto-detected with `FILTER_VALIDATE_EMAIL`.
- Session-based auth (`Auth::login`) with "remember me" support.
- The login form is a **CSS overlay** above the dashboard and fades out after a successful login (`login_success` flash + `@session` check).
- Role-based access through a custom `RoleMiddleware`, registered as the `role` alias in `bootstrap/app.php`.
- **CSRF protection on every POST route, with no exclusions.** Expired or stale tokens (HTTP 419) are handled gracefully in `bootstrap/app.php`:
  - AJAX requests get a JSON 419 response and the page reloads with a fresh token.
  - A stale `/logout` still logs the user out.
  - Any other form redirects back with a "session expired" message.

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

- **Dashboard**: a personalised welcome using the logged-in admin's name, plus:
  - stat cards for Employees, WFO, WFH, Absent and On Leave;
  - a yearly attendance line chart (this year vs last year) and an attendance donut chart;
  - a Today / Week / Month period toggle;
  - notifications.
- **Leave Requests**:
  - a three-layer modal system (detail → confirm → result) with AJAX approve/reject;
  - a leave consumption summary;
  - six colour-coded leave types: annual, sick, emergency, maternity, paternity and unpaid.

  Request IDs use the format `LR-YYYY-NNNN`. The approving admin's `employee_id` is stored as `admin_id`.
- **Leave Status**: a 28-day Gantt chart, team on-leave rate bars, a striped on-leave table and a "returning soon" section.

### Employee Portal
| Action | Route | Controller |
|---|---|---|
| Portal page | `GET /portal` | `EmpPortalController@index` |
| Clock in | `POST /portal/clock-in` | `EmpPortalController@clockIn` |
| Clock out | `POST /portal/clock-out` | `EmpPortalController@clockOut` |
| Request leave | `POST /portal/leave-request` | `EmpPortalController@requestLeave` |
| Withdraw leave | `POST /portal/leave/{id}/withdraw` | `EmpPortalController@withdrawLeave` |

- **Clock-in/out state machine:** `ready_to_clock_in` → `clocked_in` → `day_complete` (or `leave_active`).
- **Work location** selection (Office / Home).
- **Live indicators:** a live clock and a live worked-hours timer. The expected clock-out indicator shows ✕ until the expected time is reached, and stays ✕ if the employee clocked out early.
- **Leave request rules,** enforced on the server and mirrored in the browser:
  - **Quota:** requested days can't exceed the remaining quota for that leave type. The quota is calculated as `allocation_limit − (Approved + Pending days)` within the type's reset period (`Monthly` or `Yearly`). Pending requests reserve days, so requests can't be stacked past the limit.
  - **No overlap:** a new request can't overlap an existing **Pending** or **Approved** request.
  - **Eligibility:** Maternity is only offered to `F` employees and Paternity only to `M` employees.
  - **No double submits:** the checks run inside a DB transaction with a row lock, so two quick submissions can't both pass.
  - **Error feedback:** a violation keeps the form input and shows an inline message plus an error pop-up.
- **Request history** with withdraw for pending requests.
- **Standalone layout** (does not extend `layouts.app`).

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.3 (8.2+ supported), Laravel 12 (`bootstrap/app.php` `Application::configure()` style) |
| Database | MariaDB 10.11 in Docker (MySQL 8 / MariaDB 10.6+ for non-Docker) |
| Web server | Apache 2.4 + `mod_php` (Docker image `php:8.3-apache`) |
| Containers | Docker, Docker Compose v2 |
| Templating | Blade |
| Styling | Custom CSS with CSS custom properties (no CSS framework) |
| Fonts / Icons | Google Fonts: **Alfa Slab One** (headings), **Poppins** (body); **Material Symbols Rounded** (icons) |
| Charts | **Chart.js** (jsDelivr CDN) |
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
- **28-day Gantt chart** — leave ranges drawn as colored bars per employee, color mapped per leave type.
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
│       ├── LeaveType.php
│       └── LeaveBalance.php
├── bootstrap/
│   └── app.php                  # middleware alias + 419 (CSRF) handler
├── docker/
│   ├── entrypoint.sh
│   ├── apache/000-default.conf
│   ├── php/php.ini
│   └── mysql/init/              # SQL dump (git-ignored)
├── public/
│   ├── css/
│   │   ├── style.css
│   │   ├── style2.css
│   │   ├── style3.css
│   │   └── portal.css
│   ├── logo.svg
│   ├── script.js                # sidebar, dropdowns (admin layout)
│   └── portal.js                # live clock, timers, portal modals, leave validation
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
├── routes/web.php
├── Dockerfile
├── docker-compose.yml
├── .dockerignore
├── .env.docker.example
└── .env.db.example
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

---

## Running with Docker

### What runs

| Service | Image | URL / Port | Purpose |
|---|---|---|---|
| `app` | built from `Dockerfile` | http://localhost:8080 | Laravel on Apache + PHP 8.3 |
| `db` | `mariadb:10.11` | `localhost:3307` | Database |
| `phpmyadmin` | `phpmyadmin:5` | http://localhost:8081 | Database GUI |

### Docker files

```
tech-mcrae/
├── Dockerfile                    # Multi-stage build: Composer stage → php:8.3-apache runtime
├── docker-compose.yml            # app + db + phpmyadmin, volumes, healthcheck
├── .dockerignore                 # Keeps vendor/, .env, caches, logs out of the image
├── .env.docker.example           # Laravel config template for the container
├── .env.db.example               # MariaDB credentials template
└── docker/
    ├── entrypoint.sh             # Runs on every container start (see below)
    ├── apache/000-default.conf   # Apache vhost: DocumentRoot = public/, AllowOverride All
    ├── php/php.ini               # PHP overrides (copied to conf.d/zz-app.ini)
    └── mysql/init/               # Put your .sql dump here; auto-imported on first start
```

### Steps

**1. Install Docker Desktop** (Windows: enable the WSL 2 backend).

```bash
docker --version
docker compose version
```

**2. Clone the repository and create the env files**

```bash
gh repo clone chrysan95/techmcrae
cd techmcrae
cp .env.docker.example .env.docker
cp .env.db.example .env.db
```

**3. Add the database dump**

Export your database to `docker/mysql/init/01-techmcrae.sql`, for example from phpMyAdmin with **Export → Quick → SQL**. Remove any `CREATE DATABASE` or `USE` lines; the container creates and selects `techmcrae` itself.

**4. Build and start**

```bash
docker compose up -d --build
docker compose logs -f app      # wait for "Database is up."
```

**5. Open the app**

| What | Where |
|---|---|
| App | http://localhost:8080 |
| phpMyAdmin | http://localhost:8081 (user/password from `.env.db`) |
| HeidiSQL / DBeaver | host `localhost`, port `3307` |

Always use the same host (`localhost:8080`, not `127.0.0.1:8080`). Cookies are per host, and switching hosts causes CSRF mismatches.

---

## Default Login

Log in with either the **email** or **Employee ID** of a seeded record:

| Role | Login | Password | Redirects to |
|---|---|---|---|
| Admin | `TMC0001` / admin email | as seeded | `/dashboard` |
| Employee | `TMC0002` / employee email | as seeded | `/portal` |

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
- **No server-side pagination**: tables use `->get()` and filter client-side; large datasets will slow down. This was done only for testing only because we only had a small dummy dataset.
- **Chart.js loaded unpinned from CDN**: pin a version (e.g. `chart.js@4.4.1`) for reproducible builds; requires internet access in the browser.
- **No WebSockets**: real-time updates are client-side timers/AJAX, not pushed from the server.

---

## Stylesheets

| File | Loaded by | Covers |
|---|---|---|
| `bootstrap.min.css` | `layouts/app.blade.php` (line 9) | Bootstrap 5.3.8 base utilities and pagination styling |
| `style2.css` | `layouts/app.blade.php` (line 10) | Main content layout, admin dashboard, stat cards, dashboard grid, Chart.js overflow fixes, notifications panel, responsive breakpoints |
| `style.css` | `layouts/app.blade.php` (line 11) | Global reset, Poppins font, sidebar, sidebar nav and dropdown menus |
| `style3.css` | `layouts/app.blade.php` (line 12) | Login overlay, generic records pages (Employee, Attendance, Leave Requests), data tables, pills/badges, empty states, leave summary panel, leave request modals (detail → confirm → result), Leave Status page (Gantt, on-leave bars, returning soon) |
| `portal.css` | `portal.blade.php` (line 9) | Employee portal only: standalone, does not use the shared layout or any of the files above |

**Load order (admin pages):** `bootstrap.min.css` → `style2.css` → `style.css` → `style3.css` → `@yield('styles')`.
Later files override earlier ones, so page-specific rules belong in `style3.css` or a new file pushed via `@section('styles')`, without editing shared rules.

**Fonts:** Alfa Slab One (headings), Poppins (body), and Material Symbols Rounded (icons) load from Google Fonts in `layouts/app.blade.php` (lines 13–14). The portal loads its own copies plus the local `Redotic.ttf`.

---

## Explanation

This project is a university assignment turned into a personal project. After the assignment is done I then took matter into my own hands to fix mistakes I made and make it better while learning more about Docker. 

---

## Use of AI

AI was used to help with the writing of this README.