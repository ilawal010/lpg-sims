@extends('layouts.manager')

@section('title', 'Sales Agents')
@section('header', 'Sales Agents')

@section('content')

<style>
    .agents-page {
        --agents-green: #176b45;
        --agents-border: #e2e8e5;
        --agents-muted: #64748b;
        color: #172b23;
    }

    .agents-page * {
        box-sizing: border-box;
    }

    .agents-heading {
        margin-bottom: 22px;
    }

    .agents-heading h1 {
        margin: 0 0 6px;
        font-size: 24px;
        font-weight: 800;
    }

    .agents-subtitle {
        margin: 0;
        color: var(--agents-muted);
        font-size: 13px;
        line-height: 1.6;
    }

    .agents-summary {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .agents-stat {
        background: #fff;
        border: 1px solid var(--agents-border);
        border-radius: 12px;
        padding: 20px;
    }

    .agents-stat-label {
        color: var(--agents-muted);
        font-size: 12px;
        margin-bottom: 10px;
    }

    .agents-stat-value {
        font-size: 27px;
        font-weight: 800;
    }

    .agents-stat-note {
        color: var(--agents-muted);
        font-size: 11px;
        margin-top: 7px;
    }

    .agents-panel {
        background: #fff;
        border: 1px solid var(--agents-border);
        border-radius: 12px;
        overflow: hidden;
    }

    .agents-panel-header {
        padding: 20px;
        border-bottom: 1px solid var(--agents-border);
    }

    .agents-panel-header h2 {
        margin: 0 0 6px;
        font-size: 16px;
        font-weight: 750;
    }

    .agents-filters {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 12px;
        margin-top: 18px;
    }

    .agents-field {
        display: flex;
        flex-direction: column;
        gap: 7px;
        min-width: 180px;
    }

    .agents-field.search {
        flex: 1;
        min-width: 240px;
    }

    .agents-field label {
        font-size: 12px;
        font-weight: 700;
    }

    .agents-input {
        width: 100%;
        min-height: 41px;
        padding: 9px 11px;
        border: 1px solid #d5dfd9;
        border-radius: 8px;
        background: #fff;
        color: #172b23;
        font-size: 13px;
    }

    .agents-input:focus {
        outline: none;
        border-color: var(--agents-green);
        box-shadow: 0 0 0 3px rgba(23,107,69,.1);
    }

    .agents-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 41px;
        padding: 10px 16px;
        border: 1px solid transparent;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .agents-btn-primary {
        background: var(--agents-green);
        color: #fff;
    }

    .agents-btn-secondary {
        background: #fff;
        color: #334155;
        border-color: var(--agents-border);
    }

    .agents-table-wrap {
        overflow-x: auto;
    }

    .agents-table {
        width: 100%;
        min-width: 900px;
        border-collapse: collapse;
    }

    .agents-table th {
        padding: 13px 15px;
        background: #f8faf9;
        border-bottom: 1px solid var(--agents-border);
        color: var(--agents-muted);
        font-size: 11px;
        font-weight: 750;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .agents-table td {
        padding: 16px 15px;
        border-bottom: 1px solid #edf1ee;
        font-size: 13px;
        vertical-align: middle;
    }

    .agents-table tbody tr:last-child td {
        border-bottom: none;
    }

    .agents-name {
        font-weight: 750;
    }

    .agents-secondary {
        color: var(--agents-muted);
        font-size: 11px;
        margin-top: 5px;
    }

    .agents-branch {
        display: inline-block;
        margin: 3px 5px 3px 0;
        padding: 5px 9px;
        border-radius: 20px;
        background: #e8f5ec;
        color: #176b45;
        font-size: 11px;
        font-weight: 700;
    }

    .agents-role {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        background: #e0f2fe;
        color: #075985;
        font-size: 11px;
        font-weight: 750;
    }

    .agents-empty {
        padding: 50px 20px;
        text-align: center;
    }

    .agents-empty h3 {
        margin: 0 0 8px;
        font-size: 17px;
    }

    .agents-empty p {
        color: var(--agents-muted);
        font-size: 13px;
    }

    .agents-footer {
        padding: 16px 20px;
        border-top: 1px solid var(--agents-border);
    }

    @media (max-width: 650px) {
        .agents-summary {
            grid-template-columns: 1fr;
        }

        .agents-heading h1 {
            font-size: 21px;
        }
    }
</style>

<div class="agents-page">

    <div class="agents-heading">
        <h1>Sales Agents</h1>

        <p class="agents-subtitle">
            View Sales Agents assigned to your authorized branches.
            Staff creation, branch assignment and permissions
            remain under Owner administration.
        </p>
    </div>

    <div class="agents-summary">

        <div class="agents-stat">
            <div class="agents-stat-label">
                Matching Sales Agents
            </div>

            <div class="agents-stat-value">
                {{ number_format($agents->total()) }}
            </div>

            <div class="agents-stat-note">
                Based on your current search and branch filters
            </div>
        </div>

        <div class="agents-stat">
            <div class="agents-stat-label">
                Assigned Active Branches
            </div>

            <div class="agents-stat-value">
                {{ number_format($branches->count()) }}
            </div>

            <div class="agents-stat-note">
                Branches you are authorized to supervise
            </div>
        </div>

    </div>

    <div class="agents-panel">

        <div class="agents-panel-header">

            <h2>Sales Agents Register</h2>

            <p class="agents-subtitle">
                Search by name, email, phone number or staff number.
            </p>

            <form method="GET"
                  action="{{ route('manager.agents.index') }}"
                  class="agents-filters">

                <div class="agents-field search">
                    <label for="agent-search">
                        Search Sales Agent
                    </label>

                    <input
                        id="agent-search"
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name, email, phone or staff number"
                        class="agents-input"
                    >
                </div>

                <div class="agents-field">
                    <label for="agent-branch">
                        Branch
                    </label>

                    <select
                        id="agent-branch"
                        name="branch_id"
                        class="agents-input"
                    >
                        <option value="">
                            All Assigned Branches
                        </option>

                        @foreach($branches as $branch)
                            <option
                                value="{{ $branch->id }}"
                                @selected(
                                    (string) ($filters['branch_id'] ?? '')
                                    === (string) $branch->id
                                )
                            >
                                {{ $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="agents-btn agents-btn-primary"
                >
                    Apply Filters
                </button>

                <a href="{{ route('manager.agents.index') }}"
                   class="agents-btn agents-btn-secondary">
                    Reset
                </a>

            </form>
        </div>

        <div class="agents-table-wrap">

            <table class="agents-table">
                <thead>
                    <tr>
                        <th>Sales Agent</th>
                        <th>Staff Number</th>
                        <th>Phone</th>
                        <th>Assigned Branches</th>
                        <th>Role</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($agents as $agent)
                        <tr>
                            <td>
                                <div class="agents-name">
                                    {{ $agent->name }}
                                </div>

                                <div class="agents-secondary">
                                    {{ $agent->email }}
                                </div>
                            </td>

                            <td>
                                {{ $agent->staff_number ?: '—' }}
                            </td>

                            <td>
                                {{ $agent->phone ?: '—' }}
                            </td>

                            <td>
                                @forelse($agent->branches as $branch)
                                    <span class="agents-branch">
                                        {{ $branch->branch_name }}
                                    </span>
                                @empty
                                    <span class="agents-secondary">
                                        No active branch
                                    </span>
                                @endforelse
                            </td>

                            <td>
                                <span class="agents-role">
                                    Sales Agent
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="agents-empty">
                                    <h3>No Sales Agents Found</h3>
                                    <p>
                                        No Sales Agents match your
                                        selected search or branch filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>

        @if($agents->hasPages())
            <div class="agents-footer">
                {{ $agents->links() }}
            </div>
        @endif

    </div>
</div>

@endsection
