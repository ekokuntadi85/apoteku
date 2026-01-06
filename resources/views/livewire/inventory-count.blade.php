<div>
    <div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">

        @if(session()->has('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif

        @if($view === 'list')
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4">
                <h1 class="text-2xl font-bold mb-2 sm:mb-0">Riwayat Stok Opname</h1>
                <button type="button" wire:click="changeView('create')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:hover:bg-gray-500">Buat Opname Baru</button>
            </div>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg overflow-hidden">
                <div class="overflow-x-auto hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Petugas</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($opnames as $opname)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $opname->opname_date }}</td>
                                    <td class="px-6 py-4">{{ $opname->notes }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $opname->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($opname->status === 'finalized')
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">FINALIZED</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">DRAFT</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('stock-opname.detail', $opname->id) }}" class="inline-flex items-center px-3 py-1.5 border border-blue-500 text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none transition">Lihat Detail</a>
                                        @can('delete-purchase')
                                        @if($opname->status === 'draft')
                                        <button type="button" wire:click="deleteOpname({{ $opname->id }})" wire:confirm="Apakah Anda yakin ingin menghapus opname ini? Stok akan dikembalikan ke keadaan semula." class="ml-2 inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none transition">Hapus</button>
                                        @endif
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">Tidak ada riwayat opname.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="md:hidden">
                    @forelse($opnames as $opname)
                        <div class="p-4 border-b dark:border-gray-600">
                            <p><strong>Tanggal:</strong> {{ $opname->opname_date }}</p>
                            <p><strong>Catatan:</strong> {{ $opname->notes }}</p>
                            <p><strong>Petugas:</strong> {{ $opname->user->name }}</p>
                            <div class="mt-2 text-right">
                                <button type="button" wire:click="changeView('detail', {{ $opname->id }})" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Lihat</button>
                                @can('delete-purchase')
                                <button type="button" wire:click="deleteOpname({{ $opname->id }})" wire:confirm="Apakah Anda yakin ingin menghapus opname ini? Stok akan dikembalikan ke keadaan semula." class="ml-2 inline-flex items-center px-3 py-1.5 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-500 focus:outline-none focus:border-red-700 focus:shadow-outline-red active:bg-red-700 transition ease-in-out duration-150">Hapus</button>
                                @endcan
                            </div>
                        </div>
                    @empty
                        <p class="text-center py-4">Tidak ada riwayat opname.</p>
                    @endforelse
                </div>
            </div>
            <div class="mt-4">{{ $opnames->links() }}</div>

        @elseif($view === 'create')
            <h1 class="text-2xl font-bold mb-4">Buat Stok Opname Baru</h1>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 sm:p-6">
                <div class="mb-4">
                    <label for="searchProduct" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Cari Produk</label>
                    <input type="text" id="searchProduct" wire:model.live.debounce.300ms="searchProduct" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600" placeholder="Ketik untuk mencari produk...">
                    @if(!empty($productResults))
                        <ul class="border border-gray-300 rounded-md mt-1 bg-white dark:bg-gray-800 max-h-60 overflow-y-auto">
                            @foreach($productResults as $product)
                                <li class="p-2 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer" wire:click="addProductToOpname({{ $product->id }})">{{ $product->name }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <form wire:submit.prevent="saveOpname">
                    <div class="mb-4">
                        <label for="opname_notes" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Catatan (Opsional)</label>
                        <textarea id="opname_notes" wire:model="opname_notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600"></textarea>
                    </div>

                    <!-- Desktop Table -->
                    <div class="overflow-x-auto hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 mb-4">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Produk</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Batch</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stok Sistem</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Stok Fisik</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                @forelse($opname_items as $index => $item)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $item['product_name'] }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $item['batch_number'] }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">{{ $item['system_stock'] }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">
                                            <input type="number" wire:model="opname_items.{{ $index }}.physical_stock" class="w-24 text-center rounded-md border-gray-300 shadow-sm dark:bg-gray-800 dark:border-gray-600">
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right">
                                            <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">Hapus</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center py-8 text-gray-500">Tambahkan produk untuk diopname.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4">
                        @forelse($opname_items as $index => $item)
                            <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow">
                                <p class="font-bold">{{ $item['product_name'] }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Batch: {{ $item['batch_number'] }}</p>
                                <div class="mt-2 flex justify-between items-center">
                                    <div class="text-center">
                                        <p class="text-xs text-gray-500">Stok Sistem</p>
                                        <p>{{ $item['system_stock'] }}</p>
                                    </div>
                                    <div class="text-center">
                                        <label for="mobile_physical_stock_{{ $index }}" class="text-xs text-gray-500">Stok Fisik</label>
                                        <input id="mobile_physical_stock_{{ $index }}" type="number" wire:model="opname_items.{{ $index }}.physical_stock" class="w-20 text-center rounded-md border-gray-300 shadow-sm dark:bg-gray-900 dark:border-gray-600">
                                    </div>
                                    <div>
                                        <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 p-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 012 0v6a1 1 0 11-2 0V8z" clip-rule="evenodd" /></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center py-8 text-gray-500">Tambahkan produk untuk diopname.</p>
                        @endforelse
                    </div>
                    @error('opname_items') <p class="text-red-500 text-xs italic mt-2">{{ $message }}</p> @enderror

                    <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-4 mt-4">
                        <button type="button" wire:click="changeView('list')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Batal</button>
                        <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition dark:bg-gray-600 dark:hover:bg-gray-500">Simpan Opname</button>
                    </div>
                </form>
            </div>

        @elseif($view === 'detail')
            <h1 class="text-2xl font-bold mb-4">Detail Stok Opname #{{ $selectedOpname->id }}</h1>
            <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 sm:p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <div><strong>Tanggal:</strong> {{ $selectedOpname->opname_date }}</div>
                    <div><strong>Petugas:</strong> {{ $selectedOpname->user->name }}</div>
                    <div class="sm:col-span-2"><strong>Catatan:</strong> {{ $selectedOpname->notes }}</div>
                </div>

                <!-- Desktop Table -->
                <div class="overflow-x-auto hidden md:block">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600 mt-4">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-2/5">Produk</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Batch</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Stok Sistem</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Stok Fisik</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase w-1/5">Selisih</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-600">
                            @foreach($selectedOpname->details as $detail)
                                <tr>
                                    <td class="px-4 py-4">{{ $detail->productBatch->product->name }}</td>
                                    <td class="px-4 py-4">{{ $detail->productBatch->batch_number }}</td>
                                    <td class="px-4 py-4 text-center">{{ $detail->system_stock }}</td>
                                    <td class="px-4 py-4 text-center">{{ $detail->physical_stock }}</td>
                                    <td class="px-4 py-4 text-center">{{ $detail->difference }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="md:hidden space-y-4 mt-4">
                    @foreach($selectedOpname->details as $detail)
                        <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg shadow">
                            <p class="font-bold">{{ $detail->productBatch->product->name }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Batch: {{ $detail->productBatch->batch_number }}</p>
                            <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                                <div>
                                    <p class="text-xs text-gray-500">Sistem</p>
                                    <p>{{ $detail->system_stock }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Fisik</p>
                                    <p>{{ $detail->physical_stock }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500">Selisih</p>
                                    <p>{{ $detail->difference }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex justify-end mt-4">
                    <button type="button" wire:click="changeView('list')" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 disabled:opacity-25 transition dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500">Kembali</button>
                </div>
            </div>
        @endif
    </div>
</div>