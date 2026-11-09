<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Sales Agent') | Intisar Oil & Gas</title>

    <style>
        :root {
            --primary: #11633e;
            --primary-dark: #093b28;
            --sidebar: #103d2d;
            --background: #f4f7f5;
            --surface: #ffffff;
            --border: #e3ebe6;
            --text: #20352b;
            --muted: #78877e;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        a { text-decoration: none; }

        .app {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 265px;
            min-width: 265px;
            height: 100vh;
            position: sticky;
            top: 0;
            background: var(--sidebar);
            color: white;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 27px 22px 23px;
            border-bottom: 1px solid rgba(255,255,255,.1);
        }

        .brand-title {
            font-size: 19px;
            font-weight: 750;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #a9c7b6;
            margin-top: 6px;
            letter-spacing: .6px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 15px 12px 25px;
            overflow-y: auto;
        }

        .nav-heading {
            font-size: 10px;
            font-weight: 700;
            color: #91b3a0;
            letter-spacing: 1.3px;
            padding: 23px 12px 9px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 12px 13px;
            border-radius: 8px;
            color: #d6e5dc;
            font-size: 13px;
            margin: 2px 0;
        }

        .nav-link:hover,
        .nav-link.active {
            background: #21734d;
            color: white;
        }

        .nav-icon {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
        }

        .nav-icon svg {
            width: 19px;
            height: 19px;
            fill: none;
            stroke: currentColor;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .sidebar-bottom {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.1);
            font-size: 11px;
            color: #a9c7b6;
        }

        .main-wrapper {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 75px;
            background: white;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .topbar-title {
            font-size: 17px;
            font-weight: 700;
        }

        .topbar-subtitle {
            color: var(--muted);
            font-size: 12px;
            margin-top: 4px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-avatar {
            width: 37px;
            height: 37px;
            background: #e6f3eb;
            color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
        }

        .user-name {
            font-size: 13px;
            font-weight: 650;
        }

        .user-role {
            font-size: 11px;
            color: var(--muted);
            margin-top: 3px;
        }

        .logout-button {
            border: 1px solid var(--border);
            background: white;
            padding: 9px 13px;
            border-radius: 7px;
            cursor: pointer;
        }

        .content {
            padding: 30px 32px;
            max-width: 1600px;
            margin: auto;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 17px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 650;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-secondary {
            background: white;
            border-color: var(--border);
            color: var(--text);
        }

        .alert-success,
        .alert-error {
            padding: 13px 17px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .alert-success {
            background: #e8f6ec;
            color: #17643b;
        }

        .alert-error {
            background: #fff0f0;
            color: #a52a2a;
        }

        .mobile-toggle {
            display: none;
            border: none;
            background: transparent;
            font-size: 25px;
            cursor: pointer;
        }

        .sidebar-overlay { display: none; }

        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                left: -265px;
                transition: left .25s ease;
            }

            .sidebar.open { left: 0; }

            .sidebar-overlay.show {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.4);
                z-index: 90;
            }

            .mobile-toggle { display: block; }

            .topbar { padding: 0 18px; }

            .content { padding: 22px 16px; }

            .user-details { display: none; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="app">
    <div id="sidebarOverlay"
         class="sidebar-overlay"
         onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-title">Intisar Oil &amp; Gas</div>
            <div class="brand-subtitle">LPG-SIMS · SALES AGENT</div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-heading">SALES OPERATIONS</div>

            <a href="{{ route('admin.lpg-pos.index') }}"
               class="nav-link {{ request()->routeIs('admin.lpg-pos.*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="16" rx="2"/>
                        <path d="M7 9h10M7 13h10M7 17h6"/>
                    </svg>
                </span>
                <span>Point of Sale</span>
            </a>

            @if(Route::has('agent.sales.index'))
                <a href="{{ route('agent.sales.index') }}"
                   class="nav-link {{ request()->routeIs('agent.sales.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M5 3h14v18H5zM8 8h8M8 12h8M8 16h5"/>
                        </svg>
                    </span>
                    <span>My Sales</span>
                </a>
            @endif

            @if(Route::has('agent.reports.sales.index'))
                <a href="{{ route('agent.reports.sales.index') }}"
                   class="nav-link {{ request()->routeIs('agent.reports.sales.*') ? 'active' : '' }}">
                    <span class="nav-icon">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 20V10M10 20V4M16 20v-8M22 20V7"/>
                        </svg>
                    </span>
                    <span>My Sales Report</span>
                </a>
            @endif

            <div class="nav-heading">MY ACCOUNT</div>

            <a href="{{ route('profile.edit') }}"
               class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24">
                        <circle cx="12" cy="8" r="4"/>
                        <path d="M4 21v-2a8 8 0 0 1 16 0v2"/>
                    </svg>
                </span>
                <span>My Profile</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            Intisar Oil &amp; Gas · LPG-SIMS
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <div style="display:flex;align-items:center;gap:14px;">
                <button type="button"
                        class="mobile-toggle"
                        onclick="toggleSidebar()"
                        aria-label="Toggle navigation">☰</button>

                <div>
                    <div class="topbar-title">
                        @yield('header', 'Sales Operations')
                    </div>
                    <div class="topbar-subtitle">
                        Intisar Oil &amp; Gas
                    </div>
                </div>
            </div>

            <div class="topbar-right">
                <div class="user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="user-details">
                    <div class="user-name">
                        {{ auth()->user()->name }}
                    </div>
                    <div class="user-role">Sales Agent</div>
                </div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-button">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert-error">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').classList.toggle('show');
    }
</script>

@stack('scripts')
</body>
</html>
