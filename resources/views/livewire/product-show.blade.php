<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white/70 dark:bg-gray-800/60 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
        <div class="flex flex-col md:flex-row justify-between md:items-center mb-6">
            <div>
                <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">{{ $product->name }}</h2>
                <p class="text-md text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
            </div>
            <div class="flex space-x-2 mt-4 md:mt-0">
                <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center justify-center bg-gradient-to-r from-indigo-500 to-fuchsia-500 hover:from-indigo-600 hover:to-fuchsia-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">Edit</a>
                @can('access-dashboard')
                <button title="Hapus produk ini" wire:click="deleteProduct()" wire:confirm="Apakah Anda yakin ingin menghapus produk ini?" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-400">Hapus</button>
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-8">
            <div>
                <h3 class="text-xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600 border-b pb-2">Detail Produk</h3>
                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3">
                    <div class="flex justify-between md:block">
                        <span class="font-medium text-gray-600 dark:text-gray-300 md:block">Harga Jual Dasar</span>
                        <span class="text-gray-900 dark:text-white md:block">Rp {{ number_format($product->baseUnit->selling_price, 0) }}</span>
                    </div>
                    <div class="flex justify-between md:block">
                        <span class="font-medium text-gray-600 dark:text-gray-300 md:block">Kategori</span>
                        <span class="text-gray-900 dark:text-white md:block">{{ $product->category->name }}</span>
                    </div>
                    <div class="flex justify-between md:block">
                        <span class="font-medium text-gray-600 dark:text-gray-300 md:block">Satuan Dasar</span>
                        <span class="text-gray-900 dark:text-white md:block">{{ $product->baseUnit->name }}</span>
                    </div>
                    <div class="flex justify-between md:block">
                        <span class="font-medium text-gray-600 dark:text-gray-300 md:block">Total Stok</span>
                        <span class="font-bold text-lg text-gray-900 dark:text-white md:block">{{ $product->total_stock }}</span>
                    </div>
                </div>
            </div>
            
            <div>
                <h3 class="text-xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600 border-b pb-2">Detail Satuan</h3>
                <div class="mt-4 space-y-4">
                    @forelse($product->productUnits as $unit)
                        <div class="bg-white/80 dark:bg-gray-700/70 p-4 rounded-xl shadow-sm border border-gray-200/70 dark:border-gray-600/60">
                            <p class="font-semibold text-gray-800 dark:text-gray-100">
                                {{ $unit->name }}
                                @if($unit->is_base_unit)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Dasar</span>
                                @endif
                            </p>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                <p class="text-gray-600 dark:text-gray-300">Faktor Konversi: <span class="text-gray-900 dark:text-white">{{ $unit->conversion_factor }}</span></p>
                                <p class="text-gray-600 dark:text-gray-300">Harga Jual: <span class="text-gray-900 dark:text-white">Rp {{ number_format($unit->selling_price, 0) }}</span></p>
                                <p class="text-gray-600 dark:text-gray-300">Harga Beli: <span class="text-gray-900 dark:text-white">Rp {{ number_format($unit->purchase_price, 0) }}</span></p>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">Tidak ada satuan lain yang ditentukan.</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h3 class="text-xl font-semibold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600 border-b pb-2">Sejarah Stok</h3>
                
                <!-- Active Batches -->
                <div class="mt-4 space-y-4">
                    @if($product->activeBatches->count() > 0)
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                                <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                Batch Aktif ({{ $product->activeBatches->count() }})
                            </h4>
                            <div class="space-y-3">
                                @foreach($product->activeBatches as $batch)
                                    <div class="bg-white/80 dark:bg-gray-700/70 p-4 rounded-xl shadow-sm border border-gray-200/70 dark:border-gray-600/60 @if($batch->purchase) cursor-pointer hover:bg-indigo-50/60 dark:hover:bg-zinc-800/70 transition-colors @endif" @if($batch->purchase) tabindex="0" onclick="window.location='{{ route('purchases.show', $batch->purchase->id) }}'" @endif>
                                        <div class="flex justify-between items-center">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2">
                                                    <p class="font-semibold text-gray-800 dark:text-gray-100">{{ $batch->purchase->supplier->name ?? 'Stok Awal' }}</p>
                                                    @if($batch->batch_number && $batch->batch_number !== '-')
                                                        <span class="text-xs bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 px-2 py-0.5 rounded">Batch: {{ $batch->batch_number }}</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batch->purchase->purchase_date ?? $batch->created_at->format('Y-m-d') }}</p>
                                                @if ($batch->expiration_date)
                                                    @php
                                                        $expires = \Carbon\Carbon::parse($batch->expiration_date);
                                                        $isExpired = $expires->isPast();
                                                        $isExpiringSoon = !$isExpired && $expires->isBefore(now()->addDays(90));
                                                        
                                                        $badgeClass = 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300';
                                                        $badgeText = '';
                                                        if ($isExpired) { $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300'; $badgeText = 'Expired'; }
                                                        elseif ($isExpiringSoon) { $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'; $badgeText = 'Expires Soon'; }
                                                    @endphp
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <span class="text-sm text-gray-600 dark:text-gray-300">ED: {{ $expires->format('d/m/Y') }}</span>
                                                        @if($badgeText)
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold {{ $badgeClass }}">{{ $badgeText }}</span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="text-right">
                                                <p class="font-bold text-lg text-gray-800 dark:text-gray-100">{{ $batch->stock }} <span class="text-sm font-normal">{{ $product->baseUnit->name }}</span></p>
                                                <p class="text-sm text-gray-600 dark:text-gray-300">@ Rp {{ number_format($batch->purchase_price, 0) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada batch dengan stok aktif</p>
                        </div>
                    @endif
                </div>

                <!-- Depleted Batches (Collapsible) -->
                @if($product->depletedBatches->count() > 0)
                    <div class="mt-6" x-data="{ showDepleted: false }">
                        <button @click="showDepleted = !showDepleted" class="w-full flex items-center justify-between text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 transition-colors py-2 px-3 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                            <span class="flex items-center">
                                <span class="inline-block w-2 h-2 bg-gray-400 rounded-full mr-2"></span>
                                Batch Habis ({{ $product->depletedBatches->count() }})
                            </span>
                            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': showDepleted }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        
                        <div x-show="showDepleted" x-collapse class="mt-3 space-y-3">
                            @foreach($product->depletedBatches as $batch)
                                <div class="bg-gray-50/50 dark:bg-gray-800/30 p-4 rounded-xl border border-gray-200/50 dark:border-gray-700/50 opacity-60">
                                    <div class="flex justify-between items-center">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <p class="font-medium text-gray-700 dark:text-gray-300">{{ $batch->purchase->supplier->name ?? 'Stok Awal' }}</p>
                                                @if($batch->batch_number && $batch->batch_number !== '-')
                                                    <span class="text-xs bg-gray-200 text-gray-600 dark:bg-gray-700 dark:text-gray-400 px-2 py-0.5 rounded">Batch: {{ $batch->batch_number }}</span>
                                                @endif
                                                <span class="text-xs bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 px-2 py-0.5 rounded font-semibold">Habis</span>
                                            </div>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $batch->purchase->purchase_date ?? $batch->created_at->format('Y-m-d') }}</p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-sm text-gray-500 dark:text-gray-400 line-through">{{ $batch->stock }} {{ $product->baseUnit->name }}</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">@ Rp {{ number_format($batch->purchase_price, 0) }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>