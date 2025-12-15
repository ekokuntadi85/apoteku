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

        // Record COGS (HPP) Journal
        $cogs = 0;
        foreach ($detail->transactionDetailBatches()->with('productBatch')->get() as $batchPivot) {
            $cogs += $batchPivot->quantity * $batchPivot->productBatch->purchase_price;
        }

        if ($cogs > 0) {
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
