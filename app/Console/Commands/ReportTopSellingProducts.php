<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReportTopSellingProducts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:report-top-selling-products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analyzes the top 100 best-selling products of the last month and identifies which have a stock level below 5.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Analyzing top 100 best-selling products from the last month to find items with stock below 5...');

        // 1. Get top 100 selling product IDs and their sales volume
        $top100ProductsInfo = DB::table('transaction_details')
            ->join('transactions', 'transaction_details.transaction_id', '=', 'transactions.id')
            ->select(
                'transaction_details.product_id',
                DB::raw('SUM(transaction_details.quantity) as total_sold')
            )
            ->where('transactions.created_at', '>=', now()->subMonth())
            ->groupBy('transaction_details.product_id')
            ->orderByDesc('total_sold')
            ->limit(100)
            ->get();

        if ($top100ProductsInfo->isEmpty()) {
            $this->info('No sales data found for the last month.');
            return;
        }

        // 2. Get current stock for these products
        $productIds = $top100ProductsInfo->pluck('product_id');

        $currentStockLevels = DB::table('product_batches')
            ->whereIn('product_id', $productIds)
            ->select('product_id', DB::raw('SUM(stock) as current_stock'))
            ->groupBy('product_id')
            ->get()
            ->keyBy('product_id'); // Key by product_id for easy lookup

        // 3. Filter for low stock and prepare data for the table
        $lowStockProducts = [];
        $products = DB::table('products')->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($top100ProductsInfo as $productInfo) {
            $productId = $productInfo->product_id;
            $stockInfo = $currentStockLevels->get($productId);
            $currentStock = $stockInfo ? $stockInfo->current_stock : 0;

            if ($currentStock < 5) {
                $product = $products->get($productId);
                if ($product) {
                    $lowStockProducts[] = [
                        'name' => $product->name,
                        'current_stock' => $currentStock,
                        'total_sold_last_month' => $productInfo->total_sold,
                    ];
                }
            }
        }

        if (empty($lowStockProducts)) {
            $this->info('None of the top 100 best-selling products have low stock (less than 5).');
            return;
        }

        // 4. Display the results
        $this->table(
            ['Product Name', 'Current Stock', 'Total Sold (Last Month)'],
            $lowStockProducts
        );
    }
}
