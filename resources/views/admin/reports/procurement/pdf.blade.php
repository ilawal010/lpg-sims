<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Procurement Advice Report</title>

    <style>
        @page {
            margin: 25px 30px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #24372b;
        }

        .header {
            border-bottom: 2px solid #176b3a;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .title {
            font-size: 19px;
            font-weight: bold;
            color: #176b3a;
            margin-bottom: 5px;
        }

        .subtitle {
            color: #64748b;
            font-size: 9px;
        }

        .meta {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }

        .meta td {
            padding: 5px 0;
            font-size: 9px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .summary td {
            border: 1px solid #dce8df;
            background: #f2f7f3;
            padding: 9px;
            text-align: center;
            width: 20%;
        }

        .summary-label {
            font-size: 8px;
            color: #64748b;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 15px;
            font-weight: bold;
            color: #176b3a;
        }

        .section-title {
            color: #176b3a;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8px;
        }

        .report-table th {
            background: #176b3a;
            color: #ffffff;
            padding: 8px 5px;
            text-align: left;
            border: 1px solid #176b3a;
        }

        .report-table td {
            padding: 7px 5px;
            border: 1px solid #dce8df;
            vertical-align: top;
        }

        .report-table tr:nth-child(even) td {
            background: #f8faf8;
        }

        .number {
            text-align: right;
        }

        .critical {
            color: #b42318;
            font-weight: bold;
        }

        .reorder {
            color: #9a6500;
            font-weight: bold;
        }

        .routine {
            color: #175cd3;
            font-weight: bold;
        }

        .sufficient {
            color: #176b3a;
            font-weight: bold;
        }

        .not_configured {
            color: #64748b;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 20px;
            font-size: 8px;
            color: #64748b;
            line-height: 1.5;
        }

        .empty {
            text-align: center;
            color: #64748b;
            padding: 20px;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">
            PROCUREMENT ADVICE REPORT
        </div>

        <div class="subtitle">
            LPG and Accessories Stock Replenishment Recommendations
        </div>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>Branch:</strong>
                @if(!empty($filters['branch_id']))
                    {{ $branches->firstWhere('id', (int)$filters['branch_id'])?->branch_name ?? 'Selected Branch' }}
                @else
                    All Authorized Branches
                @endif
            </td>

            <td style="text-align:right;">
                <strong>Generated:</strong>
                {{ $generatedAt->format('d M Y, h:i A') }}
            </td>
        </tr>

        <tr>
            <td>
                <strong>Item Type:</strong>
                {{ ucfirst($filters['item_type'] ?? 'All') }}
            </td>

            <td style="text-align:right;">
                <strong>Display:</strong>
                {{ !empty($filters['procurement_only'])
                    ? 'Items to Procure Only'
                    : 'All Items' }}
            </td>
        </tr>
    </table>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Inventory Items</div>
                <div class="summary-value">
                    {{ number_format($summary['total_items']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Items to Procure</div>
                <div class="summary-value">
                    {{ number_format($summary['items_to_procure']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Critical</div>
                <div class="summary-value">
                    {{ number_format($summary['critical_items']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">High Priority</div>
                <div class="summary-value">
                    {{ number_format($summary['high_priority_items']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Configuration Required</div>
                <div class="summary-value">
                    {{ number_format($summary['configuration_required']) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        Procurement Requirements
    </div>

    <table class="report-table">
        <thead>
            <tr>
                <th>Branch</th>
                <th>Item Code</th>
                <th>Item Name</th>
                <th>Type</th>
                <th class="number">Available</th>
                <th class="number">Minimum</th>
                <th class="number">Target</th>
                <th class="number">To Procure</th>
                <th>Unit</th>
                <th>Priority</th>
            </tr>
        </thead>

        <tbody>
            @forelse($advice as $item)
                <tr>
                    <td>{{ $item['branch_name'] }}</td>

                    <td>{{ $item['item_code'] }}</td>

                    <td>{{ $item['item_name'] }}</td>

                    <td>{{ strtoupper($item['item_type']) }}</td>

                    <td class="number">
                        {{ number_format((float)$item['available_stock'], 3) }}
                    </td>

                    <td class="number">
                        {{ $item['minimum_stock'] !== null
                            ? number_format((float)$item['minimum_stock'], 3)
                            : '-' }}
                    </td>

                    <td class="number">
                        {{ $item['target_stock'] !== null
                            ? number_format((float)$item['target_stock'], 3)
                            : '-' }}
                    </td>

                    <td class="number">
                        <strong>
                            {{ $item['quantity_to_procure'] !== null
                                ? number_format((float)$item['quantity_to_procure'], 3)
                                : '-' }}
                        </strong>
                    </td>

                    <td>{{ $item['unit'] }}</td>

                    <td class="{{ $item['status'] }}">
                        {{ $item['priority'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="empty">
                        No procurement records match the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        <strong>Note:</strong>
        Procurement quantities are advisory and are calculated
        using available stock and configured target stock levels.
        This report does not constitute a purchase order or
        authorize a supplier transaction.
    </div>

</body>
</html>
