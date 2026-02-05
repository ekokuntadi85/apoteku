<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Hutang Usaha</h1>
    
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 space-y-4 md:space-y-0 gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-1/3">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" wire:model.live.debounce="search" placeholder="Cari invoice/supplier..." class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>

        <!-- Filter -->
         <div class="w-full md:w-auto">
            <select wire:model.live="filterStatus" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 w-full">
                <option value="all">Semua Belum Lunas</option>
                <option value="unpaid_only">Belum Dibayar</option>
                <option value="partial">Cicilan (Parsial)</option>
            </select>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border border-gray-200 sm:rounded-xl dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-indigo-50 to-fuchsia-50 dark:from-zinc-800 dark:to-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">No Invoice</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jatuh Tempo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($purchases as $purchase)
                    <tr class="hover:bg-indigo-50/60 dark:hover:bg-zinc-800/70 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200 font-mono text-sm">{{ $purchase->invoice_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->purchase_date ? $purchase->purchase_date : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200 font-medium">{{ $purchase->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono text-gray-900 dark:text-white">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($purchase->payment_status == 'paid')
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Lunas</span>
                            @elseif($purchase->payment_status == 'partial')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Cicilan</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Belum Bayar</span>
                            @endif
                        </td>
                         <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $purchase->due_date ? $purchase->due_date : '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('purchases.show', $purchase) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1.5 px-3 rounded-full text-xs dark:bg-blue-600 dark:hover:bg-blue-700" wire:navigate>Lihat</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            {{ $purchases->links() }}
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @foreach($purchases as $purchase)
        <div class="bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-4 border border-gray-200/70 dark:border-gray-600/60 backdrop-blur">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white">{{ $purchase->invoice_number }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $purchase->supplier->name }}</p>
                </div>
                 @if($purchase->payment_status == 'paid')
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Lunas</span>
                @elseif($purchase->payment_status == 'partial')
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Cicilan</span>
                @else
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Belum Bayar</span>
                @endif
            </div>
            
            <div class="flex justify-between items-center mt-3">
                <span class="font-mono font-bold text-gray-900 dark:text-white">Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</span>
                 <a href="{{ route('purchases.show', $purchase) }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-1 px-3 rounded-full text-xs dark:bg-blue-600 dark:hover:bg-blue-700" wire:navigate>Lihat Invoice</a>
            </div>
            <div class="text-xs text-gray-400 mt-2">Jatuh Tempo: {{ $purchase->due_date ? $purchase->due_date : '-' }}</div>
        </div>
        @endforeach
         <div class="mt-4">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
