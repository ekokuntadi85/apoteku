<?php

namespace App\Livewire;

use App\Exports\ItemSalesReportExport;
use App\Models\Product;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ItemReport extends Component
{
    use WithPagination;

    public $searchTerm = '';
    public ?Product $selectedProduct = null;

    // Filter properties
    public $filterType = 'all'; // all, day, month, range
    public $filterDate;
    public $filterMonth;
    public $filterStartDate;
    public $filterEndDate;

    public $totalQuantityInBaseUnit = 0;
    public $baseUnitName = '';

    public function mount()
    {
        $this->filterDate = Carbon::today()->format('Y-m-d');
        $this->filterMonth = Carbon::today()->format('Y-m');
        $this->filterStartDate = Carbon::today()->startOfMonth()->format('Y-m-d');
        $this->filterEndDate = Carbon::today()->endOfMonth()->format('Y-m-d');
    }

    #[Computed]
    public function products()
    {
        if (strlen($this->searchTerm) < 2) {
            return [];
        }

        return Product::where('name', 'like', '%' . $this->searchTerm . '%')
            ->orWhere('sku', 'like', '%' . $this->searchTerm . '%')
            ->take(5)
            ->get();
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::with('baseUnit')->find($productId);
        $this->searchTerm = '';
        $this->resetFilters();
        $this->baseUnitName = $this->selectedProduct->baseUnit->name ?? '';
    }

    public function clearSelection()
    {
        $this->selectedProduct = null;
        $this->searchTerm = '';
        $this->resetFilters();
        $this->baseUnitName = '';
        $this->totalQuantityInBaseUnit = 0;
    }

    public function applyFilter()
    {
        $this->resetPage('salesPage');
    }

    public function resetFilters()
    {
        $this->filterType = 'all';
        $this->mount(); // Reset dates to default
        $this->resetPage('salesPage');
    }

    public function exportExcel()
    {
        if (!$this->selectedProduct) {
            return;
        }

        $fileName = 'laporan-penjualan-' . str_replace(' ', '-', strtolower($this->selectedProduct->name)) . '.xlsx';
        return Excel::download(new ItemSalesReportExport(
            $this->selectedProduct->id,
            $this->filterType,
            $this->filterDate,
            $this->filterMonth,
            $this->filterStartDate,
            $this->filterEndDate
        ), $fileName);
    }

    public function render()
    {
        $details = null;
        if ($this->selectedProduct) {
            $query = TransactionDetail::where('transaction_details.product_id', $this->selectedProduct->id)
                ->whereHas('transaction', function ($q) {
                    if ($this->filterType === 'day') {
                        $q->whereDate('created_at', $this->filterDate);
                    } elseif ($this->filterType === 'month') {
                        $q->whereMonth('created_at', Carbon::parse($this->filterMonth)->month)
                          ->whereYear('created_at', Carbon::parse($this->filterMonth)->year);
                    } elseif ($this->filterType === 'range') {
                        $q->whereBetween('created_at', [$this->filterStartDate . ' 00:00:00', $this->filterEndDate . ' 23:59:59']);
                    }
                });

            // More efficient calculation of total quantity in base unit
            $this->totalQuantityInBaseUnit = (clone $query)
                ->join('product_units', 'transaction_details.product_unit_id', '=', 'product_units.id')
                ->sum(DB::raw('transaction_details.quantity * product_units.conversion_factor'));

            $details = $query->with(['transaction:id,invoice_number,created_at', 'productUnit'])
                ->latest('created_at')
                ->paginate(10, ['*'], 'salesPage');
        }

        return view('livewire.item-report', [
            'details' => $details,
        ]);
    }
}