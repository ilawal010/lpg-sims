@extends('layouts.manager')

@section('title', 'LPG Storage Units')
@section('header', 'Storage Units')

@section('content')
<style>
.manager-storage-page {
    color: #24352e;
}

.manager-storage-heading {
    margin-bottom: 22px;
}

.manager-storage-heading h1 {
    margin: 0 0 6px;
    font-size: 25px;
    font-weight: 800;
    color: #174d35;
}

.manager-storage-heading p {
    margin: 0;
    color: #718078;
    font-size: 13px;
    line-height: 1.6;
}

.manager-storage-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 22px;
}

.manager-storage-card {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    padding: 20px;
}

.manager-storage-card .label {
    color: #718078;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 11px;
}

.manager-storage-card .value {
    color: #174d35;
    font-size: 25px;
    font-weight: 800;
}

.manager-storage-card .sub {
    color: #8b9890;
    font-size: 11px;
    margin-top: 8px;
}

.manager-storage-panel {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 22px;
}

.manager-storage-panel-header {
    padding: 20px;
    border-bottom: 1px solid #e8eee9;
}

.manager-storage-panel-header h2 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 800;
    color: #174d35;
}

.manager-storage-panel-header p {
    margin: 0;
    color: #849188;
    font-size: 12px;
}

.manager-storage-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
    margin-top: 18px;
}

.manager-storage-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 155px;
}

.manager-storage-field.search {
    flex: 1;
    min-width: 220px;
}

.manager-storage-field label {
    font-size: 12px;
    font-weight: 700;
}

.manager-storage-input {
    width: 100%;
    min-height: 41px;
    padding: 9px 11px;
    border: 1px solid #d5dfd9;
    border-radius: 8px;
    background: #fff;
    color: #172b23;
    font-size: 13px;
}

.manager-storage-input:focus {
    outline: none;
    border-color: #176b45;
    box-shadow: 0 0 0 3px rgba(23, 107, 69, .1);
}

.manager-storage-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 41px;
    padding: 10px 15px;
    border-radius: 8px;
    border: 1px solid transparent;
    font-size: 12px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
}

.manager-storage-btn-primary {
    background: #176b45;
    color: #fff;
}

.manager-storage-btn-secondary {
    background: #fff;
    color: #334155;
    border-color: #d5dfd9;
}

.manager-storage-table-wrap {
    overflow-x: auto;
}

.manager-storage-table {
    width: 100%;
    min-width: 1100px;
    border-collapse: collapse;
    font-size: 12px;
}

.manager-storage-table th {
    padding: 13px 15px;
    text-align: left;
    background: #f5f9f6;
    color: #52685b;
    font-size: 11px;
    white-space: nowrap;
}

.manager-storage-table td {
    padding: 15px;
    border-top: 1px solid #edf1ee;
    vertical-align: middle;
}

.manager-storage-table tbody tr:hover {
    background: #fbfdfb;
}

.manager-storage-strong {
    color: #174d35;
    font-weight: 750;
}

.manager-storage-muted {
    color: #819087;
    font-size: 11px;
}

.manager-storage-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 750;
    white-space: nowrap;
}

.manager-storage-badge.active {
    background: #e5f5eb;
    color: #167245;
}

.manager-storage-badge.inactive {
    background: #f1f5f9;
    color: #64748b;
}

.manager-storage-badge.maintenance {
    background: #fff4d6;
    color: #986300;
}

.manager-storage-badge.decommissioned {
    background: #fee8e8;
    color: #b42318;
}

.manager-storage-progress {
    width: 110px;
    height: 7px;
    border-radius: 10px;
    background: #e7eee9;
    overflow: hidden;
    margin-top: 7px;
}

.manager-storage-progress-fill {
    height: 100%;
    background: #198754;
    border-radius: 10px;
}

.manager-storage-empty {
    padding: 45px 20px;
    text-align: center;
    color: #849188;
    font-size: 13px;
}

@media (max-width: 1100px) {
    .manager-storage-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .manager-storage-cards {
        grid-template-columns: 1fr;
    }

    .manager-storage-heading h1 {
        font-size: 21px;
    }
}
</style>

