<?php

namespace App\Livewire\FinancialReports;

use Livewire\Component;
use App\Models\Transaction;
use App\Models\Expense;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Laporan Laba Rugi')]
class IncomeStatement extends Component
{
    public $startDate;
    public $endDate;
    public $period = 'this_month';

    public function mount()
    {
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedPeriod($value)
    {
        switch ($value) {
            case 'this_month':
                $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
                break;
            case 'last_month':
                $this->startDate = Carbon::now()->subMonth()->startOfMonth()->format('Y-m-d');
                $this->endDate = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
                break;
            case 'this_year':
                $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
                $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
                break;
        }
    }

    public function render()
    {
        // Use Journal Entries as single source of truth
        // This ensures consistency with other financial reports
        
        // 1. Revenue (Account 401 - Sales Revenue)
        $revenueAccount = \App\Models\Account::where('code', '401')->first();
        $revenue = 0;
        
        if ($revenueAccount) {
            $revenue = \App\Models\JournalDetail::where('account_id', $revenueAccount->id)
                ->whereHas('journalEntry', function($q) {
                    $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
                })
                ->sum('credit'); // Revenue is credit
        }

        // 2. Cost of Goods Sold (Account 501 - COGS)
        $cogsAccount = \App\Models\Account::where('code', '501')->first();
        $cogs = 0;
        
        if ($cogsAccount) {
            $cogs = \App\Models\JournalDetail::where('account_id', $cogsAccount->id)
                ->whereHas('journalEntry', function($q) {
                    $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
                })
                ->sum('debit'); // COGS is debit (expense)
        }

        // 3. Gross Profit
        $grossProfit = $revenue - $cogs;

        // 4. Operating Expenses (Accounts 502-507)
        $expenseAccounts = \App\Models\Account::whereBetween('code', ['502', '507'])->get();
        
        $expensesByCategory = [];
        $totalExpenses = 0;
        
        foreach ($expenseAccounts as $account) {
            $amount = \App\Models\JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function($q) {
                    $q->whereBetween('transaction_date', [$this->startDate, $this->endDate]);
                })
                ->sum('debit'); // Expenses are debit
            
            if ($amount > 0) {
                $expensesByCategory[$account->name] = $amount;
                $totalExpenses += $amount;
            }
        }

        // 5. Net Profit
        $netProfit = $grossProfit - $totalExpenses;

        return view('livewire.financial-reports.income-statement', [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'grossProfit' => $grossProfit,
            'totalExpenses' => $totalExpenses,
            'expensesByCategory' => $expensesByCategory,
            'netProfit' => $netProfit
        ]);
    }
}
