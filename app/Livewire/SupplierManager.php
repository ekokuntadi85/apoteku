<?php

namespace App\Livewire;

use App\Models\Supplier;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Manajemen Supplier')]
class SupplierManager extends Component
{
    use WithPagination;

    public $name;
    public $phone;
    public $address;
    public $supplierId;
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
                Rule::unique('suppliers', 'name')->ignore($this->supplierId),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'name.unique' => 'Nama supplier sudah ada.',
    ];

    public function render()
    {
        $suppliers = Supplier::query()
                            ->when($this->search, function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('phone', 'like', '%' . $this->search . '%')
                                      ->orWhere('address', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->paginate(10);
        return view('livewire.supplier-manager', compact('suppliers'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $supplier = Supplier::find($this->supplierId);
            $supplier->update([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Supplier berhasil diperbarui.');
        } else {
            Supplier::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address,
            ]);
            session()->flash('message', 'Supplier berhasil ditambahkan.');
        }

        $this->closeModal(); // Close modal and reset form
    }

    public function createSupplier() // Method to open modal for new supplier
    {
        $this->resetInput(); // Clear form fields
        $this->showModal = true; // Open the modal
    }

    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->isUpdateMode = true;
        $this->showModal = true; // Open modal for edit
    }

    public function delete($id)
    {
        // Add logic to check for related purchases before deleting
        $supplier = Supplier::find($id);
        if ($supplier->purchases()->count() > 0) {
            session()->flash('error', 'Supplier tidak dapat dihapus karena memiliki riwayat pembelian terkait.');
            return;
        }

        if ($supplier->delete()) {
            session()->flash('message', 'Supplier berhasil dihapus.');
        }
    }

    public function resetInput()
    {
        $this->name = '';
        $this->phone = '';
        $this->address = '';
        $this->supplierId = null;
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