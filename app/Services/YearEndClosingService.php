<?php

namespace App\Services;

use App\Models\YearEndClosing;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\ProductBatch;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;
use App\Models\StockMovement;
use App\Models\JournalEntry;
use App\Models\JournalDetail;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class YearEndClosingService
{
    /**
     * Validate if year closing can be performed
     * 
     * @param int $year Year to close (e.g., 2024)
     * @throws \Exception if validation fails
     */
    public function validateClosing(int $year): void
    {
        $currentYear = now()->year;
        
        // Cannot close current or future years
        if ($year >= $currentYear) {
            throw new \Exception("Tidak dapat menutup tahun {$year}. Hanya tahun lalu yang dapat ditutup.");
        }
        
        // Check if already closed successfully
        $existing = YearEndClosing::where('closing_year', $year)->first();
        if ($existing && $existing->status === 'completed') {
            throw new \Exception("Tahun {$year} sudah pernah ditutup sebelumnya.");
        }
        
        // Check if there's a different closing in progress
        $processing = YearEndClosing::where('closing_year', '!=', $year)
            ->where('status', 'processing')
            ->first();
        if ($processing) {
            throw new \Exception("Terdapat proses tutup tahun yang sedang berjalan (Tahun {$processing->closing_year}).");
        }
        
        Log::info("Year-end closing validation passed for year {$year}");
    }

    /**
     * Create database backup before closing
     * 
     * @return string Path to backup file
     * @throws \Exception if backup fails
     */
    public function createDatabaseBackup(): string
    {
        Log::info("Creating database backup before year-end closing...");
        
        try {
            // Run the existing backup command
            Artisan::call('backup:auto');
            
            $output = Artisan::output();
            Log::info("Backup command output: {$output}");
            
            // Try to extract backup file path from output
            // The backup command should return the backup file name
            if (preg_match('/backup-(\d{4}-\d{2}-\d{2}-\d{6})/', $output, $matches)) {
                $backupFileName = "backup-{$matches[1]}.sql.gz";
                Log::info("Database backup created: {$backupFileName}");
                return $backupFileName;
            }
            
            // Fallback: use current timestamp
            $backupFileName = "backup-before-closing-" . now()->format('Y-m-d-His') . ".sql.gz";
            Log::info("Using fallback backup file name: {$backupFileName}");
            return $backupFileName;
            
        } catch (\Exception $e) {
            Log::error("Failed to create database backup: {$e->getMessage()}");
            throw new \Exception("Gagal membuat backup database: {$e->getMessage()}");
        }
    }

    /**
     * Create opening balance purchase for the new year
     * 
     * @param int $closingYear Year being closed (e.g., 2024)
     * @return Purchase The opening balance purchase
     */
    public function createOpeningBalance(int $closingYear): Purchase
    {
        Log::info("Creating opening balance for year " . ($closingYear + 1));
        
        $newYear = $closingYear + 1;
        
        // 1. Find or create "Saldo Awal Sistem" supplier
        $supplier = Supplier::firstOrCreate(
            ['name' => 'SALDO AWAL SISTEM'],
            [
                'phone' => '-',
                'address' => 'Sistem',
            ]
        );
        
        Log::info("Using supplier: {$supplier->name} (ID: {$supplier->id})");
        
        // 2. Create opening balance purchase
        $invoiceNumber = "SA-{$newYear}";
        $purchaseDate = Carbon::create($newYear, 1, 1);
        
        $purchase = Purchase::create([
            'invoice_number' => $invoiceNumber,
            'purchase_date' => $purchaseDate,
            'total_price' => 0, // Will be calculated after batches are created
            'payment_status' => 'paid',
            'supplier_id' => $supplier->id,
        ]);
        
        Log::info("Created opening balance purchase: {$invoiceNumber} (ID: {$purchase->id})");
        
        // 3. Get all product batches with stock > 0
        $activeBatches = ProductBatch::where('stock', '>', 0)->get();
        
        Log::info("Found {$activeBatches->count()} active batches to convert");
        
        $totalValue = 0;
        
        // 4. Create new batches linked to opening balance purchase
        foreach ($activeBatches as $oldBatch) {
            // Get purchase price - use old batch price, or fallback to last known price
            $purchasePrice = $oldBatch->purchase_price;
            
            // CRITICAL: If old batch has no price (e.g. from manual opname), get last known price
            if (!$purchasePrice || $purchasePrice <= 0) {
                // Find most recent batch for this product with a valid price
                $lastBatchWithPrice = ProductBatch::where('product_id', $oldBatch->product_id)
                    ->where('purchase_price', '>', 0)
                    ->orderBy('created_at', 'desc')
                    ->first();
                
                if ($lastBatchWithPrice) {
                    $purchasePrice = $lastBatchWithPrice->purchase_price;
                    Log::warning("Batch {$oldBatch->batch_number} has no price, using last known price: {$purchasePrice}");
                } else {
                    // FALLBACK 2: Estimate from product master selling_price (70% ratio)
                    $product = $oldBatch->product;
                    if ($product && $product->selling_price > 0) {
                        $purchasePrice = $product->selling_price * 0.7;
                        Log::warning("Batch {$oldBatch->batch_number}: no batch history, estimated from selling price (70% = {$purchasePrice})");
                    } else {
                        // LAST RESORT: Set to 0 and log critical error
                        $purchasePrice = 0;
                        Log::error("No price found for product {$oldBatch->product_id} batch {$oldBatch->batch_number}!");
                    }
                }
            }
            
            $newBatch = ProductBatch::create([
                'purchase_id' => $purchase->id,
                'product_id' => $oldBatch->product_id,
                'product_unit_id' => $oldBatch->product_unit_id,
                'batch_number' => "SA-{$newYear}-" . str_replace('SA-', '', $oldBatch->batch_number),
                'stock' => $oldBatch->stock,
                'purchase_price' => $purchasePrice, // Use calculated price
                'expiration_date' => $oldBatch->expiration_date,
            ]);
            
            // Calculate total value
            $batchValue = $oldBatch->stock * $purchasePrice;
            $totalValue += $batchValue;
            
            Log::info("Created opening batch: {$newBatch->batch_number} for product {$oldBatch->product_id}, Stock: {$oldBatch->stock}, Price: {$purchasePrice}");
        }
        
        // 5. Update purchase total price
        $purchase->update(['total_price' => $totalValue]);
        
        Log::info("Opening balance total value: " . number_format($totalValue, 2));
        Log::info("Opening balance created successfully");
        
        return $purchase;
    }

    /**
     * Delete all transaction data
     * 
     * @return array Statistics of deleted records
     */
    public function deleteAllTransactionData(): array
    {
        Log::info("Starting deletion of all transaction data...");
        
        $stats = [
            'transactions' => 0,
            'transaction_details' => 0,
            'transaction_detail_batches' => 0,
            'purchases' => 0,
            'product_batches' => 0,
            'stock_movements' => 0,
            'journal_entries' => 0,
            'journal_details' => 0,
            'expenses' => 0,
            'payments' => 0,
        ];
        
        // Delete in correct order to respect foreign key constraints
        
        // 1. Transaction Detail Batches (child of transaction_details)
        $stats['transaction_detail_batches'] = TransactionDetailBatch::count();
        TransactionDetailBatch::query()->delete();
        Log::info("Deleted {$stats['transaction_detail_batches']} transaction detail batches");
        
        // 2. Transaction Details (child of transactions)
        $stats['transaction_details'] = TransactionDetail::count();
        TransactionDetail::query()->delete();
        Log::info("Deleted {$stats['transaction_details']} transaction details");
        
        // 3. Payments (child of transactions)
        $stats['payments'] = Payment::count();
        Payment::query()->delete();
        Log::info("Deleted {$stats['payments']} payments");
        
        // 4. Transactions
        $stats['transactions'] = Transaction::count();
        Transaction::query()->delete();
        Log::info("Deleted {$stats['transactions']} transactions");
        
        // 5. Product Batches with stock = 0 (old batches that are depleted)
        $zeroStockBatches = ProductBatch::where('stock', 0)->count();
        ProductBatch::where('stock', 0)->delete();
        $stats['product_batches'] = $zeroStockBatches;
        Log::info("Deleted {$zeroStockBatches} product batches with zero stock");
        
        // 6. Purchases (all will be deleted, opening balance will be the only one left)
        $stats['purchases'] = Purchase::count();
        // Purchase::query()->delete(); // DISABLED: Will be deleted manually in executeClosing() to preserve opening balance
        Log::info("Counted {$stats['purchases']} purchases (deletion handled separately)");
        
        // 7. Stock Movements (all movement logs)
        $stats['stock_movements'] = StockMovement::count();
        StockMovement::query()->delete();
        Log::info("Deleted {$stats['stock_movements']} stock movements");
        
        // 8. Journal Details (child of journal_entries)
        $stats['journal_details'] = JournalDetail::count();
        JournalDetail::query()->delete();
        Log::info("Deleted {$stats['journal_details']} journal details");
        
        // 9. Journal Entries
        $stats['journal_entries'] = JournalEntry::count();
        JournalEntry::query()->delete();
        Log::info("Deleted {$stats['journal_entries']} journal entries");
        
        // 10. Expenses
        $stats['expenses'] = Expense::count();
        Expense::query()->delete();
        Log::info("Deleted {$stats['expenses']} expenses");
        
        // 11. Stock Opname Details (child of stock_opnames)
        $opnameDetails = \App\Models\StockOpnameDetail::count();
        \App\Models\StockOpnameDetail::query()->delete();
        Log::info("Deleted {$opnameDetails} stock opname details");
        
        // 12. Stock Opnames
        $opnames = \App\Models\StockOpname::count();
        \App\Models\StockOpname::query()->delete();
        Log::info("Deleted {$opnames} stock opnames");
        
        
        Log::info("All transaction data deleted successfully");
        
        return $stats;
    }

    /**
     * Execute year-end closing process
     * 
     * @param int $closingYear Year to close (e.g., 2024)
     * @param int $userId User performing the closing
     * @return YearEndClosing The closing record
     */
    public function executeClosing(int $closingYear, int $userId): YearEndClosing
    {
        Log::info("=== STARTING YEAR-END CLOSING FOR YEAR {$closingYear} ===");
        Log::info("User ID: {$userId}");
        
        // Create initial record
        $closing = YearEndClosing::create([
            'closing_year' => $closingYear,
            'closed_by' => $userId,
            'status' => 'processing',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Step 1: Validate
            Log::info("Step 1: Validating...");
            $this->validateClosing($closingYear);
            
            // Step 2: Create backup
            Log::info("Step 2: Creating database backup...");
            $backupFile = $this->createDatabaseBackup();
            $closing->update(['backup_file_path' => $backupFile]);
            
            // Step 3: Create opening balance (BEFORE deleting data)
            Log::info("Step 3: Creating opening balance...");
            $openingPurchase = $this->createOpeningBalance($closingYear);
            $closing->update(['opening_purchase_id' => $openingPurchase->id]);
            
            // Step 3.5 moved to Step 5.7 (after all deletions)
            
            // Step 4: Delete old product batches (with stock > 0 that were converted)
            // These are the OLD batches that we just converted to opening balance
            // CRITICAL: Also delete orphan batches (purchase_id = NULL)
            Log::info("Step 4: Deleting old product batches (converted to opening balance)...");
            $oldBatchesCount = ProductBatch::where('stock', '>', 0)
                ->where(function($query) use ($openingPurchase) {
                    $query->where('purchase_id', '!=', $openingPurchase->id)
                          ->orWhereNull('purchase_id');
                })
                ->count();
            ProductBatch::where('stock', '>', 0)
                ->where(function($query) use ($openingPurchase) {
                    $query->where('purchase_id', '!=', $openingPurchase->id)
                          ->orWhereNull('purchase_id');
                })
                ->delete();
            Log::info("Deleted {$oldBatchesCount} old batches including orphans (now in opening balance)");
            
            // Step 5: Delete all transaction data
            Log::info("Step 5: Deleting all transaction data...");
            $stats = $this->deleteAllTransactionData();
            
            // Step 5.5: Delete old purchases EXCEPT opening balance
            Log::info("Step 5.5: Deleting old purchases (preserving opening balance)...");
            $oldPurchasesCount = Purchase::where('id', '!=', $openingPurchase->id)->count();
            Purchase::where('id', '!=', $openingPurchase->id)->delete();
            Log::info("Deleted {$oldPurchasesCount} old purchases (preserved opening balance purchase ID {$openingPurchase->id})");
            $stats['purchases'] = $oldPurchasesCount;
            
            // Step 5.7: Create opening balance journal entry (AFTER all deletions!)
            Log::info("Step 5.7: Creating opening balance journal entry...");
            $journalEntry = \App\Models\JournalEntry::create([
                'transaction_date' => $openingPurchase->purchase_date,
                'reference_number' => 'OB-' . ($closingYear + 1),
                'description' => 'Saldo Awal Persediaan Tahun ' . ($closingYear + 1),
                'total_amount' => $openingPurchase->total_price,
            ]);
            
            // Get account IDs
            $inventoryAccount = \App\Models\Account::where('code', '104')->first();
            $equityAccount = \App\Models\Account::where('code', '301')->first();
            
            if (!$inventoryAccount || !$equityAccount) {
                throw new \Exception('Required accounts (104, 301) not found in chart of accounts');
            }
            
            // Debit: Inventory (Asset)
            \App\Models\JournalDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $inventoryAccount->id,
                'debit' => $openingPurchase->total_price,
                'credit' => 0,
            ]);
            
            // Credit: Owner's Equity
            \App\Models\JournalDetail::create([
                'journal_entry_id' => $journalEntry->id,
                'account_id' => $equityAccount->id,
                'debit' => 0,
                'credit' => $openingPurchase->total_price,
            ]);
            
            Log::info("Opening balance journal created: {$journalEntry->reference_number}, Amount: " . number_format($openingPurchase->total_price));
            
            // Step 6: Update closing record with statistics
            $closing->update([
                'total_transactions_deleted' => $stats['transactions'],
                'total_purchases_deleted' => $stats['purchases'],
                'total_journal_entries_deleted' => $stats['journal_entries'],
                'total_expenses_deleted' => $stats['expenses'],
                'total_payments_deleted' => $stats['payments'],
                'total_stock_movements_deleted' => $stats['stock_movements'],
                'closed_at' => now(),
                'status' => 'completed',
            ]);
            
            DB::commit();
            
            Log::info("=== YEAR-END CLOSING COMPLETED SUCCESSFULLY ===");
            Log::info("Summary:");
            Log::info("- Transactions deleted: {$stats['transactions']}");
            Log::info("- Purchases deleted: {$stats['purchases']}");
            Log::info("- Journal entries deleted: {$stats['journal_entries']}");
            Log::info("- Expenses deleted: {$stats['expenses']}");
            Log::info("- Opening balance purchase ID: {$openingPurchase->id}");
            
            return $closing->fresh();
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Year-end closing failed: {$e->getMessage()}");
            Log::error("Stack trace: {$e->getTraceAsString()}");
            
            $closing->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }
}
