@extends('layouts.admin')

@section('title', 'Staff Management')
@section('header', 'Staff Management')

@push('styles')
<style>
    .staff-table-wrap {
        overflow-x: auto;
    }

    .staff-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .staff-table th {
        background: #f7faf8;
        color: #667a6d;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .5px;
        text-align: left;
        padding: 15px 16px;
        border-bottom: 1px solid #e5ece7;
    }

    .staff-table td {
        padding: 16px;
        border-bottom: 1px solid #edf1ee;
        vertical-align: middle;
    }

    .staff-table tr:last-child td {
        border-bottom: none;
    }

    .staff-person {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .staff-avatar {
        width: 38px;
        height: 38px;
        flex-shrink: 0;
        border-radius: 10px;
        background: #e7f3eb;
        color: #12643d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 750;
    }

    .staff-name {
        font-weight: 650;
        color: #263b2f;
    }

    .staff-email {
        font-size: 12px;
        color: #819087;
        margin-top: 4px;
    }

    .role-badge {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        background: #eaf4ed;
        color: #196740;
        font-size: 11px;
        font-weight: 650;
    }

    .role-owner {
        background: #e9edf9;
        color: #394c93;
    }

    .role-manager {
        background: #fff3de;
        color: #956314;
    }

    .edit-link {
        display: inline-block;
        padding: 8px 12px;
        border: 1px solid #dce8df;
        border-radius: 7px;
        color: #17653e;
        font-weight: 650;
        font-size: 12px;
    }

    .edit-link:hover {
        background: #eff7f1;
    }

    .empty-state {
        text-align: center;
        padding: 55px 20px;
        color: #7b8a80;
    }

    .staff-count {
        font-size: 12px;
        color: #809086;
        margin-bottom: 16px;
    }

    .pagination-wrap {
        margin-top: 20px;
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Staff Management</h1>

        <p class="page-description">
            Manage authorized users and their access roles.
        </p>
    </div>

    <a href="{{ route('admin.users.create') }}"
       class="btn btn-primary">
        + Add Staff
    </a>
</div>

<div class="card">

    <div class="staff-count">
        Total registered users: {{ $users->total() }}
    </div>

    <div class="staff-table-wrap">
        <table class="staff-table">

            <thead>
                <tr>
                    <th>Staff</th>
                    <th>Staff Number</th>
                    <th>Phone Number</th>
                    <th>Role</th>
                    <th>Date Created</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="staff-person">
                                <div class="staff-avatar">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>

                                <div>
                                    <div class="staff-name">
                                        {{ $user->name }}
                                    </div>

                                    <div class="staff-email">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $user->staff_number ?? 'Not Assigned' }}</strong>
                        </td>
                        <td>
                            {{ $user->phone ?? '—' }}
                        </td>
                        <td>
                            <span class="role-badge
                                {{ $user->role?->name === 'owner' ? 'role-owner' : '' }}
                                {{ $user->role?->name === 'manager' ? 'role-manager' : '' }}">
                                {{ $user->role?->display_name ?? 'Unassigned' }}
                            </span>
                        </td>

                        <td>
                            {{ $user->created_at?->format('d M Y') }}
                        </td>

                        <td style="text-align:right;">
                            @if($user->hasRole('owner'))
                                <span style="font-size:11px;color:#849188;">
                                    Protected
                                </span>
                            @else
                                <a href="{{ route('admin.users.edit', $user) }}"
                                   class="edit-link">
                                    Edit
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                No staff accounts found.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>

        </table>
    </div>

    @if($users->hasPages())
        <div class="pagination-wrap">
            {{ $users->links() }}
        </div>
    @endif

</div>

@endsection
