@extends(auth()->user()->hasRole('manager') ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Financial Reports')
@section('header', 'Financial Reports')

@push('styles')
<style>
    .financial-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        align-items: end;
    }

    .financial-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 650;
    }

    .financial-field input,
    .financial-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 13px;
    }

    .financial-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        align-items: center;
    }

    .financial-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: 22px 0;
    }

    .financial-summary-grid.manager-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .financial-stat-label {
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .financial-stat-value {
        font-size: 24px;
        font-weight: 750;
        overflow-wrap: anywhere;
        font-variant-numeric: tabular-nums;
    }

    .financial-stat-note {
        margin-top: 10px;
        font-size: 12px;
        color: var(--muted);
    }

    .financial-section-title {
        margin: 0 0 16px;
        font-size: 17px;
        font-weight: 700;
    }

    .financial-table-wrapper {
        overflow-x: auto;
    }

    .financial-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .financial-table th,
    .financial-table td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        white-space: nowrap;
    }

    .financial-table th {
        color: var(--muted);
        font-weight: 700;
        background: #f8faf9;
    }

    .financial-table .number {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .financial-table tfoot td {
        font-weight: 750;
        background: #f8faf9;
    }

    .financial-category {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 6px;
        background: #e8f4ec;
        color: #11633e;
        font-weight: 650;
    }

    .financial-category.accessory {
        background: #edf1fb;
        color: #345b9a;
    }

    .financial-note {
        font-size: 12px;
        color: var(--muted);
        line-height: 1.7;
    }

    .financial-note strong {
        color: var(--text);
    }

    @media (max-width: 1100px) {
        .financial-filters,
        .financial-summary-grid,
        .financial-summary-grid.manager-summary {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .financial-filters,
        .financial-summary-grid,
        .financial-summary-grid.manager-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Financial Reports</h1>

        <div class="page-description">
            @if($canViewCosts)
                Consolidated financial performance of LPG and accessory sales.
            @else
                Consolidated LPG and accessory sales revenue
                for your authorized branches.
            @endif

            Only posted transactions are included.
        </div>
    </div>

    <div class="financial-actions">
        <a
            href="{{ route('admin.reports.financial.export.csv', [
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'branch_id' => $filters['branch_id'],
            ]) }}"
            class="btn btn-primary"
        >
            Export CSV
        </a>

        <a
            href="{{ route('admin.reports.financial.export.pdf', [
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'branch_id' => $filters['branch_id'],
            ]) }}"
            class="btn btn-primary"
        >
            Export PDF
        </a>
    </div>
</div>

@if($errors->any())
    <div class="alert-error">
        <strong>Please correct the following:</strong>

        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Report Filters --}}
<div class="card">
    <h2 class="financial-section-title">Report Filters</h2>

    <form
        method="GET"
        action="{{ route('admin.reports.financial.index') }}"
        class="financial-filters"
    >
        <div class="financial-field">
            <label for="date_from">From Date</label>

            <input
                type="date"
                id="date_from"
                name="date_from"
                value="{{ $filters['date_from'] ?? '' }}"
            >
        </div>

        <div class="financial-field">
            <label for="date_to">To Date</label>

            <input
                type="date"
                id="date_to"
                name="date_to"
                value="{{ $filters['date_to'] ?? '' }}"
            >
        </div>

        <div class="financial-field">
            <label for="branch_id">Branch</label>

            <select id="branch_id" name="branch_id">
                <option value="">All permitted branches</option>

                @foreach($branches as $branch)
                    <option
                        value="{{ $branch->id }}"
                        @selected(
                            (string) ($filters['branch_id'] ?? '')
                            === (string) $branch->id
                        )
                    >
                        {{ $branch->branch_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="financial-actions">
            <button type="submit" class="btn btn-primary">
                Apply Filters
            </button>

            <a
                href="{{ route('admin.reports.financial.index') }}"
                class="btn btn-secondary"
            >
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Financial Summary --}}
<div class="financial-summary-grid {{ $canViewCosts ? '' : 'manager-summary' }}">

    <div class="card">
        <div class="financial-stat-label">
            Total Revenue
        </div>

        <div class="financial-stat-value">
            ₦{{ number_format($summary['revenue'], 2) }}
        </div>

        <div class="financial-stat-note">
            LPG and accessory sales
        </div>
    </div>

    @if($canViewCosts)
        <div class="card">
            <div class="financial-stat-label">
                Cost of Goods Sold
            </div>

            <div class="financial-stat-value">
                ₦{{ number_format($summary['cogs'], 2) }}
            </div>

            <div class="financial-stat-note">
                Recorded cost of posted sales
            </div>
        </div>

        <div class="card">
            <div class="financial-stat-label">
                Gross Profit
            </div>

            <div class="financial-stat-value">
                ₦{{ number_format($summary['gross_profit'], 2) }}
            </div>

            <div class="financial-stat-note">
                Before operating expenses
            </div>
        </div>

        <div class="card">
            <div class="financial-stat-label">
                Gross Profit Margin
            </div>

            <div class="financial-stat-value">
                {{ number_format($summary['gross_margin'], 2) }}%
            </div>

            <div class="financial-stat-note">
                {{ number_format($summary['transactions']) }}
                posted sales transactions
            </div>
        </div>
    @else
        <div class="card">
            <div class="financial-stat-label">
                Posted Sales Transactions
            </div>

            <div class="financial-stat-value">
                {{ number_format($summary['transactions']) }}
            </div>

            <div class="financial-stat-note">
                Across authorized branches
            </div>
        </div>
    @endif

