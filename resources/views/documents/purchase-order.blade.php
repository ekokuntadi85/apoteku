<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Pesanan - {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            display: table;
            width: 100%;
        }
        .header-logo {
            display: table-cell;
            vertical-align: middle;
            width: 80px; /* Adjust based on logo size */
            padding-right: 15px;
        }
        .header-text {
            display: table-cell;
            vertical-align: middle;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 2px 0;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            vertical-align: top;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }
        .content-table th {
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
            width: 100%;
        }
        .signature {
            float: right;
            width: 250px; /* Increased width to prevent wrapping */
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
        }
        .signature-name {
            font-weight: bold;
            white-space: nowrap; /* Prevent name from wrapping */
        }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($settings['app_logo_path']) && $settings['app_logo_path'])
        <div class="header-logo">
            <img src="{{ public_path('storage/' . $settings['app_logo_path']) }}" alt="Logo" style="max-height: 80px; width: auto;">
        </div>
        @endif
        <div class="header-text">
            <h1>{{ $settings['app_name'] ?? 'Apotek Sehat Selalu' }}</h1>
            <p>{{ $settings['address'] ?? 'Alamat Apotek' }}</p>
            <p>Telp: {{ $settings['phone_number'] ?? '-' }}</p>
            <p>SIA: {{ $settings['sia_number'] ?? '-' }}</p>
            <p>SIPA: {{ $settings['sipa_number'] ?? '-' }}</p>
        </div>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="margin: 0; text-decoration: underline;">SURAT PESANAN</h2>
        <p style="margin: 5px 0;">Nomor: {{ $purchaseOrder->po_number }}</p>
    </div>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Kepada Yth:</strong><br>
                {{ $purchaseOrder->supplier->name }}<br>
                {{ $purchaseOrder->supplier->address ?? 'Alamat tidak tersedia' }}<br>
                Telp: {{ $purchaseOrder->supplier->phone ?? '-' }}
            </td>
            <td width="40%" style="text-align: right;">
                <strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d F Y') }}<br>
                <strong>Status:</strong> {{ ucfirst($purchaseOrder->status) }}
            </td>
        </tr>
    </table>

    <p>Mohon dikirimkan obat-obatan/barang sebagai berikut:</p>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">No</th>
                <th style="width: 45%;">Nama Barang</th>
                <th style="width: 15%;">Satuan</th>
                <th style="width: 10%; text-align: center;">Jumlah</th>
                <th style="width: 25%;">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->details as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $detail->product->name }}</td>
                <td>{{ $detail->productUnit->name }}</td>
                <td style="text-align: center;">{{ $detail->quantity }}</td>
                <td>{{ $detail->notes ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p>Hormat Kami,<br>Apoteker Penanggung Jawab</p>
            <div class="signature-line"></div>
            <p class="signature-name">( {{ $settings['pharmacist_name'] ?? '.................................................' }} )</p>
            <p>SIPA: {{ $settings['sipa_number'] ?? '...........................................' }}</p>
        </div>
    </div>
</body>
</html>
