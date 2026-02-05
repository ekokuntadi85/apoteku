<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneratePurchaseRecommendation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-purchase-recommendation
                            {--budget=1500000 : The total budget for purchasing.}
                            {--sales-period=30 : The period in days to calculate sales velocity.}
                            {--safety-stock=7 : The minimum number of days of stock to keep.}
                            {--target-stock=14 : The target number of days of stock to have after reordering.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates a prioritized purchase recommendation list based on sales velocity, current stock, and a given budget.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $budget = (float) $this->option('budget');
        $salesPeriod = (int) $this->option('sales-period');
        $safetyStockDays = (int) $this->option('safety-stock');
        $targetStockDays = (int) $this->option('target-stock');

        $this->info("Generating purchase recommendation with the following parameters:");
        $this->line("Budget: <fg=yellow>Rp " . number_format($budget, 2) . "</>");
        $this->line("Sales Period: <fg=yellow>{$salesPeriod} days</>");
        $this->line("Safety Stock: <fg=yellow>{$safetyStockDays} days</>");
        $this->line("Target Stock: <fg=yellow>{$targetStockDays} days</>");
        $this->line('');

        // 1. Calculate Sales Velocity
        $this->line('Step 1: Calculating daily sales velocity for all products...');
        $salesData = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->where('transactions.created_at', '>=', Carbon::now()->subDays($salesPeriod))
            ->select(
                'transaction_details.product_id',
                DB::raw("SUM(transaction_details.quantity) as total_sold")
            )
            ->groupBy('transaction_details.product_id')
            ->get()
            ->keyBy('product_id');

        // 2. Get Current Stock for all products
        $this->line('Step 2: Fetching current stock levels...');
        $currentStockLevels = DB::table('product_batches')
            ->select('product_id', DB::raw('SUM(stock) as current_stock'))
            ->where('stock', '>', 0)
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id');

        // 3. Get Latest Purchase Price and Supplier for all products
        $this->line('Step 3: Fetching latest purchase prices and suppliers...');
        $latestPurchaseInfo = DB::table('product_batches as pb1')
            ->select('pb1.product_id', 'pb1.purchase_price', 's.name as supplier_name')
            ->join('purchases as p', 'pb1.purchase_id', '=', 'p.id')
            ->join('suppliers as s', 'p.supplier_id', '=', 's.id')
            ->whereIn('pb1.id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                      ->from('product_batches')
                      ->groupBy('product_id');
            })
            ->get()
            ->keyBy('product_id');

        // 4. Combine data and calculate reorder candidates
        $this->line('Step 4: Analyzing products to identify reorder candidates...');
        $allProducts = DB::table('products')->get()->keyBy('id');
        $candidates = [];

        foreach ($allProducts as $productId => $product) {
            $totalSold = $salesData->get($productId)->total_sold ?? 0;
            $dailyVelocity = $totalSold / $salesPeriod;

            if ($dailyVelocity <= 0) {
                continue; // Skip products that haven't sold
            }

            $currentStock = $currentStockLevels->get($productId)->current_stock ?? 0;
            $daysOfStockRemaining = $currentStock / $dailyVelocity;

            if ($daysOfStockRemaining < $safetyStockDays) {
                $purchaseInfo = $latestPurchaseInfo->get($productId);
                if (!$purchaseInfo || $purchaseInfo->purchase_price <= 0) {
                    continue; // Skip if we can't determine purchase price
                }

                $candidates[] = [
                    'product_id' => $productId,
                    'name' => $product->name,
                    'daily_velocity' => $dailyVelocity,
                    'current_stock' => $currentStock,
                    'days_remaining' => $daysOfStockRemaining,
                    'purchase_price' => $purchaseInfo->purchase_price,
                    'supplier_name' => $purchaseInfo->supplier_name,
                ];
            }
        }

        if (empty($candidates)) {
            $this->info('Analysis complete. No products require reordering at this time.');
            return;
        }

        // 5. Prioritize candidates (most urgent first)
        usort($candidates, fn($a, $b) => $a['days_remaining'] <=> $b['days_remaining']);

        // 6. Build purchase list within budget
        $this->line('Step 5: Building final purchase list within budget...');
        $purchaseList = [];
        $remainingBudget = $budget;
        $totalCost = 0;

        foreach ($candidates as $candidate) {
            $desiredStock = $candidate['daily_velocity'] * $targetStockDays;
            $quantityToOrder = ceil($desiredStock - $candidate['current_stock']);

            if ($quantityToOrder <= 0) continue;

            $cost = $quantityToOrder * $candidate['purchase_price'];

            if ($cost <= $remainingBudget) {
                $purchaseList[] = [
                    'name' => $candidate['name'],
                    'supplier' => $candidate['supplier_name'],
                    'qty_to_order' => $quantityToOrder,
                    'cost' => $cost,
                ];
                $remainingBudget -= $cost;
                $totalCost += $cost;
            }
        }

        if (empty($purchaseList)) {
            $this->warn('Analysis complete. There are products that need reordering, but none could fit within the specified budget.');
            return;
        }

        // 7. Display results
        $this->line('');
        $this->info('--- Purchase Recommendation ---');
        $this->table(
            ['Product Name', 'Supplier', 'Quantity to Order', 'Estimated Cost'],
            collect($purchaseList)->map(function($item) {
                return [
                    $item['name'],
                    $item['supplier'],
                    $item['qty_to_order'],
                    'Rp ' . number_format($item['cost'], 2),
                ];
            })
        );

        $this->line('');
        $this->info('--- Budget Summary ---');
        $this->line("Total Estimated Cost: <fg=green>Rp " . number_format($totalCost, 2) . "</>");
        $this->line("Remaining Budget: <fg=yellow>Rp " . number_format($remainingBudget, 2) . "</>");
    }
}