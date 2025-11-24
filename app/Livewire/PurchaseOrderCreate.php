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

#[Title('Buat Surat Pesanan')]
class PurchaseOrderCreate extends Component
{
    public $supplier_id;
    public $po_number;
    public $order_date;
    public $status = 'draft';
    public $notes;
    public $type = 'general';
    
    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';
    public $allUnits = []; // All units for dosage form dropdown

    public $product_id;
    public $quantity;
    public $estimated_price;
    
    public $selectedProductUnits = [];
    public $selectedProductUnitId;
    
    public $order_items = [];

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'po_number' => 'required|string|max:255|unique:purchase_orders,po_number',
        'order_date' => 'required|date',
        'status' => 'required|in:draft,sent,completed,cancelled',
        'type' => 'required|in:general,oot,prekursor',
        'order_items' => 'required|array|min:1',
        'order_items.*.product_id' => 'required|exists:products,id',
        'order_items.*.product_unit_id' => 'required|exists:product_units,id',
        'order_items.*.quantity' => 'required|integer|min:1',
        'order_items.*.estimated_price' => 'nullable|numeric|min:0',
    ];

    public function mount()
    {
        $this->order_date = now()->format('Y-m-d');
        $this->type = request()->query('type', 'general');
        $this->allUnits = \App\Models\ProductUnit::select('name')->distinct()->pluck('name')->toArray();
        $this->generatePoNumber();
    }

    private function generatePoNumber()
    {
        // Determine type prefix
        $typePrefix = match($this->type) {
            'oot' => 'OOT',
            'prekursor' => 'PRE',
            default => 'U',
        };
        
        // Format: SP-[TYPE]-DDMMYY-XXXX
        $dateStr = now()->format('dmy'); // DDMMYY format
        
        // Count orders created today with the same type
        $count = PurchaseOrder::whereDate('created_at', today())
                              ->where('type', $this->type)
                              ->count() + 1;
        
        $this->po_number = 'SP-' . $typePrefix . '-' . $dateStr . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
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
            'active_substance' => $product->active_substance ?? '',
            'dosage_form' => $selectedUnit['name'], // Default to selected unit
            'product_unit_id' => $this->selectedProductUnitId,
            'unit_name' => $selectedUnit['name'],
            'quantity' => $this->quantity,
            'estimated_price' => $this->estimated_price,
            'subtotal' => ($this->estimated_price ?? 0) * $this->quantity,
        ];

        $this->resetItemForm();
    }

    public function updatedOrderItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $index = $parts[0];
        $field = $parts[1];

        // Recalculate subtotal if quantity or price changes
        if (in_array($field, ['quantity', 'estimated_price'])) {
            $qty = (int)($this->order_items[$index]['quantity'] ?? 0);
            $price = (float)($this->order_items[$index]['estimated_price'] ?? 0);
            $this->order_items[$index]['subtotal'] = $qty * $price;
        }
    }

    public function removeItem($index)
    {
        unset($this->order_items[$index]);
        $this->order_items = array_values($this->order_items);
    }

    public function saveOrder()
    {
        $this->validate();

        DB::transaction(function () {
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $this->po_number,
                'supplier_id' => $this->supplier_id,
                'order_date' => $this->order_date,
                'status' => 'draft',
                'notes' => $this->notes,
                'type' => $this->type,
            ]);

            foreach ($this->order_items as $item) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'active_substance' => $item['active_substance'] ?? null,
                    'dosage_form' => $item['dosage_form'] ?? null,
                    'product_unit_id' => $item['product_unit_id'],
                    'quantity' => $item['quantity'],
                    'estimated_price' => $item['estimated_price'],
                ]);
            }
        });

        session()->flash('message', 'Surat Pesanan berhasil dibuat.');
        return redirect()->route('purchase-orders.index');
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
        return view('livewire.purchase-order-create', compact('suppliers'));
    }
}
