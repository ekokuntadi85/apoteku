<?php

namespace App\Livewire;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockConsistencyCheck extends Component
{
    public $inconsistentProducts = [];
    public $negativeStockProducts = []; // New property for negative stock
    public $checkPerformed = false;

    public function checkStockConsistency()
    {
        $this->checkPerformed = false;
        $this->inconsistentProducts = [];
        $this->negativeStockProducts = [];

        $productBatchesSub = DB::table('product_batches')
            ->select('product_id', DB::raw('SUM(stock) as product_batch_stock'))
            ->groupBy('product_id');

        $stockMovementsSub = DB::table('stock_movements')
            ->join('product_batches', 'stock_movements.product_batch_id', '=', 'product_batches.id')
            ->select('product_batches.product_id', DB::raw('SUM(stock_movements.quantity) as stock_movement_stock'))
            ->groupBy('product_batches.product_id');

        // Query to find inconsistent products
        $inconsistent = Product::query()
            ->leftJoinSub($productBatchesSub, 'pb', function ($join) {
                $join->on('products.id', '=', 'pb.product_id');
            })
            ->leftJoinSub($stockMovementsSub, 'sm', function ($join) {
                $join->on('products.id', '=', 'sm.product_id');
            })
            ->select(
                'products.id',
                'products.name',
                DB::raw('COALESCE(pb.product_batch_stock, 0) as product_table_stock'),
                DB::raw('COALESCE(sm.stock_movement_stock, 0) as calculated_stock')
            )
            ->whereRaw('COALESCE(pb.product_batch_stock, 0) != COALESCE(sm.stock_movement_stock, 0)')
            ->get();

        $this->inconsistentProducts = $inconsistent->toArray();

        // Query to find products with negative stock
        $negative = Product::query()
            ->leftJoinSub($productBatchesSub, 'pb', function ($join) {
                $join->on('products.id', '=', 'pb.product_id');
            })
            ->leftJoinSub($stockMovementsSub, 'sm', function ($join) {
                $join->on('products.id', '=', 'sm.product_id');
            })
            ->select(
                'products.id',
                'products.name',
                DB::raw('COALESCE(pb.product_batch_stock, 0) as product_table_stock'),
                DB::raw('COALESCE(sm.stock_movement_stock, 0) as calculated_stock')
            )
            ->whereRaw('COALESCE(pb.product_batch_stock, 0) < 0') // Negative in batches
            ->orWhereRaw('COALESCE(sm.stock_movement_stock, 0) < 0') // Negative in movements
            ->get();

        $this->negativeStockProducts = $negative->toArray();

        $this->checkPerformed = true;
    }

    public function fixStockInconsistencies()
    {
        if (empty($this->inconsistentProducts)) {
            return;
        }

        DB::transaction(function () {
            foreach ($this->inconsistentProducts as $product) {
                $product = (object)$product;
                
                $truth_stock = $product->calculated_stock;
                $current_batch_stock = $product->product_table_stock;
                $difference = $truth_stock - $current_batch_stock;

                if ($difference !== 0) {
                    $latestBatch = ProductBatch::where('product_id', $product->id)->latest()->first();

                    // Edge Case: If the product has movements but no batch, create one.
                    if (!$latestBatch) {
                        $latestBatch = ProductBatch::create([
                            'product_id' => $product->id,
                            'batch_number' => 'ADJ-INTEGRITY-' . uniqid(),
                            'purchase_price' => 0, // Cannot determine price, default to 0
                            'stock' => 0, // Start with 0 before applying the adjustment
                            'expiration_date' => null,
                        ]);
                    }
                    
                    // Directly update the batch stock. This does not trigger observers.
                    $latestBatch->increment('stock', $difference);
                }
            }
        });

        session()->flash('message', 'Stok produk telah berhasil disesuaikan secara langsung.');
        $this->checkStockConsistency();
    }

    public function render()
    {
        return view('livewire.stock-consistency-check');
    }
}