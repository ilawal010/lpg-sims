@extends('layouts.admin')

@section('title', 'Dashboard')
@section('header', 'Management Dashboard')

@section('content')

    <div class="page-header">
        <div>
            <h1 class="page-title">Welcome, {{ auth()->user()->name }}</h1>

            <p class="page-description">
                Intisar Oil &amp; Gas — LPG Sales, Inventory
                &amp; Management System
            </p>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">
            LPG-SIMS Administration
        </h3>

        <p style="color:#78877e; font-size:14px; line-height:1.8;">
            Your management workspace is ready.
            Business modules will be activated progressively
            as development continues.
        </p>

        @if(auth()->user()->hasPermission('users.manage'))
            <a href="{{ route('admin.users.index') }}"
               class="btn btn-primary">
                Staff Management
            </a>
        @endif
    </div>

@endsection
