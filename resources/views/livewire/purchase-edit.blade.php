<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Edit Pembelian #{{ $invoice_number }}</h2>

        <!-- Purchase Details -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Utama</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <select id="supplier_id" wire:model="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" disabled>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                    @error('supplier_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="invoice_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Invoice</label>
                    <input type="text" id="invoice_number" wire:model="invoice_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('invoice_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="purchase_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembelian</label>
                    <input type="date" id="purchase_date" wire:model.live="purchase_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('purchase_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jatuh Tempo (Opsional)</label>
                    <input type="date" id="due_date" wire:model="due_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Add Purchase Item -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Tambah Item</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="relative">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @if(!empty($searchResults))
                        <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                            @foreach($searchResults as $product)
                                <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                    {{ $product->name }} ({{ $product->sku }})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($selectedProductName))
                        <p class="text-green-600 text-sm mt-2 dark:text-green-400">Terpilih: {{ $selectedProductName }}</p>
                    @endif
                    @error('product_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div wire:key="unit-select-{{ $product_id }}">
                    <label for="selectedProductUnitId" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan Pembelian</label>
                    <select id="selectedProductUnitId" wire:model.live="selectedProductUnitId" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="">Pilih Satuan</option>
                        @foreach($selectedProductUnits as $unit)
                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                        @endforeach
                    </select>
                    @error('selectedProductUnitId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="batch_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Batch</label>
                    <input type="text" id="batch_number" wire:model="batch_number" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('batch_number') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="purchase_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Beli per Satuan</label>
                    <input type="number" step="0.01" id="purchase_price" wire:model.live="purchase_price" 
                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 {{ (float)$purchase_price > 0 && (float)$lastKnownPurchasePrice > 0 ? (((float)$purchase_price / ((float)($selectedUnitConversionFactor ?: 1))) > (float)$lastKnownPurchasePrice ? 'text-red-600 font-bold border-red-300' : (((float)$purchase_price / ((float)($selectedUnitConversionFactor ?: 1))) < (float)$lastKnownPurchasePrice ? 'text-green-600 font-bold border-green-300' : '')) : '' }}">
                    
                    @if((float)$purchase_price > 0 && (float)$lastKnownPurchasePrice > 0)
                        @php
                            $currentBasePrice = (float)$purchase_price / ((float)($selectedUnitConversionFactor ?: 1));
                        @endphp
                        @if($currentBasePrice > (float)$lastKnownPurchasePrice)
                            <p class="text-xs text-red-600 mt-1 italic">harga beli lebih mahal</p>
                        @elseif($currentBasePrice < (float)$lastKnownPurchasePrice)
                            <p class="text-xs text-green-600 mt-1 italic">harga beli lebih murah</p>
                        @endif
                    @endif
                    
                    @error('purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual per Satuan</label>
                    <input type="number" step="0.01" min="0" id="selling_price" wire:model="selling_price" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuantitas (dalam Satuan Terpilih)</label>
                    <input type="number" id="stock" wire:model="stock" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('stock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Kadaluarsa (Opsional)</label>
                    <input type="date" id="expiration_date" wire:model="expiration_date" wire:keydown.enter="addItem" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('expiration_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="text-right mt-6">
                <button type="button" wire:click="addItem()" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600">Tambah Item ke Daftar</button>
            </div>
        </div>

        <!-- Purchase Items List -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg overflow-hidden mt-6">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-600 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Daftar Item Pembelian</h3>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 text-xs font-bold rounded-full">
                    {{ count($purchase_items) }} Item
                </span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Produk / Satuan</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Batch / Exp</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jumlah</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Harga Beli</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subtotal</th>
                            <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                        @forelse($purchase_items as $index => $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-600/50 transition-colors">
                                <td class="px-4 py-3">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $item['product_name'] }}</div>
                                    <div class="text-[11px] text-gray-500 dark:text-gray-400">Unit: {{ $item['unit_name'] }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="space-y-1">
                                        <div class="text-[11px] font-medium text-gray-700 dark:text-gray-300">B: {{ $item['batch_number'] ?: '-' }}</div>
                                        <div class="text-[11px] text-gray-500 dark:text-gray-400">E: {{ $item['expiration_date'] ? \Carbon\Carbon::parse($item['expiration_date'])->format('d/m/Y') : '-' }}</div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $item['original_stock_input'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @php
                                        $currentBasePriceInList = (float)($item['purchase_price'] ?? 0) / ((float)($item['conversion_factor'] ?: 1));
                                        $lastBasePriceInList = (float)($item['last_purchase_price_base'] ?? 0);
                                        $priceState = ($currentBasePriceInList > 0 && $lastBasePriceInList > 0) ? ($currentBasePriceInList > $lastBasePriceInList ? 'expensive' : ($currentBasePriceInList < $lastBasePriceInList ? 'cheaper' : 'normal')) : 'normal';
                                    @endphp
                                    <div class="relative inline-block w-full">
                                        <div class="text-sm font-bold {{ $priceState === 'expensive' ? 'text-red-600' : ($priceState === 'cheaper' ? 'text-green-600' : 'text-gray-900 dark:text-white') }}">
                                            Rp {{ number_format($item['purchase_price'], 0) }}
                                        </div>
                                        @if($priceState !== 'normal')
                                            <div class="text-[9px] mt-0.5 {{ $priceState === 'expensive' ? 'text-red-500' : 'text-green-500' }} font-medium">Beli: {{ $priceState === 'expensive' ? '↑ Mahal' : '↓ Murah' }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="text-sm font-bold text-gray-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0) }}</div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 rounded-full transition-colors">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                    Belum ada item yang ditambahkan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-blue-50 dark:bg-blue-900/20">
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-right text-lg font-bold text-gray-700 dark:text-gray-300">Total Pembelian</td>
                            <td class="px-4 py-4 text-right text-xl font-black text-blue-600 dark:text-blue-400">Rp {{ number_format($total_purchase_price, 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('purchases.show', $purchaseId) }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="savePurchase()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                Update Pembelian
            </button>
        </div>
    </div>

    <!-- Price Warning Modal -->
    @if($showPriceWarningModal && $itemToAddCache)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            @php
                $priceChangeType = $itemToAddCache['price_change_type'] ?? 'increase';
                $lastPrice = $itemToAddCache['last_purchase_price'] ?? 0;
                $conversionFactor = $itemToAddCache['conversion_factor'] ?? 1;
                $lastPriceInUnit = (float)$lastPrice * (float)$conversionFactor;
                $isLowSellingPriceOnly = $priceChangeType === 'none' && ($itemToAddCache['is_low_selling_price'] ?? false);
            @endphp

            @if($isLowSellingPriceOnly)
                <h3 class="text-xl font-bold text-red-600 dark:text-red-400">
                    Peringatan Harga Jual
                </h3>
            @else
                <h3 class="text-xl font-bold {{ $priceChangeType === 'increase' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400' }}">
                    {{ $priceChangeType === 'increase' ? 'Harga Beli Naik' : 'Harga Beli Turun' }}
                </h3>
            @endif
            
            <div class="mt-4 text-gray-700 dark:text-gray-300">
                <p>Harga beli untuk produk <strong>{{ $itemToAddCache['product_name'] }}</strong>:</p>
                <div class="mt-2 bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    @if($priceChangeType !== 'none')
                        <p class="text-sm">Harga beli terakhir: <strong>Rp {{ number_format($lastPriceInUnit, 0) }}</strong> per {{ $itemToAddCache['unit_name'] }}</p>
                    @endif
                    <p class="text-sm mt-1">Harga beli saat ini: <strong class="{{ $priceChangeType === 'increase' ? 'text-red-600' : ($priceChangeType === 'decrease' ? 'text-green-600' : 'text-gray-900 dark:text-white') }}">Rp {{ number_format($itemToAddCache['purchase_price'], 0) }}</strong> per {{ $itemToAddCache['unit_name'] }}</p>
                </div>
                
                @if($isLowSellingPriceOnly)
                    <p class="mt-3 text-sm text-red-600 font-medium italic">Harga jual atau harga member yang diinput lebih rendah dari harga beli. Silakan periksa kembali agar tidak rugi.</p>
                @elseif($priceChangeType === 'increase')
                    <p class="mt-3 text-sm italic">Harga beli naik. Anda mungkin perlu menaikkan harga jual untuk mempertahankan margin keuntungan.</p>
                @elseif($priceChangeType === 'decrease')
                    <p class="mt-3 text-sm italic">Harga beli turun. Anda bisa menurunkan harga jual untuk lebih kompetitif, atau mempertahankan harga jual untuk margin lebih tinggi.</p>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 mt-6">
                <div>
                    <label for="newSellingPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Update Harga Jual</label>
                    <input type="number" id="newSellingPrice" wire:model.live="newSellingPrice" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:text-gray-200 dark:border-gray-600">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Harga Saat Ini: Rp {{ number_format($itemToAddCache['current_selling_price'] ?? 0, 0) }}</p>
                    <p class="text-[10px] text-red-500 mt-0.5">Minimal: Rp {{ number_format($itemToAddCache['purchase_price'], 0) }}</p>
                </div>
                <div>
                    <label for="newMemberPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Update Harga Member</label>
                    <input type="number" id="newMemberPrice" wire:model.live="newMemberPrice" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:text-gray-200 dark:border-gray-600">
                    <p class="text-[10px] text-gray-500 dark:text-gray-400 mt-1">Harga Saat Ini: Rp {{ number_format($itemToAddCache['current_member_price'] ?? 0, 0) }}</p>
                    <p class="text-[10px] text-red-500 mt-0.5">Minimal: Rp {{ number_format($itemToAddCache['purchase_price'], 0) }}</p>
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="button" wire:click="closePriceWarningModal" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                    Batal
                </button>
                <button type="button" wire:click="updatePriceAndAddItem" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600">
                    Update Harga & Lanjutkan
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('confirm-lower-price', (message) => {
            if (confirm(message)) {
                Livewire.dispatch('confirmedAddItem');
            }
        });

        Livewire.on('focus-search', () => {
            const searchInput = document.getElementById('searchProduct');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        });
    });
</script>
@endpush
