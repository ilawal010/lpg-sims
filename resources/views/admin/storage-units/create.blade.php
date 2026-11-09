@extends('layouts.admin')

@section('title', 'Register LPG Storage Unit')
@section('header', 'LPG Storage Unit Management')

@push('styles')
<style>
    .su-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .su-heading h1 {
        margin: 0 0 6px;
        color: #183d2a;
        font-size: 23px;
        font-weight: 750;
    }

    .su-heading p {
        margin: 0;
        color: #748679;
        font-size: 13px;
        line-height: 1.7;
    }

    .su-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    .su-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .su-card-head {
        padding: 18px 22px;
        background: #f8fbf9;
        border-bottom: 1px solid #e2ebe5;
    }

    .su-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .su-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.6;
        color: #829085;
    }

    .su-card-body {
        padding: 22px;
    }

    .su-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .su-field {
        min-width: 0;
    }

    .su-field.full {
        grid-column: 1 / -1;
    }

    .su-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .su-required {
        color: #b42323;
    }

    .su-field input,
    .su-field select,
    .su-field textarea {
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

    .su-field textarea {
        min-height: 105px;
        resize: vertical;
    }

    .su-field input:focus,
    .su-field select:focus,
    .su-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .su-help {
        margin-top: 7px;
        color: #859389;
        font-size: 11px;
        line-height: 1.6;
    }

    .su-error {
        margin-top: 6px;
        color: #b42323;
        font-size: 11px;
    }

    .su-btn {
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

    .su-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .su-btn.primary:hover {
        background: #0d5031;
        color: #fff;
    }

    .su-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #476151;
    }

    .su-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 18px 22px;
        border-top: 1px solid #e2ebe5;
        background: #fbfdfb;
    }

    .su-info {
        font-size: 12px;
        color: #64786a;
        line-height: 1.9;
    }

    .su-info strong {
        color: #254532;
    }

    .su-info ul {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .su-code-preview {
        padding: 18px;
        background: #f1f8f3;
        border: 1px dashed #b8d9c3;
        border-radius: 9px;
    }

    .su-code-preview strong {
        display: block;
        color: #11643e;
        font-size: 19px;
        margin: 7px 0;
    }

    .su-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        font-size: 12px;
        line-height: 1.7;
    }

    .su-alert.error {
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
    }

    .su-alert.warning {
        background: #fff8e8;
        border: 1px solid #f0dfad;
        color: #8c651d;
    }

    .su-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    @media (max-width: 1000px) {
        .su-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .su-grid {
            grid-template-columns: 1fr;
        }

        .su-actions .su-btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

<div class="su-heading">
    <div>
        <h1>Register LPG Storage Unit</h1>
        <p>
            Register a storage tank or storage cylinder
            under an existing operating branch.
        </p>
    </div>

    <a href="{{ route('admin.storage-units.index') }}"
       class="su-btn secondary">
        ← Back to Storage Register
    </a>
</div>

@if($errors->any())
    <div class="su-alert error">
        <strong>Please correct the following:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if($branches->isEmpty())
    <div class="su-alert warning">
        There are no active branches available.
        Register or activate a branch before adding an LPG storage unit.

        <a href="{{ route('admin.branches.index') }}">
            Open Branch Management
        </a>
    </div>
@endif

<form method="POST"
      action="{{ route('admin.storage-units.store') }}">

    @csrf

    <div class="su-layout">

        <div>

            {{-- STORAGE IDENTIFICATION --}}
            <div class="su-card">
                <div class="su-card-head">
                    <div class="su-card-title">
                        Storage Unit Identification
                    </div>
                    <div class="su-card-description">
                        Select the branch and identify the storage facility.
                    </div>
                </div>

                <div class="su-card-body">
                    <div class="su-grid">

                        <div class="su-field full">
                            <label for="branch_id">
                                Branch / Location
                                <span class="su-required">*</span>
                            </label>

                            <select
                                id="branch_id"
                                name="branch_id"
                                required>

                                <option value="">
                                    Select operating branch
                                </option>

                                @foreach($branches as $branch)
                                    <option
                                        value="{{ $branch->id }}"
                                        @selected((string) old('branch_id') === (string) $branch->id)>
                                        {{ $branch->branch_code }}
                                        — {{ $branch->branch_name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('branch_id')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field">
                            <label for="storage_type">
                                Storage Type
                                <span class="su-required">*</span>
                            </label>

                            <select
                                id="storage_type"
                                name="storage_type"
                                required>

                                <option value="">
                                    Select storage type
                                </option>

                                <option value="tank"
                                    @selected(old('storage_type') === 'tank')>
                                    LPG Storage Tank
                                </option>

                                <option value="cylinder"
                                    @selected(old('storage_type') === 'cylinder')>
                                    LPG Storage Cylinder
                                </option>
                            </select>

                            @error('storage_type')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field">
                            <label for="capacity_kg">
                                Rated Capacity (kg)
                                <span class="su-required">*</span>
                            </label>

                            <input
                                type="number"
                                id="capacity_kg"
                                name="capacity_kg"
                                min="0.001"
                                max="999999999999.999"
                                step="0.001"
                                required
                                value="{{ old('capacity_kg') }}"
                                placeholder="e.g. 5000">

                            <div class="su-help">
                                Enter rated LPG capacity in kilograms,
                                not the current stock quantity.
                            </div>

                            @error('capacity_kg')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field full">
                            <label for="storage_name">
                                Storage Unit Name
                                <span class="su-required">*</span>
                            </label>

                            <input
                                type="text"
                                id="storage_name"
                                name="storage_name"
                                maxlength="150"
                                required
                                value="{{ old('storage_name') }}"
                                placeholder="e.g. Main LPG Tank 1">

                            @error('storage_name')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- TECHNICAL DETAILS --}}
            <div class="su-card">
                <div class="su-card-head">
                    <div class="su-card-title">
                        Technical Information
                    </div>
                    <div class="su-card-description">
                        Optional identification and installation details.
                    </div>
                </div>

                <div class="su-card-body">
                    <div class="su-grid">

                        <div class="su-field">
                            <label for="manufacturer">
                                Manufacturer
                            </label>

                            <input
                                type="text"
                                id="manufacturer"
                                name="manufacturer"
                                maxlength="150"
                                value="{{ old('manufacturer') }}"
                                placeholder="Manufacturer name">

                            @error('manufacturer')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field">
                            <label for="serial_number">
                                Serial Number
                            </label>

                            <input
                                type="text"
                                id="serial_number"
                                name="serial_number"
                                maxlength="100"
                                value="{{ old('serial_number') }}"
                                placeholder="Manufacturer serial number">

                            @error('serial_number')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field">
                            <label for="installation_date">
                                Installation Date
                            </label>

                            <input
                                type="date"
                                id="installation_date"
                                name="installation_date"
                                value="{{ old('installation_date') }}">

                            @error('installation_date')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field">
                            <label for="status">
                                Operational Status
                                <span class="su-required">*</span>
                            </label>

                            <select
                                id="status"
                                name="status"
                                required>

                                @foreach([
                                    'active' => 'Active',
                                    'inactive' => 'Inactive',
                                    'maintenance' => 'Under Maintenance',
                                    'decommissioned' => 'Decommissioned',
                                ] as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(old('status', 'active') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('status')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="su-field full">
                            <label for="notes">
                                Additional Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                maxlength="5000"
                                placeholder="Optional storage unit information">{{ old('notes') }}</textarea>

                            @error('notes')
                                <div class="su-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="su-card">
                <div class="su-actions">
                    <a href="{{ route('admin.storage-units.index') }}"
                       class="su-btn secondary">
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="su-btn primary"
                        @disabled($branches->isEmpty())>
                        Register Storage Unit
                    </button>
                </div>
            </div>

        </div>

        {{-- INFORMATION SIDEBAR --}}
        <div>

            <div class="su-card">
                <div class="su-card-head">
                    <div class="su-card-title">
                        Automatic Storage Codes
                    </div>
                </div>

                <div class="su-card-body">
                    <div class="su-code-preview">
                        <div class="su-help">
                            LPG STORAGE TANK
                        </div>
                        <strong>INT-T001</strong>

                        <div class="su-help">
                            LPG STORAGE CYLINDER
                        </div>
                        <strong>INT-C001</strong>

                        <div class="su-help">
                            These are examples. The actual next
                            available code is generated when
                            registration is submitted.
                        </div>
                    </div>
                </div>
            </div>

            <div class="su-card">
                <div class="su-card-head">
                    <div class="su-card-title">
                        Registration Guidelines
                    </div>
                </div>

                <div class="su-card-body">
                    <div class="su-info">
                        <ul>
                            <li>
                                Each storage unit must belong to
                                an active registered branch.
                            </li>
                            <li>
                                Storage tanks and cylinders are
                                registered individually.
                            </li>
                            <li>
                                Storage codes are generated automatically.
                            </li>
                            <li>
                                Capacity must be entered in kilograms.
                            </li>
                            <li>
                                Stock balances are not entered
                                during storage-unit registration.
                            </li>
                            <li>
                                All registrations are recorded
                                in the audit trail.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="su-card">
                <div class="su-card-head">
                    <div class="su-card-title">
                        Inventory Management
                    </div>
                </div>

                <div class="su-card-body">
                    <div class="su-info">
                        <strong>Coming in subsequent steps:</strong>
                        <ul>
                            <li>Opening LPG stock</li>
                            <li>Stock receipts</li>
                            <li>Storage-unit balances</li>
                            <li>LPG dispensing records</li>
                            <li>Inter-unit stock transfers</li>
                            <li>Stock adjustments and reconciliation</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>

</form>

@endsection
