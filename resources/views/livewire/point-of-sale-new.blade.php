<div class="w-full h-screen flex flex-col md:flex-row bg-gray-100 dark:bg-gray-900 font-sans" x-data="{ paymentModal: false }">

    <!-- Main Content Area (Search and Cart) -->
    <div class="flex-1 flex flex-col p-4 md:p-6">
        <!-- Success Message -->
        @if (session()->has('message'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="mb-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded shadow-md flex justify-between items-center" 
                 role="alert">
                <div>
                    <p class="font-bold">Sukses!</p>
                    <p>{{ session('message') }}</p>
                </div>
                <button @click="show = false" class="text-emerald-700 hover:text-emerald-900">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Error Message -->
        @if (session()->has('error'))
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="mb-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 text-red-700 dark:text-red-200 p-4 rounded shadow-md flex justify-between items-center" 
                 role="alert">
                <div>
                    <p class="font-bold">Error!</p>
                    <p>{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-red-700 hover:text-red-900">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <!-- Header with Search -->
        <header class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Point of Sale</h1>
            <div class="relative w-full">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text"
                        id="search-product-input"
                        wire:model.live.debounce.300ms="search"
                        wire:keydown.enter.prevent="searchProducts"
                        placeholder="Cari produk berdasarkan nama atau SKU..."
                        class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            </div>
        </header>

        <!-- Search Results -->
        <main class="flex-1 overflow-y-auto bg-white dark:bg-gray-800 rounded-lg shadow-inner p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-2 gap-4">
                @if (!empty($search))
                    @forelse ($products as $product)
                        @php
                            $totalStock = $product->productBatches->sum('stock');
                            $isOutOfStock = $totalStock <= 0;
                        @endphp
                        <div 
                            @if(!$isOutOfStock) wire:click="quickAddProduct({{ $product->id }})" @endif
                            class="bg-white dark:bg-gray-700 rounded-xl p-4 flex flex-col justify-between border border-gray-100 dark:border-gray-600 transition-all duration-200 {{ $isOutOfStock ? 'opacity-60 cursor-not-allowed grayscale' : 'cursor-pointer hover:shadow-lg hover:scale-105 hover:ring-2 hover:ring-emerald-500' }}">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white truncate text-lg">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">SKU: {{ $product->sku }}</p>
                            </div>
                            <div class="flex justify-between items-end mt-2">
                                <div class="flex flex-wrap gap-1">
                                    @foreach($product->productUnits as $unit)
                                        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-600 px-2 py-1 rounded">
                                            {{ $unit->name }} (Stock: {{ intdiv($product->productBatches->sum('stock'), $unit->conversion_factor) }})
                                        </span>
                                    @endforeach
                                </div>
                                @if($isOutOfStock)
                                    <span class="text-xs font-bold text-red-600 bg-red-100 dark:bg-red-900/30 dark:text-red-400 px-2 py-1 rounded">Stok Habis</span>
                                @else
                                    <p class="text-right font-bold text-emerald-600 dark:text-emerald-400 text-lg">
                                        Rp {{ number_format($product->productUnits->first()?->selling_price ?? 0, 0) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-10">
                            <p class="text-gray-500 dark:text-gray-400">Produk tidak ditemukan untuk "{{ $search }}".</p>
                        </div>
                    @endforelse
                @else
                    <div class="col-span-full text-center py-10 flex flex-col items-center">
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">Mulai transaksi dengan mencari produk.</p>
                    </div>
                @endif
            </div>
            @if(!empty($search))
            <div class="mt-4">
                {{ $products->links() }}
            </div>
            @endif
        </main>
    </div>

    <!-- Right Sidebar (Cart and Payment) -->
    <aside class="w-full md:w-2/5 lg:w-1/3 bg-white dark:bg-gray-800 shadow-2xl flex flex-col">
        <!-- Cart Items -->
        <div class="p-6 flex-1 overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Keranjang</h2>
            <div class="space-y-4">
                @forelse($cart_items as $index => $item)
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-4 flex items-start space-x-4">
                        <div class="flex-1">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">@ {{ number_format($item['price'], 0) }}</p>
                            
                            <div class="flex items-center space-x-2">
                                <div class="flex items-center bg-gray-200 dark:bg-gray-600 rounded-lg">
                                    <button wire:click="updateQuantity({{ $index }}, {{ $item['original_quantity_input'] - 1 }}); checkStock({{ $index }})" class="px-3 py-1 text-lg font-bold hover:bg-gray-300 dark:hover:bg-gray-500 rounded-l-lg transition-colors">-</button>
                                    <input type="text" value="{{ $item['original_quantity_input'] }}" readonly class="w-10 text-center bg-transparent font-semibold text-gray-900 dark:text-white border-none p-0 focus:ring-0">
                                    <button wire:click="updateQuantity({{ $index }}, {{ $item['original_quantity_input'] + 1 }}); checkStock({{ $index }})" class="px-3 py-1 text-lg font-bold hover:bg-gray-300 dark:hover:bg-gray-500 rounded-r-lg transition-colors">+</button>
                                </div>

                                @if(isset($item['available_units']) && count($item['available_units']) > 1)
                                    <select wire:change="updateItemUnit({{ $index }}, $event.target.value)" 
                                            class="py-1 pl-3 pr-8 h-[36px] text-sm font-semibold border-gray-300 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors">
                                            @foreach($item['available_units'] as $unit)
                                                <option value="{{ $unit['id'] }}" @selected($unit['id'] == $item['product_unit_id']) @if($unit['stock'] <= 0) disabled @endif>
                                                    {{ $unit['name'] }} ({{ $unit['stock'] }})
                                                </option>
                                            @endforeach
                                    </select>
                                @else
                                    <span class="h-[36px] flex items-center px-3 bg-gray-200 dark:bg-gray-600 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-200">
                                        {{ $item['unit_name'] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0) }}</p>
                            <button wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-xs font-medium mt-2">Hapus</button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-16">
                        <p class="text-gray-500 dark:text-gray-400">Keranjang belanja kosong.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Payment Section -->
        <div class="p-6 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700">
            <!-- Total Display -->
            <div class="flex justify-between items-center mb-4">
                <span class="text-xl font-semibold text-gray-700 dark:text-gray-200">Total</span>
                <span class="text-4xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0) }}</span>
            </div>

            <!-- Customer Selection (Simplified) -->
            <div class="mb-4">
                <select wire:model="customer_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Quick Cash Buttons -->
            <div class="grid grid-cols-3 gap-2 mb-3">
                <button wire:click="$set('amount_paid', 10000)" class="py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 font-medium transition-colors">10K</button>
                <button wire:click="$set('amount_paid', 20000)" class="py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 font-medium transition-colors">20K</button>
                <button wire:click="$set('amount_paid', 50000)" class="py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 font-medium transition-colors">50K</button>
                <button wire:click="$set('amount_paid', 100000)" class="py-2 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/50 font-medium transition-colors">100K</button>
                <button wire:click="$set('amount_paid', {{ $total_price }})" class="py-2 bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/50 font-medium transition-colors">Uang Pas</button>
                <button wire:click="$set('amount_paid', 0)" class="py-2 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Reset</button>
            </div>

            <!-- Large Cash Input -->
            <div class="relative mb-4">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500 text-xl">Rp</span>
                <input type="number" 
                       wire:model.live="amount_paid" 
                       placeholder="0"
                       class="w-full h-14 pl-10 pr-4 text-3xl font-bold text-right rounded-xl border-2 border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                >
            </div>

            <!-- Change Display -->
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 text-white p-3 rounded-xl mb-3 shadow-lg shadow-emerald-500/20">
                <div class="flex justify-between items-center">
                    <span class="font-medium opacity-90">Kembalian</span>
                    <span class="text-xl font-bold">Rp {{ number_format($change, 0) }}</span>
                </div>
            </div>

            <!-- Print Receipt Checkbox -->
            <label class="flex items-center justify-center mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <input type="checkbox" wire:model="print_receipt" class="w-5 h-5 text-emerald-600 rounded border-gray-300 focus:ring-emerald-500">
                <span class="ml-2 text-gray-700 dark:text-gray-300 font-medium">Cetak Struk Transaksi</span>
            </label>

            <!-- Checkout Button -->
            <button wire:click="checkout" 
                    wire:loading.attr="disabled" 
                    class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold py-4 rounded-xl text-xl hover:from-emerald-700 hover:to-teal-700 shadow-lg hover:shadow-xl hover:shadow-emerald-500/30 transition-all transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="{{ count($cart_items) === 0 ? 'true' : 'false' }}">
                <span wire:loading.remove>Selesaikan Transaksi</span>
                <span wire:loading class="flex items-center justify-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </aside>

    <!-- Modals Removed -->

    @script
    <script>
        Livewire.on('transaction-completed', (event) => {
            const { transactionId, shouldPrint } = event[0];

            if (shouldPrint) {
                const url = `/transactions/${transactionId}/receipt`;
                // Open receipt in new tab - auto-close is handled by receipt page itself
                window.open(url, '_blank');
            }
            
            // Focus back to search input for next transaction
            setTimeout(() => {
                document.getElementById('search-product-input').focus();
            }, 100);
        });

        // Auto focus on load
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                document.getElementById('search-product-input')?.focus();
            }, 100);
        });

        Livewire.on('focus-search-input', () => {
            document.getElementById('search-product-input').focus();
        });
    </script>
    @endscript
</div>