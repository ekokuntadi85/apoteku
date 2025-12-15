<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\JournalService;

class PurchaseObserver
{
    public function created(Purchase $purchase)
    {
        // 1. Record Inventory Increase
        // Debit: Inventory (104)
        // Credit: Accounts Payable (201)
        
        JournalService::createEntry(
            $purchase->purchase_date ?? now()->format('Y-m-d'),
            'PUR-' . $purchase->invoice_number,
            'Pembelian Dari ' . optional($purchase->supplier)->name,
            [
                ['account_code' => '104', 'amount' => $purchase->total_price] // Debit Inventory
            ],
            [
                ['account_code' => '201', 'amount' => $purchase->total_price] // Credit Accounts Payable
            ]
        );

        // 2. If paid immediately during creation
        if ($purchase->payment_status === 'paid') {
            JournalService::createEntry(
                $purchase->purchase_date ?? now()->format('Y-m-d'),
                'PAY-PUR-' . $purchase->invoice_number,
                'Pembayaran Lunas Invoice ' . $purchase->invoice_number,
                [
                    ['account_code' => '201', 'amount' => $purchase->total_price] // Debit AP
                ],
                [
                    ['account_code' => '101', 'amount' => $purchase->total_price] // Credit Cash
                ]
            );
        }
    }
    
    public function updated(Purchase $purchase)
    {
        \Log::info('PurchaseObserver::updated triggered', [
            'invoice' => $purchase->invoice_number,
            'isDirty' => $purchase->isDirty('payment_status'),
            'payment_status' => $purchase->payment_status
        ]);
        
        // Handle Payment Status Change (Unpaid -> Paid)
        // This happens when user pays off the debt later
        if ($purchase->isDirty('payment_status') && $purchase->payment_status === 'paid') {
            
            // Check if payment journal already exists
            $payRef = 'PAY-PUR-' . $purchase->invoice_number;
            
            \Log::info('Creating payment journal for purchase', ['reference' => $payRef]);
            
            if (!\App\Models\JournalEntry::where('reference_number', $payRef)->exists()) {
                JournalService::createEntry(
                    now()->format('Y-m-d'), // Use current date for payment date
                    $payRef,
                    'Pelunasan Hutang Pembelian #' . $purchase->invoice_number,
                    [
                        ['account_code' => '201', 'amount' => $purchase->total_price] // Debit Accounts Payable (reduce debt)
                    ],
                    [
                        ['account_code' => '101', 'amount' => $purchase->total_price] // Credit Cash (money out)
                    ]
                );
                
                \Log::info('Payment journal created successfully', ['reference' => $payRef]);
            } else {
                \Log::warning('Payment journal already exists', ['reference' => $payRef]);
            }
        }
    }
}
