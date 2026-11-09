@extends('layouts.admin')

@section('title', 'Edit LPG Supplier')

@section('content')
<style>
    .supplier-edit-page {
        --primary: #176b45;
        --primary-dark: #105435;
        --border: #e2e8e5;
        --muted: #64748b;
        color: #172b23;
        max-width: 1100px;
    }

    .supplier-edit-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .supplier-edit-header h1 {
        font-size: 24px;
        font-weight: 750;
        margin: 0 0 5px;
    }

    .supplier-subtitle {
        color: var(--muted);
        font-size: 13px;
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

    .supplier-input[readonly] {
        background: #f1f5f3;
        color: #475569;
        cursor: not-allowed;
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

    .supplier-meta {
        display: flex;
        gap: 22px;
        flex-wrap: wrap;
        padding: 15px 20px;
        border-top: 1px solid var(--border);
        background: #fcfdfc;
        font-size: 12px;
        color: var(--muted);
    }

    .supplier-meta strong {
        color: #334155;
    }

    @media (max-width: 700px) {
        .supplier-grid {
            grid-template-columns: 1fr;
        }

        .supplier-edit-header h1 {
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

<div class="supplier-edit-page">

    <div class="supplier-edit-header">
        <div>
            <h1>Edit LPG Supplier</h1>
            <p class="supplier-subtitle">
                Update supplier information and operational status.
            </p>
        </div>

        <a href="{{ route('admin.suppliers.index') }}"
           class="supplier-btn supplier-btn-secondary">
            ← Back to Supplier Register
        </a>
    </div>

    <div class="supplier-notice">
        <strong>Supplier:</strong>
        {{ $supplier->supplier_name }}
        ({{ $supplier->supplier_code }}).
        Changes to this record will be captured in the system audit trail.
        The supplier identification code cannot be modified.
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
          action="{{ route('admin.suppliers.update', $supplier) }}"
          id="supplierEditForm">

        @csrf
        @method('PUT')

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>1. Supplier Identification</h2>
                <p class="supplier-subtitle">
                    Registered supplier information.
                </p>
            </div>

            <div class="supplier-card-body">
                <div class="supplier-grid">

                    <div class="supplier-field">
                        <label for="supplier_code">Supplier Code</label>
                        <input
                            type="text"
                            id="supplier_code"
                            value="{{ $supplier->supplier_code }}"
                            class="supplier-input"
                            readonly
                        >
                        <span class="supplier-help">
                            System-generated and permanently assigned.
                        </span>
                    </div>

                    <div class="supplier-field">
                        <label for="supplier_name">
                            Supplier / Company Name
                            <span class="supplier-required">*</span>
                        </label>
                        <input
                            type="text"
                            id="supplier_name"
                            name="supplier_name"
                            value="{{ old('supplier_name', $supplier->supplier_name) }}"
                            maxlength="180"
                            required
                            class="supplier-input @error('supplier_name') is-invalid @enderror"
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
                            value="{{ old('registration_number', $supplier->registration_number) }}"
                            maxlength="100"
                            class="supplier-input"
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
                            value="{{ old('tax_identification_number', $supplier->tax_identification_number) }}"
                            maxlength="100"
                            class="supplier-input"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>2. Contact Information</h2>
                <p class="supplier-subtitle">
                    Primary supplier contact information.
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
                            value="{{ old('contact_person', $supplier->contact_person) }}"
                            maxlength="150"
                            class="supplier-input"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="email">Email Address</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="{{ old('email', $supplier->email) }}"
                            maxlength="150"
                            class="supplier-input"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="phone">Primary Phone</label>
                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="{{ old('phone', $supplier->phone) }}"
                            maxlength="30"
                            class="supplier-input"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="alternate_phone">Alternate Phone</label>
                        <input
                            type="tel"
                            id="alternate_phone"
                            name="alternate_phone"
                            value="{{ old('alternate_phone', $supplier->alternate_phone) }}"
                            maxlength="30"
                            class="supplier-input"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>3. Supplier Location</h2>
                <p class="supplier-subtitle">
                    Business address and location.
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
                        >{{ old('address', $supplier->address) }}</textarea>
                    </div>

                    <div class="supplier-field">
                        <label for="city">City / Town</label>
                        <input
                            type="text"
                            id="city"
                            name="city"
                            value="{{ old('city', $supplier->city) }}"
                            maxlength="100"
                            class="supplier-input"
                        >
                    </div>

                    <div class="supplier-field">
                        <label for="state">State</label>
                        <input
                            type="text"
                            id="state"
                            name="state"
                            value="{{ old('state', $supplier->state) }}"
                            maxlength="100"
                            class="supplier-input"
                        >
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>4. Operational Status and Notes</h2>
                <p class="supplier-subtitle">
                    Control the supplier's availability for future LPG receipts.
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
                                @selected(old('status', $supplier->status) === 'active')>
                                Active
                            </option>

                            <option value="inactive"
                                @selected(old('status', $supplier->status) === 'inactive')>
                                Inactive
                            </option>
                        </select>

                        <span class="supplier-help">
                            Inactive suppliers remain in historical records
                            but will not be selectable for new stock receipts.
                        </span>
                    </div>

                    <div class="supplier-field full">
                        <label for="notes">Additional Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="supplier-input"
                        >{{ old('notes', $supplier->notes) }}</textarea>
                    </div>

                </div>
            </div>
        </div>

        <div class="supplier-card">
            <div class="supplier-card-header">
                <h2>5. Record Information</h2>
                <p class="supplier-subtitle">
                    System-maintained supplier accountability details.
                </p>
            </div>

            <div class="supplier-meta">
                <div>
                    <strong>Supplier ID:</strong>
                    {{ $supplier->id }}
                </div>

                <div>
                    <strong>Created:</strong>
                    {{ $supplier->created_at?->format('d M Y, h:i A') ?? '—' }}
                </div>

                <div>
                    <strong>Last Updated:</strong>
                    {{ $supplier->updated_at?->format('d M Y, h:i A') ?? '—' }}
                </div>
            </div>
        </div>

        <div class="supplier-actions">
            <a href="{{ route('admin.suppliers.index') }}"
               class="supplier-btn supplier-btn-secondary">
                Cancel
            </a>

            <button type="submit"
                    id="supplierUpdateButton"
                    class="supplier-btn supplier-btn-primary">
                Save Supplier Changes
            </button>
        </div>

    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('supplierEditForm');
    const button = document.getElementById('supplierUpdateButton');

    if (!form || !button) return;

    form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
            return;
        }

        if (!confirm('Confirm changes to this LPG supplier?')) {
            event.preventDefault();
            return;
        }

        button.disabled = true;
        button.textContent = 'Saving Changes...';
    });
});
</script>
@endsection
