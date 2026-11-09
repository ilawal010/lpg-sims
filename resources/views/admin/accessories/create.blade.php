@extends('layouts.admin')

@section('title', 'Add Accessory')
@section('header', 'Accessories Management')

@push('styles')
<style>
    .acc-form-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .acc-form-title {
        font-size: 23px;
        font-weight: 750;
        color: #20352b;
        margin: 0;
    }

    .acc-form-subtitle {
        color: #78877e;
        font-size: 13px;
        margin-top: 7px;
    }

    .acc-form-card {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 28px;
        margin-bottom: 20px;
    }

    .acc-section-title {
        font-size: 15px;
        font-weight: 750;
        color: #11633e;
        margin: 0 0 20px;
        padding-bottom: 12px;
        border-bottom: 1px solid #e3ebe6;
    }

    .acc-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .acc-form-field {
        min-width: 0;
    }

    .acc-form-field.full {
        grid-column: 1 / -1;
    }

    .acc-form-field label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #20352b;
        margin-bottom: 8px;
    }

    .acc-form-control {
        width: 100%;
        min-height: 43px;
        border: 1px solid #d9e5dd;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        color: #20352b;
        background: #fff;
    }

    .acc-form-control:focus {
        outline: none;
        border-color: #11633e;
        box-shadow: 0 0 0 3px rgba(17, 99, 62, .09);
    }

    textarea.acc-form-control {
        min-height: 100px;
        resize: vertical;
    }

    .acc-form-help {
        color: #78877e;
        font-size: 11px;
        margin-top: 6px;
    }

    .acc-required {
        color: #b42318;
    }

    .acc-error {
        color: #b42318;
        font-size: 11px;
        margin-top: 6px;
    }

    .acc-form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }

    .acc-form-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 43px;
        padding: 11px 20px;
        border-radius: 8px;
        border: 1px solid transparent;
        background: #11633e;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        text-decoration: none;
    }

    .acc-form-button:hover {
        background: #093b28;
    }

    .acc-form-button.secondary {
        background: #fff;
        color: #20352b;
        border-color: #d9e5dd;
    }

    .acc-form-button.secondary:hover {
        background: #f4f7f5;
    }

    .acc-info {
        background: #f0f8f3;
        border: 1px solid #d3e9d9;
        border-radius: 9px;
        padding: 14px 16px;
        font-size: 12px;
        color: #28543a;
        margin-bottom: 22px;
    }

    @media (max-width: 700px) {
        .acc-form-grid {
            grid-template-columns: 1fr;
        }

        .acc-form-card {
            padding: 18px;
        }
    }
</style>
@endpush

@section('content')

<div class="acc-form-header">
    <div>
        <h1 class="acc-form-title">Add Accessory</h1>
        <div class="acc-form-subtitle">
            Register a new cylinder, regulator, hose, burner
            or other LPG accessory.
        </div>
    </div>

    <a href="{{ route('admin.accessories.index') }}"
       class="acc-form-button secondary">
        Back to Register
    </a>
</div>

@if($errors->any())
    <div class="alert-error">
        <strong>Please correct the following errors:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="acc-info">
    <strong>Automatic Numbering:</strong>
    The system will generate a unique accessory number,
    such as ACC-000001, after the record is saved.
</div>

