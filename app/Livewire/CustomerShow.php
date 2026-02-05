<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;

use Livewire\Attributes\Title;

#[Title('Lihat Pelanggan')]
class CustomerShow extends Component
{
    public $customer;

    public function mount(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function deleteCustomer()
    {
        $this->customer->delete();
        session()->flash('message', 'Customer berhasil dihapus.');
        return redirect()->route('customers.index');
    }

    public function render()
    {
        return view('livewire.customer-show');
    }
}