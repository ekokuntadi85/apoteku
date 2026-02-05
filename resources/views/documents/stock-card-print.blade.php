<!DOCTYPE html>
<html>
<head>
    <title>Kartu Stok {{ $product->name }}</title>
    <style>
        @page {
            margin: 20mm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        
        .header h2 {
            margin: 0 0 10px 0;
            font-size: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header h3 {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 11px;
            color: #666;
        }
        
        .balance-info {
            background-color: #f5f5f5;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-left: 4px solid #4a5568;
            border-radius: 3px;
        }
        
        .balance-info strong {
            color: #2d3748;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 8px 10px;
            text-align: left;
        }
        
        th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        td {
            font-size: 11px;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .font-bold {
            font-weight: bold;
        }
        
        .text-green {
            color: #059669;
        }
        
        .text-red {
            color: #dc2626;
        }
        
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9fafb;
            border: 2px solid #4a5568;
            border-radius: 5px;
        }
        
        .summary p {
            margin: 5px 0;
            font-size: 13px;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 60px;
        }
        
        .signature-box {
            text-align: center;
            width: 40%;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
        }
        
        /* Page numbers */
        @media print {
            .page-number:after {
                content: counter(page);
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Kartu Stok Produk</h2>
        <h3>{{ $product->name }}</h3>
        <p><strong>SKU:</strong> {{ $product->sku }} | <strong>Periode:</strong> {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <div class="balance-info">
        <strong>Saldo Awal (s/d {{ \Carbon\Carbon::parse($startDate)->subDay()->format('d/m/Y') }}):</strong> 
        <span class="{{ $initialBalance >= 0 ? 'text-green' : 'text-red' }} font-bold">{{ $initialBalance }}</span>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%">Tanggal</th>
                <th style="width: 8%">Kode</th>
                <th style="width: 35%">Keterangan</th>
                <th style="width: 12%" class="text-right">Masuk</th>
                <th style="width: 12%" class="text-right">Keluar</th>
                <th style="width: 12%" class="text-right">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @php
                $currentBalance = $initialBalance;
            @endphp
            @forelse($finalMovements as $movement)
                @php
                    $currentBalance += $movement['masuk'] - $movement['keluar'];
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($movement['created_at'])->format('d/m/Y') }}</td>
                    <td class="text-center font-bold">{{ $movement['type'] }}</td>
                    <td>{{ $movement['remarks'] }}</td>
                    <td class="text-right {{ $movement['masuk'] > 0 ? 'text-green font-bold' : '' }}">
                        {{ $movement['masuk'] > 0 ? number_format($movement['masuk'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right {{ $movement['keluar'] > 0 ? 'text-red font-bold' : '' }}">
                        {{ $movement['keluar'] > 0 ? number_format($movement['keluar'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="text-right font-bold {{ $currentBalance >= 0 ? 'text-green' : 'text-red' }}">
                        {{ number_format($currentBalance, 0, ',', '.') }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 30px;">Tidak ada pergerakan stok dalam periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Saldo Akhir (s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}):</strong> 
            <span class="{{ $currentBalance >= 0 ? 'text-green' : 'text-red' }} font-bold" style="font-size: 16px;">{{ number_format($currentBalance, 0, ',', '.') }}</span>
        </p>
    </div>

    <div class="footer">
        <div class="signature-section">
            <div class="signature-box">
                <p>Disiapkan Oleh,</p>
                <div class="signature-line">
                    <p>(...........................)</p>
                </div>
            </div>
            <div class="signature-box">
                <p>Disetujui Oleh,</p>
                <div class="signature-line">
                    <p>(...........................)</p>
                </div>
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 30px; font-size: 10px; color: #999;">
            Dicetak pada: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | Halaman <span class="page-number"></span>
        </p>
    </div>
</body>
</html>
