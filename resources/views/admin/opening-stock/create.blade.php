@extends('layouts.admin')

@section('title', 'Post LPG Opening Stock')
@section('header', 'LPG Inventory Management')

@push('styles')
<style>
    .osp-page {
        color: #294331;
    }

    .osp-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .osp-heading h1 {
        margin: 0 0 6px;
        color: #183d2a;
        font-size: 23px;
        font-weight: 750;
    }

    .osp-heading p {
        margin: 0;
        color: #788b7d;
        font-size: 13px;
        line-height: 1.7;
    }

    .osp-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 20px;
        align-items: start;
    }

    .osp-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .osp-card-head {
        padding: 18px 22px;
        background: #f8fbf9;
        border-bottom: 1px solid #e2ebe5;
    }

    .osp-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .osp-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.7;
        color: #829085;
    }

    .osp-card-body {
        padding: 22px;
    }

    .osp-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .osp-field {
        min-width: 0;
    }

    .osp-field.full {
        grid-column: 1 / -1;
    }

    .osp-field label {
        display: block;
        margin-bottom: 8px;
        color: #405849;
        font-size: 12px;
        font-weight: 750;
    }

    .osp-required {
        color: #b42323;
    }

    .osp-field input,
    .osp-field textarea {
        display: block;
        width: 100%;
        min-height: 44px;
        padding: 12px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-family: inherit;
        font-size: 13px;
        box-sizing: border-box;
    }

    .osp-field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .osp-field input:focus,
    .osp-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .osp-field input[readonly] {
        background: #f5f8f6;
        color: #637b69;
    }

    .osp-help {
        margin-top: 7px;
        color: #859389;
        font-size: 11px;
        line-height: 1.7;
    }

    .osp-error {
        margin-top: 6px;
        color: #b42323;
        font-size: 11px;
    }

    .osp-alert {
        padding: 14px 17px;
        border-radius: 9px;
        margin-bottom: 20px;
        font-size: 12px;
        line-height: 1.7;
    }

    .osp-alert.error {
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
    }

    .osp-alert.warning {
        background: #fff8e8;
        border: 1px solid #f0dfad;
        color: #8c651d;
    }

    .osp-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .osp-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        padding: 12px 19px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
    }

    .osp-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .osp-btn.primary:hover {
        background: #0d5031;
    }

    .osp-btn.primary:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .osp-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #476151;
    }

    .osp-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 18px 22px;
        background: #fbfdfb;
        border-top: 1px solid #e2ebe5;
    }

    .osp-summary {
        padding: 20px;
        border: 1px solid #cfe5d5;
        background: #f1f8f3;
        border-radius: 10px;
    }

    .osp-summary-label {
        font-size: 11px;
        font-weight: 750;
        color: #668372;
        margin-bottom: 8px;
    }

    .osp-summary-value {
        font-size: 25px;
        font-weight: 800;
        color: #11643e;
        overflow-wrap: anywhere;
    }

    .osp-summary-note {
        margin-top: 8px;
        color: #809184;
        font-size: 11px;
        line-height: 1.7;
    }

    .osp-details {
        display: grid;
        gap: 15px;
    }

    .osp-detail {
        padding-bottom: 13px;
        border-bottom: 1px solid #edf2ee;
    }

    .osp-detail:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .osp-detail-label {
        font-size: 11px;
        color: #859389;
        margin-bottom: 6px;
    }

    .osp-detail-value {
        font-size: 13px;
        font-weight: 750;
        color: #284735;
        overflow-wrap: anywhere;
    }

    .osp-code {
        display: inline-flex;
        padding: 7px 12px;
        border-radius: 7px;
        background: #eaf6ee;
        color: #11643e;
        font-weight: 800;
        font-size: 14px;
    }

    .osp-info {
        font-size: 12px;
        color: #64786a;
        line-height: 1.9;
    }

    .osp-info ul {
        margin: 8px 0 0;
        padding-left: 18px;
    }

    .osp-preview {
        background: #f8fbf9;
        border: 1px solid #e2ebe5;
        border-radius: 9px;
        padding: 16px;
        margin-top: 18px;
    }

    .osp-preview-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        font-size: 12px;
        color: #64786a;
    }

    .osp-preview-row strong {
        color: #284735;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .osp-preview-total {
        border-top: 1px solid #dce9df;
        margin-top: 8px;
        padding-top: 12px;
        font-weight: 800;
    }

    .osp-preview-total strong {
        color: #11643e;
        font-size: 17px;
    }

    @media (max-width: 1050px) {
        .osp-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .osp-grid {
            grid-template-columns: 1fr;
        }

        .osp-actions .osp-btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

<div class="osp-page">

    <div class="osp-heading">
        <div>
            <h1>Post LPG Opening Stock</h1>
            <p>
                Establish the initial LPG inventory quantity
                and acquisition value for this storage unit.
            </p>
        </div>

        <a href="{{ route('admin.opening-stock.index') }}"
           class="osp-btn secondary">
            ← Back to Opening Stock
        </a>
    </div>

    @if($errors->any())
        <div class="osp-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="osp-alert warning">
        <strong>Important:</strong>
        Opening stock is a one-time inventory posting.
        After submission, it cannot be edited or deleted
        through ordinary stock operations. Confirm the
        physical LPG quantity and acquisition cost before posting.
    </div>

    <form
        method="POST"
        action="{{ route('admin.opening-stock.store', $storageUnit) }}"
        id="openingStockForm">

        @csrf

        <div class="osp-layout">

            <div>

                {{-- STORAGE UNIT DETAILS --}}
                <div class="osp-card">
                    <div class="osp-card-head">
                        <div class="osp-card-title">
                            Storage Unit Information
                        </div>
                        <div class="osp-card-description">
                            The selected storage unit is fixed
                            for this opening stock transaction.
                        </div>
                    </div>

                    <div class="osp-card-body">
                        <div class="osp-grid">

                            <div class="osp-field">
                                <label>Storage Code</label>
                                <input
                                    type="text"
                                    value="{{ $storageUnit->storage_code }}"
                                    readonly>
                            </div>

                            <div class="osp-field">
                                <label>Storage Type</label>
                                <input
                                    type="text"
                                    value="{{ $storageUnit->storage_type === 'tank'
                                        ? 'LPG Storage Tank'
                                        : 'LPG Storage Cylinder' }}"
                                    readonly>
                            </div>

                            <div class="osp-field full">
                                <label>Storage Unit Name</label>
                                <input
                                    type="text"
                                    value="{{ $storageUnit->storage_name }}"
                                    readonly>
                            </div>

                            <div class="osp-field full">
                                <label>Branch / Location</label>
                                <input
                                    type="text"
                                    value="{{ $storageUnit->branch?->branch_code }} — {{ $storageUnit->branch?->branch_name }}"
                                    readonly>
                            </div>

                            <div class="osp-field full">
                                <label>Rated Storage Capacity</label>
                                <input
                                    type="text"
                                    value="{{ number_format((float) $storageUnit->capacity_kg, 3) }} kg"
                                    readonly>

                                <div class="osp-help">
                                    Opening stock must not exceed
                                    this storage unit's rated capacity.
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- OPENING STOCK ENTRY --}}
                <div class="osp-card">
                    <div class="osp-card-head">
                        <div class="osp-card-title">
                            Opening Stock Details
                        </div>
                        <div class="osp-card-description">
                            Enter the verified physical LPG quantity
                            and acquisition cost.
                        </div>
                    </div>

                    <div class="osp-card-body">
                        <div class="osp-grid">

                            <div class="osp-field">
                                <label for="quantity_kg">
                                    Opening Quantity (kg)
                                    <span class="osp-required">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="quantity_kg"
                                    name="quantity_kg"
                                    min="0.001"
                                    max="{{ $storageUnit->capacity_kg }}"
                                    step="0.001"
                                    value="{{ old('quantity_kg') }}"
                                    placeholder="e.g. 1500"
                                    required>

                                <div class="osp-help">
                                    Maximum:
                                    {{ number_format((float) $storageUnit->capacity_kg, 3) }}
                                    kg
                                </div>

                                @error('quantity_kg')
                                    <div class="osp-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="osp-field">
                                <label for="unit_cost">
                                    Acquisition Cost (₦/kg)
                                    <span class="osp-required">*</span>
                                </label>

                                <input
                                    type="number"
                                    id="unit_cost"
                                    name="unit_cost"
                                    min="0"
                                    max="99999999999999.9999"
                                    step="0.0001"
                                    value="{{ old('unit_cost') }}"
                                    placeholder="e.g. 850"
                                    required>

                                <div class="osp-help">
                                    Cost per kilogram of LPG,
                                    not the selling price.
                                </div>

                                @error('unit_cost')
                                    <div class="osp-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="osp-field full">
                                <div class="osp-preview">
                                    <div class="osp-preview-row">
                                        <span>Opening Quantity</span>
                                        <strong id="previewQuantity">
                                            0.000 kg
                                        </strong>
                                    </div>

                                    <div class="osp-preview-row">
                                        <span>Acquisition Cost</span>
                                        <strong id="previewUnitCost">
                                            ₦0.0000 / kg
                                        </strong>
                                    </div>

                                    <div class="osp-preview-row osp-preview-total">
                                        <span>Total Opening Stock Value</span>
                                        <strong id="previewTotal">
                                            ₦0.00
                                        </strong>
                                    </div>
                                </div>

                                <div class="osp-help">
                                    This is an estimated display value.
                                    The server calculates the recorded
                                    inventory value using decimal arithmetic.
                                </div>
                            </div>

                            <div class="osp-field">
                                <label for="movement_date">
                                    Opening Stock Date
                                    <span class="osp-required">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="movement_date"
                                    name="movement_date"
                                    max="{{ now()->toDateString() }}"
                                    value="{{ old('movement_date', now()->toDateString()) }}"
                                    required>

                                @error('movement_date')
                                    <div class="osp-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="osp-field">
                                <label for="document_reference">
                                    Document Reference
                                </label>

                                <input
                                    type="text"
                                    id="document_reference"
                                    name="document_reference"
                                    maxlength="100"
                                    value="{{ old('document_reference') }}"
                                    placeholder="e.g. OPEN-INV-001">

                                <div class="osp-help">
                                    Optional physical stock verification
                                    or supporting document number.
                                </div>

                                @error('document_reference')
                                    <div class="osp-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="osp-field full">
                                <label for="description">
                                    Remarks / Description
                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    maxlength="500"
                                    placeholder="Describe the opening stock verification or supporting information">{{ old('description') }}</textarea>

                                @error('description')
                                    <div class="osp-error">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- SUBMISSION --}}
                <div class="osp-card">
                    <div class="osp-actions">
                        <a href="{{ route('admin.opening-stock.index') }}"
                           class="osp-btn secondary">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="osp-btn primary"
                            id="submitOpeningStock">
                            Confirm & Post Opening Stock
                        </button>
                    </div>
                </div>

            </div>

            {{-- INFORMATION SIDEBAR --}}
            <div>

                <div class="osp-card">
                    <div class="osp-card-head">
                        <div class="osp-card-title">
                            Selected Storage Unit
                        </div>
                    </div>

                    <div class="osp-card-body">
                        <div class="osp-details">

                            <div class="osp-detail">
                                <div class="osp-detail-label">
                                    Storage Code
                                </div>
                                <div class="osp-detail-value">
                                    <span class="osp-code">
                                        {{ $storageUnit->storage_code }}
                                    </span>
                                </div>
                            </div>

                            <div class="osp-detail">
                                <div class="osp-detail-label">
                                    Storage Unit
                                </div>
                                <div class="osp-detail-value">
                                    {{ $storageUnit->storage_name }}
                                </div>
                            </div>

                            <div class="osp-detail">
                                <div class="osp-detail-label">
                                    Branch
                                </div>
                                <div class="osp-detail-value">
                                    {{ $storageUnit->branch?->branch_name }}
                                </div>
                            </div>

                            <div class="osp-detail">
                                <div class="osp-detail-label">
                                    Rated Capacity
                                </div>
                                <div class="osp-detail-value">
                                    {{ number_format((float) $storageUnit->capacity_kg, 3) }}
                                    kg
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="osp-card">
                    <div class="osp-card-head">
                        <div class="osp-card-title">
                            Inventory Valuation
                        </div>
                    </div>

                    <div class="osp-card-body">
                        <div class="osp-summary">
                            <div class="osp-summary-label">
                                ESTIMATED OPENING STOCK VALUE
                            </div>

                            <div
                                class="osp-summary-value"
                                id="sidebarTotal">
                                ₦0.00
                            </div>

                            <div class="osp-summary-note">
                                Opening quantity multiplied by
                                acquisition cost per kilogram.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="osp-card">
                    <div class="osp-card-head">
                        <div class="osp-card-title">
                            Posting Guidelines
                        </div>
                    </div>

                    <div class="osp-card-body">
                        <div class="osp-info">
                            <ul>
                                <li>
                                    Verify the physical LPG quantity
                                    before posting.
                                </li>
                                <li>
                                    Quantity must not exceed rated
                                    storage capacity.
                                </li>
                                <li>
                                    Acquisition cost is recorded
                                    separately from LPG selling price.
                                </li>
                                <li>
                                    Opening stock can only be
                                    posted once per storage unit.
                                </li>
                                <li>
                                    Posting creates an incoming
                                    stock ledger entry.
                                </li>
                                <li>
                                    The transaction is recorded
                                    in the audit trail.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInput = document.getElementById('quantity_kg');
    const unitCostInput = document.getElementById('unit_cost');

    const quantityPreview = document.getElementById('previewQuantity');
    const unitCostPreview = document.getElementById('previewUnitCost');
    const totalPreview = document.getElementById('previewTotal');
    const sidebarTotal = document.getElementById('sidebarTotal');

    const form = document.getElementById('openingStockForm');
    const submitButton = document.getElementById('submitOpeningStock');

    const quantityFormatter = new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 3,
        maximumFractionDigits: 3
    });

    const costFormatter = new Intl.NumberFormat('en-NG', {
        minimumFractionDigits: 4,
        maximumFractionDigits: 4
    });

    const moneyFormatter = new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function updatePreview() {
        const quantity = Number(quantityInput.value);
        const unitCost = Number(unitCostInput.value);

        const validQuantity =
            Number.isFinite(quantity) && quantity >= 0;

        const validCost =
            Number.isFinite(unitCost) && unitCost >= 0;

        const safeQuantity = validQuantity ? quantity : 0;
        const safeCost = validCost ? unitCost : 0;

        const total = safeQuantity * safeCost;

        quantityPreview.textContent =
            quantityFormatter.format(safeQuantity) + ' kg';

        unitCostPreview.textContent =
            moneyFormatter.format(safeCost).split('.')[0]
            + '.' + costFormatter.format(safeCost).split('.')[1]
            + ' / kg';

        const formattedTotal = Number.isFinite(total)
            ? moneyFormatter.format(total)
            : '₦0.00';

        totalPreview.textContent = formattedTotal;
        sidebarTotal.textContent = formattedTotal;
    }

    quantityInput.addEventListener('input', updatePreview);
    unitCostInput.addEventListener('input', updatePreview);

    updatePreview();

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            return;
        }

        const quantity = Number(quantityInput.value);
        const capacity = Number(quantityInput.max);

        if (
            !Number.isFinite(quantity) ||
            quantity <= 0 ||
            quantity > capacity
        ) {
            event.preventDefault();
            alert(
                'Opening stock quantity must be greater than zero '
                + 'and must not exceed the rated storage capacity.'
            );
            quantityInput.focus();
            return;
        }

        const confirmed = confirm(
            'Confirm opening stock posting for '
            + @json($storageUnit->storage_code)
            + '?\n\n'
            + 'Quantity: '
            + quantityFormatter.format(quantity)
            + ' kg\n'
            + 'Estimated Value: '
            + totalPreview.textContent
            + '\n\n'
            + 'This transaction cannot be edited or deleted '
            + 'through ordinary stock operations.'
        );

        if (!confirmed) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = 'Posting Opening Stock...';
    });
});
</script>

@endsection
