<?php

namespace Tests\Feature\Livewire;

use App\Livewire\PurchaseCreate;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PurchaseCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_purchase_from_po_and_link_them()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $supplier = Supplier::create(['name' => 'Supplier Test', 'address' => 'Addr', 'phone' => '123']);
        $category = \App\Models\Category::create(['name' => 'Category Test']);
        
        $product = Product::create(['name' => 'Product Test', 'sku' => 'SKU123', 'category_id' => $category->id]);
        $unit = ProductUnit::create([
            'product_id' => $product->id,
            'name' => 'Pcs',
            'conversion_factor' => 1,
            'is_base_unit' => true,
            'purchase_price' => 1000,
            'selling_price' => 1200
        ]);

        $po = PurchaseOrder::create([
            'po_number' => 'PO-001',
            'supplier_id' => $supplier->id,
            'order_date' => now(),
            'status' => 'sent',
            'type' => 'general'
        ]);

        PurchaseOrderDetail::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'product_unit_id' => $unit->id,
            'quantity' => 10,
            'estimated_price' => 1000,
        ]);

        Livewire::test(PurchaseCreate::class)
            // Load PO
            ->call('loadPo', $po->id)
            ->assertSet('selectedPoId', $po->id)
            ->assertSet('supplier_id', $supplier->id)
            // Fill required fields
            ->set('invoice_number', 'INV-001')
            ->set('purchase_date', '2025-01-01')
            ->set('total_purchase_price', 10000)
            // Fill item details (simulate user inputting batch/expiry)
            ->set('purchase_items.0.batch_number', 'BATCH1')
            ->set('purchase_items.0.expiration_date', '2026-01-01')
            ->call('savePurchase');

        // Assert Purchase Created
        $purchase = Purchase::where('invoice_number', 'INV-001')->first();
        $this->assertNotNull($purchase);
        $this->assertEquals($po->id, $purchase->purchase_order_id);

        // Assert PO Updated
        $po->refresh();
        $this->assertEquals('completed', $po->status);

        // Assert Relationship works
        $this->assertTrue($po->purchase->is($purchase));
    }
}
