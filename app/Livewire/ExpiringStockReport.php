<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ProductBatch;
use Carbon\Carbon;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Laporan Stok Kedaluwarsa')]
class ExpiringStockReport extends Component
{
    use WithPagination;

    public $expiry_threshold_months = 2;
    // public $search = ''; // Removed

    public function render()
    {
        $thresholdDate = Carbon::now()->addMonths((int)$this->expiry_threshold_months)->endOfDay();

        $productBatches = ProductBatch::with(['product', 'purchase.supplier'])
                                    ->where('stock', '>', 0)
                                    ->where('expiration_date', '<=', $thresholdDate)
                                    // ->where(function ($query) { // Removed search logic
                                    //     $query->whereHas('product', function ($query) {
                                    //         $query->where('name', 'like', '%' . $this->search . '%');
                                    //     })
                                    //     ->orWhereHas('purchase.supplier', function ($query) {
                                    //         $query->where('name', 'like', '%' . $this->search . '%');
                                    //     })
                                    //     ->orWhere('batch_number', 'like', '%' . $this->search . '%');
                                    // })
                                    ->orderBy('expiration_date', 'asc')
                                    ->paginate(10);

        return view('livewire.expiring-stock-report', compact('productBatches'));
    }

    // public function updatingSearch() // Removed
    // {
    //     $this->resetPage();
    // }

    public function updatingExpiryThresholdMonths()
    {
        $this->resetPage();
    }
}