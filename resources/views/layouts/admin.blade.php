<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') | Intisar Oil & Gas</title>

    <style>
        :root {
            --primary: #11633e;
            --primary-dark: #093b28;
            --sidebar: #103d2d;
            --sidebar-deep: #0a3023;
            --background: #f4f7f5;
            --surface: #ffffff;
            --border: #e3ebe6;
            --text: #20352b;
            --muted: #78877e;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--background);
            color: var(--text);
        }

        a {
            text-decoration: none;
        }

        .app {
            min-height: 100vh;
            display: flex;
        }

        /* Sidebar */

        .sidebar {
            width: 265px;
            min-width: 265px;
            height: 100vh;
            position: sticky;
            top: 0;
            background: var(--sidebar);
            color: #ffffff;
            display: flex;
            flex-direction: column;
            z-index: 100;
        }

        .sidebar-brand {
            padding: 27px 22px 23px;
            border-bottom: 1px solid rgba(255,255,255,.10);
        }

        .brand-title {
            font-size: 19px;
            font-weight: 750;
            letter-spacing: -.4px;
        }

        .brand-subtitle {
            font-size: 11px;
            color: #a9c7b6;
            margin-top: 6px;
            letter-spacing: .6px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 15px 12px 25px;
        }

        .nav-heading {
            font-size: 10px;
            font-weight: 700;
            color: #91b3a0;
            text-transform: uppercase;
            letter-spacing: 1.3px;
            padding: 23px 12px 9px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 13px;
            border-radius: 8px;
            color: #d6e5dc;
            font-size: 13px;
            font-weight: 500;
            margin: 2px 0;
            transition: background .2s ease;
        }

        .nav-link:hover {
            background: rgba(255,255,255,.09);
            color: #ffffff;
        }

        .nav-link.active {
            background: #21734d;
            color: #ffffff;
            font-weight: 650;
        }

        .nav-link.disabled {
            color: #83a293;
            opacity: .65;
            cursor: not-allowed;
        }

        .nav-icon {
            width: 19px;
            height: 19px;
            flex-shrink: 0;
        }

        .nav-icon svg {
            width: 19px;
            height: 19px;
            stroke: currentColor;
            fill: none;
            stroke-width: 1.8;
            stroke-linecap: round;
            stroke-linejoin: round;
        }

        .coming-soon {
            margin-left: auto;
            font-size: 9px;
            opacity: .7;
        }

        .sidebar-bottom {
            padding: 16px 20px;
            border-top: 1px solid rgba(255,255,255,.10);
            font-size: 11px;
            color: #a9c7b6;
        }

        /* Main */

        .main-wrapper {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            height: 75px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 32px;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
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
            color: #4c5c52;
            cursor: pointer;
            font-size: 12px;
        }

        .logout-button:hover {
            background: #f4f7f5;
        }

        .content {
            padding: 30px 32px;
            max-width: 1600px;
            margin: auto;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 24px;
        }

        .page-title {
            font-size: 23px;
            font-weight: 750;
            margin: 0;
        }

        .page-description {
            font-size: 13px;
            color: var(--muted);
            margin-top: 7px;
        }

        .card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,.015);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 17px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 13px;
            font-weight: 650;
            cursor: pointer;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-secondary {
            background: white;
            border-color: var(--border);
            color: var(--text);
        }

        .alert-success {
            padding: 13px 17px;
            border-radius: 8px;
            background: #e8f6ec;
            border: 1px solid #bde2c8;
            color: #17643b;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .alert-error {
            padding: 13px 17px;
            border-radius: 8px;
            background: #fff0f0;
            border: 1px solid #f2caca;
            color: #a52a2a;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .mobile-toggle {
            display: none;
            border: none;
            background: transparent;
            font-size: 25px;
            cursor: pointer;
            color: var(--primary);
        }

        .sidebar-overlay {
            display: none;
        }

        @media (max-width: 900px) {
            .sidebar {
                position: fixed;
                left: -265px;
                transition: left .25s ease;
            }

            .sidebar.open {
                left: 0;
            }

            .sidebar-overlay.show {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,.4);
                z-index: 90;
            }

            .mobile-toggle {
                display: block;
            }

            .topbar {
                padding: 0 18px;
            }

            .content {
                padding: 22px 16px;
            }

            .user-details {
                display: none;
            }

            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }
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
            <div class="brand-subtitle">LPG-SIMS MANAGEMENT</div>
        </div>

        <nav class="sidebar-nav">

           
        @php
            $menus = [
                'OVERVIEW' => [
                    ['Dashboard', 'dashboard', 'dashboard', 'dashboard.view'],
                ],

                'SALES & POS' => [
                    ['Point of Sale', 'admin.lpg-pos.index', 'pos', 'sales.create'],
                    ['Sales History', 'admin.lpg-sales.index', 'sales', 'sales.view'],
                    ['LPG Pricing', 'admin.lpg-pricing.index', 'tags', 'prices.manage'],
                ],

                'INVENTORY' => [
                    ['LPG Stock', 'admin.lpg-stock.index', 'inventory', 'inventory.view'],
                    ['Accessories', 'admin.accessories.index', 'accessory', 'inventory.view'],
                    ['Stock Adjustments', null, 'adjust', 'inventory.adjust'],
                    ['Branches / Locations', 'admin.branches.index', 'map-pin', 'users.manage'],
                    ['LPG Storage Units', 'admin.storage-units.index', 'database', 'users.manage'],
                    ['LPG Opening Stock', 'admin.opening-stock.index', 'stock', 'users.manage'],
                ],

                'PROCUREMENT' => [
                    ['LPG Suppliers', 'admin.suppliers.index', 'supplier', 'users.manage'],
                ],

                'FINANCE' => [
                    ['Expenses', 'admin.expenses.index', 'expense', 'expenses.view'],
                    ['Cashbook', 'admin.cashbook.index', 'cashbook', 'cashbook.view'],
                ],

                'REPORTS & ANALYTICS' => [
                    ['Sales Reports', 'admin.reports.sales.index', 'report', 'reports.view'],
                    ['Inventory Reports', 'admin.reports.inventory.index', 'report', 'reports.view'],
                    ['Procurement Advice', 'admin.reports.procurement.index', 'report', 'reports.view'],
                    ['Financial Reports', 'admin.reports.financial.index', 'report', 'reports.view'],
                ],

                'ADMINISTRATION' => [
                    ['Staff Management', 'admin.users.index', 'users', 'users.manage'],
                    ['Roles & Permissions', 'admin.roles.index', 'shield', 'users.manage'],
                    ['Audit Logs', 'admin.audit-logs.index', 'clipboard-list', 'users.manage'],
                    ['Business Settings', 'admin.settings.edit', 'settings', 'users.manage'],
                    ['Procurement Stock Settings', 'admin.reports.procurement.settings.index', 'settings', 'reports.view'],
                ],
            ];
        @endphp


            @foreach($menus as $section => $items)

                
                @php
                    $visibleItems = collect($items)->filter(function ($item) {
                        $user = auth()->user();

                        if (! $user?->hasPermission($item[3])) {
                            return false;
                        }
                        
                        if (in_array($item[1], [
                            'admin.reports.procurement.index',
                            'admin.reports.procurement.settings.index',
                        ], true)) {
                            return $user->hasRole('owner')
                                || $user->hasRole('manager');
                        }

                        
                        if (in_array($item[1], [
                            'admin.roles.index',
                            'admin.audit-logs.index',
                            'admin.settings.edit',
                            'admin.branches.index',
                            'admin.storage-units.index',
                            'admin.opening-stock.index',
                            'admin.suppliers.index',
                            'admin.lpg-sales.index' => 'admin.lpg-sales.*',
                            'admin.lpg-pos.index' => 'admin.lpg-pos.*',
                            'admin.lpg-stock.index' => 'admin.lpg-stock.*',
                            'admin.reports.procurement.settings.index',
                        ], true)) {
                            return $user->hasRole('owner');
                        }


                        return true;
                    });
                @endphp


                @if($visibleItems->isNotEmpty())

                    <div class="nav-heading">{{ $section }}</div>

                    @foreach($visibleItems as $item)

                        @php
                            [$label, $route, $icon, $permission] = $item;

                            $available = $route && Route::has($route);

                            
                            $active = $available && request()->routeIs(
                                match ($route) {
                                    'admin.users.index' => 'admin.users.*',
                                    'admin.branches.index' => 'admin.branches.*',
                                    'admin.storage-units.index' => 'admin.storage-units.*',
                                    'admin.opening-stock.index' => 'admin.opening-stock.*',
                                    'admin.suppliers.index' => 'admin.suppliers.*',
                                    default => $route,
                                }
                            );

                        @endphp

                        @if($available)
                            <a href="{{ route($route) }}"
                               class="nav-link {{ $active ? 'active' : '' }}">
                        @else
                            <span class="nav-link disabled"
                                  title="Module under development">
                        @endif

                            <span class="nav-icon">
                                <svg viewBox="0 0 24 24">
                                    @switch($icon)

                                        @case('dashboard')
                                            <rect x="3" y="3" width="7" height="7" rx="1"/>
                                            <rect x="14" y="3" width="7" height="7" rx="1"/>
                                            <rect x="3" y="14" width="7" height="7" rx="1"/>
                                            <rect x="14" y="14" width="7" height="7" rx="1"/>
                                            @break

                                        @case('users')
                                        @case('customer')
                                            <circle cx="9" cy="8" r="3"/>
                                            <path d="M3 20v-2a6 6 0 0 1 12 0v2"/>
                                            <path d="M17 5a3 3 0 0 1 0 6M19 15a5 5 0 0 1 2 4"/>
                                            @break

                                        @case('settings')
                                            <circle cx="12" cy="12" r="3"/>
                                            <path d="M12 2v3M12 19v3M2 12h3M19 12h3M5 5l2 2M17 17l2 2M19 5l-2 2M7 17l-2 2"/>
                                            @break

                                        @case('report')
                                            <path d="M5 3h10l4 4v14H5z"/>
                                            <path d="M15 3v5h4M8 12h8M8 16h8"/>
                                            @break

                                        @case('shield')
                                            <path d="M12 2 20 6v6c0 5-4 8-8 10-4-2-8-5-8-10V6z"/>
                                            <path d="m9 12 2 2 4-4"/>
                                            @break

                                        @default
                                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                                            <path d="M7 9h10M7 13h10M7 17h6"/>
                                    @endswitch
                                </svg>
                            </span>

                            <span>{{ $label }}</span>

                            @unless($available)
                                <span class="coming-soon">SOON</span>
                            @endunless

                        @if($available)
                            </a>
                        @else
                            </span>
                        @endif

                    @endforeach

                @endif

            @endforeach
        
            <div class="nav-heading">MY ACCOUNT</div>

            <a href="{{ route('profile.edit') }}"
            class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">

                <span class="nav-icon">
                    <svg viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round">

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

            <div class="topbar-left">

                <button type="button"
                        class="mobile-toggle"
                        onclick="toggleSidebar()"
                        aria-label="Toggle navigation">
                    ☰
                </button>

                <div>
                    <div class="topbar-title">
                        @yield('header', 'Management Dashboard')
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

                    <div class="user-role">
                        {{ auth()->user()->role?->display_name ?? 'User' }}
                    </div>
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
                <div class="alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert-error">
                    {{ session('error') }}
                </div>
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
