<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\StockMovement;
use App\Models\TransactionDetailBatch;
use App\Models\ProductUnit;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Livewire\WithPagination;

use Livewire\Attributes\Title;

#[Title('Point of Sale (New)')]
class PointOfSaleNew extends Component
{
    use WithPagination;

   public $cart_items = [];
    public $customer_id;
    public $customer_search = '';
    public $total_price = 0;
    public $focusCounter = 0; // Pemicu fokus untuk Alpine.js
    public $search = '';
    public $amount_paid;
    public $change = 0;
    public $currentDateTime;
    public $loggedInUser;
    public $invoiceNumber;
    public $isProcessing = false;
    public $print_receipt = false; // NEW: Print receipt preference
    public $selected_customer; // Track selected customer for member pricing

    protected $rules = [
        'customer_id' => 'nullable|exists:customers,id',
        'cart_items' => 'required|array|min:1',
        'amount_paid' => 'required|numeric|min:0',
    ];

    protected $messages = [
        'cart_items.required' => 'Keranjang belanja tidak boleh kosong.',
        'amount_paid.required' => 'Jumlah bayar wajib diisi.',
    ];

    public function mount()
    {
        $umumCustomer = Customer::firstOrCreate(['name' => 'UMUM'], ['phone' => null, 'address' => null]);
        $this->customer_id = $umumCustomer->id;
        $this->selected_customer = $umumCustomer;
        $this->updateDateTimeAndUser();
        $this->dispatch('focus-search-input');
    }

    private function updateDateTimeAndUser()
    {
        $this->currentDateTime = Carbon::now()->format('Y-m-d H:i:s');
        $this->loggedInUser = Auth::check() ? Auth::user()->name : 'Guest';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function searchProducts()
    {
        // This is handled by render()// Validate that all cart items have sufficient stock
    }
    private function validateCartStock()
    {
        $hasError = false;
        foreach ($this->cart_items as $index => $item) {
            $product = Product::with('productBatches')->find($item['product_id']);
            if (!$product) continue;
            $requiredBaseQty = $item['quantity']; // already in base units
            $totalStockInBaseUnits = $product->productBatches->sum('stock');
            if ($totalStockInBaseUnits < $requiredBaseQty) {
                $this->addError('cart_items', "Stok tidak cukup untuk {$item['product_name']}. Harap kurangi jumlah atau pilih satuan lain.");
                $hasError = true;
            }
        }
        return !$hasError;
    }

    /**
     * Get the appropriate price for a product unit based on customer membership
     */
    private function getMemberPrice($productUnit, $customerId = null)
    {
        $customerId = $customerId ?? $this->customer_id;
        
        if (!$customerId) {
            return $productUnit->selling_price;
        }
        
        $customer = Customer::find($customerId);
        
        // If customer is a member and product has member price, use it
        if ($customer && $customer->is_member && $productUnit->member_price !== null) {
            return $productUnit->member_price;
        }
        
        // Otherwise use regular selling price
        return $productUnit->selling_price;
    }


    // NEW: Quick add product with default unit and quantity 1
    public function quickAddProduct($productId)
    {
        $product = Product::with(['productUnits', 'productBatches'])->find($productId);
        if (!$product) return;

        // Get the default (base) unit or first available unit
        $defaultUnit = $product->productUnits->first();
        if (!$defaultUnit) {
            session()->flash('error', 'Produk tidak memiliki satuan.');
            return;
        }

        // Check stock
        $quantityInBaseUnits = 1 * $defaultUnit->conversion_factor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $quantityInBaseUnits) {
            session()->flash('error', 'Stok tidak mencukupi.');
            return;
        }

        // Check if item already exists in cart
        $foundIndex = -1;
        foreach ($this->cart_items as $index => $item) {
            if ($item['product_id'] == $product->id && $item['product_unit_id'] == $defaultUnit->id) {
                $foundIndex = $index;
                break;
            }
        }

        if ($foundIndex !== -1) {
            // Bug Fix 1: Check if adding one more exceeds stock
            $nextQuantityBase = ($this->cart_items[$foundIndex]['original_quantity_input'] + 1) * $this->cart_items[$foundIndex]['conversion_factor'];
            if ($totalStockInBaseUnits < $nextQuantityBase) {
                session()->flash('error', 'Stok tidak mencukupi untuk menambah ' . $product->name);
                return;
            }

            // Increment quantity and move to top
            $updatedItem = $this->cart_items[$foundIndex];
            $updatedItem['original_quantity_input'] += 1;
            $updatedItem['quantity'] = $nextQuantityBase;
            $updatedItem['subtotal'] = $updatedItem['original_quantity_input'] * $updatedItem['price'];
            
            unset($this->cart_items[$foundIndex]);
            $this->cart_items = array_values($this->cart_items); // Reindex
            array_unshift($this->cart_items, $updatedItem);

        } else {
            // Add new item with member pricing
            $actualPrice = $this->getMemberPrice($defaultUnit);
            $isMemberPrice = ($actualPrice != $defaultUnit->selling_price);
            
            array_unshift($this->cart_items, [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_unit_id' => $defaultUnit->id,
                'unit_name' => $defaultUnit->name,
                'conversion_factor' => $defaultUnit->conversion_factor,
                'original_quantity_input' => 1,
                'quantity' => $quantityInBaseUnits,
                'price' => $actualPrice,
                'regular_price' => $defaultUnit->selling_price,
                'is_member_price' => $isMemberPrice,
                'subtotal' => 1 * $actualPrice,
                'available_units' => $product->productUnits->map(function($u) use ($totalStockInBaseUnits) {
                    $unitStock = intdiv($totalStockInBaseUnits, $u->conversion_factor);
                    return [
                        'id' => $u->id,
                        'name' => $u->name,
                        'conversion_factor' => $u->conversion_factor,
                        'selling_price' => $u->selling_price,
                        'stock' => $unitStock
                    ];
                })->toArray()
            ]);
        }

        $this->calculateTotalPrice();
        $this->dispatch('focus-search-input');
    }

