<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Hutang Usaha')]
class AccountsPayable extends Component
{
    use WithPagination;

    public $search = '';
    public $filterStatus = 'all';

    public function render()
    {
        $purchases = Purchase::with('supplier')
                                ->where('payment_status', '!=', 'paid') // Default showing unpaid/partial
                                ->where(function ($query) {
                                    $query->where('invoice_number', 'like', '%' . $this->search . '%')
                                          ->orWhereHas('supplier', function ($q) {
                                              $q->where('name', 'like', '%' . $this->search . '%');
                                          });
                                })
                                ->when($this->filterStatus !== 'all', function ($query) {
                                    if ($this->filterStatus == 'unpaid_only') {
                                        $query->where('payment_status', 'pending');
                                    } elseif ($this->filterStatus == 'partial') {
                                        $query->where('payment_status', 'partial');
                                    }
                                })
                                ->latest()
                                ->paginate(10);

        return view('livewire.accounts-payable', compact('purchases'));
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}
