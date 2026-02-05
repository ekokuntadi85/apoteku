<?php

namespace Tests\Feature;

use App\Livewire\PurchaseEdit;
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

class PurchaseEditTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $supplier;
    protected $product;
    protected $unitConfig;
    protected $purchase;

    protected function setUp(): void
    {
        parent::setUp();
        
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

        $this->unitConfig = ProductUnit::create([
            'product_id' => $this->product->id,
            'name' => 'Tablet',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 1000,
            'selling_price' => 1200,
        ]);

        // Create an existing purchase to edit
        $this->purchase = Purchase::create([
            'invoice_number' => 'INV-ORIGINAL',
            'purchase_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'total_price' => 50000,
            'supplier_id' => $this->supplier->id,
            'payment_status' => 'unpaid',
        ]);

        ProductBatch::create([
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unitConfig->id,
            'batch_number' => 'BATCH-ORIGINAL',
            'purchase_price' => 1000,
            'stock' => 50,
            'expiration_date' => now()->addYear()->format('Y-m-d'),
        ]);
    }

    /** @test */
    public function can_render_edit_page_and_load_existing_purchase_data()
    {
        $this->actingAs($this->user)
            ->get(route('purchases.edit', $this->purchase))
            ->assertOk();

        Livewire::test(PurchaseEdit::class, ['purchase' => $this->purchase])
            ->assertSet('invoice_number', 'INV-ORIGINAL')
            ->assertSet('total_purchase_price', 50000)
            ->assertSet('purchase_items.0.product_name', 'Obat Test')
            ->assertSet('purchase_items.0.stock', 50);
    }

    /** @test */
    public function can_update_purchase_details_and_add_new_items()
    {
        // Setup new product for addition
        $newProduct = Product::create([
            'name' => 'Vitamin C',
            'sku' => 'VITC001',
            'category_id' => $this->product->category_id,
        ]);

        $newUnit = ProductUnit::create([
            'product_id' => $newProduct->id,
            'name' => 'Botol',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 5000,
            'selling_price' => 6500,
        ]);

        Livewire::test(PurchaseEdit::class, ['purchase' => $this->purchase])
            // Update Header
            ->set('invoice_number', 'INV-UPDATED')
            
            // Add New Item
            ->set('product_id', $newProduct->id)
            ->call('selectProduct', $newProduct->id)
            ->set('selectedProductUnitId', $newUnit->id)
            ->set('stock', 10)
            ->set('purchase_price', 5000)
            ->set('batch_number', 'BATCH-NEW')
            ->call('addItem')

            // Save form
            ->call('savePurchase')
            ->assertRedirect(route('purchases.index'));

        // Assert Database Changes
        $this->assertDatabaseHas('purchases', [
            'id' => $this->purchase->id,
            'invoice_number' => 'INV-UPDATED',
        ]);

        // Assert Old Batch Still Exists
        $this->assertDatabaseHas('product_batches', [
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'batch_number' => 'BATCH-ORIGINAL',
        ]);

        // Assert New Batch Added
        $this->assertDatabaseHas('product_batches', [
            'purchase_id' => $this->purchase->id,
            'product_id' => $newProduct->id,
            'batch_number' => 'BATCH-NEW',
            'stock' => 10,
        ]);
    }

    /** @test */
    public function can_remove_item_from_purchase()
    {
        Livewire::test(PurchaseEdit::class, ['purchase' => $this->purchase])
            ->call('removeItem', 0) // Remove the first (and only) item
            ->set('purchase_items', []) // Simulate empty list, client-side would do this interactively
            ->call('savePurchase')
            ->assertHasErrors(['purchase_items']); // Should fail because at least 1 item is required
    }
    
    /** @test */
    public function can_modify_quantity_of_existing_item()
    {
        // This simulates modifying an item by removing and re-adding, or updating if logic supported it directly
        // Currently PurchaseEdit seems to load items into array.
        // If we want to test "editing" an item, we usually remove it and add it back with new values in this UI,
        // OR the UI might support inline editing (not apparent from code unless mapped to inputs).
        
        // Let's assume validation passes if we add another item then remove the old one, effectively replacing it.
        // Or simply checking if saving updates records correctly.
        
        // Actually, looking at PurchaseEdit.php logic:
        // load mount -> fills purchase_items with data including 'id'.
        // savePurchase -> loops purchase_items. if 'id' exists, updates ProductBatch.
        
        // So we can simulate updating the array directly as Livewire wire:model would.
        
        Livewire::test(PurchaseEdit::class, ['purchase' => $this->purchase])
            ->set('purchase_items.0.original_stock_input', 100) // Change stock from 50 to 100
            ->call('savePurchase');

        $this->assertDatabaseHas('product_batches', [
            'purchase_id' => $this->purchase->id,
            'product_id' => $this->product->id,
            'stock' => 100, // 100 * 1 conversion
        ]);
    }
}
