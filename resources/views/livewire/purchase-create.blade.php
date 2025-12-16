<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Buat Pembelian Baru</h2>
    </div>

    @if($selectedPoId)
        <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-4 dark:bg-blue-900 dark:text-blue-100 dark:border-blue-500 flex justify-between items-center" role="alert">
            <div>
                <p class="font-bold">Mode Import Surat Pesanan</p>
                <p>Anda sedang memproses Surat Pesanan: <strong>{{ $selectedPoNumber }}</strong></p>
            </div>
            <button wire:click="cancelSelectedPo" class="text-sm underline hover:text-blue-900 dark:hover:text-blue-200">Batalkan / Reset</button>
        </div>
    @endif

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-900 dark:text-green-100 dark:border-green-700" role="alert">
            <strong class="font-bold">Sukses!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <!-- The original h2 "Catat Pembelian Baru" is removed as it's replaced by the new one above -->

        <!-- Purchase Details -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Utama</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="supplier_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                    <select id="supplier_id" wire:model="supplier_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="">Pilih Supplier</option>
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
                    <input type="date" id="due_date" wire:model="due_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 disabled:bg-gray-100 disabled:text-gray-500 dark:disabled:bg-gray-900 dark:disabled:text-gray-500" @if($payment_type === 'tunai') disabled @endif>
                    @if($payment_type === 'tunai')
                        <p class="text-xs text-gray-500 mt-1">Otomatis sama dengan tanggal pembelian (Tunai)</p>
                    @endif
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
                                <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600 flex justify-between">
                                    <span>{{ $product->name }} ({{ $product->sku }})</span>
                                    <span class="text-gray-500 dark:text-gray-400">Stok: {{ (int)$product->product_batches_sum_stock }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                    @if(!empty($selectedProductName))
                        <p class="text-green-600 text-sm mt-2 dark:text-green-400">Terpilih: {{ $selectedProductName }}</p>
                    @endif
                    @error('product_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
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
                    <input type="number" step="0.01" min="0" id="purchase_price" wire:model="purchase_price" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual per Satuan</label>
                    <input type="number" step="0.01" min="0" id="selling_price" wire:model="selling_price" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="stock" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kuantitas (dalam Satuan Terpilih)</label>
                    <input type="number" min="1" id="stock" wire:model="stock" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('stock') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="expiration_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Kadaluarsa (Opsional)</label>
                    <input type="date" id="expiration_date" wire:model="expiration_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('expiration_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="text-right mt-6">
                <button type="button" wire:click="addItem()" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600">Tambah Item ke Daftar</button>
            </div>
        </div>

        <!-- Purchase Items List -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Daftar Item</h3>
            <div class="space-y-4">
                @forelse($purchase_items as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm border border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h4 class="font-bold text-lg text-gray-900 dark:text-white">{{ $item['product_name'] }}</h4>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Satuan: {{ $item['unit_name'] }}</span>
                            </div>
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-medium">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                            <!-- Batch Number -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">No. Batch</label>
                                <input type="text" wire:model="purchase_items.{{ $index }}.batch_number" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Nomor Batch">
                                @error("purchase_items.{$index}.batch_number") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Expiration Date -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Tgl. Kadaluarsa</label>
                                <input type="date" wire:model="purchase_items.{{ $index }}.expiration_date" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error("purchase_items.{$index}.expiration_date") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Quantity -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah ({{ $item['unit_name'] }})</label>
                                <input type="number" wire:model.blur="purchase_items.{{ $index }}.original_stock_input" min="1" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error("purchase_items.{$index}.original_stock_input") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Purchase Price -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Beli</label>
                                <input type="number" wire:model.blur="purchase_items.{{ $index }}.purchase_price" min="0" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error("purchase_items.{$index}.purchase_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <!-- Selling Price -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Harga Jual</label>
                                <input type="number" wire:model.blur="purchase_items.{{ $index }}.selling_price" min="0" class="w-full text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                @error("purchase_items.{$index}.selling_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="mt-3 flex justify-end items-center border-t border-gray-200 dark:border-gray-700 pt-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400 mr-2">Subtotal:</span>
                            <span class="font-bold text-lg text-gray-900 dark:text-white">Rp {{ number_format($item['subtotal'], 0) }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada item yang ditambahkan.</p>
                    </div>
                @endforelse
            </div>
            <div class="mt-6 pt-4 border-t-2 border-gray-200 dark:border-gray-600 flex justify-between items-center bg-gray-100 dark:bg-gray-800 p-4 rounded-lg">
                <span class="text-xl font-bold text-gray-900 dark:text-white">Total Pembelian</span>
                <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">Rp {{ number_format($total_purchase_price, 0) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('purchases.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="savePurchase()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                Simpan Pembelian
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
                $lastPriceInUnit = $lastPrice * $conversionFactor;
            @endphp
            
            <h3 class="text-xl font-bold {{ $priceChangeType === 'increase' ? 'text-yellow-600 dark:text-yellow-400' : 'text-blue-600 dark:text-blue-400' }}">
                {{ $priceChangeType === 'increase' ? 'Harga Beli Naik' : 'Harga Beli Turun' }}
            </h3>
            
            <div class="mt-4 text-gray-700 dark:text-gray-300">
                <p>Harga beli untuk produk <strong>{{ $itemToAddCache['product_name'] }}</strong>:</p>
                <div class="mt-2 bg-gray-100 dark:bg-gray-700 p-3 rounded">
                    <p class="text-sm">Harga beli terakhir: <strong>Rp {{ number_format($lastPriceInUnit, 0) }}</strong> per {{ $itemToAddCache['unit_name'] }}</p>
                    <p class="text-sm mt-1">Harga beli baru: <strong class="{{ $priceChangeType === 'increase' ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($itemToAddCache['purchase_price'], 0) }}</strong> per {{ $itemToAddCache['unit_name'] }}</p>
                </div>
                
                @if($priceChangeType === 'increase')
                    <p class="mt-3 text-sm">Harga beli naik. Anda mungkin perlu menaikkan harga jual untuk mempertahankan margin keuntungan.</p>
                @else
                    <p class="mt-3 text-sm">Harga beli turun. Anda bisa menurunkan harga jual untuk lebih kompetitif, atau mempertahankan harga jual untuk margin lebih tinggi.</p>
                @endif
            </div>

            <div class="mt-6">
                <label for="newSellingPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual per {{ $itemToAddCache['unit_name'] }}</label>
                <input type="number" id="newSellingPrice" wire:model="newSellingPrice" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-900 dark:text-gray-200 dark:border-gray-600">
                @error('newSellingPrice') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimal: Rp {{ number_format($itemToAddCache['purchase_price'], 0) }}</p>
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

    <!-- Payment Type Selection Modal -->
    @if($showPaymentTypeModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 backdrop-blur-sm">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8 w-full max-w-lg text-center transform transition-all scale-100">
            <h2 class="text-2xl font-bold mb-2 text-gray-800 dark:text-white">Pilih Jenis Pembelian</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-8">Silakan pilih metode pembayaran untuk transaksi ini.</p>
            
            <div class="grid grid-cols-2 gap-6">
                <button wire:click="selectPaymentType('tunai')" class="flex flex-col items-center justify-center p-6 border-2 border-green-500 rounded-xl hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors group">
                    <div class="bg-green-100 dark:bg-green-900/50 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400">Tunai</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 mt-2">Jatuh tempo = hari ini. <br>Status otomatis Lunas.</span>
                </button>

                <button wire:click="selectPaymentType('tempo')" class="flex flex-col items-center justify-center p-6 border-2 border-blue-500 rounded-xl hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors group">
                    <div class="bg-blue-100 dark:bg-blue-900/50 p-4 rounded-full mb-4 group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold text-gray-800 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400">Tempo</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 mt-2">Atur tanggal jatuh tempo.<br>Status belum lunas.</span>
                </button>
            </div>

            <div class="mt-8 text-xs text-gray-400">
                <a href="{{ route('purchases.index') }}" class="underline hover:text-gray-600 dark:hover:text-gray-300">Batal / Kembali</a>
            </div>
        </div>
    </div>
    @endif



    @script
    <script>
        // Validate date format before adding item
        document.addEventListener('DOMContentLoaded', function() {
            const expirationDateInput = document.getElementById('expiration_date');
            
            if (expirationDateInput) {
                expirationDateInput.addEventListener('blur', function() {
                    const value = this.value.trim();
                    
                    // Skip validation if empty (nullable)
                    if (value === '') return;
                    
                    // Validate format YYYY-MM-DD
                    const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
                    if (!dateRegex.test(value)) {
                        alert('Format tanggal tidak valid. Gunakan format YYYY-MM-DD (contoh: 2025-12-31)');
                        this.value = '';
                        return;
                    }
                    
                    // Validate year range
                    const year = parseInt(value.split('-')[0]);
                    if (year < 1900 || year > 2100) {
                        alert('Tahun tidak valid. Tahun harus antara 1900-2100');
                        this.value = '';
                        return;
                    }
                    
                    // Validate if it's a real date
                    const date = new Date(value);
                    if (isNaN(date.getTime())) {
                        alert('Tanggal tidak valid');
                        this.value = '';
                        return;
                    }
                });
            }
        });

        Livewire.on('confirm-lower-price', (message) => {
            if (confirm(message)) {
                Livewire.dispatch('confirmedAddItem');
            }
        });

        Livewire.on('selling-price-warning', (message) => {
            alert(message);
        });
    </script>
    @endscript
</div>
</div>