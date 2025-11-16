<!DOCTYPE html>
<html>
<head>
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: "Arial Narrow", sans-serif; /* Changed font-family to a narrow sans-serif */
            font-size: 10px; 
            width: 48mm; /* Adjusted to 48mm */
            margin: 0; 
            padding: 2mm; 
        }
        .container {
            width: 125%;
        }
        .header {
            text-align: center;
        }
        .details,
        .items,
        .total {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .items th,
        .items td,
        .total td {
            padding: 1px 0; 
            border: none; 
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h3>{{ config('settings.app_name', 'APOTEK MUAZARA') }}</h3>
            <p>{{ config('settings.address', 'Desa Cermee RT. 15 No.1 Bondowoso') }}</p>
            <p>{{ config('settings.phone_number', '') }}</p>
        </div>

        <div class="line"></div>

        <table class="details">
            <tr>
                <td>Invoice:</td>
                <td class="text-right">{{ $transaction->invoice_number }}</td>
            </tr>
            <tr>
                <td>Tanggal:</td>
                <td class="text-right">{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir:</td>
                <td class="text-right">{{ $transaction->user->name }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <table class="items">
            <tbody>
                @foreach($transaction->transactionDetails as $detail)
                <tr>
                    <td colspan="2">{{ $detail->product->name }}</td>
                </tr>
                <tr>
                    <td>{{ $detail->quantity }} x Rp {{ number_format($detail->price, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($detail->quantity * $detail->price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="line"></div>

        <table class="total">
            <tr>
                <td>Total:</td>
                <td class="text-right">Rp {{ number_format($transaction->total_price, 0) }}</td>
            </tr>
            <tr>
                <td>Bayar:</td>
                <td class="text-right">Rp {{ number_format($transaction->amount_paid, 0) }}</td>
            </tr>
            <tr>
                <td>Kembali:</td>
                <td class="text-right">Rp {{ number_format($transaction->change, 0) }}</td>
            </tr>
        </table>

        <div class="line"></div>

        <div class="text-center" style="margin-top: 5px;">
            <p>Terima Kasih!</p>
            <p>Semoga Allah berikan Kesembuhan</p>
        </div>
    </div>
</body>
</html>