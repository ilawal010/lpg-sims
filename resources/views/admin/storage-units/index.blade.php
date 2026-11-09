@extends('layouts.admin')

@section('title', 'LPG Storage Unit Register')
@section('header', 'LPG Storage Unit Management')

@push('styles')
<style>
    .storage-page {
        color: #263e30;
    }

    .storage-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
    }

    .storage-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .storage-heading p {
        margin: 0;
        color: #748679;
        font-size: 13px;
        line-height: 1.7;
    }

    .storage-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 17px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .storage-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .storage-btn.primary:hover {
        background: #0b5030;
        color: #fff;
    }

    .storage-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #45614e;
    }

    .storage-btn.secondary:hover {
        background: #f1f7f3;
    }

    .storage-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .storage-stat {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 11px;
        padding: 19px;
    }

    .storage-stat-label {
        font-size: 11px;
        font-weight: 700;
        color: #7c8c80;
        margin-bottom: 11px;
    }

    .storage-stat-value {
        font-size: 25px;
        font-weight: 800;
        color: #1c4930;
        line-height: 1.2;
    }

    .storage-stat-note {
        margin-top: 7px;
        font-size: 11px;
        color: #8a998d;
    }

    .storage-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .storage-card-header {
        padding: 17px 20px;
        border-bottom: 1px solid #e6ede8;
        background: #f8fbf9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .storage-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #284735;
    }

    .storage-card-subtitle {
        font-size: 11px;
        color: #819084;
        margin-top: 4px;
    }

    .storage-filters {
        padding: 19px 20px;
        display: grid;
        grid-template-columns: minmax(180px, 2fr)
            repeat(3, minmax(130px, 1fr)) auto;
        gap: 12px;
        align-items: end;
    }

    .storage-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 11px;
        font-weight: 750;
        color: #516757;
    }

    .storage-field input,
    .storage-field select {
        width: 100%;
        min-height: 41px;
        padding: 10px 11px;
        border: 1px solid #dbe5de;
        border-radius: 8px;
        background: #fff;
        color: #314838;
        font-size: 12px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .storage-field input:focus,
    .storage-field select:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .storage-filter-actions {
        display: flex;
        gap: 8px;
    }

    .storage-table-wrap {
        overflow-x: auto;
    }

    .storage-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .storage-table th {
        text-align: left;
        padding: 14px 16px;
        background: #f8faf8;
        border-bottom: 1px solid #e6ede8;
        color: #6a806f;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .storage-table td {
        padding: 16px;
        border-bottom: 1px solid #edf2ee;
        vertical-align: middle;
        color: #405647;
    }

    .storage-table tbody tr:hover {
        background: #fbfdfb;
    }

    .storage-table tbody tr:last-child td {
        border-bottom: none;
    }

    .storage-code {
        font-weight: 800;
        color: #126b42;
        white-space: nowrap;
    }

    .storage-name {
        font-weight: 750;
        color: #254633;
        margin-bottom: 4px;
    }

    .storage-muted {
        font-size: 11px;
        color: #91a095;
    }

    .storage-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .storage-badge.tank {
        background: #eaf2ff;
        color: #315d9a;
    }

    .storage-badge.cylinder {
        background: #f2eaff;
        color: #7145a1;
    }

    .storage-badge.active {
        background: #e5f6eb;
        color: #187347;
    }

    .storage-badge.inactive {
        background: #f0f1f2;
        color: #68747b;
    }

    .storage-badge.maintenance {
        background: #fff2d8;
        color: #a46b16;
    }

    .storage-badge.decommissioned {
        background: #ffe9e9;
        color: #a83e3e;
    }

    .storage-action {
        color: #126b42;
        text-decoration: none;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .storage-action:hover {
        text-decoration: underline;
    }

    .storage-empty {
        padding: 55px 24px;
        text-align: center;
    }

    .storage-empty-icon {
        width: 58px;
        height: 58px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        background: #edf7f0;
        color: #16804b;
        font-size: 25px;
        font-weight: 800;
    }

    .storage-empty h3 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #244732;
    }

    .storage-empty p {
        margin: 0 auto 18px;
        max-width: 420px;
        color: #809184;
        font-size: 12px;
        line-height: 1.8;
    }

    .storage-alert {
        padding: 13px 17px;
        border-radius: 9px;
        margin-bottom: 18px;
        font-size: 12px;
        font-weight: 650;
    }

    .storage-alert.success {
        background: #eaf8ee;
        border: 1px solid #cce9d4;
        color: #176b40;
    }

    .storage-pagination {
        padding: 18px 20px;
        border-top: 1px solid #edf2ee;
    }

    .storage-footer-note {
        padding: 15px 20px;
        border-top: 1px solid #edf2ee;
        background: #fbfdfb;
        color: #829085;
        font-size: 11px;
        line-height: 1.7;
    }

    @media (max-width: 1100px) {
        .storage-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .storage-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .storage-stats,
        .storage-filters {
            grid-template-columns: 1fr;
        }

        .storage-filter-actions {
            flex-wrap: wrap;
        }

        .storage-heading h1 {
            font-size: 20px;
        }
    }
</style>
@endpush

@section('content')

<div class="storage-page">

    <div class="storage-heading">
        <div>
            <h1>LPG Storage Unit Register</h1>
            <p>
                Manage storage tanks and storage cylinders
                across registered operating branches.
            </p>
        </div>

        <a href="{{ route('admin.storage-units.create') }}"
           class="storage-btn primary">
            + Register Storage Unit
        </a>
    </div>

    @if(session('success'))
        <div class="storage-alert success">
            {{ session('success') }}
        </div>
    @endif

    @php
        $totalUnits = \App\Models\LpgStorageUnit::count();

        $totalTanks = \App\Models\LpgStorageUnit::tanks()
            ->count();

        $totalCylinders = \App\Models\LpgStorageUnit::cylinders()
            ->count();

        $activeUnits = \App\Models\LpgStorageUnit::active()
            ->count();
    @endphp

    {{-- SUMMARY CARDS --}}
    <div class="storage-stats">

        <div class="storage-stat">
            <div class="storage-stat-label">
                Total Storage Units
            </div>
            <div class="storage-stat-value">
                {{ number_format($totalUnits) }}
            </div>
            <div class="storage-stat-note">
                Registered across all branches
            </div>
        </div>

        <div class="storage-stat">
            <div class="storage-stat-label">
                Storage Tanks
            </div>
            <div class="storage-stat-value">
                {{ number_format($totalTanks) }}
            </div>
            <div class="storage-stat-note">
                Bulk LPG storage facilities
            </div>
        </div>

        <div class="storage-stat">
            <div class="storage-stat-label">
                Storage Cylinders
            </div>
            <div class="storage-stat-value">
                {{ number_format($totalCylinders) }}
            </div>
            <div class="storage-stat-note">
                Cylinder-based storage units
            </div>
        </div>

        <div class="storage-stat">
            <div class="storage-stat-label">
                Active Storage Units
            </div>
            <div class="storage-stat-value">
                {{ number_format($activeUnits) }}
            </div>
            <div class="storage-stat-note">
                Currently marked operational
            </div>
        </div>

    </div>

    {{-- FILTERS --}}
    <div class="storage-card">

        <div class="storage-card-header">
            <div>
                <div class="storage-card-title">
                    Search & Filter
                </div>
                <div class="storage-card-subtitle">
                    Find storage units by branch, type or status.
                </div>
            </div>
        </div>

        <form method="GET"
              action="{{ route('admin.storage-units.index') }}"
              class="storage-filters">

            <div class="storage-field">
                <label for="search">
                    Search Storage Units
                </label>

                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Code, name, serial number or branch">
            </div>

            <div class="storage-field">
                <label for="branch_id">
                    Branch / Location
                </label>

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

            <div class="storage-field">
                <label for="storage_type">
                    Storage Type
                </label>

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

            <div class="storage-field">
                <label for="status">
                    Operational Status
                </label>

                <select id="status" name="status">
                    <option value="">All Statuses</option>

                    @foreach([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'maintenance' => 'Maintenance',
                        'decommissioned' => 'Decommissioned',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(request('status') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="storage-filter-actions">
                <button type="submit"
                        class="storage-btn primary">
                    Filter
                </button>

                <a href="{{ route('admin.storage-units.index') }}"
                   class="storage-btn secondary">
                    Reset
                </a>
            </div>

        </form>
    </div>

    {{-- STORAGE REGISTER --}}
    <div class="storage-card">

        <div class="storage-card-header">
            <div>
                <div class="storage-card-title">
                    Registered LPG Storage Units
                </div>

                <div class="storage-card-subtitle">
                    {{ number_format($storageUnits->total()) }}
                    matching record(s)
                </div>
            </div>
        </div>

        @if($storageUnits->count() > 0)

            <div class="storage-table-wrap">
                <table class="storage-table">

                    <thead>
                        <tr>
                            <th>Storage Code</th>
                            <th>Storage Unit</th>
                            <th>Branch / Location</th>
                            <th>Type</th>
                            <th>Capacity (kg)</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($storageUnits as $unit)
                            <tr>
                                <td>
                                    <span class="storage-code">
                                        {{ $unit->storage_code }}
                                    </span>
                                </td>

                                <td>
                                    <div class="storage-name">
                                        {{ $unit->storage_name }}
                                    </div>

                                    <div class="storage-muted">
                                        Serial:
                                        {{ $unit->serial_number ?: 'Not specified' }}
                                    </div>
                                </td>

                                <td>
                                    <div class="storage-name">
                                        {{ $unit->branch?->branch_name ?? 'Unknown Branch' }}
                                    </div>

                                    <div class="storage-muted">
                                        {{ $unit->branch?->branch_code ?? '—' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="storage-badge {{ $unit->storage_type }}">
                                        {{ $unit->storage_type === 'tank'
                                            ? 'Storage Tank'
                                            : 'Storage Cylinder' }}
                                    </span>
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format((float) $unit->capacity_kg, 3) }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="storage-badge {{ $unit->status }}">
                                        {{ ucfirst($unit->status) }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        href="{{ route('admin.storage-units.edit', $unit) }}"
                                        class="storage-action">
                                        Edit Unit →
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>

            @if($storageUnits->hasPages())
                <div class="storage-pagination">
                    {{ $storageUnits->links() }}
                </div>
            @endif

        @else

            <div class="storage-empty">
                <div class="storage-empty-icon">
                    LPG
                </div>

                <h3>No Storage Units Found</h3>

                @if(request()->hasAny([
                    'search',
                    'branch_id',
                    'storage_type',
                    'status'
                ]))
                    <p>
                        No registered storage units match
                        the selected search criteria.
                        Try resetting the filters.
                    </p>

                    <a
                        href="{{ route('admin.storage-units.index') }}"
                        class="storage-btn secondary">
                        Reset Filters
                    </a>
                @else
                    <p>
                        Begin by registering an LPG storage tank
                        or storage cylinder under an existing branch.
                    </p>

                    <a
                        href="{{ route('admin.storage-units.create') }}"
                        class="storage-btn primary">
                        + Register First Storage Unit
                    </a>
                @endif
            </div>

        @endif

        <div class="storage-footer-note">
            Storage capacity represents the rated capacity of
            each unit, not its current LPG stock balance.
            Inventory balances will be managed through the
            stock ledger.
        </div>

    </div>

</div>

@endsection
