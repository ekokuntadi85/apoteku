<?php

namespace App\Livewire;

use App\Models\Supplier;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\ProductUnit; // Import ProductUnit
use Livewire\Component;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use Livewire\Attributes\Title;

#[Title('Buat Pembelian')]
class PurchaseCreate extends Component
{
    public $supplier_id;
    public $invoice_number;
    public $purchase_date;
    public $due_date;
    public $total_purchase_price = 0;
    public $payment_status = 'unpaid';

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $batch_number;
    public $purchase_price; // This will be the price per selected unit
    public $selling_price; // Selling price per selected unit
    public $stock; // This will be the stock in the selected unit
    public $expiration_date;
    public $lastKnownPurchasePrice; // Properti baru untuk menyimpan harga beli terakhir
    public $lastKnownSellingPrice; // Last known selling price
    public $currentStock = 0; // Properti baru untuk stok saat ini


    public $selectedProductUnits = []; // New property for available units for selected product
    public $selectedProductUnitId; // New property for the currently selected unit ID
    public $selectedProductUnitPurchasePrice; // New property to display purchase price of selected unit

    public $purchase_items = [];
    public $showPriceWarningModal = false;
    public $newSellingPrice;
    public $itemToAddCache = null;

    // PO Integration
    public $selectedPoId = null;
    public $selectedPoNumber = null;

