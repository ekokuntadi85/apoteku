<?php

namespace App\Livewire\FinancialReports;

use Livewire\Component;
use App\Models\Account;
use App\Models\JournalDetail;
use Carbon\Carbon;
use Livewire\Attributes\Title;

#[Title('Laporan Neraca')]
class BalanceSheet extends Component
{
    public $endDate;

    public function mount()
    {
        $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    private function getAccountBalance($type, $endDate)
    {
        $accounts = Account::where('type', $type)->where('is_active', true)->get();
        $data = [];
        $total = 0;

        foreach ($accounts as $account) {
            $debit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('debit');

            $credit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('credit');

            // Asset: Debit - Credit
            // Liability & Equity: Credit - Debit
            $balance = ($type === 'asset') ? ($debit - $credit) : ($credit - $debit);

            if ($balance != 0) {
                $data[] = [
                    'code' => $account->code,
                    'name' => $account->name,
                    'balance' => $balance
                ];
                $total += $balance;
            }
        }

        return ['accounts' => $data, 'total' => $total];
    }

    private function calculateNetProfit($endDate)
    {
        // Revenue (Credit - Debit)
        $revenueAccounts = Account::where('type', 'revenue')->get();
        $totalRevenue = 0;
        foreach ($revenueAccounts as $account) {
            $debit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('debit');
            $credit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('credit');
            $totalRevenue += ($credit - $debit);
        }

        // Expenses (Debit - Credit)
        $expenseAccounts = Account::where('type', 'expense')->get();
        $totalExpenses = 0;
        foreach ($expenseAccounts as $account) {
            $debit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('debit');
            $credit = JournalDetail::where('account_id', $account->id)
                ->whereHas('journalEntry', function ($query) use ($endDate) {
                    $query->where('transaction_date', '<=', $endDate);
                })->sum('credit');
            $totalExpenses += ($debit - $credit);
        }
        
        // Note: In a real advanced system, COGS is also an account type. 
        // For simplicity here, assuming COGS is tracked via Journal Entries if implemented, 
        // otherwise it needs to be calculated dynamically like in IncomeStatement.
        // HOWEVER, for Balance Sheet to balance, ALL movements must be in Journal Entries.
        // If COGS is not journalized, Retained Earnings will be wrong and Balance Sheet won't balance.
        // For now, let's assume the IncomeStatement logic is the source of truth for "Current Period Profit".
        
        // Actually, to be consistent with the simple Journal Entry system we just made:
        // We will calculate simplistic Net Profit based on Journal Entries only.
        // If the user hasn't journalized Sales/Expenses yet, this will be 0.
        // BUT, the IncomeStatement component calculates it dynamically from Transactions/Expenses tables.
        // To make the Balance Sheet match the "Business Reality", we should probably pull the Net Profit 
        // from the same logic as IncomeStatement, OR ensure all those transactions write to Journal.
        
        // CURRENT STATE: Transactions/Expenses DO NOT write to Journal yet.
        // So Journal-based Balance Sheet will ONLY show manual entries (Modal Awal).
        // This is exactly what the user wants to see: their "Modal Awal".
        
        return $totalRevenue - $totalExpenses;
    }

    public function render()
    {
        $assets = $this->getAccountBalance('asset', $this->endDate);
        $liabilities = $this->getAccountBalance('liability', $this->endDate);
        $equity = $this->getAccountBalance('equity', $this->endDate);

        // Calculate Current Learning (Laba Periode Berjalan)
        // Since we don't have auto-journal yet, this might be 0, which is fine for "Modal Awal" check.
        $currentEarnings = $this->calculateNetProfit($this->endDate);
        
        // Add Current Earnings to Equity to balance the sheet (conceptually)
        // However, since we haven't implemented auto-journaling for Sales/Expenses yet, 
        // the Asset side (Cash from Sales) is also missing from the Journal.
        // So keeping it strictly based on Journal Entries is the most "Honest" view of the Ledger.
        
        return view('livewire.financial-reports.balance-sheet', [
            'assets' => $assets['accounts'],
            'totalAssets' => $assets['total'],
            'liabilities' => $liabilities['accounts'],
            'totalLiabilities' => $liabilities['total'],
            'equity' => $equity['accounts'],
            'totalEquity' => $equity['total'],
            'currentEarnings' => $currentEarnings
        ]);
    }
}
