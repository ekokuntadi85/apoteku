<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductBatch;

class FixZeroPriceBatches extends Command
{
    protected $signature = 'batch:fix-zero-prices {--dry-run : Run without making changes}';
    protected $description = 'Fix product batches with zero  price by setting to last known price';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Finding batches with zero or null purchase_price...');
        
        $zeroPriceBatches = ProductBatch::where(function($query) {
            $query->whereNull('purchase_price')
                  ->orWhere('purchase_price', '<=', 0);
        })->get();
        
        $this->info("Found {$zeroPriceBatches->count()} batches with zero price");
        
        if ($zeroPriceBatches->count() == 0) {
            $this->info('✅ No batches need fixing!');
            return 0;
        }
        
        $fixed = 0;
        $failed = 0;
        
        foreach ($zeroPriceBatches as $batch) {
            // Find last known price for this product
            $lastBatchWithPrice = ProductBatch::where('product_id', $batch->product_id)
                ->where('id', '!=', $batch->id)
                ->where('purchase_price', '>', 0)
                ->orderBy('created_at', 'desc')
                ->first();
            
            if ($lastBatchWithPrice) {
                $newPrice = $lastBatchWithPrice->purchase_price;
                
                $this->line(sprintf(
                    'Batch ID %d (%s): Rp 0 → Rp %s',
                    $batch->id,
                    $batch->batch_number,
                    number_format($newPrice, 0, ',', '.')
                ));
                
                if (!$dryRun) {
                    $batch->update(['purchase_price' => $newPrice]);
                }
                
                $fixed++;
            } else {
                $this->error(sprintf(
                    'Batch ID %d (%s): No price found for product ID %d!',
                    $batch->id,
                    $batch->batch_number,
                    $batch->product_id
                ));
                $failed++;
            }
        }
        
        $this->newLine();
        
        if ($dryRun) {
            $this->info("DRY RUN SUMMARY:");
            $this->info("✅ Would fix: {$fixed} batches");
            $this->error("❌ Cannot fix: {$failed} batches (no price history)");
            $this->newLine();
            $this->info("Run without --dry-run to apply changes:");
            $this->line("php artisan batch:fix-zero-prices");
        } else {
            $this->info("RESULTS:");
            $this->info("✅ Fixed: {$fixed} batches");
            if ($failed > 0) {
                $this->error("❌ Failed: {$failed} batches (no price history)");
                $this->warn("Manual price update required for failed batches");
            }
        }
        
        return 0;
    }
}
