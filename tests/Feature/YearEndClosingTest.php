<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\Supplier;
use App\Models\Customer;
use App\Models\Category;
use App\Models\Unit;
use App\Models\ProductUnit;
use App\Models\YearEndClosing;
use App\Models\JournalEntry;
use App\Models\Expense;
use App\Services\YearEndClosingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class YearEndClosingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a super admin user
        $this->user = User::factory()->create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
        ]);
        
        $this->service = new YearEndClosingService();
    }

    /**
     * Test: Create sample 2024 data, execute closing, verify opening balance
     *
     * @test
     */
    public function it_transfers_2024_final_balance_to_2025_opening_balance()
    {
        // ==================== STEP 1: Setup Master Data ====================
        
        // Create category
        $category = Category::create(['name' => 'Obat']);
        
        // Create unit
        $unit = Unit::create(['name' => 'Box']);
        
        // Create products
        $product1 = Product::create([
            'name' => 'Paracetamol 500mg',
            'active_substance' => 'Paracetamol',
            'dosage_form' => 'Tablet',
            'sku' => 'MED001',
            'selling_price' => 10000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        
        $product2 = Product::create([
            'name' => 'Amoxicillin 500mg',
            'active_substance' => 'Amoxicillin',
            'dosage_form' => 'Kapsul',
            'sku' => 'MED002',
            'selling_price' => 15000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        
        // Create product units (base units)
        $productUnit1 = ProductUnit::create([
            'product_id' => $product1->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
        ]);
        
        $productUnit2 = ProductUnit::create([
            'product_id' => $product2->id,
            'unit_id' => $unit->id,
            'conversion_factor' => 1,
            'is_base_unit' => true,
        ]);
        
        // Create supplier
        $supplier = Supplier::create([
            'name' => 'PT Pharma Supplier',
            'phone' => '081234567890',
            'address' => 'Jakarta',
        ]);
        
        // Create customer
        $customer = Customer::create([
            'name' => 'Toko ABC',
            'phone' => '081234567890',
            'address' => 'Bandung',
        ]);
        
        // ==================== STEP 2: Create 2024 Purchases ====================
        // Set time to 2024
        Carbon::setTestNow(Carbon::create(2024, 6, 15));
        
        $purchase1 = Purchase::create([
            'invoice_number' => 'PUR-2024-001',
            'purchase_date' => Carbon::create(2024, 6, 15),
            'total_price' => 5000000,
            'payment_status' => 'paid',
            'supplier_id' => $supplier->id,
        ]);
        
        // Create product batches from purchases
        $batch1 = ProductBatch::create([
            'purchase_id' => $purchase1->id,
            'product_id' => $product1->id,
            'product_unit_id' => $productUnit1->id,
            'batch_number' => 'BATCH-2024-001',
            'stock' => 100, // Initial stock: 100 boxes
            'purchase_price' => 8000,
            'expiration_date' => Carbon::create(2026, 12, 31),
        ]);
        
        $batch2 = ProductBatch::create([
            'purchase_id' => $purchase1->id,
            'product_id' => $product2->id,
            'product_unit_id' => $productUnit2->id,
            'batch_number' => 'BATCH-2024-002',
            'stock' => 50, // Initial stock: 50 boxes
            'purchase_price' => 12000,
            'expiration_date' => Carbon::create(2026, 6, 30),
        ]);
        
        // ==================== STEP 3: Create 2024 Sales Transactions ====================
        $transaction1 = Transaction::create([
            'type' => 'pos',
            'payment_status' => 'paid',
            'total_price' => 500000,
            'grand_total' => 500000,
            'amount_paid' => 500000,
            'change' => 0,
            'invoice_number' => 'TRX-2024-001',
            'user_id' => $this->user->id,
            'customer_id' => $customer->id,
            'created_at' => Carbon::create(2024, 7, 1),
        ]);
        
        TransactionDetail::create([
            'transaction_id' => $transaction1->id,
            'product_id' => $product1->id,
            'product_unit_id' => $productUnit1->id,
            'quantity' => 30, // Sell 30 boxes
            'price' => 10000,
            'subtotal' => 300000,
        ]);
        
        TransactionDetail::create([
            'transaction_id' => $transaction1->id,
            'product_id' => $product2->id,
            'product_unit_id' => $productUnit2->id,
            'quantity' => 10, // Sell 10 boxes
            'price' => 15000,
            'subtotal' => 150000,
        ]);
        
        // Manually reduce stock (simulating what StockService would do)
        $batch1->decrement('stock', 30); // 100 - 30 = 70
        $batch2->decrement('stock', 10); // 50 - 10 = 40
        
        // ==================== STEP 4: Verify Data Before Closing ====================
        $this->assertEquals(1, Purchase::count(), 'Should have 1 purchase');
        $this->assertEquals(1, Transaction::count(), 'Should have 1 transaction');
        $this->assertEquals(2, ProductBatch::count(), 'Should have 2 batches');
        $this->assertEquals(70, $batch1->fresh()->stock, 'Batch 1 should have 70 stock remaining');
        $this->assertEquals(40, $batch2->fresh()->stock, 'Batch 2 should have 40 stock remaining');
        
        $totalStockValue = (70 * 8000) + (40 * 12000); // 560,000 + 480,000 = 1,040,000
        
        // ==================== STEP 5: Execute Year-End Closing ====================
        // Reset time to 2025 (can only close previous year)
        Carbon::setTestNow(Carbon::create(2025, 1, 1));
        
        $closing = $this->service->executeClosing(2024, $this->user->id);
        
        // ==================== STEP 6: Verify Closing Record ====================
        $this->assertNotNull($closing, 'Closing record should be created');
        $this->assertEquals('completed', $closing->status, 'Closing should be completed');
        $this->assertEquals(2024, $closing->closing_year, 'Should close year 2024');
        $this->assertEquals(1, $closing->total_transactions_deleted, 'Should delete 1 transaction');
        $this->assertEquals(1, $closing->total_purchases_deleted, 'Should delete 1 purchase');
        
        // ==================== STEP 7: Verify Old Data Deleted ====================
        $this->assertEquals(0, Transaction::count(), 'All transactions should be deleted');
        $this->assertEquals(1, Purchase::count(), 'Should have only opening balance purchase');
        $this->assertEquals(0, JournalEntry::count(), 'All journal entries should be deleted');
        
        // ==================== STEP 8: Verify Opening Balance Purchase ====================
        $openingPurchase = Purchase::first();
        
        $this->assertNotNull($openingPurchase, 'Opening purchase should exist');
        $this->assertEquals('SA-2025', $openingPurchase->invoice_number, 'Invoice should be SA-2025');
        $this->assertEquals('2025-01-01', $openingPurchase->purchase_date->format('Y-m-d'), 'Date should be Jan 1, 2025');
        $this->assertEquals('paid', $openingPurchase->payment_status, 'Should be paid');
        $this->assertEquals('SALDO AWAL SISTEM', $openingPurchase->supplier->name, 'Supplier should be system');
        $this->assertEquals($totalStockValue, $openingPurchase->total_price, 'Total should match stock value');
        
        // ==================== STEP 9: Verify Opening Balance Batches ====================
        $openingBatches = ProductBatch::where('purchase_id', $openingPurchase->id)
            ->orderBy('product_id')
            ->get();
        
        $this->assertCount(2, $openingBatches, 'Should have 2 opening batches');
        
        // Check Product 1 opening batch
        $openingBatch1 = $openingBatches->where('product_id', $product1->id)->first();
        $this->assertNotNull($openingBatch1, 'Opening batch 1 should exist');
        $this->assertEquals(70, $openingBatch1->stock, 'Opening stock should be 70 (final balance from 2024)');
        $this->assertEquals(8000, $openingBatch1->purchase_price, 'Price should be preserved');
        $this->assertEquals('2026-12-31', $openingBatch1->expiration_date->format('Y-m-d'), 'Expiry should be preserved');
        $this->assertStringContainsString('SA-2025', $openingBatch1->batch_number, 'Batch number should contain SA-2025');
        
        // Check Product 2 opening batch
        $openingBatch2 = $openingBatches->where('product_id', $product2->id)->first();
        $this->assertNotNull($openingBatch2, 'Opening batch 2 should exist');
        $this->assertEquals(40, $openingBatch2->stock, 'Opening stock should be 40 (final balance from 2024)');
        $this->assertEquals(12000, $openingBatch2->purchase_price, 'Price should be preserved');
        $this->assertEquals('2026-06-30', $openingBatch2->expiration_date->format('Y-m-d'), 'Expiry should be preserved');
        $this->assertStringContainsString('SA-2025', $openingBatch2->batch_number, 'Batch number should contain SA-2025');
        
        // ==================== STEP 10: Verify Master Data Preserved ====================
        $this->assertEquals(2, Product::count(), 'Products should be preserved');
        $this->assertEquals(1, Customer::count(), 'Customer should be preserved');
        $this->assertEquals(2, Supplier::count(), 'Suppliers should be preserved (original + system)');
        $this->assertEquals(1, Category::count(), 'Category should be preserved');
        $this->assertEquals(1, Unit::count(), 'Unit should be preserved');
        
        // ==================== STEP 11: Verify Total Stock Value ====================
        $calculatedValue = $openingBatches->sum(function ($batch) {
            return $batch->stock * $batch->purchase_price;
        });
        
        $this->assertEquals($totalStockValue, $calculatedValue, 'Total stock value should match');
        $this->assertEquals($openingPurchase->total_price, $calculatedValue, 'Purchase total should match calculated value');
        
        // ==================== SUCCESS ====================
        $this->assertTrue(true, '✅ Year-end closing test passed! Final balance correctly transferred to opening balance.');
        
        // Reset time
        Carbon::setTestNow();
    }

    /**
     * Test: Validate cannot close current year
     *
     * @test
     */
    public function it_prevents_closing_current_year()
    {
        Carbon::setTestNow(Carbon::create(2025, 1, 1));
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Tidak dapat menutup tahun 2025');
        
        $this->service->validateClosing(2025);
        
        Carbon::setTestNow();
    }

    /**
     * Test: Validate cannot close already closed year
     *
     * @test
     */
    public function it_prevents_closing_already_closed_year()
    {
        // Create existing closing record
        YearEndClosing::create([
            'closing_year' => 2024,
            'closed_by' => $this->user->id,
            'status' => 'completed',
            'closed_at' => now(),
        ]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('sudah pernah ditutup');
        
        $this->service->validateClosing(2024);
    }

    /**
     * Test: Rollback on error
     *
     * @test
     */
    public function it_rolls_back_on_error()
    {
        // Create some test data
        $category = Category::create(['name' => 'Test']);
        $unit = Unit::create(['name' => 'Pcs']);
        $supplier = Supplier::create(['name' => 'Test Supplier', 'phone' => '123', 'address' => 'Test']);
        
        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TEST001',
            'selling_price' => 10000,
            'category_id' => $category->id,
            'unit_id' => $unit->id,
        ]);
        
        $purchase = Purchase::create([
            'invoice_number' => 'TEST-001',
            'purchase_date' => now(),
            'total_price' => 10000,
            'supplier_id' => $supplier->id,
            'payment_status' => 'paid',
        ]);
        
        $initialPurchaseCount = Purchase::count();
        
        // Try to close with invalid year (should fail validation)
        try {
            Carbon::setTestNow(Carbon::create(2025, 1, 1));
            $this->service->executeClosing(2025, $this->user->id);
        } catch (\Exception $e) {
            // Expected to throw exception
        }
        
        // Verify rollback - purchase count should remain same
        $this->assertEquals($initialPurchaseCount, Purchase::count(), 'Data should be rolled back on error');
        
        Carbon::setTestNow();
    }
}
