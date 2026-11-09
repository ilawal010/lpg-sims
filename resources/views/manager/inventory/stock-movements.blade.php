@extends('layouts.manager')

@section('title', 'LPG Stock Movements')
@section('header', 'Stock Movements')

@section('content')
<style>
.manager-sm-page {
    color: #24352e;
}

.manager-sm-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.manager-sm-head h1 {
    font-size: 25px;
    font-weight: 800;
    color: #174d35;
    margin: 0 0 6px;
}

.manager-sm-head p {
    color: #718078;
    margin: 0;
    font-size: 13px;
}

.manager-sm-back {
    padding: 11px 16px;
    border: 1px solid #dfe8e1;
    border-radius: 9px;
    background: #fff;
    color: #174d35;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
}

.manager-sm-back:hover {
    background: #edf6ef;
}

.manager-sm-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 22px;
}

.manager-sm-card {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    padding: 19px;
}

.manager-sm-card-label {
    font-size: 12px;
    color: #718078;
    font-weight: 600;
    margin-bottom: 10px;
}

.manager-sm-card-value {
    color: #174d35;
    font-size: 22px;
    font-weight: 800;
    overflow-wrap: anywhere;
}

.manager-sm-card-note {
    color: #8b9890;
    font-size: 11px;
    margin-top: 7px;
}

.manager-sm-panel {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    margin-bottom: 22px;
    overflow: hidden;
}

.manager-sm-panel-head {
    padding: 18px 20px;
    border-bottom: 1px solid #e8eee9;
}

.manager-sm-panel-head h2 {
    margin: 0;
    color: #174d35;
    font-size: 16px;
    font-weight: 800;
}

.manager-sm-panel-head p {
    margin: 5px 0 0;
    color: #849188;
    font-size: 12px;
}

.manager-sm-filters {
    padding: 20px;
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
}

.manager-sm-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #375647;
    margin-bottom: 7px;
}

.manager-sm-field input,
.manager-sm-field select {
    display: block;
    width: 100%;
    min-width: 0;
    padding: 11px 12px;
    border: 1px solid #dce6df;
    border-radius: 8px;
    background: #fff;
    color: #24352e;
    font-size: 12px;
    box-sizing: border-box;
}

.manager-sm-field input:focus,
.manager-sm-field select:focus {
    outline: none;
    border-color: #218455;
    box-shadow: 0 0 0 3px rgba(33, 132, 85, .1);
}

.manager-sm-filter-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    padding: 0 20px 20px;
}

.manager-sm-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 18px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
    text-decoration: none;
}

.manager-sm-btn-primary {
    background: #176b45;
    color: #fff;
    border: 1px solid #176b45;
}

.manager-sm-btn-secondary {
    background: #fff;
    color: #176b45;
    border: 1px solid #dce6df;
}

.manager-sm-table-wrap {
    overflow-x: auto;
}

.manager-sm-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    font-size: 12px;
}

.manager-sm-table th {
    padding: 13px 14px;
    text-align: left;
    background: #f5f9f6;
    color: #52685b;
    font-size: 11px;
    white-space: nowrap;
}

.manager-sm-table td {
    padding: 14px;
    border-top: 1px solid #edf1ee;
    vertical-align: middle;
}

.manager-sm-table tbody tr:hover {
    background: #fbfdfb;
}

.manager-sm-strong {
    color: #174d35;
    font-weight: 750;
}

.manager-sm-muted {
    color: #819087;
}

.manager-sm-ref {
    overflow-wrap: anywhere;
    max-width: 240px;
}

.manager-sm-badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 750;
}

.manager-sm-badge.in {
    background: #e5f5eb;
    color: #167245;
}

.manager-sm-badge.out {
    background: #fff0e6;
    color: #ae602a;
}

.manager-sm-empty {
    padding: 35px 20px !important;
    text-align: center;
    color: #849188;
}

.manager-sm-pagination {
    padding: 15px 20px;
}

