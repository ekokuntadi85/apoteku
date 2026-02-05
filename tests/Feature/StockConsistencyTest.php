<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockConsistencyTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_maintains_stock_consistency_with_fefo_logic_and_final_validation()
    {
        // 1. Setup: Create user, product, and batches with varying expiration dates
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Batches are created out of order to ensure sorting by expiration_date is working
        $batch1_expires_later = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
            'expiration_date' => now()->addDays(20),
        ]);

        $batch2_expires_first = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 15,
            'expiration_date' => now()->addDays(5), // Expires first
        ]);

        $batch3_expires_middle = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 20,
            'expiration_date' => now()->addDays(10), // Expires second
        ]);

        $this->assertEquals(45, $product->refresh()->total_stock, "Initial stock calculation is incorrect.");

        // 2. Act (Sale #1): Sell 25 units. Should use all of batch2 (15) and 10 of batch3.
        $transaction1 = Transaction::factory()->create(['user_id' => $user->id]);
        TransactionDetail::factory()->create([
            'transaction_id' => $transaction1->id,
            'product_id' => $product->id,
            'quantity' => 25,
        ]);

        // 3. Assertions (Sale #1): Check FEFO logic and stock reduction
        $this->assertEquals(0, $batch2_expires_first->refresh()->stock, "Batch expiring first should be empty.");
        $this->assertEquals(10, $batch3_expires_middle->refresh()->stock, "Batch expiring second should have 10 units left.");
        $this->assertEquals(10, $batch1_expires_later->refresh()->stock, "Batch expiring last should be untouched.");
        $this->assertEquals(20, $product->refresh()->total_stock, "Total stock after Sale #1 is incorrect.");

        // 4. Act (Deletion of Sale #1): Delete the transaction to test stock restoration.
        $transaction1->delete();

        // 5. Assertions (After Deletion): Stock should be fully restored to its original state.
        $this->assertEquals(15, $batch2_expires_first->refresh()->stock, "Stock for batch expiring first was not restored correctly.");
        $this->assertEquals(20, $batch3_expires_middle->refresh()->stock, "Stock for batch expiring second was not restored correctly.");
        $this->assertEquals(10, $batch1_expires_later->refresh()->stock, "Stock for batch expiring last should not have changed.");
        $this->assertEquals(45, $product->refresh()->total_stock, "Total stock after deletion is incorrect.");

        // 6. Act (Stock Opname): Adjust stock for one batch.
        $opname = StockOpname::create(['opname_date' => now(), 'user_id' => $user->id]);
        StockOpnameDetail::create([
            'stock_opname_id' => $opname->id,
            'product_batch_id' => $batch2_expires_first->id,
            'system_stock' => 15,
            'physical_stock' => 12, // Found a discrepancy
            'difference' => -3,
        ]);

        // 7. Assertions (After Opname): Check stock after adjustment.
        $this->assertEquals(12, $batch2_expires_first->refresh()->stock, "Stock was not adjusted correctly by opname.");
        $this->assertEquals(42, $product->refresh()->total_stock, "Total stock after opname is incorrect.");

        // 8. Final Validation: Three-way stock comparison
        // View from Product Manager (Model Accessor)
        $productManagerStock = $product->refresh()->total_stock;

        // View from Database (Direct Sum)
        $databaseStock = ProductBatch::where('product_id', $product->id)->sum('stock');

        // View from Stock Card (Sum of Movements)
        $stockCardStock = StockMovement::whereIn('product_batch_id', [$batch1_expires_later->id, $batch2_expires_first->id, $batch3_expires_middle->id])->sum('quantity');

        $this->assertEquals($productManagerStock, $databaseStock, "Product Manager stock does not match direct DB sum.");
        $this->assertEquals($databaseStock, $stockCardStock, "Direct DB sum does not match the Stock Card (movements) sum.");
        $this->assertEquals(42, $productManagerStock, "Final validated stock is not the expected value.");

        // Log final state for clarity
        error_log("Final validation successful: Product Manager ({$productManagerStock}), DB ({$databaseStock}), Stock Card ({$stockCardStock})");
    }
}
