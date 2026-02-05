<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\WithPagination;
use Livewire\Attributes\Title;

#[Title('Manajemen Pengeluaran')]
class ExpenseManager extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryId = 'all';
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $expenses = Expense::with(['category', 'user'])
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->where(function($query) {
                $query->where('description', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->when($this->categoryId !== 'all', function($query) {
                $query->where('expense_category_id', $this->categoryId);
            })
            ->latest('expense_date')
            ->paginate(10);

        $categories = ExpenseCategory::all();

        return view('livewire.expense-manager', compact('expenses', 'categories'));
    }
}
