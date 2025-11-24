<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Title;

#[Title('Edit Surat Pesanan')]
class PurchaseOrderEdit extends Component
{
    public PurchaseOrder $purchaseOrder;
    
    public $supplier_id;
    public $po_number;
    public $order_date;
    public $status;
    public $notes;
    
    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $quantity;
    public $estimated_price;
    
    public $selectedProductUnits = [];
    public $selectedProductUnitId;
    
    public $order_items = [];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'po_number' => 'required|string|max:255',
        'order_date' => 'required|date',
        'order_items' => 'required|array|min:1',
        'order_items.*.product_id' => 'required|exists:products,id',
        'order_items.*.product_unit_id' => 'required|exists:product_units,id',
        'order_items.*.quantity' => 'required|integer|min:1',
        'order_items.*.estimated_price' => 'nullable|numeric|min:0',
    ];

    public function mount(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->status !== 'draft') {
            return redirect()->route('purchase-orders.show', $purchaseOrder);
        }

        $this->purchaseOrder = $purchaseOrder;
        $this->supplier_id = $purchaseOrder->supplier_id;
        $this->po_number = $purchaseOrder->po_number;
        $this->order_date = $purchaseOrder->order_date->format('Y-m-d');
        $this->status = $purchaseOrder->status;
        $this->notes = $purchaseOrder->notes;

        foreach ($purchaseOrder->details as $detail) {
            $this->order_items[] = [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product->name,
                'product_unit_id' => $detail->product_unit_id,
                'unit_name' => $detail->productUnit->name,
                'quantity' => $detail->quantity,
                'estimated_price' => $detail->estimated_price,
                'subtotal' => ($detail->estimated_price ?? 0) * $detail->quantity,
            ];
        }
    }

    public function updatedSearchProduct($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                                      ->orWhere('sku', 'like', '%' . $value . '%')
                                      ->with('productUnits')
                                      ->limit(10)
                                      ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::with('productUnits')->find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->searchProduct = '';
            $this->searchResults = [];
            
            $this->selectedProductUnits = $product->productUnits->toArray();
            
            // Default to base unit
            $baseUnit = $product->productUnits->firstWhere('is_base_unit', true);
            if ($baseUnit) {
                $this->selectedProductUnitId = $baseUnit['id'];
                $this->estimated_price = $baseUnit['purchase_price'];
            } else {
                $this->selectedProductUnitId = null;
                $this->estimated_price = '';
            }
        }
    }
    
    public function updatedSelectedProductUnitId($value)
    {
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $value);
        if ($selectedUnit) {
            $this->estimated_price = $selectedUnit['purchase_price'];
        }
    }

    public function addItem()
    {
        $this->validate([
            'product_id' => 'required|exists:products,id',
            'selectedProductUnitId' => 'required|exists:product_units,id',
            'quantity' => 'required|integer|min:1',
            'estimated_price' => 'nullable|numeric|min:0',
        ]);

        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);

        $this->order_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'product_unit_id' => $this->selectedProductUnitId,
            'unit_name' => $selectedUnit['name'],
            'quantity' => $this->quantity,
            'estimated_price' => $this->estimated_price,
            'subtotal' => ($this->estimated_price ?? 0) * $this->quantity,
        ];

        $this->resetItemForm();
    }

    public function removeItem($index)
    {
        unset($this->order_items[$index]);
        $this->order_items = array_values($this->order_items);
    }

    public function updateOrder()
    {
        $this->validate();

        DB::transaction(function () {
            $this->purchaseOrder->update([
                'supplier_id' => $this->supplier_id,
                'order_date' => $this->order_date,
                'notes' => $this->notes,
            ]);

            // Delete existing details and recreate
            $this->purchaseOrder->details()->delete();

            foreach ($this->order_items as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $this->purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'],
                ]);
            }
        });

        session()->flash('message', 'Surat Pesanan berhasil diperbarui.');
        return redirect()->route('purchase-orders.show', $this->purchaseOrder);
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->quantity = '';
        $this->estimated_price = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->selectedProductUnits = [];
        $this->selectedProductUnitId = null;
    }

    public function render()
    {
        $suppliers = Supplier::all();
        return view('livewire.purchase-order-edit', compact('suppliers'));
    }
}
