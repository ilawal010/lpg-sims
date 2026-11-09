<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Intisar Oil &amp; Gas - Sales Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #24332b;
        }

        h1 {
            margin: 0;
            font-size: 19px;
            color: #176b3a;
        }

        h2 {
            font-size: 12px;
            margin-top: 22px;
            color: #176b3a;
        }

        .subtitle {
            margin-top: 4px;
            color: #666;
        }

        .header {
            border-bottom: 2px solid #176b3a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }

        .meta {
            margin-bottom: 15px;
        }

        .meta td {
            padding: 4px 15px 4px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary th,
        .summary td,
        .details th,
        .details td {
            border: 1px solid #d8e2dc;
            padding: 7px;
            text-align: left;
        }

        th {
            background: #e8f3ec;
            color: #176b3a;
        }

        .amount {
            text-align: right !important;
            white-space: nowrap;
        }

        .details {
            margin-top: 10px;
        }

        .details thead {
            display: table-header-group;
        }

        .details tr {
            page-break-inside: avoid;
        }

        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>INTISAR OIL &amp; GAS</h1>

        <div class="subtitle">
            Sales Performance Report
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>From:</strong>
                {{ $filters['date_from'] ?? 'All Dates' }}
            </td>

            <td>
                <strong>To:</strong>
                {{ $filters['date_to'] ?? 'All Dates' }}
            </td>

            <td>
                <strong>Branch:</strong>
                {{ $branchName ?? 'All Authorized Branches' }}
            </td>

            <td>
                <strong>Generated:</strong>
                {{ now()->format('d M Y, h:i A') }}
            </td>
        </tr>
    </table>

    <h2>Sales Summary</h2>

    <table class="summary">
        <thead>
            <tr>
                <th>Category</th>
                <th>Transactions</th>
                <th class="amount">Revenue (NGN)</th>

                @if($canViewCosts)
                    <th class="amount">COGS (NGN)</th>
                    <th class="amount">Gross Profit (NGN)</th>
                @endif
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>LPG</td>

                <td>
                    {{ number_format($lpgSummary['transactions'] ?? 0) }}
                </td>

                <td class="amount">
                    {{ number_format($lpgSummary['revenue'] ?? 0, 2) }}
                </td>

                @if($canViewCosts)
                    <td class="amount">
                        {{ number_format($lpgSummary['cogs'] ?? 0, 2) }}
                    </td>

                    <td class="amount">
                        {{ number_format($lpgSummary['gross_profit'] ?? 0, 2) }}
                    </td>
                @endif
            </tr>

            <tr>
                <td>Accessories</td>

                <td>
                    {{ number_format($accessorySummary['transactions'] ?? 0) }}
                </td>

                <td class="amount">
                    {{ number_format($accessorySummary['revenue'] ?? 0, 2) }}
                </td>

                @if($canViewCosts)
                    <td class="amount">
                        {{ number_format($accessorySummary['cogs'] ?? 0, 2) }}
                    </td>

                    <td class="amount">
                        {{ number_format($accessorySummary['gross_profit'] ?? 0, 2) }}
                    </td>
                @endif
            </tr>

            <tr>
                <th>Total</th>

                <th>
                    {{ number_format($summary['transactions'] ?? 0) }}
                </th>

                <th class="amount">
                    {{ number_format($summary['revenue'] ?? 0, 2) }}
                </th>

                @if($canViewCosts)
                    <th class="amount">
                        {{ number_format($summary['cogs'] ?? 0, 2) }}
                    </th>

                    <th class="amount">
                        {{ number_format($summary['gross_profit'] ?? 0, 2) }}
                    </th>
                @endif
            </tr>
        </tbody>
    </table>

    <h2>Sales Transactions</h2>

    <table class="details">
        <thead>
            <tr>
                <th>Date</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Branch</th>
                <th>Sales Agent</th>
                <th>Quantity</th>
                <th class="amount">Revenue (NGN)</th>

                @if($canViewCosts)
                    <th class="amount">COGS (NGN)</th>
                    <th class="amount">Gross Profit (NGN)</th>
                @endif
            </tr>
        </thead>

        <tbody>
            @forelse($sales as $sale)
                <tr>
                    <td>{{ $sale['date'] ?? '' }}</td>
                    <td>{{ $sale['item_name'] ?? '' }}</td>
                    <td>{{ $sale['category'] ?? '' }}</td>
                    <td>{{ $sale['branch'] ?? '' }}</td>
                    <td>{{ $sale['attendant'] ?? '' }}</td>
                    <td>{{ $sale['quantity'] ?? '' }}</td>

                    <td class="amount">
                        {{ number_format($sale['revenue'] ?? 0, 2) }}
                    </td>

                    @if($canViewCosts)
                        <td class="amount">
                            {{ number_format($sale['cogs'] ?? 0, 2) }}
                        </td>

                        <td class="amount">
                            {{ number_format($sale['gross_profit'] ?? 0, 2) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td
                        colspan="{{ $canViewCosts ? 9 : 7 }}"
                        style="text-align: center;"
                    >
                        No posted sales found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Intisar Oil &amp; Gas | LPG-SIMS |
        System-generated Sales Report
    </div>

</body>
</html>
