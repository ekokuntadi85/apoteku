<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_correctly_updates_stock_after_a_transaction_and_deletion()
    {
        // 1. Setup
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 10,
        ]);

        // 2. Create a transaction
        $transaction = Transaction::factory()->create([
            'user_id' => $user->id,
        ]);

        $transactionDetail = TransactionDetail::factory()->create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        // 3. Assert stock is decremented
        $this->assertEquals(7, $batch->fresh()->stock);

        // 4. Delete the transaction
        $transaction->delete();

        // 5. Assert stock is returned
        $this->assertEquals(10, $batch->fresh()->stock);
    }
}
