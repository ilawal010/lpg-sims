@extends('layouts.agent')

@section('title', 'My Sales')
@section('header', 'My Sales')

@push('styles')
<style>
    .sales-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 24px;
    }

    .sales-heading h2 {
        font-size: 22px;
        margin: 0 0 6px;
        color: #20352b;
    }

    .sales-heading p {
        color: #78877e;
        font-size: 13px;
        margin: 0;
    }

    .sales-card {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        margin-bottom: 24px;
        overflow: hidden;
    }

    .sales-card-header {
        padding: 19px 22px;
        border-bottom: 1px solid #e3ebe6;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .sales-card-header h3 {
        font-size: 16px;
        margin: 0;
        color: #20352b;
    }

    .sales-count {
        background: #e8f5ed;
        color: #11633e;
        font-size: 12px;
        font-weight: 700;
        padding: 6px 11px;
        border-radius: 20px;
    }

    .sales-table-wrap {
        overflow-x: auto;
    }

    .sales-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        white-space: nowrap;
    }

    .sales-table th {
        background: #f8faf9;
        color: #65766c;
        text-align: left;
        padding: 14px 17px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .sales-table td {
        padding: 15px 17px;
        border-top: 1px solid #edf1ee;
    }

    .sales-table tbody tr:hover {
        background: #fbfdfb;
    }

    .sales-amount {
        font-weight: 700;
        color: #11633e;
    }

    .sales-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        background: #e8f5ed;
        color: #11633e;
        font-size: 11px;
        font-weight: 700;
    }

    .receipt-link {
        color: #11633e;
        font-weight: 650;
    }

    .receipt-link:hover {
        text-decoration: underline;
    }

    .sales-empty {
        padding: 40px 20px;
        text-align: center;
        color: #78877e;
        font-size: 13px;
    }

    .sales-pagination {
        padding: 15px 20px;
        border-top: 1px solid #edf1ee;
    }

    .sales-pagination nav {
        max-width: 100%;
    }

    @media(max-width: 600px) {
        .sales-card-header {
            padding: 16px;
        }

        .sales-table th,
        .sales-table td {
            padding: 12px;
        }
    }
</style>
@endpush

@section('content')

<div class="sales-heading">
    <div>
        <h2>My Sales History</h2>
        <p>View your completed LPG and accessory sales transactions.</p>
    </div>

    <a href="{{ route('admin.lpg-pos.index') }}" class="btn btn-primary">
        Point of Sale
    </a>
</div>

{{-- LPG SALES --}}
<section class="sales-card">
    <div class="sales-card-header">
        <h3>LPG Sales</h3>
        <span class="sales-count">
            {{ number_format($lpgSales->total()) }} Transactions
        </span>
    </div>

    <div class="sales-table-wrap">
        <table class="sales-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lpgSales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>
                            {{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}
                        </td>
                        <td>{{ $sale->branch?->name ?? '—' }}</td>
                        <td class="sales-amount">
                            ₦{{ number_format((float) $sale->total_amount, 2) }}
                        </td>
                        <td>
                            <span class="sales-status">Posted</span>
                        </td>
                        <td>
                            <a class="receipt-link"
                               href="{{ route('admin.lpg-sales.receipt', $sale->id) }}"
                               target="_blank"
                               rel="noopener">
                                View / Print
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="sales-empty">
                            No completed LPG sales found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($lpgSales->hasPages())
        <div class="sales-pagination">
            {{ $lpgSales->withQueryString()->links() }}
        </div>
    @endif
</section>

{{-- ACCESSORY SALES --}}
<section class="sales-card">
    <div class="sales-card-header">
        <h3>Accessory Sales</h3>
        <span class="sales-count">
            {{ number_format($accessorySales->total()) }} Transactions
        </span>
    </div>

    <div class="sales-table-wrap">
        <table class="sales-table">
            <thead>
                <tr>
                    <th>Sale ID</th>
                    <th>Date</th>
                    <th>Accessory</th>
                    <th>Branch</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Receipt</th>
                </tr>
            </thead>
            <tbody>
                @forelse($accessorySales as $sale)
                    <tr>
                        <td>#{{ $sale->id }}</td>
                        <td>
                            {{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}
                        </td>
                        <td>{{ $sale->accessory?->name ?? '—' }}</td>
                        <td>{{ $sale->branch?->name ?? '—' }}</td>
                        <td class="sales-amount">
                            ₦{{ number_format((float) $sale->total_amount, 2) }}
                        </td>
                        <td>
                            <span class="sales-status">Posted</span>
                        </td>
                        <td>
                            <a class="receipt-link"
                               href="{{ route('admin.accessory-sales.receipt', $sale->id) }}"
                               target="_blank"
                               rel="noopener">
                                View / Print
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="sales-empty">
                            No completed accessory sales found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($accessorySales->hasPages())
        <div class="sales-pagination">
            {{ $accessorySales->withQueryString()->links() }}
        </div>
    @endif
</section>

@endsection
