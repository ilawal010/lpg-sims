@extends('layouts.admin')

@section('title', 'Audit Logs')
@section('header', 'Audit Logs')

@push('styles')
<style>
    .audit-heading {
        margin-bottom: 22px;
    }

    .audit-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .audit-heading p {
        margin: 0;
        font-size: 13px;
        color: #718377;
    }

    .audit-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
        margin-bottom: 22px;
    }

    .audit-stat,
    .audit-panel {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
    }

    .audit-stat {
        padding: 20px;
    }

    .audit-stat-label {
        font-size: 12px;
        color: #748578;
        margin-bottom: 9px;
    }

    .audit-stat-value {
        font-size: 28px;
        font-weight: 750;
        color: #12623d;
    }

    .audit-panel {
        margin-bottom: 20px;
        overflow: hidden;
    }

    .audit-panel-header {
        padding: 18px 20px;
        border-bottom: 1px solid #e7eee9;
        font-weight: 700;
        font-size: 14px;
        color: #264634;
    }

    .audit-filter-form {
        padding: 20px;
    }

    .audit-filter-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 15px;
    }

    .audit-field label {
        display: block;
        margin-bottom: 7px;
        font-size: 12px;
        font-weight: 650;
        color: #42584a;
    }

    .audit-field select,
    .audit-field input {
        width: 100%;
        padding: 10px 11px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-size: 13px;
        box-sizing: border-box;
    }

    .audit-filter-actions {
        display: flex;
        gap: 10px;
        margin-top: 17px;
    }

    .audit-button {
        display: inline-block;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid #cdded2;
        background: #fff;
        color: #17623e;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .audit-button.primary {
        background: #11643e;
        border-color: #11643e;
        color: #fff;
    }

    .audit-table-wrap {
        overflow-x: auto;
    }

    .audit-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .audit-table th {
        padding: 14px;
        text-align: left;
        background: #f7faf8;
        color: #65796b;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        border-bottom: 1px solid #e3ebe6;
        white-space: nowrap;
    }

    .audit-table td {
        padding: 15px 14px;
        border-bottom: 1px solid #edf2ee;
        color: #33483a;
        vertical-align: middle;
    }

    .audit-table tr:last-child td {
        border-bottom: none;
    }

    .audit-table a {
        color: #11643e;
        font-weight: 700;
        text-decoration: none;
    }

    .audit-action {
        display: inline-block;
        padding: 5px 9px;
        background: #eaf5ee;
        color: #17643b;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 650;
        text-transform: capitalize;
    }

    .audit-description {
        max-width: 280px;
        line-height: 1.5;
    }

    .audit-pagination {
        padding: 16px 20px;
    }

    .audit-empty {
        text-align: center;
        padding: 38px 20px !important;
        color: #849188 !important;
    }

    .audit-errors {
        padding: 12px 16px;
        margin-bottom: 18px;
        background: #fff0f0;
        border: 1px solid #f3caca;
        color: #a32929;
        border-radius: 8px;
        font-size: 12px;
    }

    @media (max-width: 850px) {
        .audit-filter-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        .audit-stats,
        .audit-filter-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="audit-heading">
    <h1>Audit Logs</h1>
    <p>
        Review administrative activities and system changes.
        Audit records are read-only.
    </p>
</div>

<div class="audit-stats">

    <div class="audit-stat">
        <div class="audit-stat-label">Total Audit Records</div>
        <div class="audit-stat-value">
            {{ number_format($totalLogs) }}
        </div>
    </div>

    <div class="audit-stat">
        <div class="audit-stat-label">Activities Today</div>
        <div class="audit-stat-value">
            {{ number_format($todayLogs) }}
        </div>
    </div>

    <div class="audit-stat">
        <div class="audit-stat-label">Recorded Users</div>
        <div class="audit-stat-value">
            {{ number_format($uniqueUsers) }}
        </div>
    </div>

</div>

@if($errors->any())
    <div class="audit-errors">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<div class="audit-panel">

    <div class="audit-panel-header">
        Filter Audit Records
    </div>

    <form method="GET"
          action="{{ route('admin.audit-logs.index') }}"
          class="audit-filter-form">

        <div class="audit-filter-grid">

            <div class="audit-field">
                <label for="user_id">Staff / User</label>

                <select name="user_id" id="user_id">
                    <option value="">All Users</option>

                    @foreach($users as $user)
                        <option value="{{ $user->id }}"
                            @selected(request('user_id') == $user->id)>
                            {{ $user->name }}
                            {{ $user->staff_number ? '(' . $user->staff_number . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="audit-field">
                <label for="module">Module</label>

                <select name="module" id="module">
                    <option value="">All Modules</option>

                    @foreach($modules as $module)
                        <option value="{{ $module }}"
                            @selected(request('module') === $module)>
                            {{ ucwords(str_replace('_', ' ', $module)) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="audit-field">
                <label for="action">Action</label>

                <select name="action" id="action">
                    <option value="">All Actions</option>

                    @foreach($actions as $action)
                        <option value="{{ $action }}"
                            @selected(request('action') === $action)>
                            {{ ucfirst($action) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="audit-field">
                <label for="date_from">Date From</label>

                <input type="date"
                       name="date_from"
                       id="date_from"
                       value="{{ request('date_from') }}">
            </div>

            <div class="audit-field">
                <label for="date_to">Date To</label>

                <input type="date"
                       name="date_to"
                       id="date_to"
                       value="{{ request('date_to') }}">
            </div>

        </div>

        <div class="audit-filter-actions">
            <button type="submit" class="audit-button primary">
                Apply Filters
            </button>

            <a href="{{ route('admin.audit-logs.index') }}"
               class="audit-button">
                Reset
            </a>
        </div>

    </form>
</div>

<div class="audit-panel">

    <div class="audit-panel-header">
        Activity Register
    </div>

    <div class="audit-table-wrap">

        <table class="audit-table">

            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>User</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Description</th>
                    <th>IP Address</th>
                    <th>Details</th>
                </tr>
            </thead>

            <tbody>

                @forelse($logs as $log)

                    <tr>
                        <td style="white-space:nowrap;">
                            {{ $log->created_at?->format('d M Y') }}
                            <div style="color:#849188;margin-top:4px;">
                                {{ $log->created_at?->format('h:i A') }}
                            </div>
                        </td>

                        <td>
                            <strong>
                                {{ $log->user?->name ?? 'System / Unknown' }}
                            </strong>

                            @if($log->user?->staff_number)
                                <div style="color:#849188;margin-top:4px;">
                                    {{ $log->user->staff_number }}
                                </div>
                            @endif
                        </td>

                        <td>
                            {{ ucwords(str_replace('_', ' ', $log->module)) }}
                        </td>

                        <td>
                            <span class="audit-action">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td>
                            <div class="audit-description">
                                {{ $log->description ?: 'No description' }}
                            </div>
                        </td>

                        <td>{{ $log->ip_address ?: '—' }}</td>

                        <td>
                            <a href="{{ route('admin.audit-logs.show', $log) }}">
                                View
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="audit-empty">
                            No audit records match the selected filters.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>

    </div>

    @if($logs->hasPages())
        <div class="audit-pagination">
            {{ $logs->links() }}
        </div>
    @endif

</div>

@endsection
