@extends('layouts.admin')

@section('title', 'My Profile')
@section('header', 'My Profile')

@push('styles')
<style>
    .profile-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .profile-label {
        font-size: 12px;
        color: #78877e;
        margin-bottom: 7px;
    }

    .profile-value {
        font-size: 14px;
        font-weight: 650;
        color: #20352b;
    }

    .profile-section-title {
        font-size: 16px;
        font-weight: 700;
        margin: 0 0 22px;
    }

    .profile-input {
        display: block;
        width: 100%;
        padding: 12px;
        border: 1px solid #dce7df;
        border-radius: 8px;
        font-size: 13px;
        margin-top: 8px;
    }

    .profile-input:focus {
        outline: none;
        border-color: #11633e;
    }

    .profile-field {
        margin-bottom: 20px;
    }

    .profile-error {
        font-size: 12px;
        color: #b91c1c;
        margin-top: 6px;
    }

    .profile-card {
        margin-bottom: 24px;
    }

    @media(max-width:650px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="page-header">
    <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-description">
            View your account information and manage your password.
        </p>
    </div>
</div>

<div class="card profile-card">

    <h3 class="profile-section-title">Account Information</h3>

    <div class="profile-grid">

        <div>
            <div class="profile-label">Full Name</div>
            <div class="profile-value">{{ auth()->user()->name }}</div>
        </div>

        <div>
            <div class="profile-label">Staff Number</div>
            <div class="profile-value">
                {{ auth()->user()->staff_number ?? 'Not Assigned' }}
            </div>
        </div>

        <div>
            <div class="profile-label">Phone Number</div>
            <div class="profile-value">
                {{ auth()->user()->phone ?? 'Not Provided' }}
            </div>
        </div>

        <div>
            <div class="profile-label">Email Address</div>
            <div class="profile-value">
                {{ auth()->user()->email ?? 'Not Provided' }}
            </div>
        </div>

        <div>
            <div class="profile-label">Role</div>
            <div class="profile-value">
                {{ auth()->user()->role?->display_name ?? 'Unassigned' }}
            </div>
        </div>

    </div>

</div>

<div class="card profile-card">

    <h3 class="profile-section-title">Change Password</h3>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        @method('PUT')

        <div class="profile-field">
            <label for="current_password" class="profile-label">
                Current Password
            </label>

            <input
                id="current_password"
                type="password"
                name="current_password"
                class="profile-input"
                required
                autocomplete="current-password"
            >

            @error('current_password', 'updatePassword')
                <div class="profile-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="profile-field">
            <label for="password" class="profile-label">
                New Password
            </label>

            <input
                id="password"
                type="password"
                name="password"
                class="profile-input"
                required
                minlength="12"
                autocomplete="new-password"
            >

            @error('password', 'updatePassword')
                <div class="profile-error">{{ $message }}</div>
            @enderror
        </div>

        <div class="profile-field">
            <label for="password_confirmation" class="profile-label">
                Confirm New Password
            </label>

            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="profile-input"
                required
                minlength="12"
                autocomplete="new-password"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            Update Password
        </button>

        @if(session('status') === 'password-updated')
            <span style="margin-left:12px;color:#17643b;font-size:12px;">
                Password updated successfully.
            </span>
        @endif

    </form>

</div>

@endsection
