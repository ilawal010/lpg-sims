@extends('layouts.admin')

@section('title', 'Roles & Permissions')
@section('header', 'Roles & Permissions')

@push('styles')
<style>
    .roles-summary {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 24px;
    }

    .role-stat {
        background: #fff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 22px;
    }

    .role-stat-label {
        color: #78877e;
        font-size: 12px;
        margin-bottom: 10px;
    }

    .role-stat-value {
        color: #11633e;
        font-size: 28px;
        font-weight: 750;
    }

    .roles-table-wrap {
        overflow-x: auto;
    }

    .roles-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .roles-table th {
        padding: 15px;
        background: #f7faf8;
        color: #6a7c70;
        font-size: 11px;
        text-align: left;
        text-transform: uppercase;
        letter-spacing: .5px;
        border-bottom: 1px solid #e3ebe6;
    }

    .roles-table td {
        padding: 18px 15px;
        border-bottom: 1px solid #edf1ee;
        vertical-align: middle;
    }

    .roles-table tr:last-child td {
        border-bottom: none;
    }

    .role-title {
        font-weight: 700;
        color: #20352b;
    }

    .role-description {
        margin-top: 5px;
        color: #819087;
        font-size: 12px;
    }

    .role-badge {
        display: inline-block;
        padding: 6px 11px;
        border-radius: 20px;
        background: #eaf5ee;
        color: #17643b;
        font-size: 11px;
        font-weight: 650;
    }

    .role-badge.protected {
        background: #e9edf9;
        color: #40518d;
    }

    .role-action {
        display: inline-block;
        padding: 9px 13px;
        border: 1px solid #cfe3d5;
        border-radius: 7px;
        color: #11633e;
        font-size: 12px;
        font-weight: 650;
    }

    .role-action:hover {
        background: #eff7f1;
    }

    .roles-note {
        margin-top: 20px;
        padding: 15px 18px;
        border: 1px solid #e3ebe6;
        border-radius: 9px;
        background: #f7faf8;
        color: #64776b;
        font-size: 12px;
        line-height: 1.7;
    }

    @media (max-width: 750px) {
        .roles-summary {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Roles & Permissions</h1>
        <p class="page-description">
            Manage staff access to LPG-SIMS modules and functions.
        </p>
    </div>
</div>

<div class="roles-summary">

    <div class="role-stat">
        <div class="role-stat-label">System Roles</div>
        <div class="role-stat-value">{{ $roles->count() }}</div>
    </div>

    <div class="role-stat">
        <div class="role-stat-label">Registered Users</div>
        <div class="role-stat-value">
            {{ $roles->sum('users_count') }}
        </div>
    </div>

    <div class="role-stat">
        <div class="role-stat-label">Available Permissions</div>
        <div class="role-stat-value">
            {{ \App\Models\Permission::count() }}
        </div>
    </div>

</div>

<div class="card">

    <div class="roles-table-wrap">

        <table class="roles-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Assigned Users</th>
                    <th>Permissions</th>
                    <th>Status</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>

            <tbody>

                @forelse($roles as $role)

                    <tr>
                        <td>
                            <div class="role-title">
                                {{ $role->display_name }}
                            </div>

                            <div class="role-description">
                                {{ $role->description ?: 'System access role' }}
                            </div>
                        </td>

                        <td>{{ $role->users_count }}</td>

                        <td>
                            <strong>{{ $role->permissions_count }}</strong>
                            permissions
                        </td>

                        <td>
                            @if($role->name === 'owner')
                                <span class="role-badge protected">
                                    Protected
                                </span>
                            @else
                                <span class="role-badge">
                                    Configurable
                                </span>
                            @endif
                        </td>

                        <td style="text-align:right;">
                            <a href="{{ route('admin.roles.edit', $role) }}"
                               class="role-action">
                                {{ $role->name === 'owner'
                                    ? 'View Permissions'
                                    : 'Manage Permissions' }}
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5" style="text-align:center;padding:35px;">
                            No roles found.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>

    </div>

    <div class="roles-note">
        <strong>Access Control:</strong>
        The Owner / Super Admin role has unrestricted access.
        Manager and Sales Attendant permissions can be configured
        by the Owner. Changes take effect when authorization is
        checked on subsequent requests.
    </div>

</div>

@endsection
