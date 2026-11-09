@extends('layouts.admin')

@section('title', 'LPG Sales Register')
@section('header', 'LPG Sales Management')

@section('content')
<style>
    .sales-page {
        --sales-green: #176b45;
        --sales-border: #e2e8e5;
        --sales-muted: #64748b;
        color: #172b23;
    }

    .sales-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
    }

    .sales-header h1 {
        font-size: 24px;
        font-weight: 750;
        margin: 0 0 5px;
    }

    .sales-subtitle {
        font-size: 13px;
        color: var(--sales-muted);
        margin: 0;
    }

    .sales-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .sales-stat {
        background: #fff;
        border: 1px solid var(--sales-border);
        border-radius: 12px;
        padding: 18px;
    }

    .sales-stat-label {
        color: var(--sales-muted);
        font-size: 12px;
        margin-bottom: 9px;
    }

    .sales-stat-value {
        font-size: 25px;
        font-weight: 750;
        line-height: 1.25;
    }

    .sales-stat-note {
        color: var(--sales-muted);
        font-size: 11px;
        margin-top: 7px;
    }

    .sales-panel {
        background: #fff;
        border: 1px solid var(--sales-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .sales-panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--sales-border);
    }

    .sales-panel-header h2 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .sales-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 12px;
        margin-top: 18px;
    }

    .sales-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 145px;
    }

    .sales-field.search {
        flex: 1;
        min-width: 210px;
    }

    .sales-field label {
        font-size: 12px;
        font-weight: 650;
    }

    .sales-input {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
        color: #172b23;
    }

    .sales-input:focus {
        outline: none;
        border-color: var(--sales-green);
        box-shadow: 0 0 0 3px rgba(23,107,69,.1);
    }

    .sales-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 10px 15px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
        min-height: 40px;
    }

    .sales-btn-primary {
        background: var(--sales-green);
        color: #fff;
    }

    .sales-btn-primary:hover {
        background: #105435;
        color: #fff;
    }

    .sales-btn-secondary {
        background: #fff;
        color: #334155;
        border-color: var(--sales-border);
    }

    .sales-table-wrap {
        overflow-x: auto;
    }

    .sales-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 1120px;
    }

    .sales-table th {
        text-align: left;
        padding: 13px 15px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--sales-muted);
        background: #f8faf9;
        border-bottom: 1px solid var(--sales-border);
        white-space: nowrap;
    }

    .sales-table td {
        padding: 15px;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
        vertical-align: middle;
    }

    .sales-table tbody tr:last-child td {
        border-bottom: none;
    }

    .sales-reference {
        font-weight: 700;
        color: #172b23;
    }

    .sales-secondary {
        color: var(--sales-muted);
        font-size: 11px;
        margin-top: 4px;
    }

    .sales-number {
        font-variant-numeric: tabular-nums;
        white-space: nowrap;
    }

    .sales-status {
        display: inline-flex;
        align-items: center;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .sales-status.posted {
        background: #dcfce7;
        color: #166534;
    }

    .sales-status.draft {
        background: #fef3c7;
        color: #92400e;
    }

    .sales-status.cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .sales-profit-positive {
        color: #176b45;
        font-weight: 700;
    }

    .sales-profit-negative {
        color: #b91c1c;
        font-weight: 700;
    }

    .sales-empty {
        padding: 55px 20px;
        text-align: center;
    }

    .sales-empty h3 {
        font-size: 17px;
        margin: 0 0 8px;
    }

    .sales-empty p {
        color: var(--sales-muted);
        font-size: 13px;
    }

    .sales-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--sales-border);
    }

    @media (max-width: 1100px) {
        .sales-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .sales-stats {
            grid-template-columns: 1fr;
        }

        .sales-header h1 {
            font-size: 21px;
        }

        .sales-stat-value {
            font-size: 23px;
        }
    }
        .sales-receipt-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border: 1px solid #176b45;
        border-radius: 7px;
        background: #f0f8f3;
        color: #176b45;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
    }

    .sales-receipt-link:hover {
        background: #176b45;
        color: #fff;
    }
</style>

