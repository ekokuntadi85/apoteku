<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produk Terlaris Stok Menipis</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .container { max-width: 800px; margin: auto; }
        .form-container { margin-bottom: 2rem; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laporan Produk Terlaris Stok Menipis</h1>
        <p>Laporan ini menampilkan 100 produk terlaris bulan lalu yang memiliki stok kurang dari 5.</p>

        <div class="form-container">
            <form method="POST" action="{{ route('reports.low-stock-analysis') }}">
                @csrf
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Tampilkan Laporan</button>
            </form>
            @if($error)
                <p class="error">{{ $error }}</p>
            @endif
        </div>

        @if(isset($data))
            @if(empty($data))
                <p>Tidak ada produk terlaris yang stoknya menipis.</p>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Stok Saat Ini</th>
                            <th>Total Terjual (Bulan Lalu)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data as $item)
                            <tr>
                                <td>{{ $item['name'] }}</td>
                                <td>{{ $item['current_stock'] }}</td>
                                <td>{{ $item['total_sold_last_month'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif
    </div>
</body>
</html>
