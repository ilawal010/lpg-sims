<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;font-size:8px;color:#20382b}h1{font-size:17px;margin:0 0 7px}h2{font-size:12px;margin:15px 0 5px}p{margin:4px 0;line-height:1.5}table{width:100%;border-collapse:collapse;margin-top:8px}th,td{border:1px solid #d8e4dc;padding:5px;vertical-align:top}th{background:#e9f2ec;text-align:left}.right{text-align:right}.warn{color:#8d5510}.muted{color:#617769}
</style></head><body>
<h1>Intisar Oil &amp; Gas — Cashbook</h1>
<p>Branch: {{ $selectedBranch ? ($branches->firstWhere('id',$selectedBranch)?->branch_name ?? 'Selected branch') : 'All authorized branches' }} | From: {{ $filters['from'] }} | To: {{ $filters['to'] }}</p>
<p>Type: {{ $filters['type'] ?? 'All' }} | Method: {{ $filters['method'] ?? 'All' }} | Status: {{ $filters['status'] ?? 'All' }} | Generated: {{ now()->format('d M Y H:i') }}</p>
<p class="muted">Account balances include all recorded transactions regardless of display filters. POS is classified as Bank pending settlement reconciliation.</p>
@foreach($warnings as $warning)<p class="warn">WARNING: {{ $warning }}</p>@endforeach
<h2>Cash and Bank Account Summary (NGN)</h2>
<table><thead><tr><th>Branch</th><th class="right">Cash Open</th><th class="right">Cash In</th><th class="right">Cash Out</th><th class="right">Cash Close</th><th class="right">Bank Open</th><th class="right">Bank In</th><th class="right">Bank Out</th><th class="right">Bank Close</th></tr></thead><tbody>
@foreach($summaries as $s)<tr><td>{{ $s['branch'] }}</td><td class="right">{{ $s['cash_open'] ?? 'Unverified' }}</td><td class="right">{{ $s['cash_receipts'] }}</td><td class="right">{{ $s['cash_payments'] }}</td><td class="right">{{ $s['cash_close'] ?? 'Unverified' }}</td><td class="right">{{ $s['bank_open'] ?? 'Unverified' }}</td><td class="right">{{ $s['bank_receipts'] }}</td><td class="right">{{ $s['bank_payments'] }}</td><td class="right">{{ $s['bank_close'] ?? 'Unverified' }}</td></tr>@endforeach
</tbody></table>
<h2>Transactions</h2>
<table><thead><tr><th>Date</th><th>Reference</th><th>Branch</th><th>Type</th><th>Description</th><th>Method</th><th>Status</th><th class="right">Receipt</th><th class="right">Payment</th><th class="right">Running Cash</th><th class="right">Running Bank</th></tr></thead><tbody>
@forelse($transactions as $t)
@php($active = $t->status === 'recorded')
<tr><td>{{ $t->transaction_date?->format('Y-m-d') }}</td><td>{{ $t->transaction_number }}</td><td>{{ $t->branch?->branch_name }}</td><td>{{ $t->transaction_type }}</td><td>{{ $t->category }} — {{ $t->description }}</td><td>{{ str_replace('_',' ',$t->payment_method ?? '') }}</td><td>{{ $t->status }}</td><td class="right">{{ $active && $t->transaction_type === 'receipt' ? number_format((float)$t->amount,2) : '—' }}</td><td class="right">{{ $active && $t->transaction_type === 'payment' ? number_format((float)$t->amount,2) : '—' }}</td><td class="right">{{ $running[$t->id]['cash'] ?? '—' }}</td><td class="right">{{ $running[$t->id]['bank'] ?? '—' }}</td></tr>
@empty<tr><td colspan="11">No matching transactions.</td></tr>@endforelse
</tbody></table>
<p><strong>Filtered recorded receipts:</strong> NGN {{ number_format((float) ($totals->receipts ?? 0),2) }} &nbsp; <strong>Filtered recorded payments:</strong> NGN {{ number_format((float) ($totals->payments ?? 0),2) }}</p>
</body></html>
