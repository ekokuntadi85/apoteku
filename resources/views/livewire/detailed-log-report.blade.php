<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    {{-- Filter Section --}}
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <h3 class="text-xl font-bold mb-4 dark:text-gray-100">Laporan Rinci (Log Penjualan)</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label for="startDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Mulai:</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="startDate" wire:model.live="startDate">
            </div>
            <div>
                <label for="endDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal Akhir:</label>
                <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600" id="endDate" wire:model.live="endDate">
            </div>
            <div class="flex items-end space-x-2">
                <button type="button" wire:click="filter()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Filter</button>
                <button type="button" wire:click="exportExcel" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-green-600 dark:hover:bg-green-700">
                    <span wire:loading.remove wire:target="exportExcel">
                        Ekspor Excel
                    </span>
                    <span wire:loading wire:target="exportExcel">
                        Mengekspor...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Table Area (Desktop) / Card Area (Mobile) --}}
    <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto hidden md:block">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jumlah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Satuan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($details as $detail)
                    <tr class="dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->transaction->invoice_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->quantity }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->productUnit->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada data untuk periode ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">
                {{ $details->links() }}
            </div>
        </div>

        {{-- Card Area (Mobile) --}}
        <div class="md:hidden mt-4 space-y-4">
            @forelse($details as $detail)
                <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow-sm cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('transactions.show', $detail->transaction->id) }}'">
                    <div class="flex justify-between items-center mb-2">
                        <p class="font-semibold text-gray-800 dark:text-gray-100">Invoice: {{ $detail->transaction->invoice_number ?? '-' }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $detail->transaction->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-gray-800 dark:text-gray-100">Produk: {{ $detail->product->name }}</p>
                        <p class="text-gray-800 dark:text-gray-100">Jumlah: {{ $detail->quantity }} {{ $detail->productUnit->name ?? '-' }}</p>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center">Tidak ada data untuk periode ini.</p>
            @endforelse
            <div class="p-4">
                {{ $details->links() }}
            </div>
        </div>
    </div>
</div>