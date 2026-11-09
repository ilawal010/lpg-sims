@extends('layouts.admin')

@section('title', 'Expense Register')

@section('content')
<div class="me-expenses">

    <div class="me-heading">
        <div>
            <div class="me-eyebrow">FINANCE / EXPENSE MANAGEMENT</div>
            <h1>Expense Register</h1>
            <p>Operating expenses across all active branches.</p>
        </div>

        @if(auth()->user()?->hasPermission('expenses.create'))
            <a class="me-btn me-btn-primary"
               href="{{ route('admin.expenses.create') }}">
                + Record Expense
            </a>
        @endif
    </div>

    @if(session('success'))
        <div class="me-alert me-success" role="status">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="me-alert me-error" role="alert">
            <strong>Unable to complete the request.</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="me-panel">
        <div class="me-panel-heading">
            <h2>Filter Expenses</h2>
            <span>All active branches are available</span>
        </div>

        <form method="GET"
              action="{{ route('admin.expenses.index') }}"
              class="me-filters">

            <label>
                Branch
                <select name="branch_id">
                    <option value="">All active branches</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            @selected(
                                (string)($filters['branch_id'] ?? '') ===
                                (string)$branch->id
                            )>
                            {{ $branch->branch_name }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label>
                Category
                <input type="text"
                       name="category"
                       maxlength="100"
                       value="{{ $filters['category'] ?? '' }}"
                       placeholder="e.g. Utilities">
            </label>

            <label>
                Status
                <select name="status">
                    <option value="">All statuses</option>

                    <option value="recorded"
                        @selected(($filters['status'] ?? '') === 'recorded')>
                        Recorded
                    </option>

                    <option value="voided"
                        @selected(($filters['status'] ?? '') === 'voided')>
                        Voided
                    </option>
                </select>
            </label>

            <label>
                From Date
                <input type="date"
                       name="date_from"
                       value="{{ $filters['date_from'] ?? '' }}">
            </label>

            <label>
                To Date
                <input type="date"
                       name="date_to"
                       value="{{ $filters['date_to'] ?? '' }}">
            </label>

            <div class="me-filter-actions">
                <button type="submit" class="me-btn me-btn-primary">
                    Apply Filters
                </button>

                <a class="me-btn me-btn-outline"
                   href="{{ route('admin.expenses.index') }}">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <div class="me-panel">
        <div class="me-panel-heading">
            <h2>Expense Records</h2>

            <span>
                {{ number_format($expenses->total()) }}
                {{ \Illuminate\Support\Str::plural('expense', $expenses->total()) }}
            </span>
        </div>

        <div class="me-table-scroll">
            <table class="me-table">
                <thead>
                    <tr>
                        <th>Expense / Date</th>
                        <th>Category / Description</th>
                        <th>Branch</th>
                        <th>Payment Details</th>
                        <th class="me-num">Amount (₦)</th>
                        <th>Status</th>
                        <th>Recorded By</th>
                        <th>Actions / Void Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($expenses as $expense)
                        <tr>
                            <td>
                                <strong>{{ $expense->expense_number }}</strong>
                                <small>
                                    {{ $expense->expense_date?->format('d M Y') ?? '—' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $expense->category }}</strong>
                                <small>{{ $expense->description }}</small>

                                @if($expense->payee)
                                    <small>Payee: {{ $expense->payee }}</small>
                                @endif
                            </td>

                            <td>
                                {{ $expense->branch?->branch_name ?? '—' }}
                            </td>

                            <td>
                                <strong>
                                    {{ match($expense->payment_method) {
                                        'cash' => 'Cash',
                                        'bank_transfer' => 'Bank Transfer',
                                        'pos' => 'POS',
                                        default => 'Other',
                                    } }}
                                </strong>

                                @if($expense->payment_reference)
                                    <small>
                                        Ref: {{ $expense->payment_reference }}
                                    </small>
                                @endif
                            </td>

                            <td class="me-num">
                                {{ number_format((float)$expense->amount, 2) }}
                            </td>

                            <td>
                                <span class="me-status me-status-{{ $expense->status }}">
                                    {{ ucfirst($expense->status) }}
                                </span>
                            </td>

                            <td>
                                {{ $expense->createdBy?->name ?? '—' }}
                                <small>
                                    {{ $expense->created_at?->format('d M Y, h:i A') ?? '—' }}
                                </small>
                            </td>
                            <td>
                                @if($expense->isRecorded() && auth()->user()?->hasPermission('expenses.void'))
                                    <details class="me-void-details">
                                        <summary class="me-btn me-btn-danger">Void Expense</summary>
                                        <form method="POST" action="{{ route('admin.expenses.void', $expense) }}" class="me-void-form">
                                            @csrf
                                            <label for="void-reason-{{ $expense->id }}">Reason for voiding</label>
                                            <textarea id="void-reason-{{ $expense->id }}" name="void_reason" required minlength="10" maxlength="2000" rows="3" placeholder="Explain why this expense must be voided"></textarea>
                                            <button type="submit" class="me-btn me-btn-danger">Confirm Void</button>
                                        </form>
                                    </details>
                                @elseif($expense->isVoided())
                                    <small>Voided: {{ $expense->voided_at?->format('d M Y, h:i A') ?? '—' }}</small>
                                    <small>By: {{ $expense->voidedBy?->name ?? 'Unknown' }}</small>
                                    <small>Reason: {{ $expense->void_reason ?? '—' }}</small>
                                @else
                                    <small>—</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="me-empty">
                                No expenses match the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($expenses->hasPages())
            <div class="me-pagination">
                {{ $expenses->links() }}
            </div>
        @endif
    </div>

    <p class="me-note">
        Expense records cover all active branches.
        Recorded expenses cannot be edited or deleted from this register. Authorized Owners may void them with a recorded reason.
        Recorded expenses are posted to the Cashbook automatically; voiding synchronizes their Cashbook entries.
    </p>

</div>

<div class="me-expenses">
    <div class="me-panel me-purchase-panel">
        <div class="me-panel-heading">
            <div>
                <h2>LPG Inventory Purchases</h2>
                <p class="me-purchase-subtitle">Supplier purchases are inventory acquisitions, not operating expenses. Only recorded supplier payments affect the Cashbook.</p>
            </div>
            <span>{{ $lpgPurchases->count() }} recent purchase(s)</span>
        </div>

        <div class="me-table-scroll">
            <table class="me-table me-purchase-table">
                <thead>
                    <tr>
                        <th>Receipt / Date</th>
                        <th>Branch</th>
                        <th>Supplier</th>
                        <th class="me-num">Quantity</th>
                        <th class="me-num">Purchase Value</th>
                        <th class="me-num">Amount Paid</th>
                        <th class="me-num">Outstanding</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lpgPurchases as $purchase)
                        @php
                            // total_paid is populated by the Owner ExpenseController withSum('payments as total_paid', 'amount').
                            $paid = round((float) ($purchase->total_paid ?? 0), 2);
                            $total = round((float) $purchase->total_cost, 2);
                            $outstanding = max(0, round($total - $paid, 2));
                            $paymentStatus = $outstanding <= 0 ? 'Paid' : ($paid > 0 ? 'Partially Paid' : 'Unpaid');
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $purchase->receipt?->receipt_number ?? '—' }}</strong>
                                <small>{{ $purchase->purchase_date?->format('d M Y') ?? '—' }}</small>
                            </td>
                            <td>{{ $purchase->branch?->branch_name ?? '—' }}</td>
                            <td>{{ $purchase->supplier?->supplier_name ?? '—' }}</td>
                            <td class="me-num">{{ number_format((float) $purchase->quantity_kg, 3) }} kg</td>
                            <td class="me-num">₦{{ number_format($total, 2) }}</td>
                            <td class="me-num">₦{{ number_format($paid, 2) }}</td>
                            <td class="me-num me-purchase-outstanding">₦{{ number_format($outstanding, 2) }}</td>
                            <td>
                                <span class="me-purchase-status {{ $paymentStatus === 'Paid' ? 'paid' : ($paymentStatus === 'Unpaid' ? 'unpaid' : 'partial') }}">
                                    {{ $paymentStatus }}
                                </span>
                            </td>
                            <td>
                                @if($outstanding > 0 && auth()->user()?->hasPermission('expenses.create'))
                                    <a class="me-btn me-btn-primary me-purchase-action"
                                       href="{{ route('admin.lpg-purchase-payments.create', $purchase->id) }}">
                                        Record Payment
                                    </a>
                                @elseif($outstanding <= 0)
                                    <span class="me-purchase-complete">Fully Paid</span>
                                @else
                                    <span class="me-purchase-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="me-empty">No posted LPG inventory purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="me-purchase-footer">
            Purchase payments are posted as supplier-payment outflows, not operating expenses. No LPG stock movement is created when a payment is recorded.
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.me-expenses {
    max-width: 1500px;
    margin: 0 auto;
    color: #1e293b;
}

