@extends('layouts.manager')

@section('title', 'Accessories Inventory')
@section('header', 'Accessories Inventory')

@section('content')
<style>
.manager-accessories-page {
    color: #24352e;
}

.manager-accessories-heading {
    margin-bottom: 22px;
}

.manager-accessories-heading h1 {
    margin: 0 0 6px;
    font-size: 25px;
    font-weight: 800;
    color: #174d35;
}

.manager-accessories-heading p {
    margin: 0;
    color: #718078;
    font-size: 13px;
    line-height: 1.6;
}

.manager-accessories-cards {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
    margin-bottom: 22px;
}

.manager-accessories-card {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    padding: 20px;
}

.manager-accessories-card .label {
    color: #718078;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 11px;
}

.manager-accessories-card .value {
    color: #174d35;
    font-size: 25px;
    font-weight: 800;
}

.manager-accessories-card .sub {
    color: #8b9890;
    font-size: 11px;
    margin-top: 8px;
}

.manager-accessories-panel {
    background: #fff;
    border: 1px solid #e2ebe5;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 22px;
}

.manager-accessories-panel-header {
    padding: 20px;
    border-bottom: 1px solid #e8eee9;
}

.manager-accessories-panel-header h2 {
    margin: 0 0 6px;
    font-size: 16px;
    font-weight: 800;
    color: #174d35;
}

.manager-accessories-panel-header p {
    margin: 0;
    color: #849188;
    font-size: 12px;
}

.manager-accessories-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 12px;
    margin-top: 18px;
}

.manager-accessories-field {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 145px;
}

.manager-accessories-field.search {
    flex: 1;
    min-width: 220px;
}

.manager-accessories-field label {
    font-size: 12px;
    font-weight: 700;
}

.manager-accessories-input {
    width: 100%;
    min-height: 41px;
    padding: 9px 11px;
    border: 1px solid #d5dfd9;
    border-radius: 8px;
    background: #fff;
    color: #172b23;
    font-size: 13px;
}

.manager-accessories-input:focus {
    outline: none;
    border-color: #176b45;
    box-shadow: 0 0 0 3px rgba(23,107,69,.1);
}

