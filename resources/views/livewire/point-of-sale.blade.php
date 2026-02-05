<div class="w-full h-screen flex flex-col md:flex-row bg-gray-100 dark:bg-gray-900 font-sans" x-data="{ paymentModal: false }">

    <!-- Main Content Area (Search and Cart) -->
    <div class="flex-1 flex flex-col p-4 md:p-6">
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
                        <div wire:click="selectProduct({{ $product->id }})" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 flex flex-col justify-between cursor-pointer hover:shadow-lg hover:scale-105 transform transition-transform duration-200">
                            <div>
                                <h3 class="font-bold text-gray-800 dark:text-white truncate">{{ $product->name }}</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">SKU: {{ $product->sku }}</p>
                            </div>
                            <p class="text-right font-semibold text-blue-600 dark:text-blue-400 mt-2">Pilih Satuan</p>
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
            @if(empty($search))
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
                            <p class="text-sm text-gray-600 dark:text-gray-300">Rp {{ number_format($item['price'], 0) }} / {{ $item['unit_name'] }}</p>
                            <div class="flex items-center mt-2">
                                <button wire:click="updateQuantity({{ $index }}, {{ $item['original_quantity_input'] - 1 }}); checkStock({{ $index }})" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-600 text-lg font-bold">-</button>
                                <input type="text" value="{{ $item['original_quantity_input'] }}" readonly class="w-12 text-center bg-transparent font-semibold text-gray-900 dark:text-white">
                                <button wire:click="updateQuantity({{ $index }}, {{ $item['original_quantity_input'] + 1 }}); checkStock({{ $index }})" class="px-3 py-1 rounded-md bg-gray-200 dark:bg-gray-600 text-lg font-bold">+</button>
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
            <div class="flex justify-between items-center mb-3">
                <span class="text-xl font-semibold text-gray-700 dark:text-gray-200">Total</span>
                <span class="text-3xl font-bold text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0) }}</span>
            </div>
            <button @click="paymentModal = true" class="w-full bg-blue-600 text-white font-bold py-4 rounded-lg text-lg hover:bg-blue-700 transition-colors" :disabled="{{ count($cart_items) === 0 ? 'true' : 'false' }}">
                Bayar
            </button>
        </div>
    </aside>

    <!-- Unit Selection Modal -->
    <div x-show="$wire.isUnitModalVisible" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50 p-4" @keydown.escape.window="$wire.closeUnitModal()">
        <div @click.away="$wire.closeUnitModal()" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl">
            @if ($productForModal)
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $productForModal->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Pilih satuan dan jumlah</p>
                </div>
                <div class="p-6">
                    <div class="space-y-3 mb-6">
                        @foreach ($unitsForModal as $unit)
                            <div wire:click="addItemToCart({{ $unit->id }})" class="group flex justify-between items-center p-4 rounded-lg cursor-pointer bg-gray-50 hover:bg-blue-100 dark:bg-gray-700 dark:hover:bg-blue-900 transition-colors">
                                <div>
                                    <p class="font-bold text-lg text-gray-800 dark:text-white group-hover:text-blue-800 dark:group-hover:text-white">{{ $unit->name }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-300">Stok: {{ $unit->stock_in_unit }}</p>
                                </div>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-800 dark:group-hover:text-white">Rp {{ number_format($unit->selling_price, 0) }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center space-x-4">
                        <label for="quantityToAdd" class="text-lg font-medium text-gray-700 dark:text-gray-300">Jumlah:</label>
                        <input type="number" id="quantityToAdd" wire:model="quantityToAdd" min="1" class="w-24 text-center text-lg p-2 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:border-gray-600">
                        @error('quantityToAdd') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 rounded-b-lg text-right">
                     <button @click="$wire.closeUnitModal()" type="button" class="px-6 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">Tutup</button>
                </div>
            @endif
        </div>
    </div>

    <!-- Payment Modal (Existing) -->
    <div x-show="paymentModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div @click.away="paymentModal = false" class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Pembayaran</h2>
            <div class="mb-4">
                <label for="customer_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pelanggan</label>
                <select id="customer_id" wire:model="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Tagihan</label>
                <p class="text-3xl font-bold mt-1 text-gray-900 dark:text-white">Rp {{ number_format($total_price, 0) }}</p>
            </div>
            <div class="mb-6">
                <label for="amount_paid" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Bayar</label>
                <input type="number" id="amount_paid" wire:model.live="amount_paid" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 text-lg p-2">
                @error('amount_paid') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            <div class="flex justify-between items-center mb-6">
                <span class="text-gray-700 dark:text-gray-300">Kembalian</span>
                <span class="text-xl font-bold text-green-600 dark:text-green-400">Rp {{ number_format($change, 0) }}</span>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <button @click="paymentModal = false" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-2 sm:mt-0">Batal</button>
                <button wire:click="checkout" wire:loading.attr="disabled" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    <span wire:loading.remove>Selesaikan Transaksi</span>
                    <span wire:loading>Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    @script
    <script>
        Livewire.on('transaction-completed', (event) => {
            const { transactionId } = event[0];
            const printReceipt = confirm('Transaksi berhasil! Apakah Anda ingin mencetak struk?');

            if (printReceipt) {
                const url = `/transactions/${transactionId}/receipt`;
                window.open(url, '_blank');
            }
            // Always redirect the current window to a new POS session
            window.location.href = '{{ route('pos.index') }}';
        });

        Livewire.on('focus-search-input', () => {
            document.getElementById('search-product-input').focus();
        });
    </script>
    @endscript
</div>