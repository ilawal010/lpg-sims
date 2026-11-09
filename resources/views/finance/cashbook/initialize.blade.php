@extends('layouts.admin')

@section('content')

<style>
    .cb-init {
        max-width: 1250px;
        margin: 0 auto;
        padding: 10px 0 36px;
        color: #17372c;
    }

    .cb-init * {
        box-sizing: border-box;
    }

    .cb-init-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 26px;
    }

    .cb-init-title {
        font-size: 26px;
        font-weight: 800;
        color: #103d2c;
        margin: 0 0 7px;
    }

    .cb-init-subtitle {
        color: #64748b;
        font-size: 14px;
        margin: 0;
    }

    .cb-back {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 11px 17px;
        border: 1px solid #cbd5d1;
        border-radius: 9px;
        color: #17543a;
        background: #fff;
        text-decoration: none;
        font-weight: 700;
        font-size: 13px;
    }

    .cb-back:hover {
        background: #f0f7f3;
    }

    .cb-panel {
        background: #fff;
        border: 1px solid #e0e8e3;
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 22px;
        box-shadow: 0 3px 14px rgba(16, 61, 44, .035);
    }

    .cb-panel-head {
        padding: 21px 25px;
        border-bottom: 1px solid #e8eeea;
    }

    .cb-panel-title {
        margin: 0 0 5px;
        font-size: 17px;
        font-weight: 800;
        color: #123e2d;
    }

    .cb-panel-desc {
        margin: 0;
        font-size: 13px;
        color: #718096;
    }

    .cb-panel-body {
        padding: 25px;
    }

    .cb-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
    }

    .cb-field {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-width: 0;
    }

    .cb-label {
        font-size: 13px;
        font-weight: 700;
        color: #34483e;
    }

    .cb-required {
        color: #c24135;
    }

    .cb-input,
    .cb-select {
        width: 100%;
        min-height: 46px;
        border: 1px solid #cfdcd4;
        border-radius: 9px;
        padding: 11px 13px;
        background: #fff;
        color: #243b31;
        font-size: 14px;
        font-family: inherit;
        outline: none;
    }

    .cb-input:focus,
    .cb-select:focus {
        border-color: #168052;
        box-shadow: 0 0 0 3px rgba(22, 128, 82, .10);
    }

    .cb-hint {
        font-size: 12px;
        color: #78877e;
        line-height: 1.5;
    }

    .cb-notice {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        background: #fffaf0;
        border: 1px solid #f1dfac;
        border-radius: 10px;
        padding: 16px 18px;
        color: #805b1c;
        font-size: 13px;
        line-height: 1.65;
        margin-top: 24px;
    }

    .cb-notice-icon {
        font-size: 20px;
        flex-shrink: 0;
    }

    .cb-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
        margin-top: 25px;
    }

    .cb-primary {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: 9px;
        background: #11683f;
        color: #fff;
        border: 1px solid #11683f;
        border-radius: 9px;
        padding: 13px 23px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
    }

    .cb-primary:hover {
        background: #0d5432;
    }

    .cb-primary:disabled {
        opacity: .55;
        cursor: not-allowed;
    }

    .cb-alert {
        border-radius: 9px;
        padding: 14px 17px;
        font-size: 13px;
        margin-bottom: 20px;
        line-height: 1.6;
    }

    .cb-alert-success {
        background: #edf9f0;
        border: 1px solid #b9e3c6;
        color: #17603a;
    }

    .cb-alert-error {
        background: #fff1f0;
        border: 1px solid #f3c4bf;
        color: #a12b25;
    }

    .cb-alert ul {
        margin: 8px 0 0;
        padding-left: 20px;
    }

    .cb-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .cb-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        white-space: nowrap;
    }

    .cb-table thead {
        background: #f2f7f4;
    }

    .cb-table th {
        padding: 15px 17px;
        text-align: left;
        color: #365347;
        font-weight: 800;
        border-bottom: 1px solid #e2eae5;
    }

    .cb-table td {
        padding: 16px 17px;
        border-bottom: 1px solid #edf1ee;
        color: #33483d;
    }

    .cb-table tbody tr:last-child td {
        border-bottom: none;
    }

    .cb-table tbody tr:hover {
        background: #fbfdfb;
    }

    .cb-money {
        text-align: right !important;
        font-variant-numeric: tabular-nums;
    }

    .cb-status {
        display: inline-flex;
        align-items: center;
        border-radius: 30px;
        padding: 6px 11px;
        font-size: 11px;
        font-weight: 800;
    }

    .cb-status-success {
        background: #e7f7ed;
        color: #187345;
    }

    .cb-status-pending {
        background: #fff4df;
        color: #a26b13;
    }

    .cb-branch-name {
        font-weight: 800;
        color: #173d2c;
    }

    .cb-branch-code {
        font-size: 11px;
        color: #829087;
        margin-top: 4px;
    }

    @media (max-width: 768px) {
        .cb-form-grid {
            grid-template-columns: 1fr;
        }

        .cb-panel-body,
        .cb-panel-head {
            padding: 18px;
        }

        .cb-init-title {
            font-size: 22px;
        }

        .cb-back,
        .cb-primary {
            width: 100%;
        }

        .cb-actions {
            flex-direction: column;
            align-items: stretch;
        }
    }
</style>

