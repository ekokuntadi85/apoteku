<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use Carbon\Carbon;

class InventoryCount extends Component
{
    use WithPagination;

    // View state
    public $view = 'list'; // list, create, detail

    // Properties for creating a new opname
    public $opname_notes;
    public $opname_items = [];
    public $searchProduct = '';
    public $productResults = [];

    // Properties for viewing details
    public $selectedOpname;

    public function render()
    {
        $opnames = StockOpname::with('user')->latest()->paginate(10);
        return view('livewire.inventory-count', compact('opnames'));
    }

    public function updatedSearchProduct($value)
    {
        if (strlen($this->searchProduct) >= 2) {
            $this->productResults = Product::with('productBatches')
                ->where('name', 'like', '%' . $value . '%')
                ->orWhere('sku', 'like', '%' . $value . '%')
                ->limit(5)
                ->get();
        } else {
            $this->productResults = [];
        }
    }

    public function addProductToOpname($productId)
    {
        $product = Product::with('productBatches')->find($productId);
        if (!$product) return;

        $batches = $product->productBatches;

        if ($batches->isEmpty()) {
            // If no batches exist, create a default one to allow opname
            $batch = ProductBatch::create([
                'product_id' => $product->id,
                'batch_number' => 'OPNAME-' . uniqid(),
                'purchase_price' => 0, // Or fetch last known price, defaulting to 0
                'stock' => 0,
                'expiration_date' => null,
            ]);
            $batches->push($batch);
        }

        foreach ($batches as $batch) {
            if (!collect($this->opname_items)->contains('product_batch_id', $batch->id)) {
                $this->opname_items[] = [
                    'product_batch_id' => $batch->id,
                    'product_name' => $product->name,
                    'batch_number' => $batch->batch_number,
                    'expiration_date' => $batch->expiration_date,
                    'system_stock' => $batch->stock,
                    'physical_stock' => $batch->stock, // Default to system stock
                ];
            }
        }
        $this->searchProduct = '';
        $this->productResults = [];
    }

    public function removeItem($index)
    {
        unset($this->opname_items[$index]);
        $this->opname_items = array_values($this->opname_items);
    }

    public function saveOpname()
    {
        $this->validate([
            'opname_items' => 'required|array|min:1',
            'opname_items.*.physical_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () {
            $opname = StockOpname::create([
                'opname_date' => now(),
                'notes' => $this->opname_notes,
                'user_id' => Auth::id(),
            ]);

            foreach ($this->opname_items as $item) {
                StockOpnameDetail::create([
                    'stock_opname_id' => $opname->id,
                    'product_batch_id' => $item['product_batch_id'],
                    'system_stock' => $item['system_stock'],
                    'physical_stock' => $item['physical_stock'],
                    'difference' => $item['physical_stock'] - $item['system_stock'],
                ]);
            }
        });

        session()->flash('message', 'Stok opname berhasil disimpan.');
        $this->changeView('list');
    }

    public function deleteOpname($opnameId)
    {
        $opname = StockOpname::with('details')->find($opnameId);
        if ($opname) {
            // Observer will handle stock reversal for each detail
            $opname->delete();
            session()->flash('message', 'Stok opname berhasil dihapus dan stok telah dikembalikan.');
        }
    }

    public function changeView($view, $opnameId = null)
    {
        $this->view = $view;
        $this->resetForm();
        if ($opnameId) {
            $this->selectedOpname = StockOpname::with('details.productBatch.product', 'user')->find($opnameId);
        }
    }

    private function resetForm()
    {
        $this->opname_notes = null;
        $this->opname_items = [];
        $this->searchProduct = '';
        $this->productResults = [];
        $this->selectedOpname = null;
        $this->resetErrorBag();
    }
}