<form method="POST"
      action="{{ route('admin.accessories.store') }}">

    @csrf

    <div class="acc-form-card">
        <h2 class="acc-section-title">
            Accessory Information
        </h2>

        <div class="acc-form-grid">

            <div class="acc-form-field">
                <label for="name">
                    Accessory Name
                    <span class="acc-required">*</span>
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    class="acc-form-control"
                    value="{{ old('name') }}"
                    placeholder="e.g. 12.5kg Gas Cylinder"
                    maxlength="150"
                    required
                >

                @error('name')
                    <div class="acc-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="acc-form-field">
                <label for="category">
                    Category
                    <span class="acc-required">*</span>
                </label>

                <select
                    id="category"
                    name="category"
                    class="acc-form-control"
                    required
                >
                    <option value="">Select Category</option>

                    @foreach($categories as $category)
                        <option
                            value="{{ $category }}"
                            @selected(old('category') === $category)
                        >
                            {{ $category }}
                        </option>
                    @endforeach
                </select>

                @error('category')
                    <div class="acc-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="acc-form-field">
                <label for="branch_id">
                    Branch / Location
                    <span class="acc-required">*</span>
                </label>

                <select
                    id="branch_id"
                    name="branch_id"
                    class="acc-form-control"
                    required
                >
                    <option value="">Select Branch</option>

                    @foreach($branches as $branch)
                        <option
                            value="{{ $branch->id }}"
                            @selected((string) old('branch_id') === (string) $branch->id)
                        >
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>

                @error('branch_id')
                    <div class="acc-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="acc-form-field">
                <label for="unit">
                    Unit of Measurement
                    <span class="acc-required">*</span>
                </label>

                <select
                    id="unit"
                    name="unit"
                    class="acc-form-control"
                    required
                >
                    @foreach($units as $unit)
                        <option
                            value="{{ $unit }}"
                            @selected(old('unit', 'piece') === $unit)
                        >
                            {{ ucfirst($unit) }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>

    <div class="acc-form-card">
        <h2 class="acc-section-title">
            Quantity and Pricing
        </h2>

        <div class="acc-form-grid">

            <div class="acc-form-field">
                <label for="quantity">
                    Opening Quantity
                    <span class="acc-required">*</span>
                </label>

                <input
                    type="number"
                    id="quantity"
                    name="quantity"
                    class="acc-form-control"
                    min="0"
                    step="0.001"
                    value="{{ old('quantity', '0') }}"
                    required
                >

                <div class="acc-form-help">
                    Initial available stock quantity.
                </div>
            </div>

            <div class="acc-form-field">
                <label for="reorder_level">
                    Reorder Level
                </label>

                <input
                    type="number"
                    id="reorder_level"
                    name="reorder_level"
                    class="acc-form-control"
                    min="0"
                    step="0.001"
                    value="{{ old('reorder_level', '0') }}"
                >

                <div class="acc-form-help">
                    Minimum quantity before restocking.
                </div>
            </div>

            <div class="acc-form-field">
                <label for="unit_buying_price">
                    Unit Buying Price (₦)
                    <span class="acc-required">*</span>
                </label>

                <input
                    type="number"
                    id="unit_buying_price"
                    name="unit_buying_price"
                    class="acc-form-control"
                    min="0"
                    step="0.01"
                    value="{{ old('unit_buying_price', '0') }}"
                    required
                >
            </div>

            <div class="acc-form-field">
                <label for="unit_selling_price">
                    Unit Selling Price (₦)
                    <span class="acc-required">*</span>
                </label>

                <input
                    type="number"
                    id="unit_selling_price"
                    name="unit_selling_price"
                    class="acc-form-control"
                    min="0"
                    step="0.01"
                    value="{{ old('unit_selling_price', '0') }}"
                    required
                >
            </div>

        </div>
    </div>

    <div class="acc-form-card">
        <h2 class="acc-section-title">
            Additional Information
        </h2>

        <div class="acc-form-grid">

            <div class="acc-form-field">
                <label for="status">
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="acc-form-control"
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
            </div>

            <div class="acc-form-field full">
                <label for="description">
                    Description / Notes
                </label>

                <textarea
                    id="description"
                    name="description"
                    class="acc-form-control"
                    placeholder="Additional information about this accessory"
                >{{ old('description') }}</textarea>
            </div>

        </div>
    </div>

    <div class="acc-form-actions">
        <a href="{{ route('admin.accessories.index') }}"
           class="acc-form-button secondary">
            Cancel
        </a>

        <button type="submit"
                class="acc-form-button">
            Save Accessory
        </button>
    </div>

</form>

@endsection