@media (max-width: 1100px) {
    .manager-sm-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .manager-sm-filters {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .manager-sm-cards,
    .manager-sm-filters {
        grid-template-columns: 1fr;
    }

    .manager-sm-head h1 {
        font-size: 21px;
    }

    .manager-sm-filter-actions {
        flex-wrap: wrap;
    }
}
</style>

<div class="manager-sm-page">

    <div class="manager-sm-head">
        <div>
            <h1>LPG Stock Movement History</h1>
            <p>
                Trace LPG stock receipts, sales deductions
                and inventory movements within your assigned branches.
            </p>
        </div>

        <a
            href="{{ route('manager.lpg-stock.index') }}"
            class="manager-sm-back"
        >
            ← Back to LPG Stock
        </a>
    </div>

    {{-- Summary cards --}}
    <div class="manager-sm-cards">

        <div class="manager-sm-card">
            <div class="manager-sm-card-label">Total Movements</div>
            <div class="manager-sm-card-value">
                {{ number_format($summary['movement_count']) }}
            </div>
            <div class="manager-sm-card-note">
                Matching the selected filters
            </div>
        </div>

        <div class="manager-sm-card">
            <div class="manager-sm-card-label">Total Stock IN</div>
            <div class="manager-sm-card-value">
                {{ number_format($summary['total_in_kg'], 3) }} kg
            </div>
            <div class="manager-sm-card-note">
                Incoming stock movements
            </div>
        </div>

        <div class="manager-sm-card">
            <div class="manager-sm-card-label">Total Stock OUT</div>
            <div class="manager-sm-card-value">
                {{ number_format($summary['total_out_kg'], 3) }} kg
            </div>
            <div class="manager-sm-card-note">
                Outgoing stock movements
            </div>
        </div>

        <div class="manager-sm-card">
            <div class="manager-sm-card-label">Net Movement</div>
            <div class="manager-sm-card-value">
                {{ number_format($summary['net_movement_kg'], 3) }} kg
            </div>
            <div class="manager-sm-card-note">
                IN minus OUT for selected records
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="manager-sm-panel">

        <div class="manager-sm-panel-head">
            <h2>Filter Stock Movements</h2>
            <p>
                Search and narrow inventory transactions
                using the options below.
            </p>
        </div>

        <form
            method="GET"
            action="{{ route('manager.stock-movements.index') }}"
        >
            <div class="manager-sm-filters">

                <div class="manager-sm-field">
                    <label for="movement-search">Search Reference</label>
                    <input
                        id="movement-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Movement or document reference"
                    >
                </div>

                <div class="manager-sm-field">
                    <label for="movement-branch">Branch</label>
                    <select id="movement-branch" name="branch_id">
                        <option value="">All Assigned Branches</option>

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

                <div class="manager-sm-field">
                    <label for="movement-storage">Storage Unit</label>
                    <select
                        id="movement-storage"
                        name="lpg_storage_unit_id"
                    >
                        <option value="">All Storage Units</option>

                        @foreach($storageUnits as $unit)
                            <option
                                value="{{ $unit->id }}"
                                @selected(
                                    (string) ($filters['lpg_storage_unit_id'] ?? '')
                                    === (string) $unit->id
                                )
                            >
                                {{ $unit->storage_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="manager-sm-field">
                    <label for="movement-type">Movement Type</label>
                    <select id="movement-type" name="movement_type">
                        <option value="">All Types</option>

                        @foreach($movementTypes as $type)
                            <option
                                value="{{ $type }}"
                                @selected(
                                    ($filters['movement_type'] ?? '') === $type
                                )
                            >
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="manager-sm-field">
                    <label for="movement-direction">Direction</label>
                    <select
                        id="movement-direction"
                        name="direction"
                    >
                        <option value="">All Directions</option>

                        <option
                            value="in"
                            @selected(($filters['direction'] ?? '') === 'in')
                        >
                            IN
                        </option>

                        <option
                            value="out"
                            @selected(($filters['direction'] ?? '') === 'out')
                        >
                            OUT
                        </option>
                    </select>
                </div>

                <div class="manager-sm-field">
                    <label for="movement-date-from">Date From</label>
                    <input
                        id="movement-date-from"
                        type="date"
                        name="date_from"
                        value="{{ $filters['date_from'] ?? '' }}"
                    >
                </div>

                <div class="manager-sm-field">
                    <label for="movement-date-to">Date To</label>
                    <input
                        id="movement-date-to"
                        type="date"
                        name="date_to"
                        value="{{ $filters['date_to'] ?? '' }}"
                    >
                </div>

            </div>

            <div class="manager-sm-filter-actions">
                <button
                    type="submit"
                    class="manager-sm-btn manager-sm-btn-primary"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('manager.stock-movements.index') }}"
                    class="manager-sm-btn manager-sm-btn-secondary"
                >
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- Movement register --}}
    <div class="manager-sm-panel">

        <div class="manager-sm-panel-head">
            <h2>Stock Movement Register</h2>
            <p>
                Read-only transaction history from the LPG stock ledger.
            </p>
        </div>

        <div class="manager-sm-table-wrap">
            <table class="manager-sm-table">
                <thead>
                    <tr>
                        <th>Movement Ref.</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                        <th>Document Reference</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="manager-sm-strong">
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
                                {{ ucwords(
                                    str_replace('_', ' ', $movement->movement_type)
                                ) }}
                            </td>

                            <td>
                                <span
                                    class="manager-sm-badge {{ $movement->direction === 'in' ? 'in' : 'out' }}"
                                >
                                    {{ strtoupper($movement->direction) }}
                                </span>
                            </td>

                            <td class="manager-sm-strong">
                                {{ number_format($movement->quantity_kg, 3) }} kg
                            </td>

                            <td class="manager-sm-ref">
                                {{ $movement->document_reference ?: '—' }}
                            </td>

                            <td>
                                {{ $movement->createdBy?->name ?? '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="manager-sm-empty">
                                No stock movements match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="manager-sm-pagination">
                {{ $movements->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
