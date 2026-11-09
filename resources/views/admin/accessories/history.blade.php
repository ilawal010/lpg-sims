@extends('layouts.admin')

@section('content')
<style>
    .ah-wrap { max-width: 1400px; margin: 0 auto; }
    .ah-header { display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; margin-bottom: 24px; }
    .ah-title { font-size: 26px; font-weight: 800; color: #143e30; margin: 0 0 6px; }
    .ah-subtitle { color: #718078; font-size: 14px; margin: 0; }
    .ah-actions { display: flex; gap: 10px; flex-wrap: wrap; }
    .ah-btn { display: inline-block; padding: 10px 16px; border-radius: 9px; font-size: 13px; font-weight: 700; text-decoration: none; }
    .ah-btn-primary { background: #11613e; color: #fff; }
    .ah-btn-secondary { background: #fff; color: #285342; border: 1px solid #d9e6dd; }
    .ah-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 22px; }
    .ah-stat { background: #fff; border: 1px solid #e0e9e2; border-radius: 12px; padding: 20px; }
    .ah-stat-label { font-size: 12px; color: #728279; font-weight: 700; margin-bottom: 9px; }
    .ah-stat-value { font-size: 25px; font-weight: 800; color: #174d35; }
    .ah-stat-small { font-size: 15px; }
    .ah-card { background: #fff; border: 1px solid #e0e9e2; border-radius: 12px; overflow: hidden; margin-bottom: 20px; }
    .ah-card-heading { padding: 18px 20px; border-bottom: 1px solid #e8eee9; font-size: 16px; font-weight: 800; color: #173f31; }
    .ah-table-wrap { overflow-x: auto; }
    .ah-table { width: 100%; border-collapse: collapse; min-width: 1050px; }
    .ah-table th { background: #f3f7f4; color: #4d6759; font-size: 12px; font-weight: 800; text-align: left; padding: 14px; white-space: nowrap; }
    .ah-table td { padding: 14px; border-top: 1px solid #edf1ed; font-size: 13px; color: #344a3e; vertical-align: middle; }
    .ah-table tbody tr:hover { background: #fafcfb; }
    .ah-pill { display: inline-block; padding: 5px 9px; border-radius: 20px; background: #e8f6ec; color: #12653d; font-size: 11px; font-weight: 800; }
    .ah-qty { color: #12653d; font-weight: 800; }
    .ah-empty { padding: 45px 20px; text-align: center; color: #7a8980; font-size: 14px; }
    .ah-pagination { padding: 15px 20px; border-top: 1px solid #e8eee9; }
    @media(max-width: 900px) {
        .ah-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media(max-width: 520px) {
        .ah-summary { grid-template-columns: 1fr; }
        .ah-title { font-size: 22px; }
    }
</style>

<div class="ah-wrap">

    <div class="ah-header">
        <div>
            <h1 class="ah-title">Accessory Restocking History</h1>
            <p class="ah-subtitle">
                {{ $accessory->accessory_number }}
                —
                {{ $accessory->name }}
            </p>
        </div>

        <div class="ah-actions">
            <a href="{{ route('admin.accessories.index') }}"
               class="ah-btn ah-btn-secondary">
                Back to Accessories
            </a>

            <a href="{{ route('admin.accessories.edit', $accessory->id) }}"
               class="ah-btn ah-btn-primary">
                Edit / Restock
            </a>
        </div>
    </div>

    <div class="ah-summary">

        <div class="ah-stat">
            <div class="ah-stat-label">Accessory Number</div>
            <div class="ah-stat-value ah-stat-small">
                {{ $accessory->accessory_number }}
            </div>
        </div>

        <div class="ah-stat">
            <div class="ah-stat-label">Current Stock</div>
            <div class="ah-stat-value">
                {{ number_format((float) $accessory->quantity, 3) }}
            </div>
        </div>

        <div class="ah-stat">
            <div class="ah-stat-label">Total Quantity Restocked</div>
            <div class="ah-stat-value">
                {{ number_format((float) $totalRestocked, 3) }}
            </div>
        </div>

        <div class="ah-stat">
            <div class="ah-stat-label">Restocking Transactions</div>
            <div class="ah-stat-value">
                {{ number_format($restockCount) }}
            </div>
        </div>

    </div>

    <div class="ah-card">

        <div class="ah-card-heading">
            Stock Movement Records
        </div>

        <div class="ah-table-wrap">
            <table class="ah-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date / Time</th>
                        <th>Movement</th>
                        <th>Previous Qty</th>
                        <th>Qty Added</th>
                        <th>New Qty</th>
                        <th>Buying Price (₦)</th>
                        <th>Performed By</th>
                        <th>Remarks</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->id }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($movement->created_at)->format('d M Y, h:i A') }}
                            </td>

                            <td>
                                <span class="ah-pill">
                                    {{ ucfirst($movement->movement_type) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format((float) $movement->previous_quantity, 3) }}
                            </td>

                            <td class="ah-qty">
                                +{{ number_format((float) $movement->quantity_added, 3) }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format((float) $movement->new_quantity, 3) }}
                                </strong>
                            </td>

                            <td>
                                {{ $movement->unit_buying_price !== null
                                    ? number_format((float) $movement->unit_buying_price, 2)
                                    : '—' }}
                            </td>

                            <td>
                                {{ $movement->performed_by ?? 'Unknown User' }}
                            </td>

                            <td>
                                {{ $movement->remarks ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="ah-empty">
                                No restocking history has been recorded for this accessory.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="ah-pagination">
                {{ $movements->links() }}
            </div>
        @endif

    </div>

</div>
@endsection
