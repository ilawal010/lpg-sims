<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Manager Dashboard') | Intisar Oil &amp; Gas</title>

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
            color: #fff;
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
        }

        .nav-link:hover {
            background: rgba(255,255,255,.09);
            color: #fff;
        }

        .nav-link.active {
            background: #21734d;
            color: #fff;
            font-weight: 650;
        }

        .nav-link.disabled {
            color: #83a293;
            opacity: .65;
            cursor: not-allowed;
        }

        .nav-icon {
            width: 19px;
            min-width: 19px;
            text-align: center;
            font-size: 17px;
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

        .main-wrapper {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            min-height: 75px;
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 32px;
        }

        .topbar-left,
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
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

            .topbar { padding: 14px 18px; }

            .content { padding: 22px 16px; }

            .user-details { display: none; }

            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
@php
    $manager = auth()->user();

    $assignedBranches = $manager
        ? $manager->branches()
            ->where('branches.status', 'active')
            ->orderBy('branches.branch_name')
            ->pluck('branches.branch_name')
        : collect();

    $branchLabel = $assignedBranches->isNotEmpty()
        ? $assignedBranches->implode(', ')
        : 'No active branch assigned';

    $managerMenus = [
        'OVERVIEW' => [
            ['Dashboard', 'manager.dashboard', '▦'],
        ],
        'SALES OPERATIONS' => [
            ['Point of Sale', 'admin.lpg-pos.index', '▣'],
            ['Sales History', 'manager.sales.index', '▤'],
            ['Sales Agents', 'manager.agents.index', '♙'],
        ],
        'INVENTORY' => [
            ['LPG Stock', 'manager.lpg-stock.index', '▦'],
            ['LPG Supply Receipts', 'manager.stock-receipts.index', '▣'],
            ['Accessories', 'manager.accessories.index', '▧'],
            ['Storage Units', 'manager.storage-units.index', '▤'],
            ['Stock Movements', 'manager.stock-movements.index', '⇄'],
            ['Suppliers', 'manager.suppliers.index', '▤'],
        ],
        'FINANCE' => [
            ['Expenses', 'manager.expenses.index', '▣'],
            ['Cashbook', 'manager.cashbook.index', '▤'],
        ],
        
        'REPORTS & ANALYTICS' => [
            ['Sales Reports', 'admin.reports.sales.index', '▤'],
            ['Inventory Reports', 'admin.reports.inventory.index', '▥'],
            ['Procurement Advice', 'admin.reports.procurement.index', '▤'],
            ['Financial Reports', 'admin.reports.financial.index', '▤'],
        ],

        'MY ACCOUNT' => [
            ['My Profile', 'profile.edit', '♙'],
        ],
    ];
@endphp

<div class="app">
    <div id="sidebarOverlay"
         class="sidebar-overlay"
         onclick="toggleSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-title">Intisar Oil &amp; Gas</div>
            <div class="brand-subtitle">LPG-SIMS · BRANCH MANAGER</div>
        </div>

        <nav class="sidebar-nav">
            @foreach($managerMenus as $section => $items)
                <div class="nav-heading">{{ $section }}</div>

                @foreach($items as [$label, $routeName, $icon])
                    @php
                        $available = $routeName && Route::has($routeName);
                        $active = $available && request()->routeIs($routeName);
                    @endphp

                    @if($available)
                        <a href="{{ route($routeName) }}"
                           class="nav-link {{ $active ? 'active' : '' }}">
                    @else
                        <span class="nav-link disabled"
                              title="Module under development">
                    @endif

                        <span class="nav-icon">{{ $icon }}</span>
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
            @endforeach
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
                        @yield('header', 'Branch Manager Dashboard')
                    </div>

                    <div class="topbar-subtitle">
                        Assigned branch: {{ $branchLabel }}
                    </div>
                </div>
            </div>

            <div class="topbar-right">
                <div class="user-avatar">
                    {{ strtoupper(substr($manager->name, 0, 1)) }}
                </div>

                <div class="user-details">
                    <div class="user-name">{{ $manager->name }}</div>
                    <div class="user-role">Branch Manager</div>
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
