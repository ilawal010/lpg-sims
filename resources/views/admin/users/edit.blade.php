@extends('layouts.admin')

@section('title', 'Edit Staff')
@section('header', 'Edit Staff')

@push('styles')
<style>
    .staff-form {
        max-width: 780px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 650;
        color: #344a3d;
        margin-bottom: 9px;
    }

    .form-input {
        width: 100%;
        padding: 12px 13px;
        border: 1px solid #dce7df;
        border-radius: 8px;
        outline: none;
        background: white;
        color: #263b2f;
        font-size: 13px;
    }

    .form-input:focus {
        border-color: #16814b;
        box-shadow: 0 0 0 3px rgba(22,129,75,.08);
    }

    .form-hint {
        margin-top: 7px;
        font-size: 11px;
        color: #89958d;
    }

    .form-error {
        margin-top: 6px;
        color: #c12d2d;
        font-size: 11px;
    }

    .form-actions {
        display: flex;
        gap: 12px;
        padding-top: 22px;
        border-top: 1px solid #e9efeb;
    }

    @media (max-width: 650px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 0;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Staff Account</h1>
        <p class="page-description">
            Update employee information, access role and assigned branch.
        </p>
    </div>

    <a href="{{ route('admin.users.index') }}"
       class="btn btn-secondary">
        Back to Staff
    </a>
</div>

<div class="card staff-form">

    @if($errors->any())
        <div class="alert-error">
            Please correct the highlighted fields.
        </div>
    @endif

    <form method="POST"
          action="{{ route('admin.users.update', $user) }}">

        @csrf
        @method('PUT')

        <div class="form-grid">

            <div class="form-group">
                <label class="form-label" for="name">
                    Full Name *
                </label>

                <input type="text"
                       name="name"
                       id="name"
                       class="form-input"
                       value="{{ old('name', $user->name) }}"
                       maxlength="255"
                       required>

                @error('name')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Staff Number
                </label>

                <input type="text"
                       class="form-input"
                       value="{{ $user->staff_number }}"
                       readonly>

                <div class="form-hint">
                    Permanent staff identification number.
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="phone">
                    Phone Number *
                </label>

                <input type="tel"
                       name="phone"
                       id="phone"
                       class="form-input"
                       value="{{ old('phone', $user->phone) }}"
                       maxlength="20"
                       required>

                @error('phone')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="email">
                    Email Address
                </label>

                <input type="email"
                       name="email"
                       id="email"
                       class="form-input"
                       value="{{ old('email', $user->email) }}"
                       maxlength="255"
                       placeholder="Optional">

                <div class="form-hint">
                    Optional contact email.
                </div>

                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="role_id">
                    Staff Role *
                </label>

                <select name="role_id"
                        id="role_id"
                        class="form-input"
                        required>

                    @foreach($roles as $role)
                        <option value="{{ $role->id }}"
                            @selected(old('role_id', $user->role_id) == $role->id)>
                            {{ $role->name === 'attendant' ? 'Sales Agent' : $role->display_name }}
                        </option>
                    @endforeach

                </select>

                @error('role_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="branch_id">
                    Assigned Branch *
                </label>

                @php
                    $assignedBranchId = old(
                        'branch_id',
                        $user->branches->first()?->id
                    );
                @endphp

                <select name="branch_id"
                        id="branch_id"
                        class="form-input"
                        required>

                    <option value="">Select branch</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            @selected((string) $assignedBranchId === (string) $branch->id)>
                            {{ $branch->branch_name }}
                            ({{ $branch->branch_code }})
                        </option>
                    @endforeach

                </select>

                <div class="form-hint">
                    Sales transactions will automatically use this branch.
                    Changing it will affect future sales only.
                </div>

                @error('branch_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">
                    New Password
                </label>

                <input type="password"
                       name="password"
                       id="password"
                       class="form-input"
                       minlength="12"
                       autocomplete="new-password">

                <div class="form-hint">
                    Leave blank to keep the existing password.
                </div>

                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password_confirmation">
                    Confirm New Password
                </label>

                <input type="password"
                       name="password_confirmation"
                       id="password_confirmation"
                       class="form-input"
                       minlength="12"
                       autocomplete="new-password">
            </div>

        </div>

        <div class="form-actions">

            <button type="submit" class="btn btn-primary">
                Save Changes
            </button>

            <a href="{{ route('admin.users.index') }}"
               class="btn btn-secondary">
                Cancel
            </a>

        </div>

    </form>

</div>

@endsection
