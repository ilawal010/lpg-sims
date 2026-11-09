@extends('layouts.manager')

@section('title', 'LPG Stock')
@section('header', 'LPG Stock')

@section('content')
<style>
.manager-stock-page {
    color: #24352e;
}

.manager-stock-header {
    margin-bottom: 22px;
}

.manager-stock-header h1 {
    font-size: 25px;
    font-weight: 800;
    color: #174d35;
    margin: 0 0 6px;
}

.manager-stock-header p {
    margin: 0;
    color: #718078;
    font-size: 13px;
    line-height: 1.6;
}

.manager-stock-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 22px;
}

.manager-stock-card {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 3px 12px rgba(0,0,0,.025);
}

.manager-stock-card .label {
    color: #718078;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 11px;
}

.manager-stock-card .value {
    color: #174d35;
    font-size: 23px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.manager-stock-card .sub {
    color: #8b9890;
    font-size: 11px;
    margin-top: 8px;
}

.manager-stock-panel {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    margin-bottom: 22px;
    overflow: hidden;
}

.manager-stock-panel-header {
    padding: 17px 20px;
    border-bottom: 1px solid #e8eee9;
}

.manager-stock-panel-header h2 {
    margin: 0;
    color: #174d35;
    font-size: 16px;
    font-weight: 800;
}

.manager-stock-panel-header p {
    margin: 5px 0 0;
    color: #849188;
    font-size: 12px;
}

.manager-stock-table-wrap {
    overflow-x: auto;
}

.manager-stock-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.manager-stock-table th {
    text-align: left;
    padding: 13px 15px;
    background: #f5f9f6;
    color: #52685b;
    font-size: 11px;
    white-space: nowrap;
}

.manager-stock-table td {
    padding: 14px 15px;
    border-top: 1px solid #edf1ee;
    vertical-align: middle;
}

.manager-stock-table tbody tr:hover {
    background: #fbfdfb;
}

.manager-stock-strong {
    color: #174d35;
    font-weight: 750;
}

.manager-stock-muted {
    color: #819087;
}

.manager-stock-badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 750;
}

.manager-stock-badge.active,
.manager-stock-badge.in {
    background: #e5f5eb;
    color: #167245;
}

.manager-stock-badge.inactive,
.manager-stock-badge.out {
    background: #fff0e6;
    color: #ae602a;
}

.manager-stock-progress {
    width: 115px;
    height: 7px;
    background: #e8eee9;
    border-radius: 10px;
    overflow: hidden;
    margin-top: 7px;
}

.manager-stock-progress span {
    display: block;
    height: 100%;
    background: #218455;
    border-radius: 10px;
}

.manager-stock-empty {
    padding: 35px 20px;
    text-align: center;
    color: #849188;
    font-size: 13px;
}

@media (max-width: 1100px) {
    .manager-stock-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .manager-stock-cards {
        grid-template-columns: 1fr;
    }

    .manager-stock-header h1 {
        font-size: 21px;
    }

    .manager-stock-card {
        padding: 16px;
    }
}
</style>

<div class="manager-stock-page">

    <div class="manager-stock-header">
        <h1>LPG Stock Dashboard</h1>
        <p>
            Current LPG inventory balances, storage capacity
            and stock movements across your assigned branches.
        </p>
    </div>

    {{-- Inventory summary --}}
    <div class="manager-stock-cards">

        <div class="manager-stock-card">
            <div class="label">Available LPG Stock</div>
            <div class="value">
                {{ number_format($summary['total_stock_kg'], 3) }} kg
            </div>
            <div class="sub">
                Across authorized storage units
            </div>
        </div>

        <div class="manager-stock-card">
            <div class="label">Total Storage Capacity</div>
            <div class="value">
                {{ number_format($summary['total_capacity_kg'], 3) }} kg
            </div>
            <div class="sub">
                {{ number_format($summary['storage_units']) }}
                registered storage unit(s)
            </div>
        </div>

        <div class="manager-stock-card">
            <div class="label">Capacity Utilization</div>
            <div class="value">
                {{ number_format($summary['utilization_percent'], 2) }}%
            </div>
            <div class="sub">
                Current stock relative to storage capacity
            </div>
        </div>

        <div class="manager-stock-card">
            <div class="label">Available Storage Space</div>
            <div class="value">
                {{ number_format(
                    $summary['total_capacity_kg']
                    - $summary['total_stock_kg'],
                    3
                ) }} kg
            </div>
            <div class="sub">
                Calculated remaining storage capacity
            </div>
        </div>

    </div>

    {{-- Storage unit balances --}}
    <div class="manager-stock-panel">

        <div class="manager-stock-panel-header">
            <h2>Storage Unit Stock Balances</h2>
            <p>
                Current LPG quantities and utilization
                for authorized storage units.
            </p>
        </div>

        <div class="manager-stock-table-wrap">
            <table class="manager-stock-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Status</th>
                        <th>Available Stock</th>
                        <th>Capacity</th>
                        <th>Utilization</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($stockRows as $row)
                        <tr>
                            <td>
                                {{ $row['storage_unit']->branch?->branch_name ?? '—' }}
                            </td>

                            <td class="manager-stock-strong">
                                {{ $row['storage_unit']->storage_name }}
                            </td>

                            <td>
                                <span class="manager-stock-badge {{ $row['storage_unit']->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($row['storage_unit']->status) }}
                                </span>
                            </td>

                            <td class="manager-stock-strong">
                                {{ number_format($row['quantity_kg'], 3) }} kg
                            </td>

                            <td>
                                {{ number_format($row['capacity_kg'], 3) }} kg
                            </td>

                            <td>
                                {{ number_format($row['utilization_percent'], 2) }}%

                                <div class="manager-stock-progress">
                                    <span
                                        style="width:{{ min(100, max(0, $row['utilization_percent'])) }}%">
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="manager-stock-empty">
                                No LPG storage units found
                                in your assigned active branches.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent stock movements --}}
    <div class="manager-stock-panel">

        <div class="manager-stock-panel-header">
            <h2>Recent Stock Movements</h2>
            <p>
                Latest 20 recorded movements
                within your assigned branches.
            </p>
        </div>

        <div class="manager-stock-table-wrap">
            <table class="manager-stock-table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Movement Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentMovements as $movement)
                        <tr>
                            <td class="manager-stock-strong">
                                {{ $movement->movement_reference }}
                            </td>

                            <td>
                                {{ $movement->movement_date
                                    ? \Illuminate\Support\Carbon::parse(
                                        $movement->movement_date
                                    )->format('d M Y')
                                    : '—' }}
                            </td>

                            <td>
                                {{ $movement->branch?->branch_name ?? '—' }}
                            </td>

                            <td>
                                {{ $movement->storageUnit?->storage_name ?? '—' }}
                            </td>

                            <td>
                                {{ ucwords(str_replace(
                                    '_',
                                    ' ',
                                    $movement->movement_type
                                )) }}
                            </td>

                            <td>
                                <span class="manager-stock-badge {{ $movement->direction === 'in' ? 'in' : 'out' }}">
                                    {{ strtoupper($movement->direction) }}
                                </span>
                            </td>

                            <td class="manager-stock-strong">
                                {{ number_format($movement->quantity_kg, 3) }} kg
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="manager-stock-empty">
                                No LPG stock movements recorded
                                for your assigned branches.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
