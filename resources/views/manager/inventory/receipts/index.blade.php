@extends('layouts.manager')

@section('title', 'LPG Supply Receipts')

@section('content')
<div class="mr-receipts">
    <div class="mr-heading">
        <div>
            <div class="mr-eyebrow">INVENTORY / LPG SUPPLY</div>
            <h1>LPG Supply Receipts</h1>
            <p>Supplier deliveries and receipt posting for your assigned branches.</p>
        </div>
        @if(auth()->user()?->hasPermission('inventory.receive'))
            <a class="mr-btn mr-btn-primary" href="{{ route('manager.stock-receipts.create') }}">+ New Supply Receipt</a>
        @endif
    </div>

    @if(session('success'))
        <div class="mr-alert mr-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mr-alert mr-error" role="alert">
            <strong>Unable to complete the request.</strong>
            <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="mr-panel">
        <div class="mr-panel-heading"><h2>Filter receipts</h2><span>Only authorized branches are available</span></div>
        <form method="GET" action="{{ route('manager.stock-receipts.index') }}" class="mr-filters">
            <label>Search receipt or supplier
                <input type="search" name="search" value="{{ $filters['search'] ?? '' }}" maxlength="150" placeholder="Receipt, invoice, supplier...">
            </label>
            <label>Status
                <select name="status">
                    <option value="">All statuses</option>
                    @foreach(['draft' => 'Draft', 'posted' => 'Posted', 'cancelled' => 'Cancelled'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>Branch
                <select name="branch_id">
                    <option value="">All assigned branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string)($filters['branch_id'] ?? '') === (string)$branch->id)>{{ $branch->branch_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>From date
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </label>
            <label>To date
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </label>
            <div class="mr-filter-actions">
                <button type="submit" class="mr-btn mr-btn-primary">Apply Filters</button>
                <a class="mr-btn mr-btn-outline" href="{{ route('manager.stock-receipts.index') }}">Reset</a>
            </div>
        </form>
    </div>

    <div class="mr-panel">
        <div class="mr-panel-heading">
            <h2>Receipt Register</h2>
            <span>{{ number_format($receipts->total()) }} {{ \Illuminate\Support\Str::plural('receipt', $receipts->total()) }}</span>
        </div>
        <div class="mr-table-scroll">
            <table class="mr-table">
                <thead><tr>
                    <th>Receipt / Date</th><th>Supplier</th><th>Branch / Storage</th>
                    <th class="mr-num">Quantity (kg)</th><th>Status</th><th>Recorded by</th><th>Actions</th>
                </tr></thead>
                <tbody>
                    @forelse($receipts as $receipt)
                        <tr>
                            <td>
                                <strong>{{ $receipt->receipt_number }}</strong>
                                <small>{{ $receipt->receipt_date?->format('d M Y') ?? '—' }}</small>
                                @if($receipt->supplier_invoice_number)
                                    <small>Invoice: {{ $receipt->supplier_invoice_number }}</small>
                                @endif
                            </td>
                            <td>{{ $receipt->supplier?->supplier_name ?? '—' }}</td>
                            <td>
                                <strong>{{ $receipt->branch?->branch_name ?? '—' }}</strong>
                                <small>{{ $receipt->storageUnit?->storage_name ?? '—' }}</small>
                            </td>
                            <td class="mr-num">{{ number_format((float)$receipt->quantity_kg, 3) }}</td>
                            <td><span class="mr-status mr-status-{{ $receipt->status }}">{{ ucfirst($receipt->status) }}</span></td>
                            <td>
                                {{ $receipt->createdBy?->name ?? '—' }}
                                @if($receipt->isPosted())
                                    <small>Posted by: {{ $receipt->postedBy?->name ?? '—' }}</small>
                                @endif
                            </td>
                            <td>
                                @if($receipt->isDraft() && auth()->user()?->hasPermission('inventory.receive'))
                                    <div class="mr-actions">
                                        <a class="mr-action-link" href="{{ route('manager.stock-receipts.edit', $receipt) }}">Edit</a>
                                        <form method="POST" action="{{ route('manager.stock-receipts.post', $receipt) }}" onsubmit="return confirm('Post receipt {{ $receipt->receipt_number }}? This will add {{ number_format((float)$receipt->quantity_kg, 3) }} kg to LPG stock and cannot be undone from this page.');">
                                            @csrf
                                            <button type="submit" class="mr-action-post">Post</button>
                                        </form>
                                        <form method="POST" action="{{ route('manager.stock-receipts.cancel', $receipt) }}" onsubmit="return confirm('Cancel draft receipt {{ $receipt->receipt_number }}?');">
                                            @csrf
                                            <button type="submit" class="mr-action-cancel">Cancel</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="mr-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mr-empty">No LPG supply receipts match the selected filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($receipts->hasPages())
            <div class="mr-pagination">{{ $receipts->links() }}</div>
        @endif
    </div>
    <p class="mr-note">Only posting a draft receipt changes LPG stock. Inventory valuation, weighted-average cost, COGS and profitability are not displayed here.</p>
</div>
@endsection

@push('styles')
<style>
.mr-receipts{max-width:1500px;margin:0 auto;color:#1e293b}.mr-heading{display:flex;align-items:center;justify-content:space-between;gap:18px;flex-wrap:wrap;margin-bottom:22px}.mr-eyebrow{font-size:11px;letter-spacing:.12em;font-weight:800;color:#14834a}.mr-heading h1{font-size:27px;font-weight:800;margin:5px 0}.mr-heading p{margin:0;color:#64748b;font-size:14px}.mr-btn{display:inline-flex;align-items:center;justify-content:center;border-radius:9px;padding:10px 15px;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;border:1px solid transparent;white-space:nowrap}.mr-btn-primary{background:#147d49;color:#fff}.mr-btn-primary:hover{background:#0e6037;color:#fff}.mr-btn-outline{background:#fff;border-color:#cbd5e1;color:#334155}.mr-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin-bottom:18px;overflow:hidden;box-shadow:0 2px 8px rgba(15,23,42,.035)}.mr-panel-heading{padding:18px 20px;display:flex;justify-content:space-between;gap:10px;align-items:center;border-bottom:1px solid #e2e8f0}.mr-panel-heading h2{font-size:16px;margin:0;font-weight:800}.mr-panel-heading span{font-size:12px;color:#64748b}.mr-filters{padding:18px 20px;display:grid;grid-template-columns:repeat(5,minmax(140px,1fr));gap:13px;align-items:end}.mr-filters label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:700;color:#475569}.mr-filters input,.mr-filters select{width:100%;min-width:0;padding:10px 11px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#0f172a;font-size:13px}.mr-filter-actions{display:flex;gap:8px;align-items:center;grid-column:1/-1}.mr-table-scroll{overflow-x:auto}.mr-table{width:100%;border-collapse:collapse;font-size:13px}.mr-table th{background:#f8fafc;color:#475569;font-size:11px;text-transform:uppercase;letter-spacing:.04em;text-align:left;white-space:nowrap;padding:13px 15px;border-bottom:1px solid #e2e8f0}.mr-table td{padding:14px 15px;border-bottom:1px solid #f1f5f9;vertical-align:top}.mr-table td strong{display:block;font-weight:750;color:#0f172a}.mr-table td small{display:block;color:#64748b;margin-top:5px;font-size:11px}.mr-table tr:last-child td{border-bottom:0}.mr-num{text-align:right!important;white-space:nowrap;font-variant-numeric:tabular-nums}.mr-status{display:inline-block;padding:5px 10px;border-radius:999px;font-size:11px;font-weight:800}.mr-status-draft{background:#fef3c7;color:#92400e}.mr-status-posted{background:#dcfce7;color:#166534}.mr-status-cancelled{background:#fee2e2;color:#991b1b}.mr-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.mr-actions form{margin:0}.mr-actions button,.mr-action-link{border:0;background:none;padding:0;font-size:12px;font-weight:800;cursor:pointer;text-decoration:none}.mr-action-link{color:#2563eb}.mr-action-post{color:#15803d}.mr-action-cancel{color:#b91c1c}.mr-muted{color:#94a3b8}.mr-empty{text-align:center;padding:35px!important;color:#64748b}.mr-pagination{padding:16px 20px;border-top:1px solid #e2e8f0}.mr-alert{padding:13px 16px;border-radius:9px;margin-bottom:15px;font-size:13px}.mr-success{background:#ecfdf5;color:#166534;border:1px solid #a7f3d0}.mr-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}.mr-alert ul{margin:7px 0 0;padding-left:20px}.mr-note{font-size:12px;color:#64748b;margin:12px 0 25px}@media(max-width:1100px){.mr-filters{grid-template-columns:repeat(3,minmax(150px,1fr))}}@media(max-width:650px){.mr-filters{grid-template-columns:1fr}.mr-heading h1{font-size:23px}.mr-panel-heading{align-items:flex-start;flex-direction:column}}
</style>
@endpush
