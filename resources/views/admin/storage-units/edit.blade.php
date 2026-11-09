@extends('layouts.admin')

@section('title', 'Edit LPG Storage Unit')
@section('header', 'LPG Storage Unit Management')

@push('styles')
<style>
    .sue-heading {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .sue-heading h1 {
        margin: 0 0 6px;
        color: #183d2a;
        font-size: 23px;
        font-weight: 750;
    }

    .sue-heading p {
        margin: 0;
        color: #748679;
        font-size: 13px;
        line-height: 1.7;
    }

    .sue-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    .sue-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .sue-card-head {
        padding: 18px 22px;
        background: #f8fbf9;
        border-bottom: 1px solid #e2ebe5;
    }

    .sue-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .sue-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.6;
        color: #829085;
    }

    .sue-card-body {
        padding: 22px;
    }

    .sue-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .sue-field {
        min-width: 0;
    }

    .sue-field.full {
        grid-column: 1 / -1;
    }

    .sue-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .sue-required {
        color: #b42323;
    }

    .sue-field input,
    .sue-field select,
    .sue-field textarea {
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

    .sue-field textarea {
        min-height: 105px;
        resize: vertical;
    }

    .sue-field input:focus,
    .sue-field select:focus,
    .sue-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .sue-field input[readonly] {
        background: #f5f8f6;
        color: #637b69;
        cursor: not-allowed;
    }

    .sue-help {
        margin-top: 7px;
        color: #859389;
        font-size: 11px;
        line-height: 1.6;
    }

    .sue-error {
        margin-top: 6px;
        color: #b42323;
        font-size: 11px;
    }

    .sue-btn {
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

    .sue-btn.primary {
        background: #11643e;
        color: #fff;
    }

    .sue-btn.primary:hover {
        background: #0d5031;
        color: #fff;
    }

    .sue-btn.secondary {
        background: #fff;
        border-color: #d8e4dc;
        color: #476151;
    }

    .sue-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 10px;
        padding: 18px 22px;
        border-top: 1px solid #e2ebe5;
        background: #fbfdfb;
    }

    .sue-info {
        font-size: 12px;
        color: #64786a;
        line-height: 1.9;
    }

    .sue-info ul {
        margin: 10px 0 0;
        padding-left: 18px;
    }

    .sue-code {
        padding: 18px;
        background: #f1f8f3;
        border: 1px dashed #b8d9c3;
        border-radius: 9px;
    }

    .sue-code strong {
        display: block;
        margin-top: 7px;
        font-size: 23px;
        color: #11643e;
    }

    .sue-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
        font-size: 12px;
        line-height: 1.7;
    }

    .sue-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .sue-meta {
        display: grid;
        gap: 13px;
    }

    .sue-meta-item {
        padding-bottom: 12px;
        border-bottom: 1px solid #edf2ee;
    }

    .sue-meta-item:last-child {
        padding-bottom: 0;
        border-bottom: none;
    }

    .sue-meta-label {
        font-size: 11px;
        color: #859389;
        margin-bottom: 5px;
    }

    .sue-meta-value {
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    @media (max-width: 1000px) {
        .sue-layout {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 650px) {
        .sue-grid {
            grid-template-columns: 1fr;
        }

        .sue-actions .sue-btn {
            flex: 1;
        }
    }
</style>
@endpush

@section('content')

<div class="sue-heading">
    <div>
        <h1>Edit LPG Storage Unit</h1>
        <p>
            Update {{ $storageUnit->storage_name }}
            ({{ $storageUnit->storage_code }}).
        </p>
    </div>

    <a href="{{ route('admin.storage-units.index') }}"
       class="sue-btn secondary">
        ← Back to Storage Register
    </a>
</div>

@if($errors->any())
    <div class="sue-alert">
        <strong>Please correct the following:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST"
      action="{{ route('admin.storage-units.update', $storageUnit) }}">

    @csrf
    @method('PUT')

    {{-- Required identifiers are submitted unchanged.
         The controller verifies both values. --}}
    <input type="hidden"
           name="branch_id"
           value="{{ $storageUnit->branch_id }}">

    <input type="hidden"
           name="storage_type"
           value="{{ $storageUnit->storage_type }}">

    <div class="sue-layout">

        <div>

            {{-- STORAGE IDENTIFICATION --}}
            <div class="sue-card">
                <div class="sue-card-head">
                    <div class="sue-card-title">
                        Storage Unit Identification
                    </div>
                    <div class="sue-card-description">
                        Review the permanent identification details
                        and update the storage unit name.
                    </div>
                </div>

                <div class="sue-card-body">
                    <div class="sue-grid">

                        <div class="sue-field">
                            <label>Storage Code</label>
                            <input
                                type="text"
                                value="{{ $storageUnit->storage_code }}"
                                readonly>
                            <div class="sue-help">
                                Automatically generated and immutable.
                            </div>
                        </div>

                        <div class="sue-field">
                            <label>Storage Type</label>
                            <input
                                type="text"
                                value="{{ $storageUnit->storage_type === 'tank'
                                    ? 'LPG Storage Tank'
                                    : 'LPG Storage Cylinder' }}"
                                readonly>
                        </div>

                        <div class="sue-field full">
                            <label>Branch / Location</label>
                            <input
                                type="text"
                                value="{{ $storageUnit->branch?->branch_code }}
                                    — {{ $storageUnit->branch?->branch_name }}"
                                readonly>
                            <div class="sue-help">
                                Storage units cannot be reassigned to
                                another branch through ordinary editing.
                            </div>
                        </div>

                        <div class="sue-field full">
                            <label for="storage_name">
                                Storage Unit Name
                                <span class="sue-required">*</span>
                            </label>

                            <input
                                type="text"
                                id="storage_name"
                                name="storage_name"
                                maxlength="150"
                                required
                                value="{{ old('storage_name', $storageUnit->storage_name) }}">

                            @error('storage_name')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sue-field full">
                            <label for="capacity_kg">
                                Rated Capacity (kg)
                                <span class="sue-required">*</span>
                            </label>

                            <input
                                type="number"
                                id="capacity_kg"
                                name="capacity_kg"
                                min="0.001"
                                max="999999999999.999"
                                step="0.001"
                                required
                                value="{{ old('capacity_kg', $storageUnit->capacity_kg) }}">

                            <div class="sue-help">
                                Rated storage capacity, not current LPG stock.
                            </div>

                            @error('capacity_kg')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            {{-- TECHNICAL DETAILS --}}
            <div class="sue-card">
                <div class="sue-card-head">
                    <div class="sue-card-title">
                        Technical Information
                    </div>
                    <div class="sue-card-description">
                        Maintain the equipment's technical and
                        manufacturer information.
                    </div>
                </div>

                <div class="sue-card-body">
                    <div class="sue-grid">

                        <div class="sue-field">
                            <label for="manufacturer">
                                Manufacturer
                            </label>

                            <input
                                type="text"
                                id="manufacturer"
                                name="manufacturer"
                                maxlength="150"
                                value="{{ old('manufacturer', $storageUnit->manufacturer) }}">

                            @error('manufacturer')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sue-field">
                            <label for="serial_number">
                                Serial Number
                            </label>

                            <input
                                type="text"
                                id="serial_number"
                                name="serial_number"
                                maxlength="100"
                                value="{{ old('serial_number', $storageUnit->serial_number) }}">

                            @error('serial_number')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sue-field">
                            <label for="installation_date">
                                Installation Date
                            </label>

                            <input
                                type="date"
                                id="installation_date"
                                name="installation_date"
                                value="{{ old(
                                    'installation_date',
                                    $storageUnit->installation_date?->format('Y-m-d')
                                ) }}">

                            @error('installation_date')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sue-field">
                            <label for="status">
                                Operational Status
                                <span class="sue-required">*</span>
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
                                        @selected(old('status', $storageUnit->status) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('status')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="sue-field full">
                            <label for="notes">
                                Additional Notes
                            </label>

                            <textarea
                                id="notes"
                                name="notes"
                                maxlength="5000"
                                placeholder="Optional technical notes">{{ old('notes', $storageUnit->notes) }}</textarea>

                            @error('notes')
                                <div class="sue-error">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                </div>
            </div>

            <div class="sue-card">
                <div class="sue-actions">
                    <a href="{{ route('admin.storage-units.index') }}"
                       class="sue-btn secondary">
                        Cancel
                    </a>

                    <button type="submit"
                            class="sue-btn primary">
                        Save Storage Unit Changes
                    </button>
                </div>
            </div>

        </div>

        {{-- INFORMATION SIDEBAR --}}
        <div>

            <div class="sue-card">
                <div class="sue-card-head">
                    <div class="sue-card-title">
                        Official Storage Code
                    </div>
                </div>

                <div class="sue-card-body">
                    <div class="sue-code">
                        <div class="sue-help">
                            REGISTERED IDENTIFIER
                        </div>
                        <strong>
                            {{ $storageUnit->storage_code }}
                        </strong>
                        <div class="sue-help">
                            This identifier remains associated
                            with the storage unit.
                        </div>
                    </div>
                </div>
            </div>

            <div class="sue-card">
                <div class="sue-card-head">
                    <div class="sue-card-title">
                        Registration Details
                    </div>
                </div>

                <div class="sue-card-body">
                    <div class="sue-meta">

                        <div class="sue-meta-item">
                            <div class="sue-meta-label">
                                Date Registered
                            </div>
                            <div class="sue-meta-value">
                                {{ $storageUnit->created_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>

                        <div class="sue-meta-item">
                            <div class="sue-meta-label">
                                Last Updated
                            </div>
                            <div class="sue-meta-value">
                                {{ $storageUnit->updated_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>

                        <div class="sue-meta-item">
                            <div class="sue-meta-label">
                                Current Status
                            </div>
                            <div class="sue-meta-value">
                                {{ ucfirst($storageUnit->status) }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="sue-card">
                <div class="sue-card-head">
                    <div class="sue-card-title">
                        Operational Controls
                    </div>
                </div>

                <div class="sue-card-body">
                    <div class="sue-info">
                        <ul>
                            <li>
                                Storage code and storage type
                                cannot be changed.
                            </li>
                            <li>
                                Branch assignment is protected.
                            </li>
                            <li>
                                Changes are recorded in the audit trail.
                            </li>
                            <li>
                                Rated capacity is separate from
                                the actual LPG stock balance.
                            </li>
                            <li>
                                Inventory transactions will be
                                managed through the stock ledger.
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

    </div>

</form>

@endsection
