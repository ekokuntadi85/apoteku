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
        // 1. Revenue (Pendapatan)
        // Only paid transactions or all? Accrual basis usually counts all.
        // Let's assume Accrual (all valid invoices)
        $transactions = Transaction::with(['transactionDetails.transactionDetailBatches.productBatch'])
            ->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59'])
            ->where('payment_status', '!=', 'cancelled') // Exclude cancelled
            ->get();

        $revenue = $transactions->sum('total_price');

        // 2. Cost of Goods Sold (HPP)
        $cogs = 0;
        foreach ($transactions as $transaction) {
            foreach ($transaction->transactionDetails as $detail) {
                // Try to get cost from batches
                $batchCost = 0;
                $batchQuantity = 0;
                
                foreach ($detail->transactionDetailBatches as $batchDetail) {
                    $purchasePrice = $batchDetail->productBatch->purchase_price ?? 0;
                    $batchCost += $batchDetail->quantity * $purchasePrice;
                    $batchQuantity += $batchDetail->quantity;
                }
                
                // If batches cover the full quantity, use batch cost
                if ($batchQuantity >= $detail->quantity) {
                    $cogs += $batchCost;
                } else {
                    // Fallback: Use latest batch purchase price for missing quantity
                    $remainingQty = $detail->quantity - $batchQuantity;
                    
                    $latestBatch = $detail->product->productBatches()->latest('created_at')->first();
                    $fallbackPrice = $latestBatch ? $latestBatch->purchase_price : 0;
                    
                    $cogs += $batchCost + ($remainingQty * $fallbackPrice);
                }
            }
        }

        // 3. Gross Profit
        $grossProfit = $revenue - $cogs;

        // 4. Expenses
        $expenses = Expense::with('category')
            ->whereBetween('expense_date', [$this->startDate, $this->endDate])
            ->get();
        
        $totalExpenses = $expenses->sum('amount');
        
        $expensesByCategory = $expenses->groupBy('category.name')
            ->map(function ($row) {
                return $row->sum('amount');
            });

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
