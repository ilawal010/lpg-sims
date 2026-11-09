@extends('layouts.admin')

@section('title', 'Accessories')
@section('header', 'Accessories Management')

@push('styles')
<style>
    .acc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .acc-title {
        margin: 0;
        font-size: 23px;
        font-weight: 750;
        color: #20352b;
    }

    .acc-description {
        margin: 7px 0 0;
        font-size: 13px;
        color: #78877e;
    }

    .acc-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 22px;
    }

    .acc-stat {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 20px;
    }

    .acc-stat-label {
        color: #78877e;
        font-size: 12px;
        margin-bottom: 9px;
    }

    .acc-stat-value {
        color: #20352b;
        font-size: 22px;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .acc-panel {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 20px;
    }

    .acc-filters {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr auto;
        gap: 12px;
        align-items: end;
    }

    .acc-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 650;
        color: #20352b;
    }

    .acc-input {
        width: 100%;
        min-height: 40px;
        padding: 9px 11px;
        border: 1px solid #d9e5dd;
        border-radius: 8px;
        background: #fff;
        color: #20352b;
        font-size: 13px;
    }

    .acc-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .acc-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 15px;
        min-height: 40px;
        border: 1px solid transparent;
        border-radius: 8px;
        background: #11633e;
        color: #fff;
        font-size: 12px;
        font-weight: 650;
        text-decoration: none;
        cursor: pointer;
    }

    .acc-button-secondary {
        background: #fff;
        border-color: #d9e5dd;
        color: #20352b;
    }

    .acc-table-wrap {
        overflow-x: auto;
    }

    .acc-table {
        width: 100%;
        border-collapse: collapse;
        white-space: nowrap;
        font-size: 12px;
    }

    .acc-table th {
        padding: 13px 11px;
        text-align: left;
        background: #f4f7f5;
        color: #617268;
        font-weight: 700;
    }

    .acc-table td {
        padding: 14px 11px;
        border-bottom: 1px solid #edf1ee;
        color: #20352b;
    }

    .acc-table tbody tr:hover {
        background: #f9fbfa;
    }

    .acc-number {
        font-weight: 700;
        color: #11633e;
    }

    .acc-status {
        display: inline-block;
        padding: 5px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 650;
    }

    .acc-status-active {
        background: #e7f5eb;
        color: #17643b;
    }

    .acc-status-inactive {
        background: #f1f1f1;
        color: #666;
    }

    .acc-empty {
        padding: 45px 15px;
        text-align: center;
        color: #78877e;
    }

    .acc-footer {
        margin-top: 20px;
    }

    @media (max-width: 1100px) {
        .acc-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .acc-filters {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .acc-stats,
        .acc-filters {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

@php
    $totalItems = $accessories->total();

    $pageQuantity = $accessories->getCollection()
        ->sum(fn ($item) => (float) $item->quantity);

    $pageStockValue = $accessories->getCollection()
        ->sum(fn ($item) => (float) $item->stock_value);

    $pageSalesValue = $accessories->getCollection()
        ->sum(fn ($item) => (float) $item->expected_sales_value);
@endphp

<div class="acc-header">
    <div>
        <h1 class="acc-title">Accessories Register</h1>
        <p class="acc-description">
            Manage LPG cylinders, regulators, hoses,
            burners and other accessories.
        </p>
    </div>

    <a href="{{ route('admin.accessories.create') }}"
       class="acc-button">
        + Add Accessory
    </a>
</div>

<div class="acc-stats">
    <div class="acc-stat">
        <div class="acc-stat-label">Registered Items</div>
        <div class="acc-stat-value">
            {{ number_format($totalItems) }}
        </div>
    </div>

    <div class="acc-stat">
        <div class="acc-stat-label">Quantity on This Page</div>
        <div class="acc-stat-value">
            {{ number_format($pageQuantity, 3) }}
        </div>
    </div>

    <div class="acc-stat">
        <div class="acc-stat-label">Stock Value on This Page</div>
        <div class="acc-stat-value">
            ₦{{ number_format($pageStockValue, 2) }}
        </div>
    </div>

    <div class="acc-stat">
        <div class="acc-stat-label">Sales Value on This Page</div>
        <div class="acc-stat-value">
            ₦{{ number_format($pageSalesValue, 2) }}
        </div>
    </div>
</div>

<div class="acc-panel">
    <form method="GET"
          action="{{ route('admin.accessories.index') }}"
          class="acc-filters">

        <div class="acc-field">
            <label for="search">Search Accessories</label>
            <input
                id="search"
                type="text"
                name="search"
                class="acc-input"
                placeholder="Accessory number or name"
                value="{{ request('search') }}"
            >
        </div>

        <div class="acc-field">
            <label for="category">Category</label>
            <select id="category"
                    name="category"
                    class="acc-input">
                <option value="">All Categories</option>

                @foreach($categories as $category)
                    <option value="{{ $category }}"
                        @selected(request('category') == $category)>
                        {{ $category }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="acc-field">
            <label for="status">Status</label>
            <select id="status"
                    name="status"
                    class="acc-input">
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

        <div class="acc-actions">
            <button type="submit" class="acc-button">
                Filter
            </button>

            <a href="{{ route('admin.accessories.index') }}"
               class="acc-button acc-button-secondary">
                Reset
            </a>
        </div>
    </form>
</div>

<div class="acc-panel">
    <div class="acc-table-wrap">
        <table class="acc-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Accessory Number</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Branch</th>
                    <th>Quantity</th>
                    <th>Unit</th>
                    <th>Buying Price</th>
                    <th>Selling Price</th>
                    <th>Stock Value</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($accessories as $accessory)
                    <tr>
                        <td>{{ $accessory->id }}</td>

                        <td>
                            <span class="acc-number">
                                {{ $accessory->accessory_number }}
                            </span>
                        </td>

                        <td>{{ $accessory->name }}</td>

                        <td>{{ $accessory->category }}</td>

                        <td>
                            {{ $accessory->branch?->branch_name ?? '—' }}
                        </td>

                        <td>
                            {{ number_format((float) $accessory->quantity, 3) }}
                        </td>

                        <td>{{ $accessory->unit }}</td>

                        <td>
                            ₦{{ number_format((float) $accessory->unit_buying_price, 2) }}
                        </td>

                        <td>
                            ₦{{ number_format((float) $accessory->unit_selling_price, 2) }}
                        </td>

                        <td>
                            ₦{{ number_format((float) $accessory->stock_value, 2) }}
                        </td>

                        <td>
                            <span class="acc-status
                                {{ $accessory->status === 'active'
                                    ? 'acc-status-active'
                                    : 'acc-status-inactive' }}">
                                {{ ucfirst($accessory->status) }}
                            </span>
                        </td>
                        
                        <td>
                            <div style="display: flex; gap: 7px; align-items: center;">

                                <a href="{{ route('admin.accessories.edit', $accessory->id) }}"
                                style="
                                    display: inline-block;
                                    padding: 7px 14px;
                                    background: #e8f5ec;
                                    color: #11613e;
                                    border: 1px solid #c8e4d1;
                                    border-radius: 7px;
                                    font-size: 12px;
                                    font-weight: 700;
                                    text-decoration: none;
                                    white-space: nowrap;
                                ">
                                    Edit
                                </a>

                                <a href="{{ route('admin.accessories.history', $accessory->id) }}"
                                style="
                                    display: inline-block;
                                    padding: 7px 14px;
                                    background: #f3f6f4;
                                    color: #344e43;
                                    border: 1px solid #dce5df;
                                    border-radius: 7px;
                                    font-size: 12px;
                                    font-weight: 700;
                                    text-decoration: none;
                                    white-space: nowrap;
                                ">
                                    History
                                </a>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="11">
                            <div class="acc-empty">
                                No accessories found.
                                Accessories will appear here
                                once they are registered.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="acc-footer">
        {{ $accessories->links() }}
    </div>
</div>

@endsection
