@extends('layouts.admin')

@section('title', 'LPG Stock Movement Details')

@section('content')
<style>
    .md-page {
        color: #24352e;
    }

    .md-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 24px;
    }

    .md-header h1 {
        margin: 0 0 6px;
        color: #174d35;
        font-size: 25px;
        font-weight: 800;
    }

    .md-header p {
        margin: 0;
        color: #718078;
        font-size: 13px;
    }

    .md-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 16px;
        border: 1px solid #dce6df;
        border-radius: 9px;
        background: #fff;
        color: #176b45;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .md-btn:hover {
        background: #edf6ef;
    }

    .md-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 20px;
        align-items: start;
    }

    .md-panel {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .md-panel-head {
        padding: 18px 20px;
        border-bottom: 1px solid #e8eee9;
    }

    .md-panel-head h2 {
        margin: 0;
        color: #174d35;
        font-size: 16px;
        font-weight: 800;
    }

    .md-panel-head p {
        margin: 5px 0 0;
        color: #849188;
        font-size: 12px;
    }

    .md-panel-body {
        padding: 20px;
    }

    .md-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
    }

    .md-field-label {
        margin-bottom: 6px;
        color: #819087;
        font-size: 11px;
        font-weight: 650;
    }

    .md-field-value {
        color: #24352e;
        font-size: 13px;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .md-field-value.primary {
        color: #176b45;
    }

    .md-badge {
        display: inline-block;
        padding: 6px 11px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 750;
    }

    .md-badge.in {
        background: #e5f5eb;
        color: #167245;
    }

    .md-badge.out {
        background: #fff0e6;
        color: #ae602a;
    }

    .md-amount {
        color: #174d35;
        font-size: 25px;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .md-note {
        padding: 14px;
        background: #f5f9f6;
        border: 1px solid #e3ece5;
        border-radius: 9px;
        color: #52685b;
        font-size: 12px;
        line-height: 1.7;
        overflow-wrap: anywhere;
    }

    .md-audit-row {
        padding: 13px 0;
        border-bottom: 1px solid #edf1ee;
    }

    .md-audit-row:last-child {
        border-bottom: none;
    }

    @media (max-width: 950px) {
        .md-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .md-header h1 {
            font-size: 21px;
        }

        .md-fields {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="md-page">

    <div class="md-header">
        <div>
            <h1>LPG Stock Movement Details</h1>
            <p>
                Movement Reference:
                <strong>{{ $movement->movement_reference }}</strong>
            </p>
        </div>

        <a href="{{ route('admin.lpg-stock.movements') }}"
           class="md-btn">
            ← Back to Movement History
        </a>
    </div>

    <div class="md-grid">

        <div>

            <div class="md-panel">
                <div class="md-panel-head">
                    <h2>Movement Information</h2>
                    <p>Recorded LPG stock transaction details.</p>
                </div>

                <div class="md-panel-body">
                    <div class="md-fields">

                        <div>
                            <div class="md-field-label">Movement Reference</div>
                            <div class="md-field-value primary">
                                {{ $movement->movement_reference }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Movement Date</div>
                            <div class="md-field-value">
                                {{ $movement->movement_date
                                    ? \Illuminate\Support\Carbon::parse($movement->movement_date)->format('d M Y')
                                    : '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Movement Type</div>
                            <div class="md-field-value">
                                {{ ucwords(str_replace('_', ' ', $movement->movement_type)) }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Direction</div>
                            <span class="md-badge {{ $movement->direction === 'in' ? 'in' : 'out' }}">
                                {{ strtoupper($movement->direction) }}
                            </span>
                        </div>

                        <div>
                            <div class="md-field-label">Branch</div>
                            <div class="md-field-value">
                                {{ $movement->branch?->branch_name ?? '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Storage Unit</div>
                            <div class="md-field-value">
                                {{ $movement->storageUnit?->storage_name ?? '—' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="md-panel">
                <div class="md-panel-head">
                    <h2>Quantity and Costing</h2>
                    <p>Financial values recorded in the stock ledger.</p>
                </div>

                <div class="md-panel-body">
                    <div class="md-fields">

                        <div>
                            <div class="md-field-label">Quantity</div>
                            <div class="md-amount">
                                {{ number_format($movement->quantity_kg, 3) }} kg
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Total Cost</div>
                            <div class="md-amount">
                                ₦{{ number_format($movement->total_cost ?? 0, 2) }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Unit Cost per kg</div>
                            <div class="md-field-value">
                                ₦{{ number_format($movement->unit_cost ?? 0, 2) }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Movement Direction</div>
                            <div class="md-field-value">
                                {{ $movement->direction === 'in' ? 'Stock Addition' : 'Stock Deduction' }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="md-panel">
                <div class="md-panel-head">
                    <h2>Source Transaction</h2>
                    <p>Original document and transaction references.</p>
                </div>

                <div class="md-panel-body">
                    <div class="md-fields">

                        <div>
                            <div class="md-field-label">Document Reference</div>
                            <div class="md-field-value primary">
                                {{ $movement->document_reference ?: '—' }}
                            </div>
                        </div>

                        <div>
                            <div class="md-field-label">Transaction Group</div>
                            <div class="md-field-value">
                                {{ $movement->transaction_group ?: '—' }}
                            </div>
                        </div>

                    </div>

                    <div style="margin-top:20px;">
                        <div class="md-field-label">Description</div>
                        <div class="md-note">
                            {{ $movement->description ?: 'No description recorded.' }}
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div>

            <div class="md-panel">
                <div class="md-panel-head">
                    <h2>Audit Information</h2>
                    <p>System-recorded transaction history.</p>
                </div>

                <div class="md-panel-body">

                    <div class="md-audit-row">
                        <div class="md-field-label">Movement ID</div>
                        <div class="md-field-value">
                            {{ $movement->id }}
                        </div>
                    </div>

                    <div class="md-audit-row">
                        <div class="md-field-label">Recorded By</div>
                        <div class="md-field-value">
                            {{ $movement->createdBy?->name ?? '—' }}
                        </div>
                    </div>

                    <div class="md-audit-row">
                        <div class="md-field-label">Date Created</div>
                        <div class="md-field-value">
                            {{ $movement->created_at
                                ? $movement->created_at->format('d M Y, h:i A')
                                : '—' }}
                        </div>
                    </div>

                    <div class="md-audit-row">
                        <div class="md-field-label">Last Updated</div>
                        <div class="md-field-value">
                            {{ $movement->updated_at
                                ? $movement->updated_at->format('d M Y, h:i A')
                                : '—' }}
                        </div>
                    </div>

                    <div style="margin-top:20px;" class="md-note">
                        This is a read-only inventory ledger record.
                        Stock quantities and financial values cannot be
                        changed from this page.
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
@endsection
