@extends('layouts.admin')

@section('title', 'Branch / Location Management')
@section('header', 'Branch / Location Management')

@push('styles')
<style>
    .branch-page {
        width: 100%;
    }

    .branch-heading {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        flex-wrap: wrap;
        margin-bottom: 22px;
    }

    .branch-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .branch-heading p {
        margin: 0;
        color: #718377;
        font-size: 13px;
        line-height: 1.7;
    }

    .branch-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 17px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 12px;
        font-weight: 750;
        font-family: inherit;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
        box-sizing: border-box;
    }

    .branch-btn-primary {
        background: #11643e;
        color: #fff;
    }

    .branch-btn-primary:hover {
        background: #0d5031;
        color: #fff;
    }

    .branch-btn-secondary {
        background: #fff;
        color: #476151;
        border-color: #d8e4dc;
    }

    .branch-btn-secondary:hover {
        background: #f1f7f3;
    }

    .branch-btn-small {
        padding: 8px 13px;
        font-size: 11px;
    }

    .branch-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .branch-card-header {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .branch-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .branch-card-description {
        margin-top: 5px;
        font-size: 12px;
        color: #7d8e82;
        line-height: 1.6;
    }

    .branch-card-body {
        padding: 22px;
    }

    .branch-filter-grid {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr) auto;
        gap: 16px;
        align-items: end;
    }

    .branch-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .branch-field input,
    .branch-field select {
        width: 100%;
        height: 43px;
        padding: 10px 12px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .branch-field input:focus,
    .branch-field select:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .branch-filter-actions {
        display: flex;
        gap: 9px;
    }

    .branch-filter-actions .branch-btn {
        height: 43px;
    }

    .branch-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 26px;
        padding: 5px 9px;
        border-radius: 20px;
        background: #e4f3e9;
        color: #11643e;
        font-size: 11px;
        font-weight: 750;
    }

    .branch-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .branch-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .branch-table th {
        padding: 14px 16px;
        text-align: left;
        background: #f7faf8;
        color: #617467;
        font-weight: 750;
        white-space: nowrap;
    }

    .branch-table td {
        padding: 15px 16px;
        border-top: 1px solid #edf2ee;
        color: #405548;
        vertical-align: middle;
    }

    .branch-table tbody tr:hover {
        background: #fbfdfb;
    }

    .branch-code {
        font-weight: 750;
        color: #11643e;
        white-space: nowrap;
    }

    .branch-name {
        font-weight: 750;
        color: #254532;
    }

    .branch-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 750;
    }

    .branch-status::before {
        content: "";
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .branch-status.active {
        background: #e8f6ed;
        color: #147344;
    }

    .branch-status.inactive {
        background: #f0f2f0;
        color: #78877c;
    }

    .branch-muted {
        color: #8b9b90;
        font-size: 12px;
    }

    .branch-actions {
        text-align: right !important;
    }

    .branch-empty {
        padding: 50px 20px !important;
        text-align: center;
    }

    .branch-empty-icon {
        width: 54px;
        height: 54px;
        border-radius: 14px;
        background: #eaf5ee;
        color: #11643e;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
    }

    .branch-empty-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
        margin-bottom: 7px;
    }

    .branch-empty-text {
        font-size: 12px;
        color: #839387;
        line-height: 1.7;
        margin-bottom: 17px;
    }

    .branch-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        font-size: 12px;
        line-height: 1.7;
    }

    .branch-alert.success {
        background: #eaf7ef;
        border: 1px solid #bfe4cc;
        color: #17643b;
    }

    .branch-pagination {
        padding: 15px 20px;
        border-top: 1px solid #edf2ee;
    }

    @media (max-width: 850px) {
        .branch-filter-grid {
            grid-template-columns: 1fr;
        }

        .branch-filter-actions {
            flex-wrap: wrap;
        }

        .branch-heading {
            align-items: flex-start;
        }
    }
</style>
@endpush

@section('content')

