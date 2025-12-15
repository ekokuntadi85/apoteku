<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\JournalEntry;
use App\Services\JournalService;

class SyncHistoricalJournals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'finance:sync-historical-journals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate journal entries for existing Transactions, Purchases, and Expenses';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting historical journal synchronization...');

        $this->syncPurchases();
        $this->syncTransactions();
        $this->syncExpenses();

        $this->info('Synchronization complete!');
    }

    private function syncPurchases()
    {
        $this->info('Syncing Purchases...');
        $purchases = Purchase::with('supplier')->get();
        $bar = $this->output->createProgressBar(count($purchases));
        $bar->start();

        foreach ($purchases as $purchase) {
            // 1. Check if Purchase Journal exists
            $ref = 'PUR-' . $purchase->invoice_number;
            if (!JournalEntry::where('reference_number', $ref)->exists()) {
                JournalService::createEntry(
                    $purchase->purchase_date ?? $purchase->created_at->format('Y-m-d'),
                    $ref,
                    'Pembelian Dari ' . optional($purchase->supplier)->name . ' (Sync)',
                    [
                        ['account_code' => '104', 'amount' => $purchase->total_price] // Debit Inventory
                    ],
                    [
                        ['account_code' => '201', 'amount' => $purchase->total_price] // Credit Expenses Payable
                    ]
                );
            }

            // 2. Check if Payment Journal exists (if paid)
            if ($purchase->payment_status === 'paid') {
                $payRef = 'PAY-PUR-' . $purchase->invoice_number;
                if (!JournalEntry::where('reference_number', $payRef)->exists()) {
                    JournalService::createEntry(
                        $purchase->purchase_date ?? $purchase->created_at->format('Y-m-d'),
                        $payRef,
                        'Pembayaran Lunas Invoice ' . $purchase->invoice_number . ' (Sync)',
                        [
                            ['account_code' => '201', 'amount' => $purchase->total_price] // Debit AP
                        ],
                        [
                            ['account_code' => '101', 'amount' => $purchase->total_price] // Credit Cash
                        ]
                    );
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function syncTransactions()
    {
        $this->info('Syncing Transactions (Sales)...');
        $transactions = Transaction::with(['transactionDetails.product', 'transactionDetails.productUnit', 'transactionDetails.transactionDetailBatches.productBatch'])->get();
        $bar = $this->output->createProgressBar(count($transactions));
        $bar->start();

        foreach ($transactions as $transaction) {
            // 1. Revenue Journal
            $ref = 'INV-' . $transaction->invoice_number;
            if (!JournalEntry::where('reference_number', $ref)->exists()) {
                $debitAccountCode = '101'; // Default Cash
                if ($transaction->payment_status !== 'paid') {
                    $debitAccountCode = '103'; // Piutang Usaha
                }

                JournalService::createEntry(
                    $transaction->created_at->format('Y-m-d'),
                    $ref,
                    'Penjualan #' . $transaction->invoice_number . ' (Sync)',
                    [
                        ['account_code' => $debitAccountCode, 'amount' => $transaction->total_price]
                    ],
                    [
                        ['account_code' => '401', 'amount' => $transaction->total_price]
                    ]
                );
            }

            // 2. Payment Journal (if paid and was initially credit)
            // This handles cases where transaction was created as credit but later paid
            if ($transaction->payment_status === 'paid') {
                $payRef = 'PAY-INV-' . $transaction->invoice_number;
                
                // Check if this was a credit sale (has AR journal entry)
                $hasAREntry = JournalEntry::where('reference_number', $ref)
                    ->whereHas('journalDetails', function($q) {
                        $q->where('account_id', function($subq) {
                            $subq->select('id')->from('accounts')->where('code', '103')->limit(1);
                        })->where('debit', '>', 0);
                    })->exists();
                
                // Only create payment journal if it was a credit sale and payment journal doesn't exist
                if ($hasAREntry && !JournalEntry::where('reference_number', $payRef)->exists()) {
                    JournalService::createEntry(
                        $transaction->created_at->format('Y-m-d'), // Use transaction date as fallback
                        $payRef,
                        'Pelunasan Piutang #' . $transaction->invoice_number . ' (Sync)',
                        [
                            ['account_code' => '101', 'amount' => $transaction->total_price] // Debit Cash
                        ],
                        [
                            ['account_code' => '103', 'amount' => $transaction->total_price] // Credit AR
                        ]
                    );
                }
            }

            // 3. COGS Journals per Detail
            foreach ($transaction->transactionDetails as $detail) {
                // Calculate COGS from batch data
                $cogs = 0;
                
                if ($detail->transactionDetailBatches && $detail->transactionDetailBatches->count() > 0) {
                    // Use actual batch data
                    foreach ($detail->transactionDetailBatches as $batchPivot) {
                        if ($batchPivot->productBatch) {
                            $cogs += $batchPivot->quantity * $batchPivot->productBatch->purchase_price;
                        }
                    }
                } else {
                    // Fallback: Estimate COGS using product's current purchase price
                    // This is for old data that doesn't have batch tracking
                    if ($detail->product && $detail->product->purchase_price > 0) {
                        $cogs = $detail->quantity * $detail->product->purchase_price;
                        $this->warn("  ⚠️  Using fallback COGS for {$detail->product->name} (no batch data)");
                    }
                }
                
                if ($cogs > 0) {
                    $cogsRef = 'COGS-' . $transaction->invoice_number . '-' . $detail->id;
                    if (!JournalEntry::where('reference_number', $cogsRef)->exists()) {
                         JournalService::createEntry(
                             $transaction->created_at->format('Y-m-d'),
                             $cogsRef,
                             'HPP ' . optional($detail->product)->name . ' (Sync)',
                             [
                                 ['account_code' => '501', 'amount' => $cogs] // Debit HPP
                             ],
                             [
                                 ['account_code' => '104', 'amount' => $cogs] // Credit Inventory
                             ]
                         );
                    }
                } else {
                    $this->warn("  ⚠️  Skipping COGS for detail #{$detail->id} (COGS = 0)");
                }
            }

            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }

    private function syncExpenses()
    {
        $this->info('Syncing Expenses...');
        $expenses = Expense::with('category')->get();
        $bar = $this->output->createProgressBar(count($expenses));
        $bar->start();

        foreach ($expenses as $expense) {
            $ref = 'EXP-' . $expense->id;
            if (!JournalEntry::where('reference_number', $ref)->exists()) {
                
                $expenseAccount = '507'; 
                $categoryName = strtolower(optional($expense->category)->name ?? '');
                if (str_contains($categoryName, 'gaji')) $expenseAccount = '502';
                if (str_contains($categoryName, 'listrik') || str_contains($categoryName, 'air')) $expenseAccount = '503';
                if (str_contains($categoryName, 'sewa')) $expenseAccount = '504';
                if (str_contains($categoryName, 'perlengkapan')) $expenseAccount = '505';
                if (str_contains($categoryName, 'penyusutan')) $expenseAccount = '506';

                JournalService::createEntry(
                    $expense->expense_date ? $expense->expense_date->format('Y-m-d') : $expense->created_at->format('Y-m-d'),
                    $ref,
                    'Pengeluaran: ' . $expense->description . ' (Sync)',
                    [
                        ['account_code' => $expenseAccount, 'amount' => $expense->amount]
                    ],
                    [
                        ['account_code' => '101', 'amount' => $expense->amount] // Credit Cash
                    ]
                );
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
    }
}
