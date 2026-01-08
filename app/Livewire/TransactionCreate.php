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

#[Title('Buat Transaksi')]
class TransactionCreate extends Component
{
    public $type = 'pos'; // Default to POS
    public $payment_status = 'paid'; // Default to paid
    public $total_price = 0;
    public $due_date;
    public $customer_id;

    // For product search
    public $searchProduct = '';
    public $searchResults = [];
    public $selectedProductName = '';

    public $product_id;
    public $quantity;
    public $price;
    public $transaction_items = [];
    public $selected_customer; // Track selected customer for member pricing

    protected $rules = [
        'type' => 'required|in:pos,invoice',
        'payment_status' => 'required|in:paid,unpaid',
        'total_price' => 'required|numeric|min:0',
        'due_date' => 'nullable|date',
        'customer_id' => 'nullable|exists:customers,id',
        'transaction_items' => 'required|array|min:1',
        'transaction_items.*.product_id' => 'required|exists:products,id',
        'transaction_items.*.quantity' => 'required|integer|min:1',
        'transaction_items.*.price' => 'required|numeric|min:0',
    ];

    protected $itemRules = [
        'product_id' => 'required|exists:products,id',
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

    public function mount()
    {
        // Set default customer to 'UMUM'
        $umumCustomer = Customer::firstOrCreate(
            ['name' => 'UMUM'],
            ['phone' => null, 'address' => null]
        );
        $this->customer_id = $umumCustomer->id;
        $this->selected_customer = $umumCustomer;
    }

    public function render()
    {
        $customers = Customer::all();
        return view('livewire.transaction-create', compact('customers'));
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

    public function selectProduct($productId)
    {
        $product = Product::with('productUnits')->find($productId);
        if ($product) {
            $this->product_id = $product->id;
            $this->selectedProductName = $product->name;
            
            // Get the first/base product unit and apply member pricing
            $productUnit = $product->productUnits->first();
            if ($productUnit) {
                $this->price = $this->getMemberPrice($productUnit);
            } else {
                $this->price = 0;
            }
            
            $this->searchProduct = ''; // Clear search input
            $this->searchResults = []; // Clear search results
        }
    }

    /**
     * Handle customer change - refresh transaction item prices
     */
    public function updatedCustomerId($value)
    {
        $this->selected_customer = Customer::find($value);
        
        // If there's a selected product, refresh its price
        if ($this->product_id) {
            $product = Product::with('productUnits')->find($this->product_id);
            if ($product) {
                $productUnit = $product->productUnits->first();
                if ($productUnit) {
                    $this->price = $this->getMemberPrice($productUnit);
                }
            }
        }
    }

    public function addItem()
    {
        $this->validate($this->itemRules);

        $product = Product::find($this->product_id);

        // Check stock before adding item
        if ($product->total_stock < $this->quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok produk tidak mencukupi. Stok tersedia: ' . $product->total_stock,
            ]);
        }

        $this->transaction_items[] = [
            'product_id' => $this->product_id,
            'product_name' => $product->name,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'subtotal' => $this->quantity * $this->price,
        ];

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
    }

    public function saveTransaction()
    {
        $this->validate();

        DB::transaction(function () {
            $transaction = Transaction::create([
                'type' => $this->type,
                'payment_status' => $this->payment_status,
                'total_price' => $this->total_price,
                'due_date' => $this->due_date,
                'customer_id' => $this->customer_id,
                'user_id' => Auth::id(),
            ]);

            foreach ($this->transaction_items as $item) {
                $detail = TransactionDetail::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);

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
                    throw ValidationException::withMessages(['quantity' => 'Stok produk ' . $product->name . ' tidak mencukupi.']);
                }
            }
        });

        session()->flash('message', 'Transaksi berhasil dicatat.');
        $this->resetAll();
        return redirect()->route('transactions.index');
    }

    private function resetItemForm()
    {
        $this->product_id = '';
        $this->quantity = '';
        $this->price = '';
        $this->searchProduct = '';
        $this->searchResults = [];
        $this->selectedProductName = '';
        $this->resetErrorBag(['product_id', 'quantity', 'price']);
    }

    private function resetAll()
    {
        $this->type = 'pos';
        $this->payment_status = 'paid'; // Default to paid
        $this->total_price = 0;
        $this->due_date = '';
        $this->customer_id = Customer::firstOrCreate(['name' => 'UMUM'])->id; // Reset to UMUM customer
        $this->transaction_items = [];
        $this->resetItemForm();
        $this->resetErrorBag();
    }
}