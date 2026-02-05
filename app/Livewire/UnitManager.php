<?php

namespace App\Livewire;

use App\Models\Unit;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Manajemen Satuan')]
class UnitManager extends Component
{
    use WithPagination;

    public $name;
    public $short_name;
    public $unitId;
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
                Rule::unique('units', 'name')->ignore($this->unitId),
            ],
            'short_name' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama unit wajib diisi.',
        'name.unique' => 'Nama unit sudah ada.',
    ];

    public function render()
    {
        $units = Unit::query()
                    ->when($this->search, function ($query) {
                        $query->where('name', 'like', '%' . $this->search . '%')
                              ->orWhere('short_name', 'like', '%' . $this->search . '%');
                    })
                    ->latest()
                    ->paginate(10);
        return view('livewire.unit-manager', compact('units'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $unit = Unit::find($this->unitId);
            $unit->update([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);
            session()->flash('message', 'Unit berhasil diperbarui.');
        } else {
            Unit::create([
                'name' => $this->name,
                'short_name' => $this->short_name,
            ]);
            session()->flash('message', 'Unit berhasil ditambahkan.');
        }

        $this->closeModal(); // Close modal and reset form
    }

    public function createUnit() // Method to open modal for new unit
    {
        $this->resetInput(); // Clear form fields
        $this->showModal = true; // Open the modal
    }

    public function edit($id)
    {
        $unit = Unit::findOrFail($id);
        $this->unitId = $unit->id;
        $this->name = $unit->name;
        $this->short_name = $unit->short_name;
        $this->isUpdateMode = true;
        $this->showModal = true; // Open modal for edit
    }

    public function delete($id)
    {
        // Add logic to check for related products before deleting
        $unit = Unit::find($id);
        if ($unit->products()->count() > 0) {
            session()->flash('error', 'Unit tidak dapat dihapus karena memiliki produk terkait.');
            return;
        }

        if ($unit->delete()) {
            session()->flash('message', 'Unit berhasil dihapus.');
        }
    }

    public function resetInput()
    {
        $this->name = '';
        $this->short_name = '';
        $this->unitId = null;
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