<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>
        Accessories Receipt - {{ $sale->sale_number }}
    </title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            background: #edf2ef;
            color: #111;
            font-family: Arial, sans-serif;
        }

        .receipt {
            width: 58mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 3mm;
            background: #fff;
            font-size: 10px;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }

        .receipt-header {
            text-align: center;
        }

        .receipt-header h1 {
            font-size: 13px;
            font-weight: 800;
            margin: 0 0 4px;
        }

        .receipt-header p {
            font-size: 10px;
            margin: 2px 0;
        }

        .receipt-title {
            font-size: 11px;
            font-weight: 700;
            margin-top: 8px;
        }

        .receipt-divider {
            border: 0;
            border-top: 1px dashed #111;
            margin: 9px 0;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 5px;
            margin: 5px 0;
        }

        .receipt-row span:first-child {
            flex: 1;
        }

        .receipt-row span:last-child {
            text-align: right;
            font-weight: 600;
        }

        .receipt-reference {
            font-size: 9px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .receipt-total {
            font-size: 14px;
            font-weight: 800;
        }

        .receipt-footer {
            text-align: center;
            font-size: 10px;
            margin-top: 12px;
        }

        .receipt-footer p {
            margin: 4px 0;
        }

        .print-actions {
            max-width: 360px;
            margin: 20px auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .print-button {
            display: block;
            width: 100%;
            padding: 13px 16px;
            border: 0;
            border-radius: 8px;
            background: #176b45;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            text-align: center;
            text-decoration: none;
            cursor: pointer;
        }

        .back-button {
            background: #fff;
            color: #176b45;
            border: 1px solid #cbd5e1;
        }

        .print-note {
            font-size: 12px;
            line-height: 1.6;
            color: #64748b;
            text-align: center;
        }

        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            html,
            body {
                width: 58mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            .receipt {
                width: 58mm;
                max-width: 58mm;
                margin: 0;
                padding: 2mm 3mm;
                box-shadow: none;
                border: 0;
            }

            .print-actions {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="receipt">

        <div class="receipt-header">
            <h1>INTISAR OIL &amp; GAS</h1>

            <p>
                {{ $sale->branch?->branch_name ?? 'Accessories Sales' }}
            </p>

            <div class="receipt-title">
                ACCESSORIES SALES RECEIPT
            </div>
        </div>

        <hr class="receipt-divider">

        <div class="receipt-reference">
            <strong>Receipt No:</strong>
            {{ $sale->sale_number }}
        </div>

        <div class="receipt-row">
            <span>Date</span>
            <span>
                {{ $sale->sale_date?->format('d/m/Y') ?? '—' }}
            </span>
        </div>

        <div class="receipt-row">
            <span>Time</span>
            <span>
                {{ $sale->posted_at?->format('h:i A') ?? '—' }}
            </span>
        </div>

        <div class="receipt-row">
            <span>Sales Agent</span>
            <span>
                {{ $sale->attendant?->name ?? '—' }}
            </span>
        </div>

        <hr class="receipt-divider">

        <div class="receipt-row">
            <span>Product</span>
            <span>
                {{ $sale->accessory?->name ?? 'Accessory' }}
            </span>
        </div>

        <div class="receipt-reference">
            <strong>Product Code:</strong>
            {{ $sale->accessory?->accessory_number ?? '—' }}
        </div>

        <div class="receipt-row">
            <span>Quantity</span>
            <span>
                {{ number_format((float) $sale->quantity, 3) }}
                {{ $sale->accessory?->unit ?? '' }}
            </span>
        </div>

        <div class="receipt-row">
            <span>Unit Price</span>
            <span>
                NGN {{ number_format(
                    (float) $sale->unit_selling_price,
                    2
                ) }}
            </span>
        </div>

        <hr class="receipt-divider">

        <div class="receipt-row receipt-total">
            <span>TOTAL</span>
            <span>
                NGN {{ number_format(
                    (float) $sale->total_amount,
                    2
                ) }}
            </span>
        </div>

        <hr class="receipt-divider">

        <div class="receipt-row">
            <span>Status</span>
            <span>POSTED</span>
        </div>

        <hr class="receipt-divider">

        <div class="receipt-footer">
            <p><strong>Thank you for your patronage!</strong></p>
            <p>Intisar Oil &amp; Gas</p>
            <p>Powered by LPG-SIMS</p>
        </div>

    </div>

    <div class="print-actions">

        <button
            type="button"
            class="print-button"
            onclick="window.print()"
        >
            Print 58mm Receipt
        </button>

        <button
            type="button"
            class="print-button"
            onclick="shareReceipt()"
            style="background:#fff;color:#176b45;border:1px solid #176b45;"
        >
            Share Receipt
        </button>

        <a
            href="{{ route('admin.lpg-pos.index') }}"
            class="print-button"
        >
            New Sale
        </a>

        <p class="print-note">
            On Android, select your compatible Bluetooth
            thermal printer or printing application
            when the print dialog opens.
        </p>

    </div>

    <script>
        async function shareReceipt() {
            const receiptUrl = window.location.href;

            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Intisar Oil & Gas - Accessories Receipt',
                        text: 'Accessories Sales Receipt',
                        url: receiptUrl
                    });
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        alert('Unable to share this receipt.');
                    }
                }
            } else {
                try {
                    await navigator.clipboard.writeText(receiptUrl);
                    alert('Receipt link copied successfully.');
                } catch (error) {
                    alert('Sharing is not supported by this browser.');
                }
            }
        }
    </script>

</body>
</html>
