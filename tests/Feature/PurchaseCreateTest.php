<?php

namespace Tests\Feature;

use App\Livewire\PurchaseCreate;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PurchaseCreateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $supplier;
    protected $product;
    protected $unitConfig;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permission required for accessing purchases
        Permission::create(['name' => 'access-purchases']);

        $this->user = User::factory()->create();
        $this->user->givePermissionTo('access-purchases');

        $this->supplier = Supplier::create([
            'name' => 'Supplier Test', 
            'email' => 'supplier@test.com',
            'phone' => '08123456789',
            'address' => 'Test Address'
        ]);

        $category = Category::create([
            'name' => 'Obat Bebas',
            'description' => 'Test Category'
        ]);

        $this->product = Product::create([
            'name' => 'Obat Test',
            'sku' => 'OBT001',
            'barcode' => '899123456',
            'description' => 'Obat untuk testing',
            'min_stock' => 10,
            'category_id' => $category->id,
        ]);

        // Create base unit (Tablet)
        $this->unitConfig = ProductUnit::create([
            'product_id' => $this->product->id,
            'name' => 'Tablet',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 1000,
            'selling_price' => 1200,
        ]);
        
        // Create larger unit (Strip)
        ProductUnit::create([
            'product_id' => $this->product->id,
            'name' => 'Strip',
            'conversion_factor' => 10,
            'is_base_unit' => false,
            'purchase_price' => 9500, // Slightly cheaper bulk price
            'selling_price' => 12000,
        ]);
    }

    /** @test */
    public function creates_purchase_page_renders_successfully()
    {
        $this->actingAs($this->user)
            ->get(route('purchases.create'))
            ->assertOk(); 
            
        Livewire::test(PurchaseCreate::class)
            ->assertStatus(200);
    }

    /** @test */
    public function can_add_item_to_purchase_list()
    {
        Livewire::test(PurchaseCreate::class)
            ->set('product_id', $this->product->id)
            ->call('selectProduct', $this->product->id) // Trigger selection logic
            ->set('selectedProductUnitId', $this->unitConfig->id)
            ->set('batch_number', 'BATCH001')
            ->set('purchase_price', 1000)
            ->set('selling_price', 1200)
            ->set('stock', 50)
            ->set('expiration_date', now()->addYear()->format('Y-m-d'))
            ->call('addItem')
            ->assertSet('purchase_items.0.product_name', 'Obat Test')
            ->assertSet('purchase_items.0.stock', 50) // 50 * 1
            ->assertSet('total_purchase_price', 50000); // 50 * 1000
    }

    /** @test */
    public function correctly_calculates_stock_conversion_for_larger_units()
    {
        $stripUnit = ProductUnit::where('name', 'Strip')->first();

        Livewire::test(PurchaseCreate::class)
            ->set('product_id', $this->product->id)
            ->call('selectProduct', $this->product->id)
            ->set('selectedProductUnitId', $stripUnit->id) // Select Strip
            ->set('stock', 5) // Buy 5 Strips
            ->set('purchase_price', 9500)
            ->set('selling_price', 12000)
            ->call('addItem')
            ->assertSet('purchase_items.0.unit_name', 'Strip')
            ->assertSet('purchase_items.0.purchase_price', 9500)
            ->assertSet('purchase_items.0.stock', 50) // 5 strips * 10 tablets = 50 tablets base stock
            ->assertSet('purchase_items.0.original_stock_input', 5)
            ->assertSet('total_purchase_price', 47500); // 5 * 9500
    }

    /** @test */
    public function shows_price_warning_modal_when_purchase_price_changes()
    {
        // Setup existing history
        // Previous purchase: Price 1000 per tablet
        $purchase = Purchase::create([
            'supplier_id' => $this->supplier->id,
            'invoice_number' => 'INV-OLD-001',
            'purchase_date' => now()->subMonth(),
            'total_price' => 100000,
        ]);

        ProductBatch::create([
            'purchase_id' => $purchase->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unitConfig->id,
            'batch_number' => 'OLD-BATCH',
            'purchase_price' => 1000, // Old price
            'stock' => 100,
            'expiration_date' => now()->addYear(),
        ]);

        // Attempt to add item with NEW price (1200)
        Livewire::test(PurchaseCreate::class)
            ->set('product_id', $this->product->id)
            ->call('selectProduct', $this->product->id)
            ->set('selectedProductUnitId', $this->unitConfig->id)
            ->set('purchase_price', 1200) // Price increased
            ->set('selling_price', 1500)
            ->set('stock', 10)
            ->call('addItem')
            ->assertSet('showPriceWarningModal', true)
            ->assertSet('itemToAddCache.product_id', $this->product->id)
            ->assertSet('itemToAddCache.price_change_type', 'increase');
    }

    /** @test */
    public function can_save_complete_purchase()
    {
        Livewire::test(PurchaseCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('invoice_number', 'INV-NEW-001')
            ->set('purchase_date', now()->format('Y-m-d'))
            ->set('due_date', now()->addDays(30)->format('Y-m-d'))
            // Add item directly to array to simulate confirmed add
            ->set('purchase_items', [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'product_unit_id' => $this->unitConfig->id,
                    'unit_name' => 'Tablet',
                    'conversion_factor' => 1,
                    'batch_number' => 'BATCH-TEST',
                    'purchase_price' => 1000,
                    'selling_price' => 1200,
                    'stock' => 100,
                    'original_stock_input' => 100,
                    'expiration_date' => now()->addYear()->format('Y-m-d'),
                    'subtotal' => 100000,
                ]
            ])
            ->set('total_purchase_price', 100000)
            ->call('savePurchase')
            ->assertHasNoErrors()
            ->assertRedirect(route('purchases.index'));

        $this->assertDatabaseHas('purchases', [
            'invoice_number' => 'INV-NEW-001',
            'supplier_id' => $this->supplier->id,
        ]);

        $this->assertDatabaseHas('product_batches', [
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH-TEST',
            'stock' => 100,
        ]);
    }

    /** @test */
    public function validation_fails_if_required_fields_missing()
    {
        Livewire::test(PurchaseCreate::class)
            ->call('savePurchase')
            ->assertHasErrors(['supplier_id', 'invoice_number', 'purchase_items']);
    }
}
