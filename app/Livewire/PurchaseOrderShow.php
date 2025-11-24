<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseOrder;
use Livewire\Attributes\Title;

#[Title('Detail Surat Pesanan')]
class PurchaseOrderShow extends Component
{
    public PurchaseOrder $purchaseOrder;

    public function mount(PurchaseOrder $purchaseOrder)
    {
        $this->purchaseOrder = $purchaseOrder->load(['supplier', 'details.product', 'details.productUnit']);
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
