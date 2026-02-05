<!DOCTYPE html>
<html>
<head>
    <title>Laporan Stok Kedaluwarsa</title>
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
        .items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items th,
        .items td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Laporan Stok Kedaluwarsa</h1>
            <h3>APOTEK MUAZARA</h3>
            <p>Desa Cermee RT. 15 No.1 Bondowoso</p>
            <p>Ambang Batas Kedaluwarsa: {{ $expiry_threshold_months }} Bulan</p>
        </div>

        <table class="items">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Nomor Batch</th>
                    <th>Tanggal Kedaluwarsa</th>
                    <th>Stok</th>
                    <th>Harga Beli</th>
                    <th>Supplier</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productBatches as $batch)
                <tr>
                    <td>{{ $batch->product->name ?? 'N/A' }}</td>
                    <td>{{ $batch->batch_number }}</td>
                    <td>{{ \Carbon\Carbon::parse($batch->expiration_date)->format('d M Y') }}</td>
                    <td>{{ $batch->stock }}</td>
                    <td>Rp {{ number_format($batch->purchase_price, 2) }}</td>
                    <td>{{ $batch->purchase->supplier->name ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada stok kedaluwarsa dalam periode ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</body>
</html>
