@extends('layouts.admin')

@section('title', 'Edit LPG Stock Draft')

@section('content')
<style>
    .draft-page {
        max-width: 1120px;
        margin: 0 auto;
        padding-bottom: 30px;
        color: #24352e;
    }

    .draft-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .draft-header h1 {
        margin: 0 0 6px;
        color: #174d35;
        font-size: 25px;
        font-weight: 800;
    }

    .draft-header p {
        margin: 0;
        color: #718078;
        font-size: 13px;
    }

    .draft-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 18px;
    }

    .draft-card-head {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
    }

    .draft-card-head h2 {
        margin: 0 0 5px;
        font-size: 16px;
        color: #174d35;
    }

    .draft-card-head p {
        margin: 0;
        font-size: 12px;
        color: #718078;
    }

    .draft-card-body {
        padding: 22px;
    }

    .draft-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 17px;
    }

    .draft-field {
        min-width: 0;
    }

    .draft-field.full {
        grid-column: 1 / -1;
    }

    .draft-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 700;
        color: #465c4d;
    }

    .draft-field input,
    .draft-field select,
    .draft-field textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 11px 12px;
        min-height: 43px;
        background: #fff;
        border: 1px solid #dce7df;
        border-radius: 8px;
        font: inherit;
        font-size: 13px;
        color: #24352e;
    }

    .draft-field input:focus,
    .draft-field select:focus,
    .draft-field textarea:focus {
        outline: none;
        border-color: #218455;
        box-shadow: 0 0 0 3px rgba(33,132,85,.1);
    }

    .draft-field textarea {
        min-height: 90px;
        resize: vertical;
    }

    .draft-error {
        display: block;
        color: #a92929;
        font-size: 11px;
        margin-top: 5px;
    }

    .draft-alert {
        padding: 14px 17px;
        margin-bottom: 18px;
        border-radius: 9px;
        font-size: 12px;
        line-height: 1.7;
    }

    .draft-alert.info {
        background: #edf6ff;
        border: 1px solid #c9e0f6;
        color: #285b80;
    }

    .draft-alert.danger {
        background: #fff1ef;
        border: 1px solid #f0cbc6;
        color: #a52b27;
    }

    .draft-alert.warning {
        background: #fff8e9;
        border: 1px solid #f2dba4;
        color: #8a5b16;
    }

    .draft-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 20px;
    }

    .draft-summary-item {
        background: #f7faf8;
        border: 1px solid #e2ebe5;
        border-radius: 9px;
        padding: 15px;
    }

    .draft-summary-item span {
        display: block;
        font-size: 11px;
        color: #718078;
        margin-bottom: 7px;
    }

    .draft-summary-item strong {
        color: #174d35;
        font-size: 18px;
        overflow-wrap: anywhere;
    }

    .draft-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        padding: 18px 22px;
        border-top: 1px solid #e2ebe5;
    }

    .draft-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 20px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
    }

    .draft-btn.primary {
        background: #176b45;
        border: 1px solid #176b45;
        color: white;
    }

    .draft-btn.secondary {
        background: white;
        border: 1px solid #dce7df;
        color: #52685b;
    }

    .draft-btn:disabled {
        opacity: .6;
        cursor: wait;
    }

    @media(max-width:650px) {
        .draft-grid, .draft-summary {
            grid-template-columns: 1fr;
        }

        .draft-card-body {
            padding: 16px;
        }

        .draft-actions .draft-btn {
            flex: 1;
        }
    }
</style>

