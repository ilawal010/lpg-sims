@extends('layouts.admin')

@section('title', 'LPG Opening Stock')
@section('header', 'LPG Inventory Management')

@push('styles')
<style>
    .os-page { color: #294331; }
    .os-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }
    .os-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        color: #183d2a;
        font-weight: 750;
    }
    .os-heading p {
        margin: 0;
        font-size: 13px;
        color: #788b7d;
        line-height: 1.7;
    }
    .os-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }
    .os-card-head {
        padding: 17px 20px;
        background: #f8fbf9;
        border-bottom: 1px solid #e2ebe5;
    }
    .os-card-title {
        color: #284735;
        font-size: 14px;
        font-weight: 750;
    }
    .os-card-subtitle {
        color: #819084;
        font-size: 11px;
        margin-top: 5px;
    }
    .os-filters {
        padding: 20px;
        display: grid;
        grid-template-columns: minmax(180px, 2fr) repeat(2, minmax(140px, 1fr)) auto;
        gap: 12px;
        align-items: end;
    }
    .os-field label {
        display: block;
        margin-bottom: 7px;
        color: #526958;
        font-size: 11px;
        font-weight: 750;
    }
    .os-field input,
    .os-field select {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #dbe5de;
        border-radius: 8px;
        background: #fff;
        color: #304b38;
        font-family: inherit;
        font-size: 12px;
        box-sizing: border-box;
    }
    .os-field input:focus,
    .os-field select:focus {
        outline: none;
        border-color: #16804b;
        box-shadow: 0 0 0 3px rgba(22,128,75,.1);
    }
    .os-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }
    .os-btn.primary { background: #11643e; color: #fff; }
    .os-btn.primary:hover { background: #0d5031; color: #fff; }
    .os-btn.secondary {
        background: #fff;
        color: #486451;
        border-color: #d8e4dc;
    }
    .os-actions { display: flex; gap: 8px; }
    .os-table-wrap { overflow-x: auto; }
    .os-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }
    .os-table th {
        padding: 14px 16px;
        text-align: left;
        background: #f8faf8;
        border-bottom: 1px solid #e6ede8;
        color: #718476;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .3px;
        white-space: nowrap;
    }
    .os-table td {
        padding: 16px;
        border-bottom: 1px solid #edf2ee;
        color: #405647;
        vertical-align: middle;
    }
    .os-table tbody tr:hover { background: #fbfdfb; }
    .os-code { color: #126b42; font-weight: 800; }
    .os-name { color: #254633; font-weight: 750; }
    .os-muted {
        color: #91a095;
        font-size: 11px;
        margin-top: 4px;
    }
    .os-badge {
        display: inline-flex;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }
    .os-badge.pending {
        background: #fff2d8;
        color: #98651b;
    }
    .os-badge.recorded {
        background: #e5f6eb;
        color: #187347;
    }
    .os-badge.unavailable {
        background: #f0f1f2;
        color: #68747b;
    }
    .os-link {
        color: #126b42;
        font-size: 11px;
        font-weight: 800;
        text-decoration: none;
        white-space: nowrap;
    }
    .os-link:hover { text-decoration: underline; }
    .os-empty {
        text-align: center;
        padding: 50px 20px;
    }
    .os-empty h3 {
        color: #244732;
        font-size: 16px;
        margin: 0 0 9px;
    }
    .os-empty p {
        color: #809184;
        font-size: 12px;
        line-height: 1.8;
        margin: 0 auto 18px;
        max-width: 440px;
    }
    .os-alert {
        padding: 13px 17px;
        border-radius: 9px;
        margin-bottom: 18px;
        font-size: 12px;
        font-weight: 650;
    }
    .os-alert.success {
        background: #eaf8ee;
        border: 1px solid #cce9d4;
        color: #176b40;
    }
    .os-footer {
        padding: 15px 20px;
        border-top: 1px solid #edf2ee;
        color: #829085;
        background: #fbfdfb;
        font-size: 11px;
        line-height: 1.7;
    }
    .os-pagination { padding: 18px 20px; }

    @media (max-width: 950px) {
        .os-filters { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 600px) {
        .os-filters { grid-template-columns: 1fr; }
        .os-actions { flex-wrap: wrap; }
    }
</style>
@endpush

@section('content')

<div class="os-page">

    <div class="os-heading">
        <div>
            <h1>LPG Opening Stock</h1>
            <p>
                Establish the initial LPG inventory quantity
                for each registered storage tank or cylinder.
            </p>
        </div>

        <a href="{{ route('admin.storage-units.index') }}"
           class="os-btn secondary">
            View Storage Units
        </a>
    </div>

    @if(session('success'))
        <div class="os-alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- FILTERS --}}
    <div class="os-card">
        <div class="os-card-head">
            <div class="os-card-title">Search & Filter</div>
            <div class="os-card-subtitle">
                Filter storage units by branch and storage type.
            </div>
        </div>

        <form method="GET"
              action="{{ route('admin.opening-stock.index') }}"
              class="os-filters">

            <div class="os-field">
                <label for="search">Search Storage Units</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Storage code or name">
            </div>

            <div class="os-field">
                <label for="branch_id">Branch / Location</label>
                <select id="branch_id" name="branch_id">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected((string) request('branch_id') === (string) $branch->id)>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="os-field">
                <label for="storage_type">Storage Type</label>
                <select id="storage_type" name="storage_type">
                    <option value="">All Types</option>
                    <option value="tank"
                        @selected(request('storage_type') === 'tank')>
                        Storage Tank
                    </option>
                    <option value="cylinder"
                        @selected(request('storage_type') === 'cylinder')>
                        Storage Cylinder
                    </option>
                </select>
            </div>

            <div class="os-actions">
                <button type="submit" class="os-btn primary">
                    Filter
                </button>
                <a href="{{ route('admin.opening-stock.index') }}"
                   class="os-btn secondary">
                    Reset
                </a>
            </div>

        </form>
    </div>

    {{-- OPENING STOCK REGISTER --}}
    <div class="os-card">

        <div class="os-card-head">
            <div class="os-card-title">
                Storage Unit Opening Stock Register
            </div>
            <div class="os-card-subtitle">
                {{ number_format($storageUnits->total()) }}
                matching storage unit(s)
            </div>
        </div>

        @if($storageUnits->count())

            <div class="os-table-wrap">
                <table class="os-table">
                    <thead>
                        <tr>
                            <th>Storage Code</th>
                            <th>Storage Unit</th>
                            <th>Branch</th>
                            <th>Rated Capacity</th>
                            <th>Current Stock</th>
                            <th>Opening Stock Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($storageUnits as $unit)
                            @php
                                $hasMovements =
                                    $unit->stock_movements_count > 0;

                                $isOperational =
                                    $unit->status === 'active'
                                    && $unit->branch?->status === 'active';

                                $canPost =
                                    !$hasMovements && $isOperational;

                                $statusLabel = $hasMovements
                                    ? 'Transactions Exist'
                                    : ($isOperational
                                        ? 'Pending Opening Stock'
                                        : 'Unavailable');

                                $statusClass = $hasMovements
                                    ? 'recorded'
                                    : ($isOperational
                                        ? 'pending'
                                        : 'unavailable');
                            @endphp

                            <tr>
                                <td>
                                    <span class="os-code">
                                        {{ $unit->storage_code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="os-name">
                                        {{ $unit->storage_name }}
                                    </div>
                                    <div class="os-muted">
                                        {{ $unit->storage_type === 'tank'
                                            ? 'LPG Storage Tank'
                                            : 'LPG Storage Cylinder' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="os-name">
                                        {{ $unit->branch?->branch_name ?? '—' }}
                                    </div>
                                    <div class="os-muted">
                                        {{ $unit->branch?->branch_code ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    {{ number_format((float) $unit->capacity_kg, 3) }}
                                    kg
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format((float) ($balances[$unit->id] ?? 0), 3) }}
                                    </strong>
                                    kg
                                </td>

                                <td>
                                    <span class="os-badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if($canPost)
                                        <a
                                            href="{{ route('admin.opening-stock.create', $unit) }}"
                                            class="os-link">
                                            Post Opening Stock →
                                        </a>
                                    @else
                                        <span class="os-muted">
                                            Not available
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($storageUnits->hasPages())
                <div class="os-pagination">
                    {{ $storageUnits->links() }}
                </div>
            @endif

        @else

            <div class="os-empty">
                <h3>No Storage Units Found</h3>
                <p>
                    No storage units match your current filters,
                    or no LPG storage units have been registered.
                </p>

                <a href="{{ route('admin.storage-units.index') }}"
                   class="os-btn primary">
                    View Storage Unit Register
                </a>
            </div>

        @endif

        <div class="os-footer">
            Opening stock can be posted only before the first
            inventory transaction for a storage unit. Current stock
            balances are calculated from the LPG stock movement ledger.
        </div>

    </div>

</div>

@endsection
