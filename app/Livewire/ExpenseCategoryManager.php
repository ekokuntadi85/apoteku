<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ExpenseCategory;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

#[Title('Kategori Pengeluaran')]
class ExpenseCategoryManager extends Component
{
    public $search = '';
    
    public $showModal = false;
    public $editMode = false;
    public $categoryId;

    #[Rule('required|string|max:255')]
    public $name;

    #[Rule('nullable|string')]
    public $description;

    public function create()
    {
        $this->reset(['name', 'description', 'categoryId', 'editMode']);
        $this->showModal = true;
    }

    public function edit(ExpenseCategory $category)
    {
        $this->editMode = true;
        $this->categoryId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editMode) {
            $category = ExpenseCategory::find($this->categoryId);
            $category->update([
                'name' => $this->name,
                'description' => $this->description,
            ]);
        } else {
            ExpenseCategory::create([
                'name' => $this->name,
                'description' => $this->description,
            ]);
        }

        $this->showModal = false;
        $this->reset(['name', 'description']);
    }

    public function delete(ExpenseCategory $category)
    {
        if ($category->expenses()->count() > 0) {
            $this->addError('delete', 'Kategori ini tidak dapat dihapus karena sedang digunakan dalam transaksi pengeluaran.');
            return;
        }
        
        $category->delete();
    }

    public function render()
    {
        $categories = ExpenseCategory::where('name', 'like', '%' . $this->search . '%')
            ->get();

        return view('livewire.expense-category-manager', compact('categories'));
    }
}
