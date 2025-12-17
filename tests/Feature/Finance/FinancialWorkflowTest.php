<?php

namespace Tests\Feature\Finance;

use App\Livewire\FinancialReports\IncomeStatement;
use App\Models\Category;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductUnit;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FinancialWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Seed necessary data
        $this->seed(\Database\Seeders\AccountSeeder::class);
    }

    /** @test */
    public function it_simulates_financial_workflow_and_validates_report()
    {
        // 1. Setup Master Data (Product, Unit, Category)
        $unit = Unit::create(['name' => 'Pcs', 'short_name' => 'pcs']);
        $category = Category::create(['name' => 'Obat Bebas', 'description' => 'Obat tanpa resep']);
        
        $product = Product::create([
            'name' => 'Paracetamol 500mg',
            'sku' => 'PCT500',
            'category_id' => $category->id,
            'active_substance' => 'Paracetamol',
            'dosage_form' => 'Tablet',
        ]);

        $productUnit = ProductUnit::create([
            'product_id' => $product->id,
            'name' => 'Pcs',
            'conversion_factor' => 1,
            'selling_price' => 5000,
            'purchase_price' => 3000,
            'is_base_unit' => true
        ]);

        // 2. Transaction: Purchase (Pembelian Stock) = Cost
        $supplier = Supplier::create([
            'name' => 'PT Farma Sehat',
            'email' => 'contact@farmasehat.com', 
            'phone' => '08123456789'
        ]);

        $purchase = Purchase::create([
            'invoice_number' => 'INV-SUP-001',
            'purchase_date' => now(),
            'total_price' => 300000, // Bought 100 items @ 3000
            'payment_status' => 'paid',
            'supplier_id' => $supplier->id,
        ]);

        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'purchase_id' => $purchase->id,
            'batch_number' => 'BATCH001',
            'stock' => 100,
            'purchase_price' => 3000, // COGS per unit
            'expiration_date' => now()->addYear(),
            'product_unit_id' => $productUnit->id // Linked to ProductUnit
        ]);

        // 3. Transaction: Expense (Pengeluaran Operasional)
        $expenseCategory = ExpenseCategory::create(['name' => 'Listrik', 'description' => 'Biaya utilitas']);
        
        $expense = Expense::create([
            'expense_category_id' => $expenseCategory->id,
            'user_id' => $this->user->id,
            'amount' => 50000, // PLN Token
            'description' => 'Token Listrik Bulan Ini',
            'expense_date' => now(),
        ]);

        // 4. Transaction: Sales (Penjualan) = Revenue
        $transaction = Transaction::create([
            'type' => 'pos', // Correct enum value from migration
            'invoice_number' => 'INV-001',
            'total_price' => 50000, // Sold 10 items @ 5000
            'payment_status' => 'paid',
            'user_id' => $this->user->id,
        ]);

        $detail = TransactionDetail::create([
            'transaction_id' => $transaction->id,
            'product_id' => $product->id,
            'product_unit_id' => $productUnit->id, // Linked to ProductUnit
            'quantity' => 10,
            'price' => 5000,
        ]);

        // Linked automatically by StockService via Observer
        // TransactionDetailBatch::create([...]) not needed

        // 5. Validation: Check Income Statement
        // Expected:
        // Revenue: 50,000 (10 * 5000)
        // COGS: 30,000 (10 * 3000)
        // Gross Profit: 20,000
        // Expenses: 50,000
        // Net Profit: -30,000 (Loss)

        Livewire::test(IncomeStatement::class)
            ->set('period', 'this_month')
            ->assertSeeHtml(number_format(50000, 2, ',', '.')) // Revenue
            ->assertSeeHtml(number_format(30000, 2, ',', '.')) // COGS
            ->assertSeeHtml(number_format(50000, 0, ',', '.')) // Expense (Check integer format too just in case)
            ->assertViewHas('netProfit', -30000);

        // 6. Delete Transaction (Simulate Deletion)
        $transaction->delete();
        
        // After delete sale:
        // Revenue: 0
        // COGS: 0
        // Expenses: 50,000
        // Net Profit: -50,000

        Livewire::test(IncomeStatement::class)
            ->set('period', 'this_month')
            ->assertViewHas('revenue', 0)
            ->assertViewHas('cogs', 0)
            ->assertViewHas('totalExpenses', 50000.00) // Ensure float comparison
            ->assertViewHas('netProfit', -50000);

        // 7. Delete Expense
        $expense->delete();

        // After delete expense:
        // Net Profit: 0

        Livewire::test(IncomeStatement::class)
            ->set('period', 'this_month')
            ->assertViewHas('totalExpenses', 0.00)
            ->assertViewHas('netProfit', 0);
    }
}
