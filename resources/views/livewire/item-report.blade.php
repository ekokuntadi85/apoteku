<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <h3 class="text-xl font-bold mb-4 dark:text-gray-100">Laporan Penjualan per Item</h3>

        @if ($selectedProduct)
            <div class="flex justify-between items-center mb-4">
                <h4 class="text-lg font-bold dark:text-gray-200">Menampilkan laporan untuk: <span class="text-blue-500">{{ $selectedProduct->name }}</span></h4>
                <div class="flex items-center space-x-4">
                    <button type="button" wire:click="exportExcel" class="text-sm bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-3 rounded focus:outline-none focus:shadow-outline dark:bg-green-600 dark:hover:bg-green-700">
                        <span wire:loading.remove wire:target="exportExcel">
                            Ekspor Excel
                        </span>
                        <span wire:loading wire:target="exportExcel">
                            Mengekspor...
                        </span>
                    </button>
                    <button wire:click="clearSelection" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">&times; Hapus Pilihan</button>
                </div>
            </div>
        @else
            <div class="mb-4 relative">
                <label for="searchTerm" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Cari Produk:</label>
                <input type="text" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
                       placeholder="Ketik nama atau SKU produk..."
                       wire:model.live.debounce.300ms="searchTerm">

                @if(!empty($this->products))
                    <ul class="absolute z-10 w-full bg-white border border-gray-300 rounded-md mt-1 dark:bg-gray-800 dark:border-gray-600">
                        @foreach($this->products as $product)
                            <li class="px-4 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700" 
                                wire:click="selectProduct({{ $product->id }})">
                                {{ $product->name }} ({{ $product->sku }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif
    </div>

    @if ($selectedProduct)
        {{-- Filter Section --}}
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
            <h4 class="text-lg font-bold mb-4 dark:text-gray-100">Filter Riwayat Penjualan</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="filterType" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Jenis Filter:</label>
                    <select wire:model.live="filterType" id="filterType" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="all">Semua</option>
                        <option value="day">Per Hari</option>
                        <option value="month">Per Bulan</option>
                        <option value="range">Rentang Tanggal</option>
                    </select>
                </div>
                @if ($filterType === 'day')
                    <div>
                        <label for="filterDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Tanggal:</label>
                        <input type="date" wire:model.live="filterDate" id="filterDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    </div>
                @elseif ($filterType === 'month')
                    <div>
                        <label for="filterMonth" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Bulan:</label>
                        <input type="month" wire:model.live="filterMonth" id="filterMonth" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    </div>
                @elseif ($filterType === 'range')
                    <div>
                        <label for="filterStartDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Dari Tanggal:</label>
                        <input type="date" wire:model.live="filterStartDate" id="filterStartDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    </div>
                    <div>
                        <label for="filterEndDate" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Sampai Tanggal:</label>
                        <input type="date" wire:model.live="filterEndDate" id="filterEndDate" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    </div>
                @endif
                <div class="flex items-end">
                    <button type="button" wire:click="applyFilter()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Terapkan Filter</button>
                </div>
            </div>
        </div>

        {{-- Total Quantity Display --}}
        <div class="text-left text-xl font-bold mb-4 dark:text-gray-100">
            Total Item Terjual (dalam {{ $baseUnitName }}): {{ number_format($totalQuantityInBaseUnit, 2) }}
        </div>

        {{-- Table Area (Desktop) / Card Area (Mobile) --}}
        <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
            <div class="overflow-x-auto hidden md:block">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Invoice</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jumlah</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Satuan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($details as $detail)
                        <tr class="dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->transaction->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                                <a href="{{ route('transactions.show', $detail->transaction->id) }}" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-500">
                                    {{ $detail->transaction->invoice_number ?? '-' }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $detail->productUnit->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada riwayat penjualan untuk produk ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($details)
                <div class="p-4">
                    {{ $details->links('livewire::tailwind', [], 'salesPage') }}
                </div>
                @endif
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
                            <p class="text-gray-800 dark:text-gray-100">Jumlah: {{ $detail->quantity }}</p>
                            <p class="text-gray-800 dark:text-gray-100">Satuan: {{ $detail->productUnit->name ?? '-' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center">Tidak ada riwayat penjualan untuk produk ini.</p>
                @endforelse
                @if($details)
                <div class="p-4">
                    {{ $details->links('livewire::tailwind', [], 'salesPage') }}
                </div>
                @endif
            </div>
        </div>
    @endif
</div>