.me-heading {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.me-eyebrow {
    font-size: 11px;
    letter-spacing: .12em;
    font-weight: 800;
    color: #14834a;
}

.me-heading h1 {
    font-size: 27px;
    font-weight: 800;
    margin: 5px 0;
}

.me-heading p {
    margin: 0;
    color: #64748b;
    font-size: 14px;
}

.me-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    padding: 10px 15px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    border: 1px solid transparent;
    white-space: nowrap;
}

.me-btn-primary {
    background: #147d49;
    color: #fff;
}

.me-btn-primary:hover {
    background: #0e6037;
    color: #fff;
}

.me-btn-outline {
    background: #fff;
    border-color: #cbd5e1;
    color: #334155;
}

.me-panel {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    margin-bottom: 18px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, .035);
}

.me-panel-heading {
    padding: 18px 20px;
    display: flex;
    justify-content: space-between;
    gap: 10px;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
}

.me-panel-heading h2 {
    font-size: 16px;
    margin: 0;
    font-weight: 800;
}

.me-panel-heading span {
    font-size: 12px;
    color: #64748b;
}

.me-filters {
    padding: 18px 20px;
    display: grid;
    grid-template-columns: repeat(5, minmax(140px, 1fr));
    gap: 13px;
    align-items: end;
}

