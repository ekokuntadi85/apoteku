<?php

namespace App\Observers;

use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class ProductBatchObserver
{
    /**
     * Handle the ProductBatch "created" event.
     */
    public function created(ProductBatch $productBatch): void
    {
        // Record initial stock movement for purchase
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'PB',
            'quantity' => $productBatch->stock,
            'remarks' => 'Pembelian awal',
        ]);
    }

    /**
     * Handle the ProductBatch "updated" event.
     */
    public function updated(ProductBatch $productBatch): void
    {
        // Removed the ADJ stock movement creation here.
        // Stock movements for sales (PJ) and opname (OP) will be handled explicitly in their respective modules.
    }

    /**
     * Handle the ProductBatch "deleted" event.
     */
    public function deleted(ProductBatch $productBatch): void
    {
        // Record stock movement for deletion (negative quantity)
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'DEL', // Deletion
            'quantity' => -$productBatch->stock, // Record current stock as negative
            'remarks' => 'Batch dihapus',
        ]);
    }

    /**
     * Handle the ProductBatch "restored" event.
     */
    public function restored(ProductBatch $productBatch): void
    {
        // Record stock movement for restoration
        StockMovement::create([
            'product_batch_id' => $productBatch->id,
            'type' => 'RES', // Restoration
            'quantity' => $productBatch->stock,
            'remarks' => 'Batch dikembalikan',
        ]);
    }

    /**
     * Handle the ProductBatch "force deleted" event.
     * This event is not typically used for stock tracking as it bypasses soft deletes.
     */
    public function forceDeleted(ProductBatch $productBatch): void
    {
        // No specific stock movement for force delete, as it's usually a permanent removal
        // and 'deleted' event should cover the stock reduction.
    }
}
