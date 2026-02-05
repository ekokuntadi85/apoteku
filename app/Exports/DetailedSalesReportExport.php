<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DetailedSalesReportExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return TransactionDetail::query()
            ->whereHas('transaction', function ($query) {
                $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
            })
            ->with(['transaction:id,invoice_number,created_at', 'product:id,name', 'productUnit'])
            ->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Invoice',
            'Nama Produk',
            'Jumlah',
            'Satuan',
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->transaction->created_at->format('Y-m-d H:i:s'),
            $detail->transaction->invoice_number ?? '-',
            $detail->product->name,
            $detail->quantity,
            $detail->productUnit->name ?? '-',
        ];
    }
}