<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Purchase;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Title('Lihat Pembelian')]
class PurchaseShow extends Component
{
    public $purchase;

    public function mount(Purchase $purchase)
    {
        $this->loadPurchaseData($purchase);
    }

    /**
     * Load purchase with relationships and compute derived batch attributes.
     * This method is used both on initial mount and after status changes.
     */
    private function loadPurchaseData(Purchase $purchase)
    {
        $this->purchase = $purchase->load(['supplier', 'productBatches.product.baseUnit', 'productBatches.productUnit']);

        // Eager load initial stock movements to solve N+1 problem
        $batchIds = $this->purchase->productBatches->pluck('id');
        $stockMovements = StockMovement::whereIn('product_batch_id', $batchIds)
                                       ->where('type', 'PB')
                                       ->get()
                                       ->keyBy('product_batch_id');

        // Attach the original purchased stock quantity and calculated prices to each batch
        foreach ($this->purchase->productBatches as $batch) {
            $movement = $stockMovements->get($batch->id);
            $batch->original_stock = $movement ? $movement->quantity : 0; // This is in BASE units

            if ($batch->productUnit && $batch->productUnit->conversion_factor > 0) {
                // Final Fix: The purchase_price from DB is correct for the purchased unit. Only quantity needs conversion.
                $batch->display_purchase_price = $batch->purchase_price;
                $batch->original_input_quantity = $batch->original_stock / $batch->productUnit->conversion_factor;
                $batch->display_unit_name = $batch->productUnit->name;
            } else {
                // Fallback for base unit purchases or data issues
                $batch->display_purchase_price = $batch->purchase_price;
                $batch->original_input_quantity = $batch->original_stock;
                $batch->display_unit_name = $batch->product->baseUnit->name ?? 'units';
            }
        }
    }

    public function markAsPaid()
    {
        // Update status in DB
        $this->purchase->update(['payment_status' => 'paid']);
        // Refresh the model (including relationships) and recompute derived batch attributes
        $this->loadPurchaseData($this->purchase->fresh());
        session()->flash('message', 'Pembelian berhasil ditandai lunas.');
    }

    public function deletePurchase()
    {
        // Gather IDs of all product batches belonging to this purchase (use query builder to ensure fresh data)
        $batchIds = $this->purchase->productBatches()->pluck('id');
        Log::info('Attempting deletePurchase', ['purchase_id' => $this->purchase->id, 'batch_ids' => $batchIds]);

        // Determine if any batch has been used in sales or other operations
        $hasStockUsage = StockMovement::whereIn('product_batch_id', $batchIds)
                            ->where('type', '!=', 'PB')
                            ->exists();
        $hasDetailUsage = \App\Models\TransactionDetailBatch::whereIn('product_batch_id', $batchIds)
                            ->exists();
        $hasUsage = $hasStockUsage || $hasDetailUsage;
        Log::info('hasUsage check', ['hasStockUsage' => $hasStockUsage, 'hasDetailUsage' => $hasDetailUsage]);

        if ($hasUsage) {
            // Prevent deletion – inform the user
            // Reload purchase data to ensure UI displays correctly after re-render
            $this->loadPurchaseData($this->purchase->fresh());
            session()->flash('error', 'Tidak dapat menghapus pembelian karena ada barang yang sudah dipakai (terjual/diubah).');
            return; // abort – Livewire will stay on the same page
        }

        // No usage found – safe to delete
        DB::transaction(function () {
            $this->purchase->productBatches()->delete(); // Delete related product batches
            $this->purchase->delete(); // Delete the purchase record
        });

        session()->flash('message', 'Pembelian berhasil dihapus.');
        return redirect()->route('purchases.index');
    }

    public function render()
    {
        return view('livewire.purchase-show');
    }
}
