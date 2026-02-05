<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

#[Title('Tambah Pengeluaran')]
class ExpenseCreate extends Component
{
    #[Rule('required|exists:expense_categories,id')]
    public $expense_category_id;

    #[Rule('required|numeric|min:0')]
    public $amount;

    #[Rule('required|string')]
    public $description;

    #[Rule('required|date')]
    public $expense_date;

    public function mount()
    {
        $this->expense_date = date('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        Expense::create([
            'expense_category_id' => $this->expense_category_id,
            'amount' => $this->amount,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil ditambahkan!');
    }

    public function render()
    {
        return view('livewire.expense-create', [
            'categories' => ExpenseCategory::all()
        ]);
    }
}
