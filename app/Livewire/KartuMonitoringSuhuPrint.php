<?php

namespace App\Livewire;

use App\Models\KartuMonitoringSuhu;
use Livewire\Component;
use Carbon\Carbon;

class KartuMonitoringSuhuPrint extends Component
{
    public $month;
    public $groupedRecords = [];

    public function mount($month)
    {
        $this->month = $month;
        $this->loadRecords();
    }

    public function loadRecords()
    {
        $startDate = Carbon::parse($this->month)->startOfMonth();
        $endDate = Carbon::parse($this->month)->endOfMonth();

        $records = KartuMonitoringSuhu::with('user')
                                    ->whereBetween('waktu_pengukuran', [$startDate, $endDate])
                                    ->orderBy('waktu_pengukuran', 'asc')
                                    ->get();

        $this->groupedRecords = $records->groupBy(function($item) {
            return Carbon::parse($item->waktu_pengukuran)->format('Y-m-d');
        });
    }

    public function render()
    {
        return view('livewire.kartu-monitoring-suhu-print', [
            'monthName' => Carbon::parse($this->month)->translatedFormat('F'),
            'year' => Carbon::parse($this->month)->format('Y'),
            'groupedRecords' => $this->groupedRecords,
        ]);
    }
}