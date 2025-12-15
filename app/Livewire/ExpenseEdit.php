<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Livewire\Attributes\Title;
use Livewire\Attributes\Rule;

#[Title('Edit Pengeluaran')]
class ExpenseEdit extends Component
{
    public Expense $expense;

    #[Rule('required|exists:expense_categories,id')]
    public $expense_category_id;

    #[Rule('required|numeric|min:0')]
    public $amount;

    #[Rule('required|string')]
    public $description;

    #[Rule('required|date')]
    public $expense_date;

    public function mount(Expense $expense)
    {
        $this->expense = $expense;
        $this->expense_category_id = $expense->expense_category_id;
        $this->amount = $expense->amount;
        $this->description = $expense->description;
        $this->expense_date = $expense->expense_date->format('Y-m-d');
    }

    public function save()
    {
        $this->validate();

        $this->expense->update([
            'expense_category_id' => $this->expense_category_id,
            'amount' => $this->amount,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
        ]);

        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil diperbarui!');
    }

    public function delete()
    {
        $this->expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Pengeluaran berhasil dihapus!');
    }

    public function render()
    {
        return view('livewire.expense-edit', [
            'categories' => ExpenseCategory::all()
        ]);
    }
}
