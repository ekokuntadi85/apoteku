<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Carbon\Carbon;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockCard extends Component
{
    use WithPagination;

    public $selectedProductId; // Changed from selectedProductBatchId
    public $selectedProductName; // To display selected product name
    public $month; // Filter by month
    public $year; // Filter by year

    public $startDate; // New property for start date of period
    public $endDate; // New property for end date of period

    public $searchProduct = ''; // Changed from searchProductBatch
    public $productResults = []; // Changed from productBatchResults

    protected $queryString = ['selectedProductId', 'month', 'year']; // Updated query string

    public function mount()
    {
        $this->month = Carbon::now()->month;
        $this->year = Carbon::now()->year;
        $this->updateDates(); // Initialize dates

        // If selectedProductId is present in query string, load product name
        if ($this->selectedProductId) {
            $product = Product::find($this->selectedProductId);
            if ($product) {
                $this->selectedProductName = $product->name;
            }
        }
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($this->searchProduct) >= 3) {
            $this->productResults = Product::withSum('productBatches as total_stock', 'stock')
                ->where('name', 'like', '%' . $value . '%')
                ->orWhere('sku', 'like', '%' . $value . '%')
                ->limit(10)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function selectProduct($productId)
    {
        $product = Product::find($productId);
        $this->selectedProductId = $productId;
        $this->selectedProductName = $product->name;
        $this->searchProduct = '';
        $this->productResults = [];
        $this->resetPage();
    }

    public function updateDates()
    {
        $this->startDate = Carbon::create($this->year, $this->month, 1)->startOfDay();
        $this->endDate = Carbon::create($this->year, $this->month)->endOfMonth()->endOfDay();
    }

    // Computed property for initial balance
    public function getInitialBalanceProperty()
    {
        if (!$this->selectedProductId) {
            return 0;
        }

        return StockMovement::whereHas('productBatch', function($query) {
                                $query->where('product_id', $this->selectedProductId);
                            })
                            ->where('created_at', '<=', $this->startDate->copy()->subSecond())
                            ->sum('quantity');
    }

    // Computed property for final balance
    public function getFinalBalanceProperty()
    {
        if (!$this->selectedProductId) {
            return 0;
        }

        return StockMovement::whereHas('productBatch', function($query) {
                                $query->where('product_id', $this->selectedProductId);
                            })
                            ->where('created_at', '<=', $this->endDate)
                            ->sum('quantity');
    }

    public function render()
    {
        // IMPORTANT: Update dates first to ensure they're fresh for this render cycle
        $this->updateDates();
        
        $query = StockMovement::with(['productBatch.product'])
                                ->whereHas('productBatch', function($query) {
                                    $query->where('product_id', $this->selectedProductId);
                                })
                                ->whereBetween('created_at', [$this->startDate, $this->endDate]) // Filter movements within period
                                ->orderBy('created_at', 'asc'); // ASC: oldest first, newest last

        $stockMovements = $query->paginate(10);

        // Calculate the balance at the START of the current page
        // This ensures the Saldo column shows the actual cumulative balance, not just per-page balance
        $balanceBeforeCurrentPage = $this->initialBalance;
        
        if ($stockMovements->count() > 0 && $stockMovements->currentPage() > 1) {
            // Get the first item on current page
            $firstItemOnPage = $stockMovements->first();
            
            // Sum all movements BEFORE this page (from start of period up to but not including first item on page)
            // Since we're using ASC order, we need movements with created_at < firstItemOnPage
            $balanceBeforeCurrentPage = $this->initialBalance + StockMovement::whereHas('productBatch', function($query) {
                                                $query->where('product_id', $this->selectedProductId);
                                            })
                                            ->whereBetween('created_at', [$this->startDate, $this->endDate])
                                            ->where('created_at', '<', $firstItemOnPage->created_at) // All movements BEFORE first item (ASC order)
                                            ->sum('quantity');
        }

        $years = range(Carbon::now()->year, Carbon::now()->year - 5); // Last 5 years
        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $months[$i] = Carbon::create(null, $i, 1)->format('F');
        }

        return view('livewire.stock-card', compact('years', 'months', 'stockMovements', 'balanceBeforeCurrentPage'));
    }

    public function updatingSelectedProductId()
    {
        $this->resetPage();
    }

    public function updatingMonth()
    {
        $this->resetPage();
        $this->updateDates();
    }

    public function updatingYear()
    {
        $this->resetPage();
        $this->updateDates();
    }
}
