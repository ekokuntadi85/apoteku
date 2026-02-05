<?php

namespace App\Livewire;

use App\Models\Customer;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Manajemen Pelanggan')]
class CustomerManager extends Component
{
    use WithPagination;

    public $name;
    public $phone;
    public $address;
    public $is_member = false;
    public $customerId;
    public $isUpdateMode = false;
    public $showModal = false; // New property for modal visibility
    public $search = ''; // Added for search functionality

    protected function rules()
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customers', 'name')->ignore($this->customerId),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_member' => 'boolean',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama pelanggan wajib diisi.',
        'name.unique' => 'Nama pelanggan sudah ada.',
    ];

    public function render()
    {
        $customers = Customer::query()
                            ->when($this->search, function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                                      ->orWhere('address', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->paginate(10);
        return view('livewire.customer-manager', compact('customers'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $customer = Customer::find($this->customerId);
            $customer->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'is_member' => $this->is_member,
            ]);
            session()->flash('message', 'Pelanggan berhasil diperbarui.');
        } else {
            Customer::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
                'is_member' => $this->is_member,
            ]);
            session()->flash('message', 'Pelanggan berhasil ditambahkan.');
        }

        $this->closeModal(); // Close modal and reset form
    }

    public function createCustomer() // Method to open modal for new customer
    {
        $this->resetInput(); // Clear form fields
        $this->showModal = true; // Open the modal
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        $this->customerId = $customer->id;
        $this->name = $customer->name;
        $this->phone = $customer->phone;
        $this->address = $customer->address;
        $this->is_member = $customer->is_member;
        $this->isUpdateMode = true;
        $this->showModal = true; // Open modal for edit
    }

    public function delete($id)
    {
        // Add logic to check for related transactions before deleting
        $customer = Customer::find($id);
        if ($customer->transactions()->count() > 0) {
            session()->flash('error', 'Pelanggan tidak dapat dihapus karena memiliki riwayat transaksi terkait.');
            return;
        }

        if ($customer->delete()) {
            session()->flash('message', 'Pelanggan berhasil dihapus.');
        }
    }

    public function resetInput()
    {
        $this->name = '';
        $this->phone = '';
        $this->address = '';
        $this->is_member = false;
        $this->customerId = null;
        $this->isUpdateMode = false;
        $this->resetErrorBag(); // Clear validation errors
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetInput(); // Reset form when closing modal
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}