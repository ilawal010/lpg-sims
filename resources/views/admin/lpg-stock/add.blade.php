
@extends('layouts.admin')

@section('title', 'Add LPG Stock')

@section('content')
<style>
    .lpg-add-page {
        max-width: 1180px;
        margin: 0 auto;
        color: #24352e;
        padding-bottom: 35px;
    }

    .lpg-add-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 23px;
    }

    .lpg-add-heading h1 {
        margin: 0 0 6px;
        color: #174d35;
        font-size: 25px;
        font-weight: 800;
    }

    .lpg-add-heading p {
        margin: 0;
        color: #718078;
        font-size: 13px;
        line-height: 1.6;
    }

    .lpg-add-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 16px;
        background: #fff;
        border: 1px solid #dce7df;
        border-radius: 9px;
        color: #176b45;
        text-decoration: none;
        font-size: 12px;
        font-weight: 700;
        transition: .2s;
    }

    .lpg-add-back:hover {
        background: #edf7f0;
        color: #105235;
    }

    .lpg-add-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 290px;
        gap: 20px;
        align-items: start;
    }

    .lpg-add-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 13px;
        box-shadow: 0 3px 12px rgba(0,0,0,.025);
        overflow: hidden;
    }

    .lpg-add-card-header {
        padding: 20px 23px;
        border-bottom: 1px solid #e9efeb;
        background: #fbfdfb;
    }

    .lpg-add-card-header h2 {
        margin: 0 0 5px;
        color: #174d35;
        font-size: 16px;
        font-weight: 800;
    }

    .lpg-add-card-header p {
        margin: 0;
        color: #849188;
        font-size: 12px;
        line-height: 1.6;
    }

    .lpg-add-card-body {
        padding: 23px;
    }

    .lpg-add-section {
        margin-bottom: 27px;
    }

    .lpg-add-section:last-child {
        margin-bottom: 0;
    }

    .lpg-add-section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 17px;
        padding-bottom: 11px;
        border-bottom: 1px solid #edf1ee;
    }

    .lpg-add-section-number {
        width: 26px;
        height: 26px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: #e7f5ec;
        color: #176b45;
        border-radius: 7px;
        font-size: 12px;
        font-weight: 800;
    }

    .lpg-add-section-title h3 {
        margin: 0;
        color: #24543b;
        font-size: 13px;
        font-weight: 800;
    }

    .lpg-add-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .lpg-add-field {
        min-width: 0;
    }

    .lpg-add-field.full {
        grid-column: 1 / -1;
    }

    .lpg-add-label {
        display: block;
        margin-bottom: 8px;
        color: #465c4d;
        font-size: 12px;
        font-weight: 700;
    }

    .lpg-add-required {
        color: #cf453d;
        margin-left: 3px;
    }

    .lpg-add-input,
    .lpg-add-select,
    .lpg-add-textarea {
        display: block;
        width: 100%;
        min-height: 43px;
        padding: 10px 12px;
        border: 1px solid #dce7df;
        border-radius: 8px;
        background: #fff;
        color: #24352e;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
        outline: none;
        transition: border-color .2s, box-shadow .2s;
    }

    .lpg-add-input:focus,
    .lpg-add-select:focus,
    .lpg-add-textarea:focus {
        border-color: #218455;
        box-shadow: 0 0 0 3px rgba(33,132,85,.10);
    }

    .lpg-add-input.is-invalid,
    .lpg-add-select.is-invalid,
    .lpg-add-textarea.is-invalid {
        border-color: #d65353;
    }

    .lpg-add-input:disabled,
    .lpg-add-select:disabled {
        background: #f5f7f5;
        color: #8c9990;
        cursor: not-allowed;
    }

    .lpg-add-textarea {
        min-height: 95px;
        resize: vertical;
    }

    .lpg-add-hint {
        display: block;
        margin-top: 6px;
        color: #8a978e;
        font-size: 11px;
        line-height: 1.5;
    }

    .lpg-add-error {
        display: block;
        color: #b42318;
        font-size: 11px;
        margin-top: 6px;
    }

    .lpg-add-alert {
        background: #fff3f1;
        border: 1px solid #f1c7c3;
        border-left: 4px solid #c63e35;
        color: #96302a;
        border-radius: 9px;
        padding: 15px 18px;
        margin-bottom: 20px;
        font-size: 12px;
    }

    .lpg-add-alert strong {
        display: block;
        margin-bottom: 7px;
    }

    .lpg-add-alert ul {
        margin: 0;
        padding-left: 18px;
        line-height: 1.8;
    }

    .lpg-add-summary {
        padding: 21px;
    }

    .lpg-add-summary h3 {
        margin: 0 0 7px;
        color: #174d35;
        font-size: 15px;
        font-weight: 800;
    }

    .lpg-add-summary-description {
        margin: 0 0 20px;
        color: #849188;
        font-size: 12px;
        line-height: 1.7;
    }

    .lpg-add-summary-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 13px 0;
        border-bottom: 1px solid #edf1ee;
        font-size: 12px;
    }

    .lpg-add-summary-row span {
        color: #77867c;
    }

    .lpg-add-summary-row strong {
        color: #24543b;
        text-align: right;
        font-weight: 750;
        overflow-wrap: anywhere;
    }

    .lpg-add-summary-total {
        margin-top: 19px;
        padding: 18px;
        background: #edf8f1;
        border: 1px solid #d5eddd;
        border-radius: 10px;
    }

    .lpg-add-summary-total .label {
        color: #557363;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .lpg-add-summary-total .amount {
        color: #176b45;
        font-size: 25px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .lpg-add-summary-total .hint {
        color: #769383;
        font-size: 11px;
        margin-top: 7px;
    }

    .lpg-add-notice {
        margin-top: 18px;
        padding: 15px;
        border: 1px solid #e5ebe6;
        border-radius: 9px;
        background: #f8faf8;
        font-size: 11px;
        color: #64786b;
        line-height: 1.8;
    }

    .lpg-add-notice strong {
        display: block;
        color: #24543b;
        font-size: 12px;
        margin-bottom: 4px;
    }

    .lpg-add-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 11px;
        flex-wrap: wrap;
        padding: 19px 23px;
        border-top: 1px solid #e9efeb;
        background: #fbfdfb;
    }

    .lpg-add-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 43px;
        padding: 11px 19px;
        border-radius: 9px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: .2s;
        box-sizing: border-box;
    }

    .lpg-add-btn-primary {
        border: 1px solid #176b45;
        background: #176b45;
        color: #fff;
    }

    .lpg-add-btn-primary:hover {
        background: #105235;
    }

    .lpg-add-btn-primary:disabled {
        opacity: .65;
        cursor: wait;
    }

    .lpg-add-btn-secondary {
        background: #fff;
        border: 1px solid #dce7df;
        color: #52685b;
    }

    .lpg-add-btn-secondary:hover {
        background: #f2f7f3;
        color: #174d35;
    }

    @media (max-width: 1050px) {
        .lpg-add-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .lpg-add-grid {
            grid-template-columns: 1fr;
        }

        .lpg-add-heading h1 {
            font-size: 21px;
        }

        .lpg-add-card-body,
        .lpg-add-card-header,
        .lpg-add-summary {
            padding: 17px;
        }

        .lpg-add-actions {
            padding: 16px;
        }

        .lpg-add-btn {
            flex: 1;
        }
    }
