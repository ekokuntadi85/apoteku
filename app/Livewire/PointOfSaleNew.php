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
    public $search = '';
    public $amount_paid;
    public $change = 0;
    public $currentDateTime;
    public $loggedInUser;
    public $invoiceNumber;
    public $isProcessing = false;
    public $print_receipt = false; // NEW: Print receipt preference

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
            // Increment quantity
            $this->cart_items[$foundIndex]['original_quantity_input'] += 1;
            $this->cart_items[$foundIndex]['quantity'] += $quantityInBaseUnits;
            $this->cart_items[$foundIndex]['subtotal'] = $this->cart_items[$foundIndex]['original_quantity_input'] * $this->cart_items[$foundIndex]['price'];
        } else {
            // Add new item
            $this->cart_items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_unit_id' => $defaultUnit->id,
                'unit_name' => $defaultUnit->name,
                'conversion_factor' => $defaultUnit->conversion_factor,
                'original_quantity_input' => 1,
                'quantity' => $quantityInBaseUnits,
                'price' => $defaultUnit->selling_price,
                'subtotal' => 1 * $defaultUnit->selling_price,
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
            ];
        }

        $this->calculateTotalPrice();
        $this->dispatch('focus-search-input');
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
            session()->flash('error', 'Stok tidak mencukupi untuk satuan ' . $newUnit['name']);
            return;
        }

        // Update item details
        $this->cart_items[$index]['product_unit_id'] = $newUnit['id'];
        $this->cart_items[$index]['unit_name'] = $newUnit['name'];
        $this->cart_items[$index]['conversion_factor'] = $newUnit['conversion_factor'];
        $this->cart_items[$index]['price'] = $newUnit['selling_price'];
        $this->cart_items[$index]['quantity'] = $quantityInBaseUnits;
        $this->cart_items[$index]['subtotal'] = $item['original_quantity_input'] * $newUnit['selling_price'];

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
        $quantity = (int) $quantity;
        if ($quantity <= 0) {
            $this->removeItem($index);
            return;
        }

        $item = $this->cart_items[$index];
        $product = Product::find($item['product_id']);
        $selectedUnit = ProductUnit::find($item['product_unit_id']);

        if (!$product || !$selectedUnit) return;

        $newQuantityBase = $quantity * $selectedUnit->conversion_factor;
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $newQuantityBase) {
            session()->flash('error', 'Stok tidak cukup untuk ' . $product->name);
            return;
        }

        $this->cart_items[$index]['original_quantity_input'] = $quantity;
        $this->cart_items[$index]['quantity'] = $newQuantityBase;
        $this->cart_items[$index]['subtotal'] = $quantity * $item['price'];
        $this->calculateTotalPrice();
    }

    public function checkStock($index)
    {
        $item = $this->cart_items[$index];
        $product = Product::find($item['product_id']);
        $totalStockInBaseUnits = $product->productBatches->sum('stock');

        if ($totalStockInBaseUnits < $item['quantity']) {
            session()->flash('error', 'Stok untuk ' . $item['product_name'] . ' tidak lagi mencukupi.');
        }
    }

    private function calculateTotalPrice()
    {
        $this->total_price = array_sum(array_column($this->cart_items, 'subtotal'));
        $this->calculateChange();
    }

    public function updatedAmountPaid()
    {
        $this->calculateChange();
    }

    private function calculateChange()
    {
        $this->change = $this->amount_paid - $this->total_price;
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
                    'type' => 'POS',
                    'payment_status' => 'paid',
                    'total_price' => $this->total_price,
                    'amount_paid' => $this->amount_paid,
                    'change' => $this->change,
                    'customer_id' => $this->customer_id,
                    'user_id' => Auth::id(),
                    'invoice_number' => $this->invoiceNumber,
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
        $this->resetErrorBag();
    }

    public function getCustomersProperty()
    {
        return Customer::where('name', 'like', '%'.$this->customer_search.'%')->get();
    }

    public function render()
    {
        $products = Product::query();

        if (!empty($this->search)) {
            $products->where('name', 'like', '%' . $this->search . '%')
                     ->orWhere('sku', 'like', '%' . $this->search . '%');
        }

        $products = $products->with(['productUnits', 'productBatches'])->paginate(10);

        return view('livewire.point-of-sale-new', [
            'products' => $products,
            'customers' => $this->customers,
        ]);
    }
}