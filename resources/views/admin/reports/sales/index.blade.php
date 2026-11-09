@extends(auth()->user()->hasRole('manager') ? 'layouts.manager' : 'layouts.admin')

@section('title', 'Sales Reports')
@section('header', 'Sales Reports')

@push('styles')
<style>
    .report-filters {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        align-items: end;
    }

    .report-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 650;
    }

    .report-field input,
    .report-field select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--text);
        font: inherit;
        font-size: 13px;
    }

    .report-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .report-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin: 22px 0;
    }

    .report-summary-grid.manager-summary {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .report-stat-label {
        font-size: 12px;
        color: var(--muted);
        margin-bottom: 10px;
    }

    .report-stat-value {
        font-size: 24px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .report-section-title {
        margin: 0 0 16px;
        font-size: 17px;
        font-weight: 700;
    }

    .report-table-wrapper {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .report-table th,
    .report-table td {
        padding: 12px 10px;
        border-bottom: 1px solid var(--border);
        text-align: left;
        white-space: nowrap;
    }

    .report-table th {
        color: var(--muted);
        font-weight: 700;
        background: #f8faf9;
    }

    .report-table .number {
        text-align: right;
        font-variant-numeric: tabular-nums;
    }

    .report-table tfoot td {
        font-weight: 750;
    }

    .report-type {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 6px;
        background: #e8f4ec;
        color: #11633e;
        font-weight: 650;
    }

    .report-type.accessory {
        background: #edf1fb;
        color: #345b9a;
    }

    .report-note {
        margin-top: 12px;
        font-size: 12px;
        color: var(--muted);
    }

    @media (max-width: 1100px) {
        .report-filters,
        .report-summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .report-filters,
        .report-summary-grid,
        .report-summary-grid.manager-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Sales Reports</h1>

        <div class="page-description">
            Consolidated LPG and accessory sales performance.
            Only posted transactions are included.

            @unless($canViewCosts)
                Showing authorized branch sales and revenue information.
            @endunless
        </div>
    </div>

    <div class="report-actions">
        <a
            href="{{ route('admin.reports.sales.export.csv', request()->only(['date_from', 'date_to', 'branch_id'])) }}"
            class="btn btn-primary"
        >
            Export CSV
        </a>

        <a
            href="{{ route('admin.reports.sales.export.pdf', request()->only(['date_from', 'date_to', 'branch_id'])) }}"
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

<div class="card">
    <h2 class="report-section-title">Report Filters</h2>

    <form
        method="GET"
        action="{{ route('admin.reports.sales.index') }}"
        class="report-filters"
    >
        <div class="report-field">
            <label for="date_from">From Date</label>

            <input
                type="date"
                id="date_from"
                name="date_from"
                value="{{ $filters['date_from'] ?? '' }}"
            >
        </div>

        <div class="report-field">
            <label for="date_to">To Date</label>

            <input
                type="date"
                id="date_to"
                name="date_to"
                value="{{ $filters['date_to'] ?? '' }}"
            >
        </div>

        <div class="report-field">
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

        <div class="report-actions">
            <button type="submit" class="btn btn-primary">
                Apply Filters
            </button>

            <a
                href="{{ route('admin.reports.sales.index') }}"
                class="btn btn-secondary"
            >
                Reset
            </a>
        </div>
    </form>
</div>

<div class="report-summary-grid {{ $canViewCosts ? '' : 'manager-summary' }}">

    <div class="card">
        <div class="report-stat-label">Posted Transactions</div>

        <div class="report-stat-value">
            {{ number_format($summary['transactions']) }}
        </div>
    </div>

    <div class="card">
        <div class="report-stat-label">Total Revenue</div>

        <div class="report-stat-value">
            ₦{{ number_format($summary['revenue'], 2) }}
        </div>
    </div>

    @if($canViewCosts)
        <div class="card">
            <div class="report-stat-label">Cost of Goods Sold</div>

            <div class="report-stat-value">
                ₦{{ number_format($summary['cogs'], 2) }}
            </div>
        </div>

        <div class="card">
            <div class="report-stat-label">Gross Profit</div>

            <div class="report-stat-value">
                ₦{{ number_format($summary['gross_profit'], 2) }}
            </div>

            <div class="report-note">
                Gross margin:
                {{ number_format($summary['gross_margin'], 2) }}%
            </div>
        </div>
    @endif
</div>

<div class="card" style="margin-bottom: 22px;">
    <h2 class="report-section-title">Sales by Product Category</h2>

    <div class="report-table-wrapper">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th class="number">Transactions</th>
                    <th class="number">Quantity</th>
                    <th class="number">Revenue (₦)</th>

                    @if($canViewCosts)
                        <th class="number">COGS (₦)</th>
                        <th class="number">Gross Profit (₦)</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                <tr>
                    <td>LPG</td>

                    <td class="number">
                        {{ number_format($lpgSummary['transactions']) }}
                    </td>

                    <td class="number">
                        {{ number_format($lpgSummary['quantity'], 3) }} kg
                    </td>

                    <td class="number">
                        {{ number_format($lpgSummary['revenue'], 2) }}
                    </td>

                    @if($canViewCosts)
                        <td class="number">
                            {{ number_format($lpgSummary['cogs'], 2) }}
                        </td>

                        <td class="number">
                            {{ number_format($lpgSummary['gross_profit'], 2) }}
                        </td>
                    @endif
                </tr>

                <tr>
                    <td>Accessories</td>

                    <td class="number">
                        {{ number_format($accessorySummary['transactions']) }}
                    </td>

                    <td class="number">
                        {{ number_format($accessorySummary['quantity'], 3) }} units
                    </td>

                    <td class="number">
                        {{ number_format($accessorySummary['revenue'], 2) }}
                    </td>

                    @if($canViewCosts)
                        <td class="number">
                            {{ number_format($accessorySummary['cogs'], 2) }}
                        </td>

                        <td class="number">
                            {{ number_format($accessorySummary['gross_profit'], 2) }}
                        </td>
                    @endif
                </tr>
            </tbody>

            <tfoot>
                <tr>
                    <td>Total</td>

                    <td class="number">
                        {{ number_format($summary['transactions']) }}
                    </td>

                    <td class="number">—</td>

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
                    @endif
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card">
    <h2 class="report-section-title">Recent Sales Transactions</h2>

    <div class="report-table-wrapper">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Sale Number</th>
                    <th>Type</th>
                    <th>Branch</th>
                    <th>Sales Agent</th>
                    <th class="number">Quantity</th>
                    <th class="number">Revenue (₦)</th>

                    @if($canViewCosts)
                        <th class="number">COGS (₦)</th>
                        <th class="number">Gross Profit (₦)</th>
                    @endif
                </tr>
            </thead>

            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>
                            {{ $sale['sale_date']?->format('d M Y') ?? '—' }}
                        </td>

                        <td>{{ $sale['sale_number'] }}</td>

                        <td>
                            <span class="report-type {{ $sale['type'] === 'Accessory' ? 'accessory' : '' }}">
                                {{ $sale['type'] }}
                            </span>
                        </td>

                        <td>{{ $sale['branch'] ?? '—' }}</td>
                        <td>{{ $sale['attendant'] ?? '—' }}</td>

                        <td class="number">
                            {{ number_format($sale['quantity'], 3) }}
                            {{ $sale['unit'] }}
                        </td>

                        <td class="number">
                            {{ number_format($sale['revenue'], 2) }}
                        </td>

                        @if($canViewCosts)
                            <td class="number">
                                {{ number_format($sale['cogs'], 2) }}
                            </td>

                            <td class="number">
                                {{ number_format($sale['gross_profit'], 2) }}
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td
                            colspan="{{ $canViewCosts ? 9 : 7 }}"
                            style="text-align:center; padding:30px;"
                        >
                            No posted sales found for the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="report-note">
        Showing up to 25 recent transactions.
        Revenue summaries include all posted transactions
        matching the selected filters.
    </p>
</div>

@endsection