    protected $rules = [
        'supplier_id' => 'required|exists:suppliers,id',
        'invoice_number' => 'required|string|max:255|unique:purchases,invoice_number',
        'purchase_date' => 'required|date',
        'due_date' => 'nullable|date',
        'total_purchase_price' => 'required|numeric|min:0',
        'purchase_items' => 'required|array|min:1',
        'purchase_items.*.product_id' => 'required|exists:products,id',
        'purchase_items.*.product_unit_id' => 'required|exists:product_units,id', // New rule
        'purchase_items.*.batch_number' => 'nullable|string|max:255',
        'purchase_items.*.purchase_price' => 'required|numeric|min:0',
        'purchase_items.*.selling_price' => 'required|numeric|min:0',
        'purchase_items.*.stock' => 'required|integer|min:1', // This stock is in base units now
        'purchase_items.*.expiration_date' => 'nullable|date',
    ];

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'selectedProductUnitId' => 'required|exists:product_units,id', // New rule
        'batch_number' => 'nullable|string|max:255',
        'purchase_price' => 'required|numeric|min:0',
        'stock' => 'required|integer|min:1',
        'expiration_date' => 'nullable|date',
    ];

    protected $messages = [
        'supplier_id.required' => 'Supplier wajib dipilih.',
        'supplier_id.exists' => 'Supplier tidak valid.',
        'invoice_number.required' => 'Nomor invoice wajib diisi.',
        'invoice_number.unique' => 'Nomor invoice sudah ada.',
        'purchase_date.required' => 'Tanggal pembelian wajib diisi.',
        'purchase_date.date' => 'Tanggal pembelian tidak valid.',
        'due_date.date' => 'Tanggal jatuh tempo tidak valid.',
        'total_purchase_price.required' => 'Total pembelian wajib dihitung.',
        'total_purchase_price.numeric' => 'Total pembelian harus berupa angka.',
        'total_purchase_price.min' => 'Total pembelian tidak boleh negatif.',
        'purchase_items.required' => 'Setidaknya ada satu item pembelian.',
        'purchase_items.min' => 'Setidaknya ada satu item pembelian.',
        'product_id.required' => 'Produk wajib dipilih.',
        'product_id.exists' => 'Produk tidak valid.',
        'selectedProductUnitId.required' => 'Satuan produk wajib dipilih.', // New message
        'selectedProductUnitId.exists' => 'Satuan produk tidak valid.', // New message
        'purchase_price.required' => 'Harga beli wajib diisi.',
        'purchase_price.numeric' => 'Harga beli harus berupa angka.',
        'purchase_price.min' => 'Harga beli tidak boleh negatif.',
        'purchase_items.*.original_stock_input.required' => 'Kuantitas wajib diisi.',
        'purchase_items.*.original_stock_input.integer' => 'Kuantitas harus berupa angka bulat.',
        'purchase_items.*.original_stock_input.min' => 'Kuantitas minimal 1.',
        'purchase_items.*.purchase_price.required' => 'Harga beli wajib diisi.',
        'purchase_items.*.purchase_price.numeric' => 'Harga beli harus berupa angka.',
        'purchase_items.*.purchase_price.min' => 'Harga beli tidak boleh negatif.',
        'purchase_items.*.expiration_date.date' => 'Tanggal kadaluarsa tidak valid.',
        'newSellingPrice.required' => 'Harga jual baru wajib diisi.',
        'newSellingPrice.numeric' => 'Harga jual baru harus berupa angka.',
        'newSellingPrice.min' => 'Harga jual baru tidak boleh lebih rendah dari harga beli.',
    ];

    public function updatedPurchaseItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) < 2) return;

        $index = $parts[0];
        $field = $parts[1];

        if (in_array($field, ['original_stock_input', 'purchase_price'])) {
            // Ensure numeric values
            $qty = (int)($this->purchase_items[$index]['original_stock_input'] ?? 0);
            $price = (float)($this->purchase_items[$index]['purchase_price'] ?? 0);

            // Recalculate stock in base units if quantity changed
            if ($field === 'original_stock_input') {
                $conversionFactor = $this->purchase_items[$index]['conversion_factor'] ?? 1;
                $this->purchase_items[$index]['stock'] = $qty * $conversionFactor;
            }

            // Recalculate subtotal
            $this->purchase_items[$index]['subtotal'] = $qty * $price;

            $this->calculateTotalPurchasePrice();
        }

        // Check if purchase price changed and is DIFFERENT from last known price
        if ($field === 'purchase_price') {
            $product = Product::find($this->purchase_items[$index]['product_id']);
            $latestBatch = ProductBatch::where('product_id', $product->id)
                                        ->latest('created_at')
                                        ->first();
            
            if ($latestBatch) {
                $conversionFactor = $this->purchase_items[$index]['conversion_factor'] ?? 1;
                $lastKnownPurchasePrice = $latestBatch->purchase_price;
                $currentPurchasePriceInBaseUnit = $this->purchase_items[$index]['purchase_price'] / $conversionFactor;
                
                if ($currentPurchasePriceInBaseUnit != $lastKnownPurchasePrice) {
                    // Show warning that purchase price changed
                    $this->itemToAddCache = $this->purchase_items[$index];
                    $this->itemToAddCache['index'] = $index;
                    $this->itemToAddCache['last_purchase_price'] = $lastKnownPurchasePrice;
                    $this->itemToAddCache['price_change_type'] = $currentPurchasePriceInBaseUnit > $lastKnownPurchasePrice ? 'increase' : 'decrease';
                    
                    // Suggest new selling price (at least equal to or higher than purchase price)
                    $minSellingPrice = $this->purchase_items[$index]['selling_price'];
                    if ($this->purchase_items[$index]['selling_price'] < $this->purchase_items[$index]['purchase_price']) {
                        $minSellingPrice = $this->purchase_items[$index]['purchase_price'];
                    }
                    $this->newSellingPrice = $minSellingPrice;
                    $this->showPriceWarningModal = true;
                }
            }
        }

        // Check if selling price is lower than purchase price
        if ($field === 'selling_price') {
            $purchasePrice = (float)($this->purchase_items[$index]['purchase_price'] ?? 0);
            $sellingPrice = (float)($this->purchase_items[$index]['selling_price'] ?? 0);
            
            if ($sellingPrice < $purchasePrice) {
                $this->dispatch('selling-price-warning', 'Harga jual (Rp ' . number_format($sellingPrice, 0) . ') lebih rendah dari harga beli (Rp ' . number_format($purchasePrice, 0) . '). Anda akan menjual dengan rugi!');
            }
        }
    }

    public function mount()
    {
        $this->purchase_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');

        $po_id = request()->query('po_id');
        if ($po_id) {
            $this->loadPo($po_id);
        }
    }

    public function updatedPurchaseDate($value)
    {
        if ($value) {
            $this->due_date = \Illuminate\Support\Carbon::parse($value)->addDays(30)->format('Y-m-d');
        } else {
            $this->due_date = null;
        }
    }

    public function updatedSearchProduct($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where(function ($query) use ($value) {
                                        $query->where('name', 'like', '%' . $value . '%')
                                              ->orWhere('sku', 'like', '%' . $value . '%');
                                    })
                                    ->withSum('productBatches', 'stock')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::with('productUnits')->find($productId); // Load product units
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results

            // Calculate and set the current stock
            $this->currentStock = $product->productBatches()->sum('stock');

            $this->selectedProductUnits = $product->productUnits->toArray();

            // Automatically select the base unit
            $baseUnit = $product->productUnits->firstWhere('is_base_unit', true);
            if ($baseUnit) {
                $this->selectedProductUnitId = $baseUnit['id'];
                $this->purchase_price = $baseUnit['purchase_price']; // Set initial purchase price to base unit's
                $this->selling_price = $baseUnit['selling_price']; // Set initial selling price
                $this->selectedProductUnitPurchasePrice = $baseUnit['purchase_price'];
                $this->lastKnownSellingPrice = $baseUnit['selling_price'];
            } else {
                $this->selectedProductUnitId = null;
                $this->purchase_price = '';
                $this->selling_price = '';
                $this->selectedProductUnitPurchasePrice = '';
                $this->lastKnownSellingPrice = null;
            }

            // Ambil harga beli terakhir dari batch produk terbaru (dalam satuan dasar)
            $latestBatch = ProductBatch::where('product_id', $productId)
                                        ->latest('created_at')
                                        ->first();

            if ($latestBatch) {
                // Convert the last known purchase price to the currently selected unit's price
                // This assumes latestBatch->purchase_price is in base unit
                $this->lastKnownPurchasePrice = $latestBatch->purchase_price;
                // If a unit is selected, convert the last known base unit price to that unit's price
                if ($this->selectedProductUnitId) {
                    $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);
                    if ($selectedUnit && $selectedUnit['conversion_factor'] > 0) {
                        $this->purchase_price = $this->lastKnownPurchasePrice * $selectedUnit['conversion_factor'];
                        $this->selectedProductUnitPurchasePrice = $this->purchase_price;
                    }
                }
            } else {
                $this->lastKnownPurchasePrice = null;
            }
        }
    }

    public function updatedSelectedProductUnitId($value)
    {
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $value);
        if ($selectedUnit) {
            $this->purchase_price = $selectedUnit['purchase_price'];
            $this->selling_price = $selectedUnit['selling_price'];
            $this->selectedProductUnitPurchasePrice = $selectedUnit['purchase_price'];
            $this->lastKnownSellingPrice = $selectedUnit['selling_price'];

            // If there's a last known base unit purchase price, convert it to the new selected unit's price
            if ($this->lastKnownPurchasePrice !== null) {
                $this->purchase_price = $this->lastKnownPurchasePrice * $selectedUnit['conversion_factor'];
                $this->selectedProductUnitPurchasePrice = $this->purchase_price;
            }
        }
    }

    public function addItem()
    {
        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);

        if (!$selectedUnit) {
            session()->flash('error', 'Satuan produk tidak valid.');
            return;
        }

        // Calculate stock in base units
        $stockInBaseUnits = $this->stock * $selectedUnit['conversion_factor'];

        // Calculate the purchase price in base units for comparison
        $purchasePriceInBaseUnit = $this->purchase_price / $selectedUnit['conversion_factor'];

        // Check if purchase price is DIFFERENT from last known purchase price
        if ($this->lastKnownPurchasePrice !== null && $purchasePriceInBaseUnit != $this->lastKnownPurchasePrice) {
            // Warn user and allow them to update selling price
            $expirationDate = $this->expiration_date ?: \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d');

            $this->itemToAddCache = [
                'product_id' => $this->product_id,
                'product_name' => $product->name,
                'product_unit_id' => $this->selectedProductUnitId,
                'unit_name' => $selectedUnit['name'],
                'conversion_factor' => $selectedUnit['conversion_factor'],
                'batch_number' => $this->batch_number,
                'purchase_price' => $this->purchase_price,
                'selling_price' => $this->selling_price,
                'stock' => $stockInBaseUnits,
                'original_stock_input' => $this->stock,
                'expiration_date' => $expirationDate,
                'subtotal' => $this->purchase_price * $this->stock,
                'last_purchase_price' => $this->lastKnownPurchasePrice,
                'price_change_type' => $purchasePriceInBaseUnit > $this->lastKnownPurchasePrice ? 'increase' : 'decrease',
            ];

            // Suggest new selling price (at least equal to or higher than purchase price)
            $minSellingPrice = $this->selling_price;
            if ($this->selling_price < $this->purchase_price) {
                $minSellingPrice = $this->purchase_price;
            }
            $this->newSellingPrice = $minSellingPrice;
            $this->showPriceWarningModal = true;
            return;
        }

        $this->confirmedAddItem();
    }

    #[On('confirmedAddItem')]
    public function confirmedAddItem()
    {
        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->selectedProductUnits)->firstWhere('id', $this->selectedProductUnitId);

        if (!$selectedUnit) {
            session()->flash('error', 'Satuan produk tidak valid.');
            return;
        }

        // Set expiration_date to 1 year from today if it's empty
        if (empty($this->expiration_date)) {
            $this->expiration_date = \Carbon\Carbon::now()->addYear()->format('Y-m-d');
        }

        // Calculate stock in base units
        $stockInBaseUnits = $this->stock * $selectedUnit['conversion_factor'];

        $this->purchase_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'product_unit_id' => $this->selectedProductUnitId, // Store selected unit ID
            'unit_name' => $selectedUnit['name'], // Store selected unit name for display
            'conversion_factor' => $selectedUnit['conversion_factor'], // Store conversion factor
            'batch_number' => $this->batch_number,
            'purchase_price' => $this->purchase_price, // Price per selected unit
            'selling_price' => $this->selling_price, // Selling price per selected unit
            'stock' => $stockInBaseUnits, // Stock in base units
            'original_stock_input' => $this->stock, // Store original input for display
            'expiration_date' => $this->expiration_date,
            'subtotal' => $this->purchase_price * $this->stock, // Subtotal based on selected unit price and input stock
        ];

        $this->calculateTotalPurchasePrice();
        $this->resetItemForm();
    }

    public function updatePriceAndAddItem()
    {
        // Calculate the minimum selling price (should be at least equal to purchase price)
        $minSellingPrice = $this->itemToAddCache['purchase_price'];

        $this->validate([
            'newSellingPrice' => 'required|numeric|min:' . $minSellingPrice
        ]);

        // Update the selling price in the cached item
        $this->itemToAddCache['selling_price'] = $this->newSellingPrice;

        // Update the product unit's selling price
        $productUnit = ProductUnit::find($this->itemToAddCache['product_unit_id']);
        if ($productUnit) {
            $productUnit->selling_price = $this->newSellingPrice;
            $productUnit->save();
        }

        // Check if this is updating an existing item or adding a new one
        if (isset($this->itemToAddCache['index'])) {
            // Update existing item in the list
            $index = $this->itemToAddCache['index'];
            $this->purchase_items[$index]['selling_price'] = $this->newSellingPrice;
            unset($this->itemToAddCache['index']); // Remove the index key
        } else {
            // Add new item to the list
            $this->purchase_items[] = $this->itemToAddCache;
        }
        
        $this->calculateTotalPurchasePrice();

        $this->closePriceWarningModal();
        
        // Only reset form if adding new item (not updating existing)
        if (!isset($this->itemToAddCache['index'])) {
            $this->resetItemForm();
        }
    }

    public function closePriceWarningModal()
    {
        $this->showPriceWarningModal = false;
        $this->itemToAddCache = null;
        $this->newSellingPrice = null;
    }

    public function removeItem($index)
    {
        unset($this->purchase_items[$index]);
        $this->purchase_items = array_values($this->purchase_items); // Re-index the array
        $this->calculateTotalPurchasePrice();
    }

    private function calculateTotalPurchasePrice()
    {
        $this->total_purchase_price = array_sum(array_column($this->purchase_items, 'subtotal'));
    }

    public function savePurchase()
    {
        if (empty($this->due_date)) {
            $this->due_date = now()->format('Y-m-d');
        }

        $this->validate();

        DB::transaction(function () {
            $purchase = Purchase::create([
                'invoice_number' => $this->invoice_number,
                'purchase_date' => $this->purchase_date,
                'due_date' => $this->due_date,
                'total_price' => $this->total_purchase_price,
                'supplier_id' => $this->supplier_id,
                'payment_status' => $this->payment_status,
            ]);

            foreach ($this->purchase_items as $item) {
                $batchNumber = empty($item['batch_number']) ? '-' : $item['batch_number'];
                
                // Auto-set expiration date to 1 year from now if empty
                $expirationDate = empty($item['expiration_date']) 
                    ? \Carbon\Carbon::now()->addYear()->format('Y-m-d') 
                    : $item['expiration_date'];

                ProductBatch::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'product_unit_id' => $item['product_unit_id'], // Store selected unit ID
                    'batch_number' => $batchNumber,
                    'purchase_price' => $item['purchase_price'], // Price per selected unit
                    'stock' => $item['stock'], // Stock in base units
                    'expiration_date' => $expirationDate,
                ]);
            }
        });

        session()->flash('message', 'Pembelian berhasil dicatat.');
        
        // Update PO status if applicable
        if ($this->selectedPoId) {
            $po = \App\Models\PurchaseOrder::find($this->selectedPoId);
            if ($po) {
                $po->update(['status' => 'completed']);
            }
        }

        $this->resetAll();
        return redirect()->route('purchases.index');
    }

    public function loadPo($poId)
    {
        $po = \App\Models\PurchaseOrder::with(['details.product', 'details.productUnit'])->find($poId);
        
        if (!$po) {
            return;
        }

        $this->selectedPoId = $po->id;
        $this->selectedPoNumber = $po->po_number;
        $this->supplier_id = $po->supplier_id;
        
        // Clear existing items
        $this->purchase_items = [];

        foreach ($po->details as $detail) {
            // Calculate stock in base units
            $stockInBaseUnits = $detail->quantity * $detail->productUnit->conversion_factor;
            
            // Set default expiration to 1 year from now if not set
            $defaultExpiration = \Carbon\Carbon::now()->addYear()->format('Y-m-d');

            $this->purchase_items[] = [
                'product_id' => $detail->product_id,
                'product_name' => $detail->product->name,
                'product_unit_id' => $detail->product_unit_id,
                'unit_name' => $detail->productUnit->name,
                'conversion_factor' => $detail->productUnit->conversion_factor,
                'batch_number' => '', // User must fill this
                'purchase_price' => $detail->estimated_price ?? 0,
                'selling_price' => $detail->productUnit->selling_price ?? 0,
                'stock' => $stockInBaseUnits,
                'original_stock_input' => $detail->quantity,
                'expiration_date' => $defaultExpiration, // Default value
                'subtotal' => ($detail->estimated_price ?? 0) * $detail->quantity,
            ];
        }

        $this->calculateTotalPurchasePrice();
        session()->flash('message', 'Data dari Surat Pesanan #' . $po->po_number . ' berhasil dimuat. Silakan lengkapi Nomor Batch dan Tanggal Kadaluwarsa.');
    }

    public function cancelSelectedPo()
    {
        $this->selectedPoId = null;
        $this->selectedPoNumber = null;
        $this->purchase_items = [];
        $this->supplier_id = '';
        $this->calculateTotalPurchasePrice();
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->batch_number = '';
        $this->purchase_price = '';
        $this->selling_price = '';
        $this->stock = '';
        $this->expiration_date = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->selectedProductUnits = []; // Reset new properties
        $this->selectedProductUnitId = null; // Reset new properties
        $this->selectedProductUnitPurchasePrice = ''; // Reset new properties
        $this->resetErrorBag(['product_id', 'selectedProductUnitId', 'batch_number', 'purchase_price', 'selling_price', 'stock', 'expiration_date']);
    }

    private function resetAll()
    {
        $this->supplier_id = '';
        $this->invoice_number = '';
        $this->purchase_date = now()->format('Y-m-d');
        $this->due_date = now()->addDays(30)->format('Y-m-d');
        $this->total_purchase_price = 0;
        $this->purchase_items = [];
        $this->payment_status = 'unpaid';
        $this->selectedPoId = null;
        $this->selectedPoNumber = null;
        $this->resetItemForm();
        $this->resetErrorBag();
    }

    public function render()
    {
        $suppliers = Supplier::all();
        return view('livewire.purchase-create', compact('suppliers')); // Removed 'products' from compact
    }
}