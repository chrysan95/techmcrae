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
├── tests/Feature/                # PHPUnit feature tests (run by CI)
├── .github/workflows/ci.yml      # GitHub Actions: unit-tests + docker-e2e
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
| `app` | built from `Dockerfile` (tagged `techmcrae-app:1.0`) | http://localhost:8080 | Laravel on Apache + PHP 8.3 |
| `db` | `mariadb:10.11` | internal only (`db:3306`) | Database |
| `ngrok` | `ngrok/ngrok:latest` | http://localhost:4040 (inspector) | Public HTTPS tunnel. Only starts with `--profile tunnel` (see [Sharing the App with ngrok](#sharing-the-app-with-ngrok)) |

All services share a private `backend` network. The database port is not published to the host.

### Docker files

```
tech-mcrae/
├── Dockerfile                    # Multi-stage build: Composer stage → php:8.3-apache runtime, HEALTHCHECK on /health
├── docker-compose.yml            # app + db + ngrok (optional), volumes, healthchecks
├── .dockerignore                 # Keeps vendor/, .env, caches, logs out of the image
├── .env.docker.example           # Template for app config, DB credentials and ngrok token
└── docker/
    ├── entrypoint.sh             # Runs on every container start
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

**2. Clone the repository and create the env file**

```bash
gh repo clone chrysan95/techmcrae
cd techmcrae
cp .env.docker.example .env.docker
```

Edit `.env.docker` and set at least `DB_PASSWORD` and `DB_ROOT_PASSWORD`. All configuration lives in this one file: Laravel settings, the database credentials MariaDB is created with, and the ngrok token.

**3. Add the database dump**

Export your database to `docker/mysql/init/` as a `.sql` file, for example from phpMyAdmin with **Export → Quick → SQL**. Remove any `CREATE DATABASE` or `USE` lines; the container creates and selects the database itself. The dump is only imported the first time the `db_data` volume is created. To re-import, run `docker compose --env-file .env.docker down -v` (this deletes all database data).

**4. Build and start**

```bash
docker compose --env-file .env.docker up -d --build
docker compose logs -f app      # wait for "Database is up."
```

Always pass `--env-file .env.docker`. Docker Compose fills in `${...}` values in `docker-compose.yml` (DB credentials, `NGROK_AUTHTOKEN`) from `.env` by default, not from `.env.docker`. Without the flag, MariaDB gets created with your local XAMPP `.env` values or with blank ones.

**5. Open the app**

| What | Where |
|---|---|
| App | http://localhost:8080 |

Always use the same host (`localhost:8080`, not `127.0.0.1:8080`). Cookies are per host, and switching hosts causes CSRF mismatches.

---

## Sharing the App with ngrok

[ngrok](https://ngrok.com) gives your local container a public HTTPS URL, so friends or testers can open the app from anywhere without deploying it. The tunnel runs as the optional `ngrok` service in `docker-compose.yml`.

### How it works

```
Friend's browser ──HTTPS──▶ ngrok cloud ──tunnel──▶ techmcrae-ngrok container ──HTTP──▶ app:80
```

ngrok handles HTTPS and forwards plain HTTP to the `app` container over the internal `backend` network. It sends the original scheme and host in the `X-Forwarded-*` headers.

### Steps

**1. Get an authtoken**

Sign up at https://ngrok.com (free), then copy your token from https://dashboard.ngrok.com/get-started/your-authtoken. Put it in `.env.docker`:

```
NGROK_AUTHTOKEN=your_token_here
```

**2. Start the stack with the tunnel profile**

```bash
docker compose --env-file .env.docker --profile tunnel up -d --build
```

`--profile tunnel` turns on the `ngrok` service. ngrok waits until `app` reports healthy.

**3. Get the public URL**

Open the ngrok inspector at http://localhost:4040. It shows the URL and every request that goes through the tunnel. Or run:

```bash
curl -s http://localhost:4040/api/tunnels
```

The URL looks like `https://something-random.ngrok-free.dev`. Share that link.

**4. Stop sharing**

```bash
docker compose stop ngrok
```

The app keeps running locally; only the public URL goes offline.

### Code change required for ngrok: trusted proxies

