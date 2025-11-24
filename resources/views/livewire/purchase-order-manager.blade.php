<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Daftar Surat Pesanan</h2>
        
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700 flex items-center">
                <span>Buat Surat Pesanan</span>
                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 dark:bg-gray-700 ring-1 ring-black ring-opacity-5">
                <div class="py-1">
                    <a href="{{ route('purchase-orders.create', ['type' => 'general']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">SP Umum</a>
                    <a href="{{ route('purchase-orders.create', ['type' => 'oot']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">SP OOT</a>
                    <a href="{{ route('purchase-orders.create', ['type' => 'prekursor']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">SP Prekursor</a>
                </div>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <input type="text" wire:model.live="search" placeholder="Cari Surat Pesanan..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full md:w-1/3 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        <div class="flex items-center">
            <label for="filterStatus" class="mr-2 text-gray-700 text-sm font-bold dark:text-gray-300">Status:</label>
            <select wire:model.live="filterStatus" class="shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                <option value="all">Semua</option>
                <option value="draft">Draft</option>
                <option value="sent">Terkirim</option>
                <option value="completed">Selesai</option>
                <option value="cancelled">Dibatalkan</option>
            </select>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">No. SP</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($purchaseOrders as $po)
                    <tr class="cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" onclick="window.location='{{ route('purchase-orders.show', $po->id) }}'">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">#{{ $po->po_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $po->supplier->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($po->order_date)->format('d-m-Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $po->status == 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 
                                   ($po->status == 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' : 
                                   ($po->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' : 
                                   'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                                {{ ucfirst($po->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium" onclick="event.stopPropagation()">
                            <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 mr-3">Cetak</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-gray-500 dark:text-gray-400">Tidak ada Surat Pesanan ditemukan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($purchaseOrders as $po)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-600" onclick="window.location='{{ route('purchase-orders.show', $po->id) }}'">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $po->supplier->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">#{{ $po->po_number }}</p>
                </div>
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                    {{ $po->status == 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 
                       ($po->status == 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' : 
                       ($po->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' : 
                       'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                    {{ ucfirst($po->status) }}
                </span>
            </div>
            <div class="mt-2">
                <div class="flex items-center justify-between text-sm mt-1">
                    <span class="text-gray-600 dark:text-gray-400">Tanggal</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($po->order_date)->format('d-m-Y') }}</span>
                </div>
                <div class="mt-3 flex justify-end">
                     <a href="{{ route('purchase-orders.print', $po->id) }}" target="_blank" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 text-sm font-medium" onclick="event.stopPropagation()">Cetak PDF</a>
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada Surat Pesanan ditemukan.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $purchaseOrders->links() }}
    </div>
</div>
