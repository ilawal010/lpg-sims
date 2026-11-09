<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Report</title>

    <style>
        @page {
            margin: 22px 26px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #26362d;
        }

        .header {
            border-bottom: 2px solid #176b3a;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .title {
            font-size: 19px;
            font-weight: bold;
            color: #176b3a;
            margin-bottom: 5px;
        }

        .subtitle {
            font-size: 9px;
            color: #66766b;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .meta-table td {
            padding: 4px 8px 4px 0;
        }

        .meta-label {
            font-weight: bold;
            color: #42564a;
        }

        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .summary-table td {
            width: 33.33%;
            border: 1px solid #dce8df;
            background: #f4f8f5;
            padding: 10px;
        }

        .summary-label {
            color: #627568;
            font-size: 8px;
            margin-bottom: 5px;
        }

        .summary-value {
            color: #176b3a;
            font-size: 14px;
            font-weight: bold;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #176b3a;
            margin: 18px 0 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            table-layout: fixed;
        }

        .data-table th {
            background: #eaf3ec;
            color: #28583a;
            font-weight: bold;
            text-align: left;
            border: 1px solid #d5e3d9;
            padding: 7px 5px;
            font-size: 8px;
        }

        .data-table td {
            border: 1px solid #e1e8e3;
            padding: 6px 5px;
            font-size: 8px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f9fbf9;
        }

        .number {
            text-align: right !important;
        }

        .warning {
            color: #a56b00;
            font-weight: bold;
        }

        .empty {
            text-align: center;
            color: #6b7280;
            padding: 14px;
        }

        .note {
            margin-top: 12px;
            padding: 9px;
            background: #f5f7f5;
            border: 1px solid #e2e8e3;
            font-size: 8px;
            color: #56675c;
        }

        .footer {
            margin-top: 18px;
            padding-top: 7px;
            border-top: 1px solid #dce8df;
            color: #7a897f;
            font-size: 8px;
            text-align: center;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="title">
            INTISAR OIL &amp; GAS
        </div>

        <div class="subtitle">
            LPG-SIMS | Inventory Reports
        </div>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">Report:</span>
                Current Inventory Position
            </td>

            <td>
                <span class="meta-label">Branch:</span>
                {{ $branchLabel }}
            </td>

            <td>
                <span class="meta-label">Generated:</span>
                {{ $generatedAt->format('d M Y, h:i A') }}
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-label">LPG Storage Units</div>
                <div class="summary-value">
                    {{ number_format($summary['storage_units']) }}
                </div>
            </td>

            <td>
                <div class="summary-label">Available LPG Stock</div>
                <div class="summary-value">
                    {{ number_format($summary['lpg_quantity_kg'], 3) }} kg
                </div>
            </td>

            <td>
                <div class="summary-label">LPG Storage Capacity</div>
                <div class="summary-value">
                    {{ number_format($summary['lpg_capacity_kg'], 3) }} kg
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-label">Accessory Items</div>
                <div class="summary-value">
                    {{ number_format($summary['accessory_items']) }}
                </div>
            </td>
            @if($canViewCosts)
                <td>
                    <div class="summary-label">Accessory Stock Value</div>
                    <div class="summary-value">
                        NGN {{ number_format($summary['accessory_stock_value'], 2) }}
                    </div>
                </td>
            @else
                <td>
                    <div class="summary-label">Inventory Reporting</div>
                    <div class="summary-value">Operational</div>
                </td>
            @endif
            <td>
                <div class="summary-label">Low-Stock Accessories</div>
                <div class="summary-value">
                    {{ number_format($summary['low_stock_items']) }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">
        1. LPG Stock Position
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 11%;">Storage Code</th>
                <th style="width: 19%;">Storage Unit</th>
                <th style="width: 10%;">Type</th>
                <th style="width: 16%;">Branch</th>
                <th class="number" style="width: 13%;">Capacity (kg)</th>
                <th class="number" style="width: 13%;">Available (kg)</th>
                <th class="number" style="width: 10%;">Utilization</th>
                <th style="width: 8%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($lpgStock as $item)
                <tr>
                    <td>{{ $item['storage_code'] }}</td>
                    <td>{{ $item['storage_name'] }}</td>
                    <td>{{ ucfirst($item['storage_type']) }}</td>
                    <td>{{ $item['branch'] ?? '—' }}</td>

                    <td class="number">
                        {{ number_format((float) $item['capacity_kg'], 3) }}
                    </td>

                    <td class="number">
                        {{ number_format((float) $item['balance_kg'], 3) }}
                    </td>

                    <td class="number">
                        {{ number_format($item['utilization'], 2) }}%
                    </td>

                    <td>{{ ucfirst($item['status']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">
                        No LPG storage records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section-title">
        2. Accessory Inventory
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Accessory No.</th>
                <th style="width: 17%;">Item Name</th>
                <th style="width: 12%;">Category</th>
                <th style="width: 13%;">Branch</th>
                <th class="number" style="width: 10%;">Quantity</th>
                <th class="number" style="width: 10%;">Reorder Level</th>
                @if($canViewCosts)
                    <th class="number" style="width: 11%;">Buying Price</th>
                    <th class="number" style="width: 10%;">Stock Value</th>
                @endif
                <th style="width: 5%;">Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($accessoryStock as $item)
                <tr>
                    <td>{{ $item['accessory_number'] }}</td>
                    <td>{{ $item['name'] }}</td>
                    <td>{{ $item['category'] }}</td>
                    <td>{{ $item['branch'] ?? '—' }}</td>

                    <td class="number">
                        {{ number_format((float) $item['quantity'], 3) }}
                        {{ $item['unit'] }}
                    </td>

                    <td class="number">
                        {{ number_format((float) $item['reorder_level'], 3) }}
                    </td>

                    
                    @if($canViewCosts)
                        <td class="number">
                            {{ number_format((float) $item['unit_buying_price'], 2) }}
                        </td>

                        <td class="number">
                            {{ number_format((float) $item['stock_value'], 2) }}
                        </td>
                    @endif


                    <td>
                        @if ($item['low_stock'])
                            <span class="warning">Low</span>
                        @else
                            OK
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $canViewCosts ? 9 : 7 }}" class="empty">
                        No accessory inventory records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    
    <div class="note">
        <strong>Reporting Note:</strong>

        @if($canViewCosts)
            This report presents current LPG and accessory stock
            quantities, together with accessory inventory valuation
            at the time of generation. LPG monetary valuation is
            excluded pending costing validation.
        @else
            This report presents current LPG and accessory stock
            quantities, storage utilization and reorder information
            for authorized branches. Confidential procurement costs
            and inventory valuations are excluded.
        @endif

        Historical stock movement reporting is maintained separately.
    </div>


    <div class="footer">
        Intisar Oil &amp; Gas — LPG Stock and Inventory Management System
        | System-Generated Report
    </div>

</body>
</html>
