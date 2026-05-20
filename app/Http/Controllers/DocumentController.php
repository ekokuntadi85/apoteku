<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\ProductBatch;
use App\Models\StockMovement;
use App\Models\Product; // Added
use Carbon\Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\DB;

class DocumentController extends Controller
{
    public function printReceipt($transactionId)
    {
        $transaction = Transaction::with(['transactionDetails.product.baseUnit', 'transactionDetails.productUnit', 'customer', 'user'])->findOrFail($transactionId);

        // We will now always return a view, specifically designed for thermal printers.
        // The logic to check for mobile is removed as the thermal view is simple enough for all devices.
        return view('documents.receipt-thermal', compact('transaction'));
    }

    public function printInvoice($transactionId)
    {
        $transaction = Transaction::with(['transactionDetails.product.baseUnit', 'transactionDetails.productUnit', 'customer', 'user'])->findOrFail($transactionId);
        $agent = new Agent();

        if ($agent->isMobile()) {
            return view('documents.invoice-preview', compact('transaction'));
        }

        $pdf = Pdf::loadView('documents.invoice', compact('transaction'));

        return $pdf->stream('invoice_' . $transaction->invoice_number . '.pdf');
    }

    public function printExpiringStockReport(Request $request)
    {
        $expiry_threshold_months = $request->query('expiry_threshold_months', 2); // Default to 2 months
        $thresholdDate = Carbon::now()->addMonths((int)$expiry_threshold_months)->endOfDay();

        $productBatches = ProductBatch::with(['product', 'purchase.supplier'])
                                    ->where('stock', '>', 0)
                                    ->where('expiration_date', '<=', $thresholdDate)
                                    ->orderBy('expiration_date', 'asc')
                                    ->get();

        $pdf = Pdf::loadView('documents.expiring-stock-report', compact('productBatches', 'expiry_threshold_months'));

        return $pdf->setPaper('a4', 'portrait')->stream('laporan_stok_kedaluwarsa.pdf');
    }

    public function printStockCard(Request $request)
    {
        $productId = $request->query('product_id'); // Changed to product_id
        $startDate = Carbon::parse($request->query('start_date'))->startOfDay();
        $endDate = Carbon::parse($request->query('end_date'))->endOfDay();

        $product = Product::findOrFail($productId); // Get the product

        // Calculate initial balance for the product (sum of all movements for all batches of this product before startDate)
        $initialBalance = StockMovement::whereHas('productBatch', function($query) use ($productId) {
                                            $query->where('product_id', $productId);
                                        })
                                        ->where('created_at', '<=', $startDate)
                                        ->sum('quantity');

        // Get all movements for all batches of this product within the period
        $movements = StockMovement::with(['productBatch'])
                                    ->whereHas('productBatch', function($query) use ($productId) {
                                        $query->where('product_id', $productId);
                                    })
                                    ->whereBetween('created_at', [$startDate, $endDate])
                                    ->orderBy('created_at', 'asc')
                                    ->get();

        $processedMovements = collect();

        // Process Sales (PJ) movements - Group and sum by date
        $salesMovements = $movements->where('type', 'PJ');
        $salesRecap = $salesMovements->groupBy(function($item) {
            return Carbon::parse($item->created_at)->format('Y-m-d');
        })->map(function ($group) {
            return [
                'created_at' => $group->first()->created_at, // Keep original timestamp for sorting
                'type' => 'PJ',
                'masuk' => 0,
                'keluar' => abs($group->sum('quantity')),
                'remarks' => 'Rekap Penjualan Harian',
                'batch_number' => null, // No specific batch for recap
            ];
        });

        // Process other types of movements individually
        $otherMovementTypes = ['PB', 'OP', 'ADJ', 'DEL', 'RES'];
        $otherMovements = $movements->whereIn('type', $otherMovementTypes);

        foreach ($otherMovements as $movement) {
            $remarks = $movement->remarks;
            $batchNumber = $movement->productBatch->batch_number ?? 'N/A';

            // Customize remarks for PB if needed
            if ($movement->type === 'PB') {
                $remarks = 'Pembelian (Batch: ' . $batchNumber . ')';
            } elseif ($movement->type === 'OP') {
                $remarks = 'Opname (Batch: ' . $batchNumber . ') - ' . $remarks;
            } elseif ($movement->type === 'ADJ') {
                $remarks = 'Penyesuaian (Batch: ' . $batchNumber . ') - ' . $remarks;
            } elseif ($movement->type === 'DEL') {
                $remarks = 'Batch Dihapus (Batch: ' . $batchNumber . ')';
            } elseif ($movement->type === 'RES') {
                $remarks = 'Batch Dikembalikan (Batch: ' . $batchNumber . ')';
            }

            $processedMovements->push([
                'created_at' => $movement->created_at,
                'type' => $movement->type,
                'masuk' => $movement->quantity > 0 ? $movement->quantity : 0,
                'keluar' => $movement->quantity < 0 ? abs($movement->quantity) : 0,
                'remarks' => $remarks,
                'batch_number' => $batchNumber,
            ]);
        }

        // Merge and sort all movements by timestamp
        $finalMovements = $processedMovements->merge($salesRecap)->sortBy('created_at');

        $pdf = Pdf::loadView('documents.stock-card-print', compact('product', 'initialBalance', 'finalMovements', 'startDate', 'endDate'));

        return $pdf->setPaper('a4', 'portrait')->stream('kartu_stok_' . $product->name . '.pdf');
    }

    public function printTopSellingProductsReport(Request $request)
    {
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');

        $topProducts = \App\Models\TransactionDetail::query()
            ->select(
                'product_id',
                'product_unit_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->whereHas('transaction', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            })
            ->with(['product:id,name', 'productUnit:id,name'])
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('total_quantity')
            ->limit(30)
            ->get();

        $topProducts->each(function ($item) {
            $product = $item->product;
            if (!$product) {
                $item->current_stock = 0;
                return;
            }

            $batches = \App\Models\ProductBatch::with('productUnit:id,conversion_factor')->where('product_id', $product->id)->get();
            
            $totalBaseStock = 0;
            foreach ($batches as $batch) {
                $totalBaseStock += $batch->stock * ($batch->productUnit->conversion_factor ?? 1);
            }

            $soldUnit = $item->productUnit;
            $soldUnitConversionFactor = $soldUnit->conversion_factor ?? 1;

            if ($soldUnitConversionFactor > 0) {
                $item->current_stock = $totalBaseStock / $soldUnitConversionFactor;
            } else {
                $item->current_stock = 0;
            }
        });

        $pdf = Pdf::loadView('documents.top-selling-products-report', compact('topProducts', 'startDate', 'endDate'));

        return $pdf->setPaper('a4', 'portrait')->stream('laporan-produk-terlaris.pdf');
    }

    public function printProductList()
    {
        $products = Product::with(['category', 'productUnits', 'productBatches'])
            ->orderBy('name')
            ->get();
        
        return view('documents.product-list', compact('products'));
    }
}