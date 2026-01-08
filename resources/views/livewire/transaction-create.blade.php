<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Buat Transaksi Baru</h2>

        <!-- Transaction Details -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Transaksi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan (Opsional)</label>
                    <div class="flex items-center gap-2">
                        <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                            <option value="">Pilih Pelanggan</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @if($selected_customer && $selected_customer->is_member)
                            <span class="bg-gradient-to-r from-amber-400 to-amber-500 text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm whitespace-nowrap">
                                ⭐ MEMBER
                            </span>
                        @endif
                    </div>
                    @error('customer_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="payment_status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status Pembayaran</label>
                    <select id="payment_status" wire:model="payment_status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="paid">Lunas</option>
                        <option value="unpaid">Belum Lunas</option>
                    </select>
                    @error('payment_status') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tipe Transaksi</label>
                    <select id="type" wire:model="type" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="pos">POS</option>
                        <option value="invoice">Invoice</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jatuh Tempo (Opsional)</label>
                    <input type="date" id="due_date" wire:model="due_date" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('due_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Add Transaction Item -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Tambah Item</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="relative md:col-span-2">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live="searchProduct" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
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
                    <label for="quantity" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah</label>
                    <input type="number" id="quantity" wire:model="quantity" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('quantity') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="text-right mt-6">
                <button type="button" wire:click="addItem()" class="w-full md:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 dark:bg-green-500 dark:hover:bg-green-600">Tambah Item ke Keranjang</button>
            </div>
        </div>

        <!-- Transaction Items List -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Keranjang</h3>
            <div class="space-y-4">
                @forelse($transaction_items as $index => $item)
                    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item['product_name'] }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($item['subtotal'], 0) }}</p>
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-medium">Hapus</button>
                        </div>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400 py-4">Keranjang masih kosong.</p>
                @endforelse
            </div>
            <div class="mt-6 pt-4 border-t-2 border-gray-200 dark:border-gray-600 flex justify-between items-center">
                <span class="text-xl font-bold text-gray-900 dark:text-white">Total</span>
                <span class="text-xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('transactions.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="saveTransaction()" wire:loading.attr="disabled" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                <span wire:loading.remove>Simpan Transaksi</span>
                <span wire:loading>Menyimpan...</span>
            </button>
        </div>
    </div>
</div>