<div class="branch-page">

    {{-- PAGE HEADING --}}
    <div class="branch-heading">
        <div>
            <h1>Branch / Location Management</h1>
            <p>
                Register and manage LPG operating locations,
                storage facilities and branch information.
            </p>
        </div>

        <a href="{{ route('admin.branches.create') }}"
           class="branch-btn branch-btn-primary">
            <span style="font-size:17px;line-height:1;">+</span>
            Register New Branch
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="branch-alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- SEARCH AND FILTER --}}
    <div class="branch-card">

        <div class="branch-card-header">
            <div>
                <div class="branch-card-title">
                    Search & Filter Branches
                </div>
                <div class="branch-card-description">
                    Find branches by name, identification code or location.
                </div>
            </div>
        </div>

        <div class="branch-card-body">

            <form method="GET"
                  action="{{ route('admin.branches.index') }}">

                <div class="branch-filter-grid">

                    <div class="branch-field">
                        <label for="search">
                            Search Branch
                        </label>

                        <input
                            type="text"
                            id="search"
                            name="search"
                            value="{{ request('search') }}"
                            placeholder="Enter branch name, code, city or state">
                    </div>

                    <div class="branch-field">
                        <label for="status">
                            Branch Status
                        </label>

                        <select id="status" name="status">
                            <option value="">All Statuses</option>

                            <option value="active"
                                @selected(request('status') === 'active')>
                                Active
                            </option>

                            <option value="inactive"
                                @selected(request('status') === 'inactive')>
                                Inactive
                            </option>
                        </select>
                    </div>

                    <div class="branch-filter-actions">

                        <button type="submit"
                                class="branch-btn branch-btn-primary">
                            Apply Filter
                        </button>

                        <a href="{{ route('admin.branches.index') }}"
                           class="branch-btn branch-btn-secondary">
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>
    </div>

    {{-- BRANCH REGISTER --}}
    <div class="branch-card">

        <div class="branch-card-header">

            <div>
                <div class="branch-card-title">
                    Registered Branches
                </div>

                <div class="branch-card-description">
                    Official register of LPG-SIMS operating locations.
                </div>
            </div>

            <span class="branch-count">
                {{ $branches->total() }}
                {{ $branches->total() === 1 ? 'Branch' : 'Branches' }}
            </span>

        </div>

        <div class="branch-table-wrap">

            <table class="branch-table">

                <thead>
                    <tr>
                        <th>Branch Code</th>
                        <th>Branch Name</th>
                        <th>Location</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                        <th class="branch-actions">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($branches as $branch)

                        <tr>
                            <td>
                                <span class="branch-code">
                                    {{ $branch->branch_code }}
                                </span>
                            </td>

                            <td>
                                <span class="branch-name">
                                    {{ $branch->branch_name }}
                                </span>
                            </td>

                            <td>
                                {{ collect([
                                    $branch->city,
                                    $branch->state
                                ])->filter()->implode(', ') ?: '—' }}
                            </td>

                            <td>
                                {{ $branch->phone ?: '—' }}
                            </td>

                            <td>
                                <span class="branch-status {{ $branch->status }}">
                                    {{ ucfirst($branch->status) }}
                                </span>
                            </td>

                            <td>
                                <span class="branch-muted">
                                    {{ $branch->updated_at?->format('d M Y') ?? '—' }}
                                </span>
                            </td>

                            <td class="branch-actions">
                                <a href="{{ route('admin.branches.edit', $branch) }}"
                                   class="branch-btn branch-btn-secondary branch-btn-small">
                                    Edit Branch
                                </a>
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="branch-empty">

                                <div class="branch-empty-icon">
                                    <svg width="26"
                                         height="26"
                                         viewBox="0 0 24 24"
                                         fill="none"
                                         stroke="currentColor"
                                         stroke-width="1.7"
                                         stroke-linecap="round"
                                         stroke-linejoin="round">
                                        <rect x="3" y="7" width="18" height="14" rx="2"/>
                                        <path d="M8 7V3h8v4"/>
                                        <path d="M3 12h18"/>
                                        <path d="M9 16h6"/>
                                    </svg>
                                </div>

                                <div class="branch-empty-title">
                                    No Branches Found
                                </div>

                                <div class="branch-empty-text">
                                    @if(request()->filled('search') || request()->filled('status'))
                                        No branches match the selected search criteria.
                                        Try adjusting your filters.
                                    @else
                                        No LPG operating locations have been registered.
                                        Register your first branch to begin managing
                                        storage tanks and cylinders.
                                    @endif
                                </div>

                                @if(request()->filled('search') || request()->filled('status'))

                                    <a href="{{ route('admin.branches.index') }}"
                                       class="branch-btn branch-btn-secondary">
                                        Clear Filters
                                    </a>

                                @else

                                    <a href="{{ route('admin.branches.create') }}"
                                       class="branch-btn branch-btn-primary">
                                        + Register First Branch
                                    </a>

                                @endif

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if($branches->hasPages())
            <div class="branch-pagination">
                {{ $branches->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