`bootstrap/app.php` now trusts proxy headers:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    // ...
})
```

**Why:** the request reaches Laravel as plain HTTP from the ngrok container. Without this line, Laravel ignores `X-Forwarded-Proto: https` and builds every `asset()`, `url()` and form `action` as `http://...`. The browser loads the page over HTTPS, so it blocks those links as mixed content: CSS and JS don't load and forms don't submit. With the proxy trusted, Laravel builds `https://<ngrok-host>/...` links.

`at: '*'` trusts any proxy. That's fine here because only ngrok sits in front of the app. On a real server, restrict it to your load balancer's IP.

### Notes and limitations (free plan)

- **The URL changes every time the ngrok container restarts.** To keep one fixed address, claim the free static domain in the ngrok dashboard (**Domains**) and change the service command to `http --url=your-name.ngrok-free.dev app:80`.
- **Visitors see an ngrok warning page** on their first visit and must click **Visit Site**.
- **Your machine must stay on**, with Docker running, while others use the link.
- **Anyone with the link can reach the login page.** Share it only with people you trust, and stop the tunnel when you're done. All seeded accounts use the default password `password` (see [Known Limitations](#known-limitations)), so change them first.
- Leave `APP_URL` as `http://localhost:8080`. Laravel builds links from the incoming request's host, so you don't need to change it for each new tunnel URL.

### Without Docker (XAMPP / `php artisan serve`)

Install ngrok from https://ngrok.com/download, then:

```bash
ngrok config add-authtoken <your_token>
ngrok http 80      # Apache/XAMPP serving the app
ngrok http 8000    # or: php artisan serve
```

The `trustProxies` change above is needed here too.

---

## Automated Testing (GitHub Actions)

Every push to `main` and every pull request runs the workflow in `.github/workflows/ci.yml`. Results show up in the repository's **Actions** tab and as a ✔ / ✘ next to each commit and PR.

### What the workflow does

The workflow has two jobs. `docker-e2e` only runs if `unit-tests` passes.

**1. `unit-tests`: PHPUnit feature tests against a real MariaDB**

- Starts a `mariadb:10.11` service container, installs PHP 8.3 and Composer dependencies.
- Imports every `.sql` file in `docker/mysql/init/`, the same dump Docker uses, so tests run on the real seeded data.
- Runs `php artisan test` and uploads the results as the `phpunit-report` artifact (`junit.xml`).

| Test file | What it checks |
|---|---|
| `tests/Feature/AuthTest.php` | Passwords are stored hashed; login with Employee ID or email; admins go to `/dashboard`; wrong or empty credentials are rejected |
| `tests/Feature/AccessIsolationTest.php` | An employee can't withdraw or see another employee's leave; employees get 403 on admin pages; guests are redirected to login |
| `tests/Feature/LeaveValidationTest.php` | End date before start date, unknown leave type, and maternity leave for male employees are rejected; a valid request is created |
| `tests/Feature/HealthTest.php` | `/health` returns `{"status":"ok","db":"up"}` |

Tests use `DatabaseTransactions`, so every row a test creates is rolled back afterwards.

**2. `docker-e2e`: the full Docker stack, end to end**

Builds the image and starts `app` + `db` with `docker compose up --wait`, then checks:

| Step | Passes when |
|---|---|
| Health check | `GET http://localhost:8080/health` succeeds |
| Passwords hashed by init SQL | No row in `employees` has a non-bcrypt password |
| Persistence | A row inserted before `docker compose down` / `up` is still there afterwards (the `db_data` volume works) |
| Fault injection and recovery | With `db` stopped, `/health` returns **503**; after `db` restarts, `/health` recovers within 60 s |

Container logs are uploaded as the `compose-logs` artifact, even when a step fails.

### Viewing results

1. Open the repository on GitHub and go to **Actions → CI**.
2. Click a run to see each job and step. A failing step's log is expanded automatically.
3. Download `phpunit-report` or `compose-logs` from the **Artifacts** section at the bottom of the run page.

You can also run the workflow by hand from the Actions tab if you add `workflow_dispatch:` under `on:` in `ci.yml`.

### Running the tests locally

`phpunit.xml` defaults to an in-memory SQLite database, which doesn't have this project's tables, because the schema comes from the SQL dump, not from migrations. Run the tests against a throwaway MariaDB container instead (PowerShell):

