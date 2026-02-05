<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProductBatch;

class EstimateBatchPrices extends Command
{
    protected $signature = 'batch:estimate-prices {--ratio=0.7 : Ratio of selling price to use (default 70%)}';
    protected $description = 'Estimate purchase price from product selling_price for batches with zero price';

    public function handle()
    {
        $ratio = (float) $this->option('ratio');
        
        $this->info("Estimating prices for zero-price batches using {$ratio}x selling_price...");
        
        $zeroPriceBatches = ProductBatch::with('product')
            ->where(function($query) {
                $query->whereNull('purchase_price')
                      ->orWhere('purchase_price', '<=', 0);
            })
            ->get();
        
        if ($zeroPriceBatches->count() == 0) {
            $this->info('✅ No batches need estimation!');
            return 0;
        }
        
        $this->info("Found {$zeroPriceBatches->count()} batches");
        
        $estimated = 0;
        $failed = 0;
        
        foreach ($zeroPriceBatches as $batch) {
            $product = $batch->product;
            
            if ($product && $product->selling_price > 0) {
                $estimatedPrice = $product->selling_price * $ratio;
                
                $this->line(sprintf(
                    'Batch %s: Selling Rp %s → Purchase Rp %s (%d%%)',
                    $batch->batch_number,
                    number_format($product->selling_price, 0, ',', '.'),
                    number_format($estimatedPrice, 0, ',', '.'),
                    ($ratio * 100)
                ));
                
                $batch->update(['purchase_price' => $estimatedPrice]);
                $estimated++;
            } else {
                $this->error("Batch {$batch->batch_number}: No selling price for product ID {$batch->product_id}");
                $failed++;
            }
        }
        
        $this->newLine();
        $this->info("✅ Estimated: {$estimated} batches");
        
        if ($failed > 0) {
            $this->error("❌ Failed: {$failed} batches (no selling price)");
        }
        
        return 0;
    }
}