    /**
     * Logic 4: Increment quantity directly from cart card click
     */
    public function incrementQuantity($index)
    {
        if (!isset($this->cart_items[$index])) return;
        
        $item = $this->cart_items[$index];
        $currentQty = $item['original_quantity_input'];
        $product = Product::find($item['product_id']);
        
        if (!$product) return;
        
        // Find conversion factor
        $conversionFactor = $item['conversion_factor'] ?? 1;
        $newQuantityBase = ($currentQty + 1) * $conversionFactor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $newQuantityBase) {
            session()->flash('error', 'Stok tidak cukup untuk menambah ' . $product->name);
            return;
        }

        $this->updateQuantity($index, $currentQty + 1);
    }

    // NEW: Update item unit from cart
    public function updateItemUnit($index, $newUnitId)
    {
        if (!isset($this->cart_items[$index])) return;

        $item = $this->cart_items[$index];
        $availableUnits = collect($item['available_units']);
        $newUnit = $availableUnits->firstWhere('id', $newUnitId);

        if (!$newUnit) return;

        // Check stock for the new unit
        // Note: We need to re-fetch product to check stock properly or store total stock in item
        // For simplicity/performance, we'll check against the product stock again
        $product = Product::with('productBatches')->find($item['product_id']);
        if (!$product) return;

        $totalStockInBaseUnits = $product->productBatches->sum('stock');
        $quantityInBaseUnits = $item['original_quantity_input'] * $newUnit['conversion_factor'];

        if ($totalStockInBaseUnits < $quantityInBaseUnits) {
            // Bug Fix 2: Auto-clamp to max available stock for the new unit
            $maxAvailable = floor($totalStockInBaseUnits / $newUnit['conversion_factor']);
            if ($maxAvailable <= 0) {
                session()->flash('error', 'Stok tidak mencukupi untuk satuan ' . $newUnit['name']);
                return;
            }
            $item['original_quantity_input'] = $maxAvailable;
            $quantityInBaseUnits = $maxAvailable * $newUnit['conversion_factor'];
            session()->flash('warning', 'Jumlah disesuaikan ke stok maksimal: ' . $maxAvailable);
        }

        // Update item details with member pricing
        $productUnit = ProductUnit::find($newUnit['id']);
        $actualPrice = $this->getMemberPrice($productUnit);
        $isMemberPrice = ($actualPrice != $productUnit->selling_price);
        
        $this->cart_items[$index]['product_unit_id'] = $newUnit['id'];
        $this->cart_items[$index]['unit_name'] = $newUnit['name'];
        $this->cart_items[$index]['conversion_factor'] = $newUnit['conversion_factor'];
        $this->cart_items[$index]['price'] = $actualPrice;
        $this->cart_items[$index]['regular_price'] = $productUnit->selling_price;
        $this->cart_items[$index]['is_member_price'] = $isMemberPrice;
        $this->cart_items[$index]['quantity'] = $quantityInBaseUnits;
        $this->cart_items[$index]['subtotal'] = $item['original_quantity_input'] * $actualPrice;

        $this->calculateTotalPrice();
    }

    // Removed: addItemToCart and closeUnitModal - using quickAddProduct instead

    public function removeItem($index)
    {
        unset($this->cart_items[$index]);
        $this->cart_items = array_values($this->cart_items);
        $this->calculateTotalPrice();
    }

    public function updateQuantity($index, $quantity)
    {
        $quantity = (float) $quantity; // Support float/decimals if needed, or int
        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        if (!isset($this->cart_items[$index])) return;

        $item = $this->cart_items[$index];
        $product = Product::find($item['product_id']);
        
        // Find conversion factor
        $conversionFactor = 1;
        if(isset($item['conversion_factor'])) {
             $conversionFactor = $item['conversion_factor'];
        }

        if (!$product) return;

        $newQuantityBase = $quantity * $conversionFactor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $newQuantityBase) {
            // Bug Fix 2: Auto-clamp to max available stock
            $maxAvailable = floor($totalStockInBaseUnits / $conversionFactor);
            if ($maxAvailable <= 0) {
                $this->removeItem($index);
                session()->flash('error', 'Stok habis, item dihapus dari keranjang.');
                return;
            }
            $quantity = $maxAvailable;
            $newQuantityBase = $quantity * $conversionFactor;
            session()->flash('warning', 'Jumlah disesuaikan ke stok maksimal: ' . $maxAvailable);
        }

        $this->cart_items[$index]['original_quantity_input'] = $quantity;
        $this->cart_items[$index]['quantity'] = $newQuantityBase;
        $this->cart_items[$index]['subtotal'] = $quantity * $item['price'];
        $this->calculateTotalPrice();
    }

    public function updateItemQuantity($index, $value)
    {
        // Wrapper for direct input binding
        if ($value === '' || $value === null) return;
        $this->updateQuantity($index, $value);
    }

    public function checkStock($index)
    {
        // Kept for backward compatibility or specific checks, but updateQuantity handles core logic
        if (!isset($this->cart_items[$index])) return;
        
        $item = $this->cart_items[$index];
        $product = Product::find($item['product_id']);
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $item['quantity']) {
            session()->flash('error', 'Stok untuk ' . $item['product_name'] . ' tidak lagi mencukupi.');
        }
    }

    public function getSmartCashOptionsProperty()
    {
        $total = $this->total_price;
        if ($total <= 0) return [];

        $options = [];
        // Exact
        $options[] = $total;

        // Next logical amounts
        // e.g. 15.000 -> 20.000, 50.000, 100.000
        $denominations = [5000, 10000, 20000, 50000, 100000];
        
        foreach ($denominations as $denom) {
            if ($denom > $total) {
                $options[] = $denom;
            }
        }
        
        // Also consider "Total rounded up to nearest 10k or 50k" if not covered
        // e.g. 67.000 -> 70.000
        $ceil10k = ceil($total / 10000) * 10000;
        if ($ceil10k > $total && !in_array($ceil10k, $options)) {
             $options[] = $ceil10k;
        }

        // Sort and specific limits
        $options = array_unique($options);
        sort($options);
        
        return array_slice($options, 0, 4); // Return top 4 suggestions
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->cart_items, 'subtotal'));
        
        // Logic 3: Automatically sync amount_paid with total_price for speed
        $this->amount_paid = $this->total_price;
        
        $this->calculateChange();
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    private function calculateChange()
    {
        $this->change = ($this->amount_paid ?? 0) - $this->total_price;
    }

    public function checkout()
    {
        // Validate stock before proceeding
        if (!$this->validateCartStock()) {
            // Errors have been added inside validateCartStock
            return;
        }

        if ($this->isProcessing) return;

        $this->isProcessing = true;

        try {
            $this->validate();

            if ($this->amount_paid < $this->total_price) {
                $this->addError('amount_paid', 'Jumlah bayar tidak mencukupi.');
                return;
            }

            DB::beginTransaction();

            try {
                foreach ($this->cart_items as $item) {
                    $product = Product::with('productBatches')->where('id', $item['product_id'])->lockForUpdate()->first();
                    $totalStockInBaseUnits = $product->productBatches->sum('stock');

                    if ($totalStockInBaseUnits < $item['quantity']) {
                        throw new \Exception('Stok untuk ' . $item['product_name'] . ' tidak lagi mencukupi sebelum proses.');
                    }
                }

                $this->invoiceNumber = 'POS-' . Carbon::now()->format('YmdHis');

                $transaction = Transaction::create([
                'type' => 'pos',
                'payment_status' => 'paid',
                'total_price' => $this->total_price,
                'grand_total' => $this->total_price, // Added to fix SQL error
                'amount_paid' => $this->amount_paid,
                'change' => $this->change,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
                'invoice_number' => $this->invoiceNumber,
                'invoice_type' => 'normal',
                'paid_at' => Carbon::now(),
            ]);

                foreach ($this->cart_items as $item) {
                    $detail = TransactionDetail::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'product_unit_id' => $item['product_unit_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    
                }

                DB::commit();

                session()->flash('message', 'Transaksi POS berhasil dicatat dengan No is Nota: ' . $this->invoiceNumber);
                // NEW: Pass print preference to frontend
                $this->dispatch('transaction-completed', [
                    'transactionId' => $transaction->id,
                    'shouldPrint' => $this->print_receipt
                ]);
                $this->resetAll();
                $this->focusCounter++; // Picu Alpine.js untuk ambil fokus
                $this->dispatch('focus-search-input');

            } catch (\Exception $e) {
                DB::rollBack();
                $this->addError('cart_items', $e->getMessage());
                \Log::error('POS Checkout Error: ' . $e->getMessage(), ['exception' => $e]);
            }

        } finally {
            $this->isProcessing = false;
        }
    }

    private function resetAll()
    {
        $this->cart_items = [];
        $this->total_price = 0;
        $this->search = '';
        $this->amount_paid = null;
        $this->change = 0;
        $this->invoiceNumber = null;
        $this->print_receipt = false; // NEW: Reset print preference
        $this->updateDateTimeAndUser();
        $umumCustomer = Customer::firstOrCreate(['name' => 'UMUM']);
        $this->customer_id = $umumCustomer->id;
        $this->selected_customer = $umumCustomer;
        $this->resetErrorBag();
    }

    /**
     * Handle customer change - refresh cart prices based on new customer's membership status
     */
    public function updatedCustomerId($value)
    {
        $this->selected_customer = Customer::find($value);
        
        // Refresh all cart item prices based on new customer
        foreach ($this->cart_items as $index => $item) {
            $productUnit = ProductUnit::find($item['product_unit_id']);
            if ($productUnit) {
                $actualPrice = $this->getMemberPrice($productUnit);
                $isMemberPrice = ($actualPrice != $productUnit->selling_price);
                
                $this->cart_items[$index]['price'] = $actualPrice;
                $this->cart_items[$index]['regular_price'] = $productUnit->selling_price;
                $this->cart_items[$index]['is_member_price'] = $isMemberPrice;
                $this->cart_items[$index]['subtotal'] = $item['original_quantity_input'] * $actualPrice;
            }
        }
        
        $this->calculateTotalPrice();
    }

    public function updatedCustomerSearch()
    {
        // This will trigger re-render with filtered customers
    }

    public function render()
    {
        $products = Product::query();

        if (!empty($this->search)) {
            $products->where('name', 'like', '%' . $this->search . '%')
                     ->orWhere('sku', 'like', '%' . $this->search . '%');
            $products = $products->with(['productUnits', 'productBatches'])->simplePaginate(8);
        } else {
            // Logic 1: Show top 8 most sold products when search is empty
            $topProductIds = TransactionDetail::select('product_id', \DB::raw('COUNT(*) as sales_count'))
                ->groupBy('product_id')
                ->orderByDesc('sales_count')
                ->limit(8)
                ->pluck('product_id');

            if ($topProductIds->isNotEmpty()) {
                $products->whereIn('id', $topProductIds);
            } else {
                // Fallback to latest products if no sales yet
                $products->latest();
            }
            
            $products = $products->with(['productUnits', 'productBatches'])->take(8)->get();
            
            // To make it compatible with pagination links in blade, 
            // we wrap it in a dummy length aware paginator if needed, 
            // but simplePaginate(8) with empty search is better if we want pagination.
            // However, user said "Tampilkan 8 item terlaris", so exact 8 is fine.
            // Let's use simplePaginate anyway for consistent variable type.
            $products = Product::whereIn('id', $topProductIds)
                ->with(['productUnits', 'productBatches'])
                ->simplePaginate(8);
        }

        // Get customers with search filter
        $customers = Customer::query()
            ->when($this->customer_search, function($query) {
                $query->where('name', 'like', '%' . $this->customer_search . '%')
                      ->orWhere('phone', 'like', '%' . $this->customer_search . '%');
            })
            ->get();

        return view('livewire.point-of-sale-new', [
            'products' => $products,
            'customers' => $customers,
        ]);
    }
}