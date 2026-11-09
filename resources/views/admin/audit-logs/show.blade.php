@extends('layouts.admin')

@section('title', 'Audit Record Details')
@section('header', 'Audit Logs')

@push('styles')
<style>
    .audit-detail-header {
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .audit-detail-header h1 {
        margin: 0 0 6px;
        font-size: 22px;
        color: #183d2a;
    }

    .audit-detail-header p {
        margin: 0;
        font-size: 12px;
        color: #79887e;
    }

    .audit-back {
        display: inline-block;
        padding: 10px 15px;
        background: #fff;
        border: 1px solid #d6e4da;
        border-radius: 8px;
        color: #11643e;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .audit-detail-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        margin-bottom: 20px;
        overflow: hidden;
    }

    .audit-detail-title {
        padding: 17px 20px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
        color: #254532;
        font-size: 14px;
        font-weight: 750;
    }

    .audit-detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        padding: 22px;
    }

    .audit-detail-label {
        color: #7d8e82;
        font-size: 11px;
        margin-bottom: 7px;
    }

    .audit-detail-value {
        color: #293f30;
        font-size: 13px;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .audit-json {
        margin: 0;
        padding: 20px;
        background: #fbfcfb;
        color: #314b39;
        font-size: 12px;
        line-height: 1.7;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
        font-family: Consolas, monospace;
    }

    .audit-readonly {
        padding: 15px 20px;
        color: #687e6d;
        font-size: 12px;
        background: #f7faf8;
        border: 1px solid #e2ebe5;
        border-radius: 9px;
    }

    @media (max-width: 650px) {
        .audit-detail-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="audit-detail-header">
    <div>
        <h1>Audit Record #{{ $auditLog->id }}</h1>
        <p>Detailed information about this recorded activity.</p>
    </div>

    <a href="{{ route('admin.audit-logs.index') }}"
       class="audit-back">
        Back to Audit Logs
    </a>
</div>

<div class="audit-detail-card">

    <div class="audit-detail-title">
        Activity Information
    </div>

    <div class="audit-detail-grid">

        <div>
            <div class="audit-detail-label">Action</div>
            <div class="audit-detail-value">
                {{ ucfirst($auditLog->action) }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Module</div>
            <div class="audit-detail-value">
                {{ ucwords(str_replace('_', ' ', $auditLog->module)) }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Performed By</div>
            <div class="audit-detail-value">
                {{ $auditLog->user?->name ?? 'System / Unknown' }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Staff Number</div>
            <div class="audit-detail-value">
                {{ $auditLog->user?->staff_number ?? '—' }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Date & Time</div>
            <div class="audit-detail-value">
                {{ $auditLog->created_at?->format('d M Y, h:i:s A') }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">IP Address</div>
            <div class="audit-detail-value">
                {{ $auditLog->ip_address ?: '—' }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Record Type</div>
            <div class="audit-detail-value">
                {{ $auditLog->record_type ?: '—' }}
            </div>
        </div>

        <div>
            <div class="audit-detail-label">Record ID</div>
            <div class="audit-detail-value">
                {{ $auditLog->record_id ?? '—' }}
            </div>
        </div>

        <div style="grid-column:1/-1;">
            <div class="audit-detail-label">Description</div>
            <div class="audit-detail-value">
                {{ $auditLog->description ?: 'No description provided.' }}
            </div>
        </div>

        <div style="grid-column:1/-1;">
            <div class="audit-detail-label">Browser / Device</div>
            <div class="audit-detail-value">
                {{ $auditLog->user_agent ?: '—' }}
            </div>
        </div>

    </div>
</div>

<div class="audit-detail-card">

    <div class="audit-detail-title">
        Previous Values
    </div>

    <pre class="audit-json">{{ json_encode(
        $auditLog->old_values ?? [],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) }}</pre>

</div>

<div class="audit-detail-card">

    <div class="audit-detail-title">
        New Values
    </div>

    <pre class="audit-json">{{ json_encode(
        $auditLog->new_values ?? [],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
    ) }}</pre>

</div>

<div class="audit-readonly">
    This is a read-only audit record. Modification and deletion
    are not available through the LPG-SIMS interface.
</div>

@endsection
