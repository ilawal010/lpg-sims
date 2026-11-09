@extends($isOwner ? 'layouts.admin' : 'layouts.manager')
@section('title', 'Cashbook')
@section('header', 'Cashbook')
@push('styles')
<style>
.cb4{color:#173d2c}.cb4 *{box-sizing:border-box}.cb4 .cb4-head{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:20px}.cb4 h1{margin:0;font-size:25px;color:#123d2c}.cb4 h2{margin:0 0 7px;font-size:17px;color:#123d2c}.cb4 .muted{color:#728579;font-size:12px;line-height:1.5}.cb4 .panel{background:#fff;border:1px solid #e1e9e3;border-radius:12px;padding:21px;margin-bottom:18px}.cb4 .filters{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.cb4 label{display:block;font-weight:700;font-size:12px;margin-bottom:7px}.cb4 input,.cb4 select{width:100%;min-height:43px;border:1px solid #cfddd3;border-radius:8px;padding:9px 11px;background:#fff;color:#193c2b;font:inherit}.cb4 input:focus,.cb4 select:focus{outline:2px solid #b6e1c7}.cb4 .actions{display:flex;gap:9px;flex-wrap:wrap;margin-top:18px}.cb4 .action{display:inline-flex;align-items:center;justify-content:center;border:1px solid #12663e;background:#12663e;color:white;border-radius:8px;padding:11px 16px;text-decoration:none;font-size:13px;font-weight:750;cursor:pointer}.cb4 .action.alt{background:#fff;color:#12663e}.cb4 .notice{padding:12px 15px;background:#fff8e8;border:1px solid #f0dfb6;border-radius:9px;color:#795c21;font-size:13px;margin-bottom:12px}.cb4 .stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:18px}.cb4 .stat{background:#fff;border:1px solid #e1e9e3;border-radius:11px;padding:17px}.cb4 .stat strong{display:block;margin-top:7px;font-size:21px;font-variant-numeric:tabular-nums}.cb4 .table-scroll{overflow-x:auto}.cb4 table{width:100%;border-collapse:collapse;font-size:12px;min-width:900px}.cb4 th,.cb4 td{padding:12px 11px;text-align:left;border-bottom:1px solid #e7eee8;vertical-align:top}.cb4 th{background:#f1f7f3;color:#365745;font-weight:800}.cb4 .right{text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums}.cb4 .summary-title{font-size:15px;font-weight:800;margin:5px 0 13px}.cb4 .subtle{font-size:11px;color:#718477}.cb4 .summary-table{min-width:1100px}.cb4 .branch{font-weight:750}@media(max-width:850px){.cb4 .filters,.cb4 .stats{grid-template-columns:1fr}.cb4 .panel{padding:15px}}
</style>
@endpush
@section('content')
<div class="cb4">
<div class="cb4-head"><div><h1>Cashbook</h1><p class="muted">{{ $isOwner ? 'All active branches' : 'Assigned active branches' }} · Generate Cash and Bank reports</p></div>
@if($isOwner)<a class="action alt" href="{{ route('admin.cashbook.initialize') }}">Initialize Branch Cashbook</a>@endif</div>
@if($errors->any())<div class="notice">{{ $errors->first() }}</div>@endif
<div class="panel">
<form method="GET" action="{{ route($isOwner ? 'admin.cashbook.index' : 'manager.cashbook.index') }}">
<input type="hidden" name="generate" value="1">
<div class="filters">

<div>
    <label for="cb_branch">Branch</label>

    @if($isOwner)

        {{-- Owner may select any active branch or all branches. --}}
        <select id="cb_branch" name="branch_id">
            <option value="">All active branches</option>

            @foreach($branches as $branch)
                <option value="{{ $branch->id }}"
                    @selected($selectedBranch === (int) $branch->id)>
                    {{ $branch->branch_name }}
                </option>
            @endforeach
        </select>

    @else

        {{-- Manager's assigned branch is displayed automatically. --}}
        @if($branches->count() === 1)

            <input type="hidden"
                   name="branch_id"
                   value="{{ $branches->first()->id }}">

            <input id="cb_branch"
                   type="text"
                   value="{{ $branches->first()->branch_name }}"
                   readonly>

        @else

            {{-- Support multiple assigned branches if applicable. --}}
            <select id="cb_branch"
                    name="branch_id"
                    required>
                <option value="">Select assigned branch</option>

                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        @selected($selectedBranch === (int) $branch->id)>
                        {{ $branch->branch_name }}
                    </option>
                @endforeach
            </select>

        @endif

    @endif
</div>

<div><label for="cb_from">From date</label><input id="cb_from" type="date" name="from" value="{{ $filters['from'] }}" required></div>
<div><label for="cb_to">To date</label><input id="cb_to" type="date" name="to" value="{{ $filters['to'] }}" required></div>
<div><label for="cb_type">Transaction type</label><select id="cb_type" name="type"><option value="">All types</option>@foreach(['receipt'=>'Receipts','payment'=>'Payments'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['type'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
<div><label for="cb_method">Payment method</label><select id="cb_method" name="method"><option value="">All methods</option>@foreach(['cash'=>'Cash','bank_transfer'=>'Bank transfer','pos'=>'POS','other'=>'Other'] as $v=>$l)<option value="{{ $v }}" @selected(($filters['method'] ?? '') === $v)>{{ $l }}</option>@endforeach</select></div>
<div><label for="cb_status">Status</label><select id="cb_status" name="status"><option value="recorded" @selected(($filters['status'] ?? 'recorded') === 'recorded')>Recorded</option><option value="voided" @selected(($filters['status'] ?? '') === 'voided')>Voided</option><option value="" @selected(array_key_exists('status',$filters) && $filters['status'] === '')>All statuses</option></select></div>
</div>
<div class="actions"><button class="action" type="submit">Generate Cashbook</button><a class="action alt" href="{{ route($isOwner ? 'admin.cashbook.index' : 'manager.cashbook.index') }}">Clear filters</a></div>
</form></div>
@if($generated)
@foreach($warnings as $warning)<div class="notice">{{ $warning }}</div>@endforeach
<div class="stats">
<div class="stat"><span class="muted">Filtered recorded receipts</span><strong>₦{{ number_format((float) ($totals->receipts ?? 0),2) }}</strong></div>
<div class="stat"><span class="muted">Filtered recorded payments</span><strong>₦{{ number_format((float) ($totals->payments ?? 0),2) }}</strong></div>
<div class="stat"><span class="muted">Filtered net movement (not closing balance)</span><strong>₦{{ number_format((float) ($totals->receipts ?? 0) - (float) ($totals->payments ?? 0),2) }}</strong></div>
</div>
<div class="panel"><h2>Cash and Bank Account Summary</h2><p class="muted">Official balances use all recorded transactions, independent of transaction display filters. POS is allocated to Bank provisionally; settlement must be reconciled.</p>
<div class="table-scroll"><table class="summary-table"><thead><tr><th>Branch</th><th class="right">Cash opening</th><th class="right">Cash receipts</th><th class="right">Cash payments</th><th class="right">Cash closing</th><th class="right">Bank opening</th><th class="right">Bank receipts</th><th class="right">Bank payments</th><th class="right">Bank closing</th></tr></thead><tbody>
@forelse($summaries as $summary)<tr><td class="branch">{{ $summary['branch'] }}</td><td class="right">{{ $summary['cash_open'] ?? 'Unverified' }}</td><td class="right">{{ $summary['cash_receipts'] }}</td><td class="right">{{ $summary['cash_payments'] }}</td><td class="right">{{ $summary['cash_close'] ?? 'Unverified' }}</td><td class="right">{{ $summary['bank_open'] ?? 'Unverified' }}</td><td class="right">{{ $summary['bank_receipts'] }}</td><td class="right">{{ $summary['bank_payments'] }}</td><td class="right">{{ $summary['bank_close'] ?? 'Unverified' }}</td></tr>
@empty<tr><td colspan="9">No authorized active branches.</td></tr>@endforelse
</tbody></table></div></div>
<div class="panel"><div class="cb4-head"><div><h2>Generated Cashbook Transactions</h2><p class="muted">Running balances reflect all recorded transactions, even when the displayed list is filtered.</p></div><div class="actions" style="margin-top:0"><a class="action alt" href="{{ route($isOwner ? 'admin.cashbook.csv' : 'manager.cashbook.csv', $filters) }}">Download CSV</a><a class="action alt" href="{{ route($isOwner ? 'admin.cashbook.pdf' : 'manager.cashbook.pdf', $filters) }}">Download PDF</a></div></div>
<div class="table-scroll"><table><thead><tr><th>Date</th><th>Reference</th><th>Branch</th><th>Type</th><th>Category / Description</th><th>Method</th><th>Status</th><th class="right">Receipt (₦)</th><th class="right">Payment (₦)</th><th class="right">Running Cash (₦)</th><th class="right">Running Bank (₦)</th></tr></thead><tbody>
@forelse($transactions as $t)
@php($active = $t->status === 'recorded')
<tr><td>{{ $t->transaction_date?->format('d M Y') }}</td><td>{{ $t->transaction_number }}</td><td>{{ $t->branch?->branch_name }}</td><td>{{ ucfirst($t->transaction_type) }}</td><td><b>{{ $t->category }}</b><div class="subtle">{{ $t->description }}</div></td><td>{{ ucwords(str_replace('_',' ',$t->payment_method ?? '')) }}</td><td>{{ ucfirst($t->status) }}</td><td class="right">{{ $active && $t->transaction_type === 'receipt' ? number_format((float)$t->amount,2) : '—' }}</td><td class="right">{{ $active && $t->transaction_type === 'payment' ? number_format((float)$t->amount,2) : '—' }}</td><td class="right">{{ $running[$t->id]['cash'] ?? '—' }}</td><td class="right">{{ $running[$t->id]['bank'] ?? '—' }}</td></tr>
@empty<tr><td colspan="11" style="text-align:center;padding:24px">No Cashbook transactions match the selected filters.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:14px">{{ $transactions->links() }}</div></div>
@endif
</div>
@endsection
