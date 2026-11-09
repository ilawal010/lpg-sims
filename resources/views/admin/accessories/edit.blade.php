@extends('layouts.admin')

@section('content')
<style>
    .ae-wrap { max-width: 1100px; margin: 0 auto; }
    .ae-heading { margin-bottom: 24px; }
    .ae-heading h1 { font-size: 26px; font-weight: 800; color: #123d30; margin: 0 0 7px; }
    .ae-heading p { color: #718079; margin: 0; font-size: 14px; }
    .ae-card { background: #fff; border: 1px solid #e1e9e4; border-radius: 14px; padding: 26px; margin-bottom: 20px; }
    .ae-section { font-size: 16px; font-weight: 800; color: #153e31; margin: 0 0 20px; }
    .ae-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
    .ae-field { display: flex; flex-direction: column; gap: 7px; }
    .ae-field label { font-size: 13px; font-weight: 700; color: #344e43; }
    .ae-field input, .ae-field select, .ae-field textarea {
        width: 100%; box-sizing: border-box; padding: 12px 13px;
        border: 1px solid #d7e2da; border-radius: 9px;
        font-size: 14px; background: #fff; color: #193a2d;
    }
    .ae-field input:focus, .ae-field select:focus, .ae-field textarea:focus {
        outline: none; border-color: #18764b;
    }
    .ae-field input[readonly] { background: #f1f5f2; color: #53685d; }
    .ae-full { grid-column: 1 / -1; }
    .ae-note { font-size: 12px; color: #78877e; line-height: 1.6; }
    .ae-stock { background: #edf8f0; border: 1px solid #cbe7d3; border-radius: 10px; padding: 18px; }
    .ae-stock strong { color: #11613e; }
    .ae-actions { display: flex; gap: 12px; justify-content: flex-end; align-items: center; }
    .ae-btn { display: inline-block; border: 0; border-radius: 9px; padding: 12px 20px; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; }
    .ae-primary { background: #11613e; color: #fff; }
    .ae-secondary { background: #f2f5f3; color: #234638; border: 1px solid #dce5df; }
    .ae-error { color: #b42318; font-size: 12px; }
    .ae-alert { background: #fff1f0; border: 1px solid #ffc9c4; color: #9c2c21; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
    @media(max-width: 700px) {
        .ae-grid { grid-template-columns: 1fr; }
        .ae-card { padding: 18px; }
        .ae-actions { flex-direction: column-reverse; align-items: stretch; }
        .ae-btn { text-align: center; }
    }
</style>

<div class="ae-wrap">
    <div class="ae-heading">
        <h1>Edit Accessory</h1>
        <p>Update accessory information and add stock without changing its permanent accessory number.</p>
    </div>

    @if($errors->any())
        <div class="ae-alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.accessories.update', $accessory) }}">
        @csrf
        @method('PUT')

        <div class="ae-card">
            <h2 class="ae-section">Accessory Identification</h2>

            <div class="ae-grid">
                <div class="ae-field">
                    <label>Database ID</label>
                    <input type="text" value="{{ $accessory->id }}" readonly>
                </div>

                <div class="ae-field">
                    <label>Accessory Number</label>
                    <input type="text" value="{{ $accessory->accessory_number }}" readonly>
                    <span class="ae-note">Permanent identification number. Cannot be changed.</span>
                </div>

                <div class="ae-field">
                    <label for="name">Accessory Name *</label>
                    <input id="name" name="name" type="text" maxlength="150"
                           value="{{ old('name', $accessory->name) }}" required>
                    @error('name') <span class="ae-error">{{ $message }}</span> @enderror
                </div>

                <div class="ae-field">
                    <label for="category">Category *</label>
                    <select id="category" name="category" required>
                        @foreach($categories as $category)
                            <option value="{{ $category }}"
                                @selected(old('category', $accessory->category) === $category)>
                                {{ $category }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ae-field">
                    <label for="branch_id">Branch / Location *</label>
                    <select id="branch_id" name="branch_id" required>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected((string) old('branch_id', $accessory->branch_id) === (string) $branch->id)>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ae-field">
                    <label for="unit">Unit *</label>
                    <select id="unit" name="unit" required>
                        @foreach($units as $unit)
                            <option value="{{ $unit }}"
                                @selected(old('unit', $accessory->unit) === $unit)>
                                {{ ucfirst($unit) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="ae-field">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="active" @selected(old('status', $accessory->status) === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $accessory->status) === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="ae-field">
                    <label for="reorder_level">Reorder Level *</label>
                    <input id="reorder_level" name="reorder_level" type="number"
                           min="0" step="0.001"
                           value="{{ old('reorder_level', $accessory->reorder_level) }}" required>
                </div>

                <div class="ae-field ae-full">
                    <label for="description">Description / Notes</label>
                    <textarea id="description" name="description" rows="3">{{ old('description', $accessory->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="ae-card">
            <h2 class="ae-section">Stock & Restocking</h2>

            <div class="ae-grid">
                <div class="ae-field">
                    <label>Current Stock Quantity</label>
                    <input type="text" value="{{ number_format((float) $accessory->quantity, 3) }}" readonly>
                    <span class="ae-note">Current stock cannot be overwritten directly.</span>
                </div>

                <div class="ae-field">
                    <label for="restock_quantity">Additional Stock Quantity</label>
                    <input id="restock_quantity" name="restock_quantity"
                           type="number" min="0" step="0.001"
                           value="{{ old('restock_quantity', 0) }}">
                    <span class="ae-note">Enter 0 if you are only editing accessory information.</span>
                </div>

                <div class="ae-full ae-stock">
                    <strong>Restocking rule:</strong>
                    New Quantity = Current Quantity + Additional Stock.
                    <div class="ae-note">The existing stock balance will be preserved and only the additional quantity will be added.</div>
                </div>
            </div>
        </div>

        <div class="ae-card">
            <h2 class="ae-section">Pricing</h2>

            <div class="ae-grid">
                <div class="ae-field">
                    <label for="unit_buying_price">Unit Buying Price (₦) *</label>
                    <input id="unit_buying_price" name="unit_buying_price"
                           type="number" min="0" step="0.01"
                           value="{{ old('unit_buying_price', $accessory->unit_buying_price) }}" required>
                </div>

                <div class="ae-field">
                    <label for="unit_selling_price">Unit Selling Price (₦) *</label>
                    <input id="unit_selling_price" name="unit_selling_price"
                           type="number" min="0" step="0.01"
                           value="{{ old('unit_selling_price', $accessory->unit_selling_price) }}" required>
                </div>
            </div>
        </div>

        <div class="ae-actions">
            <a href="{{ route('admin.accessories.index') }}" class="ae-btn ae-secondary">
                Cancel
            </a>

            <button type="submit" class="ae-btn ae-primary">
                Save Changes & Restock
            </button>
        </div>
    </form>
</div>
@endsection
