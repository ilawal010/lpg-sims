@extends('layouts.admin')

@section('content')
<style>
    .procurement-settings {
        color: #24372b;
    }

    .procurement-settings .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .procurement-settings h1 {
        color: #176b3a;
        font-size: 24px;
        margin: 0;
    }

    .procurement-settings .subtitle {
        color: #64748b;
        font-size: 13px;
        margin-top: 6px;
    }

    .procurement-settings .panel {
        background: #fff;
        border: 1px solid #e2e8e4;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .procurement-settings h2 {
        font-size: 17px;
        color: #176b3a;
        margin: 0 0 16px;
    }

    .procurement-settings .form-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .procurement-settings label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .procurement-settings input,
    .procurement-settings select {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #cbd5ce;
        border-radius: 8px;
        background: #fff;
        box-sizing: border-box;
    }

    .procurement-settings .help {
        color: #64748b;
        font-size: 11px;
        margin-top: 5px;
    }

    .procurement-settings .actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .procurement-settings .btn {
        display: inline-block;
        background: #176b3a;
        color: #fff;
        padding: 10px 17px;
        border: 0;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }

    .procurement-settings .btn-secondary {
        background: #eef3ef;
        color: #176b3a;
    }

    .procurement-settings .table-wrap {
        overflow-x: auto;
    }

    .procurement-settings table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .procurement-settings th,
    .procurement-settings td {
        padding: 11px 10px;
        border-bottom: 1px solid #e8eee9;
        text-align: left;
    }

    .procurement-settings th {
        background: #f2f7f3;
        color: #28583a;
        white-space: nowrap;
    }

    .procurement-settings .number {
        text-align: right;
    }

    .procurement-settings .badge {
        display: inline-block;
        padding: 4px 9px;
        border-radius: 20px;
        background: #e8f5ec;
        color: #176b3a;
        font-size: 11px;
        font-weight: 600;
    }

    .procurement-settings .badge-inactive {
        background: #f1f5f9;
        color: #64748b;
    }

    .procurement-settings .alert {
        padding: 12px 15px;
        border-radius: 8px;
        margin-bottom: 18px;
    }

    .procurement-settings .alert-success {
        background: #e8f5ec;
        color: #176b3a;
    }

    .procurement-settings .alert-error {
        background: #fff1f0;
        color: #b42318;
    }

    @media (max-width: 850px) {
        .procurement-settings .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="procurement-settings">

    <div class="page-header">
        <div>
            <h1>Procurement Stock Settings</h1>
            <div class="subtitle">
                Configure minimum and target stock quantities
                used to generate procurement advice.
            </div>
        </div>

        @if(Route::has('admin.reports.procurement.index'))
            <a href="{{ route('admin.reports.procurement.index') }}"
               class="btn btn-secondary">
                View Procurement Advice
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($canManage)
        <div class="panel">
            <h2>Configure Stock Thresholds</h2>

            <form method="POST"
                  action="{{ route('admin.reports.procurement.settings.save') }}">
                @csrf

                <div class="form-grid">
                    <div>
                        <label for="branch_id">Branch</label>
                        <select name="branch_id"
                                id="branch_id"
                                required>
                            <option value="">Select Branch</option>

                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    @selected(old('branch_id') == $branch->id)>
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="item_type">Item Type</label>
                        <select name="item_type"
                                id="item_type"
                                required>
                            <option value="">Select Type</option>
                            <option value="lpg"
                                @selected(old('item_type') === 'lpg')>
                                LPG
                            </option>
                            <option value="accessory"
                                @selected(old('item_type') === 'accessory')>
                                Accessory
                            </option>
                        </select>
                    </div>

                    <div>
                        <label for="item_id">Item</label>
                        <select name="item_id"
                                id="item_id"
                                required>
                            <option value="">Select Item</option>
                        </select>
                        <div class="help" id="item_help">
                            Select a branch and item type first.
                        </div>
                    </div>

                    <div>
                        <label for="minimum_stock">
                            Minimum Stock
                        </label>
                        <input type="number"
                               name="minimum_stock"
                               id="minimum_stock"
                               min="0"
                               step="0.001"
                               value="{{ old('minimum_stock') }}">
                        <div class="help" id="minimum_help">
                            Required for LPG. Accessories use their
                            existing reorder level.
                        </div>
                    </div>

                    <div>
                        <label for="target_stock">
                            Target Stock
                        </label>
                        <input type="number"
                               name="target_stock"
                               id="target_stock"
                               min="0.001"
                               step="0.001"
                               value="{{ old('target_stock') }}"
                               required>
                        <div class="help" id="target_help">
                            Desired quantity after replenishment.
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">
                        Save Procurement Settings
                    </button>
                </div>
            </form>
        </div>
    @else
        <div class="panel">
            <strong>Read-only access</strong>
            <p class="subtitle">
                Procurement thresholds are managed by the Owner.
                You can review configurations for your authorized branches.
            </p>
        </div>
    @endif

    <div class="panel">
        <h2>Existing Procurement Configurations</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Item Type</th>
                        <th>Item</th>
                        <th class="number">Minimum Stock</th>
                        <th class="number">Target Stock</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($settings as $setting)
                        @php
                            $item = $setting->item_type === 'lpg'
                                ? $storageUnits->firstWhere('id', $setting->item_id)
                                : $accessories->firstWhere('id', $setting->item_id);

                            $minimum = $setting->item_type === 'lpg'
                                ? $setting->minimum_stock
                                : $item?->reorder_level;
                        @endphp

                        <tr>
                            <td>
                                {{ $setting->branch?->branch_name ?? '—' }}
                            </td>
                            <td>
                                {{ $setting->item_type === 'lpg' ? 'LPG' : 'Accessory' }}
                            </td>
                            <td>
                                {{ $item?->storage_name ?? $item?->name ?? 'Unavailable Item' }}
                            </td>
                            <td class="number">
                                {{ $minimum !== null ? number_format((float)$minimum, 3) : '—' }}
                            </td>
                            <td class="number">
                                {{ number_format((float)$setting->target_stock, 3) }}
                            </td>
                            <td>
                                <span class="badge {{ $setting->is_active ? '' : 'badge-inactive' }}">
                                    {{ $setting->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6"
                                style="text-align:center; padding:25px; color:#64748b;">
                                No procurement stock settings configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

@if($canManage)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.getElementById('branch_id');
    const typeSelect = document.getElementById('item_type');
    const itemSelect = document.getElementById('item_id');
    const minimumInput = document.getElementById('minimum_stock');
    const targetInput = document.getElementById('target_stock');
    const itemHelp = document.getElementById('item_help');
    const minimumHelp = document.getElementById('minimum_help');
    const targetHelp = document.getElementById('target_help');

    
    const storageUnits = {{ Illuminate\Support\Js::from(
        $storageUnits->map(function ($item) {
            return [
                'id' => $item->id,
                'branch_id' => $item->branch_id,
                'name' => $item->storage_name,
                'capacity' => $item->capacity_kg,
            ];
        })->values()->all()
    ) }};

    const accessories = {{ Illuminate\Support\Js::from(
        $accessories->map(function ($item) {
            return [
                'id' => $item->id,
                'branch_id' => $item->branch_id,
                'name' => $item->name,
                'reorder_level' => $item->reorder_level,
                'unit' => $item->unit,
            ];
        })->values()->all()
    ) }};


    const oldItemId = @json(old('item_id'));

    function refreshItems() {
        const branchId = String(branchSelect.value);
        const type = typeSelect.value;

        itemSelect.innerHTML = '<option value="">Select Item</option>';

        const source = type === 'lpg'
            ? storageUnits
            : type === 'accessory'
                ? accessories
                : [];

        source
            .filter(item => String(item.branch_id) === branchId)
            .forEach(item => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.name;
                itemSelect.appendChild(option);
            });

        if (oldItemId && Array.from(itemSelect.options)
            .some(option => option.value === String(oldItemId))) {
            itemSelect.value = String(oldItemId);
        }

        refreshThresholds();
    }

    function refreshThresholds() {
        const type = typeSelect.value;
        const itemId = String(itemSelect.value);

        minimumInput.readOnly = type === 'accessory';
        minimumInput.required = type === 'lpg';

        if (type === 'accessory') {
            const item = accessories.find(
                item => String(item.id) === itemId
            );

            minimumInput.value = item
                ? item.reorder_level
                : '';

            minimumHelp.textContent =
                'Read-only: sourced from the accessory reorder level.';

            targetInput.removeAttribute('max');
            targetHelp.textContent =
                'Target quantity in the accessory unit of measurement.';
        } else {
            minimumHelp.textContent =
                'Minimum LPG quantity in kilograms.';

            const item = storageUnits.find(
                item => String(item.id) === itemId
            );

            if (item) {
                targetInput.max = item.capacity;
                targetHelp.textContent =
                    'Maximum storage capacity: ' +
                    item.capacity + ' kg.';
            } else {
                targetInput.removeAttribute('max');
                targetHelp.textContent =
                    'Select an LPG storage unit to view its capacity.';
            }
        }

        itemHelp.textContent = itemId
            ? 'Item selected.'
            : 'Select an item belonging to the chosen branch.';
    }

    branchSelect.addEventListener('change', refreshItems);
    typeSelect.addEventListener('change', refreshItems);
    itemSelect.addEventListener('change', refreshThresholds);

    refreshItems();
});
</script>
@endif
@endsection
