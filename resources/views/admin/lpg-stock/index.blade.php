@extends('layouts.admin')

@section('title', 'LPG Stock Dashboard')

@section('content')
<style>
.lpg-stock-page { color:#24352e; }
.lpg-stock-header { margin-bottom:22px; }
.lpg-stock-header h1 { font-size:25px; font-weight:800; color:#174d35; margin:0 0 5px; }
.lpg-stock-header p { color:#718078; margin:0; font-size:13px; }
.lpg-stock-cards { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:15px; margin-bottom:22px; }
.lpg-stock-card { background:#fff; border:1px solid #e2ebe5; border-radius:12px; padding:20px; box-shadow:0 3px 12px rgba(0,0,0,.025); }
.lpg-stock-card .label { color:#718078; font-size:12px; font-weight:600; margin-bottom:11px; }
.lpg-stock-card .value { color:#174d35; font-size:23px; font-weight:800; overflow-wrap:anywhere; }
.lpg-stock-card .sub { color:#8b9890; font-size:11px; margin-top:8px; }
.lpg-stock-panel { background:#fff; border:1px solid #e2ebe5; border-radius:12px; margin-bottom:22px; overflow:hidden; }
.lpg-stock-panel-header { padding:17px 20px; border-bottom:1px solid #e8eee9; }
.lpg-stock-panel-header h2 { margin:0; color:#174d35; font-size:16px; font-weight:800; }
.lpg-stock-panel-header p { margin:5px 0 0; color:#849188; font-size:12px; }
.lpg-stock-table-wrap { overflow-x:auto; }
.lpg-stock-table { width:100%; border-collapse:collapse; font-size:12px; }
.lpg-stock-table th { text-align:left; padding:13px 15px; background:#f5f9f6; color:#52685b; font-size:11px; white-space:nowrap; }
.lpg-stock-table td { padding:14px 15px; border-top:1px solid #edf1ee; vertical-align:middle; }
.lpg-stock-table tbody tr:hover { background:#fbfdfb; }
.lpg-stock-strong { color:#174d35; font-weight:750; }
.lpg-stock-muted { color:#819087; }
.lpg-stock-badge { display:inline-block; padding:5px 9px; border-radius:20px; font-size:10px; font-weight:750; }
.lpg-stock-badge.active,.lpg-stock-badge.in { background:#e5f5eb; color:#167245; }
.lpg-stock-badge.inactive,.lpg-stock-badge.out { background:#fff0e6; color:#ae602a; }
.lpg-stock-progress { width:115px; height:7px; background:#e8eee9; border-radius:10px; overflow:hidden; margin-top:7px; }
.lpg-stock-progress span { display:block; height:100%; background:#218455; border-radius:10px; }
.lpg-stock-empty { padding:35px 20px; text-align:center; color:#849188; font-size:13px; }
@media(max-width:1100px) { .lpg-stock-cards { grid-template-columns:repeat(2,minmax(0,1fr)); } }
@media(max-width:600px) { .lpg-stock-cards { grid-template-columns:1fr; } .lpg-stock-header h1 { font-size:21px; } .lpg-stock-card { padding:16px; } }
.lpg-stock-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.lpg-stock-history-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 16px;
    border: 1px solid #176b45;
    border-radius: 9px;
    background: #176b45;
    color: #ffffff;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.lpg-stock-history-btn:hover {
    background: #105235;
    color: #ffffff;
}

@media (max-width: 600px) {
    .lpg-stock-history-btn {
        width: 100%;
    }
}
.lpg-stock-header-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.lpg-stock-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 16px;
    border: 1px solid #176b45;
    border-radius: 9px;
    background: #176b45;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    white-space: nowrap;
}

.lpg-stock-add-btn:hover {
    background: #105235;
    color: #fff;
}

@media (max-width: 600px) {
    .lpg-stock-header-actions,
    .lpg-stock-add-btn {
        width: 100%;
    }
}

.lpg-stock-alert {
    padding: 15px 18px;
    border-radius: 9px;
    margin-bottom: 18px;
    font-size: 12px;
    line-height: 1.7;
    border: 1px solid transparent;
}

.lpg-stock-alert.success {
    background: #e7f5eb;
    color: #176b45;
    border-color: #bee4c9;
}

.lpg-stock-alert.error {
    background: #fff1ef;
    color: #a52b27;
    border-color: #efc9c4;
}

.lpg-stock-alert.info {
    background: #eef6ff;
    color: #285b80;
    border-color: #c9e0f6;
}

.lpg-stock-actions {
    display: flex;
    gap: 7px;
    align-items: center;
    flex-wrap: wrap;
}

.lpg-stock-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 7px;
    padding: 8px 11px;
    font-size: 11px;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
    font-family: inherit;
    white-space: nowrap;
}

.lpg-stock-action.edit {
    background: #e7f5eb;
    color: #176b45;
    border: 1px solid #c7e5d0;
}

.lpg-stock-action.post {
    background: #176b45;
    color: white;
    border: 1px solid #176b45;
}

.lpg-stock-action.cancel {
    background: #fff;
    color: #a52b27;
    border: 1px solid #efc9c4;
}

.lpg-stock-action:disabled {
    opacity: .5;
    cursor: not-allowed;
}

.lpg-stock-capacity-warning {
    display: block;
    margin-top: 5px;
    color: #a52b27;
    font-size: 11px;
    font-weight: 600;
}

</style>

<div class="lpg-stock-page">


    @if(session('success'))
        <div class="lpg-stock-alert success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="lpg-stock-alert error" role="alert">
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="lpg-stock-alert error" role="alert">
            <strong>Unable to complete the operation.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="lpg-stock-header">
        <div>
            <h1>LPG Stock Dashboard</h1>
            <p>Real-time LPG inventory balances, storage utilization and stock movements.</p>
        </div>

        <div class="lpg-stock-header-actions">

        @if(auth()->user()?->hasRole('owner')
            && auth()->user()?->hasPermission('inventory.receive')
            && Route::has('admin.lpg-stock.create'))

            <a href="{{ route('admin.lpg-stock.create') }}" class="lpg-stock-history-btn">+ Add Stock</a>

        @endif

        <a href="{{ route('admin.lpg-stock.movements') }}"
        class="lpg-stock-history-btn">
            View Movement History →
        </a>

    </div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($draftReceipts->isNotEmpty())
    <div class="lpg-stock-panel">
    <div class="lpg-stock-panel-header"><h2>Pending LPG Stock Receipts</h2><p>Post a draft to update stock and register its inventory purchase.</p></div>
    <div class="lpg-stock-table-wrap"><table class="lpg-stock-table"><thead><tr><th>Receipt</th><th>Supplier</th><th>Branch</th><th>Tank</th><th>Quantity</th><th>Cost</th><th>Action</th></tr></thead><tbody>
    @foreach($draftReceipts as $receipt)
    
    <tr>
        <td>{{ $receipt->receipt_number }}</td>

        <td>{{ $receipt->supplier?->supplier_name }}</td>

        <td>{{ $receipt->branch?->branch_name }}</td>

        <td>{{ $receipt->storageUnit?->storage_name }}</td>

        <td>
            {{ number_format($receipt->quantity_kg, 3) }} kg
        </td>

        <td>
            ₦{{ number_format($receipt->total_cost, 2) }}
        </td>

        <td>
            @php
                $balance = $balancesByStorageUnit->get(
                    (int) $receipt->lpg_storage_unit_id
                );

                $availableKg = (float) ($balance['available_kg'] ?? 0);
                $requestedKg = (float) $receipt->quantity_kg;

                $canPost = $balance !== null
                    && $requestedKg > 0
                    && $requestedKg <= $availableKg
                    && $receipt->storageUnit?->status === 'active'
                    && $receipt->branch?->status === 'active'
                    && $receipt->supplier?->status === 'active';
            @endphp

            <div class="lpg-stock-actions">

                {{-- EDIT DRAFT --}}
                <a
                    href="{{ route('admin.lpg-stock.edit', $receipt->id) }}"
                    class="lpg-stock-action edit"
                >
                    Edit Draft
                </a>

                {{-- POST STOCK --}}
                <form
                    method="POST"
                    action="{{ route('admin.lpg-stock.post', $receipt->id) }}"
                    style="margin:0"
                    onsubmit="return confirm('Post this receipt into LPG stock and purchases?');"
                >
                    @csrf

                    <button
                        type="submit"
                        class="lpg-stock-action post"
                        @disabled(!$canPost)
                    >
                        Post Stock
                    </button>
                </form>

                {{-- CANCEL DRAFT --}}
                <form
                    method="POST"
                    action="{{ route('admin.lpg-stock.cancel', $receipt->id) }}"
                    style="margin:0"
                    onsubmit="return confirm('Are you sure you want to cancel this draft receipt?');"
                >
                    @csrf

                    <button
                        type="submit"
                        class="lpg-stock-action cancel"
                    >
                        Cancel Draft
                    </button>
                </form>

            </div>

            {{-- INFORMATION MESSAGE --}}
            @if(!$canPost)
                <span class="lpg-stock-capacity-warning">
                    @if($balance === null)
                        Storage balance information is unavailable.

                    @elseif($requestedKg > $availableKg)
                        Cannot post:
                        {{ number_format($requestedKg, 3) }} kg requested,
                        but only
                        {{ number_format($availableKg, 3) }} kg
                        of storage capacity is available.

                    @else
                        Check the branch, supplier and storage unit
                        before posting.
                    @endif
                </span>
            @endif

        </td>
    </tr>

    @endforeach
    </tbody></table></div>
    </div>
    @endif
    <div class="lpg-stock-cards">
        <div class="lpg-stock-card">
            <div class="label">Available LPG Stock</div>
            <div class="value">{{ number_format($summary['total_stock_kg'], 3) }} kg</div>
            <div class="sub">Across all storage units</div>
        </div>
        <div class="lpg-stock-card">
            <div class="label">Total Storage Capacity</div>
            <div class="value">{{ number_format($summary['total_capacity_kg'], 3) }} kg</div>
            <div class="sub">{{ $summary['storage_units'] }} registered storage unit(s)</div>
        </div>
        <div class="lpg-stock-card">
            <div class="label">Inventory Value</div>
            <div class="value">₦{{ number_format($summary['total_inventory_value'], 2) }}</div>
            <div class="sub">Based on existing weighted-average costing</div>
        </div>
        <div class="lpg-stock-card">
            <div class="label">Capacity Utilization</div>
            <div class="value">{{ number_format($summary['utilization_percent'], 2) }}%</div>
            <div class="sub">Current stock relative to total capacity</div>
        </div>
    </div>

    <div class="lpg-stock-panel">
        <div class="lpg-stock-panel-header">
            <h2>Storage Unit Stock Balances</h2>
            <p>Current LPG quantity, valuation and tank utilization by storage unit.</p>
        </div>
        <div class="lpg-stock-table-wrap">
            <table class="lpg-stock-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Status</th>
                        <th>Available Stock</th>
                        <th>Capacity</th>
                        <th>Utilization</th>
                        <th>Avg. Cost/kg</th>
                        <th>Inventory Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stockRows as $row)
                        <tr>
                            <td>{{ $row['storage_unit']->branch?->branch_name ?? '—' }}</td>
                            <td class="lpg-stock-strong">{{ $row['storage_unit']->storage_name }}</td>
                            <td>
                                <span class="lpg-stock-badge {{ $row['storage_unit']->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($row['storage_unit']->status) }}
                                </span>
                            </td>
                            <td class="lpg-stock-strong">{{ number_format($row['quantity_kg'], 3) }} kg</td>
                            <td>{{ number_format($row['capacity_kg'], 3) }} kg</td>
                            <td>
                                {{ number_format($row['utilization_percent'], 2) }}%
                                <div class="lpg-stock-progress">
                                    <span style="width:{{ min(100, max(0, $row['utilization_percent'])) }}%"></span>
                                </div>
                            </td>
                            <td>₦{{ number_format($row['weighted_average_cost'], 2) }}</td>
                            <td class="lpg-stock-strong">₦{{ number_format($row['inventory_value'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="lpg-stock-empty">No LPG storage units registered.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="lpg-stock-panel">
        <div class="lpg-stock-panel-header">
            <h2>Recent Stock Movements</h2>
            <p>Latest 20 recorded LPG inventory movements.</p>
        </div>
        <div class="lpg-stock-table-wrap">
            <table class="lpg-stock-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Movement Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentMovements as $movement)
                        <tr>
                            <td class="lpg-stock-strong">{{ $movement->movement_reference }}</td>
                            <td>{{ $movement->movement_date ? \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d M Y') : '—' }}</td>
                            <td>{{ $movement->branch?->branch_name ?? '—' }}</td>
                            <td>{{ $movement->storageUnit?->storage_name ?? '—' }}</td>
                            <td>{{ ucwords(str_replace('_', ' ', $movement->movement_type)) }}</td>
                            <td>
                                <span class="lpg-stock-badge {{ $movement->direction === 'in' ? 'in' : 'out' }}">
                                    {{ strtoupper($movement->direction) }}
                                </span>
                            </td>
                            <td>{{ number_format($movement->quantity_kg, 3) }} kg</td>
                            <td>₦{{ number_format($movement->unit_cost ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="lpg-stock-empty">No stock movements recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
