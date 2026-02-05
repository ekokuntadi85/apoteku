<?php

namespace App\Livewire;

use App\Exports\SalesSummaryExport;
use Livewire\Component;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Title;

#[Title('Laporan Penjualan')]
class SummaryReport extends Component
{
    use WithPagination;

    public $startDate;
    public $endDate;
    public $totalRevenue = 0;
    public $averageDailyRevenue = 0;
    public $totalStockValue = 0;
    public $profitLossCode = '';
    public $totalProfitLoss = 0;
    public $showProfitLossValue = false;

    // View properties
    public $viewMode = 'summary'; // 'summary' or 'detail'
    public $selectedDate;
    public $dailyTotalRevenue = 0;


    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function exportExcel()
    {
        return Excel::download(new SalesSummaryExport($this->startDate, $this->endDate), 'laporan-penjualan-ringkasan.xlsx');
    }

    public function render()
    {
        $baseQuery = Transaction::whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);

        if ($this->viewMode === 'summary') {
            $this->totalRevenue = (clone $baseQuery)->sum('total_price');

            // Calculate average daily revenue
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);
            $numberOfDays = $startDate->diffInDays($endDate) + 1;

            if ($numberOfDays > 0) {
                $this->averageDailyRevenue = $this->totalRevenue / $numberOfDays;
            } else {
                $this->averageDailyRevenue = 0;
            }

            // Calculate total stock value
            $this->totalStockValue = DB::table('product_batches')->sum(DB::raw('stock * purchase_price'));

            $dailySummaries = (clone $baseQuery)
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_price) as daily_total')
                )
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->paginate(5);

            return view('livewire.summary-report', [
                'dailySummaries' => $dailySummaries
            ]);
        } else {
            $transactions = Transaction::with(['customer', 'user'])
                ->whereDate('created_at', $this->selectedDate)
                ->latest()
                ->paginate(5);

            $this->dailyTotalRevenue = Transaction::whereDate('created_at', $this->selectedDate)->sum('total_price');

            return view('livewire.summary-report', [
                'transactions' => $transactions
            ]);
        }
    }

    public function viewDailyReport($date)
    {
        $this->selectedDate = $date;
        $this->viewMode = 'detail';
        $this->resetPage();
    }

    public function showSummary()
    {
        $this->viewMode = 'summary';
        $this->selectedDate = null;
        $this->dailyTotalRevenue = 0;
        $this->resetProfitLoss(); // Reset profit/loss when going back
        $this->resetPage();
    }


    public function filter()
    {
        $this->resetProfitLoss();
        $this->showSummary(); // Go back to summary view on new filter
    }

    public function calculateProfitLoss()
    {
        $this->validate([
            'profitLossCode' => 'required|string|in:8485',
        ], [
            'profitLossCode.required' => 'Kode proteksi wajib diisi.',
            'profitLossCode.in' => 'Kode proteksi salah.',
        ]);

        $query = Transaction::with(['transactionDetails.product.productBatches']);

        if ($this->viewMode === 'summary') {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            $transactions = $query->get();
            $revenue = $this->totalRevenue;
        } else {
            $query->whereDate('created_at', $this->selectedDate);
            $transactions = $query->get();
            $revenue = $this->dailyTotalRevenue;
        }


        $totalCOGS = 0;
        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionDetails as $detail) {
                $product = $detail->product;
                if ($product && $product->productBatches->isNotEmpty()) {
                    // This logic might need refinement if you have multiple batches per product
                    $costPerUnit = $product->productBatches->first()->purchase_price;
                    $totalCOGS += $detail->quantity * $costPerUnit;
                }
            }
        }

        $this->totalProfitLoss = $revenue - $totalCOGS;
        $this->showProfitLossValue = true;
    }

    public function resetProfitLoss()
    {
        $this->showProfitLossValue = false;
        $this->profitLossCode = '';
        $this->totalProfitLoss = 0;
    }
}