<div class="cb-init">

    <div class="cb-init-header">
        <div>
            <h1 class="cb-init-title">Cashbook Initialization</h1>
            <p class="cb-init-subtitle">
                Establish verified opening balances for Cash and Bank accounts.
            </p>
        </div>

        <a href="{{ route('admin.cashbook.index') }}" class="cb-back">
            ← Back to Cashbook
        </a>
    </div>

    @if(session('success'))
        <div class="cb-alert cb-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="cb-alert cb-alert-error">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="cb-panel">
        <div class="cb-panel-head">
            <h2 class="cb-panel-title">Initial Opening Balances</h2>
            <p class="cb-panel-desc">
                Select the branch and enter the verified balances as at the opening date.
            </p>
        </div>

        <div class="cb-panel-body">

            <form method="POST"
                  action="{{ route('admin.cashbook.initialize.store') }}"
                  id="cashbookInitializationForm">

                @csrf

                <div class="cb-form-grid">

                    <div class="cb-field">
                        <label class="cb-label" for="branch_id">
                            Branch <span class="cb-required">*</span>
                        </label>

                        <select id="branch_id"
                                name="branch_id"
                                class="cb-select"
                                required>
                            <option value="">Select an active branch</option>

                            @foreach($branches as $branch)
                                @php
                                    $initialized = $accounts->has($branch->id);
                                @endphp

                                <option value="{{ $branch->id }}"
                                    @selected(old('branch_id') == $branch->id)
                                    @disabled($initialized)>
                                    {{ $branch->branch_name }}
                                    ({{ $branch->branch_code }})
                                    {{ $initialized ? '— Already initialized' : '' }}
                                </option>
                            @endforeach
                        </select>

                        <span class="cb-hint">
                            Only branches without an existing Cashbook account can be initialized.
                        </span>
                    </div>

                    <div class="cb-field">
                        <label class="cb-label" for="opening_date">
                            Opening Date <span class="cb-required">*</span>
                        </label>

                        <input type="date"
                               id="opening_date"
                               name="opening_date"
                               class="cb-input"
                               value="{{ old('opening_date', now()->toDateString()) }}"
                               max="{{ now()->toDateString() }}"
                               required>

                        <span class="cb-hint">
                            Balances must represent the beginning of this date.
                        </span>
                    </div>

                    <div class="cb-field">
                        <label class="cb-label" for="opening_balance">
                            Cash Opening Balance (₦)
                            <span class="cb-required">*</span>
                        </label>

                        <input type="number"
                               id="opening_balance"
                               name="opening_balance"
                               class="cb-input"
                               min="0"
                               step="0.01"
                               value="{{ old('opening_balance') }}"
                               placeholder="Enter verified cash balance"
                               required>

                        <span class="cb-hint">
                            Physical cash available at the beginning of the opening date.
                        </span>
                    </div>

                    <div class="cb-field">
                        <label class="cb-label" for="bank_opening_balance">
                            Bank Opening Balance (₦)
                            <span class="cb-required">*</span>
                        </label>

                        <input type="number"
                               id="bank_opening_balance"
                               name="bank_opening_balance"
                               class="cb-input"
                               min="0"
                               step="0.01"
                               value="{{ old('bank_opening_balance') }}"
                               placeholder="Enter verified bank balance"
                               required>

                        <span class="cb-hint">
                            Verified bank balance at the beginning of the opening date.
                        </span>
                    </div>

                </div>

                <div class="cb-notice">
                    <span class="cb-notice-icon">⚠</span>
                    <div>
                        <strong>Financial Control Notice</strong>
                        <div>
                            Confirm both amounts against physical cash counts
                            and bank records before submitting.
                            Initialization is permitted only once per branch.
                            Subsequent opening balances will be calculated
                            automatically from preceding closing balances.
                        </div>
                    </div>
                </div>

                <div class="cb-actions">
                    <button type="submit"
                            class="cb-primary"
                            id="initializeButton">
                        Initialize Cashbook
                    </button>
                </div>

            </form>
        </div>
    </section>

    <section class="cb-panel">
        <div class="cb-panel-head">
            <h2 class="cb-panel-title">Branch Initialization Status</h2>
            <p class="cb-panel-desc">
                View the opening balance configuration of all active branches.
            </p>
        </div>

        <div class="cb-table-wrap">
            <table class="cb-table">
                <thead>
                    <tr>
                        <th>Branch</th>
                        <th>Opening Date</th>
                        <th class="cb-money">Cash Opening (₦)</th>
                        <th class="cb-money">Bank Opening (₦)</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($branches as $branch)
                        @php
                            $account = $accounts->get($branch->id);
                            $complete = $account
                                && $account->opening_date !== null
                                && $account->opening_balance !== null
                                && $account->bank_opening_balance !== null;
                        @endphp

                        <tr>
                            <td>
                                <div class="cb-branch-name">
                                    {{ $branch->branch_name }}
                                </div>
                                <div class="cb-branch-code">
                                    {{ $branch->branch_code }}
                                </div>
                            </td>

                            <td>
                                {{ $account?->opening_date?->format('d M Y') ?? '—' }}
                            </td>

                            <td class="cb-money">
                                {{ $account && $account->opening_balance !== null
                                    ? number_format((float) $account->opening_balance, 2)
                                    : '—' }}
                            </td>

                            <td class="cb-money">
                                {{ $account && $account->bank_opening_balance !== null
                                    ? number_format((float) $account->bank_opening_balance, 2)
                                    : '—' }}
                            </td>

                            <td>
                                @if($complete)
                                    <span class="cb-status cb-status-success">
                                        Initialized
                                    </span>
                                @elseif($account)
                                    <span class="cb-status cb-status-pending">
                                        Incomplete Setup
                                    </span>
                                @else
                                    <span class="cb-status cb-status-pending">
                                        Not Initialized
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;padding:30px;">
                                No active branches found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

</div>

@endsection
