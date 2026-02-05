<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Manajemen Kategori')]
class CategoryManager extends Component
{
    use WithPagination;

    public $name;
    public $description;
    public $categoryId;
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
                Rule::unique('categories', 'name')->ignore($this->categoryId),
            ],
            'description' => 'nullable|string',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama kategori wajib diisi.',
        'name.unique' => 'Nama kategori sudah ada.',
    ];

    public function render()
    {
        $categories = Category::query()
                            ->when($this->search, function ($query) {
                                $query->where('name', 'like', '%' . $this->search . '%')
                                      ->orWhere('description', 'like', '%' . $this->search . '%');
                            })
                            ->latest()
                            ->paginate(10);
        return view('livewire.category-manager', compact('categories'));
    }

    public function save()
    {
        $this->validate();

        if ($this->isUpdateMode) {
            $category = Category::find($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Kategori berhasil diperbarui.');
        } else {
            Category::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
            session()->flash('message', 'Kategori berhasil ditambahkan.');
        }

        $this->closeModal(); // Close modal and reset form
    }

    public function createCategory() // Method to open modal for new category
    {
        $this->resetInput(); // Clear form fields
        $this->showModal = true; // Open the modal
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->isUpdateMode = true;
        $this->showModal = true; // Open modal for edit
    }

    public function delete($id)
    {
        // Log::info('CategoryManager: Delete method called for category ID: ' . $id);
        // Add logic to check for related products before deleting
        $category = Category::find($id);
        if ($category->products()->count() > 0) {
            session()->flash('error', 'Kategori tidak dapat dihapus karena memiliki produk terkait.');
            return;
        }

        if ($category->delete()) {
            session()->flash('message', 'Kategori berhasil dihapus.');
        }
    }

    public function resetInput()
    {
        $this->name = '';
        $this->description = '';
        $this->categoryId = null;
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
