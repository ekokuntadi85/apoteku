<?php

namespace App\Http\Controllers;

use App\Models\KartuMonitoringSuhu;
use Carbon\Carbon;
use PDF;

class KartuMonitoringSuhuController extends Controller
{
    public function printPdf($month)
    {
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $records = KartuMonitoringSuhu::with('user')
                                    ->whereBetween('waktu_pengukuran', [$startDate, $endDate])
                                    ->orderBy('waktu_pengukuran', 'asc')
                                    ->get();

        $groupedRecords = $records->groupBy(function($item) {
            return Carbon::parse($item->waktu_pengukuran)->format('Y-m-d');
        });

        $data = [
            'monthName' => Carbon::parse($month)->translatedFormat('F'),
            'year' => Carbon::parse($month)->format('Y'),
            'groupedRecords' => $groupedRecords,
        ];

        $pdf = PDF::loadView('livewire.kartu-monitoring-suhu-print', $data);
        return $pdf->stream('kartu-monitoring-suhu-' . $month . '.pdf');
    }
}
