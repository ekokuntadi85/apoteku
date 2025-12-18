<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <!-- Invoice Type Selection Modal -->
    @if($showTypeModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75 flex items-center justify-center z-50" x-data>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full mx-4 p-6">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 dark:text-white text-center">Pilih Jenis Invoice</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Normal Invoice -->
                <button wire:click="selectInvoiceType('normal')" 
                        class="group p-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all text-left">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                            📄
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Penjualan Normal</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Invoice penjualan kredit dengan harga jual normal
                    </p>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <li>✓ Harga jual (selling price)</li>
                        <li>✓ Bisa diberi diskon</li>
                        <li>✓ Untuk transaksi penjualan biasa</li>
                    </ul>
                </button>

                <!-- Loan Invoice -->
                <button wire:click="selectInvoiceType('loan')" 
                        class="group p-6 border-2 border-gray-300 dark:border-gray-600 rounded-lg hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all text-left">
                    <div class="flex items-center mb-4">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                            🤝
                        </div>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Pinjaman Barang</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                        Pinjaman barang ke customer khusus
                    </p>
                    <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                        <li>✓ Harga beli (purchase price)</li>
                        <li>✓ Tanpa diskon</li>
                        <li>✓ Untuk customer khusus</li>
                    </ul>
                </button>
            </div>
        </div>
    </div>
    @endif

    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif
    
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 5000)" x-show="show" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-800 dark:border-red-700 dark:text-red-200" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Buat Invoice Penjualan Kredit</h2>

        <!-- Invoice Details -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Invoice</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan</label>
                    <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="">Pilih Pelanggan</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Jatuh Tempo</label>
                    <input type="date" id="due_date" wire:model="due_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Add Invoice Item -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Tambah Item</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative md:col-span-2">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live.debounce.300ms="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @if(!empty($searchResults))
                        <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-60 overflow-auto dark:bg-gray-800 dark:border-gray-600">
                            @foreach($searchResults as $product)
                                <li wire:click="selectProduct({{ $product->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                    {{ $product->name }} (Stok: {{ $product->total_stock ?? 0 }})
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
                    <label for="selected_unit_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Satuan</label>
                    <select id="selected_unit_id" wire:model.live="selected_unit_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" @if(empty($units)) disabled @endif>
                        @forelse($units as $unit)
                            <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                        @empty
                            <option>Pilih produk dulu</option>
                        @endforelse
                    </select>
                </div>
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                    <input type="number" id="quantity" wire:model="quantity" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Satuan</label>
                    <input type="number" step="0.01" id="price" wire:model="price" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" disabled>
                    @error('price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="text-right mt-6">
                <button type="button" wire:click="addItem()" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600">Tambah Item ke Invoice</button>
            </div>
        </div>

        <!-- Invoice Items List -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Daftar Item</h3>
            <div class="space-y-4">
                @forelse($invoice_items as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($item['subtotal'], 0) }}</p>
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">Belum ada item yang ditambahkan.</p>
                @endforelse
            </div>
            <div class="mt-6 space-y-3">
                <!-- Subtotal -->
                <div class="pt-4 border-t-2 border-gray-200 dark:border-gray-600 flex justify-between items-center">
                    <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">Subtotal</span>
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0) }}</span>
                </div>
                
                <!-- Discount (only for normal invoice) -->
                @if($invoice_type === 'normal')
                <div class="flex justify-between items-center">
                    <label class="font-semibold text-gray-700 dark:text-gray-300">Diskon</label>
                    <div class="flex items-center">
                        <span class="mr-2 text-gray-600 dark:text-gray-400">Rp</span>
                        <input type="number" wire:model.live.debounce.500ms="discount_amount" step="0.01" min="0" max="{{ $total_price }}"
                               class="w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" 
                               placeholder="0">
                    </div>
                </div>
                @error('discount_amount') <span class="text-red-500 text-xs mt-1 block text-right">{{ $message }}</span> @enderror
                @endif
                
                <!-- Grand Total -->
                <div class="pt-3 border-t-2 border-gray-300 dark:border-gray-500 flex justify-between items-center">
                    <span class="text-xl font-bold text-gray-900 dark:text-white">Grand Total</span>
                    <span class="text-xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($grand_total, 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('transactions.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="saveInvoice()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                Simpan Invoice
            </button>
        </div>
    </div>
</div>