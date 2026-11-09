
@extends('layouts.manager')

@section('title', 'Record Expense')

@section('content')
@php
    $selectedBranch = (string) old(
        'branch_id',
        $branches->count() === 1 ? $branches->first()->id : ''
    );

    $categories = [
        'Utilities',
        'Transportation',
        'Fuel and Lubricants',
        'Repairs and Maintenance',
        'Staff Welfare',
        'Office Supplies',
        'Cleaning and Sanitation',
        'Security',
        'Communication',
        'Bank Charges',
        'Other Operating Expenses',
    ];

    $selectedCategory = old('category', '');
@endphp

<div class="expense-wrap">

    <div class="expense-heading">
        <div>
            <div class="expense-eyebrow">FINANCE / EXPENSE MANAGEMENT</div>
            <h1>Record New Expense</h1>
            <p>Record an operating expense for an assigned branch.</p>
        </div>

        <a class="expense-btn"
           href="{{ route('manager.expenses.index') }}">
            ← Back to Expense Register
        </a>
    </div>

    @if($errors->any())
        <div class="expense-alert" role="alert">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('manager.expenses.store') }}"
          id="expenseForm">

        @csrf

        <input type="hidden"
               name="transaction_token"
               value="{{ old('transaction_token', $transactionToken) }}">

        <section class="expense-card">
            <h2>Expense Information</h2>

            <div class="expense-grid">

                <div class="expense-field">
                    <label for="expense_date">Expense Date *</label>
                    <input type="date"
                           id="expense_date"
                           name="expense_date"
                           value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                           required>
                    @error('expense_date')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field">
                    <label for="branch_id">Branch *</label>
                    <select id="branch_id" name="branch_id" required>
                        <option value="">Select assigned branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                @selected($selectedBranch === (string) $branch->id)>
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field">
                    <label for="category">Expense Category *</label>
                    <select id="category" name="category" required>
                        <option value="">Select category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}"
                                @selected($selectedCategory === $category)>
                                {{ $category }}
                            </option>
                        @endforeach

                        @if($selectedCategory && !in_array($selectedCategory, $categories, true))
                            <option value="{{ $selectedCategory }}" selected>
                                {{ $selectedCategory }}
                            </option>
                        @endif
                    </select>
                    @error('category')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field">
                    <label for="amount">Expense Amount (₦) *</label>
                    <input type="number"
                           id="amount"
                           name="amount"
                           min="0.01"
                           step="0.01"
                           inputmode="decimal"
                           value="{{ old('amount') }}"
                           placeholder="e.g. 15000.00"
                           required>
                    @error('amount')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field expense-full">
                    <label for="description">Expense Description *</label>
                    <textarea id="description"
                              name="description"
                              maxlength="500"
                              required
                              placeholder="Describe the purpose of this expense">{{ old('description') }}</textarea>
                    @error('description')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </section>

        <section class="expense-card">
            <h2>Payment Information</h2>

            <div class="expense-grid">

                <div class="expense-field">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method"
                            name="payment_method"
                            required>
                        <option value="">Select payment method</option>

                        @foreach([
                            'cash' => 'Cash',
                            'bank_transfer' => 'Bank Transfer',
                            'pos' => 'POS',
                            'other' => 'Other',
                        ] as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('payment_method') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('payment_method')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field">
                    <label for="payment_reference">Payment Reference</label>
                    <input type="text"
                           id="payment_reference"
                           name="payment_reference"
                           maxlength="150"
                           value="{{ old('payment_reference') }}"
                           placeholder="Transaction or receipt reference">
                    @error('payment_reference')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="expense-field expense-full">
                    <label for="payee">Paid To / Beneficiary</label>
                    <input type="text"
                           id="payee"
                           name="payee"
                           maxlength="150"
                           value="{{ old('payee') }}"
                           placeholder="Name of recipient or service provider">
                    @error('payee')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

            </div>
        </section>

        <section class="expense-card">
            <h2>Additional Information</h2>

            <div class="expense-grid">
                <div class="expense-field expense-full">
                    <label for="notes">Remarks / Supporting Notes</label>
                    <textarea id="notes"
                              name="notes"
                              maxlength="2000"
                              placeholder="Additional information about the expense">{{ old('notes') }}</textarea>
                    @error('notes')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </section>

        <div class="expense-actions">
            <a class="expense-btn"
               href="{{ route('manager.expenses.index') }}">
                Cancel
            </a>

            <button type="submit"
                    class="expense-btn expense-btn-primary"
                    id="expenseSaveButton">
                Record Expense
            </button>
        </div>

        <p class="expense-note">
            Recording an expense creates an auditable financial record.
            Recorded expenses cannot be edited or deleted from this form.
            Cashbook integration will be implemented separately.
        </p>
    </form>
</div>
@endsection

@push('styles')
<style>
.expense-wrap{max-width:1120px;margin:0 auto;color:#1e293b}
.expense-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.expense-eyebrow{font-size:11px;letter-spacing:.12em;font-weight:800;color:#14834a;margin-bottom:5px}
.expense-heading h1{margin:0 0 6px;font-size:27px;font-weight:800;color:#163b2b}
.expense-heading p{margin:0;color:#64748b;font-size:14px}
.expense-card{background:#fff;border:1px solid #e1eae5;border-radius:14px;padding:24px;margin-bottom:20px;box-shadow:0 3px 14px rgba(16,70,41,.035)}
.expense-card h2{margin:0 0 18px;font-size:16px;color:#1b5036;font-weight:800}
.expense-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
.expense-field{display:flex;flex-direction:column;gap:7px;min-width:0}
.expense-field label{font-size:13px;color:#34473c;font-weight:700}
.expense-field input,.expense-field select,.expense-field textarea{box-sizing:border-box;width:100%;min-height:43px;border:1px solid #ccdcd2;border-radius:8px;padding:10px 12px;background:#fff;color:#233a2d;font:inherit;font-size:13px}
.expense-field textarea{min-height:95px;resize:vertical}
.expense-field input:focus,.expense-field select:focus,.expense-field textarea:focus{outline:2px solid #c0e4cf;border-color:#32835a}
.expense-field .error{color:#a32626;font-size:12px}
.expense-full{grid-column:1/-1}
.expense-actions{display:flex;gap:12px;justify-content:flex-end;flex-wrap:wrap}
.expense-btn{display:inline-flex;align-items:center;justify-content:center;padding:11px 19px;border-radius:9px;border:1px solid #b8cfc0;background:#fff;color:#25563b;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer}
.expense-btn-primary{background:#187344;border-color:#187344;color:#fff}
.expense-btn-primary:hover{background:#105b35}
.expense-btn-primary:disabled{opacity:.65;cursor:wait}
.expense-alert{border-radius:9px;padding:12px 15px;margin-bottom:17px;background:#fff5f4;border:1px solid #f0c8c3;color:#8a2924;font-size:13px}
.expense-alert ul{margin:8px 0 0;padding-left:20px}
.expense-note{color:#617268;font-size:12px;margin:14px 0 25px}
@media(max-width:700px){
    .expense-grid{grid-template-columns:1fr}
    .expense-card{padding:17px}
    .expense-heading h1{font-size:23px}
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('expenseForm');
    const button = document.getElementById('expenseSaveButton');

    if (!form || !button) return;

    form.addEventListener('submit', function () {
        if (form.checkValidity()) {
            button.disabled = true;
            button.textContent = 'Recording Expense…';
        }
    });
});
</script>
@endpush
