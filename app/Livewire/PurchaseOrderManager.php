<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use Livewire\WithPagination;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;

#[Title('Manajemen Surat Pesanan')]
class PurchaseOrderManager extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all';

    #[On('purchaseOrderUpdated')]
    public function refreshPurchaseOrders()
    {
        $this->resetPage();
    }

    public function render()
    {
        $purchaseOrders = PurchaseOrder::with(['supplier'])
                                ->where(function ($query) {
                                    $query->where('po_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('supplier', function ($query) {
                                              $query->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->when($this->filterStatus !== 'all', function ($query) {
                                    $query->where('status', $this->filterStatus);
                                })
                                ->latest()
                                ->paginate(10);

        return view('livewire.purchase-order-manager', compact('purchaseOrders'));
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