</style>

<div class="lpg-add-page">

    <div class="lpg-add-header">
        <div class="lpg-add-heading">
            <h1>Add LPG Stock</h1>
            <p>
                Record a new LPG supplier delivery and its purchase cost.
            </p>
        </div>

        <a href="{{ route('admin.lpg-stock.index') }}"
           class="lpg-add-back">
            &larr; Back to LPG Stock
        </a>
    </div>

    @if($errors->any())
        <div class="lpg-add-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.lpg-stock.store') }}"
          method="POST"
          id="lpgAddStockForm">

        @csrf

        <div class="lpg-add-layout">

            <div class="lpg-add-card">

                <div class="lpg-add-card-header">
                    <h2>New LPG Stock Receipt</h2>
                    <p>
                        Complete the delivery information below.
                        The receipt will first be saved as a draft.
                    </p>
                </div>

                <div class="lpg-add-card-body">

                    {{-- SECTION 1: DELIVERY INFORMATION --}}
                    <div class="lpg-add-section">

                        <div class="lpg-add-section-title">
                            <span class="lpg-add-section-number">01</span>
                            <h3>Delivery Information</h3>
                        </div>

                        <div class="lpg-add-grid">

                            <div class="lpg-add-field">
                                <label for="receiptDate"
                                       class="lpg-add-label">
                                    Receipt Date
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="receipt_date"
                                    id="receiptDate"
                                    class="lpg-add-input @error('receipt_date') is-invalid @enderror"
                                    value="{{ old('receipt_date', date('Y-m-d')) }}"
                                    required
                                >

                                @error('receipt_date')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="stockSupplier"
                                       class="lpg-add-label">
                                    LPG Supplier
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <select
                                    name="lpg_supplier_id"
                                    id="stockSupplier"
                                    class="lpg-add-select @error('lpg_supplier_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select Supplier</option>

                                    @foreach($suppliers as $supplier)
                                        <option
                                            value="{{ $supplier->id }}"
                                            @selected(old('lpg_supplier_id') == $supplier->id)
                                        >
                                            {{ $supplier->supplier_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('lpg_supplier_id')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="stockBranch"
                                       class="lpg-add-label">
                                    Receiving Branch
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <select
                                    name="branch_id"
                                    id="stockBranch"
                                    class="lpg-add-select @error('branch_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select Branch</option>

                                    @foreach($branches as $branch)
                                        <option
                                            value="{{ $branch->id }}"
                                            data-name="{{ $branch->branch_name }}"
                                            @selected(old('branch_id') == $branch->id)
                                        >
                                            {{ $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('branch_id')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="stockUnit"
                                       class="lpg-add-label">
                                    Receiving Storage Unit
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <select
                                    name="lpg_storage_unit_id"
                                    id="stockUnit"
                                    class="lpg-add-select @error('lpg_storage_unit_id') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select Storage Unit</option>

                                    @foreach($storageUnits as $unit)
                                        <option
                                            value="{{ $unit->id }}"
                                            data-branch="{{ $unit->branch_id }}"
                                            data-name="{{ $unit->storage_name }}"
                                            @selected(old('lpg_storage_unit_id') == $unit->id)
                                        >
                                            {{ $unit->storage_name }}
                                            ({{ number_format($unit->capacity_kg, 3) }} kg)
                                        </option>
                                    @endforeach
                                </select>

                                <span class="lpg-add-hint">
                                    Only storage units belonging to the selected
                                    branch will be available.
                                </span>

                                @error('lpg_storage_unit_id')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 2: PURCHASE DETAILS --}}
                    <div class="lpg-add-section">

                        <div class="lpg-add-section-title">
                            <span class="lpg-add-section-number">02</span>
                            <h3>LPG Quantity & Purchase Cost</h3>
                        </div>

                        <div class="lpg-add-grid">

                            <div class="lpg-add-field">
                                <label for="stockQuantity"
                                       class="lpg-add-label">
                                    Quantity Received (kg)
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="quantity_kg"
                                    id="stockQuantity"
                                    class="lpg-add-input @error('quantity_kg') is-invalid @enderror"
                                    value="{{ old('quantity_kg') }}"
                                    min="0.001"
                                    step="0.001"
                                    placeholder="e.g. 2000.000"
                                    required
                                >

                                <span class="lpg-add-hint">
                                    Enter the actual LPG quantity delivered.
                                </span>

                                @error('quantity_kg')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="stockUnitCost"
                                       class="lpg-add-label">
                                    Purchase Unit Cost (₦/kg)
                                    <span class="lpg-add-required">*</span>
                                </label>

                                <input
                                    type="number"
                                    name="unit_cost"
                                    id="stockUnitCost"
                                    class="lpg-add-input @error('unit_cost') is-invalid @enderror"
                                    value="{{ old('unit_cost') }}"
                                    min="0"
                                    step="0.0001"
                                    placeholder="e.g. 900.00"
                                    required
                                >

                                <span class="lpg-add-hint">
                                    Supplier purchase price per kilogram.
                                </span>

                                @error('unit_cost')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- SECTION 3: DELIVERY DOCUMENTATION --}}
                    <div class="lpg-add-section">

                        <div class="lpg-add-section-title">
                            <span class="lpg-add-section-number">03</span>
                            <h3>Supplier & Delivery Documentation</h3>
                        </div>

                        <div class="lpg-add-grid">

                            <div class="lpg-add-field">
                                <label for="supplierInvoice"
                                       class="lpg-add-label">
                                    Supplier Invoice Number
                                </label>

                                <input
                                    type="text"
                                    name="supplier_invoice_number"
                                    id="supplierInvoice"
                                    class="lpg-add-input @error('supplier_invoice_number') is-invalid @enderror"
                                    value="{{ old('supplier_invoice_number') }}"
                                    placeholder="Enter invoice reference"
                                >

                                @error('supplier_invoice_number')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="deliveryNote"
                                       class="lpg-add-label">
                                    Delivery Note Number
                                </label>

                                <input
                                    type="text"
                                    name="delivery_note_number"
                                    id="deliveryNote"
                                    class="lpg-add-input @error('delivery_note_number') is-invalid @enderror"
                                    value="{{ old('delivery_note_number') }}"
                                    placeholder="Enter delivery note reference"
                                >

                                @error('delivery_note_number')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="vehicleRegistration"
                                       class="lpg-add-label">
                                    Delivery Vehicle Registration
                                </label>

                                <input
                                    type="text"
                                    name="vehicle_registration_number"
                                    id="vehicleRegistration"
                                    class="lpg-add-input @error('vehicle_registration_number') is-invalid @enderror"
                                    value="{{ old('vehicle_registration_number') }}"
                                    placeholder="e.g. KAD-123-AB"
                                >

                                @error('vehicle_registration_number')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field">
                                <label for="driverName"
                                       class="lpg-add-label">
                                    Delivery Driver Name
                                </label>

                                <input
                                    type="text"
                                    name="driver_name"
                                    id="driverName"
                                    class="lpg-add-input @error('driver_name') is-invalid @enderror"
                                    value="{{ old('driver_name') }}"
                                    placeholder="Enter driver's name"
                                >

                                @error('driver_name')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="lpg-add-field full">
                                <label for="stockNotes"
                                       class="lpg-add-label">
                                    Additional Notes
                                </label>

                                <textarea
                                    name="notes"
                                    id="stockNotes"
                                    class="lpg-add-textarea @error('notes') is-invalid @enderror"
                                    placeholder="Enter any delivery remarks or additional information..."
                                >{{ old('notes') }}</textarea>

                                @error('notes')
                                    <span class="lpg-add-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                </div>

                <div class="lpg-add-actions">

                    <a href="{{ route('admin.lpg-stock.index') }}"
                       class="lpg-add-btn lpg-add-btn-secondary">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="lpg-add-btn lpg-add-btn-primary"
                        id="saveDraftBtn"
                    >
                        <span id="saveDraftLabel">Save Draft Receipt</span>
                    </button>

                </div>

            </div>

            {{-- RIGHT SIDE: LIVE PURCHASE SUMMARY --}}
            <aside class="lpg-add-card">

                <div class="lpg-add-summary">

                    <h3>Purchase Summary</h3>

                    <p class="lpg-add-summary-description">
                        Review the LPG quantity and purchase value
                        before saving the delivery.
                    </p>

                    <div class="lpg-add-summary-row">
                        <span>Receiving Branch</span>
                        <strong id="summaryBranch">Not selected</strong>
                    </div>

                    <div class="lpg-add-summary-row">
                        <span>Storage Unit</span>
                        <strong id="summaryUnit">Not selected</strong>
                    </div>

                    <div class="lpg-add-summary-row">
                        <span>Quantity</span>
                        <strong id="summaryQuantity">0.000 kg</strong>
                    </div>

                    <div class="lpg-add-summary-row">
                        <span>Unit Cost</span>
                        <strong id="summaryUnitCost">₦0.00</strong>
                    </div>

                    <div class="lpg-add-summary-total">
                        <div class="label">TOTAL LPG PURCHASE VALUE</div>
                        <div class="amount" id="summaryTotal">
                            ₦0.00
                        </div>
                        <div class="hint">
                            Quantity × purchase unit cost
                        </div>
                    </div>

                    <div class="lpg-add-notice">
                        <strong>Receipt Processing</strong>

                        Saving creates a draft receipt only.
                        LPG stock and its linked purchase record
                        will be registered when the receipt is posted.

                        <p style="margin:9px 0 0">
                            No Cashbook payment is recorded merely
                            by saving this form.
                        </p>
                    </div>

                </div>

            </aside>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const branchSelect = document.getElementById('stockBranch');
    const unitSelect = document.getElementById('stockUnit');
    const quantityInput = document.getElementById('stockQuantity');
    const unitCostInput = document.getElementById('stockUnitCost');
    const form = document.getElementById('lpgAddStockForm');
    const saveButton = document.getElementById('saveDraftBtn');
    const saveLabel = document.getElementById('saveDraftLabel');

    const summaryBranch = document.getElementById('summaryBranch');
    const summaryUnit = document.getElementById('summaryUnit');
    const summaryQuantity = document.getElementById('summaryQuantity');
    const summaryUnitCost = document.getElementById('summaryUnitCost');
    const summaryTotal = document.getElementById('summaryTotal');

    const moneyFormatter = new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    const quantityFormatter = new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    });

    function filterStorageUnits() {

        const selectedBranch = branchSelect.value;
        const currentUnit = unitSelect.value;

        let selectedUnitValid = false;

        Array.from(unitSelect.options).forEach(function (option) {

            if (!option.value) {
                return;
            }

            const isAvailable =
                selectedBranch !== '' &&
                option.dataset.branch === selectedBranch;

            option.hidden = !isAvailable;
            option.disabled = !isAvailable;

            if (option.value === currentUnit && isAvailable) {
                selectedUnitValid = true;
            }
        });

        if (!selectedUnitValid) {
            unitSelect.value = '';
        }

        unitSelect.disabled = !selectedBranch;

        updateSummary();
    }

    function updateSummary() {

        const branchOption =
            branchSelect.options[branchSelect.selectedIndex];

        const unitOption =
            unitSelect.options[unitSelect.selectedIndex];

        const quantity = Math.max(
            0,
            Number(quantityInput.value) || 0
        );

        const unitCost = Math.max(
            0,
            Number(unitCostInput.value) || 0
        );

        const total = quantity * unitCost;

        summaryBranch.textContent =
            branchSelect.value
                ? branchOption.textContent.trim()
                : 'Not selected';

        summaryUnit.textContent =
            unitSelect.value
                ? (unitOption.dataset.name || unitOption.textContent.trim())
                : 'Not selected';

        summaryQuantity.textContent =
            quantityFormatter.format(quantity) + ' kg';

        summaryUnitCost.textContent =
            moneyFormatter.format(unitCost);

        summaryTotal.textContent =
            moneyFormatter.format(total);
    }

    branchSelect.addEventListener('change', filterStorageUnits);

    unitSelect.addEventListener('change', updateSummary);

    quantityInput.addEventListener('input', updateSummary);

    unitCostInput.addEventListener('input', updateSummary);

    form.addEventListener('submit', function (event) {

        if (!form.checkValidity()) {
            return;
        }

        if (saveButton.disabled) {
            event.preventDefault();
            return;
        }

        saveButton.disabled = true;
        saveLabel.textContent = 'Saving Draft...';
    });

    filterStorageUnits();
    updateSummary();

});
</script>
@endsection
