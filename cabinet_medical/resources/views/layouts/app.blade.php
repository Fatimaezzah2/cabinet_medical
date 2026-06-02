<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <script>
        document.documentElement.dataset.theme = localStorage.getItem('theme') || 'light';
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --page-bg: #f5f7fb;
            --surface-bg: #ffffff;
            --surface-border: #e5e7eb;
            --text-color: #0f172a;
            --muted-color: #64748b;
            --sidebar-link: #334155;
            --sidebar-hover-bg: #e9f2ff;
            --table-stripe: rgba(13, 110, 253, 0.04);
        }

        [data-theme="dark"] {
            color-scheme: dark;
            --page-bg: #111827;
            --surface-bg: #1f2937;
            --surface-border: #374151;
            --text-color: #f8fafc;
            --muted-color: #cbd5e1;
            --sidebar-link: #e2e8f0;
            --sidebar-hover-bg: #334155;
            --table-stripe: rgba(148, 163, 184, 0.08);
        }

        body {
            background: var(--page-bg);
            color: var(--text-color);
        }

        .navbar-brand {
            letter-spacing: 0;
        }

        .sidebar {
            background: var(--surface-bg);
            border-right: 1px solid var(--surface-border);
        }

        .sidebar .nav-link {
            color: var(--sidebar-link);
            border-radius: 6px;
            margin-bottom: 4px;
            padding: 10px 12px;
        }

        .sidebar .nav-link:hover {
            background: var(--sidebar-hover-bg);
            color: #0d6efd;
        }

        main {
            padding: 24px;
        }

        .content-card {
            background: var(--surface-bg);
            border: 1px solid var(--surface-border);
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        [data-theme="dark"] .table {
            --bs-table-bg: var(--surface-bg);
            --bs-table-color: var(--text-color);
            --bs-table-striped-bg: var(--table-stripe);
            --bs-table-striped-color: var(--text-color);
            --bs-table-hover-bg: #334155;
            --bs-table-hover-color: var(--text-color);
            border-color: var(--surface-border);
        }

        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .form-select,
        [data-theme="dark"] .card,
        [data-theme="dark"] .alert-info {
            background-color: var(--surface-bg);
            border-color: var(--surface-border);
            color: var(--text-color);
        }

        [data-theme="dark"] .text-muted,
        [data-theme="dark"] .navbar-text {
            color: var(--muted-color) !important;
        }

        [data-theme="dark"] footer {
            background: var(--surface-bg) !important;
            border-color: var(--surface-border) !important;
        }

        .auth-card {
            max-width: 460px;
            width: 100%;
        }

        label {
            margin-top: 14px;
            font-weight: 600;
        }

        input:not(.form-control),
        select:not(.form-select) {
            width: 100%;
            margin-top: 6px;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            margin-bottom: 24px;
        }

        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }

        .actions form,
        .inline-form {
            margin: 0;
        }

        .link {
            color: #0d6efd;
            text-decoration: none;
        }

        .success {
            margin-bottom: 16px;
            padding: 12px 14px;
            background: #d1e7dd;
            border: 1px solid #badbcc;
            border-radius: 6px;
            color: #0f5132;
        }

        .error {
            margin-top: 6px;
            color: #dc3545;
            font-size: 14px;
        }

        .logout-form {
            margin: 0;
        }

        @media (min-width: 768px) {
            .sidebar {
                min-height: calc(100vh - 117px);
            }

            main {
                padding: 32px;
            }
        }

        @media (max-width: 767.98px) {
            .page-header {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 px-md-4">
            <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">Cabinet Medical</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNavbar">
                <div class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                    <a class="btn btn-outline-light btn-sm" href="{{ route('language', 'en') }}">{{ __('messages.english') }}</a>
                    <a class="btn btn-outline-light btn-sm" href="{{ route('language', 'fr') }}">{{ __('messages.french') }}</a>
                    <button id="themeToggle" class="btn btn-outline-light btn-sm" type="button" aria-label="{{ __('messages.toggle_theme') }}">
                        {{ __('messages.dark_mode') }}
                    </button>

                    @auth
                        <span class="navbar-text text-white px-lg-2">{{ auth()->user()->name }}</span>
                        <form class="logout-form" method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn btn-light btn-sm" type="submit">{{ __('messages.logout') }}</button>
                        </form>
                    @else
                        <a class="btn btn-light btn-sm" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                        <a class="btn btn-success btn-sm" href="{{ route('register') }}">{{ __('messages.register') }}</a>
                    @endauth
                </div>
            </div>
        </nav>
    </header>

    <div class="container-fluid flex-grow-1">
        <div class="row flex-grow-1">
            @auth
                <aside class="col-md-3 col-lg-2 sidebar p-3">
                    <h6 class="text-uppercase text-muted mb-3">{{ __('messages.menu') }}</h6>
                    <nav class="nav flex-md-column gap-1">
                        <a class="nav-link" href="{{ route('dashboard') }}">{{ __('messages.dashboard') }}</a>
                        @if (auth()->user()->isAdmin())
                            <a class="nav-link" href="{{ route('admin.dashboard') }}">{{ __('messages.admin_dashboard') }}</a>
                            <a class="nav-link" href="{{ route('admin.users') }}">{{ __('messages.manage_users') }}</a>
                            <a class="nav-link" href="{{ route('admin.services') }}">{{ __('messages.manage_services') }}</a>
                            <a class="nav-link" href="{{ route('admin.appointments') }}">{{ __('messages.all_appointments') }}</a>
                        @endif
                        @if (auth()->user()->isDoctor())
                            <a class="nav-link" href="{{ route('doctor.dashboard') }}">{{ __('messages.doctor_dashboard') }}</a>
                            <a class="nav-link" href="{{ route('doctor.appointments') }}">{{ __('messages.my_appointments') }}</a>
                        @endif
                        @if (auth()->user()->isPatient())
                            <a class="nav-link" href="{{ route('patient.dashboard') }}">{{ __('messages.patient_dashboard') }}</a>
                            <a class="nav-link" href="{{ route('patient.appointments') }}">{{ __('messages.my_appointments') }}</a>
                            <a class="nav-link" href="{{ route('patient.appointments.create') }}">{{ __('messages.book_appointment') }}</a>
                        @endif
                        <a class="nav-link" href="{{ route('appointments.create') }}">{{ __('messages.new_appointment') }}</a>
                    </nav>
                </aside>

                <main class="col-md-9 col-lg-10">
                    @yield('content')
                </main>
            @else
                <main class="col-12 d-flex justify-content-center align-items-center">
                    @yield('content')
                </main>
            @endauth
        </div>
    </div>

    <footer class="bg-white border-top py-3 text-center text-muted">
        <small>&copy; {{ date('Y') }} Cabinet Medical. {{ __('messages.footer') }}</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        const themeToggle = document.getElementById('themeToggle');
        const darkModeLabel = @json(__('messages.dark_mode'));
        const lightModeLabel = @json(__('messages.light_mode'));

        function applyTheme(theme) {
            document.documentElement.dataset.theme = theme;
            localStorage.setItem('theme', theme);

            if (themeToggle) {
                themeToggle.textContent = theme === 'dark' ? lightModeLabel : darkModeLabel;
            }
        }

        applyTheme(localStorage.getItem('theme') || 'light');

        themeToggle?.addEventListener('click', function () {
            applyTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
        });
    </script>
    @stack('scripts')
</body>
</html>
