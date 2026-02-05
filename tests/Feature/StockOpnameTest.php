<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Factories\StockOpnameFactory;
use Database\Factories\StockOpnameDetailFactory;
use Spatie\Permission\Models\Permission;

class StockOpnameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access-products']);
        $user->givePermissionTo('access-products');
        $this->actingAs($user); // Authenticate a user for tests
        config(['app.env' => 'testing']);
        \Artisan::call('config:clear');
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $this->withoutExceptionHandling();
    }

    /** @test */
    public function it_can_create_a_stock_opname_and_details()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $controller = new \App\Http\Controllers\StockOpnameController();
        $request = \Illuminate\Http\Request::create('/stock-opnames', 'POST', [
            'notes' => 'Initial stock opname',
            'details' => [
                [
                    'product_batch_id' => $batch->id,
                    'system_stock' => 100,
                    'physical_stock' => 95,
                ],
            ],
        ]);
        $rawResponse = $controller->store($request);
        $response = new \Illuminate\Testing\TestResponse($rawResponse);

        $response->assertStatus(201);
        $this->assertDatabaseHas('stock_opnames', [
            'notes' => 'Initial stock opname',
        ]);
        $this->assertDatabaseHas('stock_opname_details', [
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'physical_stock' => 95,
        ]);
    }

    /** @test */
    public function it_correctly_adjusts_stock_on_stock_opname_application_for_shortage()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with shortage',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'physical_stock' => 90, // 10 units short
        ]);

        // Assuming an endpoint to apply the stock opname
        $controller = new \App\Http\Controllers\StockOpnameController();
        $request = \Illuminate\Http\Request::create("/stock-opnames/{$stockOpname->id}/apply", 'POST');
        $rawResponse = $controller->apply($stockOpname, $request);
        $response = new \Illuminate\Testing\TestResponse($rawResponse);

        $response->assertStatus(200);
        $this->assertEquals(90, $batch->fresh()->stock); // Stock should be adjusted to counted stock
        $this->assertDatabaseHas('stock_movements', [
            'product_batch_id' => $batch->id,
            'type' => 'OP', // Changed from stock_opname_out
            'quantity' => -10, // Changed from 10
        ]);
    }

    /** @test */
    public function it_correctly_adjusts_stock_on_stock_opname_application_for_overage()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with overage',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'physical_stock' => 110, // 10 units over
        ]);

        // Assuming an endpoint to apply the stock opname
        $controller = new \App\Http\Controllers\StockOpnameController();
        $request = \Illuminate\Http\Request::create("/stock-opnames/{$stockOpname->id}/apply", 'POST');
        $rawResponse = $controller->apply($stockOpname, $request);
        $response = new \Illuminate\Testing\TestResponse($rawResponse);

        $response->assertStatus(200);
        $this->assertEquals(110, $batch->fresh()->stock); // Stock should be adjusted to counted stock
        $this->assertDatabaseHas('stock_movements', [
            'product_batch_id' => $batch->id,
            'type' => 'OP', // Changed from stock_opname_in
            'quantity' => 10,
        ]);
    }

    /** @test */
    public function it_does_not_adjust_stock_if_counted_stock_matches_system_stock()
    {
        $product = Product::factory()->create();
        $batch = ProductBatch::factory()->create([
            'product_id' => $product->id,
            'stock' => 100,
        ]);

        $stockOpname = StockOpname::factory()->create([
            'notes' => 'Stock opname with no change',
        ]);

        StockOpnameDetail::factory()->create([
            'stock_opname_id' => $stockOpname->id,
            'product_batch_id' => $batch->id,
            'system_stock' => 100,
            'physical_stock' => 100, // No change
        ]);

        // Assuming an endpoint to apply the stock opname
        $controller = new \App\Http\Controllers\StockOpnameController();
        $request = \Illuminate\Http\Request::create("/stock-opnames/{$stockOpname->id}/apply", 'POST');
        $rawResponse = $controller->apply($stockOpname, $request);
        $response = new \Illuminate\Testing\TestResponse($rawResponse);

        $response->assertStatus(200);
        $this->assertEquals(100, $batch->fresh()->stock); // Stock should remain unchanged
        $this->assertDatabaseMissing('stock_movements', [ // No stock movement should be recorded
            'product_batch_id' => $batch->id,
            'type' => 'OP', // Assert that no 'OP' type movement is recorded
        ]);
    }
}
