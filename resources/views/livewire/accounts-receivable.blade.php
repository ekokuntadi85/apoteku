<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Piutang Usaha</h2>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <input type="text" wire:model.live="search" placeholder="Cari transaksi..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full md:w-1/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        <div class="flex items-center">
            <label for="filterStatus" class="mr-2 text-gray-700 text-sm font-bold dark:text-gray-300">Status:</label>
            <x-flux.button.group wire:model.live="filterStatus">
                <x-flux.button.group.option property="filterStatus" value="all" label="Semua" />
                <x-flux.button.group.option property="filterStatus" value="unpaid" label="Belum Lunas" />
                <x-flux.button.group.option property="filterStatus" value="paid" label="Lunas" />
            </x-flux.button.group>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">No. Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Pelanggan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Tagihan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jatuh Tempo</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($transactions as $transaction)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ $transaction->invoice_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->customer->name ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($transaction->total_price, 0) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($transaction->payment_status == 'paid')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                                    Lunas
                                </span>
                            @elseif($transaction->payment_status == 'partial')
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                                    Dibayar Sebagian
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                                    Belum Lunas
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($transaction->payment_status !== 'paid')
                                <a href="{{ route('payments.create', $transaction->id) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200">Bayar</a>
                            @endif
                            <a href="{{ route('transactions.show', $transaction->id) }}" class="ml-4 text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200">Lihat</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-10 text-gray-500 dark:text-gray-400">Tidak ada piutang ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($transactions as $transaction)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">#{{ $transaction->invoice_number ?? '-' }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $transaction->customer->name ?? '-' }}</p>
                </div>
                @if($transaction->payment_status == 'paid')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100">
                        Lunas
                    </span>
                @elseif($transaction->payment_status == 'partial')
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100">
                        Dibayar Sebagian
                    </span>
                @else
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100">
                        Belum Lunas
                    </span>
                @endif
            </div>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-gray-600 dark:text-gray-400">Total Tagihan</span>
                    <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($transaction->total_price, 0) }}</span>
                </div>
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Jatuh Tempo</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ $transaction->due_date ? \Carbon\Carbon::parse($transaction->due_date)->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
            <div class="flex justify-end mt-4 space-x-2">
                @if($transaction->payment_status !== 'paid')
                    <a href="{{ route('payments.create', $transaction->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-blue-600 dark:hover:bg-blue-700">Bayar</a>
                @endif
                <a href="{{ route('transactions.show', $transaction->id) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-indigo-600 dark:hover:bg-indigo-700">Lihat</a>
            </div>
        </div>
        @empty
        <div class="text-center py-10 px-4 bg-white dark:bg-gray-700 rounded-lg shadow-md">
            <p class="text-gray-500 dark:text-gray-400">Tidak ada piutang ditemukan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $transactions->links() }}
    </div>
</div>