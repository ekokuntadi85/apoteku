<!DOCTYPE html>
<html>
<head>
    <title>Laporan Produk Terlaris</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1, .header h2 {
            margin: 0;
            padding: 0;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan 30 Produk Terlaris</h1>
        <h2>Apotek Muazara</h2>
    </div>

    <div class="info">
        <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Peringkat</th>
                <th>Nama Item</th>
                <th>Jumlah Terjual</th>
                <th>Stok Saat Ini</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($topProducts as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->product->name ?? 'N/A' }}</td>
                    <td class="text-center">{{ number_format($item->total_quantity, 2) }}</td>
                    <td class="text-center" style="@if($item->current_stock < 10) color: red; font-weight: bold; @endif">{{ number_format($item->current_stock, 2) }}</td>
                    <td class="text-center">{{ $item->productUnit->name ?? 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">
                        Tidak ada data untuk ditampilkan pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
