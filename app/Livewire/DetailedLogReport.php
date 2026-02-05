<?php

namespace App\Livewire;

use App\Exports\DetailedSalesReportExport;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class DetailedLogReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function filter()
    {
        // This method is called when the filter button is clicked.
        // We just need to reset pagination.
        $this->resetPage();
    }

    public function exportExcel()
    {
        return Excel::download(new DetailedSalesReportExport($this->startDate, $this->endDate), 'laporan-penjualan-rinci.xlsx');
    }

    public function render()
    {
        $details = TransactionDetail::query()
            ->whereHas('transaction', function ($query) {
                $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->with(['transaction:id,invoice_number,created_at', 'product:id,name'])
            ->latest('created_at')
            ->paginate(15);

        return view('livewire.detailed-log-report', [
            'details' => $details,
        ]);
    }
}
