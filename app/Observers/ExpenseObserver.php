<?php

namespace App\Observers;

use App\Models\Expense;
use App\Services\JournalService;

class ExpenseObserver
{
    public function created(Expense $expense)
    {
        // Debit: Expense Account (507 - Beban Lain-lain for now, or mapped)
        // Credit: Cash (101)
        
        // Simple mapping based on category name could be done here, 
        // but for robustness we default to 507 or 501 etc.
        // Let's us 507 (Beban Lain-lain) as generic operational expense.
        
        $expenseAccount = '507'; 
        
        // Basic mapping attempt
        $categoryName = strtolower(optional($expense->category)->name ?? '');
        if (str_contains($categoryName, 'gaji')) $expenseAccount = '502'; // Beban Gaji
        if (str_contains($categoryName, 'listrik') || str_contains($categoryName, 'air')) $expenseAccount = '503';
        if (str_contains($categoryName, 'sewa')) $expenseAccount = '504';
        if (str_contains($categoryName, 'perlengkapan')) $expenseAccount = '505';

        JournalService::createEntry(
            $expense->expense_date ? $expense->expense_date->format('Y-m-d') : now()->format('Y-m-d'),
            'EXP-' . $expense->id,
            'Pengeluaran: ' . $expense->description,
            [
                ['account_code' => $expenseAccount, 'amount' => $expense->amount]
            ],
            [
                ['account_code' => '101', 'amount' => $expense->amount] // Credit Cash
            ]
        );
    }
}
