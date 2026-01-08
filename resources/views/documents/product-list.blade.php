<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Obat</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            margin: 0;
            padding: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 16pt;
            font-weight: bold;
        }
        
        .header p {
            margin: 2px 0;
            font-size: 10pt;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 4px 6px;
            text-align: left;
        }
        
        th {
            background-color: #e0e0e0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        .unit-row {
            background-color: #f9f9f9;
        }
        
        .footer {
            margin-top: 15px;
            font-size: 8pt;
            text-align: left;
        }
        
        .no-border-left {
            border-left: none;
        }
        
        .no-border-right {
            border-right: none;
        }
        
        .no-border-bottom {
            border-bottom: none;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>DAFTAR OBAT</h1>
        <p>{{ config('settings.app_name', 'APOTEK MUAZARA') }}</p>
        <p>Tanggal Cetak: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 8%;">Kode</th>
                <th style="width: 20%;">Nama Obat</th>
                <th style="width: 10%;">Kategori</th>
                <th colspan="5" style="text-align: center;">Detail Per Satuan</th>
            </tr>
            <tr>
                <th colspan="4"></th>
                <th style="width: 8%;">Satuan</th>
                <th style="width: 8%;">Stok</th>
                <th style="width: 11%;">H. Beli</th>
                <th style="width: 11%;">H. Jual</th>
                <th style="width: 11%;">H. Member</th>
            </tr>
        </thead>
        <tbody>
            @php
                $no = 1;
                $totalProducts = 0;
                $totalUnits = 0;
            @endphp
            
            @foreach($products as $product)
                @php
                    $totalProducts++;
                    $units = $product->productUnits;
                    $unitCount = $units->count();
                    $totalUnits += $unitCount;
                    
                    // Calculate total stock in base unit
                    $totalStockBaseUnit = $product->productBatches->sum('stock');
                @endphp
                
                @foreach($units as $index => $unit)
                    @php
                        // Calculate stock for this unit
                        $stockInThisUnit = $unit->conversion_factor > 0 
                            ? intdiv($totalStockBaseUnit, $unit->conversion_factor) 
                            : 0;
                    @endphp
                    
                    <tr class="{{ $index > 0 ? 'unit-row' : '' }}">
                        @if($index === 0)
                            <td class="text-center" rowspan="{{ $unitCount }}">{{ $no }}</td>
                            <td rowspan="{{ $unitCount }}">{{ $product->sku }}</td>
                            <td rowspan="{{ $unitCount }}">{{ $product->name }}</td>
                            <td rowspan="{{ $unitCount }}">{{ $product->category->name ?? '-' }}</td>
                        @endif
                        
                        <td>{{ $unit->name }}</td>
                        <td class="text-right">{{ number_format($stockInThisUnit, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($unit->purchase_price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($unit->selling_price, 0, ',', '.') }}</td>
                        <td class="text-right">{{ $unit->member_price ? number_format($unit->member_price, 0, ',', '.') : '-' }}</td>
                    </tr>
                @endforeach
                
                @php
                    $no++;
                @endphp
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p><strong>Keterangan:</strong></p>
        <ul style="margin: 5px 0; padding-left: 20px;">
            <li>Harga dalam Rupiah</li>
            <li>Stok dalam satuan masing-masing</li>
            <li>H. Member: Harga khusus member (tanda "-" jika tidak ada)</li>
        </ul>
        <p><strong>Total Produk:</strong> {{ $totalProducts }} item ({{ $totalUnits }} satuan)</p>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        }
        
        window.onafterprint = function() {
            window.close();
        }
    </script>
</body>
</html>
