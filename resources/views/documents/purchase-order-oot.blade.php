<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Surat Pesanan OOT - {{ $purchaseOrder->po_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            margin: 20px;
        }
        .header-section {
            margin-bottom: 15px;
        }
        .header-section p {
            margin: 2px 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            text-decoration: underline;
            margin: 20px 0 15px 0;
        }
        .info-line {
            margin: 3px 0;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .content-table th, .content-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
            font-size: 10px;
        }
        .content-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        .footer-section {
            margin-top: 30px;
        }
        .footer-info p {
            margin: 2px 0;
        }
        .signature-section {
            margin-top: 40px;
            text-align: right;
        }
        .signature-line {
            margin-top: 60px;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header-section">
        <p>Yang bertanda tangan dibawah ini :</p>
        <p>Nama&nbsp;&nbsp;&nbsp;&nbsp;: {{ $settings['pharmacist_name'] ?? '.........................................' }}</p>
        <p>Jabatan&nbsp;: APA (Apoteker Penanggungjawab Apotek)</p>
        <p>SIPA&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $settings['sipa_number'] ?? '.........................................' }}</p>
        <p style="margin-top: 8px;">Mengajukan permohonan kepada :</p>
    </div>

    <div class="title">SURAT PESANAN OBAT-OBAT TERTENTU (OOT)</div>

    <div style="float: right; margin-bottom: 10px;">
        <strong>No. SP :</strong> {{ $purchaseOrder->po_number }}
    </div>
    <div style="clear: both;"></div>

    <div class="info-line"><strong>Nama PBF</strong>&nbsp;&nbsp;: {{ $purchaseOrder->supplier->name }}</div>
    <div class="info-line"><strong>Alamat</strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $purchaseOrder->supplier->address ?? '-' }}</div>

    <p style="margin-top: 10px;">Jenis obat yang mengandung Obat - Obat Tertentu yang dipesan adalah sbb. :</p>

    <table class="content-table">
        <thead>
            <tr>
                <th style="width: 5%;">No.</th>
                <th style="width: 25%;">Nama Obat</th>
                <th style="width: 20%;">Zat Aktif</th>
                <th style="width: 15%;">Bentuk Sediaan</th>
                <th style="width: 10%;">Satuan</th>
                <th style="width: 10%;">Jml Angka</th>
                <th style="width: 15%;">Jumlah Huruf</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->details as $index => $detail)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $detail->product->name }}</td>
                <td>{{ $detail->active_substance ?? '-' }}</td>
                <td>{{ $detail->dosage_form ?? '-' }}</td>
                <td>{{ $detail->productUnit->name }}</td>
                <td style="text-align: center;">{{ $detail->quantity }}</td>
                <td>{{ \App\Helpers\NumberToWords::convert($detail->quantity) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-section">
        <p><strong>Untuk keperluan Apotek/To. Obat/Lembaga :</strong></p>
        <div class="footer-info">
            <p>Nama&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>{{ $settings['app_name'] ?? 'Apotek' }}</strong></p>
            <p>Rauf</p>
            <p>Alamat&nbsp;&nbsp;&nbsp;: {{ $settings['address'] ?? '-' }}</p>
            <p>No. SIA&nbsp;&nbsp;: {{ $settings['sia_number'] ?? '-' }}</p>
            <p>Telp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: {{ $settings['phone_number'] ?? '-' }}</p>
        </div>

        <div class="signature-section">
            <p>{{ $purchaseOrder->supplier->address ?? 'Bondowoso' }}, {{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') }}</p>
            <p>Penanggung Jawab</p>
            <div class="signature-line"></div>
            <p><strong>{{ $settings['pharmacist_name'] ?? '.........................................' }}</strong></p>
            <p>{{ $settings['sipa_number'] ?? '.........................................' }}</p>
        </div>
    </div>
</body>
</html>
