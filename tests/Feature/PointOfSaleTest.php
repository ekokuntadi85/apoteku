<?php

namespace Tests\Feature;

use App\Livewire\PointOfSale;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductUnit;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PointOfSaleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product;
    private ProductUnit $unitPcs;
    private ProductUnit $unitBox;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        // Create a default customer
        Customer::factory()->create(['name' => 'UMUM']);

        // Setup product with units and stock
        $this->product = Product::factory()->create(['name' => 'Paracetamol']);
        
        $this->unitPcs = ProductUnit::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Pcs',
            'is_base_unit' => true,
            'conversion_factor' => 1,
            'selling_price' => 1000,
        ]);

        $this->unitBox = ProductUnit::factory()->create([
            'product_id' => $this->product->id,
            'name' => 'Box',
            'is_base_unit' => false,
            'conversion_factor' => 10,
            'selling_price' => 9500, // Bulk discount
        ]);

        ProductBatch::factory()->create([
            'product_id' => $this->product->id,
            'stock' => 100, // 100 Pcs or 10 Boxes
            'expiration_date' => now()->addYear(),
        ]);
    }

    #[Test]
    public function pos_happy_path_scenario_works_correctly()
    {
        $this->assertEquals(100, $this->product->productBatches()->sum('stock'));

        Livewire::test(PointOfSale::class)
            // 1. Select the product, which should open the modal
            ->call('selectProduct', $this->product->id)
            ->assertSet('isUnitModalVisible', true)
            ->assertSet('productForModal.id', $this->product->id)

            // 2. Set quantity and add to cart using the base unit (Pcs)
            ->set('quantityToAdd', 5)
            ->call('addItemToCart', $this->unitPcs->id)
            ->assertSet('isUnitModalVisible', false) // Modal should close
            ->assertCount('cart_items', 1)
            ->assertSet('total_price', 5000) // 5 Pcs * 1000

            // 3. Set payment amount and checkout
            ->set('amount_paid', 5000)
            ->call('checkout')

            // 4. Assertions
            ->assertHasNoErrors()
            ->assertDispatched('transaction-completed');

        // Verify database state
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transactions', [
            'total_price' => 5000,
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseCount('transaction_details', 1);
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unitPcs->id,
            'quantity' => 5, // Quantity is stored as base units
        ]);

        // Verify stock deduction
        $this->assertEquals(95, $this->product->productBatches()->sum('stock')); // 100 - 5
    }

    #[Test]
    public function pos_multi_unit_scenario_deducts_stock_correctly()
    {
        $initialStock = $this->product->productBatches()->sum('stock');
        $this->assertEquals(100, $initialStock);

        Livewire::test(PointOfSale::class)
            // 1. Select product
            ->call('selectProduct', $this->product->id)

            // 2. Add 2 Boxes to the cart
            ->set('quantityToAdd', 2)
            ->call('addItemToCart', $this->unitBox->id)
            ->assertCount('cart_items', 1)
            ->assertSet('total_price', 19000) // 2 Boxes * 9500

            // 3. Checkout
            ->set('amount_paid', 20000)
            ->call('checkout')
            ->assertHasNoErrors();

        // 4. Verify database state
        $this->assertDatabaseCount('transactions', 1);
        $this->assertDatabaseHas('transaction_details', [
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unitBox->id,
            'quantity' => 20, // 2 boxes * 10 conversion_factor
        ]);

        // Verify stock deduction based on conversion factor
        // Initial stock was 100 Pcs. We sold 2 Boxes (2 * 10 = 20 Pcs).
        $expectedStock = $initialStock - (2 * $this->unitBox->conversion_factor);
        $this->assertEquals($expectedStock, $this->product->productBatches()->sum('stock'));
        $this->assertEquals(80, $this->product->productBatches()->sum('stock'));
    }

    #[Test]
    public function pos_stock_validation_prevents_overselling()
    {
        $initialStock = $this->product->productBatches()->sum('stock'); // 100 Pcs

        Livewire::test(PointOfSale::class)
            // 1. Select product
            ->call('selectProduct', $this->product->id)

            // 2. Try to add 11 Boxes (110 Pcs) which is more than the available stock (100 Pcs)
            ->set('quantityToAdd', 11)
            ->call('addItemToCart', $this->unitBox->id)

            // 3. Assert that an error is thrown
            ->assertHasErrors(['quantityToAdd' => 'Stok tidak mencukupi.'])
            ->assertSet('isUnitModalVisible', true) // Modal should remain open
            ->assertCount('cart_items', 0); // Cart should be empty

        // 4. Verify that no transaction was created and stock remains unchanged
        $this->assertDatabaseCount('transactions', 0);
        $this->assertEquals($initialStock, $this->product->productBatches()->sum('stock'));
    }

    #[Test]
    public function it_prevents_race_conditions_on_checkout()
    {
        // Setup: A product with exactly 1 stock and a fixed price
        $productWithLimitedStock = Product::factory()->create();
        $unit = ProductUnit::factory()->create([
            'product_id' => $productWithLimitedStock->id,
            'conversion_factor' => 1,
            'selling_price' => 1000, // Fixed price
        ]);
        ProductBatch::factory()->create(['product_id' => $productWithLimitedStock->id, 'stock' => 1]);

        $this->assertEquals(1, $productWithLimitedStock->productBatches()->sum('stock'));

        // Simulate User A adding the last item to cart and is ready to checkout
        $userA_Livewire = Livewire::actingAs($this->user)->test(PointOfSale::class)
            ->call('selectProduct', $productWithLimitedStock->id)
            ->set('quantityToAdd', 1)
            ->call('addItemToCart', $unit->id)
            ->set('amount_paid', 1000);

        // Simulate User B doing the same thing at the same time
        $userB_Livewire = Livewire::actingAs(User::factory()->create())->test(PointOfSale::class)
            ->call('selectProduct', $productWithLimitedStock->id)
            ->set('quantityToAdd', 1)
            ->call('addItemToCart', $unit->id)
            ->set('amount_paid', 1000);

        // User A checks out first
        $userA_Livewire->call('checkout')
            ->assertHasNoErrors();

        // Now, User B tries to checkout with the same item that is now out of stock
        $userB_Livewire->call('checkout')
            ->assertHasErrors(['cart_items']); // Should fail due to stock validation inside transaction

        // Assert that only one transaction was successful
        $this->assertDatabaseCount('transactions', 1);

        // Assert that the stock is 0, not negative
        $this->assertEquals(0, $productWithLimitedStock->fresh()->productBatches()->sum('stock'));
    }
}