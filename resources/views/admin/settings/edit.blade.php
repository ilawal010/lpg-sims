@extends('layouts.admin')

@section('title', 'Business Settings')
@section('header', 'Business Settings')

@push('styles')
<style>
    .settings-heading {
        margin-bottom: 22px;
    }

    .settings-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .settings-heading p {
        margin: 0;
        color: #718377;
        font-size: 13px;
    }

    .settings-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .settings-card-header {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
    }

    .settings-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
        margin-bottom: 5px;
    }

    .settings-card-description {
        font-size: 12px;
        color: #7d8e82;
        line-height: 1.6;
    }

    .settings-card-body {
        padding: 22px;
    }

    .settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .settings-field {
        min-width: 0;
    }

    .settings-field.full {
        grid-column: 1 / -1;
    }

    .settings-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .settings-field input,
    .settings-field textarea,
    .settings-field select {
        width: 100%;
        padding: 11px 12px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .settings-field textarea {
        min-height: 95px;
        resize: vertical;
    }

    .settings-field input:focus,
    .settings-field textarea:focus,
    .settings-field select:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .settings-field input[readonly] {
        background: #f4f7f5;
        color: #728277;
        cursor: not-allowed;
    }

    .settings-help {
        margin-top: 7px;
        color: #859389;
        font-size: 11px;
        line-height: 1.6;
    }

    .settings-error {
        color: #bd2929;
        font-size: 11px;
        margin-top: 6px;
    }

    .settings-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        font-size: 12px;
        line-height: 1.7;
    }

    .settings-alert.success {
        background: #eaf7ef;
        border: 1px solid #bfe4cc;
        color: #17643b;
    }

    .settings-alert.error {
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
    }

    .settings-locked-note {
        margin-top: 18px;
        padding: 13px 16px;
        border-radius: 8px;
        background: #f7faf8;
        border: 1px solid #e2ebe5;
        color: #667a6c;
        font-size: 12px;
        line-height: 1.7;
    }

    .settings-actions {
        position: sticky;
        bottom: 0;
        background: #fff;
        padding: 17px 22px;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        flex-wrap: wrap;
        box-shadow: 0 -4px 16px rgba(0,0,0,.035);
    }

    .settings-save {
        border: 0;
        background: #11643e;
        color: #fff;
        border-radius: 8px;
        padding: 12px 22px;
        font-size: 13px;
        font-weight: 750;
        cursor: pointer;
    }

    .settings-save:hover {
        background: #0d5031;
    }

    .settings-meta {
        color: #859389;
        font-size: 11px;
    }

    @media (max-width: 700px) {
        .settings-grid {
            grid-template-columns: 1fr;
        }
    }

    .settings-logo-layout {
        display: flex;
        align-items: center;
        gap: 28px;
        flex-wrap: wrap;
    }

    .settings-logo-preview {
        width: 190px;
        height: 170px;
        border: 1px dashed #b9d4c3;
        background: #f7faf8;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .settings-logo-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        padding: 12px;
        box-sizing: border-box;
    }

    .settings-logo-placeholder {
        text-align: center;
        color: #769082;
    }

    .settings-logo-placeholder span {
        display: block;
        font-size: 32px;
        font-weight: 800;
        color: #1c6842;
        margin-bottom: 8px;
    }

    .settings-logo-placeholder small {
        font-size: 11px;
    }

    .settings-logo-details {
        flex: 1;
        min-width: 230px;
    }

    .settings-logo-details h3 {
        margin: 0 0 9px;
        font-size: 15px;
        color: #244a34;
    }

    .settings-logo-details p {
        font-size: 12px;
        line-height: 1.7;
        color: #77887c;
        max-width: 480px;
    }

    .settings-logo-form {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 16px;
    }

    .settings-logo-form input[type="file"] {
        font-size: 12px;
        max-width: 280px;
    }

    .settings-logo-upload {
        background: #11643e;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 11px 17px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .settings-logo-upload:hover {
        background: #0d5031;
    }

    .settings-logo-remove {
        margin-top: 14px;
        background: #fff;
        color: #b42323;
        border: 1px solid #efc8c8;
        border-radius: 8px;
        padding: 10px 15px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
    }

    .settings-logo-remove:hover {
        background: #fff3f3;
    }

    @media (max-width: 700px) {
        .settings-logo-layout {
            align-items: stretch;
        }

        .settings-logo-preview {
            width: 100%;
        }
    }

</style>
@endpush

@section('content')

