<!DOCTYPE html>
<html>
<head>
    <title>Invoice #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        .container {
            width: 100%;
            margin: auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .details,
        .items,
        .total {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details td,
        .items th,
        .items td,
        .total td {
            padding: 8px;
            border: 1px solid #ccc;
        }
        .items th {
            background-color: #f2f2f2;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>INVOICE</h1>
            <h3>APOTEK MUAZARA</h3>
            <p>Desa Cermee RT. 15 No.1 Bondowoso</p>
        </div>

        <table class="details">
            <tr>
                <td>Invoice No:</td>
                <td>{{ $transaction->invoice_number }}</td>
            </tr>
            <tr>
                <td>Date:</td>
                <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Customer:</td>
                <td>{{ $transaction->customer->name ?? 'Umum' }}</td>
            </tr>
            <tr>
                <td>Cashier:</td>
                <td>{{ $transaction->user->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Payment Status:</td>
                <td>{{ ucfirst($transaction->payment_status) }}</td>
            </tr>
            @if($transaction->due_date)
            <tr>
                <td>Due Date:</td>
                <td>{{ \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') }}</td>
            </tr>
            @endif
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Qty</th>
                    <th>Harga Satuan</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->transactionDetails as $detail)
                <tr>
                    <td>{{ $detail->product->name }}</td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">Rp {{ number_format($detail->price, 0) }}</td>
                    <td class="text-right">Rp {{ number_format($detail->quantity * $detail->price, 0) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="total">
            <tr>
                <td class="text-right">Total:</td>
                <td class="text-right">Rp {{ number_format($transaction->total_price, 0) }}</td>
            </tr>
        </table>

        <div class="footer">
            <p>Terima kasih atas kepercayaan Anda.</p>
        </div>
    </div>
</body>
</html>
