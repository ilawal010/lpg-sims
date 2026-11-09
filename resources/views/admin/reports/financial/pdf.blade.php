<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Performance Report</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 30px 35px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #20352b;
            margin: 0;
        }

        .header {
            border-bottom: 3px solid #11633e;
            padding-bottom: 14px;
            margin-bottom: 22px;
        }

        .business-name {
            font-size: 20px;
            font-weight: bold;
            color: #11633e;
            margin-bottom: 5px;
        }

        .business-subtitle {
            font-size: 10px;
            color: #78877e;
        }

        .report-title {
            font-size: 17px;
            font-weight: bold;
            margin-top: 16px;
            margin-bottom: 6px;
        }

        .report-description {
            font-size: 10px;
            color: #78877e;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .details-table td {
            padding: 8px 12px;
            border: 1px solid #e3ebe6;
        }

        .details-label {
            font-weight: bold;
            background: #f4f7f5;
            width: 18%;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #11633e;
            margin-bottom: 12px;
        }

        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .financial-table th {
            background: #11633e;
            color: #ffffff;
            text-align: left;
            padding: 11px 9px;
            font-size: 10px;
        }

        .financial-table td {
            border-bottom: 1px solid #e3ebe6;
            padding: 11px 9px;
            font-size: 10px;
        }

        .financial-table .number {
            text-align: right;
        }

        .financial-table .total-row {
            background: #eaf4ee;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .summary-table td {
            border: 1px solid #e3ebe6;
            padding: 14px;
            background: #f8faf9;
        }

        .summary-label {
            font-size: 10px;
            color: #78877e;
            margin-bottom: 8px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: bold;
            color: #11633e;
        }

        .note {
            margin-top: 24px;
            padding: 12px;
            background: #f4f7f5;
            border-left: 3px solid #11633e;
            font-size: 10px;
            line-height: 1.6;
        }

        .footer {
            margin-top: 28px;
            border-top: 1px solid #e3ebe6;
            padding-top: 10px;
            color: #78877e;
            font-size: 9px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="business-name">
            INTISAR OIL &amp; GAS
        </div>

        <div class="business-subtitle">
            LPG SALES &amp; INVENTORY MANAGEMENT SYSTEM
        </div>

        <div class="report-title">
            FINANCIAL PERFORMANCE REPORT
        </div>

        <div class="report-description">
            @if($canViewCosts)
                Consolidated revenue, cost of goods sold and gross profitability
            @else
                Consolidated posted sales revenue and transaction performance
            @endif
        </div>
    </div>

    <table class="details-table">
        <tr>
            <td class="details-label">Reporting Period</td>
            <td>{{ $dateFrom }} to {{ $dateTo }}</td>

            <td class="details-label">Branch</td>
            <td>{{ $branchName }}</td>
        </tr>

        <tr>
            <td class="details-label">Generated On</td>
            <td>{{ now()->format('d M Y, h:i A') }}</td>

            <td class="details-label">Report Basis</td>
            <td>Posted Sales Only</td>
        </tr>
    </table>

    <div class="section-title">
        @if($canViewCosts)
            Financial Performance Breakdown
        @else
            Revenue Performance Breakdown
        @endif
    </div>

    <table class="financial-table">
        <thead>
            <tr>
                <th>Sales Category</th>
                <th class="number">Transactions</th>
                <th class="number">Revenue (NGN)</th>

                @if($canViewCosts)
                    <th class="number">COGS (NGN)</th>
                    <th class="number">Gross Profit (NGN)</th>
                    <th class="number">Margin (%)</th>
                @endif
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>LPG Sales</td>

                <td class="number">
                    {{ number_format($lpg['transactions']) }}
                </td>

                <td class="number">
                    {{ number_format($lpg['revenue'], 2) }}
                </td>

                @if($canViewCosts)
                    <td class="number">
                        {{ number_format($lpg['cogs'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($lpg['gross_profit'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($lpg['margin'], 2) }}%
                    </td>
                @endif
            </tr>

            <tr>
                <td>Accessory Sales</td>

                <td class="number">
                    {{ number_format($accessories['transactions']) }}
                </td>

                <td class="number">
                    {{ number_format($accessories['revenue'], 2) }}
                </td>

                @if($canViewCosts)
                    <td class="number">
                        {{ number_format($accessories['cogs'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($accessories['gross_profit'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($accessories['margin'], 2) }}%
                    </td>
                @endif
            </tr>

            <tr class="total-row">
                <td>TOTAL</td>

                <td class="number">
                    {{ number_format($summary['transactions']) }}
                </td>

                <td class="number">
                    {{ number_format($summary['revenue'], 2) }}
                </td>

                @if($canViewCosts)
                    <td class="number">
                        {{ number_format($summary['cogs'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($summary['gross_profit'], 2) }}
                    </td>

                    <td class="number">
                        {{ number_format($summary['gross_margin'], 2) }}%
                    </td>
                @endif
            </tr>
        </tbody>
    </table>

    <div class="section-title">
        @if($canViewCosts)
            Financial Summary
        @else
            Revenue Summary
        @endif
    </div>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">Total Revenue</div>

                <div class="summary-value">
                    NGN {{ number_format($summary['revenue'], 2) }}
                </div>
            </td>

            @if($canViewCosts)
                <td>
                    <div class="summary-label">
                        Total Cost of Goods Sold
                    </div>

                    <div class="summary-value">
                        NGN {{ number_format($summary['cogs'], 2) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">Gross Profit</div>

                    <div class="summary-value">
                        NGN {{ number_format($summary['gross_profit'], 2) }}
                    </div>
                </td>

                <td>
                    <div class="summary-label">
                        Gross Profit Margin
                    </div>

                    <div class="summary-value">
                        {{ number_format($summary['gross_margin'], 2) }}%
                    </div>
                </td>
            @else
                <td>
                    <div class="summary-label">
                        Posted Sales Transactions
                    </div>

                    <div class="summary-value">
                        {{ number_format($summary['transactions']) }}
                    </div>
                </td>
            @endif
        </tr>
    </table>

    <div class="note">
        <strong>Financial Reporting Note:</strong>

        @if($canViewCosts)
            This report reflects posted LPG and accessory sales within
            the selected reporting period. Gross profit represents
            revenue less cost of goods sold. Operating expenses,
            taxes and other financial obligations are not included.
            Therefore, gross profit should not be interpreted as
            net profit or available cash balance.
        @else
            This report reflects posted LPG and accessory sales revenue
            within the selected reporting period and authorized branches.
            Operating expenses, collections and cashbook transactions
            are not included. Sales revenue should not be interpreted
            as available cash balance.
        @endif
    </div>

    <div class="footer">
        Intisar Oil &amp; Gas |
        LPG Sales &amp; Inventory Management System |
        System-Generated Financial Report
    </div>

</body>
</html>
