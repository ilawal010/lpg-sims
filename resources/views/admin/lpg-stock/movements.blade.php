@extends('layouts.admin')

@section('title', 'LPG Stock Movement History')

@section('content')
<style>
    .sm-page { color:#24352e; }
    .sm-head {
        display:flex; justify-content:space-between;
        align-items:center; gap:15px; flex-wrap:wrap;
        margin-bottom:22px;
    }
    .sm-head h1 {
        font-size:25px; font-weight:800;
        color:#174d35; margin:0 0 6px;
    }
    .sm-head p { color:#718078; margin:0; font-size:13px; }
    .sm-back {
        padding:11px 16px; border:1px solid #dfe8e1;
        border-radius:9px; background:#fff;
        color:#174d35; text-decoration:none;
        font-size:12px; font-weight:700;
    }
    .sm-back:hover { background:#edf6ef; }
    .sm-cards {
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:15px; margin-bottom:22px;
    }
    .sm-card {
        background:#fff; border:1px solid #e2ebe5;
        border-radius:12px; padding:19px;
    }
    .sm-card-label {
        font-size:12px; color:#718078;
        font-weight:600; margin-bottom:10px;
    }
    .sm-card-value {
        color:#174d35; font-size:22px;
        font-weight:800; overflow-wrap:anywhere;
    }
    .sm-card-note {
        color:#8b9890; font-size:11px; margin-top:7px;
    }
    .sm-panel {
        background:#fff; border:1px solid #e2ebe5;
        border-radius:12px; margin-bottom:22px;
        overflow:hidden;
    }
    .sm-panel-head {
        padding:18px 20px;
        border-bottom:1px solid #e8eee9;
    }
    .sm-panel-head h2 {
        margin:0; color:#174d35;
        font-size:16px; font-weight:800;
    }
    .sm-panel-head p {
        margin:5px 0 0; color:#849188; font-size:12px;
    }
    .sm-filters {
        padding:20px;
        display:grid;
        grid-template-columns:repeat(4,minmax(0,1fr));
        gap:15px;
    }
    .sm-field label {
        display:block; font-size:12px;
        font-weight:700; color:#375647;
        margin-bottom:7px;
    }
    .sm-field input,.sm-field select {
        display:block; width:100%; min-width:0;
        padding:11px 12px;
        border:1px solid #dce6df;
        border-radius:8px;
        background:#fff; color:#24352e;
        font-size:12px; box-sizing:border-box;
    }
    .sm-field input:focus,.sm-field select:focus {
        outline:none; border-color:#218455;
        box-shadow:0 0 0 3px rgba(33,132,85,.1);
    }
    .sm-filter-actions {
        display:flex; gap:10px; align-items:center;
        padding:0 20px 20px;
    }
    .sm-btn {
        display:inline-flex; align-items:center;
        justify-content:center; padding:11px 18px;
        border-radius:8px; font-size:12px;
        font-weight:700; cursor:pointer;
        text-decoration:none;
    }
    .sm-btn-primary {
        background:#176b45; color:#fff;
        border:1px solid #176b45;
    }
    .sm-btn-secondary {
        background:#fff; color:#176b45;
        border:1px solid #dce6df;
    }
    .sm-table-wrap { overflow-x:auto; }
    .sm-table {
        width:100%; border-collapse:collapse;
        font-size:12px;
    }
    .sm-table th {
        padding:13px 14px; text-align:left;
        background:#f5f9f6; color:#52685b;
        font-size:11px; white-space:nowrap;
    }
    .sm-table td {
        padding:14px;
        border-top:1px solid #edf1ee;
        vertical-align:middle;
    }
    .sm-table tbody tr:hover { background:#fbfdfb; }
    .sm-strong { color:#174d35; font-weight:750; }
    .sm-muted { color:#819087; }
    .sm-ref { overflow-wrap:anywhere; max-width:240px; }
    .sm-badge {
        display:inline-block; padding:5px 10px;
        border-radius:20px; font-size:10px;
        font-weight:750;
    }
    .sm-badge.in { background:#e5f5eb; color:#167245; }
    .sm-badge.out { background:#fff0e6; color:#ae602a; }
    .sm-empty {
        padding:35px 20px!important;
        text-align:center; color:#849188;
    }
    .sm-pagination { padding:15px 20px; }
    @media(max-width:1100px) {
        .sm-cards { grid-template-columns:repeat(2,minmax(0,1fr)); }
        .sm-filters { grid-template-columns:repeat(2,minmax(0,1fr)); }
    }
    @media(max-width:600px) {
        .sm-cards,.sm-filters { grid-template-columns:1fr; }
        .sm-head h1 { font-size:21px; }
        .sm-filter-actions { flex-wrap:wrap; }
    }

        .sm-view-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 12px;
        border: 1px solid #176b45;
        border-radius: 7px;
        background: #f0f8f3;
        color: #176b45;
        font-size: 11px;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background .2s, color .2s;
    }

    .sm-view-btn:hover {
        background: #176b45;
        color: #ffffff;
    }
</style>

<div class="sm-page">

    <div class="sm-head">
        <div>
            <h1>LPG Stock Movement History</h1>
            <p>Trace LPG stock receipts, sales deductions and inventory movements.</p>
        </div>
        <a href="{{ route('admin.lpg-stock.index') }}" class="sm-back">
            ← Back to LPG Stock
        </a>
    </div>

    <div class="sm-cards">
        <div class="sm-card">
            <div class="sm-card-label">Total Movements</div>
            <div class="sm-card-value">
                {{ number_format($summary['movement_count']) }}
            </div>
            <div class="sm-card-note">Matching the selected filters</div>
        </div>

        <div class="sm-card">
            <div class="sm-card-label">Total Stock IN</div>
            <div class="sm-card-value">
                {{ number_format($summary['total_in_kg'], 3) }} kg
            </div>
            <div class="sm-card-note">Incoming stock movements</div>
        </div>

        <div class="sm-card">
            <div class="sm-card-label">Total Stock OUT</div>
            <div class="sm-card-value">
                {{ number_format($summary['total_out_kg'], 3) }} kg
            </div>
            <div class="sm-card-note">Outgoing stock movements</div>
        </div>

        <div class="sm-card">
            <div class="sm-card-label">Net Movement</div>
            <div class="sm-card-value">
                {{ number_format($summary['net_movement_kg'], 3) }} kg
            </div>
            <div class="sm-card-note">IN minus OUT for selected records</div>
        </div>
    </div>

    <div class="sm-panel">
        <div class="sm-panel-head">
            <h2>Filter Stock Movements</h2>
            <p>Search and narrow inventory transactions using the options below.</p>
        </div>

        <form method="GET" action="{{ route('admin.lpg-stock.movements') }}">
            <div class="sm-filters">
                <div class="sm-field">
                    <label>Search Reference</label>
                    <input type="text" name="search"
                           value="{{ $filters['search'] ?? '' }}"
                           placeholder="Movement or document reference">
                </div>

                <div class="sm-field">
                    <label>Branch</label>
                    <select name="branch_id">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected(($filters['branch_id'] ?? '') == $branch->id)>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm-field">
                    <label>Storage Unit</label>
                    <select name="lpg_storage_unit_id">
                        <option value="">All Storage Units</option>
                        @foreach($storageUnits as $unit)
                            <option value="{{ $unit->id }}"
                                @selected(($filters['lpg_storage_unit_id'] ?? '') == $unit->id)>
                                {{ $unit->storage_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm-field">
                    <label>Movement Type</label>
                    <select name="movement_type">
                        <option value="">All Types</option>
                        @foreach($movementTypes as $type)
                            <option value="{{ $type }}"
                                @selected(($filters['movement_type'] ?? '') === $type)>
                                {{ ucwords(str_replace('_', ' ', $type)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm-field">
                    <label>Direction</label>
                    <select name="direction">
                        <option value="">All Directions</option>
                        <option value="in" @selected(($filters['direction'] ?? '') === 'in')>
                            IN
                        </option>
                        <option value="out" @selected(($filters['direction'] ?? '') === 'out')>
                            OUT
                        </option>
                    </select>
                </div>

                <div class="sm-field">
                    <label>Date From</label>
                    <input type="date" name="date_from"
                           value="{{ $filters['date_from'] ?? '' }}">
                </div>

                <div class="sm-field">
                    <label>Date To</label>
                    <input type="date" name="date_to"
                           value="{{ $filters['date_to'] ?? '' }}">
                </div>
            </div>

            <div class="sm-filter-actions">
                <button type="submit" class="sm-btn sm-btn-primary">
                    Apply Filters
                </button>
                <a href="{{ route('admin.lpg-stock.movements') }}"
                   class="sm-btn sm-btn-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="sm-panel">
        <div class="sm-panel-head">
            <h2>Stock Movement Register</h2>
            <p>Read-only transaction history from the existing LPG stock ledger.</p>
        </div>

        <div class="sm-table-wrap">
            <table class="sm-table">
                <thead>
                    <tr>
                        <th>Movement Ref.</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Storage Unit</th>
                        <th>Type</th>
                        <th>Direction</th>
                        <th>Quantity</th>
                        <th>Unit Cost</th>
                        <th>Total Cost</th>
                        <th>Document Reference</th>
                        <th>Recorded By</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr>
                            <td class="sm-strong">
                                {{ $movement->movement_reference }}
                            </td>
                            <td>
                                {{ $movement->movement_date
                                    ? \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d M Y')
                                    : '—' }}
                            </td>
                            <td>{{ $movement->branch?->branch_name ?? '—' }}</td>
                            <td>{{ $movement->storageUnit?->storage_name ?? '—' }}</td>
                            <td>
                                {{ ucwords(str_replace('_', ' ', $movement->movement_type)) }}
                            </td>
                            <td>
                                <span class="sm-badge {{ $movement->direction === 'in' ? 'in' : 'out' }}">
                                    {{ strtoupper($movement->direction) }}
                                </span>
                            </td>
                            <td class="sm-strong">
                                {{ number_format($movement->quantity_kg, 3) }} kg
                            </td>
                            <td>₦{{ number_format($movement->unit_cost ?? 0, 2) }}</td>
                            <td>₦{{ number_format($movement->total_cost ?? 0, 2) }}</td>
                            <td class="sm-ref">
                                {{ $movement->document_reference ?: '—' }}
                            </td>
                            <td>{{ $movement->createdBy?->name ?? '—' }}</td>
                            <td>
                                <a
                                    href="{{ route('admin.lpg-stock.movements.show', $movement->id) }}"
                                    class="sm-view-btn"
                                    title="View stock movement details"
                                >
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="sm-empty">
                                No stock movements match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($movements->hasPages())
            <div class="sm-pagination">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
