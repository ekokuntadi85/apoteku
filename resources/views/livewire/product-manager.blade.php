<div class="min-h-screen bg-gray-50 dark:bg-gray-900 font-sans pb-12">
    <!-- Notifications -->
    <div class="fixed top-4 left-1/2 transform -translate-x-1/2 z-50 w-full max-w-md px-4 pointer-events-none">
        @if (session()->has('message'))
            <div class="bg-emerald-500 text-white px-6 py-4 rounded-lg shadow-xl flex items-center justify-between pointer-events-auto mb-2" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)">
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

    <!-- Header & Stats -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="container mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                        <svg class="w-8 h-8 lg:w-10 lg:h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        Master Data Obat
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm lg:text-base mt-1">Kelola stok dan harga produk apotek secara efisien.</p>
                </div>
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-black rounded-xl transition-all shadow-lg active:scale-95 gap-2 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Produk
                </a>
            </div>

            <!-- Stats Dashboard -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 lg:gap-6">
                <div class="bg-indigo-50 dark:bg-indigo-900/20 p-5 rounded-2xl border border-indigo-100 dark:border-indigo-800 flex items-center gap-4">
                    <div class="p-3 bg-white dark:bg-indigo-900/40 rounded-xl shadow-md">
                        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-indigo-600/70 dark:text-indigo-400/70 uppercase tracking-widest">Total Produk</p>
                        <p class="text-2xl lg:text-3xl font-black text-indigo-900 dark:text-indigo-100">{{ $stats['total'] }}</p>
                    </div>
                </div>
                <div class="bg-amber-50 dark:bg-amber-900/20 p-5 rounded-2xl border border-amber-100 dark:border-amber-800 flex items-center gap-4">
                    <div class="p-3 bg-white dark:bg-amber-900/40 rounded-xl shadow-md">
                        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-amber-600/70 dark:text-amber-400/70 uppercase tracking-widest">Stok Menipis</p>
                        <p class="text-2xl lg:text-3xl font-black text-amber-900 dark:text-amber-100">{{ $stats['low_stock'] }}</p>
                    </div>
                </div>
                <div class="bg-rose-50 dark:bg-rose-900/20 p-5 rounded-2xl border border-rose-100 dark:border-rose-800 flex items-center gap-4">
                    <div class="p-3 bg-white dark:bg-rose-900/40 rounded-xl shadow-md">
                        <svg class="w-8 h-8 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </div>
                    <div>
                        <p class="text-[10px] font-black text-rose-600/70 dark:text-rose-400/70 uppercase tracking-widest">Stok Habis</p>
                        <p class="text-2xl lg:text-3xl font-black text-rose-900 dark:text-indigo-100">{{ $stats['out_of_stock'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & List -->
    <div class="container mx-auto px-4 mt-8">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <!-- Filter Bar -->
            <div class="p-5 lg:p-6 bg-gray-50/50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700">
                <div class="flex flex-col lg:flex-row gap-4 lg:gap-6 items-end">
                    <div class="flex-1 w-full">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1 tracking-widest">Cari Produk</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Nama atau SKU..." class="w-full pl-11 pr-4 py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 text-sm font-medium shadow-sm">
                        </div>
                    </div>
                    <div class="w-full lg:w-56">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1 tracking-widest">Kategori</label>
                        <select wire:model.live="category_id" class="w-full py-2.5 rounded-xl border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-emerald-500 focus:border-emerald-500 text-sm font-bold shadow-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($this->categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full lg:w-64">
                        <label class="block text-[10px] font-black text-gray-500 uppercase mb-2 ml-1 tracking-widest">Status Stok</label>
                        <div class="flex p-1 bg-gray-200 dark:bg-gray-900 rounded-xl gap-1 border border-gray-200 dark:border-gray-700">
                            <button wire:click="$set('stock_filter', '')" class="flex-1 py-1.5 text-[11px] font-black rounded-lg {{ $stock_filter === '' ? 'bg-white dark:bg-gray-700 shadow text-emerald-600 dark:text-emerald-400' : 'text-gray-500 hover:text-gray-700' }}">Semua</button>
                            <button wire:click="$set('stock_filter', 'low')" class="flex-1 py-1.5 text-[11px] font-black rounded-lg {{ $stock_filter === 'low' ? 'bg-white dark:bg-gray-700 shadow text-amber-600 dark:text-amber-400' : 'text-gray-500 hover:text-gray-700' }}">Menipis</button>
                            <button wire:click="$set('stock_filter', 'out')" class="flex-1 py-1.5 text-[11px] font-black rounded-lg {{ $stock_filter === 'out' ? 'bg-white dark:bg-gray-700 shadow text-rose-600 dark:text-rose-400' : 'text-gray-500 hover:text-gray-700' }}">Habis</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table View (Desktop) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-base text-left">
                    <thead class="text-[10px] text-gray-500 uppercase bg-gray-50/50 dark:bg-gray-800/80 dark:text-gray-400 border-b dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4 font-black tracking-widest">Produk & SKU</th>
                            <th class="px-6 py-4 font-black tracking-widest text-center">Stok</th>
                            <th class="px-6 py-4 font-black tracking-widest">Pricing (Multi-Satuan)</th>
                            <th class="px-6 py-4 font-black tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-gray-700/30 transition-colors group cursor-pointer" wire:click="showDetail({{ $product->id }})">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="font-black text-gray-900 dark:text-white text-base group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors uppercase tracking-tight">{{ $product->name }}</span>
                                        <span class="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 tracking-widest font-black">{{ $product->sku ?: '-' }} | {{ $product->category->name ?? 'TANPA KATEGORI' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $stock = $product->total_stock_sum ?? 0;
                                        $colorClass = $stock <= 0 ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400 py-1.5 px-4' : ($stock < 10 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 py-1.5 px-4' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 py-1.5 px-4');
                                    @endphp
                                    <span class="rounded-full text-xs font-black inline-block min-w-[80px] shadow-sm {{ $colorClass }}">
                                        {{ $stock }} <span class="text-[10px] font-bold uppercase">{{ $product->baseUnit->name ?? '' }}</span>
                                    </span>
                                </td>
                                <td class="px-6 py-2">
                                    <div class="flex flex-col gap-1.5 py-1">
                                        @foreach($product->productUnits as $unit)
                                            <div class="flex items-center gap-3 text-sm bg-gray-100/50 dark:bg-gray-900/40 p-1.5 px-3 rounded-lg border border-gray-100 dark:border-gray-700 max-w-sm shadow-sm">
                                                <span class="font-black text-emerald-600 dark:text-emerald-400 w-16 uppercase text-[10px] tracking-widest">{{ $unit->name }}</span>
                                                <div class="flex items-center gap-4 border-l-2 border-gray-200 dark:border-gray-700 pl-3">
                                                    <span class="text-gray-400 text-[10px] font-black uppercase tracking-tighter">B: <span class="text-gray-700 dark:text-gray-300 font-bold italic text-sm">{{ number_format($unit->purchase_price, 0, ',', '.') }}</span></span>
                                                    <span class="text-emerald-600/50 text-[10px] font-black uppercase tracking-tighter">S: <span class="text-emerald-700 dark:text-emerald-400 font-black text-base">{{ number_format($unit->selling_price, 0, ',', '.') }}</span></span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-right" wire:click.stop>
                                    <div class="flex justify-end gap-2">
                                        <button wire:click="showDetail({{ $product->id }})" class="p-2.5 bg-gray-100 text-gray-600 hover:bg-gray-200 rounded-xl dark:bg-gray-700 dark:text-gray-300 transition-all hover:scale-105 active:scale-95" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </button>
                                        <a href="{{ route('products.edit', $product->id) }}" class="p-2.5 bg-indigo-50 text-indigo-600 hover:bg-indigo-100 rounded-xl dark:bg-indigo-900/30 dark:text-indigo-400 transition-all shadow-sm hover:scale-105 active:scale-95" title="Edit">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </a>
                                        <button wire:click="delete({{ $product->id }})" 
                                                wire:confirm="Yakin ingin menghapus produk ini?"
                                                class="p-2.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-xl dark:bg-rose-900/30 dark:text-rose-400 transition-all shadow-sm hover:scale-105 active:scale-95" title="Hapus">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-10 py-32 text-center text-gray-500 dark:text-gray-400">
                                    <div class="flex flex-col items-center gap-6">
                                        <svg class="w-24 h-24 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                        <p class="font-black text-3xl tracking-tight uppercase">Produk Tidak Ditemukan</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Card View (Mobile) -->
            <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($products as $product)
                    <div class="p-5 bg-white dark:bg-gray-800 space-y-4 shadow-sm" wire:click="showDetail({{ $product->id }})">
                         <div class="flex justify-between items-start border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div class="flex flex-col">
                                <span class="font-black text-gray-900 dark:text-white text-lg leading-tight uppercase tracking-tight">{{ $product->name }}</span>
                                <span class="text-[10px] text-gray-400 mt-0.5 uppercase tracking-widest font-black">{{ $product->sku ?: 'NO SKU' }}</span>
                            </div>
                            @php
                                $stock = $product->total_stock_sum ?? 0;
                                $colorClass = $stock <= 0 ? 'bg-rose-500 text-white' : ($stock < 10 ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white');
                            @endphp
                            <span class="px-3 py-1.5 rounded-lg text-xs font-black shadow-md {{ $colorClass }}">
                                {{ $stock }} <span class="text-[9px]">{{ $product->baseUnit->name ?? '' }}</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 gap-3">
                            @foreach($product->productUnits as $unit)
                                <div class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-100 dark:border-gray-700 flex justify-between items-center shadow-sm">
                                    <span class="text-xs font-black text-gray-500 uppercase tracking-widest">{{ $unit->name }}</span>
                                    <div class="flex gap-4">
                                        <div class="flex flex-col items-end">
                                            <span class="text-[9px] text-gray-400 font-black uppercase tracking-tighter">Beli</span>
                                            <span class="text-sm font-bold italic">{{ number_format($unit->purchase_price, 0, ',', '.') }}</span>
                                        </div>
                                        <div class="flex flex-col items-end border-l border-gray-200 dark:border-gray-700 pl-3">
                                            <span class="text-[9px] text-emerald-500 font-black uppercase tracking-tighter">Jual</span>
                                            <span class="text-base font-black text-emerald-600 dark:text-emerald-400">{{ number_format($unit->selling_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="flex gap-3 pt-2" wire:click.stop>
                             <button wire:click="showDetail({{ $product->id }})" class="flex-1 py-3 px-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-center text-sm font-black rounded-xl shadow-sm">Detail</button>
                             <a href="{{ route('products.edit', $product->id) }}" class="flex-1 py-3 px-3 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 text-center text-sm font-black rounded-xl shadow-sm border border-indigo-100 dark:border-indigo-800">Edit</a>
                             <button wire:click="delete({{ $product->id }})" wire:confirm="Satu produk akan dihapus, yakin?" class="p-3 px-4 bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400 rounded-xl shadow-sm border border-rose-100 dark:border-rose-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                             </button>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="p-8 bg-gray-50/50 dark:bg-gray-800/50 border-t border-gray-200 dark:border-gray-700">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Product Detail Modal -->
    <div x-data="{ open: @entangle('showModal') }" 
         x-show="open" 
         class="fixed inset-0 z-[60] overflow-y-auto"
         style="display: none;">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-md transition-opacity" 
             @click="open = false"
             x-show="open"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"></div>

        <!-- Modal Centerer -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6 lg:p-8">
            <div class="relative w-full max-w-5xl transform transition-all text-left"
                 x-show="open"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-8 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-8 sm:scale-95">
                
                @if($viewingProduct)
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] overflow-hidden shadow-2xl border border-gray-200 dark:border-gray-700">
                        <div class="p-6 lg:p-10">
                            <div class="flex justify-between items-start mb-8">
                                <div>
                                    <h2 class="text-lg lg:text-xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight uppercase">{{ $viewingProduct->name }}</h2>
                                    <p class="text-gray-500 font-bold tracking-widest text-[11px] lg:text-xs mt-1 uppercase">{{ $viewingProduct->sku ?: 'TANPA SKU' }} | {{ $viewingProduct->category->name ?? 'TANPA KATEGORI' }}</p>
                                </div>
                                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-all bg-gray-100 dark:bg-gray-700 p-3 rounded-2xl hover:rotate-90">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
                                <!-- Left Column: Details -->
                                <div class="space-y-12">
                                    <div>
                                        <h3 class="text-sm font-black text-indigo-600 dark:text-indigo-400 uppercase border-b-2 border-indigo-100 dark:border-indigo-900/50 pb-2 mb-6 tracking-widest">Detail Produk</h3>
                                        <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-900/30 p-6 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-inner">
                                            <div>
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Stok</p>
                                                <p class="text-xl lg:text-2xl font-black text-emerald-600">{{ $viewingProduct->total_stock }} <span class="text-[10px] font-bold text-gray-500 uppercase">{{ $viewingProduct->baseUnit->name ?? 'Unit' }}</span></p>
                                            </div>
                                            <div>
                                                <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1">Satuan Dasar</p>
                                                <p class="text-xl lg:text-2xl font-black text-gray-900 dark:text-white uppercase">{{ $viewingProduct->baseUnit->name ?? 'Unit' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <h3 class="text-sm font-black text-emerald-600 dark:text-emerald-400 uppercase border-b-2 border-emerald-100 dark:border-emerald-900/50 pb-2 mb-6 tracking-widest">Multi-Satuan & Harga</h3>
                                        <div class="space-y-3">
                                            @foreach($viewingProduct->productUnits as $unit)
                                                <div class="bg-white dark:bg-gray-700/50 p-5 rounded-2xl shadow border border-gray-100 dark:border-gray-700 flex justify-between items-center group transition-all hover:border-emerald-500">
                                                    <div class="flex items-center gap-4">
                                                        <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-900/40 rounded-lg flex items-center justify-center font-black text-indigo-600 dark:text-indigo-400 text-[10px] tracking-widest shadow-sm">
                                                            {{ substr($unit->name, 0, 3) }}
                                                        </div>
                                                        <span class="font-black text-gray-900 dark:text-white uppercase text-base">{{ $unit->name }}</span>
                                                    </div>
                                                    <div class="flex gap-8">
                                                        <div class="text-right">
                                                            <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-0.5">Harga Beli</p>
                                                            <p class="text-sm font-bold text-gray-600 dark:text-gray-300 italic">{{ number_format($unit->purchase_price, 0, ',', '.') }}</p>
                                                        </div>
                                                        <div class="text-right border-l-2 border-gray-200 dark:border-gray-700 pl-6">
                                                            <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-0.5">Harga Jual</p>
                                                            <p class="text-lg font-black text-emerald-600 dark:text-emerald-400">{{ number_format($unit->selling_price, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column: Batches -->
                                <div class="space-y-12">
                                    <h3 class="text-sm font-black text-amber-600 dark:text-amber-400 uppercase border-b-2 border-amber-100 dark:border-amber-900/50 pb-2 mb-6 tracking-widest">Riwayat Batch (Stok Aktif)</h3>
                                    <div class="max-h-[600px] overflow-y-auto space-y-4 pr-4 custom-scrollbar">
                                        @php
                                            $activeBatches = $viewingProduct->productBatches->where('stock', '>', 0)->sortByDesc('created_at');
                                            $depletedBatches = $viewingProduct->productBatches->where('stock', '<=', 0)->sortByDesc('created_at');
                                        @endphp

                                        @forelse($activeBatches as $batch)
                                            <a href="{{ $batch->purchase_id ? route('purchases.show', $batch->purchase_id) : '#' }}" 
                                               class="block bg-white dark:bg-gray-900/30 p-6 rounded-2xl border-l-[8px] border-emerald-500 shadow-lg border border-gray-100 dark:border-gray-700 transition-all hover:scale-[1.01] hover:border-emerald-600 group">
                                                <div class="flex justify-between items-start mb-4">
                                                    <div>
                                                        <p class="font-black text-gray-900 dark:text-white uppercase text-base tracking-tight leading-tight group-hover:text-emerald-600 transition-colors">{{ $batch->purchase->supplier->name ?? 'STOK AWAL' }}</p>
                                                        <p class="text-[10px] font-black text-gray-400 tracking-widest mt-1.5 uppercase">{{ $batch->purchase->purchase_date ?? ($batch->created_at ? $batch->created_at->format('Y-m-d') : '-') }}</p>
                                                    </div>
                                                    <div class="text-right">
                                                        <p class="text-2xl font-black text-emerald-600">{{ $batch->stock }} <span class="text-[10px] font-bold uppercase text-gray-400">{{ $viewingProduct->baseUnit->name ?? 'Unit' }}</span></p>
                                                        <p class="text-[10px] font-black text-gray-400 uppercase mt-0.5 tracking-widest">@ {{ number_format($batch->purchase_price, 0, ',', '.') }}</p>
                                                    </div>
                                                </div>
                                                @if($batch->batch_number || $batch->expiration_date)
                                                    <div class="flex gap-8 border-t border-gray-100 dark:border-gray-700/50 pt-4 mt-4">
                                                        @if($batch->batch_number && $batch->batch_number !== '-')
                                                            <div class="flex-1">
                                                                <p class="text-[9px] font-black text-gray-400 uppercase mb-1 tracking-widest">No. Batch</p>
                                                                <p class="text-xs font-black text-gray-700 dark:text-gray-300 tracking-widest uppercase">{{ $batch->batch_number }}</p>
                                                            </div>
                                                        @endif
                                                        @if($batch->expiration_date)
                                                            <div class="flex-1">
                                                                <p class="text-[9px] font-black text-rose-500 uppercase mb-1 tracking-widest">Exp. Date</p>
                                                                <p class="text-xs font-black text-rose-600 dark:text-rose-400 italic tracking-widest">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('d/m/Y') }}</p>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </a>
                                        @empty
                                            <div class="text-center py-24 bg-gray-50 dark:bg-gray-900/30 rounded-[2.5rem] border-4 border-dashed border-gray-200 dark:border-gray-700">
                                                <svg class="w-24 h-24 text-gray-300 mx-auto mb-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                                <p class="font-black text-gray-400 uppercase tracking-widest text-xl">Tidak ada batch aktif</p>
                                            </div>
                                        @endforelse

                                        @if($depletedBatches->count() > 0)
                                            <div x-data="{ showDepleted: false }" class="mt-16">
                                                <button @click="showDepleted = !showDepleted" class="w-full flex items-center justify-between py-6 px-8 bg-gray-100 dark:bg-gray-700/50 rounded-3xl text-gray-500 font-black uppercase text-sm tracking-widest hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                                                    <span>BATCH HABIS ({{ $depletedBatches->count() }})</span>
                                                    <svg class="w-8 h-8 transition-transform duration-300" :class="showDepleted ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                                </button>
                                                <div x-show="showDepleted" x-collapse class="mt-6 space-y-3 opacity-70">
                                                    @foreach($depletedBatches as $batch)
                                                        <a href="{{ $batch->purchase_id ? route('purchases.show', $batch->purchase_id) : '#' }}" 
                                                           class="block p-5 bg-gray-200/30 dark:bg-gray-800/20 rounded-2xl border border-gray-200 dark:border-gray-700 flex justify-between items-center transition-all hover:bg-gray-200/50 group">
                                                            <div>
                                                                <p class="font-black text-gray-700 dark:text-gray-400 text-base uppercase tracking-tight group-hover:text-emerald-600 transition-colors">{{ $batch->purchase->supplier->name ?? 'STOK AWAL' }}</p>
                                                                <p class="text-[10px] font-black text-gray-400 tracking-widest mt-1.5 uppercase">{{ $batch->purchase->purchase_date ?? ($batch->created_at ? $batch->created_at->format('Y-m-d') : '-') }}</p>
                                                            </div>
                                                            <span class="bg-rose-100 text-rose-600 px-4 py-1.5 rounded-lg font-black text-xs uppercase tracking-widest italic shadow-sm">HABIS</span>
                                                        </a>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                             <div class="mt-12 pt-8 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row justify-end gap-3">
                                <button @click="open = false" class="px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-black rounded-xl text-sm uppercase tracking-widest shadow-sm hover:bg-gray-200 transition-all active:scale-95">Tutup</button>
                                <a href="{{ route('products.edit', $viewingProduct->id) }}" class="px-6 py-3 bg-indigo-600 text-white font-black rounded-xl text-sm uppercase tracking-widest shadow-lg shadow-indigo-200 dark:shadow-none hover:bg-indigo-700 transition-all active:scale-95 text-center">Edit Produk</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
