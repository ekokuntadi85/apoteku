<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;
use Livewire\WithPagination;
use Livewire\Attributes\On;

#[Title('Manajemen Pembelian')]
class PurchaseManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all';

    #[On('purchaseUpdated')]
    public function refreshPurchases()
    {
        $this->resetPage(); // Optional: Reset to first page after update
    }

    public function render()
    {
        $purchases = Purchase::with(['supplier'])
                                ->where(function ($query) {
                                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('supplier', function ($query) {
                                              $query->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->when($this->filterStatus !== 'all', function ($query) {
                                    $query->where('payment_status', $this->filterStatus);
                                })
                                ->latest()
                                ->paginate(5);

        return view('livewire.purchase-manager', compact('purchases'));
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }
}