```powershell
# 1. Start a disposable MariaDB with the dump imported (port 3310 so it doesn't clash with XAMPP)
docker run -d --rm --name techmcrae-test -e MARIADB_DATABASE=techmcrae -e MARIADB_ROOT_PASSWORD=root `
  -p 3310:3306 -v "${PWD}/docker/mysql/init:/docker-entrypoint-initdb.d:ro" mariadb:10.11

# 2. Wait ~15 seconds for the import, then run the tests
$env:DB_CONNECTION="mysql"; $env:DB_HOST="127.0.0.1"; $env:DB_PORT="3310"
$env:DB_DATABASE="techmcrae"; $env:DB_USERNAME="root"; $env:DB_PASSWORD="root"
php artisan test

# 3. Remove the container (--rm deletes it on stop)
docker stop techmcrae-test
```

Don't point the tests at your real database. Transactions are rolled back, but the tests expect the seeded accounts with the default password.

### Adding a test

Create a class in `tests/Feature/` that extends `Tests\TestCase` and uses `DatabaseTransactions`, then push. CI picks it up automatically. Seeded accounts such as `TMC0001` (employee) and `TMC0099` (admin), both with the password `password`, are available in every test.

---

## Default Login

Log in with either the **email** or **Employee ID** of a seeded record:

| Role | Login | Password | Redirects to |
|---|---|---|---|
| Admin | `TMC0001` / admin email | `password` | `/dashboard` |
| Employee | `TMC0002` / employee email | `password` | `/portal` |

---

## Troubleshooting

| Problem | Fix |
|---|---|
| Charts not rendering | Browser needs internet access for the Chart.js CDN; check console for `Chart is not defined`. |
| Wrong clock / hours | Confirm `Asia/Jakarta` in `config/app.php`; portal JS formats in WIB regardless of device time. |
| CSS not loading | Confirm files are in `public/css/`; run `php artisan optimize:clear`. |
| `SQLSTATE[HY000] [2002]` | MySQL not running or wrong `DB_HOST` / `DB_PORT`. |
| 403 on admin pages | Logged-in user's `role` is not `admin`. |
| ngrok container exits / `ERR_NGROK_4018` | `NGROK_AUTHTOKEN` missing or invalid. Start with `--env-file .env.docker` so the token is passed through. |
| Unstyled page or forms not submitting through ngrok | Mixed content: make sure `trustProxies(at: '*')` is in `bootstrap/app.php` and rebuild with `--build`. |
| CI `unit-tests` fails with `no such table` locally | You're on the default SQLite config. Set the `DB_*` env vars as in [Running the tests locally](#running-the-tests-locally). |
| ngrok URL stopped working | The free URL changes when the container restarts. Check http://localhost:4040 for the new one. |

---

## Known Limitations

- **Default seeded password**: every account in the database dump uses the password `password`. Change these before exposing the app to anyone you don't trust (including over ngrok).
- **No server-side pagination**: tables use `->get()` and filter client-side; large datasets will slow down. This was done only for testing only because we only had a small dummy dataset.
- **Chart.js loaded unpinned from CDN** (`layouts/app.blade.php`): pin a version (e.g. `chart.js@4.4.1`) for reproducible builds; requires internet access in the browser.
- **No WebSockets**: real-time updates are client-side timers/AJAX, not pushed from the server.

### Resolved

- ~~**Plaintext password check**~~: `AuthController@login` now looks up the user by email or Employee ID and verifies the password with `Hash::check()`. Only bcrypt hashes (`$2y$...`) are accepted. The `Employee` model casts `password` as `hashed`, so new or changed passwords are hashed automatically. The database dump converts any remaining plaintext passwords to a bcrypt hash.
- **Login and access auditing**: successful logins, failed attempts (with IP), logouts and denied role checks (`RoleMiddleware`) are now logged. In Docker they go to the container's stderr: `docker compose logs app`.
- **Employee model saves**: `Employee` now sets `$timestamps = false` (the table has no `updated_at`) and allows `gender` to be filled. Before, any save failed, including setting the "Remember me" token at login.
- **Automated tests**: feature tests plus a Docker end-to-end job run on every push via GitHub Actions (see [Automated Testing](#automated-testing-github-actions)).
- **Health check**: `GET /health` returns `{"status":"ok","db":"up"}`, or a 503 if the database is unreachable. The Docker `HEALTHCHECK` uses it, and ngrok only starts once the app is healthy.

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