.manager-accessories-btn {
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

.manager-accessories-btn-primary {
    background: #176b45;
    color: #fff;
}

.manager-accessories-btn-secondary {
    background: #fff;
    color: #334155;
    border-color: #d5dfd9;
}

.manager-accessories-table-wrap {
    overflow-x: auto;
}

.manager-accessories-table {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    font-size: 12px;
}

.manager-accessories-table th {
    padding: 13px 15px;
    text-align: left;
    background: #f5f9f6;
    color: #52685b;
    font-size: 11px;
    white-space: nowrap;
}

.manager-accessories-table td {
    padding: 15px;
    border-top: 1px solid #edf1ee;
    vertical-align: middle;
}

.manager-accessories-table tbody tr:hover {
    background: #fbfdfb;
}

.manager-accessories-strong {
    color: #174d35;
    font-weight: 750;
}

.manager-accessories-muted {
    color: #819087;
    font-size: 11px;
}

.manager-accessories-badge {
    display: inline-block;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 750;
    white-space: nowrap;
}

.manager-accessories-badge.active,
.manager-accessories-badge.available {
    background: #e5f5eb;
    color: #167245;
}

.manager-accessories-badge.inactive {
    background: #f1f5f9;
    color: #64748b;
}

.manager-accessories-badge.low {
    background: #fff4d6;
    color: #986300;
}

.manager-accessories-badge.out {
    background: #fee8e8;
    color: #b42318;
}

.manager-accessories-empty {
    padding: 45px 20px;
    text-align: center;
    color: #849188;
    font-size: 13px;
}

.manager-accessories-footer {
    padding: 16px 20px;
    border-top: 1px solid #e8eee9;
}

@media (max-width: 1100px) {
    .manager-accessories-cards {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .manager-accessories-cards {
        grid-template-columns: 1fr;
    }

    .manager-accessories-heading h1 {
        font-size: 21px;
    }
}
</style>

<div class="manager-accessories-page">

    <div class="manager-accessories-heading">
        <h1>Accessories Inventory</h1>
        <p>
            Monitor accessory stock quantities, selling prices
            and availability within your assigned branches.
        </p>
    </div>

    {{-- Inventory summary --}}
    <div class="manager-accessories-cards">

        <div class="manager-accessories-card">
            <div class="label">Total Accessories</div>
            <div class="value">
                {{ number_format($summary['total_items']) }}
            </div>
            <div class="sub">Registered accessory items</div>
        </div>

        <div class="manager-accessories-card">
            <div class="label">Active Accessories</div>
            <div class="value">
                {{ number_format($summary['active_items']) }}
            </div>
            <div class="sub">Available for operational use</div>
        </div>

        <div class="manager-accessories-card">
            <div class="label">Low Stock Items</div>
            <div class="value">
                {{ number_format($summary['low_stock_items']) }}
            </div>
            <div class="sub">At or below reorder level</div>
        </div>

        <div class="manager-accessories-card">
            <div class="label">Out of Stock</div>
            <div class="value">
                {{ number_format($summary['out_of_stock_items']) }}
            </div>
            <div class="sub">Items with zero stock</div>
        </div>

    </div>

    <div class="manager-accessories-panel">

        <div class="manager-accessories-panel-header">
            <h2>Accessories Register</h2>
            <p>Search and filter accessories within your authorized branches.</p>

            <form
                method="GET"
                action="{{ route('manager.accessories.index') }}"
                class="manager-accessories-filters"
            >

                <div class="manager-accessories-field search">
                    <label for="accessory-search">Search</label>
                    <input
                        id="accessory-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Accessory name or number"
                        class="manager-accessories-input"
                    >
                </div>

                <div class="manager-accessories-field">
                    <label for="accessory-branch">Branch</label>
                    <select
                        id="accessory-branch"
                        name="branch_id"
                        class="manager-accessories-input"
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

                <div class="manager-accessories-field">
                    <label for="accessory-category">Category</label>
                    <select
                        id="accessory-category"
                        name="category"
                        class="manager-accessories-input"
                    >
                        <option value="">All Categories</option>

                        @foreach($categories as $category)
                            <option
                                value="{{ $category }}"
                                @selected(
                                    ($filters['category'] ?? '') === $category
                                )
                            >
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="manager-accessories-field">
                    <label for="accessory-status">Status</label>
                    <select
                        id="accessory-status"
                        name="status"
                        class="manager-accessories-input"
                    >
                        <option value="">All Statuses</option>
                        <option value="active"
                            @selected(($filters['status'] ?? '') === 'active')>
                            Active
                        </option>
                        <option value="inactive"
                            @selected(($filters['status'] ?? '') === 'inactive')>
                            Inactive
                        </option>
                    </select>
                </div>

                <div class="manager-accessories-field">
                    <label for="accessory-stock">Stock Level</label>
                    <select
                        id="accessory-stock"
                        name="stock_status"
                        class="manager-accessories-input"
                    >
                        <option value="all">All Stock Levels</option>
                        <option value="available"
                            @selected(($filters['stock_status'] ?? '') === 'available')>
                            Available
                        </option>
                        <option value="low"
                            @selected(($filters['stock_status'] ?? '') === 'low')>
                            Low Stock
                        </option>
                        <option value="out"
                            @selected(($filters['stock_status'] ?? '') === 'out')>
                            Out of Stock
                        </option>
                    </select>
                </div>

                <button
                    type="submit"
                    class="manager-accessories-btn manager-accessories-btn-primary"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('manager.accessories.index') }}"
                    class="manager-accessories-btn manager-accessories-btn-secondary"
                >
                    Reset
                </a>

            </form>
        </div>

        <div class="manager-accessories-table-wrap">
            <table class="manager-accessories-table">
                <thead>
                    <tr>
                        <th>Accessory Number</th>
                        <th>Accessory</th>
                        <th>Category</th>
                        <th>Branch</th>
                        <th>Stock Quantity</th>
                        <th>Reorder Level</th>
                        <th>Stock Status</th>
                        <th>Selling Price</th>
                        <th>Item Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($accessories as $accessory)
                        @php
                            $quantity = (float) $accessory->quantity;
                            $reorderLevel = (float) $accessory->reorder_level;

                            if ($quantity <= 0) {
                                $stockStatus = 'out';
                                $stockLabel = 'Out of Stock';
                            } elseif ($quantity <= $reorderLevel) {
                                $stockStatus = 'low';
                                $stockLabel = 'Low Stock';
                            } else {
                                $stockStatus = 'available';
                                $stockLabel = 'Available';
                            }
                        @endphp

                        <tr>
                            <td class="manager-accessories-strong">
                                {{ $accessory->accessory_number }}
                            </td>

                            <td>
                                <div class="manager-accessories-strong">
                                    {{ $accessory->name }}
                                </div>
                                <div class="manager-accessories-muted">
                                    Unit: {{ $accessory->unit }}
                                </div>
                            </td>

                            <td>{{ $accessory->category }}</td>

                            <td>
                                {{ $accessory->branch?->branch_name ?? '—' }}
                            </td>

                            <td class="manager-accessories-strong">
                                {{ number_format($quantity, 3) }}
                                {{ $accessory->unit }}
                            </td>

                            <td>
                                {{ number_format($reorderLevel, 3) }}
                            </td>

                            <td>
                                <span class="manager-accessories-badge {{ $stockStatus }}">
                                    {{ $stockLabel }}
                                </span>
                            </td>

                            <td class="manager-accessories-strong">
                                ₦{{ number_format($accessory->unit_selling_price, 2) }}
                            </td>

                            <td>
                                <span class="manager-accessories-badge {{ $accessory->status === 'active' ? 'active' : 'inactive' }}">
                                    {{ ucfirst($accessory->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="manager-accessories-empty">
                                No accessories match your selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($accessories->hasPages())
            <div class="manager-accessories-footer">
                {{ $accessories->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
