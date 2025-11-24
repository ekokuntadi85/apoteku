<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    public function print(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['supplier', 'details.product', 'details.productUnit']);
        
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        
        // Select PDF view based on type
        $view = match($purchaseOrder->type) {
            'oot' => 'documents.purchase-order-oot',
            'prekursor' => 'documents.purchase-order-prekursor',
            default => 'documents.purchase-order',
        };
        
        $pdf = Pdf::loadView($view, compact('purchaseOrder', 'settings'));
        
        return $pdf->stream('Surat-Pesanan-' . $purchaseOrder->po_number . '.pdf');
    }
}
