@extends('layouts.manager')

@section('title', 'Sales History')
@section('header', 'Sales History')

@section('content')

<style>
    .history-page {
        --history-green: #176b45;
        --history-border: #e2e8e5;
        --history-muted: #64748b;
        color: #172b23;
    }

    .history-page * {
        box-sizing: border-box;
    }

    .history-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }

    .history-heading h1 {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .history-subtitle {
        color: var(--history-muted);
        font-size: 13px;
        line-height: 1.6;
        margin: 0;
    }

    .history-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .history-stat,
    .history-panel {
        background: #fff;
        border: 1px solid var(--history-border);
        border-radius: 12px;
    }

    .history-stat {
        padding: 19px;
    }

    .history-stat-label {
        color: var(--history-muted);
        font-size: 12px;
        margin-bottom: 10px;
    }

    .history-stat-value {
        font-size: clamp(19px, 2.4vw, 27px);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .history-stat-note {
        margin-top: 8px;
        color: var(--history-muted);
        font-size: 11px;
    }

    .history-panel {
        overflow: hidden;
    }

    .history-panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--history-border);
    }

    .history-panel-header h2 {
        font-size: 16px;
        font-weight: 750;
        margin: 0 0 6px;
    }

    .history-filters {
        display: flex;
        align-items: flex-end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 20px;
    }

    .history-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 140px;
    }

    .history-field.search {
        flex: 1;
        min-width: 200px;
    }

    .history-field label {
        font-size: 12px;
        font-weight: 700;
    }

    .history-input {
        width: 100%;
        min-height: 41px;
        padding: 9px 11px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        color: #172b23;
        font: inherit;
        font-size: 13px;
    }

    .history-input:focus {
        outline: none;
        border-color: var(--history-green);
        box-shadow: 0 0 0 3px rgba(23,107,69,.1);
    }

    .history-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 41px;
        padding: 10px 15px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .history-btn-primary {
        background: var(--history-green);
        color: #fff;
    }

    .history-btn-secondary {
        background: #fff;
        border-color: var(--history-border);
        color: #334155;
    }

    .history-table-wrap {
        overflow-x: auto;
    }

    .history-table {
        width: 100%;
        min-width: 1050px;
        border-collapse: collapse;
    }

    .history-table th {
        padding: 13px 15px;
        background: #f8faf9;
        border-bottom: 1px solid var(--history-border);
        color: var(--history-muted);
        font-size: 11px;
        font-weight: 750;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .history-table td {
        padding: 15px;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
        vertical-align: middle;
    }

    .history-table tbody tr:last-child td {
        border-bottom: none;
    }

    .history-reference {
        font-weight: 750;
    }

    .history-secondary {
        margin-top: 5px;
        color: var(--history-muted);
        font-size: 11px;
    }

    .history-number {
        white-space: nowrap;
        font-variant-numeric: tabular-nums;
    }

    .history-type {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 750;
    }

    .history-type.lpg {
        background: #e0f2fe;
        color: #075985;
    }

    .history-type.accessory {
        background: #f3e8ff;
        color: #6b21a8;
    }

    .history-posted {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        background: #dcfce7;
        color: #166534;
        font-size: 11px;
        font-weight: 750;
    }

    .history-empty {
        padding: 50px 20px;
        text-align: center;
    }

    .history-empty h3 {
        font-size: 17px;
        margin: 0 0 8px;
    }

    .history-empty p {
        color: var(--history-muted);
        font-size: 13px;
    }

    .history-footer {
        padding: 16px 20px;
        border-top: 1px solid var(--history-border);
    }

    @media (max-width: 1100px) {
        .history-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .history-stats {
            grid-template-columns: 1fr;
        }

        .history-heading h1 {
            font-size: 21px;
        }
    }
</style>

<div class="history-page">

    <div class="history-heading">
        <div>
            <h1>Sales History</h1>
            <p class="history-subtitle">
                Posted LPG and Accessories sales across your
                assigned branches.
            </p>
        </div>

        <a href="{{ route('admin.lpg-pos.index') }}"
           class="history-btn history-btn-primary">
            Point of Sale
        </a>
    </div>

    <div class="history-stats">

        <div class="history-stat">
            <div class="history-stat-label">
                Posted Transactions
            </div>
            <div class="history-stat-value">
                {{ number_format($totalTransactions) }}
            </div>
            <div class="history-stat-note">
                Completed sales matching your filters
            </div>
        </div>

        <div class="history-stat">
            <div class="history-stat-label">
                Total Sales Revenue
            </div>
            <div class="history-stat-value">
                ₦{{ number_format((float) $totalRevenue, 2) }}
            </div>
            <div class="history-stat-note">
                LPG and Accessories combined
            </div>
        </div>

        <div class="history-stat">
            <div class="history-stat-label">
                LPG Quantity Sold
            </div>
            <div class="history-stat-value">
                {{ number_format((float) $totalLpgQuantity, 3) }}
                <small style="font-size:13px;">kg</small>
            </div>
            <div class="history-stat-note">
                Posted LPG sales
            </div>
        </div>

        <div class="history-stat">
            <div class="history-stat-label">
                Accessories Sold
            </div>
            <div class="history-stat-value">
                {{ number_format((float) $totalAccessoryQuantity, 3) }}
            </div>
            <div class="history-stat-note">
                Posted accessory quantities
            </div>
        </div>

    </div>

    <div class="history-panel">

        <div class="history-panel-header">
            <h2>Sales Transaction Register</h2>

            <p class="history-subtitle">
                Search completed sales by reference, transaction
                type, branch or date.
            </p>

            <form method="GET"
                  action="{{ route('manager.sales.index') }}"
                  class="history-filters">

                <div class="history-field search">
                    <label for="history-search">
                        Transaction Number
                    </label>
                    <input
                        id="history-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Search sale reference"
                        class="history-input"
                    >
                </div>

                <div class="history-field">
                    <label for="history-type">
                        Sale Type
                    </label>
                    <select
                        id="history-type"
                        name="sale_type"
                        class="history-input"
                    >
                        <option value="">All Sales</option>
                        <option value="lpg"
                            @selected(($filters['sale_type'] ?? '') === 'lpg')>
                            LPG
                        </option>
                        <option value="accessory"
                            @selected(($filters['sale_type'] ?? '') === 'accessory')>
                            Accessories
                        </option>
                    </select>
                </div>

                <div class="history-field">
                    <label for="history-branch">
                        Branch
                    </label>
                    <select
                        id="history-branch"
                        name="branch_id"
                        class="history-input"
                    >
                        <option value="">Assigned Branches</option>

                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected(
                                    (string) ($filters['branch_id'] ?? '')
                                    === (string) $branch->id
                                )>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="history-field">
                    <label for="history-from">
                        From Date
                    </label>
                    <input
                        id="history-from"
                        type="date"
                        name="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                        class="history-input"
                    >
                </div>

                <div class="history-field">
                    <label for="history-to">
                        To Date
                    </label>
                    <input
                        id="history-to"
                        type="date"
                        name="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                        class="history-input"
                    >
                </div>

                <button type="submit"
                        class="history-btn history-btn-primary">
                    Apply Filters
                </button>

                <a href="{{ route('manager.sales.index') }}"
                   class="history-btn history-btn-secondary">
                    Reset
                </a>

            </form>
        </div>

        <div class="history-table-wrap">
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Sale / Date</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th>Sales Agent</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Revenue</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($sales as $sale)
                        <tr>
                            <td>
                                <div class="history-reference">
                                    {{ $sale->sale_number }}
                                </div>
                                <div class="history-secondary">
                                    {{ \Illuminate\Support\Carbon::parse(
                                        $sale->sale_date
                                    )->format('d M Y') }}
                                </div>
                            </td>

                            <td>
                                <span class="history-type {{ $sale->sale_type }}">
                                    {{ $sale->sale_type === 'lpg'
                                        ? 'LPG'
                                        : 'Accessories' }}
                                </span>
                            </td>

                            <td>
                                {{ $sale->branch_name }}
                            </td>

                            <td>
                                {{ $sale->attendant_name ?? '—' }}
                            </td>

                            <td class="history-number">
                                {{ number_format((float) $sale->quantity, 3) }}
                                {{ $sale->sale_type === 'lpg' ? 'kg' : '' }}
                            </td>

                            <td class="history-number">
                                ₦{{ number_format((float) $sale->unit_price, 2) }}
                            </td>

                            <td class="history-number">
                                ₦{{ number_format((float) $sale->total_amount, 2) }}
                            </td>

                            <td>
                                <span class="history-posted">
                                    Posted
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="history-empty">
                                    <h3>No sales transactions found</h3>
                                    <p>
                                        No posted LPG or Accessories sales
                                        match your selected filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($sales->hasPages())
            <div class="history-footer">
                {{ $sales->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
