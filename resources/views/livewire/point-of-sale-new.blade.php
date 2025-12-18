<div class="h-screen w-full flex flex-col bg-gray-50 dark:bg-gray-900 font-sans overflow-hidden" 
     x-data="{ 
        searchFocus() { $nextTick(() => document.getElementById('search-product-input').focus()) },
        payFocus() { $nextTick(() => document.getElementById('amount-paid-input').focus()) }
     }"
     @keydown.slash.prevent="searchFocus()"
     @keydown.f2.prevent="payFocus()"
     @keydown.escape="document.activeElement.blur()"
>

    <!-- Global Toast Notifications -->
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4 pointer-events-none">
        @if (session()->has('message'))
            <div class="bg-emerald-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center justify-between mb-2 pointer-events-auto" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="font-bold">{{ session('message') }}</span>
                </div>
                <button @click="show = false" class="text-white hover:text-gray-200"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="bg-red-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center justify-between pointer-events-auto" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
                <div class="flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="font-bold">{{ session('error') }}</span>
                </div>
                <button @click="show = false" class="text-white hover:text-gray-200"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
            </div>
        @endif
    </div>

    <!-- Top Navigation Bar -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shrink-0 z-10 shadow-sm flex flex-col md:flex-row items-center justify-between px-4 py-2 md:h-16 gap-3 md:gap-0">
        
        <!-- Left: Search Bar -->
        <div class="w-full md:w-1/3 max-w-md relative order-2 md:order-1">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text"
                id="search-product-input"
                wire:model.live.debounce.300ms="search"
                wire:keydown.enter.prevent="searchProducts"
                placeholder="Cari... (/)"
                class="w-full pl-10 pr-10 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition-shadow sm:text-sm"
            >
            <!-- Clear Button -->
            @if(!empty($search))
                <button wire:click="$set('search', '')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            @endif
        </div>

        <!-- Center: Customer Selection -->
        <div class="w-full md:w-1/3 flex justify-between md:justify-center items-center order-1 md:order-2">
            <!-- Mobile Logo/Title override -->
            <h1 class="font-bold text-gray-800 dark:text-gray-200 md:hidden text-lg">POS</h1>

            <div class="w-2/3 md:w-full max-w-xs relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                     <svg class="w-5 h-5 text-gray-400 group-focus-within:text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
                <select wire:model="customer_id" 
                    class="w-full pl-10 pr-8 py-2 appearance-none rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent cursor-pointer font-medium sm:text-sm">
                    @foreach($this->customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                     <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
             <!-- Mobile Time -->
             <div class="md:hidden bg-emerald-100 dark:bg-emerald-900/30 px-2 py-1 rounded text-emerald-700 dark:text-emerald-400 font-mono font-bold text-sm">
                   {{ \Carbon\Carbon::now()->format('H:i') }}
            </div>
        </div>

        <!-- Right: Status -->
        <div class="w-1/3 hidden md:flex justify-end items-center space-x-4 order-3">
            <div class="text-right">
                <p class="text-xs text-gray-500 dark:text-gray-400">Kasir</p>
                <p class="text-sm font-bold text-gray-800 dark:text-gray-200 truncate max-w-[150px]">{{ $loggedInUser }}</p>
            </div>
            <div class="bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 rounded-md">
                <p class="text-emerald-700 dark:text-emerald-400 font-mono font-bold text-lg" 
                   x-data="{ time: '' }" x-init="setInterval(() => time = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'}), 1000); time = new Date().toLocaleTimeString('en-GB', {hour: '2-digit', minute:'2-digit'})" x-text="time">
                   {{ \Carbon\Carbon::now()->format('H:i') }}
                </p>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
        
        <!-- Left Panel: Product Grid -->
        <main class="flex-1 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            
            <!-- Mobile Customer Select (Visible only on small screens) -->
            <div class="md:hidden mb-4">
                 <select wire:model="customer_id" class="w-full rounded-lg border-gray-300 dark:bg-gray-700 dark:border-gray-600">
                    @foreach($this->customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Messages - Removed old static position, moved to Global Toast -->

            <!-- Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 pb-24 md:pb-0">
                 @forelse ($products as $product)
                    @php
                        $totalStock = $product->productBatches->sum('stock');
                        $isOutOfStock = $totalStock <= 0;
                        $price = $product->productUnits->first()?->selling_price ?? 0;
                    @endphp
                    <div 
                        @if(!$isOutOfStock) wire:click="quickAddProduct({{ $product->id }})" @endif
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md border {{ $isOutOfStock ? 'border-red-200 dark:border-red-900 opacity-60' : 'border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500' }} p-3 flex flex-col justify-between transition-all duration-200 cursor-pointer h-[130px] relative group overflow-hidden"
                    >
                        <div>
                            <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-sm leading-tight line-clamp-2 mb-1" title="{{ $product->name }}">{{ $product->name }}</h3>
                            <p class="text-[10px] text-gray-400 uppercase tracking-wider">{{ $product->sku }}</p>
                        </div>

                        <div class="mt-2">
                             @if($isOutOfStock)
                                <div class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 text-xs font-bold px-2 py-1 rounded text-center">
                                    Habis
                                </div>
                             @else
                                <p class="text-emerald-600 dark:text-emerald-400 font-bold text-base">
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </p>
                                <div class="flex justify-between items-end mt-1">
                                     <span class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded">
                                        {{ intdiv($totalStock, $product->productUnits->first()?->conversion_factor ?? 1) }} Unit
                                     </span>
                                     @if($product->productUnits->count() > 1)
                                        <span class="text-[10px] text-blue-500 dark:text-blue-400 font-medium">+Mode</span>
                                     @endif
                                </div>
                             @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full h-40 flex flex-col items-center justify-center text-gray-400">
                        <p>Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-4">
                 {{ $products->links() }}
            </div>
        </main>

        <!-- Right Panel: Cart & Payment -->
        <!-- Responsive change: w-full on mobile, fixed width on desktop -->
        <aside class="w-full md:w-[35%] lg:w-[400px] flex flex-col bg-white dark:bg-gray-800 border-t md:border-t-0 md:border-l border-gray-200 dark:border-gray-700 shrink-0 shadow-xl z-20 h-[45vh] md:h-full">
            
            <!-- Cart Header -->
             <div class="p-3 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center border-b border-gray-200 dark:border-gray-700">
                <h2 class="font-bold text-gray-700 dark:text-gray-200 text-sm">Pesanan</h2>
                <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ count($cart_items) }}</span>
            </div>

            <!-- Cart Items List -->
            <div class="flex-1 overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-gray-300">
                <div class="space-y-2">
                    @forelse($cart_items as $index => $item)
                        <div class="bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 p-2 text-sm relative hover:border-emerald-400">
                            <div class="flex justify-between items-start mb-1">
                                <span class="font-semibold text-gray-800 dark:text-gray-200 line-clamp-1 w-4/5">{{ $item['product_name'] }}</span>
                                <button wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                            
                            <div class="flex items-center justify-between mt-2">
                                <div class="flex items-center space-x-1">
                                     <input type="number" 
                                            value="{{ $item['original_quantity_input'] }}" 
                                            wire:blur="updateItemQuantity({{ $index }}, $event.target.value)"
                                            wire:keydown.enter="$event.target.blur()"
                                            class="w-12 px-1 py-1 text-center font-bold border rounded bg-gray-50 dark:bg-gray-600 dark:text-white border-gray-300 dark:border-gray-500 focus:ring-1 focus:ring-emerald-500 text-sm"
                                     >
                                     @if(isset($item['available_units']) && count($item['available_units']) > 1)
                                        <select wire:change="updateItemUnit({{ $index }}, $event.target.value)" 
                                                class="w-20 text-xs py-1 pl-1 pr-4 border-none bg-transparent focus:ring-0 cursor-pointer">
                                                @foreach($item['available_units'] as $unit)
                                                    <option value="{{ $unit['id'] }}" @selected($unit['id'] == $item['product_unit_id'])>{{ $unit['name'] }}</option>
                                                @endforeach
                                        </select>
                                     @else
                                        <span class="text-xs text-gray-500 px-1">{{ $item['unit_name'] }}</span>
                                     @endif
                                </div>
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ number_format($item['subtotal'], 0, ',', '.') }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex flex-col items-center justify-center h-full text-gray-400 text-xs">
                             <p>Keranjang Kosong</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Payment Area -->
            <div class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-3 shrink-0 shadow-inner">
                
                <div class="flex justify-between items-center mb-3">
                    <span class="text-gray-500 text-sm">Total</span>
                    <span class="text-2xl font-extrabold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0, ',', '.') }}</span>
                </div>

                <div class="space-y-2">
                    
                    <!-- Smart Cash + Uang Pas -->
                     @if(count($this->smart_cash_options) > 0)
                    <div class="grid grid-cols-4 gap-2">
                        @foreach($this->smart_cash_options as $option)
                            <!-- Skip exact match in loop if we want a dedicated button, or visually highlight it -->
                            <button 
                                wire:click="$set('amount_paid', {{ $option }})"
                                class="bg-white dark:bg-gray-800 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 py-1.5 rounded text-xs font-bold hover:bg-emerald-50 transition-colors"
                            >
                                {{ number_format($option/1000, 0) }}k
                            </button>
                        @endforeach
                    </div>
                     @endif
                    
                    <!-- Dedicated Buttons Row -->
                    <div class="grid grid-cols-2 gap-2">
                         <button wire:click="$set('amount_paid', {{ $total_price }})" class="bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800 py-1.5 rounded text-xs font-bold hover:bg-blue-100 uppercase tracking-wide">
                            Uang Pas
                         </button>
                         <button wire:click="$set('amount_paid', '')" class="bg-gray-100 text-gray-600 border border-gray-200 py-1.5 rounded text-xs font-bold hover:bg-gray-200">
                            Reset
                         </button>
                    </div>

                    <!-- Input -->
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 font-bold text-sm">Rp</span>
                         <input type="number" 
                            id="amount-paid-input"
                            wire:model.live="amount_paid" 
                            placeholder="Input (F2)"
                            class="w-full pl-8 pr-3 py-2 text-lg font-bold text-right rounded border border-gray-300 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:bg-gray-800 dark:text-white @error('amount_paid') border-red-500 ring-1 ring-red-500 @enderror"
                        >
                    </div>
                    @error('amount_paid') <span class="text-xs text-red-500 font-bold text-right block">{{ $message }}</span> @enderror
                    
                    <!-- Change -->
                     @if($amount_paid >= $total_price && $total_price > 0)
                        <div class="flex justify-between items-center bg-emerald-100 px-3 py-1.5 rounded text-emerald-800">
                            <span class="text-xs font-bold uppercase">Kembali</span>
                            <span class="text-lg font-bold">{{ number_format($change, 0, ',', '.') }}</span>
                        </div>
                     @endif

                    <div class="flex items-center justify-between my-2">
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox" wire:model="print_receipt" class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                            <span class="text-xs text-gray-600">Cetak Struk</span>
                        </label>
                    </div>

                    @error('cart_items') <div class="text-xs text-red-500 font-bold text-center mb-1">{{ $message }}</div> @enderror
                    <button wire:click="checkout" 
                            wire:loading.attr="disabled"
                            :disabled="{{ count($cart_items) === 0 ? 'true' : 'false' }}"
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded shadow-lg transition-transform active:scale-95 flex justify-center items-center relative"
                    >
                         <span wire:loading.remove>BAYAR</span>
                         <span wire:loading wire:target="checkout" class="text-sm flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Proses...
                         </span>
                    </button>
                </div>
            </div>
        </aside>

    </div>

    <!-- Scripts -->
    @script
    <script>
        Livewire.on('transaction-completed', (event) => {
            const { transactionId, shouldPrint } = event[0];
            if (shouldPrint) {
                window.open(`/transactions/${transactionId}/receipt`, '_blank');
            }
            setTimeout(() => {
                document.getElementById('search-product-input').focus();
            }, 100);
        });

        Livewire.on('focus-search-input', () => {
             document.getElementById('search-product-input').focus();
        });

        // Initialize focus
        document.addEventListener('DOMContentLoaded', () => {
             document.getElementById('search-product-input')?.focus();
        });
    </script>
    @endscript
</div>