</div>

{{-- Financial Performance Breakdown --}}
<div class="card" style="margin-bottom: 22px;">
    <h2 class="financial-section-title">
        @if($canViewCosts)
            Financial Performance by Sales Category
        @else
            Revenue by Sales Category
        @endif
    </h2>

    <div class="financial-table-wrapper">
        <table class="financial-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="number">Transactions</th>
                    <th class="number">Revenue (₦)</th>

                    @if($canViewCosts)
                        <th class="number">COGS (₦)</th>
                        <th class="number">Gross Profit (₦)</th>
                        <th class="number">Margin (%)</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @foreach([
                    'LPG Sales' => $lpg,
                    'Accessory Sales' => $accessories
                ] as $category => $data)
                    <tr>
                        <td>
                            <span class="financial-category {{ $category === 'Accessory Sales' ? 'accessory' : '' }}">
                                {{ $category }}
                            </span>
                        </td>

                        <td class="number">
                            {{ number_format($data['transactions']) }}
                        </td>

                        <td class="number">
                            {{ number_format($data['revenue'], 2) }}
                        </td>

                        @if($canViewCosts)
                            <td class="number">
                                {{ number_format($data['cogs'], 2) }}
                            </td>

                            <td class="number">
                                {{ number_format($data['gross_profit'], 2) }}
                            </td>

                            <td class="number">
                                {{ number_format($data['margin'], 2) }}%
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td>Total</td>

                    <td class="number">
                        {{ number_format($summary['transactions']) }}
                    </td>

                    <td class="number">
                        {{ number_format($summary['revenue'], 2) }}
                    </td>

                    @if($canViewCosts)
                        <td class="number">
                            {{ number_format($summary['cogs'], 2) }}
                        </td>

                        <td class="number">
                            {{ number_format($summary['gross_profit'], 2) }}
                        </td>

                        <td class="number">
                            {{ number_format($summary['gross_margin'], 2) }}%
                        </td>
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Financial Reporting Notes --}}
<div class="card">
    <h2 class="financial-section-title">
        Financial Reporting Notes
    </h2>

    <div class="financial-note">
        <p>
            <strong>Revenue:</strong>
            Total recorded revenue from posted LPG and accessory sales
            within the selected reporting period.
        </p>

        @if($canViewCosts)
            <p>
                <strong>Cost of Goods Sold:</strong>
                Recorded inventory costs associated with posted sales.
            </p>

            <p>
                <strong>Gross Profit:</strong>
                Revenue less cost of goods sold, before operating expenses.
            </p>

            <p style="margin-bottom: 0;">
                <strong>Important:</strong>
                Operating expenses and cashbook transactions are not yet
                included. Gross profit must not be interpreted as net profit
                or available cash.
            </p>
        @else
            <p style="margin-bottom: 0;">
                <strong>Important:</strong>
                This report covers posted sales revenue only.
                Operating expenses, collections and cashbook transactions
                are not included in these figures.
            </p>
        @endif
    </div>
</div>

@endsection
