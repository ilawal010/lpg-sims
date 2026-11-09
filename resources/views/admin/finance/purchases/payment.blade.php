@extends('layouts.admin')
@section('title', 'Record LPG Supplier Payment')
@section('content')
<style>
.lpp-wrap{max-width:960px;margin:0 auto;color:#203a2b}.lpp-title{font-size:25px;color:#174d35;font-weight:800;margin:0 0 6px}.lpp-sub{font-size:13px;color:#728277;margin-bottom:22px}.lpp-card{background:white;border:1px solid #dfe9e2;border-radius:13px;margin-bottom:18px;overflow:hidden}.lpp-head{background:#f6faf7;border-bottom:1px solid #e4eee6;padding:17px 22px;font-size:16px;font-weight:800;color:#174d35}.lpp-body{padding:22px}.lpp-metrics{display:grid;grid-template-columns:repeat(3,1fr);gap:14px}.lpp-metric{padding:18px;background:#f6faf7;border:1px solid #e4eee6;border-radius:10px}.lpp-metric small{display:block;color:#728277;margin-bottom:8px}.lpp-metric strong{font-size:20px;color:#174d35}.lpp-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}.lpp-field label{display:block;font-weight:700;font-size:12px;margin-bottom:7px}.lpp-field input,.lpp-field select{width:100%;box-sizing:border-box;padding:12px;border:1px solid #d7e5db;border-radius:8px;font:inherit}.lpp-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:22px}.lpp-btn{border:0;border-radius:8px;padding:12px 18px;font-weight:700;cursor:pointer;text-decoration:none}.lpp-btn.primary{background:#176b45;color:#fff}.lpp-btn.secondary{background:#f0f5f1;color:#355441}.lpp-alert{background:#fff4e7;border:1px solid #f2d7a7;color:#76521a;border-radius:9px;padding:14px;margin-bottom:18px;font-size:13px}.lpp-error{background:#fff0f0;border-color:#efc9c9;color:#9b2424}@media(max-width:650px){.lpp-metrics,.lpp-grid{grid-template-columns:1fr}.lpp-body{padding:16px}}
</style>
<div class="lpp-wrap">
<h1 class="lpp-title">Record LPG Supplier Payment</h1>
<p class="lpp-sub">Purchase #{{ $purchase->id }} · {{ $purchase->supplier?->supplier_name }} · {{ $purchase->branch?->branch_name }}</p>
@if($errors->any())<div class="lpp-alert lpp-error" role="alert"><strong>Payment not recorded:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<div class="lpp-alert">This is a payment for existing LPG inventory, not a new operating expense. Saving will create one Cashbook outflow but will not add LPG stock.</div>
<div class="lpp-card"><div class="lpp-head">Purchase Summary</div><div class="lpp-body"><div class="lpp-metrics"><div class="lpp-metric"><small>Purchase value</small><strong>₦{{ number_format($purchase->total_cost,2) }}</strong></div><div class="lpp-metric"><small>Previously paid</small><strong>₦{{ number_format($paid,2) }}</strong></div><div class="lpp-metric"><small>Outstanding</small><strong>₦{{ number_format($outstanding,2) }}</strong></div></div></div></div>
<div class="lpp-card"><div class="lpp-head">Payment Details</div><div class="lpp-body">
@if($outstanding > 0)
<form method="POST" action="{{ route('admin.lpg-purchase-payments.store', $purchase->id) }}" id="supplierPaymentForm">@csrf
<input type="hidden" name="transaction_token" value="{{ old('transaction_token',$transactionToken) }}">
<div class="lpp-grid">
<div class="lpp-field"><label for="payment_date">Payment Date *</label><input id="payment_date" name="payment_date" type="date" required value="{{ old('payment_date',date('Y-m-d')) }}"></div>
<div class="lpp-field"><label for="amount">Amount (₦) *</label><input id="amount" name="amount" type="number" min="0.01" max="{{ number_format($outstanding,2,'.','') }}" step="0.01" required value="{{ old('amount') }}"></div>
<div class="lpp-field"><label for="payment_method">Payment Method *</label><select id="payment_method" name="payment_method" required><option value="">Select method</option>@foreach(['cash'=>'Cash','bank_transfer'=>'Bank Transfer','pos'=>'POS','other'=>'Other'] as $key=>$label)<option value="{{ $key }}" @selected(old('payment_method')===$key)>{{ $label }}</option>@endforeach</select></div>
<div class="lpp-field"><label for="payment_reference">Payment Reference</label><input id="payment_reference" name="payment_reference" maxlength="150" value="{{ old('payment_reference') }}" placeholder="Transaction reference, if applicable"></div>
</div><div class="lpp-actions"><a class="lpp-btn secondary" href="{{ route('admin.expenses.index') }}">Cancel</a><button class="lpp-btn primary" type="submit" id="paymentSubmit">Record Payment</button></div>
</form>
@else <p>This purchase is fully paid.</p><a class="lpp-btn secondary" href="{{ route('admin.expenses.index') }}">Back to Purchases</a>@endif
</div></div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){const form=document.getElementById('supplierPaymentForm');if(!form)return;const button=document.getElementById('paymentSubmit');form.addEventListener('submit',function(e){if(!form.checkValidity())return;if(button.disabled){e.preventDefault();return;}button.disabled=true;button.textContent='Recording Payment...';});});</script>
@endsection
