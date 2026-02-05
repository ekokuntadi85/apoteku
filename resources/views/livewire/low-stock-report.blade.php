<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Laporan Stok Menipis</h2>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
        <div class="max-w-sm">
            <label for="stock_threshold" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tampilkan produk dengan stok di bawah:</label>
            <x-flux.dropdown wire:model.live="stock_threshold" id="stock_threshold" wire:key="stock-threshold-dropdown">
                <x-flux.dropdown.option value="5" label="5" />
                <x-flux.dropdown.option value="10" label="10" />
                <x-flux.dropdown.option value="20" label="20" />
            </x-flux.dropdown>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white dark:bg-gray-700 shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok Saat Ini</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($products as $product)
                <tr wire:key="product-{{ $product->id }}" class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('products.show', $product->id) }}'">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $product->sku }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $product->total_stock <= 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                        {{ $product->total_stock ?? 0 }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center py-10 text-gray-500 dark:text-gray-400">Tidak ada produk dengan stok menipis.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($products as $product)
        <div wire:key="product-mobile-{{ $product->id }}" class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('products.show', $product->id) }}'">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $product->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $product->sku }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Sisa Stok</p>
                    <p class="text-2xl font-bold {{ $product->total_stock <= 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $product->total_stock ?? 0 }}</p>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada produk dengan stok menipis.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $products->links() }}
    </div>
</div>
