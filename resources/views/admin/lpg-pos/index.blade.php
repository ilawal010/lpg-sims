@extends(
    auth()->user()->hasRole('attendant')
        ? 'layouts.agent'
        : (
            auth()->user()->hasRole('manager')
                ? 'layouts.manager'
                : 'layouts.admin'
        )
)

@section('title', 'Point of Sale')
@section('header', 'Point of Sale')

@section('content')

<style>
    .pos-page {
        --pos-green: #176b45;
        --pos-border: #e2e8e5;
        --pos-muted: #64748b;
        color: #172b23;
    }

    .pos-page * {
        box-sizing: border-box;
    }

    .pos-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        margin-bottom: 22px;
    }

    .pos-header h1 {
        font-size: 24px;
        font-weight: 800;
        margin: 0 0 6px;
    }

    .pos-subtitle {
        color: var(--pos-muted);
        font-size: 13px;
        line-height: 1.6;
        margin: 0;
    }

    .pos-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) minmax(280px, 1fr);
        gap: 20px;
        align-items: start;
    }

    .pos-panel {
        background: #fff;
        border: 1px solid var(--pos-border);
        border-radius: 12px;
        overflow: hidden;
        min-width: 0;
    }

    .pos-panel-header {
        padding: 19px 22px;
        border-bottom: 1px solid var(--pos-border);
    }

    .pos-panel-header h2 {
        font-size: 16px;
        font-weight: 750;
        margin: 0 0 5px;
    }

    .pos-panel-body {
        padding: 22px;
    }

    .pos-type-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 13px;
        margin-bottom: 24px;
    }

    .pos-type {
        display: block;
        cursor: pointer;
        position: relative;
    }

    .pos-type input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .pos-type-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 115px;
        padding: 16px 10px;
        text-align: center;
        border: 2px solid var(--pos-border);
        border-radius: 11px;
        background: #fff;
        transition: .15s ease;
    }

    .pos-type input:checked + .pos-type-content {
        border-color: var(--pos-green);
        background: #eff8f2;
        color: var(--pos-green);
    }

    .pos-type input:focus-visible + .pos-type-content {
        outline: 3px solid #8bc5a3;
        outline-offset: 2px;
    }

    .pos-type-icon {
        font-size: 27px;
    }

    .pos-type-name {
        font-size: 14px;
        font-weight: 800;
    }

    .pos-type-description {
        font-size: 11px;
        color: var(--pos-muted);
    }

    .pos-fields {
        display: grid;
        gap: 18px;
    }

    .pos-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .pos-field label {
        font-size: 12px;
        font-weight: 700;
        color: #334155;
    }

    .pos-input {
        width: 100%;
        min-height: 47px;
        padding: 11px 13px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        color: #172b23;
        font: inherit;
        font-size: 14px;
    }

    .pos-input:focus {
        outline: none;
        border-color: var(--pos-green);
        box-shadow: 0 0 0 3px rgba(23,107,69,.1);
    }

    .pos-help {
        font-size: 11px;
        line-height: 1.5;
        color: var(--pos-muted);
    }

    .pos-price-card {
        margin-top: 24px;
        padding: 22px;
        background: #f0f8f3;
        border: 1px solid #d2e9da;
        border-radius: 11px;
    }

    .pos-price-label {
        font-size: 12px;
        color: #527463;
    }

    .pos-price-value {
        margin-top: 8px;
        font-size: clamp(28px, 4vw, 38px);
        font-weight: 800;
        color: var(--pos-green);
        overflow-wrap: anywhere;
    }

    .pos-price-meta {
        margin-top: 7px;
        font-size: 11px;
        color: #527463;
    }


    .pos-payment-section {
        margin-top: 24px;
        padding-top: 22px;
        border-top: 1px solid var(--pos-border);
    }

    .pos-payment-section h3 {
        margin: 0 0 7px;
        font-size: 16px;
        font-weight: 800;
    }

    .pos-payment-options {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin: 15px 0 18px;
    }

    .pos-payment-option {
        display: block;
        cursor: pointer;
        position: relative;
    }

    .pos-payment-option input {
        position: absolute;
        opacity: 0;
        width: 1px;
        height: 1px;
    }

    .pos-payment-option span {
        display: flex;
        justify-content: center;
        align-items: center;
        text-align: center;
        min-height: 56px;
        padding: 10px;
        border: 2px solid var(--pos-border);
        border-radius: 9px;
        font-size: 12px;
        font-weight: 750;
    }

    .pos-payment-option input:checked + span {
        border-color: var(--pos-green);
        background: #eff8f2;
        color: var(--pos-green);
    }

    .pos-payment-option input:focus-visible + span {
        outline: 3px solid #8bc5a3;
        outline-offset: 2px;
    }

    .pos-payment-confirmation {
        display: flex;
        gap: 11px;
        align-items: flex-start;
        margin-top: 18px;
        padding: 14px;
        border: 1px solid #d2e9da;
        border-radius: 9px;
        background: #f8fcf9;
        font-size: 12px;
        line-height: 1.6;
        cursor: pointer;
    }

    .pos-payment-confirmation input {
        width: 17px;
        height: 17px;
        margin-top: 2px;
        accent-color: var(--pos-green);
        flex-shrink: 0;
    }

    .pos-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        min-height: 46px;
        padding: 11px 18px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
    }

    .pos-btn-primary {
        width: 100%;
        margin-top: 20px;
        background: var(--pos-green);
        color: #fff;
    }

    .pos-btn-primary:hover {
        background: #105435;
    }

    .pos-btn-primary:disabled {
        background: #94a3b8;
        cursor: not-allowed;
    }

    .pos-btn-secondary {
        background: #fff;
        color: var(--pos-green);
        border-color: var(--pos-border);
    }

    .pos-detail {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
    }

    .pos-detail:last-child {
        border-bottom: 0;
    }

    .pos-detail span:first-child {
        color: var(--pos-muted);
    }

    .pos-detail span:last-child {
        text-align: right;
        font-weight: 700;
    }

    .pos-rate {
        font-size: 26px;
        font-weight: 800;
        color: var(--pos-green);
        margin: 10px 0 5px;
    }

    .pos-alert {
        padding: 14px 16px;
        margin-bottom: 20px;
        border-radius: 9px;
        font-size: 13px;
        line-height: 1.6;
    }

    .pos-alert.error {
        color: #991b1b;
        background: #fef2f2;
        border: 1px solid #fecaca;
    }

    .pos-alert.success {
        color: #166534;
        background: #dcfce7;
        border: 1px solid #bbf7d0;
    }

    .pos-alert ul {
        margin: 7px 0 0;
        padding-left: 20px;
    }

    [hidden] {
        display: none !important;
    }

    @media (max-width: 950px) {
        .pos-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .pos-payment-options {
            grid-template-columns: 1fr;
        }
        .pos-header h1 {
            font-size: 21px;
        }

        .pos-panel-body {
            padding: 17px;
        }

        .pos-type-content {
            min-height: 105px;
        }
    }
