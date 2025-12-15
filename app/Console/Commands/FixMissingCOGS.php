<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\JournalEntry;
use App\Models\TransactionDetail;
use App\Services\JournalService;
use Illuminate\Support\Facades\DB;

class FixMissingCOGS extends Command
{
    protected $signature = 'finance:fix-missing-cogs';
    protected $description = 'Fix missing COGS journals by updating product purchase prices and re-syncing';

    public function handle()
    {
        $this->info('🔧 Fixing Missing COGS Journals...');
        $this->newLine();

        // Step 1: Update purchase prices
        $this->info('Step 1: Updating product purchase prices from batch data...');
        
        $nullCount = Product::whereNull('purchase_price')
            ->orWhere('purchase_price', 0)
            ->count();
        
        $this->warn("Products without purchase_price: {$nullCount}");
        
        if ($nullCount > 0) {
            DB::statement("
                UPDATE products 
                SET purchase_price = COALESCE(
                    (SELECT AVG(purchase_price) 
                     FROM product_batches 
                     WHERE product_id = products.id 
                     AND purchase_price > 0),
                    purchase_price
                )
                WHERE (purchase_price IS NULL OR purchase_price = 0)
                AND EXISTS (SELECT 1 FROM product_batches WHERE product_id = products.id)
            ");
            
            $stillNull = Product::whereNull('purchase_price')
                ->orWhere('purchase_price', 0)
                ->count();
            
            $fixed = $nullCount - $stillNull;
            $this->info("✅ Fixed {$fixed} products");
            
            if ($stillNull > 0) {
                $this->warn("⚠️  {$stillNull} products still have no price (no batch data available)");
            }
        }
        
        $this->newLine();
        
        // Step 2: Find transaction details without COGS journals
        $this->info('Step 2: Finding transaction details without COGS journals...');
        
        $detailsWithoutCOGS = TransactionDetail::with(['transaction', 'product', 'transactionDetailBatches.productBatch'])
            ->whereDoesntHave('transaction.journalEntries', function($q) {
                // This is a simplified check - we'll verify per detail below
            })
            ->get()
            ->filter(function($detail) {
                $cogsRef = 'COGS-' . $detail->transaction->invoice_number . '-' . $detail->id;
                return !JournalEntry::where('reference_number', $cogsRef)->exists();
            });
        
        $this->info("Found {$detailsWithoutCOGS->count()} details without COGS journals");
        
        if ($detailsWithoutCOGS->count() > 0) {
            $this->newLine();
            $this->info('Step 3: Creating missing COGS journals...');
            
            $bar = $this->output->createProgressBar($detailsWithoutCOGS->count());
            $bar->start();
            
            $created = 0;
            $skipped = 0;
            
            foreach ($detailsWithoutCOGS as $detail) {
                // Calculate COGS
                $cogs = 0;
                
                if ($detail->transactionDetailBatches && $detail->transactionDetailBatches->count() > 0) {
                    foreach ($detail->transactionDetailBatches as $batchPivot) {
                        if ($batchPivot->productBatch) {
                            $cogs += $batchPivot->quantity * $batchPivot->productBatch->purchase_price;
                        }
                    }
                } else {
                    // Fallback
                    if ($detail->product && $detail->product->purchase_price > 0) {
                        $cogs = $detail->quantity * $detail->product->purchase_price;
                    }
                }
                
                if ($cogs > 0) {
                    $cogsRef = 'COGS-' . $detail->transaction->invoice_number . '-' . $detail->id;
                    
                    JournalService::createEntry(
                        $detail->transaction->created_at->format('Y-m-d'),
                        $cogsRef,
                        'HPP ' . optional($detail->product)->name . ' (Fix)',
                        [
                            ['account_code' => '501', 'amount' => $cogs]
                        ],
                        [
                            ['account_code' => '104', 'amount' => $cogs]
                        ]
                    );
                    
                    $created++;
                } else {
                    $skipped++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("✅ Created {$created} COGS journals");
            
            if ($skipped > 0) {
                $this->warn("⚠️  Skipped {$skipped} details (COGS still = 0)");
            }
        }
        
        $this->newLine();
        $this->info('🎉 Done!');
        
        return 0;
    }
}
