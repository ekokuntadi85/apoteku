<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Manajemen Produk')]
class ProductManager extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = '';
    public $stock_filter = ''; // '', 'low', 'out'

    public $showModal = false;
    public $viewingProduct = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
        'stock_filter' => ['except' => ''],
    ];

    public function showDetail($id)
    {
        $this->viewingProduct = Product::with(['category', 'baseUnit', 'productUnits', 'productBatches.purchase.supplier'])->find($id);
        
        if ($this->viewingProduct) {
            $this->showModal = true;
        }
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->viewingProduct = null;
    }

    public function getStatsProperty()
    {
        return [
            'total' => Product::count(),
            'low_stock' => Product::whereExists(function ($query) {
                $query->selectRaw(1)
                    ->from('product_batches')
                    ->whereColumn('product_batches.product_id', 'products.id')
                    ->havingRaw('SUM(stock) > 0 AND SUM(stock) < 10');
            })->count(),
            'out_of_stock' => Product::whereNotExists(function ($query) {
                $query->selectRaw(1)
                    ->from('product_batches')
                    ->whereColumn('product_batches.product_id', 'products.id')
                    ->havingRaw('SUM(stock) > 0');
            })->count(),
        ];
    }

    public function getCategoriesProperty()
    {
        return \App\Models\Category::orderBy('name')->get();
    }

    public function render()
    {
        $query = Product::with(['category', 'baseUnit', 'productUnits'])
            ->withSum('productBatches as total_stock_sum', 'stock')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%');
            });

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        if ($this->stock_filter === 'low') {
            $query->whereExists(function ($q) {
                $q->selectRaw(1)
                    ->from('product_batches')
                    ->whereColumn('product_batches.product_id', 'products.id')
                    ->havingRaw('SUM(stock) > 0 AND SUM(stock) < 10');
            });
        } elseif ($this->stock_filter === 'out') {
            $query->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('product_batches')
                    ->whereColumn('product_batches.product_id', 'products.id')
                    ->havingRaw('SUM(stock) > 0');
            });
        }

        $products = $query->latest()->paginate(10);

        return view('livewire.product-manager', [
            'products' => $products,
            'stats' => $this->stats
        ]);
    }

    public function delete($id)
    {
        \Illuminate\Support\Facades\Log::info('ProductManager: Delete method called for product ID: ' . $id);

        $product = Product::withCount(['productBatches', 'transactionDetails'])->find($id);

        if ($product->product_batches_count > 0 || $product->transaction_details_count > 0) {
            session()->flash('error', 'Produk tidak dapat dihapus karena memiliki riwayat transaksi pembelian atau penjualan.');
            return;
        }

        if ($product->delete()) {
            session()->flash('message', 'Produk berhasil dihapus.');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}
