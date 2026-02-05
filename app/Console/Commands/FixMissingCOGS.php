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

        // Find transaction details without COGS journals
        $this->info('Step 1: Finding transaction details without COGS journals...');
        
        $allDetails = TransactionDetail::with(['transaction', 'product', 'transactionDetailBatches.productBatch'])
            ->get();
        
        $detailsWithoutCOGS = $allDetails->filter(function($detail) {
            $cogsRef = 'COGS-' . $detail->transaction->invoice_number . '-' . $detail->id;
            return !JournalEntry::where('reference_number', $cogsRef)->exists();
        });
        
        $this->info("Found {$detailsWithoutCOGS->count()} details without COGS journals");
        
        if ($detailsWithoutCOGS->count() > 0) {
            $this->newLine();
            $this->info('Step 2: Creating missing COGS journals...');
            
            $bar = $this->output->createProgressBar($detailsWithoutCOGS->count());
            $bar->start();
            
            $created = 0;
            $skipped = 0;
            $noBatchData = 0;
            
            foreach ($detailsWithoutCOGS as $detail) {
                // Calculate COGS from batch data
                $cogs = 0;
                
                if ($detail->transactionDetailBatches && $detail->transactionDetailBatches->count() > 0) {
                    foreach ($detail->transactionDetailBatches as $batchPivot) {
                        if ($batchPivot->productBatch) {
                            $cogs += $batchPivot->quantity * $batchPivot->productBatch->purchase_price;
                        }
                    }
                } else {
                    // No batch data - try to get from latest batch of this product
                    $latestBatch = \App\Models\ProductBatch::where('product_id', $detail->product_id)
                        ->where('purchase_price', '>', 0)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    
                    if ($latestBatch) {
                        $cogs = $detail->quantity * $latestBatch->purchase_price;
                    } else {
                        $noBatchData++;
                    }
                }
                
                if ($cogs > 0) {
                    $cogsRef = 'COGS-' . $detail->transaction->invoice_number . '-' . $detail->id;
                    
                    try {
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
                    } catch (\Exception $e) {
                        $this->error("Error creating journal for detail #{$detail->id}: " . $e->getMessage());
                        $skipped++;
                    }
                } else {
                    $skipped++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("✅ Created {$created} COGS journals");
            
            if ($skipped > 0) {
                $this->warn("⚠️  Skipped {$skipped} details (COGS = 0)");
            }
            
            if ($noBatchData > 0) {
                $this->warn("⚠️  {$noBatchData} details have no batch data at all");
            }
        } else {
            $this->info('✅ All transaction details already have COGS journals!');
        }
        
        $this->newLine();
        $this->info('🎉 Done!');
        
        return 0;
    }
}
