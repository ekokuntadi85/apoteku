<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Besar - {{ $selectedAccount ? $selectedAccount->code . ' - ' . $selectedAccount->name : 'Semua Akun' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #000;
            background: white;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px double #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14pt;
            margin-bottom: 3px;
        }
        
        .header .period {
            font-size: 9pt;
            color: #666;
        }
        
        .account-info {
            background: #f0f0f0;
            padding: 8px;
            margin: 10px 0;
            border-left: 4px solid #4F46E5;
            font-weight: bold;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        thead {
            background: #f5f5f5;
        }
        
        th {
            padding: 8px 6px;
            text-align: left;
            font-size: 9pt;
            font-weight: bold;
            border-bottom: 2px solid #333;
            text-transform: uppercase;
        }
        
        th.text-right {
            text-align: right;
        }
        
        td {
            padding: 6px;
            border-bottom: 1px solid #ddd;
            font-size: 9pt;
        }
        
        td.text-right {
            text-align: right;
        }
        
        .opening-row {
            background: #fffbeb;
            font-weight: bold;
        }
        
        .total-row {
            background: #f0f0f0;
            font-weight: bold;
            border-top: 2px solid #333;
        }
        
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #666;
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
        <h1>APOTEKU</h1>
        <h2>Buku Besar (General Ledger)</h2>
        <p class="period">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>
    
    @if($selectedAccount)
    <div class="account-info">
        {{ $selectedAccount->code }} - {{ $selectedAccount->name }} ({{ ucfirst($selectedAccount->type) }})
    </div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th style="width: 10%">Tanggal</th>
                <th style="width: 12%">Referensi</th>
                <th style="width: 38%">Keterangan</th>
                <th class="text-right" style="width: 13%">Debit</th>
                <th class="text-right" style="width: 13%">Kredit</th>
                <th class="text-right" style="width: 14%">Saldo</th>
            </tr>
        </thead>
        <tbody>
            <!-- Opening Balance -->
            <tr class="opening-row">
                <td colspan="3">Saldo Awal</td>
                <td class="text-right">-</td>
                <td class="text-right">-</td>
                <td class="text-right">Rp {{ number_format($openingBalance, 2, ',', '.') }}</td>
            </tr>
            
            <!-- Transactions -->
            @forelse($ledgerData as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}</td>
                <td>{{ $row['reference'] }}</td>
                <td>{{ $row['description'] }}</td>
                <td class="text-right">{{ $row['debit'] > 0 ? 'Rp ' . number_format($row['debit'], 2, ',', '.') : '-' }}</td>
                <td class="text-right">{{ $row['credit'] > 0 ? 'Rp ' . number_format($row['credit'], 2, ',', '.') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($row['balance'], 2, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; padding: 20px; color: #999;">
                    Tidak ada transaksi pada periode ini
                </td>
            </tr>
            @endforelse
            
            <!-- Total Row -->
            <tr class="total-row">
                <td colspan="3">TOTAL PERGERAKAN</td>
                <td class="text-right">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                <td class="text-right">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
                <td class="text-right">-</td>
            </tr>
        </tbody>
    </table>
    
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Apoteku Finance System</p>
    </div>
    
    <script>
        // Auto print when page loads
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