<div class="sales-page">

    <div class="sales-header">
        <div>
            <h1>LPG Sales Register</h1>
            <p class="sales-subtitle">
                Monitor LPG sales transactions, revenue,
                stock costs and gross profitability.
            </p>
        </div>

        {{-- POS creation link will be activated when its route is developed. --}}
    </div>

    <div class="sales-stats">

        <div class="sales-stat">
            <div class="sales-stat-label">Total Transactions</div>
            <div class="sales-stat-value">
                {{ number_format($totalSales) }}
            </div>
            <div class="sales-stat-note">
                {{ number_format($postedSales) }} posted,
                {{ number_format($draftSales) }} draft,
                {{ number_format($cancelledSales) }} cancelled
            </div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Posted Sales Revenue</div>
            <div class="sales-stat-value">
                ₦{{ number_format((float) $totalRevenue, 2) }}
            </div>
            <div class="sales-stat-note">
                Completed LPG sales only
            </div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Cost of Goods Sold</div>
            <div class="sales-stat-value">
                ₦{{ number_format((float) $totalCogs, 2) }}
            </div>
            <div class="sales-stat-note">
                Weighted-average inventory cost
            </div>
        </div>

        <div class="sales-stat">
            <div class="sales-stat-label">Gross Profit</div>
            <div class="sales-stat-value"
                 style="color: {{ (float) $totalGrossProfit < 0 ? '#b91c1c' : '#176b45' }};">
                ₦{{ number_format((float) $totalGrossProfit, 2) }}
            </div>
            <div class="sales-stat-note">
                Revenue less cost of goods sold
            </div>
        </div>

    </div>

    <div class="sales-panel">

        <div class="sales-panel-header">

            <h2>Sales Transaction History</h2>

            <p class="sales-subtitle">
                Search and filter recorded LPG sales.
                Financial totals reflect the selected filters.
            </p>

            <form method="GET"
                  action="{{ route('admin.lpg-sales.index') }}"
                  class="sales-filters">

                <div class="sales-field search">
                    <label for="sales-search">Search Transaction</label>
                    <input
                        id="sales-search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Sale number or payment reference"
                        class="sales-input"
                    >
                </div>

                <div class="sales-field">
                    <label for="sales-status">Status</label>
                    <select
                        id="sales-status"
                        name="status"
                        class="sales-input"
                    >
                        <option value="">All Statuses</option>
                        @foreach(['draft', 'posted', 'cancelled'] as $status)
                            <option value="{{ $status }}"
                                @selected(request('status') === $status)>
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sales-field">
                    <label for="sales-branch">Branch</label>
                    <select
                        id="sales-branch"
                        name="branch_id"
                        class="sales-input"
                    >
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected((string) request('branch_id') === (string) $branch->id)>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sales-field">
                    <label for="sales-date-from">From Date</label>
                    <input
                        id="sales-date-from"
                        type="date"
                        name="date_from"
                        value="{{ request('date_from') }}"
                        class="sales-input"
                    >
                </div>

                <div class="sales-field">
                    <label for="sales-date-to">To Date</label>
                    <input
                        id="sales-date-to"
                        type="date"
                        name="date_to"
                        value="{{ request('date_to') }}"
                        class="sales-input"
                    >
                </div>

                <button type="submit"
                        class="sales-btn sales-btn-primary">
                    Apply Filters
                </button>

                <a href="{{ route('admin.lpg-sales.index') }}"
                   class="sales-btn sales-btn-secondary">
                    Reset
                </a>

            </form>

        </div>

        <div class="sales-table-wrap">
            <table class="sales-table">
                <thead>
                    <tr>
                        <th>Sale / Date</th>
                        <th>Branch / Storage</th>
                        <th>Attendant</th>
                        <th>Quantity</th>
                        <th>Selling Price</th>
                        <th>Revenue</th>
                        <th>COGS</th>
                        <th>Gross Profit</th>
                        <th>Payment</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <div class="sales-reference">
                                    {{ $sale->sale_number }}
                                </div>
                                <div class="sales-secondary">
                                    {{ $sale->sale_date?->format('d M Y') ?? '—' }}
                                </div>
                            </td>

                            <td>
                                <div>
                                    {{ $sale->branch?->branch_name ?? '—' }}
                                </div>
                                <div class="sales-secondary">
                                    {{ $sale->storageUnit?->storage_name ?? '—' }}
                                </div>
                            </td>

                            <td>
                                {{ $sale->attendant?->name ?? '—' }}
                            </td>

                            <td class="sales-number">
                                {{ number_format((float) $sale->quantity_kg, 3) }}
                                kg
                            </td>

                            <td class="sales-number">
                                ₦{{ number_format((float) $sale->selling_price_per_kg, 2) }}
                            </td>

                            <td class="sales-number">
                                ₦{{ number_format((float) $sale->total_amount, 2) }}
                            </td>

                            <td class="sales-number">
                                @if($sale->status === 'posted')
                                    ₦{{ number_format((float) $sale->cost_of_goods_sold, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="sales-number">
                                @if($sale->status === 'posted')
                                    <span class="{{ (float) $sale->gross_profit < 0
                                        ? 'sales-profit-negative'
                                        : 'sales-profit-positive' }}">
                                        ₦{{ number_format((float) $sale->gross_profit, 2) }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>

                            <td>
                                {{ ucfirst(str_replace('_', ' ', $sale->payment_method ?? '—')) }}
                                @if($sale->payment_reference)
                                    <div class="sales-secondary">
                                        {{ $sale->payment_reference }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="sales-status {{ $sale->status }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <th>Receipt</th>
                            <td>
                                @if($sale->status === \App\Models\LpgSale::STATUS_POSTED)
                                    <a href="{{ route('admin.lpg-sales.receipt', $sale->id) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="sales-receipt-link">
                                        Print Receipt
                                    </a>
                                @else
                                    <span style="color:#94a3b8;">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="sales-empty">
                                    <h3>No sales transactions found</h3>
                                    <p>
                                        No LPG sales match the selected filters,
                                        or no transactions have been recorded.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="sales-footer">
                {{ $sales->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
