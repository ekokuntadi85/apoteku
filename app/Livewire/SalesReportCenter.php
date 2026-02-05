<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Pusat Laporan Penjualan')]
class SalesReportCenter extends Component
{
    public $activeTab = 'summary';

    public function render()
    {
        return view('livewire.sales-report-center');
    }
}
