<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi</title>
    <style>
        @page {
            size: 54mm;
            margin: 1mm;
        }
        html, body {
            width: 54mm;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white;
        }
        body {
            font-family: monospace;
            font-size: 9pt;
            color: black;
            box-sizing: border-box;
        }
        .receipt-container {
            width: 100%;
            box-sizing: border-box;
        }
        * {
            box-sizing: border-box;
            word-wrap: break-word;
        }
        .header, .footer {
            text-align: center;
        }
        .header h1 {
            font-size: 11pt;
            margin: 0;
            padding: 0;
        }
        .header p {
            margin: 1mm 0;
            font-size: 8pt;
        }
        .meta-info, .item-section, .totals-section, .footer {
            border-top: 1px dashed black;
            padding-top: 1.5mm;
            margin-top: 1.5mm;
        }
        .meta-info table, .totals-section table {
            width: 100%;
            border-collapse: collapse;
        }
        .item-row {
            margin-bottom: 1.5mm;
        }
        .item-breakdown {
            display: flex;
            justify-content: space-between;
        }
        .item-name {
            font-size: 9pt;
            margin: 0 0 0.5mm 0;
            padding: 0;
        }
        .item-details {
            font-size: 8pt;
        }
        .subtotal {
            font-size: 9pt;
            text-align: right;
            white-space: nowrap;
        }
        .totals-section .value {
            text-align: right;
            white-space: nowrap;
        }
        .footer {
            font-size: 8pt;
        }
        @media print {
            html, body {
                width: 54mm;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body > *:not(.receipt-container) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="header">
            <h1>{{ config('settings.app_name', 'Apotek Muazara') }}</h1>
            <p>{{ config('settings.address', 'Desa Cermee RT. 15 No.1, Cermee, Bondowoso') }}</p>
            <p>Telp: {{ config('settings.phone_number', '0857-0895-4067') }}</p>
        </div>

        <div class="meta-info">
            <table>
                <tr>
                    <td>No:</td>
                    <td class="text-right">{{ $transaction->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Tanggal:</td>
                    <td class="text-right">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>Kasir:</td>
                    <td class="text-right">{{ $transaction->user->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Pelanggan:</td>
                    <td class="text-right">{{ $transaction->customer->name ?? 'UMUM' }}</td>
                </tr>
            </table>
        </div>

        <div class="item-section">
            @foreach($transaction->transactionDetails as $detail)
                @php
                    // Calculate display quantity from base unit quantity
                    $displayQty = $detail->quantity / ($detail->productUnit->conversion_factor ?? 1);
                    // Recalculate subtotal: display quantity * price per sold unit
                    $subtotal = $displayQty * $detail->price;
                @endphp
                <div class="item-row">
                    <p class="item-name">{{ $detail->product->name }}</p>
                    <div class="item-breakdown">
                        <span class="item-details">
                            {{ rtrim(rtrim(number_format($displayQty, 2, ',', '.'), '0'), ',') }} {{ $detail->productUnit->name ?? $detail->product->baseUnit->name }} x {{ number_format($detail->price, 0, ',', '.') }}
                        </span>
                        <span class="subtotal">
                            {{ number_format($subtotal, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="totals-section">
            <table>
                <tr>
                    <td class="label">Subtotal</td>
                    <td class="value">{{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                </tr>
                {{-- Add other totals like discount or tax if needed --}}
                <tr class="bold">
                    <td class="label">Total</td>
                    <td class="value">{{ number_format($transaction->total_price, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Bayar</td>
                    <td class="value">{{ number_format($transaction->amount_paid, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Kembali</td>
                    <td class="value">{{ number_format($transaction->change, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <p>Terima kasih telah berbelanja!</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            // Trigger print dialog
            window.print();
        }

        // Auto-close after print dialog is closed
        window.onafterprint = function() {
            window.close();
        };

        // Fallback for browsers that don't support onafterprint
        // Also handles case when user cancels print
        var mediaQueryList = window.matchMedia('print');
        mediaQueryList.addListener(function(mql) {
            if (!mql.matches) {
                // User finished printing or cancelled
                setTimeout(function() {
                    window.close();
                }, 100);
            }
        });
    </script>
</body>
</html>
