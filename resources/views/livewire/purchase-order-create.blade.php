<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white">
            Buat Surat Pesanan Baru
            @if($type === 'oot')
                <span class="text-lg text-blue-600 dark:text-blue-400">(OOT)</span>
            @elseif($type === 'prekursor')
                <span class="text-lg text-purple-600 dark:text-purple-400">(Prekursor)</span>
            @else
                <span class="text-lg text-gray-600 dark:text-gray-400">(Umum)</span>
            @endif
        </h2>
    </div>

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-900 dark:text-red-100 dark:border-red-700" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-700 shadow-md rounded px-8 pt-6 pb-8 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="po_number">
                    Nomor SP
                </label>
                <input wire:model="po_number" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500" id="po_number" type="text" placeholder="Nomor SP">
                @error('po_number') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="supplier_id">
                    Supplier
                </label>
                <select wire:model="supplier_id" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500" id="supplier_id">
                    <option value="">Pilih Supplier</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>
                @error('supplier_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="order_date">
                    Tanggal Pesanan
                </label>
                <input wire:model="order_date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500" id="order_date" type="date">
                @error('order_date') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="status">
                    Status
                </label>
                <select wire:model="status" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500" id="status">
                    <option value="draft">Draft</option>
                    <option value="sent">Terkirim</option>
                </select>
                @error('status') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>
        </div>
        
        <div class="mb-4">
             <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="notes">
                Catatan
            </label>
            <textarea wire:model="notes" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500" id="notes" rows="3"></textarea>
        </div>

        <hr class="my-6 border-gray-300 dark:border-gray-600">

        <h3 class="text-xl font-bold mb-4 text-gray-800 dark:text-white">Item Pesanan</h3>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-4 items-end bg-gray-50 p-4 rounded dark:bg-gray-600">
            <div class="md:col-span-4 relative">
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300 flex items-center justify-between" for="product_search">
                    <span>Produk</span>
                    @if($selectedProductName)
                        <span class="text-xs text-green-600 dark:text-green-400 font-normal">Terpilih: {{ $selectedProductName }}</span>
                    @endif
                </label>
                <input wire:model.live="searchProduct" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-500 dark:text-gray-200 dark:border-gray-400" id="product_search" type="text" placeholder="Cari Produk...">
                
                @if(!empty($searchResults))
                    <ul class="absolute z-10 bg-white border border-gray-300 w-full rounded mt-1 max-h-60 overflow-y-auto dark:bg-gray-700 dark:border-gray-600">
                        @foreach($searchResults as $result)
                            <li wire:click="selectProduct({{ $result->id }})" class="p-2 hover:bg-gray-100 cursor-pointer dark:text-gray-200 dark:hover:bg-gray-600">
                                {{ $result->name }} ({{ $result->sku }})
                            </li>
                        @endforeach
                    </ul>
                @endif
                @error('product_id') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="unit_id">
                    Satuan
                </label>
                <select wire:model.live="selectedProductUnitId" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-500 dark:text-gray-200 dark:border-gray-400" {{ empty($selectedProductUnits) ? 'disabled' : '' }}>
                    <option value="">Pilih Satuan</option>
                    @foreach($selectedProductUnits as $unit)
                        <option value="{{ $unit['id'] }}">{{ $unit['name'] }}</option>
                    @endforeach
                </select>
                @error('selectedProductUnitId') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-2">
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="quantity">
                    Jumlah
                </label>
                <input wire:model="quantity" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-500 dark:text-gray-200 dark:border-gray-400" id="quantity" type="number" min="1">
                @error('quantity') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-3">
                <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300" for="estimated_price">
                    Est. Harga Satuan
                </label>
                <input wire:model="estimated_price" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-500 dark:text-gray-200 dark:border-gray-400" id="estimated_price" type="number" min="0">
                @error('estimated_price') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
            </div>

            <div class="md:col-span-1">
                <button wire:click="addItem" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full dark:bg-green-600 dark:hover:bg-green-700">
                    +
                </button>
            </div>
        </div>

        <div class="overflow-x-auto mb-4">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-600">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        @if(in_array($type, ['oot', 'prekursor']))
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Zat Aktif</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Bentuk Sediaan</th>
                        @endif
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Satuan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Est. Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Subtotal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-600">
                    @forelse($order_items as $index => $item)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['product_name'] }}</td>
                        @if(in_array($type, ['oot', 'prekursor']))
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-200">
                                @if($type === 'oot')
                                    <select wire:model.blur="order_items.{{ $index }}.active_substance" 
                                            class="w-full text-sm rounded border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                        <option value="">Pilih Zat Aktif</option>
                                        @foreach($ootActiveSubstances as $substance)
                                            <option value="{{ $substance }}">{{ $substance }}</option>
                                        @endforeach
                                    </select>
                                @elseif($type === 'prekursor')
                                    <select wire:model.blur="order_items.{{ $index }}.active_substance" 
                                            class="w-full text-sm rounded border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                        <option value="">Pilih Zat Aktif</option>
                                        @foreach($prekursorActiveSubstances as $substance)
                                            <option value="{{ $substance }}">{{ $substance }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-gray-200">
                                <select wire:model.blur="order_items.{{ $index }}.dosage_form" 
                                        class="w-full text-sm rounded border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                                    @foreach($allUnits as $unitName)
                                        <option value="{{ $unitName }}">{{ $unitName }}</option>
                                    @endforeach
                                </select>
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $item['unit_name'] }}</td>
                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200">
                            <input type="number" wire:model.blur="order_items.{{ $index }}.quantity" min="1"
                                   class="w-20 text-sm rounded border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        </td>
                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200">
                            <input type="number" wire:model.blur="order_items.{{ $index }}.estimated_price" min="0"
                                   class="w-24 text-sm rounded border-gray-300 dark:bg-gray-600 dark:border-gray-500 dark:text-white">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">Rp {{ number_format($item['subtotal'], 2) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button wire:click="removeItem({{ $index }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ in_array($type, ['oot', 'prekursor']) ? '8' : '6' }}" class="text-center py-4 text-gray-500 dark:text-gray-400">Belum ada item.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($order_items) > 0)
                <tfoot class="bg-gray-50 dark:bg-gray-600">
                    <tr>
                        <td colspan="{{ in_array($type, ['oot', 'prekursor']) ? '6' : '4' }}" class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">Total Estimasi:</td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format(collect($order_items)->sum('subtotal'), 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('purchase-orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700">
                Batal
            </a>
            <button wire:click="saveOrder" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">
                Simpan Surat Pesanan
            </button>
        </div>
    </div>
</div>
