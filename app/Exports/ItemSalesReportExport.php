<?php

namespace App\Exports;

use App\Models\TransactionDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ItemSalesReportExport implements FromQuery, WithHeadings, WithMapping
{
    protected $productId;
    protected $filterType;
    protected $filterDate;
    protected $filterMonth;
    protected $filterStartDate;
    protected $filterEndDate;

    public function __construct($productId, $filterType, $filterDate, $filterMonth, $filterStartDate, $filterEndDate)
    {
        $this->productId = $productId;
        $this->filterType = $filterType;
        $this->filterDate = $filterDate;
        $this->filterMonth = $filterMonth;
        $this->filterStartDate = $filterStartDate;
        $this->filterEndDate = $filterEndDate;
    }

    public function query()
    {
        $query = TransactionDetail::query()
            ->where('product_id', $this->productId)
            ->with(['transaction:id,invoice_number,created_at', 'productUnit']);

        if ($this->filterType === 'day') {
            $query->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', $this->filterDate);
            });
        } elseif ($this->filterType === 'month') {
            $query->whereHas('transaction', function ($q) {
                $q->whereMonth('created_at', Carbon::parse($this->filterMonth)->month)
                    ->whereYear('created_at', Carbon::parse($this->filterMonth)->year);
            });
        } elseif ($this->filterType === 'range') {
            $query->whereHas('transaction', function ($q) {
                $q->whereBetween('created_at', [$this->filterStartDate . ' 00:00:00', $this->filterEndDate . ' 23:59:59']);
            });
        }

        return $query->latest('created_at');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Invoice',
            'Jumlah',
            'Satuan',
        ];
    }

    public function map($detail): array
    {
        return [
            $detail->transaction->created_at->format('Y-m-d H:i:s'),
            $detail->transaction->invoice_number ?? '-',
            $detail->quantity,
            $detail->productUnit->name ?? '-',
        ];
    }
}
