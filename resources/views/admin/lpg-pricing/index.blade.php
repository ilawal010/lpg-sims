@extends('layouts.admin')

@section('title', 'LPG Selling Price')
@section('header', 'LPG Selling Price')

@push('styles')
<style>
    .pricing-heading {
        margin-bottom: 22px;
    }

    .pricing-heading h1 {
        margin: 0 0 6px;
        font-size: 23px;
        font-weight: 750;
        color: #183d2a;
    }

    .pricing-heading p {
        margin: 0;
        color: #718377;
        font-size: 13px;
    }

    .pricing-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.3fr);
        gap: 20px;
        align-items: start;
    }

    .pricing-card {
        background: #fff;
        border: 1px solid #e2ebe5;
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 20px;
    }

    .pricing-card-header {
        padding: 18px 22px;
        background: #f7faf8;
        border-bottom: 1px solid #e2ebe5;
    }

    .pricing-card-title {
        font-size: 14px;
        font-weight: 750;
        color: #254532;
    }

    .pricing-card-description {
        margin-top: 5px;
        font-size: 12px;
        line-height: 1.6;
        color: #7d8e82;
    }

    .pricing-card-body {
        padding: 22px;
    }

    .pricing-current-label {
        color: #718377;
        font-size: 12px;
        font-weight: 650;
    }

    .pricing-current-value {
        margin-top: 10px;
        color: #11643e;
        font-size: 36px;
        font-weight: 800;
        letter-spacing: -1px;
    }

    .pricing-current-value small {
        font-size: 15px;
        font-weight: 600;
        color: #789080;
    }

    .pricing-not-set {
        color: #ad6421;
        font-size: 23px;
        font-weight: 750;
        margin-top: 12px;
    }

    .pricing-meta {
        margin-top: 14px;
        color: #7b8d80;
        font-size: 12px;
        line-height: 1.8;
    }

    .pricing-field {
        margin-bottom: 18px;
    }

    .pricing-field label {
        display: block;
        margin-bottom: 8px;
        font-size: 12px;
        font-weight: 700;
        color: #405849;
    }

    .pricing-field input,
    .pricing-field textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #d8e4dc;
        border-radius: 8px;
        background: #fff;
        color: #253b2c;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }

    .pricing-field textarea {
        min-height: 110px;
        resize: vertical;
    }

    .pricing-field input:focus,
    .pricing-field textarea:focus {
        outline: none;
        border-color: #178452;
        box-shadow: 0 0 0 3px rgba(23,132,82,.1);
    }

    .pricing-help {
        margin-top: 7px;
        font-size: 11px;
        color: #859389;
        line-height: 1.6;
    }

    .pricing-error {
        margin-top: 6px;
        color: #b42323;
        font-size: 11px;
    }

    .pricing-save {
        width: 100%;
        border: 0;
        background: #11643e;
        color: #fff;
        padding: 13px 18px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 750;
        cursor: pointer;
    }

    .pricing-save:hover {
        background: #0d5031;
    }

    .pricing-alert {
        padding: 13px 16px;
        border-radius: 9px;
        margin-bottom: 20px;
        font-size: 12px;
        line-height: 1.7;
    }

    .pricing-alert.success {
        background: #eaf7ef;
        border: 1px solid #bfe4cc;
        color: #17643b;
    }

    .pricing-alert.error {
        background: #fff0f0;
        border: 1px solid #f1caca;
        color: #a72c2c;
    }

    .pricing-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .pricing-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
    }

    .pricing-table th {
        padding: 13px 15px;
        text-align: left;
        background: #f7faf8;
        color: #617467;
        font-weight: 750;
        white-space: nowrap;
    }

    .pricing-table td {
        padding: 14px 15px;
        border-top: 1px solid #edf2ee;
        color: #405548;
        vertical-align: top;
    }

    .pricing-table .amount {
        font-weight: 750;
        color: #1c6741;
        white-space: nowrap;
    }

    .pricing-empty {
        padding: 30px 20px;
        text-align: center;
        color: #8b9b90;
        font-size: 12px;
    }

    .pricing-note {
        background: #f7faf8;
        border: 1px solid #e2ebe5;
        border-radius: 9px;
        padding: 15px;
        font-size: 12px;
        line-height: 1.8;
        color: #64786a;
    }

    .pricing-history-pagination {
        padding: 15px 20px;
        border-top: 1px solid #edf2ee;
    }

    @media (max-width: 850px) {
        .pricing-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')

<div class="pricing-heading">
    <h1>LPG Selling Price Management</h1>
    <p>
        Set the approved LPG selling price per kilogram
        and maintain a complete history of price changes.
    </p>
</div>

@if(session('success'))
    <div class="pricing-alert success">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="pricing-alert error">
        <strong>Please correct the following:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="pricing-grid">

    <div>

        {{-- CURRENT SELLING PRICE --}}
        <div class="pricing-card">

            <div class="pricing-card-header">
                <div class="pricing-card-title">
                    Current Approved Selling Price
                </div>
                <div class="pricing-card-description">
                    This price will be used for LPG sales
                    at the Point of Sale.
                </div>
            </div>

            <div class="pricing-card-body">

                <div class="pricing-current-label">
                    Selling Price per Kilogram
                </div>

                @if($price->selling_price_per_kg !== null)

                    <div class="pricing-current-value">
                        ₦{{ number_format((float) $price->selling_price_per_kg, 2) }}
                        <small>/ kg</small>
                    </div>

                    <div class="pricing-meta">
                        Effective:
                        {{ $price->effective_at?->format('d M Y, h:i A') ?? 'Not available' }}
                        <br>
                        Last Updated By:
                        {{ $price->updatedBy?->name ?? 'Not available' }}
                    </div>

                @else

                    <div class="pricing-not-set">
                        Price Not Yet Set
                    </div>

                    <div class="pricing-meta">
                        The Owner must establish an approved
                        LPG selling price before sales can begin.
                    </div>

                @endif

            </div>
        </div>

        {{-- UPDATE SELLING PRICE --}}
        <div class="pricing-card">

            <div class="pricing-card-header">
                <div class="pricing-card-title">
                    {{ $price->selling_price_per_kg === null
                        ? 'Set Initial LPG Selling Price'
                        : 'Change LPG Selling Price' }}
                </div>

                <div class="pricing-card-description">
                    Every change requires a reason and
                    is recorded in the audit trail.
                </div>
            </div>

            <div class="pricing-card-body">

                <form method="POST"
                      action="{{ route('admin.lpg-pricing.update') }}">

                    @csrf
                    @method('PUT')

                    <div class="pricing-field">
                        <label for="selling_price_per_kg">
                            New Selling Price (₦/kg) *
                        </label>

                        <input
                            type="number"
                            id="selling_price_per_kg"
                            name="selling_price_per_kg"
                            min="0.01"
                            step="0.01"
                            max="9999999999999.99"
                            required
                            placeholder="Enter approved price per kg"
                            value="{{ old('selling_price_per_kg') }}">

                        <div class="pricing-help">
                            Enter the amount charged to customers
                            for one kilogram of LPG.
                        </div>

                        @error('selling_price_per_kg')
                            <div class="pricing-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="pricing-field">
                        <label for="reason">
                            Reason for Price Change *
                        </label>

                        <textarea
                            id="reason"
                            name="reason"
                            minlength="5"
                            maxlength="2000"
                            required
                            placeholder="Explain why this selling price is being introduced or changed.">{{ old('reason') }}</textarea>

                        @error('reason')
                            <div class="pricing-error">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="pricing-save">
                        {{ $price->selling_price_per_kg === null
                            ? 'Approve Initial Selling Price'
                            : 'Update Approved Selling Price' }}
                    </button>

                </form>

            </div>
        </div>

        <div class="pricing-note">
            <strong>Pricing Control:</strong>
            LPG selling prices are currently managed by
            the Owner only. Inventory costing remains
            separate from selling price configuration.
        </div>

    </div>

    {{-- PRICE HISTORY --}}
    <div class="pricing-card">

        <div class="pricing-card-header">
            <div class="pricing-card-title">
                LPG Selling Price History
            </div>
            <div class="pricing-card-description">
                Chronological record of approved LPG price changes.
            </div>
        </div>

        <div class="pricing-table-wrap">
            <table class="pricing-table">
                <thead>
                    <tr>
                        <th>Date / Time</th>
                        <th>Previous Price</th>
                        <th>New Price</th>
                        <th>Changed By</th>
                        <th>Reason</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($histories as $history)
                        <tr>
                            <td>
                                {{ $history->effective_at?->format('d M Y, h:i A') }}
                            </td>

                            <td>
                                @if($history->previous_price_per_kg !== null)
                                    ₦{{ number_format((float) $history->previous_price_per_kg, 2) }}
                                @else
                                    Initial Price
                                @endif
                            </td>

                            <td class="amount">
                                ₦{{ number_format((float) $history->new_price_per_kg, 2) }}
                            </td>

                            <td>
                                {{ $history->changedBy?->name ?? 'Unknown' }}
                            </td>

                            <td>
                                {{ $history->reason }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="pricing-empty">
                                No LPG selling price changes have been recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
            <div class="pricing-history-pagination">
                {{ $histories->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