<div class="draft-page">

    <div class="draft-header">
        <div>
            <h1>Edit LPG Stock Draft</h1>
            <p>
                Receipt: {{ $receipt->receipt_number }}
            </p>
        </div>

        <a href="{{ route('admin.lpg-stock.index') }}"
           class="draft-btn secondary">
            &larr; Back to LPG Stock
        </a>
    </div>

    <div class="draft-alert info">
        <strong>Draft Information:</strong>
        You can correct this receipt before posting.
        Saving changes will not increase LPG stock,
        create a new purchase or affect the Cashbook.
    </div>

    @if($errors->any())
        <div class="draft-alert danger" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('admin.lpg-stock.update', $receipt->id) }}"
        method="POST"
        id="draftEditForm"
    >
        @csrf
        @method('PUT')

        <div class="draft-card">
            <div class="draft-card-head">
                <h2>Receipt & Delivery Information</h2>
                <p>Update the supplier and receiving location.</p>
            </div>

            <div class="draft-card-body">
                <div class="draft-grid">

                    <div class="draft-field">
                        <label for="receiptDate">Receipt Date *</label>
                        <input
                            type="date"
                            id="receiptDate"
                            name="receipt_date"
                            value="{{ old('receipt_date', $receipt->receipt_date->format('Y-m-d')) }}"
                            required
                        >
                        @error('receipt_date')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="draft-field">
                        <label for="stockSupplier">Supplier *</label>
                        <select
                            id="stockSupplier"
                            name="lpg_supplier_id"
                            required
                        >
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option
                                    value="{{ $supplier->id }}"
                                    @selected(old('lpg_supplier_id', $receipt->lpg_supplier_id) == $supplier->id)
                                >
                                    {{ $supplier->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('lpg_supplier_id')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="draft-field">
                        <label for="stockBranch">Branch *</label>
                        <select
                            id="stockBranch"
                            name="branch_id"
                            required
                        >
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected(old('branch_id', $receipt->branch_id) == $branch->id)
                                >
                                    {{ $branch->branch_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="draft-field">
                        <label for="stockUnit">Storage Unit *</label>
                        <select
                            id="stockUnit"
                            name="lpg_storage_unit_id"
                            required
                        >
                            <option value="">Select Storage Unit</option>
                            @foreach($storageUnits as $unit)
                                <option
                                    value="{{ $unit->id }}"
                                    data-branch="{{ $unit->branch_id }}"
                                    data-capacity="{{ $unit->capacity_kg }}"
                                    @selected(old('lpg_storage_unit_id', $receipt->lpg_storage_unit_id) == $unit->id)
                                >
                                    {{ $unit->storage_name }}
                                    ({{ number_format($unit->capacity_kg, 3) }} kg)
                                </option>
                            @endforeach
                        </select>
                        @error('lpg_storage_unit_id')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
            </div>
        </div>

        <div class="draft-card">
            <div class="draft-card-head">
                <h2>Quantity & Purchase Cost</h2>
                <p>
                    Correct the delivered quantity or supplier unit price.
                </p>
            </div>

            <div class="draft-card-body">

                <div class="draft-grid">
                    <div class="draft-field">
                        <label for="stockQuantity">
                            Quantity Received (kg) *
                        </label>
                        <input
                            type="number"
                            id="stockQuantity"
                            name="quantity_kg"
                            min="0.001"
                            step="0.001"
                            value="{{ old('quantity_kg', $receipt->quantity_kg) }}"
                            required
                        >
                        @error('quantity_kg')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="draft-field">
                        <label for="stockUnitCost">
                            Purchase Unit Cost (₦/kg) *
                        </label>
                        <input
                            type="number"
                            id="stockUnitCost"
                            name="unit_cost"
                            min="0"
                            step="0.0001"
                            value="{{ old('unit_cost', $receipt->unit_cost) }}"
                            required
                        >
                        @error('unit_cost')
                            <span class="draft-error">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="draft-summary"
                     style="margin-top:20px">
                    <div class="draft-summary-item">
                        <span>Quantity Received</span>
                        <strong id="summaryQuantity">0.000 kg</strong>
                    </div>

                    <div class="draft-summary-item">
                        <span>Unit Purchase Cost</span>
                        <strong id="summaryCost">₦0.00</strong>
                    </div>

                    <div class="draft-summary-item">
                        <span>Total Purchase Value</span>
                        <strong id="summaryTotal">₦0.00</strong>
                    </div>
                </div>

                <div class="draft-alert warning"
                     id="capacityNotice"
                     style="margin-bottom:0">
                    Storage capacity will be checked again
                    when this receipt is posted.
                </div>
            </div>
        </div>

        <div class="draft-card">
            <div class="draft-card-head">
                <h2>Delivery Documentation</h2>
                <p>Update invoice and delivery references if required.</p>
            </div>

            <div class="draft-card-body">
                <div class="draft-grid">

                    <div class="draft-field">
                        <label>Supplier Invoice Number</label>
                        <input
                            type="text"
                            name="supplier_invoice_number"
                            value="{{ old('supplier_invoice_number', $receipt->supplier_invoice_number) }}"
                        >
                    </div>

                    <div class="draft-field">
                        <label>Delivery Note Number</label>
                        <input
                            type="text"
                            name="delivery_note_number"
                            value="{{ old('delivery_note_number', $receipt->delivery_note_number) }}"
                        >
                    </div>

                    <div class="draft-field">
                        <label>Vehicle Registration</label>
                        <input
                            type="text"
                            name="vehicle_registration_number"
                            value="{{ old('vehicle_registration_number', $receipt->vehicle_registration_number) }}"
                        >
                    </div>

                    <div class="draft-field">
                        <label>Driver Name</label>
                        <input
                            type="text"
                            name="driver_name"
                            value="{{ old('driver_name', $receipt->driver_name) }}"
                        >
                    </div>

                    <div class="draft-field full">
                        <label>Additional Notes</label>
                        <textarea name="notes">{{ old('notes', $receipt->notes) }}</textarea>
                    </div>

                </div>
            </div>

            <div class="draft-actions">
                <a
                    href="{{ route('admin.lpg-stock.index') }}"
                    class="draft-btn secondary"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    id="draftSaveButton"
                    class="draft-btn primary"
                >
                    Save Draft Changes
                </button>
            </div>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const branch = document.getElementById('stockBranch');
    const unit = document.getElementById('stockUnit');
    const quantity = document.getElementById('stockQuantity');
    const cost = document.getElementById('stockUnitCost');
    const notice = document.getElementById('capacityNotice');
    const form = document.getElementById('draftEditForm');
    const save = document.getElementById('draftSaveButton');

    const money = new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN'
    });

    const qtyFormat = new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    });

    function updateSummary() {
        const q = Number(quantity.value) || 0;
        const c = Number(cost.value) || 0;

        document.getElementById('summaryQuantity')
            .textContent = qtyFormat.format(q) + ' kg';

        document.getElementById('summaryCost')
            .textContent = money.format(c);

        document.getElementById('summaryTotal')
            .textContent = money.format(q * c);

        const selected = unit.options[unit.selectedIndex];

        if (!selected || !selected.value) {
            notice.textContent = 'Select a storage unit. '
                + 'Available capacity will be checked when posting.';
            return;
        }

        const capacity = Number(selected.dataset.capacity) || 0;

        if (q > capacity) {
            notice.textContent = 'Warning: The entered quantity '
                + 'exceeds the entire rated tank capacity of '
                + qtyFormat.format(capacity) + ' kg. '
                + 'The receipt cannot be posted at this quantity.';
        } else {
            notice.textContent = 'Tank capacity: '
                + qtyFormat.format(capacity) + ' kg. '
                + 'The system will verify available capacity '
                + 'against the current stock balance before posting.';
        }
    }

    function filterUnits() {
        const current = unit.value;
        let valid = false;

        Array.from(unit.options).forEach(function (option) {
            if (!option.value) return;

            const allowed = option.dataset.branch === branch.value;
            option.hidden = !allowed;
            option.disabled = !allowed;

            if (allowed && option.value === current) {
                valid = true;
            }
        });

        if (!valid) unit.value = '';

        unit.disabled = !branch.value;
        updateSummary();
    }

    branch.addEventListener('change', filterUnits);
    unit.addEventListener('change', updateSummary);
    quantity.addEventListener('input', updateSummary);
    cost.addEventListener('input', updateSummary);

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) return;

        if (save.disabled) {
            event.preventDefault();
            return;
        }

        save.disabled = true;
        save.textContent = 'Saving Changes...';
    });

    filterUnits();
});
</script>
@endsection
