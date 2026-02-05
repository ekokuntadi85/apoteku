<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use Livewire\Attributes\Title;

#[Title('Detail Surat Pesanan')]
class PurchaseOrderShow extends Component
{
    public PurchaseOrder $purchaseOrder;
    public $actualQuantities = [];

    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder->load([
            'supplier', 
            'details.product', 
            'details.productUnit', 
            'purchase.productBatches.stockMovements'
        ]);

        $this->calculateActualQuantities();
    }

    private function calculateActualQuantities()
    {
        if ($this->purchaseOrder->status === 'completed' && $this->purchaseOrder->purchase) {
            $purchaseBatches = $this->purchaseOrder->purchase->productBatches;

            foreach ($this->purchaseOrder->details as $detail) {
                // Filter batches for this product
                $productBatches = $purchaseBatches->where('product_id', $detail->product_id);
                
                // Sum the initial stock (from 'PB' movements)
                $totalBaseQty = 0;
                foreach ($productBatches as $batch) {
                    $initialMove = $batch->stockMovements->firstWhere('type', 'PB');
                    if ($initialMove) {
                        $totalBaseQty += $initialMove->quantity;
                    }
                }

                // Convert back to the unit used in PO
                $conversionFactor = $detail->productUnit->conversion_factor;
                // Avoid division by zero
                $qty = $conversionFactor > 0 ? $totalBaseQty / $conversionFactor : 0;
                
                $this->actualQuantities[$detail->id] = $qty;
            }
        }
    }

    public function render()
    {
        return view('livewire.purchase-order-show');
    }

    public function markAsSent()
    {
        if ($this->purchaseOrder->status === 'draft') {
            $this->purchaseOrder->update(['status' => 'sent']);
            session()->flash('message', 'Status Surat Pesanan berhasil diubah menjadi Terkirim.');
        }
    }

    public function cancelOrder()
    {
        if ($this->purchaseOrder->status !== 'completed') {
            $this->purchaseOrder->update(['status' => 'cancelled']);
            session()->flash('message', 'Surat Pesanan berhasil dibatalkan.');
        }
    }

    public function deleteOrder()
    {
        if (in_array($this->purchaseOrder->status, ['draft', 'cancelled'])) {
            $this->purchaseOrder->details()->delete();
            $this->purchaseOrder->delete();
            session()->flash('message', 'Surat Pesanan berhasil dihapus.');
            return redirect()->route('purchase-orders.index');
        }
    }
}