.me-filters label {
    display: flex;
    flex-direction: column;
    gap: 6px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

.me-filters input,
.me-filters select {
    box-sizing: border-box;
    width: 100%;
    min-width: 0;
    padding: 10px 11px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #fff;
    color: #0f172a;
    font-size: 13px;
}

.me-filter-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    grid-column: 1 / -1;
}

.me-table-scroll {
    overflow-x: auto;
}

.me-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.me-table th {
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: .04em;
    text-align: left;
    white-space: nowrap;
    padding: 13px 15px;
    border-bottom: 1px solid #e2e8f0;
}

.me-table td {
    padding: 14px 15px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

.me-table td strong {
    display: block;
    font-weight: 750;
    color: #0f172a;
}

.me-table td small {
    display: block;
    color: #64748b;
    margin-top: 5px;
    font-size: 11px;
}

.me-table tr:last-child td {
    border-bottom: 0;
}

.me-num {
    text-align: right !important;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
}

.me-status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 800;
}

.me-status-recorded {
    background: #dcfce7;
    color: #166534;
}

.me-status-voided {
    background: #fee2e2;
    color: #991b1b;
}

.me-empty {
    text-align: center;
    padding: 35px !important;
    color: #64748b;
}

.me-pagination {
    padding: 16px 20px;
    border-top: 1px solid #e2e8f0;
}

.me-alert {
    padding: 13px 16px;
    border-radius: 9px;
    margin-bottom: 15px;
    font-size: 13px;
}

.me-success {
    background: #ecfdf5;
    color: #166534;
    border: 1px solid #a7f3d0;
}

.me-error {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

.me-alert ul {
    margin: 7px 0 0;
    padding-left: 20px;
}

.me-note {
    font-size: 12px;
    color: #64748b;
    margin: 12px 0 25px;
}

@media (max-width: 1100px) {
    .me-filters {
        grid-template-columns: repeat(3, minmax(150px, 1fr));
    }
}

@media (max-width: 650px) {
    .me-filters {
        grid-template-columns: 1fr;
    }

    .me-heading h1 {
        font-size: 23px;
    }

    .me-panel-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .me-filter-actions {
        flex-wrap: wrap;
    }
}
.me-btn-danger { background: #991b1b; color: #fff; }
.me-btn-danger:hover { background: #7f1d1d; color: #fff; }
.me-void-details summary { list-style: none; user-select: none; }
.me-void-details summary::-webkit-details-marker { display: none; }
.me-void-form { display: flex; flex-direction: column; gap: 8px; margin-top: 10px; min-width: 220px; }
.me-void-form label { font-size: 12px; font-weight: 700; }
.me-void-form textarea { width: 100%; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 8px; padding: 9px; font: inherit; }

/* LPG supplier purchase register */
.me-purchase-panel { margin-top: 0; }
.me-purchase-panel .me-panel-heading { align-items: flex-start; }
.me-purchase-subtitle { margin: 6px 0 0; color: #64748b; font-size: 12px; line-height: 1.6; max-width: 780px; }
.me-purchase-table { min-width: 1130px; }
.me-purchase-outstanding { font-weight: 800; color: #174d35; }
.me-purchase-status { display: inline-flex; border-radius: 999px; padding: 6px 10px; font-size: 11px; font-weight: 800; white-space: nowrap; }
.me-purchase-status.paid { background: #dcfce7; color: #166534; }
.me-purchase-status.partial { background: #fef3c7; color: #92400e; }
.me-purchase-status.unpaid { background: #fee2e2; color: #991b1b; }
.me-purchase-action { padding: 9px 12px; font-size: 12px; }
.me-purchase-complete { color: #166534; font-size: 12px; font-weight: 800; white-space: nowrap; }
.me-purchase-muted { color: #94a3b8; }
.me-purchase-footer { padding: 13px 20px; border-top: 1px solid #e2e8f0; background: #fbfdfb; color: #64748b; font-size: 12px; line-height: 1.6; }
</style>
@endpush
