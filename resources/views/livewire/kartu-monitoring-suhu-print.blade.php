<div>
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
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
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
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer-text {
            margin-top: 30px;
            font-style: italic;
            text-align: right;
        }
    </style>
    <div class="header">
        <h1><b>Kartu Monitoring Suhu</b></h1>
        <h2><b>Apotek Muazara</b></h2>
    </div>

    <div class="info">
        <div style="float: left;">Bulan : {{ $monthName }}</div>
        <div style="float: right;">Tahun : {{ $year }}</div>
        <div style="clear: both;"></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Suhu Ruangan (&deg;C)</th>
                <th>Suhu Pendingin (&deg;C)</th>
                <th>User Input</th>
            </tr>
        </thead>
        <tbody>
            @foreach($groupedRecords as $date => $records)
                @foreach($records as $index => $record)
                <tr>
                    @if($index == 0)
                    <td rowspan="{{ count($records) }}">{{ \Carbon\Carbon::parse($record->waktu_pengukuran)->format('d') }}</td>
                    @endif
                    <td>{{ \Carbon\Carbon::parse($record->waktu_pengukuran)->format('H:i') }}</td>
                    <td>{{ $record->suhu_ruangan }}</td>
                    <td>{{ $record->suhu_pendingin }}</td>
                    <td>{{ $record->user?->name }}</td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <p class="footer-text">Suhu Pengukuran dalam satuan derajat Celsius</p>
</div>