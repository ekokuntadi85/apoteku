<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function showReport(Request $request)
    {
        $password = '8485';
        $data = null;
        $error = null;

        if ($request->isMethod('post')) {
            if ($request->input('password') == $password) {
                // Logic from the Artisan command
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
                    $data = [];
                } else {
                    $productIds = $top100ProductsInfo->pluck('product_id');

                    $currentStockLevels = DB::table('product_batches')
                        ->whereIn('product_id', $productIds)
                        ->select('product_id', DB::raw('SUM(stock) as current_stock'))
                        ->groupBy('product_id')
                        ->get()
                        ->keyBy('product_id');

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
                    $data = $lowStockProducts;
                }
            } else {
                $error = 'Password salah.';
            }
        }

        return view('reports.low-stock-analysis', ['data' => $data, 'error' => $error]);
    }
}