</style>

<div class="pos-page">

    <div class="pos-header">
        <div>
            <h1>Point of Sale</h1>
            <p class="pos-subtitle">
                Process LPG and Accessories sales quickly and securely.
            </p>
        </div>

        @if(auth()->user()->hasRole('manager')
            && auth()->user()->hasPermission('sales.view')
            && Route::has('manager.sales.index'))

            <a href="{{ route('manager.sales.index') }}"
            class="pos-btn pos-btn-secondary">
                Sales History &rarr;
            </a>

        @elseif(auth()->user()->hasRole('owner')
            && auth()->user()->hasPermission('sales.view')
            && Route::has('admin.lpg-sales.index'))

            <a href="{{ route('admin.lpg-sales.index') }}"
            class="pos-btn pos-btn-secondary">
                LPG Sales History &rarr;
            </a>

        @endif
    </div>

    @if(session('success'))
        <div class="pos-alert success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="pos-alert error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="pos-layout">

        <div class="pos-panel">

            <div class="pos-panel-header">
                <h2>New Sale</h2>
                <p class="pos-subtitle">
                    Select the sale type, product and quantity.
                </p>
            </div>

            <div class="pos-panel-body">

                
                <form
                    id="pos-form"
                    method="POST"
                    action="{{ route('admin.lpg-pos.store') }}"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="transaction_token"
                        value="{{ old('transaction_token', $transactionToken) }}"
                    >


                    <div class="pos-type-grid">

                        <label class="pos-type">
                            <input
                                type="radio"
                                name="sale_type"
                                value="lpg"
                                @checked(old('sale_type', 'lpg') === 'lpg')
                            >
                            <span class="pos-type-content">
                                <span class="pos-type-icon">🔥</span>
                                <span class="pos-type-name">LPG Sale</span>
                                <span class="pos-type-description">
                                    Gas sold by kilogram
                                </span>
                            </span>
                        </label>

                        <label class="pos-type">
                            <input
                                type="radio"
                                name="sale_type"
                                value="accessory"
                                @checked(old('sale_type') === 'accessory')
                            >
                            <span class="pos-type-content">
                                <span class="pos-type-icon">📦</span>
                                <span class="pos-type-name">Accessories</span>
                                <span class="pos-type-description">
                                    Cylinders, regulators and more
                                </span>
                            </span>
                        </label>

                    </div>

                    <div id="lpg-fields" class="pos-fields">

                        <div class="pos-field">
                            <label for="pos-storage">
                                LPG Storage Tank / Cylinder
                            </label>

                            <select
                                id="pos-storage"
                                name="lpg_storage_unit_id"
                                class="pos-input"
                                required
                            >
                                <option value="">
                                    Select storage source
                                </option>

                                @foreach($storageUnits as $unit)
                                    <option
                                        value="{{ $unit->id }}"
                                        @selected(old('lpg_storage_unit_id') == $unit->id)
                                    >
                                        {{ $unit->storage_name }}
                                        ({{ $unit->storage_code }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pos-field">
                            <label for="pos-lpg-quantity">
                                Quantity (kg)
                            </label>

                            <input
                                type="number"
                                id="pos-lpg-quantity"
                                name="quantity_kg"
                                class="pos-input"
                                min="0.001"
                                step="0.001"
                                max="999999999999.999"
                                placeholder="e.g. 12.500"
                                value="{{ old('quantity_kg') }}"
                                required
                            >
                        </div>

                    </div>

                    <div
                        id="accessory-fields"
                        class="pos-fields"
                        hidden
                    >

                        <div class="pos-field">
                            <label for="pos-accessory">
                                Select Accessory
                            </label>

                            <select
                                id="pos-accessory"
                                name="accessory_id"
                                class="pos-input"
                                disabled
                            >
                                <option value="">
                                    Select accessory
                                </option>

                                @foreach($accessories as $accessory)
                                    <option
                                        value="{{ $accessory->id }}"
                                        data-price="{{ $accessory->unit_selling_price }}"
                                        data-stock="{{ $accessory->quantity }}"
                                        data-unit="{{ $accessory->unit }}"
                                        @selected(old('accessory_id') == $accessory->id)
                                    >
                                        {{ $accessory->name }}
                                        ({{ $accessory->accessory_number }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="pos-field">
                            <label for="pos-accessory-quantity">
                                Quantity
                            </label>

                            <input
                                type="number"
                                id="pos-accessory-quantity"
                                name="quantity"
                                class="pos-input"
                                min="0.001"
                                step="0.001"
                                max="999999999999.999"
                                placeholder="Enter quantity"
                                value="{{ old('quantity') }}"
                                disabled
                            >

                            <span
                                id="pos-stock-info"
                                class="pos-help"
                            >
                                Select an accessory to view available stock.
                            </span>
                        </div>

                    </div>

                    <div
                        class="pos-price-card"
                        aria-live="polite"
                    >
                        <div class="pos-price-label">
                            Estimated Total Amount
                        </div>

                        <div
                            class="pos-price-value"
                            id="pos-total"
                        >
                            ₦0.00
                        </div>

                        <div
                            class="pos-price-meta"
                            id="pos-price-description"
                        >
                            Select a product and enter the quantity.
                        </div>
                    </div>

                    <section class="pos-payment-section" aria-labelledby="pos-payment-heading">
                        <h3 id="pos-payment-heading">Payment Information</h3>
                        <p class="pos-help">
                            Confirm the customer's payment before completing the sale.
                        </p>

                        <div class="pos-payment-options" role="group" aria-label="Payment method">
                            @foreach($paymentMethods as $value => $label)
                                <label class="pos-payment-option">
                                    <input
                                        type="radio"
                                        name="payment_method"
                                        value="{{ $value }}"
                                        @checked(old('payment_method', 'cash') === $value)
                                        required
                                    >
                                    <span>{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>

                        <div class="pos-field">
                            <label for="pos-payment-reference" id="pos-payment-reference-label">
                                Transaction Reference (optional)
                            </label>
                            <input
                                type="text"
                                id="pos-payment-reference"
                                name="payment_reference"
                                class="pos-input"
                                maxlength="100"
                                value="{{ old('payment_reference') }}"
                                placeholder="Optional for all payment methods"
                                autocomplete="off"
                            >
                            <span class="pos-help" id="pos-payment-reference-help">
                                You may enter a transaction reference for recordkeeping, but it is not required.
                            </span>
                        </div>

                        <label class="pos-payment-confirmation">
                            <input
                                type="checkbox"
                                id="pos-payment-confirmed"
                                name="payment_confirmed"
                                value="1"
                                @checked(old('payment_confirmed'))
                                required
                            >
                            <span>
                                I confirm that the customer's payment has been successfully
                                received or verified on the available payment device.
                                I accept responsibility for recording this transaction correctly.
                            </span>
                        </label>
                    </section>

                    <button
                        type="submit"
                        id="pos-submit"
                        class="pos-btn pos-btn-primary"
                    >
                        Process Sale
                    </button>

                    <p
                        class="pos-help"
                        style="text-align:center;margin-top:12px;"
                    >
                        You will be asked to confirm before the
                        sale is processed and stock is deducted.
                    </p>

                </form>

            </div>
        </div>

        <div>

            <div class="pos-panel">

                <div class="pos-panel-header">
                    <h2>Sales Information</h2>
                    <p class="pos-subtitle">
                        Automatically determined by the system.
                    </p>
                </div>

                <div class="pos-panel-body">

                    <div class="pos-detail">
                        <span>Branch</span>
                        <span>{{ $branch->branch_name }}</span>
                    </div>

                    <div class="pos-detail">
                        <span>Sales Agent</span>
                        <span>{{ auth()->user()->name }}</span>
                    </div>

                    <div class="pos-detail">
                        <span>Sale Date</span>
                        <span>{{ now()->format('d M Y') }}</span>
                    </div>

                    <div class="pos-detail">
                        <span>Processing</span>
                        <span>Immediate</span>
                    </div>

                    <div class="pos-detail">
                        <span>Receipt</span>
                        <span>58mm Thermal</span>
                    </div>

                </div>

            </div>

            <div
                class="pos-panel"
                style="margin-top:20px;"
            >

                <div class="pos-panel-header">
                    <h2>Current LPG Selling Price</h2>
                    <p class="pos-subtitle">
                        Centrally approved price per kilogram.
                    </p>
                </div>

                <div class="pos-panel-body">

                    <div class="pos-rate">
                        @if(
                            $currentPrice &&
                            (float) $currentPrice->selling_price_per_kg > 0
                        )
                            ₦{{ number_format(
                                (float) $currentPrice->selling_price_per_kg,
                                2
                            ) }}
                        @else
                            Not Configured
                        @endif
                    </div>

                    <p class="pos-subtitle">
                        LPG prices and accessory prices cannot
                        be modified from the POS.
                    </p>

                </div>
            </div>

        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('pos-form');
    const saleTypes = document.querySelectorAll(
        'input[name="sale_type"]'
    );

    const lpgFields = document.getElementById('lpg-fields');
    const accessoryFields = document.getElementById('accessory-fields');

    const storage = document.getElementById('pos-storage');
    const lpgQuantity = document.getElementById('pos-lpg-quantity');

    const accessory = document.getElementById('pos-accessory');
    const accessoryQuantity = document.getElementById(
        'pos-accessory-quantity'
    );

    const stockInfo = document.getElementById('pos-stock-info');
    const total = document.getElementById('pos-total');
    const priceDescription = document.getElementById(
        'pos-price-description'
    );
    const submitButton = document.getElementById('pos-submit');
    const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
    const paymentReference = document.getElementById('pos-payment-reference');
    const paymentReferenceLabel = document.getElementById('pos-payment-reference-label');
    const paymentConfirmed = document.getElementById('pos-payment-confirmed');


    const lpgPrice = @json(
        (string) ($currentPrice?->selling_price_per_kg ?? '0')
    );

    let submitting = false;

    const currency = new Intl.NumberFormat('en-NG', {
        style: 'currency',
        currency: 'NGN',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    function selectedPaymentMethod() {
        return document.querySelector('input[name="payment_method"]:checked')?.value || '';
    }

    function updatePaymentMethod() {
        const method = selectedPaymentMethod();
        paymentReference.required = false;
        paymentReferenceLabel.textContent = 'Transaction Reference (optional)';
        paymentReference.placeholder = method === 'bank_transfer'
            ? 'Bank transfer reference (optional)'
            : method === 'pos'
                ? 'POS transaction reference (optional)'
                : 'Transaction reference (optional)';
    }

    function selectedType() {
        return document.querySelector(
            'input[name="sale_type"]:checked'
        )?.value || 'lpg';
    }

    function updateSaleType() {
        const isLpg = selectedType() === 'lpg';

        lpgFields.hidden = !isLpg;
        accessoryFields.hidden = isLpg;

        storage.disabled = !isLpg;
        lpgQuantity.disabled = !isLpg;

        accessory.disabled = isLpg;
        accessoryQuantity.disabled = isLpg;

        storage.required = isLpg;
        lpgQuantity.required = isLpg;

        accessory.required = !isLpg;
        accessoryQuantity.required = !isLpg;

        calculateTotal();
    }

    function selectedAccessory() {
        return accessory.selectedOptions[0] || null;
    }

    function calculateTotal() {
        let price = 0;
        let quantity = 0;
        let unitLabel = '';

        if (selectedType() === 'lpg') {
            price = Number(lpgPrice);
            quantity = Number(lpgQuantity.value);
            unitLabel = 'kg';

            priceDescription.textContent =
                'Approved LPG price: ' + currency.format(price) + '/kg';
        } else {
            const option = selectedAccessory();

            price = Number(option?.dataset.price || 0);
            quantity = Number(accessoryQuantity.value);
            unitLabel = option?.dataset.unit || 'unit';

            const available = option?.dataset.stock;

            stockInfo.textContent = available !== undefined
                ? 'Available stock: ' + available + ' ' + unitLabel
                : 'Select an accessory to view available stock.';

            priceDescription.textContent = price > 0
                ? 'Unit price: ' + currency.format(price)
                : 'Select an accessory to view its selling price.';
        }

        const amount = Number.isFinite(price * quantity)
            ? Math.round((price * quantity + Number.EPSILON) * 100) / 100
            : 0;

        total.textContent = currency.format(
            amount > 0 ? amount : 0
        );

        return {
            price,
            quantity,
            amount,
            unitLabel
        };
    }

    saleTypes.forEach(function (radio) {
        radio.addEventListener('change', updateSaleType);
    });

    paymentMethods.forEach(function (radio) {
        radio.addEventListener('change', function () {
            paymentConfirmed.checked = false;
            paymentReference.value = '';
            updatePaymentMethod();
        });
    });

    lpgQuantity.addEventListener('input', calculateTotal);
    accessoryQuantity.addEventListener('input', calculateTotal);
    accessory.addEventListener('change', calculateTotal);

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        if (submitting) {
            return;
        }

        if (!form.reportValidity()) {
            return;
        }

        const method = selectedPaymentMethod();
        const reference = paymentReference.value.trim();
        const paymentLabels = {
            cash: 'Cash',
            bank_transfer: 'Bank Transfer',
            pos: 'POS Card'
        };

        if (!paymentLabels[method]) {
            alert('Please select a valid payment method.');
            return;
        }


        if (!paymentConfirmed.checked) {
            alert('Confirm successful payment before processing the sale.');
            paymentConfirmed.focus();
            return;
        }

        paymentReference.value = reference;

        const values = calculateTotal();
        const type = selectedType();

        if (
            !Number.isFinite(values.quantity) ||
            values.quantity <= 0 ||
            !Number.isFinite(values.price) ||
            values.price <= 0
        ) {
            alert('Select a valid product, price and quantity.');
            return;
        }

        let productName = '';

        if (type === 'lpg') {
            if (!storage.value) {
                alert('Please select an LPG storage source.');
                return;
            }

            productName = 'LPG - ' +
                storage.selectedOptions[0].textContent.trim();
        } else {
            const option = selectedAccessory();
            const available = Number(option?.dataset.stock || 0);

            if (!accessory.value) {
                alert('Please select an accessory.');
                return;
            }

            if (values.quantity > available) {
                alert('The requested quantity exceeds available stock.');
                return;
            }

            productName = option.textContent.trim();
        }

        const message =
            'CONFIRM SALE\n\n' +
            'Product: ' + productName + '\n' +
            'Quantity: ' + values.quantity + ' ' +
                values.unitLabel + '\n' +
            'Estimated Total: ' + currency.format(values.amount) +
            'Payment Method: ' + paymentLabels[method] + '\n' +
            (reference ? 'Reference: ' + reference + '\n' : '') +
            '\n\n' +
            'Process this sale and deduct stock?\n\n' +
            'The final price and stock will be verified by the server.';

        if (!window.confirm(message)) {
            return;
        }

        submitting = true;
        submitButton.disabled = true;
        submitButton.textContent = 'Processing Sale...';

        HTMLFormElement.prototype.submit.call(form);
    });

    updateSaleType();
    updatePaymentMethod();
});
</script>
@endpush
