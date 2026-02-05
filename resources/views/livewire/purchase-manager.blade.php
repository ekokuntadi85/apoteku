<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <input type="text" wire:model.live="search" placeholder="Cari pembelian..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full md:w-1/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        <div class="flex flex-col md:flex-row items-center space-y-2 md:space-y-0 md:space-x-4">
            <div class="flex items-center">
                <label for="filterStatus" class="mr-2 text-gray-700 text-sm font-bold dark:text-gray-300">Status:</label>
                <x-flux.button.group wire:model.live="filterStatus">
                    <x-flux.button.group.option property="filterStatus" value="all" label="Semua" />
                    <x-flux.button.group.option property="filterStatus" value="unpaid" label="Belum Lunas" />
                    <x-flux.button.group.option property="filterStatus" value="paid" label="Lunas" />
                </x-flux.button.group>
            </div>
            <a href="{{ route('purchases.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700">Tambah Pembelian</a>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($purchases as $purchase)
                    <tr class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" onclick="window.location='{{ route('purchases.show', $purchase->id) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ $purchase->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($purchase->total_price, 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('d-m-Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $purchase->payment_status == 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' }}">
                                {{ ucfirst($purchase->payment_status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">Tidak ada data pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($purchases as $purchase)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('purchases.show', $purchase->id) }}'">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $purchase->supplier->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">#{{ $purchase->invoice_number }}</p>
                </div>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $purchase->payment_status == 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' }}">
                    {{ ucfirst($purchase->payment_status) }}
                </span>
            </div>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Pembelian</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($purchase->total_price, 2) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Tanggal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</span>
                </div>
                @if($purchase->due_date)
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Jatuh Tempo</span>
                    <span class="font-semibold text-red-500 dark:text-red-400">{{ \Carbon\Carbon::parse($purchase->due_date)->format('d-m-Y') }}</span>
                </div>
                @endif
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada pembelian ditemukan.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $purchases->links() }}
    </div>
</div>
