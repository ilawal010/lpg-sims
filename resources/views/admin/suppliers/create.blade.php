@extends('layouts.admin')

@section('title', 'Register LPG Supplier')

@section('content')
<style>
    .supplier-form-page {
        --primary: #176b45;
        --primary-dark: #105435;
        --border: #e2e8e5;
        --muted: #64748b;
        color: #172b23;
        max-width: 1100px;
    }

    .supplier-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        margin-bottom: 22px;
        flex-wrap: wrap;
    }

    .supplier-form-header h1 {
        font-size: 24px;
        font-weight: 750;
        margin: 0 0 5px;
    }

    .supplier-subtitle {
        font-size: 13px;
        color: var(--muted);
        margin: 0;
    }

    .supplier-card {
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .supplier-card-header {
        padding: 17px 20px;
        background: #f8faf9;
        border-bottom: 1px solid var(--border);
    }

    .supplier-card-header h2 {
        font-size: 15px;
        font-weight: 700;
        margin: 0 0 4px;
    }

    .supplier-card-body {
        padding: 22px 20px;
    }

    .supplier-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 19px;
    }

    .supplier-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
    }

    .supplier-field.full {
        grid-column: 1 / -1;
    }

    .supplier-field label {
        font-size: 12px;
        font-weight: 700;
    }

    .supplier-required {
        color: #dc2626;
    }

    .supplier-input {
        width: 100%;
        min-height: 42px;
        padding: 10px 12px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        color: #172b23;
        font-size: 13px;
        box-sizing: border-box;
    }

    .supplier-input:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(23, 107, 69, .1);
    }

    textarea.supplier-input {
        min-height: 95px;
        resize: vertical;
    }

    .supplier-input.is-invalid {
        border-color: #dc2626;
    }

    .supplier-error {
        color: #b91c1c;
        font-size: 11px;
    }

    .supplier-help {
        font-size: 11px;
        color: var(--muted);
    }

    .supplier-notice {
        padding: 14px 16px;
        background: #eff8f2;
        border: 1px solid #c9e6d3;
        border-radius: 9px;
        color: #205b3a;
        font-size: 12px;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .supplier-alert {
        padding: 13px 16px;
        border-radius: 8px;
        margin-bottom: 20px;
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
        font-size: 13px;
    }

    .supplier-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 5px;
    }

    .supplier-btn {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        padding: 11px 19px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        border: 1px solid transparent;
        cursor: pointer;
    }

    .supplier-btn-primary {
        background: var(--primary);
        color: #fff;
    }

    .supplier-btn-primary:hover {
        background: var(--primary-dark);
        color: #fff;
    }

    .supplier-btn-secondary {
        background: #fff;
        border-color: var(--border);
        color: #334155;
    }

    @media (max-width: 700px) {
        .supplier-grid {
            grid-template-columns: 1fr;
        }

        .supplier-form-header h1 {
            font-size: 21px;
        }

        .supplier-actions {
            flex-direction: column-reverse;
        }

        .supplier-btn {
            width: 100%;
        }
    }
</style>

