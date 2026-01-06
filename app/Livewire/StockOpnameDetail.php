<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\StockOpname;
use App\Services\StockOpnameService;

class StockOpnameDetail extends Component
{
    public StockOpname $opname;
    public bool $showFinalizeModal = false;
    public float $totalAdjustmentValue = 0;
    public string $adjustmentType = '';
    public array $itemsWithoutPrice = [];
    public bool $hasWarnings = false;
    
    public function mount(StockOpname $opname)
    {
        $this->opname = $opname->load(['details.productBatch.product', 'journalEntry']);
        $this->calculateAdjustment();
    }
    
    public function calculateAdjustment()
    {
        $this->totalAdjustmentValue = 0;
        $this->itemsWithoutPrice = [];
        
        foreach ($this->opname->details as $detail) {
            // Check for zero or null purchase price
            if (!$detail->productBatch->purchase_price || $detail->productBatch->purchase_price <= 0) {
                $this->itemsWithoutPrice[] = [
                    'name' => $detail->productBatch->product->name,
                    'batch' => $detail->productBatch->batch_number,
                    'difference' => $detail->difference
                ];
                $this->hasWarnings = true;
            }
            
            $adjustmentValue = $detail->difference * $detail->productBatch->purchase_price;
            $this->totalAdjustmentValue += $adjustmentValue;
        }
        
        if ($this->totalAdjustmentValue < 0) {
            $this->adjustmentType = 'LOSS';
        } elseif ($this->totalAdjustmentValue > 0) {
            $this->adjustmentType = 'GAIN';
        } else {
            $this->adjustmentType = 'NO ADJUSTMENT';
        }
    }
    
    public function openFinalizeModal()
    {
        if ($this->opname->isFinalized()) {
            session()->flash('error', 'Stock opname sudah difinalisasi sebelumnya.');
            return;
        }
        
        $this->showFinalizeModal = true;
    }
    
    public function confirmFinalize()
    {
        try {
            $service = new StockOpnameService();
            $service->finalizeOpname($this->opname);
            
            // Refresh data
            $this->opname->refresh();
            
            session()->flash('success', 'Stock opname berhasil difinalisasi! Jurnal keuangan telah dibuat otomatis.');
            $this->showFinalizeModal = false;
            
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal finalisasi: ' . $e->getMessage());
            $this->showFinalizeModal = false;
        }
    }
    
    public function render()
    {
        return view('livewire.stock-opname-detail');
    }
}
