<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Informasi Kepegawaian - Tech McRae')</title>
    <link rel="icon" href="{{ asset('logo.svg') }}">
    <link rel="stylesheet" href="{{ asset('\bootstrap\bootstrap-5.3.8-dist\css\bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style3.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Alfa+Slab+One&family=Poppins:wght@400;500;600;700&display=swap">
    @yield('styles')
    <script src="{{ asset('script.js') }}"></script>
    @yield('scripts')
</head>
<body>
    <button class="sidebar-menu-button">
        <a href="{{ url('/dashboard') }}" class="header-logo">
                <img src="{{ asset('logo.svg') }}" alt="Tech McRae" />
            </a>
        <span class="material-symbols-rounded">menu</span>
    </button>

    <aside class="sidebar collapsed">
        <header class="sidebar-header">
            <a href="{{ url('/dashboard') }}" class="header-logo">
                <img src="{{ asset('logo.svg') }}" alt="Tech McRae" />
            </a>
            <button class="sidebar-toggler">
                <span class="material-symbols-rounded">chevron_left</span>
            </button>
        </header>
        <nav class="sidebar-nav">
            <ul class="nav-list primary-nav">
                <li class="nav-item">
                    <a href="{{ url('/dashboard') }}" class="nav-link">
                        <span class="material-symbols-rounded">dashboard</span>
                        <span class="nav-label">Dashboard</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-item"><a class="nav-link dropdown-title">Dashboard</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/employees') }}" class="nav-link">
                        <span class="material-symbols-rounded">group</span>
                        <span class="nav-label">Employees</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-item"><a class="nav-link dropdown-title">Employees</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown-container">
                    <a href="#" class="nav-link dropdown-toggle">
                        <span class="material-symbols-rounded">calendar_today</span>
                        <span class="nav-label">Attendance</span>
                        <span class="dropdown-icon material-symbols-rounded">keyboard_arrow_down</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-item"><a class="nav-link dropdown-title">Attendance</a></li>
                        <li class="nav-item"><a href="{{ url('/attendance/records') }}" class="nav-link dropdown-link">Attendance Records</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown-container">
                    <a href="#" class="nav-link dropdown-toggle">
                        <span class="material-symbols-rounded">star</span>
                        <span class="nav-label">Leave</span>
                        <span class="dropdown-icon material-symbols-rounded">keyboard_arrow_down</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="nav-item"><a class="nav-link dropdown-title">Leave</a></li>
                        <li class="nav-item"><a href="{{ url('/leave/requests') }}" class="nav-link dropdown-link">Leave Requests</a></li>
                        <li class="nav-item"><a href="{{ url('/leave/status') }}" class="nav-link dropdown-link">Leave Status</a></li>
                    </ul>
                </li>
            </ul>
            <ul class="nav-list secondary-nav">
                <li class="nav-item">
                    <form action="{{ url('/logout') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="nav-link" style="background:none;border:none;width:100%;text-align:left;cursor:pointer;">
                            <span class="material-symbols-rounded">logout</span>
                            <span class="nav-label">Sign Out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        @yield('content')
    </main>

    {{-- 1. IF GUEST: Show the overlay permanently --}}
    @guest
    <div class="login-overlay" id="loginOverlay">
        <div class="login-box">
            <div class="login-logo">
                <img src="{{ asset('logo.svg') }}" alt="Tech McRae">
            </div>
            <form action="{{ url('/login') }}" method="POST" class="login-form">
                @csrf
                <div class="login-field">
                    <input type="text" name="login" id="login" placeholder="Email/Employee ID" value="{{ old('login') }}" required>
                </div>
                <div class="login-field login-field-password">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <button type="button" class="toggle-password" aria-label="Toggle password visibility">
                        <span class="material-symbols-rounded">visibility</span>
                    </button>
                </div>
                @if ($errors->any())
                    <div class="login-error">{{ $errors->first() }}</div>
                @endif
                <label class="remember-me">
                    <input type="checkbox" name="remember" checked>
                    <span class="check-box"><span class="material-symbols-rounded">check</span></span>
                    <span class="text">Remember me</span>
                </label>
                <button type="submit" class="login-btn">Log In</button>
            </form>
        </div>
    </div>
    @endguest

    {{-- 2. IF JUST LOGGED IN: Render the overlay, then fade it out --}}
    @auth
        @if(session('login_success'))
        <div class="login-overlay" id="loginOverlay">
            <div class="login-box">
                <div class="login-logo">
                    <img src="{{ asset('logo.svg') }}" alt="Tech McRae">
                </div>
            </div>
        </div>
        <script>
            // Trigger the CSS transition shortly after the dashboard loads
            document.addEventListener("DOMContentLoaded", function() {
                setTimeout(() => {
                    document.getElementById('loginOverlay').classList.add('logged-in');
                }, 150); // Slight delay ensures the CSS transition renders smoothly
            });
        </script>
        @endif
    @endauth

    <script src="{{ asset('script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Password toggle
        document.querySelector('.toggle-password')?.addEventListener('click', function () {
            const pw = document.getElementById('password');
            const icon = this.querySelector('.material-symbols-rounded');
            if (pw.type === 'password') { pw.type = 'text'; icon.textContent = 'visibility_off'; }
            else { pw.type = 'password'; icon.textContent = 'visibility'; }
        });
    </script>
    @yield('scripts') 
</body>
</html>