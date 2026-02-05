<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function decrementStock(TransactionDetail $detail): void
    {
        DB::transaction(function () use ($detail) {
            $quantityToDecrement = $detail->quantity;
            $product = $detail->product;

            // Calculate total available stock for the product
            $totalAvailableStock = $product->productBatches()->sum('stock');

            // --- NEW CHECK ---
            if ($quantityToDecrement > $totalAvailableStock) {
                // Throw an exception to prevent over-selling
                throw new \Exception('Insufficient stock for product: ' . $product->name . '. Available: ' . $totalAvailableStock . ', Requested: ' . $quantityToDecrement);
            }
            // --- END NEW CHECK ---

            $batches = $product->productBatches()
                ->where('stock', '>', 0)
                ->orderBy('expiration_date', 'asc')
                ->lockForUpdate()
                ->get();

            foreach ($batches as $batch) {
                if ($quantityToDecrement <= 0) {
                    break;
                }

                $stockToTake = min($batch->stock, $quantityToDecrement);

                if ($stockToTake > 0) {
                    $batch->decrement('stock', $stockToTake);

                    StockMovement::create([
                        'product_batch_id' => $batch->id,
                        'type' => 'PJ', // Penjualan
                        'quantity' => -$stockToTake,
                        'remarks' => 'Penjualan #' . $detail->transaction->invoice_number,
                    ]);

                    TransactionDetailBatch::create([
                        'transaction_detail_id' => $detail->id,
                        'product_batch_id' => $batch->id,
                        'quantity' => $stockToTake,
                    ]);
                }

                $quantityToDecrement -= $stockToTake;
            }
        });
    }

    public function incrementStock(TransactionDetail $detail): void
    {
        DB::transaction(function () use ($detail) {
            foreach ($detail->transactionDetailBatches as $tdb) {
                $batch = $tdb->productBatch;
                if ($batch) {
                    $batch->increment('stock', $tdb->quantity);

                    StockMovement::create([
                        'product_batch_id' => $batch->id,
                        'type' => 'R-PJ', // Retur Penjualan
                        'quantity' => $tdb->quantity,
                        'remarks' => 'Retur/Hapus Penjualan #' . $detail->transaction->invoice_number,
                    ]);
                }
            }
        });
    }
}