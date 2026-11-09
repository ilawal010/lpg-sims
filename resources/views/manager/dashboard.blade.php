@extends('layouts.manager')

@section('title', 'Manager Dashboard')
@section('header', 'Branch Manager Dashboard')

@push('styles')
<style>
    .manager-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .manager-stat {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 22px;
        min-width: 0;
    }

    .manager-stat-label {
        color: #78877e;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 12px;
    }

    .manager-stat-value {
        color: #103d2d;
        font-size: 25px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .manager-stat-note {
        color: #78877e;
        font-size: 12px;
        margin-top: 10px;
    }

    .manager-section {
        margin-bottom: 24px;
    }

    .manager-section-title {
        font-size: 17px;
        font-weight: 750;
        margin: 0 0 16px;
    }

    .manager-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .manager-metric-row {
        display: flex;
        justify-content: space-between;
        gap: 16px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
    }

    .manager-metric-row:last-child {
        border-bottom: none;
    }

    .manager-metric-label {
        color: #78877e;
    }

    .manager-metric-value {
        color: #20352b;
        font-weight: 700;
        text-align: right;
    }

    .manager-branch {
        padding: 14px 16px;
        margin-top: 12px;
        border: 1px solid #e3ebe6;
        border-radius: 9px;
        background: #f8faf9;
    }

    .manager-branch-name {
        font-weight: 700;
    }

    .manager-branch-code {
        color: #78877e;
        font-size: 12px;
        margin-top: 5px;
    }

    @media (max-width: 1200px) {
        .manager-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .manager-stats,
        .manager-grid {
            grid-template-columns: 1fr;
        }

        .manager-stat-value {
            font-size: 23px;
        }
    }
</style>
@endpush

@section('content')

    @php
        $todayTransactions =
            (int) $lpgToday->transactions
            + (int) $accessoriesToday->transactions;

        $todayRevenue =
            (float) $lpgToday->revenue
            + (float) $accessoriesToday->revenue;

        $monthTransactions =
            (int) $lpgMonth->transactions
            + (int) $accessoriesMonth->transactions;

        $monthRevenue =
            (float) $lpgMonth->revenue
            + (float) $accessoriesMonth->revenue;

        $allTimeTransactions =
            (int) $lpgAllTime->transactions
            + (int) $accessoriesAllTime->transactions;

        $allTimeRevenue =
            (float) $lpgAllTime->revenue
            + (float) $accessoriesAllTime->revenue;
    @endphp

    <div class="page-header">
        <div>
            <h1 class="page-title">
                Welcome, {{ auth()->user()->name }}
            </h1>

            <p class="page-description">
                Intisar Oil &amp; Gas — Branch Operations Management
            </p>
        </div>
    </div>

    <div class="manager-stats">

        <div class="manager-stat">
            <div class="manager-stat-label">
                Today's Sales Revenue
            </div>

            <div class="manager-stat-value">
                ₦{{ number_format($todayRevenue, 2) }}
            </div>

            <div class="manager-stat-note">
                {{ number_format($todayTransactions) }}
                posted transactions
            </div>
        </div>

        <div class="manager-stat">
            <div class="manager-stat-label">
                Today's LPG Sold
            </div>

            <div class="manager-stat-value">
                {{ number_format((float) $lpgToday->quantity, 3) }} kg
            </div>

            <div class="manager-stat-note">
                {{ number_format((int) $lpgToday->transactions) }}
                LPG transactions
            </div>
        </div>

        <div class="manager-stat">
            <div class="manager-stat-label">
                This Month's Revenue
            </div>

            <div class="manager-stat-value">
                ₦{{ number_format($monthRevenue, 2) }}
            </div>

            <div class="manager-stat-note">
                {{ number_format($monthTransactions) }}
                posted transactions
            </div>
        </div>

        <div class="manager-stat">
            <div class="manager-stat-label">
                All-Time Sales Revenue
            </div>

            <div class="manager-stat-value">
                ₦{{ number_format($allTimeRevenue, 2) }}
            </div>

            <div class="manager-stat-note">
                {{ number_format($allTimeTransactions) }}
                posted transactions
            </div>
        </div>

    </div>

    <div class="manager-section">
        <h2 class="manager-section-title">
            Today's Sales Breakdown
        </h2>

        <div class="manager-grid">

            <div class="card">
                <h3 style="margin-top:0;">LPG Sales</h3>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Revenue</span>
                    <span class="manager-metric-value">
                        ₦{{ number_format((float) $lpgToday->revenue, 2) }}
                    </span>
                </div>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Quantity Sold</span>
                    <span class="manager-metric-value">
                        {{ number_format((float) $lpgToday->quantity, 3) }} kg
                    </span>
                </div>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Transactions</span>
                    <span class="manager-metric-value">
                        {{ number_format((int) $lpgToday->transactions) }}
                    </span>
                </div>
            </div>

            <div class="card">
                <h3 style="margin-top:0;">Accessory Sales</h3>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Revenue</span>
                    <span class="manager-metric-value">
                        ₦{{ number_format((float) $accessoriesToday->revenue, 2) }}
                    </span>
                </div>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Units Sold</span>
                    <span class="manager-metric-value">
                        {{ number_format((float) $accessoriesToday->quantity, 0) }}
                    </span>
                </div>

                <div class="manager-metric-row">
                    <span class="manager-metric-label">Transactions</span>
                    <span class="manager-metric-value">
                        {{ number_format((int) $accessoriesToday->transactions) }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    <div class="manager-section">
        <h2 class="manager-section-title">
            Monthly Performance
        </h2>

        <div class="card">
            <div class="manager-metric-row">
                <span class="manager-metric-label">LPG Revenue</span>
                <span class="manager-metric-value">
                    ₦{{ number_format((float) $lpgMonth->revenue, 2) }}
                </span>
            </div>

            <div class="manager-metric-row">
                <span class="manager-metric-label">LPG Quantity Sold</span>
                <span class="manager-metric-value">
                    {{ number_format((float) $lpgMonth->quantity, 3) }} kg
                </span>
            </div>

            <div class="manager-metric-row">
                <span class="manager-metric-label">Accessory Revenue</span>
                <span class="manager-metric-value">
                    ₦{{ number_format((float) $accessoriesMonth->revenue, 2) }}
                </span>
            </div>

            <div class="manager-metric-row">
                <span class="manager-metric-label">Combined Revenue</span>
                <span class="manager-metric-value">
                    ₦{{ number_format($monthRevenue, 2) }}
                </span>
            </div>
        </div>
    </div>

    <div class="manager-section">
        <h2 class="manager-section-title">
            Your Assigned Branches
        </h2>

        <div class="card">
            <p style="color:#78877e;font-size:13px;margin-top:0;">
                All dashboard figures are restricted to your
                assigned active branches.
            </p>

            @forelse($branches as $branch)
                <div class="manager-branch">
                    <div class="manager-branch-name">
                        {{ $branch->branch_name }}
                    </div>

                    <div class="manager-branch-code">
                        Branch Code: {{ $branch->branch_code }}
                    </div>
                </div>
            @empty
                <div class="alert-error">
                    No active branch is currently assigned
                    to your account. Please contact the Owner.
                </div>
            @endforelse
        </div>
    </div>

@endsection
