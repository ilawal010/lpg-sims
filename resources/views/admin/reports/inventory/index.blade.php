@extends('layouts.admin')

@section('content')

<style>
    .inventory-report {
        color: #24372b;
    }

    .inventory-report .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .inventory-report .report-title {
        font-size: 24px;
        font-weight: 700;
        margin: 0;
        color: #176b3a;
    }

    .inventory-report .report-subtitle {
        margin-top: 5px;
        color: #64748b;
        font-size: 13px;
    }

    .inventory-report .report-panel {
        background: #fff;
        border: 1px solid #e2e8e4;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 20px;
    }

    .inventory-report .filter-grid {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
    }

    .inventory-report .filter-field {
        min-width: 220px;
    }

    .inventory-report label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .inventory-report select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5ce;
        border-radius: 8px;
        background: #fff;
    }

    .inventory-report .report-btn {
        display: inline-block;
        padding: 10px 16px;
        border-radius: 8px;
        border: 0;
        background: #176b3a;
        color: #fff;
        text-decoration: none;
        font-weight: 600;
        cursor: pointer;
    }

    .inventory-report .report-btn-secondary {
        background: #eef3ef;
        color: #176b3a;
    }

    .inventory-report .summary-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .inventory-report .summary-card {
        background: #fff;
        border: 1px solid #e2e8e4;
        border-radius: 12px;
        padding: 18px;
    }

    .inventory-report .summary-label {
        font-size: 12px;
        color: #64748b;
        margin-bottom: 8px;
    }

    .inventory-report .summary-value {
        font-size: 22px;
        font-weight: 700;
        color: #176b3a;
        overflow-wrap: anywhere;
    }

    .inventory-report .section-title {
        margin: 0 0 14px;
        font-size: 17px;
        color: #176b3a;
    }

    .inventory-report .table-wrap {
        overflow-x: auto;
    }

    .inventory-report table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .inventory-report th,
    .inventory-report td {
        padding: 11px 10px;
        border-bottom: 1px solid #e8eee9;
        text-align: left;
        vertical-align: middle;
    }

    .inventory-report th {
        background: #f2f7f3;
        color: #28583a;
        font-weight: 700;
        white-space: nowrap;
    }

    .inventory-report .number {
        text-align: right;
        white-space: nowrap;
    }

    .inventory-report .status {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 20px;
        background: #e8f5ec;
        color: #176b3a;
        font-size: 11px;
        font-weight: 600;
    }

    .inventory-report .status-warning {
        background: #fff1d6;
        color: #9a6500;
    }

    .inventory-report .empty {
        padding: 24px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 900px) {
        .inventory-report .summary-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .inventory-report .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

    <div class="inventory-report">

        
    <div class="report-header">
        <div>
            <h1 class="report-title">Inventory Reports</h1>

            
            <div class="report-subtitle">
                Current LPG stock position, storage utilization
                and accessory inventory
                @if($canViewCosts)
                    valuation.
                @else
                    quantities.
                @endif
            </div>

        </div>

        
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">

                <a
                    href="{{ route('admin.reports.inventory.export.csv', request()->only('branch_id')) }}"
                    class="report-btn"
                >
                    Export CSV
                </a>

                <a
                    href="{{ route('admin.reports.inventory.export.pdf', request()->only('branch_id')) }}"
                    class="report-btn"
                >
                    Export PDF
                </a>

            </div>

    </div>


    <div class="report-panel">
        <form method="GET"
              action="{{ route('admin.reports.inventory.index') }}">

            <div class="filter-grid">
                <div class="filter-field">
                    <label for="branch_id">Branch</label>

                    <select name="branch_id" id="branch_id">
                        <option value="">All Authorized Branches</option>

                        @foreach ($branches as $branch)
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

                <button type="submit" class="report-btn">
                    Apply Filter
                </button>

                <a href="{{ route('admin.reports.inventory.index') }}"
                   class="report-btn report-btn-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="summary-grid">

        <div class="summary-card">
            <div class="summary-label">LPG Storage Units</div>
            <div class="summary-value">
                {{ number_format($summary['storage_units']) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Available LPG Stock</div>
            <div class="summary-value">
                {{ number_format($summary['lpg_quantity_kg'], 3) }} kg
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Total LPG Storage Capacity</div>
            <div class="summary-value">
                {{ number_format($summary['lpg_capacity_kg'], 3) }} kg
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Accessory Inventory Items</div>
            <div class="summary-value">
                {{ number_format($summary['accessory_items']) }}
            </div>
        </div>

        
        @if($canViewCosts)
            <div class="summary-card">
                <div class="summary-label">Accessory Stock Value</div>
                <div class="summary-value">
                    NGN {{ number_format($summary['accessory_stock_value'], 2) }}
                </div>
            </div>
        @endif

        <div class="summary-card">
            <div class="summary-label">Low-Stock Accessory Items</div>
            <div class="summary-value">
                {{ number_format($summary['low_stock_items']) }}
            </div>
        </div>

    </div>

    <div class="report-panel">
        <h2 class="section-title">LPG Stock Position</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Storage Code</th>
                        <th>Storage Unit</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th class="number">Capacity (kg)</th>
                        <th class="number">Available (kg)</th>
                        <th class="number">Utilization</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($lpgStock as $item)
                        <tr>
                            <td>{{ $item['storage_code'] }}</td>
                            <td>{{ $item['storage_name'] }}</td>
                            <td>{{ ucfirst($item['storage_type']) }}</td>
                            <td>{{ $item['branch'] ?? '—' }}</td>
                            <td class="number">
                                {{ number_format((float) $item['capacity_kg'], 3) }}
                            </td>
                            <td class="number">
                                {{ number_format((float) $item['balance_kg'], 3) }}
                            </td>
                            <td class="number">
                                {{ number_format($item['utilization'], 2) }}%
                            </td>
                            <td>
                                <span class="status">
                                    {{ ucfirst($item['status']) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">
                                No LPG storage units found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="report-panel">
        <h2 class="section-title">Accessory Inventory</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Accessory No.</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Branch</th>
                        <th class="number">Quantity</th>
                        <th class="number">Reorder Level</th>
                        @if($canViewCosts)
                            <th class="number">Buying Price</th>
                            <th class="number">Stock Value</th>
                        @endif
                        <th>Stock Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($accessoryStock as $item)
                        <tr>
                            <td>{{ $item['accessory_number'] }}</td>
                            <td>{{ $item['name'] }}</td>
                            <td>{{ $item['category'] }}</td>
                            <td>{{ $item['branch'] ?? '—' }}</td>
                            <td class="number">
                                {{ number_format((float) $item['quantity'], 3) }}
                                {{ $item['unit'] }}
                            </td>
                            <td class="number">
                                {{ number_format((float) $item['reorder_level'], 3) }}
                            </td>
                            @if($canViewCosts)
                                <td class="number">
                                    {{ number_format((float) $item['unit_buying_price'], 2) }}
                                </td>
                                <td class="number">
                                    {{ number_format((float) $item['stock_value'], 2) }}
                                </td>
                            @endif
                            <td>
                                @if ($item['low_stock'])
                                    <span class="status status-warning">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="status">
                                        In Stock
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $canViewCosts ? 9 : 7 }}" class="empty">
                                No accessory inventory records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection
