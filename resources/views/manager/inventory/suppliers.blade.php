@extends('layouts.manager')

@section('title', 'LPG Suppliers')

@section('content')
@php
    $total = (int) ($summary['total_suppliers'] ?? 0);
    $active = (int) ($summary['active_suppliers'] ?? 0);
    $inactive = (int) ($summary['inactive_suppliers'] ?? 0);
@endphp
<div class="msup-page">
    <div class="msup-heading">
        <div>
            <div class="msup-eyebrow">INVENTORY / SUPPLIER DIRECTORY</div>
            <h1>LPG Suppliers</h1>
            <p>Central supplier directory for LPG receiving and branch operations.</p>
        </div>
        <span class="msup-readonly">Read-only access</span>
    </div>

    <div class="msup-stats">
        <div class="msup-stat"><span>Total Suppliers</span><strong>{{ number_format($total) }}</strong><small>Registered suppliers</small></div>
        <div class="msup-stat"><span>Active Suppliers</span><strong>{{ number_format($active) }}</strong><small>Available for receiving</small></div>
        <div class="msup-stat"><span>Inactive Suppliers</span><strong>{{ number_format($inactive) }}</strong><small>Not available for new receipts</small></div>
    </div>

    <section class="msup-panel">
        <div class="msup-panel-title">
            <div><h2>Supplier Register</h2><p>Search suppliers and view their operational contact details.</p></div>
        </div>
        <form action="{{ route('manager.suppliers.index') }}" method="GET" class="msup-filters">
            <label class="msup-field msup-search">
                <span>Search suppliers</span>
                <input type="search" name="search" value="{{ old('search', $filters['search'] ?? '') }}" placeholder="Supplier name or code" maxlength="150">
            </label>
            <label class="msup-field">
                <span>Status</span>
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Active</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Inactive</option>
                </select>
            </label>
            <button type="submit" class="msup-btn">Apply Filters</button>
            <a class="msup-reset" href="{{ route('manager.suppliers.index') }}">Reset</a>
        </form>

        @if ($errors->any())
            <div class="msup-alert" role="alert">{{ $errors->first() }}</div>
        @endif

        <div class="msup-table-scroll">
            <table class="msup-table">
                <thead><tr><th>Supplier</th><th>Contact Person</th><th>Telephone</th><th>Email</th><th>Location</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td><strong>{{ $supplier->supplier_name }}</strong><span class="msup-sub">{{ $supplier->supplier_code }}</span></td>
                            <td>{{ $supplier->contact_person ?: '—' }}</td>
                            <td>
                                @if($supplier->phone)<a href="tel:{{ $supplier->phone }}">{{ $supplier->phone }}</a>@else — @endif
                                @if($supplier->alternate_phone)<span class="msup-sub">{{ $supplier->alternate_phone }}</span>@endif
                            </td>
                            <td>@if($supplier->email)<a href="mailto:{{ $supplier->email }}">{{ $supplier->email }}</a>@else — @endif</td>
                            <td>
                                {{ collect([$supplier->city, $supplier->state])->filter()->implode(', ') ?: '—' }}
                                @if($supplier->address)<span class="msup-sub">{{ $supplier->address }}</span>@endif
                            </td>
                            <td><span class="msup-status {{ $supplier->status === 'active' ? 'is-active' : 'is-inactive' }}">{{ ucfirst($supplier->status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="msup-empty">No suppliers found for the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="msup-pagination">
            <span>Showing {{ $suppliers->firstItem() ?? 0 }}–{{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} suppliers</span>
            {{ $suppliers->links() }}
        </div>
    </section>
    <p class="msup-footnote">Supplier registration, editing and status changes are managed centrally by the Owner.</p>
</div>
@endsection

@push('styles')
<style>
    .msup-page{max-width:1450px;margin:0 auto;color:#17352b}.msup-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:18px;margin-bottom:22px}.msup-eyebrow{font-size:11px;font-weight:800;letter-spacing:.11em;color:#138458;margin-bottom:7px}.msup-heading h1{font-size:27px;line-height:1.2;font-weight:800;margin:0 0 8px}.msup-heading p,.msup-panel-title p{margin:0;color:#728179;font-size:13px}.msup-readonly{background:#edf6f1;border:1px solid #cde9d9;color:#176443;border-radius:30px;padding:8px 13px;font-size:12px;font-weight:700;white-space:nowrap}.msup-stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;margin-bottom:20px}.msup-stat{background:#fff;border:1px solid #e1eae4;border-radius:12px;padding:19px 21px;box-shadow:0 2px 10px rgba(18,53,35,.035)}.msup-stat span{display:block;font-size:12px;font-weight:700;color:#687d71}.msup-stat strong{display:block;font-size:29px;margin:7px 0;color:#154b34}.msup-stat small{color:#83928a;font-size:11px}.msup-panel{background:#fff;border:1px solid #e1eae4;border-radius:13px;overflow:hidden;box-shadow:0 2px 10px rgba(18,53,35,.035)}.msup-panel-title{padding:22px 23px 10px}.msup-panel-title h2{font-size:17px;margin:0 0 5px;font-weight:800}.msup-filters{padding:12px 23px 22px;display:flex;align-items:flex-end;gap:12px;flex-wrap:wrap}.msup-field{display:flex;flex-direction:column;gap:7px;min-width:175px}.msup-search{flex:1;min-width:240px}.msup-field span{font-size:12px;font-weight:700;color:#456456}.msup-field input,.msup-field select{height:41px;border:1px solid #d7e3db;border-radius:8px;padding:0 12px;background:#fff;color:#234b37;font:inherit;font-size:13px;max-width:100%;outline:none}.msup-field input:focus,.msup-field select:focus{border-color:#16975b;box-shadow:0 0 0 3px #e0f3e8}.msup-btn{height:41px;border:0;background:#12784b;color:#fff;font-weight:700;font-size:13px;padding:0 19px;border-radius:8px;cursor:pointer}.msup-btn:hover{background:#0e603c}.msup-reset{height:41px;display:inline-flex;align-items:center;text-decoration:none;color:#536d5c;font-size:13px;font-weight:700;padding:0 8px}.msup-alert{margin:0 23px 15px;padding:11px 13px;border-radius:8px;background:#fff3f1;color:#9c3024}.msup-table-scroll{overflow-x:auto}.msup-table{border-collapse:collapse;width:100%;min-width:870px;text-align:left}.msup-table th{background:#f5f9f6;border-top:1px solid #e7eee9;border-bottom:1px solid #e7eee9;padding:13px 18px;font-size:11px;letter-spacing:.035em;text-transform:uppercase;color:#617668;white-space:nowrap}.msup-table td{padding:15px 18px;border-bottom:1px solid #edf1ed;font-size:12px;vertical-align:top;color:#385446}.msup-table tbody tr:hover{background:#fbfdfb}.msup-table td strong{font-size:13px;color:#193f2b}.msup-sub{display:block;margin-top:5px;color:#85948a;font-size:11px;max-width:240px;overflow-wrap:anywhere}.msup-table a{color:#117a50;text-decoration:none}.msup-table a:hover{text-decoration:underline}.msup-status{display:inline-block;border-radius:25px;padding:5px 10px;font-weight:700;font-size:11px}.msup-status.is-active{background:#e6f6eb;color:#147a42}.msup-status.is-inactive{background:#f1f2f2;color:#69716e}.msup-empty{text-align:center;padding:35px!important;color:#82918a!important}.msup-pagination{padding:17px 23px;display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap}.msup-pagination>span{color:#718579;font-size:12px}.msup-footnote{color:#82928a;font-size:12px;margin:16px 2px}@media(max-width:760px){.msup-stats{grid-template-columns:1fr}.msup-heading{flex-direction:column}.msup-filters{flex-direction:column;align-items:stretch}.msup-field,.msup-search{width:100%;min-width:0}.msup-btn{width:100%}}
</style>
@endpush
