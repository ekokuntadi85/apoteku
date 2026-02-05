<?php

namespace App\Observers;

use App\Models\StockOpnameDetail;
use App\Models\StockMovement;

class StockOpnameDetailObserver
{
    /**
     * Handle the StockOpnameDetail "creating" event.
     */
    public function creating(StockOpnameDetail $detail): void
    {
        $systemStock = (int) $detail->system_stock;
        $physicalStock = (int) $detail->physical_stock;

        $detail->difference = $physicalStock - $systemStock;
    }

    /**
     * Handle the StockOpnameDetail "created" event.
     */
    public function created(StockOpnameDetail $detail): void
    {
        if ($detail->difference !== 0) {
            $detail->productBatch()->increment('stock', $detail->difference);

            StockMovement::create([
                'product_batch_id' => $detail->product_batch_id,
                'type' => 'OP',
                'quantity' => $detail->difference,
                'remarks' => $detail->stockOpname->notes ?? 'Opname #' . $detail->stock_opname_id,
            ]);
        }
    }

    /**
     * Handle the StockOpnameDetail "updating" event.
     */
    public function updating(StockOpnameDetail $detail): void
    {
        $originalDifference = $detail->getOriginal('difference');
        if ($originalDifference !== 0) {
            // Revert the original adjustment
            $detail->productBatch()->decrement('stock', $originalDifference);

            // Log the reversal
            StockMovement::create([
                'product_batch_id' => $detail->product_batch_id,
                'type' => 'R-OP',
                'quantity' => -$originalDifference,
                'remarks' => 'Revisi Opname #' . $detail->stock_opname_id,
            ]);
        }
    }

    /**
     * Handle the StockOpnameDetail "updated" event.
     */
    public function updated(StockOpnameDetail $detail): void
    {
        // Apply the new adjustment
        if ($detail->difference !== 0) {
            $detail->productBatch()->increment('stock', $detail->difference);

            StockMovement::create([
                'product_batch_id' => $detail->product_batch_id,
                'type' => 'OP',
                'quantity' => $detail->difference,
                'remarks' => 'Opname (Diedit) #' . $detail->stock_opname_id,
            ]);
        }
    }

    /**
     * Handle the StockOpnameDetail "deleted" event.
     */
    public function deleted(StockOpnameDetail $detail): void
    {
        if ($detail->difference !== 0) {
            // Revert the adjustment
            $detail->productBatch()->decrement('stock', $detail->difference);

            StockMovement::create([
                'product_batch_id' => $detail->product_batch_id,
                'type' => 'R-OP',
                'quantity' => -$detail->difference,
                'remarks' => 'Hapus Opname #' . $detail->stock_opname_id,
            ]);
        }
    }
}
