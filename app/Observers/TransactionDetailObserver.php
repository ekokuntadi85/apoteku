<?php

namespace App\Observers;

use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\TransactionDetailBatch;
use App\Services\StockService;

class TransactionDetailObserver
{
    /**
     * Handle the TransactionDetail "created" event.
     */
    public function created(TransactionDetail $detail): void
    {
        (new StockService())->decrementStock($detail);

        // IMPORTANT: Refresh to ensure we have the latest transactionDetailBatches
        // This prevents race condition where batches might not be loaded yet
        $detail->refresh();

        // Record COGS (HPP) Journal
        $cogs = 0;
        $batches = $detail->transactionDetailBatches()->with('productBatch')->get();
        
        // Check if we have batch data
        if ($batches->isEmpty()) {
            \Log::warning("No batch data for TransactionDetail #{$detail->id}. COGS will be 0.", [
                'transaction_id' => $detail->transaction_id,
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity
            ]);
            return; // Skip COGS journal if no batch data
        }
        
        // Calculate COGS from batches with null safety
        foreach ($batches as $batchPivot) {
            if (!$batchPivot->productBatch) {
                \Log::error("ProductBatch is null for TransactionDetailBatch", [
                    'batch_pivot_id' => $batchPivot->id,
                    'transaction_detail_id' => $detail->id
                ]);
                continue;
            }
            
            $cogs += $batchPivot->quantity * $batchPivot->productBatch->purchase_price;
        }

        // Only create journal if COGS > 0
        if ($cogs > 0) {
            try {
                \App\Services\JournalService::createEntry(
                    $detail->transaction->created_at->format('Y-m-d'),
                    'COGS-' . $detail->transaction->invoice_number . '-' . $detail->id,
                    'HPP ' . $detail->product->name . ' (' . $detail->quantity . ' ' . $detail->productUnit->name . ')',
                    [
                        ['account_code' => '501', 'amount' => $cogs] // Debit HPP
                    ],
                    [
                        ['account_code' => '104', 'amount' => $cogs] // Credit Inventory
                    ]
                );
                
                \Log::info("COGS journal created successfully", [
                    'transaction_detail_id' => $detail->id,
                    'cogs' => $cogs
                ]);
            } catch (\Exception $e) {
                \Log::error("Failed to create COGS journal", [
                    'transaction_detail_id' => $detail->id,
                    'error' => $e->getMessage()
                ]);
                // Don't throw - we don't want to fail the transaction just because journal failed
            }
        } else {
            \Log::warning("COGS is 0 for TransactionDetail #{$detail->id}", [
                'batches_count' => $batches->count()
            ]);
        }
    }

    /**
     * Handle the TransactionDetail "deleting" event.
     */
    public function deleting(TransactionDetail $detail): void
    {
        (new StockService())->incrementStock($detail);
    }

    /**
     * Handle the TransactionDetail "deleted" event.
     */
    public function deleted(TransactionDetail $detail): void
    {
        // Logic moved to deleting() to prevent race condition with cascading deletes.
    }
}
