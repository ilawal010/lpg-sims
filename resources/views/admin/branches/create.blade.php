@extends('layouts.admin')

@section('title', 'Register New Branch')
@section('header', 'Branch / Location Management')

@push('styles')
<style>
    .branch-form-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
    }

    .branch-form-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .branch-form-heading p {
        margin: 0;
        color: #718377;
        font-size: 13px;
        line-height: 1.7;
    }

    .branch-form-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    .branch-form-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .branch-form-card-header {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
    }

    .branch-form-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .branch-form-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.6;
        color: #7d8e82;
    }

    .branch-form-card-body {
        padding: 22px;
    }

    .branch-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .branch-form-field {
        min-width: 0;
    }

    .branch-form-field.full {
        grid-column: 1 / -1;
    }

    .branch-form-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .branch-form-required {
        color: #b42323;
    }

    .branch-form-field input,
    .branch-form-field select,
    .branch-form-field textarea {
        display: block;
        width: 100%;
        padding: 12px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .branch-form-field input,
    .branch-form-field select {
        min-height: 44px;
    }

    .branch-form-field textarea {
        min-height: 105px;
        resize: vertical;
    }

    .branch-form-field input:focus,
    .branch-form-field select:focus,
    .branch-form-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .branch-form-field input[readonly] {
        background: #f5f8f6;
        color: #637b69;
        cursor: not-allowed;
    }

    .branch-form-help {
        margin-top: 7px;
        font-size: 11px;
        color: #859389;
        line-height: 1.6;
    }

    .branch-form-error {
        margin-top: 6px;
        font-size: 11px;
        color: #b42323;
    }

    .branch-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 22px;
        border-top: 1px solid #e2ebe5;
        background: #fbfdfb;
    }

    .branch-form-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 19px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-family: inherit;
        font-size: 12px;
        font-weight: 750;
        text-decoration: none;
        cursor: pointer;
        box-sizing: border-box;
    }

    .branch-form-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .branch-form-btn.primary:hover {
        background: #0d5031;
        color: #fff;
    }

    .branch-form-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #476151;
    }

    .branch-form-btn.secondary:hover {
        background: #f1f7f3;
    }

    .branch-form-code {
        padding: 18px;
        background: #f1f8f3;
        border: 1px dashed #b8d9c3;
        border-radius: 9px;
        margin-bottom: 17px;
    }

    .branch-form-code-label {
        font-size: 11px;
        font-weight: 750;
        color: #637b69;
        margin-bottom: 8px;
    }

    .branch-form-code-value {
        font-size: 21px;
        font-weight: 800;
        letter-spacing: .5px;
        color: #11643e;
    }

    .branch-form-code-note {
        margin-top: 8px;
        font-size: 11px;
        line-height: 1.7;
        color: #718377;
    }

    .branch-form-info {
        font-size: 12px;
        color: #64786a;
        line-height: 1.9;
    }

    .branch-form-info strong {
        color: #254532;
    }

    .branch-form-info ul {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .branch-form-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
        font-size: 12px;
        line-height: 1.7;
    }

    .branch-form-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    @media (max-width: 1000px) {
        .branch-form-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .branch-form-grid {
            grid-template-columns: 1fr;
        }

        .branch-form-actions {
            flex-wrap: wrap;
        }

        .branch-form-btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

<div class="branch-form-heading">

    <div>
        <h1>Register New Branch</h1>
        <p>
            Create a new operating location for LPG storage,
            dispensing and inventory management.
        </p>
    </div>

    <a href="{{ route('admin.branches.index') }}"
       class="branch-form-btn secondary">
        ← Back to Branch Register
    </a>

</div>

@if($errors->any())
    <div class="branch-form-alert">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('admin.branches.store') }}">

    @csrf

    <div class="branch-form-layout">

        {{-- MAIN REGISTRATION FORM --}}
        <div>

            {{-- BRANCH INFORMATION --}}
            <div class="branch-form-card">

                <div class="branch-form-card-header">
                    <div class="branch-form-card-title">
                        Branch Identification
                    </div>

                    <div class="branch-form-card-description">
                        Enter the official name and operational status
                        of the new branch.
                    </div>
                </div>

                <div class="branch-form-card-body">

                    <div class="branch-form-grid">

                        <div class="branch-form-field full">
                            <label for="branch_name">
                                Branch / Location Name
                                <span class="branch-form-required">*</span>
                            </label>

                            <input
                                type="text"
                                id="branch_name"
                                name="branch_name"
                                maxlength="150"
                                required
                                value="{{ old('branch_name') }}"
                                placeholder="e.g. Intisar Oil & Gas - Main Station">

                            @error('branch_name')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-form-field full">
                            <label for="status">
                                Operational Status
                                <span class="branch-form-required">*</span>
                            </label>

                            <select id="status"
                                    name="status"
                                    required>

                                <option value="active"
                                    @selected(old('status', 'active') === 'active')>
                                    Active
                                </option>

                                <option value="inactive"
                                    @selected(old('status') === 'inactive')>
                                    Inactive
                                </option>

                            </select>

                            <div class="branch-form-help">
                                Only active branches should be available
                                for new operational transactions.
                            </div>

                            @error('status')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                </div>
            </div>

            {{-- LOCATION DETAILS --}}
            <div class="branch-form-card">

                <div class="branch-form-card-header">
                    <div class="branch-form-card-title">
                        Location Information
                    </div>

                    <div class="branch-form-card-description">
                        Provide the physical address of the LPG facility.
                    </div>
                </div>

                <div class="branch-form-card-body">

                    <div class="branch-form-grid">

                        <div class="branch-form-field full">
                            <label for="address">
                                Physical Address
                            </label>

                            <input
                                type="text"
                                id="address"
                                name="address"
                                maxlength="255"
                                value="{{ old('address') }}"
                                placeholder="Street address or facility location">

                            @error('address')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-form-field">
                            <label for="city">
                                City / Town
                            </label>

                            <input
                                type="text"
                                id="city"
                                name="city"
                                maxlength="100"
                                value="{{ old('city') }}"
                                placeholder="Enter city or town">

                            @error('city')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-form-field">
                            <label for="state">
                                State
                            </label>

                            <input
                                type="text"
                                id="state"
                                name="state"
                                maxlength="100"
                                value="{{ old('state') }}"
                                placeholder="Enter state">

                            @error('state')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                </div>
            </div>

            {{-- CONTACT INFORMATION --}}
            <div class="branch-form-card">

                <div class="branch-form-card-header">
                    <div class="branch-form-card-title">
                        Branch Contact Information
                    </div>

                    <div class="branch-form-card-description">
                        Optional contact details for this operating location.
                    </div>
                </div>

                <div class="branch-form-card-body">

                    <div class="branch-form-grid">

                        <div class="branch-form-field">
                            <label for="phone">
                                Contact Phone
                            </label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                maxlength="30"
                                value="{{ old('phone') }}"
                                placeholder="e.g. 08012345678">

                            @error('phone')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-form-field">
                            <label for="email">
                                Contact Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                maxlength="150"
                                value="{{ old('email') }}"
                                placeholder="branch@example.com">

                            @error('email')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-form-field full">
                            <label for="notes">
                                Additional Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                maxlength="5000"
                                placeholder="Enter any relevant information about this branch.">{{ old('notes') }}</textarea>

                            @error('notes')
                                <div class="branch-form-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>

                </div>

            </div>

            {{-- FORM ACTIONS --}}
            <div class="branch-form-card">

                <div class="branch-form-actions">

                    <a href="{{ route('admin.branches.index') }}"
                       class="branch-form-btn secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            class="branch-form-btn primary">
                        Register Branch
                    </button>

                </div>

            </div>

        </div>

        {{-- INFORMATION SIDEBAR --}}
        <div>

            <div class="branch-form-card">

                <div class="branch-form-card-header">
                    <div class="branch-form-card-title">
                        Automatic Branch Code
                    </div>

                    <div class="branch-form-card-description">
                        System-generated branch identification.
                    </div>
                </div>

                <div class="branch-form-card-body">

                    <div class="branch-form-code">

                        <div class="branch-form-code-label">
                            BRANCH CODE FORMAT
                        </div>

                        <div class="branch-form-code-value">
                            INT-B###
                        </div>

                        <div class="branch-form-code-note">
                            The actual code will be assigned
                            automatically when the branch is saved.
                        </div>

                    </div>

                    <div class="branch-form-info">
                        <strong>Code Examples</strong>
                        <ul>
                            <li>INT-B001 — First branch</li>
                            <li>INT-B002 — Second branch</li>
                            <li>INT-B003 — Third branch</li>
                        </ul>
                    </div>

                </div>
            </div>

            <div class="branch-form-card">

                <div class="branch-form-card-header">
                    <div class="branch-form-card-title">
                        Branch Registration Notes
                    </div>
                </div>

                <div class="branch-form-card-body">

                    <div class="branch-form-info">
                        <strong>Important Information</strong>

                        <ul>
                            <li>
                                Each branch receives a unique
                                identification code.
                            </li>

                            <li>
                                A branch can accommodate multiple
                                LPG storage tanks and cylinders.
                            </li>

                            <li>
                                Inventory quantities will be
                                tracked against registered
                                storage units.
                            </li>

                            <li>
                                Branch creation and subsequent
                                updates are recorded in
                                the system audit trail.
                            </li>

                            <li>
                                Branches can be deactivated
                                without deleting their records.
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>

    </div>

</form>

@endsection
