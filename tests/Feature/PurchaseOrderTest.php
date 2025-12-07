<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    public $user;
    public $supplier;
    public $product;
    public $unit;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user
        $this->user = User::factory()->create();
        
        // Grant permission
        \Spatie\Permission\Models\Permission::create(['name' => 'access-purchases']);
        $this->user->givePermissionTo('access-purchases');
        
        // Create supplier
        $this->supplier = Supplier::create([
            'name' => 'PT. Supplier Test',
            'address' => 'Jl. Test No. 1',
            'phone' => '08123456789',
        ]);

        // Create category
        $category = \App\Models\Category::create(['name' => 'Obat Bebas']);

        // Create product
        $this->product = Product::create([
            'name' => 'Obat Test',
            'sku' => 'TEST001',
            'barcode' => '123456789',
            'min_stock' => 10,
            'category_id' => $category->id,
        ]);

        // Create unit linked to product
        $this->unit = ProductUnit::create([
            'product_id' => $this->product->id,
            'name' => 'Box',
            'is_base_unit' => true,
            'conversion_factor' => 1,
            'purchase_price' => 50000,
            'selling_price' => 60000,
        ]);
    }

    /** @test */
    public function can_render_purchase_order_pages()
    {
        $this->actingAs($this->user)
            ->get(route('purchase-orders.index'))
            ->assertStatus(200);

        $this->actingAs($this->user)
            ->get(route('purchase-orders.create'))
            ->assertStatus(200);
    }

    /** @test */
    public function can_create_general_purchase_order()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\PurchaseOrderCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('type', 'general')
            ->call('selectProduct', $this->product->id)
            ->set('selectedProductUnitId', $this->unit->id)
            ->set('quantity', 10)
            ->set('estimated_price', 50000)
            ->set('item_notes', 'Catatan Item Test')
            ->call('addItem')
            ->call('saveOrder')
            ->assertHasNoErrors()
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $this->supplier->id,
            'type' => 'general',
            'status' => 'draft',
        ]);

        $this->assertDatabaseHas('purchase_order_details', [
            'product_id' => $this->product->id,
            'quantity' => 10,
            'notes' => 'Catatan Item Test',
        ]);
    }

    /** @test */
    public function cannot_create_oot_purchase_order_without_active_substance()
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\PurchaseOrderCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('type', 'oot')
            ->call('selectProduct', $this->product->id)
            ->set('selectedProductUnitId', $this->unit->id)
            ->set('quantity', 5)
            ->set('estimated_price', 50000)
            ->call('addItem')
            // active_substance is intentionally left empty in the item array
            ->call('saveOrder')
            ->assertHasErrors(['order_items.0.active_substance']);
            
        $this->assertDatabaseCount('purchase_orders', 0);
    }

    /** @test */
    public function can_create_oot_purchase_order_with_active_substance()
    {
        // First add item, then update the item in the array to include active_substance
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\PurchaseOrderCreate::class)
            ->set('supplier_id', $this->supplier->id)
            ->set('type', 'oot')
            ->call('selectProduct', $this->product->id)
            ->set('selectedProductUnitId', $this->unit->id)
            ->set('quantity', 5)
            ->set('estimated_price', 50000)
            ->call('addItem');

        // Simulate user selecting active substance in the table
        $orderItems = $component->get('order_items');
        $orderItems[0]['active_substance'] = 'Tramadol';
        $orderItems[0]['dosage_form'] = 'Box';
        
        $component->set('order_items', $orderItems)
            ->call('saveOrder')
            ->assertHasNoErrors()
            ->assertRedirect(route('purchase-orders.index'));

        $this->assertDatabaseHas('purchase_orders', [
            'type' => 'oot',
        ]);

        $this->assertDatabaseHas('purchase_order_details', [
            'active_substance' => 'Tramadol',
            'dosage_form' => 'Box',
        ]);
    }

    /** @test */
    public function can_edit_purchase_order()
    {
        // Create initial PO
        $po = PurchaseOrder::create([
            'po_number' => 'SP-TEST-001',
            'supplier_id' => $this->supplier->id,
            'order_date' => now(),
            'status' => 'draft',
            'type' => 'general',
        ]);

        $detail = \App\Models\PurchaseOrderDetail::create([
            'purchase_order_id' => $po->id,
            'product_id' => $this->product->id,
            'product_unit_id' => $this->unit->id,
            'quantity' => 10,
            'estimated_price' => 50000,
            'notes' => 'Old Note',
        ]);

        // Test Edit Component
        $component = Livewire::actingAs($this->user)
            ->test(\App\Livewire\PurchaseOrderEdit::class, ['purchaseOrder' => $po]);

        // Update item note
        $orderItems = $component->get('order_items');
        $orderItems[0]['notes'] = 'Updated Note';
        $orderItems[0]['quantity'] = 20;

        $component->set('order_items', $orderItems)
            ->call('updateOrder')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('purchase_order_details', [
            'purchase_order_id' => $po->id,
            'quantity' => 20,
            'notes' => 'Updated Note',
        ]);
    }
}
