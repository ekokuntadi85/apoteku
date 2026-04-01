<div id="pos-root" class="h-screen w-full flex flex-col bg-gray-50 dark:bg-gray-900 font-sans overflow-hidden"
     @reset-mobile-tab.window="mobileTab = 'products'"
     x-data="{
        mobileTab: 'products',
        needsFocus: false,
        searchFocus() { $nextTick(() => document.getElementById('search-product-input').focus()) },
     }"
     @transaction-completed.window="
         const data = Array.isArray($event.detail) ? $event.detail[0] : $event.detail;
         if (data && data.shouldPrint && data.transactionId) {
             window.open(`/transactions/${data.transactionId}/receipt`, '_blank');
         }
         mobileTab = 'products';
         
         setTimeout(() => {
             const el = document.getElementById('search-product-input');
             if (el && !document.hidden) {
                 el.focus();
             } else {
                 needsFocus = true;
             }
         }, 300);
     "
     @focus-search-input.window="
         setTimeout(() => {
             const el = document.getElementById('search-product-input');
             if (el && !document.hidden) {
                 el.focus();
             } else {
                 needsFocus = true;
             }
         }, 100);
     "
     @focus.window="
         if (needsFocus) {
             setTimeout(() => {
                 const el = document.getElementById('search-product-input');
                 if (el) el.focus();
                 needsFocus = false;
             }, 100);
         }
     "
     @keydown.slash.prevent="searchFocus()"
     @keydown.escape.window.stop="$wire.set('search', ''); searchFocus();"
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
                x-init="$el.focus()"
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

            <!-- Customer Selector Container -->
            <div class="flex items-center gap-2 w-2/3 md:w-full max-w-xs">
                <!-- Searchable Customer Dropdown -->
                <div class="flex-1 relative" 
                     x-data="{ 
                         open: false, 
                         search: '',
                         customers: @js($customers),
                         get filteredCustomers() {
                             if (!this.search) return this.customers;
                             return this.customers.filter(c => 
                                 c.name.toLowerCase().includes(this.search.toLowerCase()) ||
                                 (c.phone && c.phone.includes(this.search))
                             );
                         },
                         selectCustomer(id) {
                             $wire.set('customer_id', id);
                             this.open = false;
                             this.search = '';
                         }
                     }"
                     @click.away="open = false"
                     @keydown.escape="open = false">
                    
                    <!-- Trigger Button -->
                    <button type="button" @click="open = !open"
                        class="w-full pl-10 pr-8 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent cursor-pointer font-medium sm:text-sm text-left flex items-center justify-between">
                        <span class="truncate">{{ $selected_customer ? $selected_customer->name : 'Pilih Customer' }}</span>
                        <svg class="w-4 h-4 text-gray-400 ml-2" :class="{'rotate-180': open}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    
                    <!-- Icon -->
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                    </div>
                    
                    <!-- Dropdown Panel -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute z-50 mt-1 w-full bg-white dark:bg-gray-700 rounded-lg shadow-lg border border-gray-200 dark:border-gray-600 max-h-80 overflow-hidden"
                         style="display: none;">
                        
                        <!-- Search Input -->
                        <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                            <input type="text" 
                                   x-model="search"
                                   x-ref="searchInput"
                                   @click.stop
                                   placeholder="Cari customer..."
                                   class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        
                        <!-- Customer List -->
                        <div class="max-h-60 overflow-y-auto">
                            <template x-for="customer in filteredCustomers" :key="customer.id">
                                <button type="button"
                                        @click="selectCustomer(customer.id)"
                                        class="w-full px-4 py-2 text-left hover:bg-emerald-50 dark:hover:bg-gray-600 flex items-center justify-between group"
                                        :class="{'bg-emerald-100 dark:bg-gray-600': customer.id == $wire.customer_id}">
                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate" x-text="customer.name"></span>
                                    <span x-show="customer.is_member" class="bg-gradient-to-r from-amber-400 to-amber-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full whitespace-nowrap ml-2">
                                        ⭐ MEMBER
                                    </span>
                                </button>
                            </template>
                            <div x-show="filteredCustomers.length === 0" class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                                Tidak ada customer ditemukan
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Member Badge (only show when customer is member, hidden on mobile to avoid layout overflow) -->
                @if($selected_customer && $selected_customer->is_member)
                    <span class="hidden md:inline-flex bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm whitespace-nowrap">
                        ⭐ MEMBER
                    </span>
                @endif
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

    <!-- Mobile Tab Bar (hidden on md+ desktop) -->
    <div class="md:hidden flex shrink-0 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <button
            @click="mobileTab = 'products'"
            class="flex-1 py-3 text-sm font-semibold flex items-center justify-center gap-1.5 border-b-2 transition-colors duration-200"
            :class="mobileTab === 'products' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 dark:text-gray-400'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            Produk
        </button>
        <button
            @click="mobileTab = 'cart'"
            class="flex-1 py-3 text-sm font-semibold flex items-center justify-center gap-1.5 border-b-2 transition-colors duration-200"
            :class="mobileTab === 'cart' ? 'border-emerald-500 text-emerald-600 dark:text-emerald-400' : 'border-transparent text-gray-500 dark:text-gray-400'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Pesanan
            @if(count($cart_items) > 0)
            <span class="bg-emerald-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] px-1 rounded-full flex items-center justify-center leading-none">
                {{ count($cart_items) }}
            </span>
            @endif
        </button>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
        
        <!-- Left Panel: Product Grid -->
        <main :class="mobileTab !== 'products' ? 'hidden md:block' : ''" class="flex-1 min-w-0 overflow-y-auto p-4 bg-gray-50 dark:bg-gray-900 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            
            <!-- Grid (3 Kolom pada Desktop agar lebih lega) -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 pb-24 md:pb-0">
                 @forelse ($products as $product)
                    @php
                        $totalStock = $product->productBatches->sum('stock');
                        $isOutOfStock = $totalStock <= 0;
                        $price = $product->productUnits->first()?->selling_price ?? 0;
                    @endphp
                    <div
                        @if(!$isOutOfStock) wire:click="quickAddProduct({{ $product->id }})" @endif
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm hover:shadow-md border {{ $isOutOfStock ? 'border-red-200 dark:border-red-900 opacity-60' : 'border-gray-200 dark:border-gray-700 hover:border-emerald-500 dark:hover:border-emerald-500' }} p-2.5 flex flex-col gap-1 transition-all duration-200 cursor-pointer relative group overflow-hidden"
                    >
                        {{-- Baris 1: Nama Produk --}}
                        <h3 class="font-semibold text-gray-800 dark:text-gray-200 text-sm leading-tight line-clamp-1" title="{{ $product->name }}">{{ $product->name }}</h3>

                        {{-- Baris 2: SKU (kiri) + Stok/Habis (kanan) --}}
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] text-gray-400 uppercase tracking-wider truncate max-w-[55%]">{{ $product->sku }}</span>
                            @if($isOutOfStock)
                                <span class="text-[10px] text-red-500 dark:text-red-400 font-bold bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded">Habis</span>
                            @else
                                <span class="text-[10px] text-gray-500 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded shrink-0">
                                    {{ intdiv($totalStock, $product->productUnits->first()?->conversion_factor ?? 1) }} Unit
                                </span>
                            @endif
                        </div>

                        {{-- Baris 3: Harga (kiri) + +Mode (kanan) --}}
                        @if(!$isOutOfStock)
                        <div class="flex justify-between items-center">
                            <span class="text-emerald-600 dark:text-emerald-400 font-bold text-sm leading-none">
                                Rp {{ number_format($price, 0, ',', '.') }}
                            </span>
                            @if($product->productUnits->count() > 1)
                                <span class="text-[10px] text-blue-500 dark:text-blue-400 font-medium">+Mode</span>
                            @endif
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full h-40 flex flex-col items-center justify-center text-gray-400">
                        <p>Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
            
            <div class="mt-2 flex justify-center">
                 {{ $products->links() }}
            </div>
        </main>

        {{-- ── DESKTOP CART (hidden on mobile, file: pos-partials/cart-desktop.blade.php) ── --}}
        @include('livewire.pos-partials.cart-desktop')

        {{-- ── MOBILE CART (hidden on desktop, file: pos-partials/cart-mobile.blade.php) ── --}}
        <div x-show="mobileTab === 'cart'" style="display:none" class="h-full md:hidden">
            @include('livewire.pos-partials.cart-mobile')
        </div>

    </div>

    <!-- Floating Cart Bar - Mobile Only, shown on products tab when cart has items -->
    @if(count($cart_items) > 0)
    <div
        class="md:hidden fixed bottom-0 left-0 right-0 z-30 px-3 pb-3"
        x-show="mobileTab === 'products'"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-3"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-3"
    >
        <div class="bg-emerald-600 rounded-2xl shadow-2xl p-4 flex items-center justify-between">
            <div>
                <p class="text-emerald-100 text-xs font-medium">{{ count($cart_items) }} item dipilih</p>
                <p class="text-white text-xl font-extrabold tracking-tight">Rp {{ number_format($total_price, 0, ',', '.') }}</p>
            </div>
            <button
                @click="mobileTab = 'cart'"
                class="bg-white text-emerald-700 font-bold px-5 py-2.5 rounded-xl text-sm shadow-lg active:scale-95 transition-transform flex items-center gap-1.5"
            >
                BAYAR
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>
    @endif

</div>
