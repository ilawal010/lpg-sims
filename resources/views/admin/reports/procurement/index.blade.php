@extends('layouts.admin')

@section('content')
<style>
    .procurement-report {
        color: #24372b;
    }

    .procurement-report .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
    }

    .procurement-report .report-title {
        margin: 0;
        color: #176b3a;
        font-size: 24px;
        font-weight: 700;
    }

    .procurement-report .report-subtitle {
        margin-top: 6px;
        color: #64748b;
        font-size: 13px;
    }

    .procurement-report .report-panel {
        background: #fff;
        border: 1px solid #e2e8e4;
        border-radius: 12px;
        padding: 18px;
        margin-bottom: 20px;
    }

    .procurement-report .summary-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 22px;
    }

    .procurement-report .summary-card {
        background: #fff;
        border: 1px solid #e2e8e4;
        border-radius: 12px;
        padding: 17px;
    }

    .procurement-report .summary-label {
        color: #64748b;
        font-size: 12px;
        margin-bottom: 8px;
    }

    .procurement-report .summary-value {
        color: #176b3a;
        font-size: 24px;
        font-weight: 700;
    }

    .procurement-report .filter-grid {
        display: flex;
        flex-wrap: wrap;
        align-items: end;
        gap: 12px;
    }

    .procurement-report .filter-field {
        min-width: 170px;
        flex: 1;
    }

    .procurement-report label {
        display: block;
        margin-bottom: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    .procurement-report select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5ce;
        border-radius: 8px;
        background: #fff;
    }

    .procurement-report .report-btn {
        display: inline-block;
        padding: 10px 15px;
        border: 0;
        border-radius: 8px;
        background: #176b3a;
        color: #fff;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .procurement-report .report-btn-secondary {
        background: #eef3ef;
        color: #176b3a;
    }

    .procurement-report .section-title {
        margin: 0 0 15px;
        font-size: 17px;
        color: #176b3a;
    }

    .procurement-report .table-wrap {
        overflow-x: auto;
    }

    .procurement-report table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .procurement-report th,
    .procurement-report td {
        padding: 11px 10px;
        border-bottom: 1px solid #e8eee9;
        text-align: left;
        vertical-align: middle;
    }

    .procurement-report th {
        background: #f2f7f3;
        color: #28583a;
        font-weight: 700;
        white-space: nowrap;
    }

    .procurement-report .number {
        text-align: right;
        white-space: nowrap;
    }

    .procurement-report .priority {
        display: inline-block;
        border-radius: 20px;
        padding: 5px 9px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .procurement-report .priority-critical {
        background: #fee4e2;
        color: #b42318;
    }

    .procurement-report .priority-reorder {
        background: #fff1d6;
        color: #9a6500;
    }

    .procurement-report .priority-routine {
        background: #e0efff;
        color: #175cd3;
    }

    .procurement-report .priority-sufficient {
        background: #e8f5ec;
        color: #176b3a;
    }

    .procurement-report .priority-not_configured {
        background: #f1f5f9;
        color: #475569;
    }

    .procurement-report .empty {
        padding: 25px;
        text-align: center;
        color: #64748b;
    }

    @media (max-width: 1100px) {
        .procurement-report .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 650px) {
        .procurement-report .summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="procurement-report">

    <div class="report-header">
        <div>
            <h1 class="report-title">Procurement Advice</h1>

            <div class="report-subtitle">
                Recommended procurement quantities based on
                available stock, minimum levels and target stock.
            </div>
        </div>

        <div style="display:flex; gap:10px; flex-wrap:wrap;">

            <a href="{{ route('admin.reports.procurement.settings.index') }}"
               class="report-btn report-btn-secondary">
                Stock Settings
            </a>

            <a href="{{ route('admin.reports.procurement.export.csv', request()->query()) }}"
               class="report-btn">
                Export CSV
            </a>

            <a href="{{ route('admin.reports.procurement.export.pdf', request()->query()) }}"
               class="report-btn">
                Export PDF
            </a>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-label">Inventory Items</div>
            <div class="summary-value">
                {{ number_format($summary['total_items']) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Items to Procure</div>
            <div class="summary-value">
                {{ number_format($summary['items_to_procure']) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Critical Items</div>
            <div class="summary-value">
                {{ number_format($summary['critical_items']) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">High Priority</div>
            <div class="summary-value">
                {{ number_format($summary['high_priority_items']) }}
            </div>
        </div>

        <div class="summary-card">
            <div class="summary-label">Configuration Required</div>
            <div class="summary-value">
                {{ number_format($summary['configuration_required']) }}
            </div>
        </div>
    </div>

    <div class="report-panel">
        <form method="GET"
              action="{{ route('admin.reports.procurement.index') }}">

            <div class="filter-grid">

                <div class="filter-field">
                    <label for="branch_id">Branch</label>
                    <select name="branch_id" id="branch_id">
                        <option value="">All Authorized Branches</option>

                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected(
                                    (string)($filters['branch_id'] ?? '')
                                    === (string)$branch->id
                                )>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label for="item_type">Item Type</label>
                    <select name="item_type" id="item_type">
                        <option value="">All Items</option>
                        <option value="lpg"
                            @selected(($filters['item_type'] ?? '') === 'lpg')>
                            LPG
                        </option>
                        <option value="accessory"
                            @selected(($filters['item_type'] ?? '') === 'accessory')>
                            Accessories
                        </option>
                    </select>
                </div>

                <div class="filter-field">
                    <label for="priority">Priority</label>
                    <select name="priority" id="priority">
                        <option value="">All Priorities</option>

                        @foreach([
                            'critical' => 'Critical',
                            'reorder' => 'High',
                            'routine' => 'Routine',
                            'sufficient' => 'No Procurement',
                            'not_configured' => 'Configuration Required',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                @selected(($filters['priority'] ?? '') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-field">
                    <label for="procurement_only">Display</label>
                    <select name="procurement_only"
                            id="procurement_only">
                        <option value="0">All Items</option>
                        <option value="1"
                            @selected((string)($filters['procurement_only'] ?? '0') === '1')>
                            Items to Procure Only
                        </option>
                    </select>
                </div>

                <button type="submit" class="report-btn">
                    Apply Filters
                </button>

                <a href="{{ route('admin.reports.procurement.index') }}"
                   class="report-btn report-btn-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="report-panel">
        <h2 class="section-title">Procurement Requirements</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th>Type</th>
                        <th class="number">Available</th>
                        <th class="number">Minimum</th>
                        <th class="number">Target</th>
                        <th class="number">To Procure</th>
                        <th>Unit</th>
                        <th>Priority</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($advice as $item)
                        <tr>
                            <td>{{ $item['branch_name'] }}</td>
                            <td>{{ $item['item_code'] }}</td>
                            <td>{{ $item['item_name'] }}</td>
                            <td>{{ strtoupper($item['item_type']) }}</td>

                            <td class="number">
                                {{ number_format((float)$item['available_stock'], 3) }}
                            </td>

                            <td class="number">
                                {{ $item['minimum_stock'] !== null
                                    ? number_format((float)$item['minimum_stock'], 3)
                                    : '—' }}
                            </td>

                            <td class="number">
                                {{ $item['target_stock'] !== null
                                    ? number_format((float)$item['target_stock'], 3)
                                    : '—' }}
                            </td>

                            <td class="number">
                                <strong>
                                    {{ $item['quantity_to_procure'] !== null
                                        ? number_format((float)$item['quantity_to_procure'], 3)
                                        : '—' }}
                                </strong>
                            </td>

                            <td>{{ $item['unit'] }}</td>

                            <td>
                                <span class="priority priority-{{ $item['status'] }}">
                                    {{ $item['priority'] }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="empty">
                                No procurement records match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="report-subtitle" style="margin-top:16px;">
            Generated:
            {{ $generatedAt->format('d M Y, h:i A') }}
        </div>
    </div>

</div>
@endsection