<div class="manager-storage-page">

    <div class="manager-storage-heading">
        <h1>LPG Storage Units</h1>
        <p>
            Monitor storage capacity, available LPG stock
            and operational status across your assigned branches.
        </p>
    </div>

    {{-- Summary --}}
    <div class="manager-storage-cards">

        <div class="manager-storage-card">
            <div class="label">Registered Storage Units</div>
            <div class="value">
                {{ number_format($summary['total_units']) }}
            </div>
            <div class="sub">Within selected authorized branches</div>
        </div>

        <div class="manager-storage-card">
            <div class="label">Active Storage Units</div>
            <div class="value">
                {{ number_format($summary['active_units']) }}
            </div>
            <div class="sub">Currently marked active</div>
        </div>

        <div class="manager-storage-card">
            <div class="label">Total Storage Capacity</div>
            <div class="value">
                {{ number_format($summary['total_capacity_kg'], 2) }}
                <small style="font-size: 12px;">kg</small>
            </div>
            <div class="sub">Combined registered capacity</div>
        </div>

        <div class="manager-storage-card">
            <div class="label">Available LPG Stock</div>
            <div class="value">
                {{ number_format($summary['total_stock_kg'], 2) }}
                <small style="font-size: 12px;">kg</small>
            </div>
            <div class="sub">
                Overall utilization:
                {{ number_format($summary['utilization_percent'], 2) }}%
            </div>
        </div>

    </div>

    <div class="manager-storage-panel">

        <div class="manager-storage-panel-header">
            <h2>Storage Units Register</h2>
            <p>Operational storage information for authorized branches.</p>

            <form
                method="GET"
                action="{{ route('manager.storage-units.index') }}"
                class="manager-storage-filters"
            >

                <div class="manager-storage-field search">
                    <label for="storage-search">Search</label>
                    <input
                        id="storage-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Code, name or serial number"
                        class="manager-storage-input"
                    >
                </div>

                <div class="manager-storage-field">
                    <label for="storage-branch">Branch</label>
                    <select
                        id="storage-branch"
                        name="branch_id"
                        class="manager-storage-input"
                    >
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

                <div class="manager-storage-field">
                    <label for="storage-type">Storage Type</label>
                    <select
                        id="storage-type"
                        name="storage_type"
                        class="manager-storage-input"
                    >
                        <option value="">All Types</option>
                        <option value="tank"
                            @selected(($filters['storage_type'] ?? '') === 'tank')>
                            Tank
                        </option>
                        <option value="cylinder"
                            @selected(($filters['storage_type'] ?? '') === 'cylinder')>
                            Cylinder
                        </option>
                    </select>
                </div>

                <div class="manager-storage-field">
                    <label for="storage-status">Status</label>
                    <select
                        id="storage-status"
                        name="status"
                        class="manager-storage-input"
                    >
                        <option value="">All Statuses</option>

                        @foreach(['active', 'inactive', 'maintenance', 'decommissioned'] as $status)
                            <option
                                value="{{ $status }}"
                                @selected(($filters['status'] ?? '') === $status)
                            >
                                {{ ucfirst($status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="manager-storage-btn manager-storage-btn-primary"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('manager.storage-units.index') }}"
                    class="manager-storage-btn manager-storage-btn-secondary"
                >
                    Reset
                </a>

            </form>
        </div>

        <div class="manager-storage-table-wrap">
            <table class="manager-storage-table">
                <thead>
                    <tr>
                        <th>Storage Code</th>
                        <th>Storage Unit</th>
                        <th>Type</th>
                        <th>Branch</th>
                        <th>Capacity (kg)</th>
                        <th>Available LPG (kg)</th>
                        <th>Utilization</th>
                        <th>Manufacturer / Serial</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        @php
                            $unit = $row['storage_unit'];
                            $utilization = $row['utilization_percent'];
                            $progressWidth = min(100, max(0, $utilization));
                        @endphp

                        <tr>
                            <td class="manager-storage-strong">
                                {{ $unit->storage_code }}
                            </td>

                            <td>
                                <div class="manager-storage-strong">
                                    {{ $unit->storage_name }}
                                </div>
                                <div class="manager-storage-muted">
                                    {{ $unit->serial_number ?: 'No serial number' }}
                                </div>
                            </td>

                            <td>
                                {{ ucfirst($unit->storage_type) }}
                            </td>

                            <td>
                                {{ $unit->branch?->branch_name ?? '—' }}
                            </td>

                            <td class="manager-storage-strong">
                                {{ number_format($row['capacity_kg'], 2) }}
                            </td>

                            <td class="manager-storage-strong">
                                {{ number_format($row['quantity_kg'], 2) }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($utilization, 2) }}%
                                </strong>

                                <div class="manager-storage-progress">
                                    <div
                                        class="manager-storage-progress-fill"
                                        style="width: {{ $progressWidth }}%;"
                                    ></div>
                                </div>
                            </td>

                            <td>
                                <div>{{ $unit->manufacturer ?: '—' }}</div>
                                <div class="manager-storage-muted">
                                    {{ $unit->serial_number ?: '—' }}
                                </div>
                            </td>

                            <td>
                                <span class="manager-storage-badge {{ $unit->status }}">
                                    {{ ucfirst($unit->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="manager-storage-empty">
                                No storage units match your selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
