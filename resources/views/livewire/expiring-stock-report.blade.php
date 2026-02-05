<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Laporan Stok Kedaluwarsa</h2>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="expiry_threshold_months" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tampilkan produk kedaluwarsa dalam (bulan):</label>
                <select wire:model.live="expiry_threshold_months" id="expiry_threshold_months" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @foreach(range(1, 6) as $month)
                        <option value="{{ $month }}">{{ $month }} Bulan</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block bg-white dark:bg-gray-700 shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Beli</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kedaluwarsa</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                @forelse($productBatches as $batch)
                <tr class="dark:hover:bg-gray-600">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $batch->product->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $batch->batch_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ $batch->stock }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">Rp {{ number_format($batch->purchase_price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ \Carbon\Carbon::parse($batch->expiration_date)->diffInDays(\Carbon\Carbon::now()) <= 30 ? 'text-red-500 font-semibold' : 'text-gray-900 dark:text-white' }}">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $batch->purchase->supplier->name ?? 'N/A' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">Tidak ada stok kedaluwarsa ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($productBatches as $batch)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $batch->product->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Batch: {{ $batch->batch_number }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-300">Stok</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $batch->stock }}</p>
                </div>
            </div>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Harga Beli</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($batch->purchase_price, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Tgl Kedaluwarsa</span>
                    <span class="font-semibold {{ \Carbon\Carbon::parse($batch->expiration_date)->diffInDays(\Carbon\Carbon::now()) <= 30 ? 'text-red-500' : 'text-gray-900 dark:text-white' }}">{{ \Carbon\Carbon::parse($batch->expiration_date)->format('m/Y') }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Supplier</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $batch->purchase->supplier->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada stok kedaluwarsa ditemukan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $productBatches->links() }}
    </div>
</div>
