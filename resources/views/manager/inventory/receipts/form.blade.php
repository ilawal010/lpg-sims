@extends('layouts.manager')

@section('title', $receipt ? 'Edit LPG Supply Receipt' : 'New LPG Supply Receipt')

@section('content')
@php
    $editing = $receipt !== null;
    $formAction = $editing
        ? route('manager.stock-receipts.update', $receipt->id)
        : route('manager.stock-receipts.store');
    $selectedBranch = (string) old('branch_id', $receipt?->branch_id ?? ($branches->count() === 1 ? $branches->first()->id : ''));
    $selectedUnit = (string) old('lpg_storage_unit_id', $receipt?->lpg_storage_unit_id ?? '');
    $selectedSupplier = (string) old('lpg_supplier_id', $receipt?->lpg_supplier_id ?? '');
@endphp

<style>
    .receipt-wrap { max-width: 1120px; margin: 0 auto; }
    .receipt-heading { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; flex-wrap:wrap; margin-bottom:22px; }
    .receipt-heading h1 { margin:0 0 6px; font-size:1.6rem; font-weight:750; color:#163b2b; }
    .receipt-heading p { margin:0; color:#66756d; font-size:.94rem; }
    .receipt-card { background:#fff; border:1px solid #e1eae5; border-radius:14px; padding:24px; margin-bottom:20px; box-shadow:0 3px 14px rgba(16,70,41,.035); }
    .receipt-card h2 { margin:0 0 17px; font-size:1.05rem; color:#1b5036; font-weight:750; }
    .receipt-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
    .receipt-field { display:flex; flex-direction:column; gap:7px; min-width:0; }
    .receipt-field label { font-size:.88rem; color:#34473c; font-weight:650; }
    .receipt-field input,.receipt-field select,.receipt-field textarea { box-sizing:border-box; width:100%; min-height:43px; border:1px solid #ccdcd2; border-radius:8px; padding:10px 12px; background:#fff; color:#233a2d; font:inherit; }
    .receipt-field textarea { min-height:94px; resize:vertical; }
    .receipt-field input:focus,.receipt-field select:focus,.receipt-field textarea:focus { outline:2px solid #c0e4cf; border-color:#32835a; }
    .receipt-field .help { color:#6d7b72; font-size:.79rem; }
    .receipt-field .error { color:#a32626; font-size:.81rem; }
    .receipt-full { grid-column:1/-1; }
    .receipt-actions { display:flex; gap:12px; justify-content:flex-end; flex-wrap:wrap; }
    .receipt-btn { display:inline-flex; align-items:center; justify-content:center; padding:11px 19px; border-radius:9px; border:1px solid #b8cfc0; background:#fff; color:#25563b; font-weight:700; text-decoration:none; cursor:pointer; font:inherit; }
    .receipt-btn-primary { background:#187344; border-color:#187344; color:white; }
    .receipt-alert { border-radius:9px; padding:12px 15px; margin-bottom:17px; background:#fff5f4; border:1px solid #f0c8c3; color:#8a2924; }
    .receipt-total { border-radius:10px; background:#eff8f2; border:1px solid #d6e9dc; padding:16px 18px; }
    .receipt-total .caption { color:#42654d; font-size:.84rem; }
    .receipt-total strong { display:block; margin-top:5px; color:#135b34; font-size:1.45rem; }
    .receipt-note { color:#617268; font-size:.85rem; margin:12px 0 0; }
    @media(max-width:700px) { .receipt-grid { grid-template-columns:1fr; } .receipt-card { padding:17px; } }
</style>

<div class="receipt-wrap">
    <div class="receipt-heading">
        <div>
            <h1>{{ $editing ? 'Edit LPG Supply Receipt' : 'New LPG Supply Receipt' }}</h1>
            <p>{{ $editing ? 'Update an unposted supplier delivery.' : 'Record a supplier delivery as a draft before posting it to LPG stock.' }}</p>
            @if($editing)
                <p style="margin-top:7px;font-weight:650;">Reference: {{ $receipt->receipt_number }}</p>
            @endif
        </div>
        <a class="receipt-btn" href="{{ route('manager.stock-receipts.index') }}">← Back to Receipts</a>
    </div>

    @if($errors->any())
        <div class="receipt-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ $formAction }}" id="lpgReceiptForm">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <section class="receipt-card">
            <h2>Supplier and Receiving Location</h2>
            <div class="receipt-grid">
                <div class="receipt-field">
                    <label for="receipt_date">Receipt Date <span aria-hidden="true">*</span></label>
                    <input type="date" id="receipt_date" name="receipt_date" value="{{ old('receipt_date', $receipt?->receipt_date?->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
                    @error('receipt_date') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="receipt-field">
                    <label for="lpg_supplier_id">Registered Supplier *</label>
                    <select id="lpg_supplier_id" name="lpg_supplier_id" required>
                        <option value="">Select supplier</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected($selectedSupplier === (string) $supplier->id)>{{ $supplier->supplier_name }}</option>
                        @endforeach
                    </select>
                    @error('lpg_supplier_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="receipt-field">
                    <label for="branch_id">Receiving Branch *</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Select assigned branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected($selectedBranch === (string) $branch->id)>{{ $branch->branch_name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="receipt-field">
                    <label for="lpg_storage_unit_id">Receiving Storage Unit *</label>
                    <select id="lpg_storage_unit_id" name="lpg_storage_unit_id" required>
                        <option value="">Select storage unit</option>
                        @foreach($storageUnits as $unit)
                            <option value="{{ $unit->id }}" data-branch="{{ $unit->branch_id }}" @selected($selectedUnit === (string) $unit->id)>{{ $unit->storage_name }}</option>
                        @endforeach
                    </select>
                    <span class="help">Only storage units in the selected branch are available.</span>
                    @error('lpg_storage_unit_id') <span class="error">{{ $message }}</span> @enderror
                </div>
            </div>
        </section>

        <section class="receipt-card">
            <h2>Delivered LPG and Purchase Cost</h2>
            <div class="receipt-grid">
                <div class="receipt-field">
                    <label for="quantity_kg">Quantity Received (kg) *</label>
                    <input type="number" id="quantity_kg" name="quantity_kg" min="0.001" step="0.001" inputmode="decimal" value="{{ old('quantity_kg', $receipt?->quantity_kg ?? '') }}" placeholder="e.g. 1500.000" required>
                    @error('quantity_kg') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="receipt-field">
                    <label for="unit_cost">Supplier Invoice Unit Cost (₦/kg) *</label>
                    <input type="number" id="unit_cost" name="unit_cost" min="0" step="0.0001" inputmode="decimal" value="{{ old('unit_cost', $receipt?->unit_cost ?? '') }}" placeholder="e.g. 850.0000" required>
                    <span class="help">Enter the actual supplier purchase cost for this delivery.</span>
                    @error('unit_cost') <span class="error">{{ $message }}</span> @enderror
                </div>
                <div class="receipt-full receipt-total" aria-live="polite">
                    <span class="caption">Estimated Supplier Invoice Total</span>
                    <strong id="receiptEstimatedTotal">₦0.00</strong>
                    <span class="caption">Calculated from entered quantity × unit cost. Final amount is validated on the server.</span>
                </div>
            </div>
        </section>

        <section class="receipt-card">
            <h2>Delivery Documentation</h2>
            <div class="receipt-grid">
                <div class="receipt-field">
                    <label for="supplier_invoice_number">Supplier Invoice Number</label>
                    <input id="supplier_invoice_number" name="supplier_invoice_number" type="text" maxlength="150" value="{{ old('supplier_invoice_number', $receipt?->supplier_invoice_number ?? '') }}">
                </div>
                <div class="receipt-field">
                    <label for="delivery_note_number">Delivery Note Number</label>
                    <input id="delivery_note_number" name="delivery_note_number" type="text" maxlength="150" value="{{ old('delivery_note_number', $receipt?->delivery_note_number ?? '') }}">
                </div>
                <div class="receipt-field">
                    <label for="vehicle_registration_number">Delivery Vehicle Registration</label>
                    <input id="vehicle_registration_number" name="vehicle_registration_number" type="text" maxlength="100" value="{{ old('vehicle_registration_number', $receipt?->vehicle_registration_number ?? '') }}">
                </div>
                <div class="receipt-field">
                    <label for="driver_name">Driver Name</label>
                    <input id="driver_name" name="driver_name" type="text" maxlength="150" value="{{ old('driver_name', $receipt?->driver_name ?? '') }}">
                </div>
                <div class="receipt-field receipt-full">
                    <label for="notes">Remarks / Receiving Notes</label>
                    <textarea id="notes" name="notes" maxlength="2000">{{ old('notes', $receipt?->notes ?? '') }}</textarea>
                </div>
            </div>
        </section>

        <div class="receipt-actions">
            <a class="receipt-btn" href="{{ route('manager.stock-receipts.index') }}">Cancel</a>
            <button class="receipt-btn receipt-btn-primary" type="submit" id="receiptSaveButton">{{ $editing ? 'Save Draft Changes' : 'Save Draft Receipt' }}</button>
        </div>
        <p class="receipt-note">Saving this form does not increase LPG stock. Stock is updated only when the receipt is subsequently posted.</p>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const branch = document.getElementById('branch_id');
    const unit = document.getElementById('lpg_storage_unit_id');
    const quantity = document.getElementById('quantity_kg');
    const cost = document.getElementById('unit_cost');
    const total = document.getElementById('receiptEstimatedTotal');
    const form = document.getElementById('lpgReceiptForm');
    const submit = document.getElementById('receiptSaveButton');

    function filterUnits() {
        const branchId = branch.value;
        Array.from(unit.options).forEach(function (option) {
            if (!option.value) return;
            const allowed = !!branchId && option.dataset.branch === branchId;
            option.hidden = !allowed;
            option.disabled = !allowed;
        });
        if (unit.selectedOptions.length && unit.selectedOptions[0].disabled) unit.value = '';
    }
    function updateTotal() {
        const q = Number(quantity.value);
        const c = Number(cost.value);
        const amount = Number.isFinite(q) && Number.isFinite(c) && q >= 0 && c >= 0 ? q * c : 0;
        total.textContent = '₦' + amount.toLocaleString('en-NG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    branch.addEventListener('change', filterUnits);
    quantity.addEventListener('input', updateTotal);
    cost.addEventListener('input', updateTotal);
    form.addEventListener('submit', function () {
        if (form.checkValidity()) {
            submit.disabled = true;
            submit.textContent = 'Saving Draft…';
        }
    });
    filterUnits();
    updateTotal();
});
</script>
@endsection