<div class="settings-heading">
    <h1>Business Settings</h1>
    <p>
        Configure company information, receipts, LPG operations
        and financial preferences.
    </p>
</div>

@if(session('success'))
    <div class="settings-alert success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="settings-alert error">
        <strong>Please correct the following:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- COMPANY LOGO & BRANDING --}}
<div class="settings-card">

    <div class="settings-card-header">
        <div class="settings-card-title">
            Company Logo & Branding
        </div>
        <div class="settings-card-description">
            Manage the official company logo displayed on
            receipts, invoices and business documents.
        </div>
    </div>

    <div class="settings-card-body">

            <div class="settings-logo-layout">

                <div class="settings-logo-preview">
                    @if($settings->logo_path)
                        <img
                            src="{{ asset('storage/' . $settings->logo_path) }}"
                            alt="Company Logo">
                    @else
                        <div class="settings-logo-placeholder">
                            <span>IOG</span>
                            <small>No logo uploaded</small>
                        </div>
                    @endif
                </div>

                <div class="settings-logo-details">

                    <h3>Official Business Logo</h3>

                    <p>
                        Upload a clear company logo for Intisar Oil & Gas.
                        PNG, JPG and WebP formats are supported.
                        Maximum file size: 2 MB.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('admin.settings.logo.upload') }}"
                        enctype="multipart/form-data"
                        class="settings-logo-form">

                        @csrf

                        <input
                            type="file"
                            name="logo"
                            id="business_logo"
                            accept=".png,.jpg,.jpeg,.webp"
                            required>

                        @error('logo')
                            <div class="settings-error">
                                {{ $message }}
                            </div>
                        @enderror

                        <button type="submit"
                                class="settings-logo-upload">
                            {{ $settings->logo_path
                                ? 'Replace Company Logo'
                                : 'Upload Company Logo' }}
                        </button>

                    </form>

                    @if($settings->logo_path)
                        <form
                            method="POST"
                            action="{{ route('admin.settings.logo.remove') }}"
                            onsubmit="return confirm('Remove the current company logo?');">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="settings-logo-remove">
                                Remove Logo
                            </button>
                        </form>
                    @endif

                </div>

            </div>

        </div>
    </div>


