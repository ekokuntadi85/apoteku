<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="max-w-2xl mx-auto bg-white/70 dark:bg-gray-800/60 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
        <h2 class="text-3xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Edit Produk</h2>

        <form wire:submit.prevent="save">
            <input type="hidden" wire:model="productId">
            <div class="space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Produk</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Stock Keeping Unit)</label>
                    <input type="text" id="sku" wire:model="sku" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                    @error('sku') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 to-purple-600">Satuan Produk</h3>

                    @foreach($productUnits as $index => $unit)
                        <div wire:key="unit-{{ $index }}" class="p-4 border border-gray-200/70 rounded-xl dark:border-gray-600/60 bg-white/80 dark:bg-gray-700/70 backdrop-blur">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="productUnits.{{ $index }}.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Satuan</label>
                                    @if($unit['is_base_unit'])
                                        <input type="text" id="productUnits.{{ $index }}.name" wire:model="productUnits.{{ $index }}.name" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500" readonly>
                                    @else
                                        <select id="productUnits.{{ $index }}.name" wire:model="productUnits.{{ $index }}.name" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                                            <option value="">Pilih Satuan</option>
                                            @foreach($units as $u)
                                                <option value="{{ $u->name }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    @error('productUnits.' . $index . '.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.conversion_factor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Faktor Konversi (x Satuan Dasar)</label>
                                    <input type="number" step="0.01" id="productUnits.{{ $index }}.conversion_factor" wire:model="productUnits.{{ $index }}.conversion_factor" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500" @if($unit['is_base_unit']) readonly @endif>
                                    @error('productUnits.' . $index . '.conversion_factor') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" step="1" id="productUnits.{{ $index }}.selling_price" wire:model="productUnits.{{ $index }}.selling_price" class="pl-10 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    @error('productUnits.' . $index . '.selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.purchase_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Beli</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" step="1" id="productUnits.{{ $index }}.purchase_price" wire:model="productUnits.{{ $index }}.purchase_price" class="pl-10 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500">
                                    </div>
                                    @error('productUnits.' . $index . '.purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if(!$unit['is_base_unit'])
                                <div class="mt-4 text-right">
                                    <button type="button" wire:click="removeUnit({{ $index }})" class="inline-flex items-center px-3 py-1 border border-transparent shadow-sm text-sm font-semibold rounded-full text-white bg-rose-500 hover:bg-rose-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-400 dark:bg-rose-600 dark:hover:bg-rose-700">
                                        Hapus Satuan
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" wire:click="addUnit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-emerald-500 hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-400 dark:bg-emerald-600 dark:hover:bg-emerald-700">
                            <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5" />
                            Tambah Satuan
                        </button>
                    </div>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <select id="category_id" wire:model="category_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <a href="{{ route('products.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-indigo-500 to-fuchsia-500 hover:from-indigo-600 hover:to-fuchsia-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400">
                    Update Produk
                </button>
            </div>
        </form>
    </div>
</div>