<div class="supplier-form-page">

    <div class="supplier-form-header">
        <div>
            <h1>Register LPG Supplier</h1>
            <p class="supplier-subtitle">
                Create a supplier record for LPG purchases and stock receipts.
            </p>
        </div>

        <a href="{{ route('admin.suppliers.index') }}"
           class="supplier-btn supplier-btn-secondary">
            ← Back to Supplier Register
        </a>
    </div>

    <div class="supplier-notice">
        <strong>Supplier Identification:</strong>
        The system will automatically generate a unique supplier code
        (for example, INT-S001) when the registration is completed.
        Supplier codes cannot be changed after registration.
    </div>

    @if($errors->any())
        <div class="supplier-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul style="margin:8px 0 0;padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.suppliers.store') }}"
          id="supplierRegistrationForm">

        @csrf

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>1. Supplier Information</h2>
                <p class="supplier-subtitle">
                    Enter the LPG supply company's identification details.
                </p>
            </div>

            <div class="supplier-card-body">
                <div class="supplier-grid">

                    <div class="supplier-field full">
                        <label for="supplier_name">
                            Supplier / Company Name
                            <span class="supplier-required">*</span>
                        </label>
                        <input
                            type="text"
                            id="supplier_name"
                            name="supplier_name"
                            value="{{ old('supplier_name') }}"
                            maxlength="180"
                            required
                            class="supplier-input @error('supplier_name') is-invalid @enderror"
                            placeholder="Enter registered supplier or company name"
                        >
                        @error('supplier_name')
                            <span class="supplier-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="supplier-field">
                        <label for="registration_number">
                            Business Registration Number
                        </label>
                        <input
                            type="text"
                            id="registration_number"
                            name="registration_number"
                            value="{{ old('registration_number') }}"
                            maxlength="100"
                            class="supplier-input"
                            placeholder="CAC / RC / BN number"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="tax_identification_number">
                            Tax Identification Number
                        </label>
                        <input
                            type="text"
                            id="tax_identification_number"
                            name="tax_identification_number"
                            value="{{ old('tax_identification_number') }}"
                            maxlength="100"
                            class="supplier-input"
                            placeholder="Enter TIN, if available"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>2. Contact Information</h2>
                <p class="supplier-subtitle">
                    Provide the primary contact details for deliveries and enquiries.
                </p>
            </div>

            <div class="supplier-card-body">
                <div class="supplier-grid">

                    <div class="supplier-field">
                        <label for="contact_person">Contact Person</label>
                        <input
                            type="text"
                            id="contact_person"
                            name="contact_person"
                            value="{{ old('contact_person') }}"
                            maxlength="150"
                            class="supplier-input"
                            placeholder="Full name"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            maxlength="150"
                            class="supplier-input"
                            placeholder="supplier@example.com"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="phone">Primary Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone') }}"
                            maxlength="30"
                            class="supplier-input"
                            placeholder="080XXXXXXXX"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="alternate_phone">Alternate Phone</label>
                        <input
                            type="tel"
                            id="alternate_phone"
                            name="alternate_phone"
                            value="{{ old('alternate_phone') }}"
                            maxlength="30"
                            class="supplier-input"
                            placeholder="Optional"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>3. Supplier Location</h2>
                <p class="supplier-subtitle">
                    Record the supplier's business address.
                </p>
            </div>

            <div class="supplier-card-body">
                <div class="supplier-grid">

                    <div class="supplier-field full">
                        <label for="address">Business Address</label>
                        <textarea
                            id="address"
                            name="address"
                            class="supplier-input"
                            placeholder="Street address and business location"
                        >{{ old('address') }}</textarea>
                    </div>

                    <div class="supplier-field">
                        <label for="city">City / Town</label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city') }}"
                            maxlength="100"
                            class="supplier-input"
                            placeholder="Enter city or town"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="state">State</label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="{{ old('state') }}"
                            maxlength="100"
                            class="supplier-input"
                            placeholder="Enter state"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>4. Operational Status</h2>
                <p class="supplier-subtitle">
                    Set the supplier's availability for LPG purchasing.
                </p>
            </div>

            <div class="supplier-card-body">
                <div class="supplier-grid">

                    <div class="supplier-field">
                        <label for="status">
                            Supplier Status
                            <span class="supplier-required">*</span>
                        </label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="supplier-input"
                        >
                            <option value="active"
                                @selected(old('status', 'active') === 'active')>
                                Active
                            </option>
                            <option value="inactive"
                                @selected(old('status') === 'inactive')>
                                Inactive
                            </option>
                        </select>

                        <span class="supplier-help">
                            Only active suppliers will be selectable for new LPG receipts.
                        </span>
                    </div>

                    <div class="supplier-field full">
                        <label for="notes">Additional Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="supplier-input"
                            placeholder="Optional supplier remarks"
                        >{{ old('notes') }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-actions">
            <a href="{{ route('admin.suppliers.index') }}"
               class="supplier-btn supplier-btn-secondary">
                Cancel
            </a>

            <button type="submit"
                    id="supplierSubmitButton"
                    class="supplier-btn supplier-btn-primary">
                Register Supplier
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('supplierRegistrationForm');
    const button = document.getElementById('supplierSubmitButton');

    if (!form || !button) return;

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            return;
        }

        if (!confirm('Confirm registration of this LPG supplier?')) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.textContent = 'Registering Supplier...';
    });
});
</script>
@endsection
