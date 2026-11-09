<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Individual Sales Report</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 28px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #20352b;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #11633e;
            padding-bottom: 14px;
            margin-bottom: 20px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
            color: #11633e;
        }

        .subtitle {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
        }

        .system {
            font-size: 9px;
            color: #78877e;
            margin-top: 4px;
        }

        .meta {
            width: 100%;
            margin-bottom: 20px;
        }

        .meta td {
            padding: 5px 0;
            vertical-align: top;
        }

        .meta-label {
            font-weight: bold;
            width: 115px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .summary th {
            background: #e8f5ed;
            color: #11633e;
            padding: 9px;
            text-align: left;
            border: 1px solid #dce9e0;
        }

        .summary td {
            padding: 9px;
            border: 1px solid #dce9e0;
        }

        .summary-total {
            background: #f3f8f5;
            font-weight: bold;
        }

        h3 {
            color: #11633e;
            font-size: 12px;
            margin-top: 22px;
            margin-bottom: 10px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .data-table th {
            background: #11633e;
            color: #ffffff;
            padding: 8px;
            text-align: left;
        }

        .data-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e3ebe6;
        }

        .data-table tr:nth-child(even) {
            background: #f8faf9;
        }

        .amount {
            text-align: right;
        }

        .empty {
            text-align: center;
            color: #78877e;
            padding: 15px;
        }

        .footer {
            border-top: 1px solid #dce9e0;
            margin-top: 25px;
            padding-top: 10px;
            font-size: 8px;
            color: #78877e;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="company">INTISAR OIL &amp; GAS</div>
        <div class="subtitle">INDIVIDUAL SALES REPORT</div>
        <div class="system">LPG Sales, Inventory &amp; Management System</div>
    </div>

    <table class="meta">
        <tr>
            <td class="meta-label">Sales Agent:</td>
            <td>{{ $agent->name }}</td>
            <td class="meta-label">Generated:</td>
            <td>{{ now()->format('d M Y, h:i A') }}</td>
        </tr>
        <tr>
            <td class="meta-label">Start Date:</td>
            <td>{{ \Illuminate\Support\Carbon::parse($dateFrom)->format('d M Y') }}</td>
            <td class="meta-label">End Date:</td>
            <td>{{ \Illuminate\Support\Carbon::parse($dateTo)->format('d M Y') }}</td>
        </tr>
    </table>

    <table class="summary">
        <thead>
            <tr>
                <th>Sales Category</th>
                <th>Transactions</th>
                <th>Revenue (NGN)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>LPG Sales</td>
                <td>{{ number_format($lpgSales->count()) }}</td>
                <td>{{ number_format($lpgRevenue, 2) }}</td>
            </tr>
            <tr>
                <td>Accessory Sales</td>
                <td>{{ number_format($accessorySales->count()) }}</td>
                <td>{{ number_format($accessoryRevenue, 2) }}</td>
            </tr>
            <tr class="summary-total">
                <td>Total Sales</td>
                <td>{{ number_format($totalTransactions) }}</td>
                <td>{{ number_format($totalRevenue, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>LPG SALES DETAILS</h3>

    <table class="data-table">
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Date</th>
                <th>Branch</th>
                <th class="amount">Amount (NGN)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lpgSales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                    <td>{{ $sale->branch?->name ?? '—' }}</td>
                    <td class="amount">{{ number_format((float) $sale->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">
                        No LPG sales for this period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h3>ACCESSORY SALES DETAILS</h3>

    <table class="data-table">
        <thead>
            <tr>
                <th>Sale ID</th>
                <th>Date</th>
                <th>Accessory</th>
                <th>Branch</th>
                <th class="amount">Amount (NGN)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accessorySales as $sale)
                <tr>
                    <td>#{{ $sale->id }}</td>
                    <td>{{ \Illuminate\Support\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                    <td>{{ $sale->accessory?->name ?? '—' }}</td>
                    <td>{{ $sale->branch?->name ?? '—' }}</td>
                    <td class="amount">{{ number_format((float) $sale->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">
                        No accessory sales for this period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Intisar Oil &amp; Gas | LPG-SIMS
        <div>
            This report contains only the authenticated Sales Agent's posted transactions.
        </div>
    </div>

</body>
</html>
