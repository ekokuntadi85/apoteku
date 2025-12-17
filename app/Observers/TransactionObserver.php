<?php

namespace App\Observers;

use App\Models\Transaction;
use App\Services\JournalService;
use App\Models\Account;

class TransactionObserver
{
    use JournalCleanupTrait;
    
    public function created(Transaction $transaction)
    {
        $this->recordJournal($transaction);
    }

    public function updated(Transaction $transaction)
    {
        \Log::info('TransactionObserver::updated triggered', [
            'invoice' => $transaction->invoice_number,
            'isDirty' => $transaction->isDirty('payment_status'),
            'payment_status' => $transaction->payment_status
        ]);
        
        // 1. Handle Payment Status Change (Unpaid -> Paid)
        // This usually happens for Credit Sales (Piutang) being paid off later.
        if ($transaction->isDirty('payment_status') && $transaction->payment_status === 'paid') {
            
            // Check if we already have a payment journal for this to avoid duplicates
            // Ref: PAY-INV-{invoice_number}
            $payRef = 'PAY-INV-' . $transaction->invoice_number;
            
            \Log::info('Creating payment journal', ['reference' => $payRef]);
            
            if (!\App\Models\JournalEntry::where('reference_number', $payRef)->exists()) {
                 JournalService::createEntry(
                    now()->format('Y-m-d'), // Use current date for payment date
                    $payRef,
                    'Pelunasan Piutang #' . $transaction->invoice_number,
                    [
                        ['account_code' => '101', 'amount' => $transaction->total_price] // Debit Cash
                    ],
                    [
                        ['account_code' => '103', 'amount' => $transaction->total_price] // Credit Accounts Receivable
                    ]
                );
                
                \Log::info('Payment journal created successfully', ['reference' => $payRef]);
            } else {
                \Log::warning('Payment journal already exists', ['reference' => $payRef]);
            }
        }
    }

    protected function recordJournal(Transaction $transaction)
    {
        // 1. Record Revenue (Sales)
        // Debit: Cash (101) or AR (103)
        // Credit: Sales Revenue (401)

        $debitAccountCode = '101'; // Default Cash
        if ($transaction->payment_status !== 'paid') {
            $debitAccountCode = '103'; // Piutang Usaha
        }

        JournalService::createEntry(
            $transaction->created_at->format('Y-m-d'),
            'INV-' . $transaction->invoice_number,
            'Penjualan #' . $transaction->invoice_number,
            [
                ['account_code' => $debitAccountCode, 'amount' => $transaction->total_price]
            ],
            [
                ['account_code' => '401', 'amount' => $transaction->total_price]
            ]
        );

        // 2. Record COGS (HPP) => This is tricky because StockService might not have finished running yet if TransactionDetail is created AFTER Transaction.
        // BUT Transaction usually saves parent first, then children.
        // TransactionDetailObserver runs on "created".
        // IF we are in TransactionObserver "created", the child details MIGHT NOT exist yet.
        // This is a common Laravel "gotcha".
        
        // SOLUTION: The COGS journal should probably be triggered by the TransactionDetailObserver OR we need to wait until the transaction is fully built.
        // But TransactionDetailObserver fires one by one. We don't want 10 journal entries for 1 transaction (10 items). We want 1 aggregated entry.
        
        // ALTERNATIVE: Use a "job" that runs slightly later, or hook into a service method like "finalizeTransaction".
        // HOWEVER, based on current architecture, `Transaction` is created, then details are added.
        // So `TransactionObserver::created` will see 0 details.
        
        // Let's look at `TransactionDetailObserver`. We can record COGS per Item there?
        // It's a bit "spammy" for the ledger (many lines), but accurate.
        // Better: Hook into `TransactionDetail` creation and update a "Pending COGS" or just journalize it per item.
        // Journalizing per item is actually fine for granular data, just verbose.
        // OR: Sum it up at the end?
        // Since we want "Automatic", let's modify `TransactionDetailObserver` to also record COGS.
    }
    
    /**
     * Handle the Transaction "deleting" event.
     * Clean up all related journal entries when transaction is deleted.
     */
    public function deleting(Transaction $transaction)
    {
        \Log::info('TransactionObserver::deleting triggered', [
            'invoice' => $transaction->invoice_number
        ]);
        
        // Delete revenue journal (INV-{invoice_number})
        $this->deleteRelatedJournals('INV-', $transaction->invoice_number);
        
        // Delete payment journal if exists (PAY-INV-{invoice_number})
        $this->deleteRelatedJournals('PAY-INV-', $transaction->invoice_number);
        
        // Delete all COGS journals (COGS-{invoice_number}-*)
        $this->deleteRelatedJournals('COGS-', $transaction->invoice_number);
        
        \Log::info('All related journal entries deleted for transaction', [
            'invoice' => $transaction->invoice_number
        ]);
    }
}
