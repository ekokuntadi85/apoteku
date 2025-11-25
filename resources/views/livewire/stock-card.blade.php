<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200" x-data="{ showLegend: false }">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold dark:text-gray-100">Kartu Stok</h2>
        
        <!-- Movement Type Legend Button -->
        <button @click="showLegend = !showLegend" class="text-sm text-blue-600 dark:text-blue-400 hover:underline flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Keterangan Tipe
        </button>
    </div>

    <!-- Movement Type Legend Modal -->
    <div x-show="showLegend" x-cloak class="mb-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-3">Keterangan Tipe Pergerakan Stok:</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">PB</span>
                <span class="text-gray-700 dark:text-gray-300">Pembelian (Masuk)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">PJ</span>
                <span class="text-gray-700 dark:text-gray-300">Penjualan (Keluar)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">OP</span>
                <span class="text-gray-700 dark:text-gray-300">Opname (Penyesuaian)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">ADJ</span>
                <span class="text-gray-700 dark:text-gray-300">Adjustment (Penyesuaian)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">DEL</span>
                <span class="text-gray-700 dark:text-gray-300">Delete (Batch Dihapus)</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">RES</span>
                <span class="text-gray-700 dark:text-gray-300">Restore (Dikembalikan)</span>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
        <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Filter</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="mb-4 relative" x-data="{ searching: false }">
                <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk:</label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="searchProduct" 
                        wire:model.live.debounce.500ms="searchProduct" 
                        @input="searching = true"
                        wire:loading.remove="searching = false"
                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 pr-20" 
                        placeholder="Ketik min. 3 karakter untuk mencari...">
                    
                    <!-- Clear Button -->
                    @if($searchProduct)
                        <button 
                            wire:click="$set('searchProduct', '')" 
                            class="absolute right-10 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                    
                    <!-- Loading Spinner -->
                    <div wire:loading wire:target="searchProduct" class="absolute right-2 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-4 w-4 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                
                @if(!empty($productResults))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400 border-b dark:border-gray-600">
                            Gunakan ↑↓ untuk navigasi, Enter untuk memilih
                        </li>
                        @foreach($productResults as $product)
                            <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-blue-50 dark:text-gray-200 dark:hover:bg-blue-900/30 transition-colors">
                                <div class="font-medium">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }} • Stok: {{ $product->total_stock ?? 0 }}</div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="mb-4">
                <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Bulan:</label>
                <select id="month" wire:model.live="month" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @foreach($months as $key => $value)
                        <option value="{{ $key }}">{{ $value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
                <select id="year" wire:model.live="year" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @foreach($years as $yearOption)
                        <option value="{{ $yearOption }}">{{ $yearOption }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($selectedProductId)
            <div class="mt-6 p-4 border-l-4 border-blue-500 rounded-md bg-gradient-to-r from-blue-50 to-transparent dark:from-blue-900/20 dark:to-transparent">
                <p class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Produk Terpilih: {{ $selectedProductName }}
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                    <p class="text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <span class="text-sm">Saldo Awal (s/d {{ \Carbon\Carbon::parse($startDate)->subDay()->format('d M Y') }}):</span>
                        <span class="font-bold text-lg {{ $this->initialBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $this->initialBalance }}</span>
                    </p>
                    <p class="text-gray-700 dark:text-gray-300 flex items-center gap-2">
                        <span class="text-sm">Saldo Akhir (s/d {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}):</span>
                        <span class="font-bold text-lg {{ $this->finalBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $this->finalBalance }}</span>
                    </p>
                </div>
            </div>
        @else
            <!-- Enhanced Empty State -->
            <div class="mt-6 text-center py-8 px-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-2 text-gray-600 dark:text-gray-400 font-medium">Silakan cari dan pilih produk untuk melihat kartu stok</p>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">💡 Tip: Anda dapat mencari berdasarkan nama produk atau SKU</p>
            </div>
        @endif
    </div>

    <div class="flex justify-between items-center mb-4">
        <h3 class="text-xl font-semibold dark:text-gray-100">Pergerakan Stok</h3>
        
        @if($selectedProductId)
            <a href="{{ route('reports.stock-card.print', [
                'product_id' => $selectedProductId,
                'start_date' => \Carbon\Carbon::parse($startDate)->format('Y-m-d'),
                'end_date' => \Carbon\Carbon::parse($endDate)->format('Y-m-d'),
            ]) }}" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700 transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Kartu Stok
            </a>
        @else
            <button disabled class="bg-gray-300 text-gray-500 font-bold py-2 px-4 rounded cursor-not-allowed opacity-50 flex items-center gap-2" title="Pilih produk terlebih dahulu">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Kartu Stok
            </button>
        @endif
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tipe</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kuantitas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Catatan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Batch</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @php
                        $currentBalance = $balanceBeforeCurrentPage;
                    @endphp
                    @forelse($stockMovements as $movement)
                        @php
                            $currentBalance += $movement->quantity;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                {{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $badgeColors = [
                                        'PB' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'PJ' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'OP' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'ADJ' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                                        'DEL' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                                        'RES' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                                    ];
                                    $colorClass = $badgeColors[$movement->type] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200';
                                @endphp
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $colorClass }}">
                                    {{ $movement->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ $movement->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-200 max-w-xs truncate" title="{{ $movement->remarks }}">
                                {{ $movement->remarks }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-200">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-xs font-mono">
                                    {{ $movement->productBatch->batch_number }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $currentBalance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $currentBalance }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-10">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada pergerakan stok dalam periode ini</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View for History -->
    <div class="block md:hidden space-y-4">
        @php
            $currentBalance = $balanceBeforeCurrentPage;
        @endphp
        @forelse($stockMovements as $movement)
            @php
                $currentBalance += $movement->quantity;
                $badgeColors = [
                    'PB' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                    'PJ' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                    'OP' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                    'ADJ' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
                    'DEL' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
                    'RES' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                ];
                $colorClass = $badgeColors[$movement->type] ?? 'bg-gray-100 text-gray-800';
            @endphp
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border-l-4 {{ $movement->quantity > 0 ? 'border-green-500' : 'border-red-500' }}">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $colorClass }}">
                            {{ $movement->type }}
                        </span>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ \Carbon\Carbon::parse($movement->created_at)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 dark:text-gray-300">Kuantitas</p>
                        <p class="text-xl font-bold {{ $movement->quantity > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                        </p>
                    </div>
                </div>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600 dark:text-gray-400">Batch</span>
                        <span class="font-mono text-xs px-2 py-1 bg-gray-100 dark:bg-gray-600 rounded">{{ $movement->productBatch->batch_number }}</span>
                    </div>
                    @if($movement->remarks)
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                        <span class="font-medium">Catatan:</span> {{ $movement->remarks }}
                    </div>
                    @endif
                    <div class="flex items-center justify-between text-sm pt-2 border-t dark:border-gray-600">
                        <span class="text-gray-600 dark:text-gray-400">Saldo</span>
                        <span class="font-bold text-lg {{ $currentBalance >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">{{ $currentBalance }}</span>
                    </div>
                </div>
            </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2 2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
            </svg>
            <p class="mt-2 text-gray-500 dark:text-gray-400">Tidak ada pergerakan stok dalam periode ini</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $stockMovements->links() }}
    </div>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>