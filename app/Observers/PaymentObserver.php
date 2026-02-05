<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\JournalService;

class PaymentObserver
{
    use JournalCleanupTrait;
    
    /**
     * Handle the Payment "created" event.
     */
    public function created(Payment $payment): void
    {
        $transaction = $payment->transaction;
        $ref = 'PAY-' . $transaction->invoice_number . '-' . $payment->id;
        
        \Log::info('PaymentObserver::created triggered', [
            'payment_id' => $payment->id,
            'transaction_id' => $transaction->id,
            'amount' => $payment->amount
        ]);
        
        // Create journal entry for payment
        JournalService::createEntry(
            $payment->payment_date->format('Y-m-d'),
            $ref,
            'Pembayaran ' . $transaction->invoice_number,
            [
                ['account_code' => '101', 'amount' => $payment->amount] // Debit Cash
            ],
            [
                ['account_code' => '103', 'amount' => $payment->amount] // Credit Accounts Receivable
            ]
        );
        
        // Update transaction amount_paid and status
        $transaction->amount_paid += $payment->amount;
        
        if ($transaction->amount_paid >= $transaction->grand_total) {
            $transaction->payment_status = 'paid';
            $transaction->paid_at = now();
        } elseif ($transaction->amount_paid > 0) {
            $transaction->payment_status = 'partial';
        }
        
        $transaction->saveQuietly(); // Use saveQuietly to prevent triggering TransactionObserver::updated
        
        \Log::info('Payment processed successfully', [
            'payment_id' => $payment->id,
            'new_amount_paid' => $transaction->amount_paid,
            'new_status' => $transaction->payment_status
        ]);
    }

    /**
     * Handle the Payment "deleted" event.
     */
    public function deleted(Payment $payment): void
    {
        \Log::info('PaymentObserver::deleted triggered', [
            'payment_id' => $payment->id
        ]);
        
        // Delete payment journal
        $this->deleteRelatedJournals('PAY-', $payment->transaction->invoice_number . '-' . $payment->id);
        
        // Recalculate transaction amount_paid and status
        $transaction = $payment->transaction;
        $transaction->amount_paid = $transaction->payments()->sum('amount');
        
        if ($transaction->amount_paid >= $transaction->grand_total) {
            $transaction->payment_status = 'paid';
            $transaction->paid_at = now();
        } elseif ($transaction->amount_paid > 0) {
            $transaction->payment_status = 'partial';
            $transaction->paid_at = null;
        } else {
            $transaction->payment_status = 'unpaid';
            $transaction->paid_at = null;
        }
        
        $transaction->saveQuietly();
    }
}
