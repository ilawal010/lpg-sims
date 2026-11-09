@extends('layouts.admin')

@section('title', 'LPG Supplier Register')

@section('content')
<style>
    .supplier-page {
        --supplier-green: #176b45;
        --supplier-border: #e2e8e5;
        --supplier-muted: #64748b;
        color: #172b23;
    }

    .supplier-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .supplier-header h1 {
        font-size: 24px;
        font-weight: 750;
        margin: 0 0 5px;
    }

    .supplier-subtitle {
        color: var(--supplier-muted);
        font-size: 13px;
        margin: 0;
    }

    .supplier-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 13px;
        font-weight: 650;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .supplier-btn-primary {
        background: var(--supplier-green);
        color: #fff;
    }

    .supplier-btn-primary:hover {
        background: #105435;
        color: #fff;
    }

    .supplier-btn-secondary {
        background: #fff;
        color: #334155;
        border-color: var(--supplier-border);
    }

    .supplier-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .supplier-stat {
        background: #fff;
        border: 1px solid var(--supplier-border);
        border-radius: 12px;
        padding: 18px;
    }

    .supplier-stat-label {
        font-size: 12px;
        color: var(--supplier-muted);
        margin-bottom: 9px;
    }

    .supplier-stat-value {
        font-size: 27px;
        font-weight: 750;
        line-height: 1.2;
    }

    .supplier-panel {
        background: #fff;
        border: 1px solid var(--supplier-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .supplier-panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid var(--supplier-border);
    }

    .supplier-panel-header h2 {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .supplier-filters {
        display: flex;
        align-items: end;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 17px;
    }

    .supplier-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
        min-width: 175px;
    }

    .supplier-field.search {
        flex: 1;
        min-width: 220px;
    }

    .supplier-field label {
        font-size: 12px;
        font-weight: 650;
    }

    .supplier-input {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        font-size: 13px;
        color: #172b23;
    }

    .supplier-input:focus {
        outline: none;
        border-color: var(--supplier-green);
        box-shadow: 0 0 0 3px rgba(23,107,69,.1);
    }

    .supplier-table-wrap {
        overflow-x: auto;
    }

    .supplier-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 850px;
    }

    .supplier-table th {
        text-align: left;
        padding: 13px 17px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #64748b;
        background: #f8faf9;
        border-bottom: 1px solid var(--supplier-border);
        white-space: nowrap;
    }

    .supplier-table td {
        padding: 15px 17px;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
        vertical-align: middle;
    }

    .supplier-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .supplier-name {
        font-weight: 700;
        color: #172b23;
    }

    .supplier-code {
        font-size: 11px;
        color: var(--supplier-muted);
        margin-top: 4px;
    }

    .supplier-contact-secondary {
        font-size: 11px;
        color: var(--supplier-muted);
        margin-top: 4px;
    }

    .supplier-status {
        display: inline-flex;
        align-items: center;
        border-radius: 20px;
        padding: 5px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .supplier-status.active {
        color: #166534;
        background: #dcfce7;
    }

    .supplier-status.inactive {
        color: #92400e;
        background: #fef3c7;
    }

    .supplier-edit {
        color: var(--supplier-green);
        font-weight: 700;
        text-decoration: none;
    }

    .supplier-empty {
        padding: 55px 20px;
        text-align: center;
    }

    .supplier-empty h3 {
        font-size: 17px;
        margin: 0 0 8px;
    }

    .supplier-empty p {
        color: var(--supplier-muted);
        font-size: 13px;
        margin: 0 0 18px;
    }

    .supplier-footer {
        padding: 15px 20px;
        border-top: 1px solid var(--supplier-border);
    }

    @media (max-width: 700px) {
        .supplier-stats {
            grid-template-columns: 1fr;
        }

        .supplier-header h1 {
            font-size: 21px;
        }
    }
</style>

<div class="supplier-page">

    <div class="supplier-header">
        <div>
            <h1>LPG Supplier Register</h1>
            <p class="supplier-subtitle">
                Manage LPG supply companies, contact details and operational status.
            </p>
        </div>

        <a href="{{ route('admin.suppliers.create') }}"
           class="supplier-btn supplier-btn-primary">
            + Register Supplier
        </a>
    </div>

    <div class="supplier-stats">
        <div class="supplier-stat">
            <div class="supplier-stat-label">Total Suppliers</div>
            <div class="supplier-stat-value">
                {{ number_format($totalSuppliers) }}
            </div>
        </div>

        <div class="supplier-stat">
            <div class="supplier-stat-label">Active Suppliers</div>
            <div class="supplier-stat-value" style="color:#176b45;">
                {{ number_format($activeSuppliers) }}
            </div>
        </div>

        <div class="supplier-stat">
            <div class="supplier-stat-label">Inactive Suppliers</div>
            <div class="supplier-stat-value">
                {{ number_format($inactiveSuppliers) }}
            </div>
        </div>
    </div>

    <div class="supplier-panel">

        <div class="supplier-panel-header">
            <h2>Registered LPG Suppliers</h2>
            <p class="supplier-subtitle">
                Search and manage approved supply sources.
            </p>

            <form method="GET"
                  action="{{ route('admin.suppliers.index') }}"
                  class="supplier-filters">

                <div class="supplier-field search">
                    <label for="supplier-search">Search Supplier</label>
                    <input
                        id="supplier-search"
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Supplier name, code, phone or email"
                        class="supplier-input"
                    >
                </div>

                <div class="supplier-field">
                    <label for="supplier-status">Status</label>
                    <select
                        id="supplier-status"
                        name="status"
                        class="supplier-input"
                    >
                        <option value="">All Statuses</option>
                        <option value="active"
                            @selected(request('status') === 'active')>
                            Active
                        </option>
                        <option value="inactive"
                            @selected(request('status') === 'inactive')>
                            Inactive
                        </option>
                    </select>
                </div>

                <button type="submit"
                        class="supplier-btn supplier-btn-primary">
                    Apply Filters
                </button>

                <a href="{{ route('admin.suppliers.index') }}"
                   class="supplier-btn supplier-btn-secondary">
                    Reset
                </a>
            </form>
        </div>

        <div class="supplier-table-wrap">
            <table class="supplier-table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact Person</th>
                        <th>Phone / Email</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="supplier-name">
                                    {{ $supplier->supplier_name }}
                                </div>
                                <div class="supplier-code">
                                    {{ $supplier->supplier_code }}
                                </div>
                            </td>

                            <td>
                                {{ $supplier->contact_person ?: '—' }}
                            </td>

                            <td>
                                <div>
                                    {{ $supplier->phone ?: '—' }}
                                </div>
                                @if($supplier->email)
                                    <div class="supplier-contact-secondary">
                                        {{ $supplier->email }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                {{ collect([
                                    $supplier->city,
                                    $supplier->state
                                ])->filter()->implode(', ') ?: '—' }}
                            </td>

                            <td>
                                <span class="supplier-status {{ $supplier->status }}">
                                    {{ ucfirst($supplier->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $supplier->updated_at?->format('d M Y') ?? '—' }}
                            </td>

                            <td>
                                <a href="{{ route('admin.suppliers.edit', $supplier) }}"
                                   class="supplier-edit">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="supplier-empty">
                                    <h3>
                                        {{ request()->filled('search') || request()->filled('status')
                                            ? 'No matching suppliers'
                                            : 'No LPG suppliers registered' }}
                                    </h3>

                                    <p>
                                        {{ request()->filled('search') || request()->filled('status')
                                            ? 'Try adjusting your search or status filters.'
                                            : 'Start by registering your first LPG supplier.' }}
                                    </p>

                                    @if(!request()->filled('search') && !request()->filled('status'))
                                        <a href="{{ route('admin.suppliers.create') }}"
                                           class="supplier-btn supplier-btn-primary">
                                            + Register First Supplier
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($suppliers->hasPages())
            <div class="supplier-footer">
                {{ $suppliers->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
