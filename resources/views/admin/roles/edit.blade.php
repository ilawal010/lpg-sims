@extends('layouts.admin')

@section('title', 'Manage Role Permissions')
@section('header', 'Roles & Permissions')

@php
    $isOwner = $role->name === 'owner';
@endphp

@push('styles')
<style>
    .permission-header {
        background: #ffffff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 22px;
        margin-bottom: 22px;
    }

    .permission-role {
        font-size: 19px;
        font-weight: 750;
        color: #20352b;
        margin-bottom: 7px;
    }

    .permission-description {
        font-size: 12px;
        color: #78877e;
        line-height: 1.7;
    }

    .permission-group {
        background: #ffffff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        margin-bottom: 18px;
        overflow: hidden;
    }

    .permission-group-header {
        background: #f7faf8;
        border-bottom: 1px solid #e3ebe6;
        padding: 16px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
    }

    .permission-group-title {
        font-size: 13px;
        font-weight: 750;
        color: #254532;
        text-transform: capitalize;
    }

    .permission-group-count {
        color: #829087;
        font-size: 11px;
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0;
    }

    .permission-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 16px 20px;
        border-bottom: 1px solid #f0f3f1;
        cursor: pointer;
    }

    .permission-item:hover {
        background: #fafcfb;
    }

    .permission-item input {
        width: 17px;
        height: 17px;
        accent-color: #11633e;
        margin-top: 2px;
        flex-shrink: 0;
    }

    .permission-name {
        font-size: 13px;
        font-weight: 650;
        color: #20352b;
    }

    .permission-code {
        font-size: 11px;
        color: #89968d;
        margin-top: 5px;
        overflow-wrap: anywhere;
    }

    .permission-actions {
        position: sticky;
        bottom: 0;
        background: #ffffff;
        border: 1px solid #e3ebe6;
        border-radius: 12px;
        padding: 17px 20px;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        box-shadow: 0 -3px 15px rgba(0,0,0,.035);
    }

    .permission-warning {
        color: #906018;
        background: #fff8e8;
        border: 1px solid #f1dfb4;
        border-radius: 8px;
        padding: 13px 16px;
        font-size: 12px;
        margin-bottom: 20px;
        line-height: 1.7;
    }

    .permission-error {
        color: #b91c1c;
        font-size: 12px;
        margin-bottom: 16px;
    }

    @media (max-width: 700px) {
        .permission-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">
            {{ $isOwner ? 'View' : 'Manage' }} Role Permissions
        </h1>

        <p class="page-description">
            Configure access rights for the selected system role.
        </p>
    </div>

    <a href="{{ route('admin.roles.index') }}"
       class="btn btn-secondary">
        Back to Roles
    </a>
</div>

<div class="permission-header">
    <div class="permission-role">
        {{ $role->display_name }}
    </div>

    <div class="permission-description">
        {{ $role->description ?: 'LPG-SIMS system role' }}
    </div>
</div>

@if($isOwner)
    <div class="permission-warning">
        The Owner / Super Admin role is protected and has
        unrestricted access to all system functions.
        Its permissions cannot be modified from this page.
    </div>
@endif

@if($errors->any())
    <div class="alert-error">
        Please review the selected permissions.

        @foreach($errors->all() as $error)
            <div class="permission-error">{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST"
      action="{{ route('admin.roles.update', $role) }}">

    @csrf
    @method('PUT')

    @foreach($permissions as $module => $modulePermissions)

        <div class="permission-group">

            <div class="permission-group-header">

                <div class="permission-group-title">
                    {{ ucwords(str_replace(['_', '-'], ' ', $module)) }}
                </div>

                <div class="permission-group-count">
                    {{ $modulePermissions->count() }} permissions
                </div>

            </div>

            <div class="permission-grid">

                @foreach($modulePermissions as $permission)

                    @php
                        $selected = old('permissions') !== null
                            ? in_array(
                                $permission->id,
                                array_map('intval', (array) old('permissions', [])),
                                true
                            )
                            : (
                                $isOwner ||
                                in_array(
                                    $permission->id,
                                    $assignedPermissions,
                                    true
                                )
                            );
                    @endphp

                    <label class="permission-item">

                        <input
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->id }}"
                            @checked($selected)
                            @disabled($isOwner)
                        >

                        <span>
                            <div class="permission-name">
                                {{ $permission->display_name }}
                            </div>

                            <div class="permission-code">
                                {{ $permission->name }}
                            </div>
                        </span>

                    </label>

                @endforeach

            </div>

        </div>

    @endforeach

    <div class="permission-actions">

        @unless($isOwner)
            <button type="submit"
                    class="btn btn-primary"
                    onclick="return confirm('Save the selected permissions for this role?')">
                Save Permissions
            </button>
        @endunless

        <a href="{{ route('admin.roles.index') }}"
           class="btn btn-secondary">
            {{ $isOwner ? 'Back to Roles' : 'Cancel' }}
        </a>

    </div>

</form>

@endsection
