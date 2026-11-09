@extends('layouts.admin')

@section('title', 'Edit Branch')
@section('header', 'Branch / Location Management')

@push('styles')
<style>
    .branch-edit-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .branch-edit-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .branch-edit-heading p {
        margin: 0;
        font-size: 13px;
        color: #718377;
        line-height: 1.7;
    }

    .branch-edit-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    .branch-edit-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .branch-edit-card-header {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
    }

    .branch-edit-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .branch-edit-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.6;
        color: #7d8e82;
    }

    .branch-edit-card-body {
        padding: 22px;
    }

    .branch-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .branch-edit-field {
        min-width: 0;
    }

    .branch-edit-field.full {
        grid-column: 1 / -1;
    }

    .branch-edit-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .branch-edit-required {
        color: #b42323;
    }

    .branch-edit-field input,
    .branch-edit-field select,
    .branch-edit-field textarea {
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

    .branch-edit-field input,
    .branch-edit-field select {
        min-height: 44px;
    }

    .branch-edit-field textarea {
        min-height: 105px;
        resize: vertical;
    }

    .branch-edit-field input:focus,
    .branch-edit-field select:focus,
    .branch-edit-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .branch-edit-field input[readonly] {
        background: #f5f8f6;
        color: #637b69;
        cursor: not-allowed;
    }

    .branch-edit-help {
        margin-top: 7px;
        font-size: 11px;
        color: #859389;
        line-height: 1.6;
    }

    .branch-edit-error {
        margin-top: 6px;
        font-size: 11px;
        color: #b42323;
    }

    .branch-edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 18px 22px;
        border-top: 1px solid #e2ebe5;
        background: #fbfdfb;
    }

    .branch-edit-btn {
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

    .branch-edit-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .branch-edit-btn.primary:hover {
        background: #0d5031;
        color: #fff;
    }

    .branch-edit-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #476151;
    }

    .branch-edit-btn.secondary:hover {
        background: #f1f7f3;
    }

    .branch-edit-code {
        padding: 18px;
        background: #f1f8f3;
        border: 1px dashed #b8d9c3;
        border-radius: 9px;
    }

    .branch-edit-code-label {
        font-size: 11px;
        font-weight: 750;
        color: #637b69;
        margin-bottom: 8px;
    }

    .branch-edit-code-value {
        font-size: 24px;
        font-weight: 800;
        letter-spacing: .5px;
        color: #11643e;
    }

    .branch-edit-info {
        font-size: 12px;
        line-height: 1.9;
        color: #64786a;
    }

    .branch-edit-info strong {
        color: #254532;
    }

    .branch-edit-info ul {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .branch-edit-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
        font-size: 12px;
        line-height: 1.7;
    }

    .branch-edit-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .branch-edit-meta {
        display: grid;
        gap: 13px;
    }

    .branch-edit-meta-item {
        border-bottom: 1px solid #edf2ee;
        padding-bottom: 12px;
    }

    .branch-edit-meta-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .branch-edit-meta-label {
        font-size: 11px;
        color: #859389;
        margin-bottom: 5px;
    }

    .branch-edit-meta-value {
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    @media (max-width: 1000px) {
        .branch-edit-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .branch-edit-grid {
            grid-template-columns: 1fr;
        }

        .branch-edit-actions {
            flex-wrap: wrap;
        }

        .branch-edit-btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

<div class="branch-edit-heading">
    <div>
        <h1>Edit Branch / Location</h1>
        <p>
            Update the operating information for
            {{ $branch->branch_name }}.
        </p>
    </div>

    <a href="{{ route('admin.branches.index') }}"
       class="branch-edit-btn secondary">
        ← Back to Branch Register
    </a>
</div>

@if($errors->any())
    <div class="branch-edit-alert">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('admin.branches.update', $branch) }}">

    @csrf
    @method('PUT')

    <div class="branch-edit-layout">

        <div>

            {{-- BRANCH IDENTIFICATION --}}
            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Branch Identification
                    </div>
                    <div class="branch-edit-card-description">
                        Update the branch name and operational status.
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-grid">

                        <div class="branch-edit-field">
                            <label for="branch_code">
                                Branch Code
                            </label>
                            <input
                                type="text"
                                id="branch_code"
                                value="{{ $branch->branch_code }}"
                                readonly>
                            <div class="branch-edit-help">
                                System-generated and cannot be changed.
                            </div>
                        </div>

                        <div class="branch-edit-field">
                            <label for="status">
                                Operational Status
                                <span class="branch-edit-required">*</span>
                            </label>

                            <select id="status" name="status" required>
                                <option value="active"
                                    @selected(old('status', $branch->status) === 'active')>
                                    Active
                                </option>
                                <option value="inactive"
                                    @selected(old('status', $branch->status) === 'inactive')>
                                    Inactive
                                </option>
                            </select>

                            @error('status')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-edit-field full">
                            <label for="branch_name">
                                Branch / Location Name
                                <span class="branch-edit-required">*</span>
                            </label>

                            <input
                                type="text"
                                id="branch_name"
                                name="branch_name"
                                maxlength="150"
                                required
                                value="{{ old('branch_name', $branch->branch_name) }}">

                            @error('branch_name')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- LOCATION INFORMATION --}}
            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Location Information
                    </div>
                    <div class="branch-edit-card-description">
                        Update the physical location of the LPG facility.
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-grid">

                        <div class="branch-edit-field full">
                            <label for="address">
                                Physical Address
                            </label>

                            <input
                                type="text"
                                id="address"
                                name="address"
                                maxlength="255"
                                value="{{ old('address', $branch->address) }}"
                                placeholder="Street address or facility location">

                            @error('address')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-edit-field">
                            <label for="city">
                                City / Town
                            </label>

                            <input
                                type="text"
                                id="city"
                                name="city"
                                maxlength="100"
                                value="{{ old('city', $branch->city) }}">

                            @error('city')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-edit-field">
                            <label for="state">
                                State
                            </label>

                            <input
                                type="text"
                                id="state"
                                name="state"
                                maxlength="100"
                                value="{{ old('state', $branch->state) }}">

                            @error('state')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- CONTACT INFORMATION --}}
            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Branch Contact Information
                    </div>
                    <div class="branch-edit-card-description">
                        Update contact details and additional notes.
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-grid">

                        <div class="branch-edit-field">
                            <label for="phone">
                                Contact Phone
                            </label>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                maxlength="30"
                                value="{{ old('phone', $branch->phone) }}">

                            @error('phone')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-edit-field">
                            <label for="email">
                                Contact Email
                            </label>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                maxlength="150"
                                value="{{ old('email', $branch->email) }}">

                            @error('email')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="branch-edit-field full">
                            <label for="notes">
                                Additional Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                maxlength="5000"
                                placeholder="Additional branch information">{{ old('notes', $branch->notes) }}</textarea>

                            @error('notes')
                                <div class="branch-edit-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- ACTIONS --}}
            <div class="branch-edit-card">
                <div class="branch-edit-actions">

                    <a href="{{ route('admin.branches.index') }}"
                       class="branch-edit-btn secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            class="branch-edit-btn primary">
                        Save Branch Changes
                    </button>

                </div>
            </div>

        </div>

        {{-- SIDEBAR --}}
        <div>

            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Branch Identification
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-code">
                        <div class="branch-edit-code-label">
                            OFFICIAL BRANCH CODE
                        </div>

                        <div class="branch-edit-code-value">
                            {{ $branch->branch_code }}
                        </div>

                        <div class="branch-edit-help">
                            This code remains permanently associated
                            with the branch.
                        </div>
                    </div>
                </div>
            </div>

            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Registration Details
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-meta">

                        <div class="branch-edit-meta-item">
                            <div class="branch-edit-meta-label">
                                Date Registered
                            </div>
                            <div class="branch-edit-meta-value">
                                {{ $branch->created_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>

                        <div class="branch-edit-meta-item">
                            <div class="branch-edit-meta-label">
                                Last Updated
                            </div>
                            <div class="branch-edit-meta-value">
                                {{ $branch->updated_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>

                        <div class="branch-edit-meta-item">
                            <div class="branch-edit-meta-label">
                                Current Status
                            </div>
                            <div class="branch-edit-meta-value">
                                {{ ucfirst($branch->status) }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="branch-edit-card">
                <div class="branch-edit-card-header">
                    <div class="branch-edit-card-title">
                        Operational Controls
                    </div>
                </div>

                <div class="branch-edit-card-body">
                    <div class="branch-edit-info">
                        <strong>Important:</strong>

                        <ul>
                            <li>
                                Branch identification codes cannot be changed.
                            </li>
                            <li>
                                Branch information changes are recorded
                                in the audit trail.
                            </li>
                            <li>
                                Inactive branches should not accept
                                new sales or stock transactions.
                            </li>
                            <li>
                                Existing historical records are retained
                                when a branch is deactivated.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>

</form>

@endsection