<form method="POST"
      action="{{ route('admin.settings.update') }}">

    @csrf
    @method('PUT')

    {{-- COMPANY PROFILE --}}
    <div class="settings-card">

        <div class="settings-card-header">
            <div class="settings-card-title">
                Company Profile
            </div>
            <div class="settings-card-description">
                Official business information used on receipts,
                invoices and reports.
            </div>
        </div>

        <div class="settings-card-body">
            <div class="settings-grid">

                <div class="settings-field">
                    <label for="business_name">
                        Business Name *
                    </label>

                    <input type="text"
                           id="business_name"
                           name="business_name"
                           maxlength="255"
                           required
                           value="{{ old('business_name', $settings->business_name) }}">

                    @error('business_name')
                        <div class="settings-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="settings-field">
                    <label for="trading_name">
                        Trading Name
                    </label>

                    <input type="text"
                           id="trading_name"
                           name="trading_name"
                           maxlength="255"
                           value="{{ old('trading_name', $settings->trading_name) }}">
                </div>

                <div class="settings-field full">
                    <label for="address">
                        Business Address
                    </label>

                    <textarea id="address"
                              name="address"
                              maxlength="2000">{{ old('address', $settings->address) }}</textarea>
                </div>

                <div class="settings-field">
                    <label for="phone">
                        Business Phone
                    </label>

                    <input type="text"
                           id="phone"
                           name="phone"
                           maxlength="30"
                           value="{{ old('phone', $settings->phone) }}">
                </div>

                <div class="settings-field">
                    <label for="email">
                        Business Email
                    </label>

                    <input type="email"
                           id="email"
                           name="email"
                           maxlength="255"
                           value="{{ old('email', $settings->email) }}">
                </div>

            </div>
        </div>
    </div>
    
    {{-- RECEIPT CONFIGURATION --}}
    <div class="settings-card">

        <div class="settings-card-header">
            <div class="settings-card-title">
                Receipt Configuration
            </div>
            <div class="settings-card-description">
                Customize the information displayed on customer
                sales receipts.
            </div>
        </div>

        <div class="settings-card-body">
            <div class="settings-grid">

                <div class="settings-field full">
                    <label for="receipt_heading">
                        Receipt Heading
                    </label>

                    <input type="text"
                           id="receipt_heading"
                           name="receipt_heading"
                           maxlength="255"
                           placeholder="Thank you for choosing Intisar Oil & Gas"
                           value="{{ old('receipt_heading', $settings->receipt_heading) }}">
                </div>

                <div class="settings-field full">
                    <label for="receipt_footer">
                        Receipt Footer
                    </label>

                    <textarea id="receipt_footer"
                              name="receipt_footer"
                              maxlength="2000"
                              placeholder="Thank you for your patronage.">{{ old('receipt_footer', $settings->receipt_footer) }}</textarea>
                </div>

                <div class="settings-field">
                    <label for="receipt_prefix">
                        Receipt Number Prefix *
                    </label>

                    <input type="text"
                           id="receipt_prefix"
                           name="receipt_prefix"
                           maxlength="20"
                           required
                           value="{{ old('receipt_prefix', $settings->receipt_prefix) }}">

                    <div class="settings-help">
                        Example: INT. Used when generating sales receipt numbers.
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- LPG OPERATIONS --}}
    <div class="settings-card">

        <div class="settings-card-header">
            <div class="settings-card-title">
                LPG Operations
            </div>
            <div class="settings-card-description">
                Default gas measurement and inventory warning configuration.
            </div>
        </div>

        <div class="settings-card-body">
            <div class="settings-grid">

                <div class="settings-field">
                    <label>LPG Measurement Unit</label>

                    <input type="text"
                           value="{{ $settings->lpg_unit }}"
                           readonly>

                    <div class="settings-help">
                        Gas inventory and sales quantities are measured in kilograms.
                    </div>
                </div>

                <div class="settings-field">
                    <label for="low_stock_threshold_kg">
                        Low Stock Warning (kg) *
                    </label>

                    <input type="number"
                           id="low_stock_threshold_kg"
                           name="low_stock_threshold_kg"
                           min="0"
                           max="999999999.999"
                           step="0.001"
                           required
                           value="{{ old('low_stock_threshold_kg', $settings->low_stock_threshold_kg) }}">

                    <div class="settings-help">
                        The stock level at which LPG inventory warnings will be triggered.
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- FINANCIAL CONFIGURATION --}}
    <div class="settings-card">

        <div class="settings-card-header">
            <div class="settings-card-title">
                Financial Configuration
            </div>
            <div class="settings-card-description">
                Currency and inventory costing controls.
            </div>
        </div>

        <div class="settings-card-body">

            <div class="settings-grid">

                <div class="settings-field">
                    <label>Currency</label>

                    <input type="text"
                           value="{{ $settings->currency_code }} ({{ $settings->currency_symbol }})"
                           readonly>
                </div>

                <div class="settings-field">
                    <label>Inventory Costing Method</label>

                    <input type="text"
                           value="Weighted Average Cost"
                           readonly>
                </div>

            </div>

            <div class="settings-locked-note">
                Financial configuration is protected to maintain
                consistent valuation and profitability calculations.
                LPG-SIMS uses Nigerian Naira and weighted-average
                inventory costing.
            </div>

        </div>
    </div>

    {{-- SYSTEM PREFERENCES --}}
    <div class="settings-card">

        <div class="settings-card-header">
            <div class="settings-card-title">
                System Preferences
            </div>
            <div class="settings-card-description">
                Date presentation and operating timezone.
            </div>
        </div>

        <div class="settings-card-body">
            <div class="settings-grid">

                <div class="settings-field">
                    <label for="timezone">
                        Business Timezone
                    </label>

                    <select id="timezone" name="timezone">
                        <option value="Africa/Lagos"
                            @selected(old('timezone', $settings->timezone) === 'Africa/Lagos')>
                            Africa/Lagos (Nigeria)
                        </option>
                    </select>
                </div>

                <div class="settings-field">
                    <label for="date_format">
                        Date Format
                    </label>

                    <select id="date_format" name="date_format">
                        <option value="d/m/Y"
                            @selected(old('date_format', $settings->date_format) === 'd/m/Y')>
                            DD/MM/YYYY
                        </option>

                        <option value="d-m-Y"
                            @selected(old('date_format', $settings->date_format) === 'd-m-Y')>
                            DD-MM-YYYY
                        </option>

                        <option value="Y-m-d"
                            @selected(old('date_format', $settings->date_format) === 'Y-m-d')>
                            YYYY-MM-DD
                        </option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="settings-actions">

        <div class="settings-meta">
            @if($settings->updated_at)
                Last updated:
                {{ $settings->updated_at->format('d M Y, h:i A') }}
            @endif
        </div>

        <button type="submit" class="settings-save">
            Save Business Settings
        </button>

    </div>

</form>

@endsection
