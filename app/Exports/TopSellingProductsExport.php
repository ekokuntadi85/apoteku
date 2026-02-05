<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TopSellingProductsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    private $rank = 0;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return TransactionDetail::query()
            ->select(
                'product_id',
                'product_unit_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->whereHas('transaction', function ($query) {
                $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->with(['product:id,name', 'productUnit:id,name'])
            ->groupBy('product_id', 'product_unit_id')
            ->orderByDesc('total_quantity')
            ->limit(30);
    }

    public function headings(): array
    {
        return [
            'Peringkat',
            'Nama Item',
            'Jumlah Terjual',
            'Stok Saat Ini',
            'Satuan',
        ];
    }

    public function map($row): array
    {
        $this->rank++;

        $product = $row->product;
        $current_stock = 0;
        if ($product) {
            $batches = \App\Models\ProductBatch::with('productUnit:id,conversion_factor')->where('product_id', $product->id)->get();
            
            $totalBaseStock = 0;
            foreach ($batches as $batch) {
                $totalBaseStock += $batch->stock * ($batch->productUnit->conversion_factor ?? 1);
            }

            $soldUnit = $row->productUnit;
            $soldUnitConversionFactor = $soldUnit->conversion_factor ?? 1;

            if ($soldUnitConversionFactor > 0) {
                $current_stock = $totalBaseStock / $soldUnitConversionFactor;
            }
        }

        return [
            $this->rank,
            $row->product->name ?? 'N/A',
            $row->total_quantity,
            $current_stock,
            $row->productUnit->name ?? 'N/A',
        ];
    }
}