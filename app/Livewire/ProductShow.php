<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

use Livewire\Attributes\Title;

#[Title('Lihat Produk')]
class ProductShow extends Component
{
    public $product;

    public function mount(Product $product)
    {
        $this->product = $product->load(['category', 'baseUnit', 'productUnits', 'productBatches.purchase.supplier']);
        
        // Separate active and depleted batches
        $this->product->activeBatches = $this->product->productBatches->where('stock', '>', 0)->sortByDesc('created_at');
        $this->product->depletedBatches = $this->product->productBatches->where('stock', '=', 0)->sortByDesc('created_at');
    }

    public function deleteProduct()
    {
        if ($this->product->delete()) {
            session()->flash('message', 'Produk berhasil dihapus.');
        }
        return redirect()->route('products.index');
    }

    public function render()
    {
        return view('livewire.product-show');
    }
}