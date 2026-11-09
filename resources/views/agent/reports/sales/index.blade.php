@extends('layouts.agent')

@section('title', 'My Sales Report')
@section('header', 'My Sales Report')

@push('styles')
<style>
    .report-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .report-heading h2 {
        font-size: 22px;
        margin: 0 0 6px;
    }

    .report-heading p {
        color: #78877e;
        font-size: 13px;
        margin: 0;
    }

    .report-panel {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 22px;
    }

    .report-panel h3 {
        margin: 0 0 18px;
        font-size: 16px;
    }

    .filter-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 14px;
    }

    .filter-field {
        flex: 1;
        min-width: 180px;
    }

    .filter-field label {
        display: block;
        font-size: 12px;
        font-weight: 650;
        margin-bottom: 7px;
    }

    .filter-field input {
        width: 100%;
        border: 1px solid #dbe5de;
        border-radius: 8px;
        padding: 11px 12px;
        font: inherit;
        font-size: 13px;
    }

    .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .summary-card {
        background: white;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 20px;
    }

    .summary-label {
        color: #78877e;
        font-size: 12px;
        margin-bottom: 11px;
    }

    .summary-value {
        color: #11633e;
        font-size: 22px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .report-table-wrap {
        overflow-x: auto;
    }

    .report-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .report-table th {
        background: #f8faf9;
        color: #65766c;
        text-align: left;
        padding: 12px;
        font-size: 11px;
        text-transform: uppercase;
    }

    .report-table td {
        padding: 13px 12px;
        border-top: 1px solid #edf1ee;
    }

    .report-amount {
        color: #11633e;
        font-weight: 700;
    }

    .report-empty {
        color: #78877e;
        text-align: center;
        padding: 25px;
    }

    .report-meta {
        font-size: 12px;
        color: #78877e;
        margin-bottom: 20px;
    }

    .report-error {
        color: #a52a2a;
        font-size: 12px;
        margin-top: 7px;
    }

    @media(max-width: 900px) {
        .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media(max-width: 500px) {
        .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="report-heading">
    <div>
        <h2>Individual Sales Report</h2>
        <p>Generate your LPG and accessory sales report for a selected period.</p>
    </div>

    @if(Route::has('agent.reports.sales.pdf'))
        <a href="{{ route('agent.reports.sales.pdf', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ]) }}" class="btn btn-primary" target="_blank" rel="noopener">
            Download / Print PDF
        </a>
    @endif
</div>

<div class="report-panel">
    <h3>Select Reporting Period</h3>

    <form method="GET"
          action="{{ route('agent.reports.sales.index') }}"
          class="filter-form">

        <div class="filter-field">
            <label for="date_from">Start Date</label>
            <input type="date"
                   id="date_from"
                   name="date_from"
                   value="{{ old('date_from', $dateFrom) }}"
                   required>

            @error('date_from')
                <div class="report-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="filter-field">
            <label for="date_to">End Date</label>
            <input type="date"
                   id="date_to"
                   name="date_to"
                   value="{{ old('date_to', $dateTo) }}"
                   required>

            @error('date_to')
                <div class="report-error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">
            Generate Report
        </button>
    </form>
</div>

<div class="report-meta">
    <strong>Sales Agent:</strong> {{ auth()->user()->name }}
    &nbsp; | &nbsp;
    <strong>Period:</strong>
    {{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y') }}
    to
    {{ \Illuminate\Support\Carbon::parse($dateTo)->format('d M Y') }}
</div>

<div class="summary-grid">
    <div class="summary-card">
        <div class="summary-label">LPG Sales Revenue</div>
        <div class="summary-value">
            ₦{{ number_format($lpgRevenue, 2) }}
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-label">Accessory Sales Revenue</div>
        <div class="summary-value">
            ₦{{ number_format($accessoryRevenue, 2) }}
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-label">Total Sales Revenue</div>
        <div class="summary-value">
            ₦{{ number_format($totalRevenue, 2) }}
        </div>
    </div>

    <div class="summary-card">
        <div class="summary-label">Total Transactions</div>
        <div class="summary-value">
            {{ number_format($totalTransactions) }}
        </div>
    </div>
</div>

<div class="report-panel">
    <h3>LPG Sales ({{ $lpgSales->count() }})</h3>

    <div class="report-table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lpgSales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                        <td>{{ $sale->branch?->name ?? '—' }}</td>
                        <td class="report-amount">
                            {{ number_format((float) $sale->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="report-empty">
                            No LPG sales within the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="report-panel">
    <h3>Accessory Sales ({{ $accessorySales->count() }})</h3>

    <div class="report-table-wrap">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Accessory</th>
                    <th>Branch</th>
                    <th>Amount (₦)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accessorySales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                        <td>{{ $sale->accessory?->name ?? '—' }}</td>
                        <td>{{ $sale->branch?->name ?? '—' }}</td>
                        <td class="report-amount">
                            {{ number_format((float) $sale->total_amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="report-empty">
                            No accessory sales within the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
