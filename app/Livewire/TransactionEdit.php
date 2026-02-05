<?php

namespace App\Livewire;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\TransactionDetailBatch;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;

#[Title('Edit Transaksi')]
class TransactionEdit extends Component
{
    public $transactionId;
    public $type;
    public $payment_status;
    public $total_price;
    public $due_date;
    public $customer_id;
    
    // New fields for invoice enhancements
    public $invoice_type = 'normal';
    public $discount_amount = 0;
    public $grand_total = 0;

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $product_units = [];
    public $product_unit_id;
    public $quantity;
    public $price;
    public $stock_warning = '';
    public $transaction_items = [];
    public $original_transaction_items = []; // To store original quantities for stock adjustment

    protected function rules()
    {
        return [
            'type' => 'required|in:pos,invoice',
            'payment_status' => 'required|in:paid,unpaid,partial,cancelled',
            'total_price' => 'required|numeric|min:0',
            'due_date' => 'nullable|date',
            'customer_id' => 'nullable|exists:customers,id',
            'transaction_items' => 'required|array|min:1',
            'transaction_items.*.product_id' => 'required|exists:products,id',
            'transaction_items.*.quantity' => 'required|integer|min:1',
            'transaction_items.*.price' => 'required|numeric|min:0',
            'invoice_type' => 'required_if:type,invoice|in:normal,loan',
            'discount_amount' => 'nullable|numeric|min:0',
        ];
    }

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
        'product_unit_id' => 'required|exists:product_units,id',
        'quantity' => 'required|integer|min:1',
        'price' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'type.required' => 'Tipe transaksi wajib diisi.',
        'type.in' => 'Tipe transaksi tidak valid.',
        'payment_status.required' => 'Status pembayaran wajib diisi.',
        'payment_status.in' => 'Status pembayaran tidak valid.',
        'total_price.required' => 'Total harga wajib diisi.',
        'total_price.numeric' => 'Total harga harus berupa angka.',
        'total_price.min' => 'Total harga tidak boleh negatif.',
        'transaction_items.required' => 'Setidaknya ada satu item transaksi.',
        'transaction_items.min' => 'Setidaknya ada satu item transaksi.',
        'product_id.required' => 'Produk wajib dipilih.',
        'product_id.exists' => 'Produk tidak valid.',
        'quantity.required' => 'Kuantitas wajib diisi.',
        'quantity.integer' => 'Kuantitas harus berupa angka bulat.',
        'quantity.min' => 'Kuantitas minimal 1.',
        'price.required' => 'Harga wajib diisi.',
        'price.numeric' => 'Harga harus berupa angka.',
        'price.min' => 'Harga tidak boleh negatif.',
    ];

    public function mount(Transaction $transaction)
    {
        $this->transactionId = $transaction->id;
        $this->type = $transaction->type;
        $this->payment_status = $transaction->payment_status;
        $this->total_price = $transaction->total_price;
        $this->due_date = $transaction->due_date;
        $this->customer_id = $transaction->customer_id;
        
        // Load new fields
        $this->invoice_type = $transaction->invoice_type ?? 'normal';
        $this->discount_amount = $transaction->discount_amount ?? 0;
        $this->grand_total = $transaction->grand_total ?? $transaction->total_price;

        foreach ($transaction->transactionDetails as $detail) {
            $item = [
                'id' => $detail->id, // Keep detail ID for update/delete
                'product_id' => $detail->product_id,
                'product_unit_id' => $detail->product_unit_id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'price' => $detail->price,
                'subtotal' => $detail->quantity * $detail->price,
            ];
            $this->transaction_items[] = $item;
            $this->original_transaction_items[] = $item; // Store original for stock adjustment
        }
    }

    public function render()
    {
        $customers = Customer::all();
        // Products are fetched dynamically via search
        return view('livewire.transaction-edit', compact('customers'));
    }

    public function updatedSearchProduct($value)
    {
        if (empty($value)) {
            $this->searchResults = [];
            return;
        }

        $this->searchResults = Product::where('name', 'like', '%' . $value . '%')
                                    ->orWhere('sku', 'like', '%' . $value . '%')
                                    ->limit(10)
                                    ->get();
    }

    public function selectProduct($productId)
    {
        $product = Product::with('productUnits')->find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            $this->product_units = $product->productUnits;
            $this->product_unit_id = null; // Reset unit selection
            $this->price = null; // Reset price
            $this->quantity = ''; // Reset quantity
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results
        }
    }

    private function checkStockAvailability()
    {
        $this->stock_warning = '';
        
        // Only check stock when user is actively selecting product/unit/quantity for adding
        // Don't check during save or other operations
        if (!$this->product_id || !$this->product_unit_id || !$this->quantity || $this->quantity <= 0) {
            return;
        }

        $product = Product::with('productUnits')->find($this->product_id);
        if (!$product) {
            return;
        }

        // Get the selected unit - need to fetch from database if not in $this->product_units
        $selectedUnit = collect($this->product_units)->firstWhere('id', $this->product_unit_id);
        
        // If not found in current units, fetch from product relationship
        if (!$selectedUnit) {
            $unitFromDb = $product->productUnits->firstWhere('id', $this->product_unit_id);
            if ($unitFromDb) {
                $selectedUnit = [
                    'id' => $unitFromDb->id,
                    'name' => $unitFromDb->name,
                    'conversion_factor' => $unitFromDb->conversion_factor,
                ];
            }
        }
        
        if (!$selectedUnit) {
            return;
        }

        $requestedStockInBaseUnit = $this->quantity * $selectedUnit['conversion_factor'];
        
        // Calculate stock already used by ORIGINAL transaction for this product
        $stockUsedByOriginalTransaction = 0;
        foreach ($this->original_transaction_items as $item) {
            if ($item['product_id'] == $this->product_id) {
                if (isset($item['product_unit_id'])) {
                    $originalUnit = $product->productUnits->firstWhere('id', $item['product_unit_id']);
                    if ($originalUnit) {
                        $stockUsedByOriginalTransaction += $item['quantity'] * $originalUnit->conversion_factor;
                    }
                }
            }
        }
        
        // Calculate stock used by items CURRENTLY in cart (not yet saved)
        $stockUsedByCurrentCart = 0;
        foreach ($this->transaction_items as $item) {
            if ($item['product_id'] == $this->product_id) {
                if (isset($item['product_unit_id'])) {
                    $cartUnit = $product->productUnits->firstWhere('id', $item['product_unit_id']);
                    if ($cartUnit) {
                        $stockUsedByCurrentCart += $item['quantity'] * $cartUnit->conversion_factor;
                    }
                }
            }
        }
        
        // Available stock = current stock + stock from original transaction - stock in current cart
        $availableStock = $product->total_stock + $stockUsedByOriginalTransaction - $stockUsedByCurrentCart;

        if ($availableStock < $requestedStockInBaseUnit) {
            $this->stock_warning = 'Stok tidak mencukupi. Stok tersedia: ' . floor($availableStock / $selectedUnit['conversion_factor']) . ' ' . $selectedUnit['name'] . ' (Total: ' . $availableStock . ' unit dasar)';
        }
    }

    public function updatedProductUnitId($unitId)
    {
        if ($unitId) {
            $selectedUnit = collect($this->product_units)->firstWhere('id', $unitId);
            if ($selectedUnit) {
                // Use appropriate price based on invoice type
                if ($this->type === 'invoice' && $this->invoice_type === 'loan') {
                    // For loan, use purchase_price from latest batch
                    $batch = \App\Models\ProductBatch::where('product_id', $this->product_id)
                                        ->where('product_unit_id', $unitId)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                    $this->price = $batch ? $batch->purchase_price : $selectedUnit['selling_price'];
                } else {
                    // For normal/POS, use selling_price
                    $this->price = $selectedUnit['selling_price'];
                }
            }
        }
        // Only check stock when actively adding items (all fields filled)
        if ($this->product_id && $this->product_unit_id && $this->quantity) {
            $this->checkStockAvailability();
        }
    }

    public function updatedQuantity()
    {
        // Only check stock when actively adding items (all fields filled)
        if ($this->product_id && $this->product_unit_id && $this->quantity) {
            $this->checkStockAvailability();
        }
    }

    public function addItem()
    {
        $this->checkStockAvailability();
        if (!empty($this->stock_warning)) {
            return;
        }

        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);
        $selectedUnit = collect($this->product_units)->firstWhere('id', $this->product_unit_id);

        $items = $this->transaction_items;
        $items[] = [
            'product_id' => $this->product_id,
            'product_unit_id' => $this->product_unit_id,
            'product_name' => $product->name . ' (' . $selectedUnit['name'] . ')',
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity * $this->price,
        ];
        $this->transaction_items = $items;

        $this->calculateTotalPrice();
        $this->resetItemForm();
    }

    public function removeItem($index)
    {
        unset($this->transaction_items[$index]);
        $this->transaction_items = array_values($this->transaction_items); // Re-index the array
        $this->calculateTotalPrice();
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->transaction_items, 'subtotal'));
        $this->calculateGrandTotal();
    }
    
    public function updatedInvoiceType()
    {
        if ($this->invoice_type === 'loan') {
            $this->discount_amount = 0;
        }
        $this->calculateGrandTotal();
    }
    
    public function updatedDiscountAmount()
    {
        if ($this->discount_amount > $this->total_price) {
            $this->discount_amount = $this->total_price;
        }
        $this->calculateGrandTotal();
    }
    
    private function calculateGrandTotal()
    {
        $this->grand_total = $this->total_price - $this->discount_amount;
    }

    public function saveTransaction()
    {
        // Clear stock warning before validation (items already in cart are validated)
        $this->stock_warning = '';
        
        $this->validate();

        DB::transaction(function () {
            $transaction = Transaction::with('transactionDetails.transactionDetailBatches.productBatch')->findOrFail($this->transactionId);

            // Revert stock based on transactionDetailBatches
            foreach ($transaction->transactionDetails as $detail) {
                foreach ($detail->transactionDetailBatches as $detailBatch) {
                    if ($detailBatch->productBatch) {
                        $detailBatch->productBatch->increment('stock', $detailBatch->quantity);
                    }
                }
            }

            // Delete old transaction detail batches
            $transaction->transactionDetails()->each(function ($detail) {
                $detail->transactionDetailBatches()->delete();
            });


            $transaction->update([
                'type' => $this->type,
                'payment_status' => $this->payment_status,
                'total_price' => $this->total_price,
                'discount_amount' => $this->discount_amount,
                'grand_total' => $this->grand_total,
                'invoice_type' => $this->invoice_type,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
            ]);

            $currentDetailIds = collect($this->transaction_items)->pluck('id')->filter();
            $transaction->transactionDetails()->whereNotIn('id', $currentDetailIds)->delete();

            foreach ($this->transaction_items as $item) {
                $detail = $transaction->transactionDetails()->updateOrCreate(
                    ['id' => $item['id'] ?? null],
                    [
                        'product_id' => $item['product_id'],
                        'product_unit_id' => $item['product_unit_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]
                );

                $product = Product::find($item['product_id']);
                $remainingQuantity = $item['quantity'];

                $batches = $product->productBatches()->where('stock', '>', 0)->orderBy('expiration_date', 'asc')->get();

                foreach ($batches as $batch) {
                    if ($remainingQuantity <= 0) {
                        break;
                    }

                    $quantityToDeduct = min($remainingQuantity, $batch->stock);

                    $batch->decrement('stock', $quantityToDeduct);

                    TransactionDetailBatch::create([
                        'transaction_detail_id' => $detail->id,
                        'product_batch_id' => $batch->id,
                        'quantity' => $quantityToDeduct,
                    ]);

                    $remainingQuantity -= $quantityToDeduct;
                }

                if ($remainingQuantity > 0) {
                    // Allow saving even if batch stock is insufficient (create batch with remaining)
                    // throw ValidationException::withMessages(['quantity' => 'Stok produk tidak mencukupi.']);
                }
            }
        });

        session()->flash('message', 'Transaksi berhasil diperbarui.');
        return redirect()->route('transactions.index');
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->product_unit_id = '';
        $this->quantity = '';
        $this->price = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->product_units = [];
        $this->stock_warning = ''; // Clear stock warning
        $this->resetErrorBag(['product_id', 'product_unit_id', 'quantity', 'price']);
    }
}
