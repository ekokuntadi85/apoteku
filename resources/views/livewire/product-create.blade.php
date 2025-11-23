<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="max-w-2xl mx-auto bg-white/70 dark:bg-gray-800/60 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
        <h2 class="text-3xl font-bold mb-8 bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">Tambah Produk Baru</h2>

        <form wire:submit.prevent="save">
            <div class="space-y-8">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Produk</label>
                    <input type="text" id="name" wire:model="name" class="mt-1 block w-full h-11 shadow-sm sm:text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                    @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700 dark:text-gray-300">SKU (Stock Keeping Unit)</label>
                    <input type="text" id="sku" wire:model="sku" class="mt-1 block w-full h-11 shadow-sm sm:text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                    @error('sku') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-4">
                    <h3 class="text-lg font-semibold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-600">Satuan Produk</h3>

                    @foreach($productUnits as $index => $unit)
                        <div wire:key="unit-{{ $index }}" class="p-5 border-2 border-emerald-200/70 dark:border-emerald-700/60 rounded-xl bg-gradient-to-br from-white to-emerald-50/30 dark:from-gray-700/70 dark:to-emerald-900/10 backdrop-blur shadow-sm">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="productUnits.{{ $index }}.name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Satuan</label>
                                    <select id="productUnits.{{ $index }}.name" wire:model="productUnits.{{ $index }}.name" class="mt-1 block w-full h-11 pl-3 pr-10 text-base border-2 border-emerald-200 dark:border-emerald-700 bg-emerald-50/30 dark:bg-emerald-900/10 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-800 sm:text-sm rounded-lg dark:text-gray-200 transition-all duration-200">
                                        <option value="">Pilih Satuan</option>
                                        @foreach($units as $u)
                                            <option value="{{ $u->name }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('productUnits.' . $index . '.name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.conversion_factor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Faktor Konversi (x Satuan Dasar)</label>
                                    <input type="number" step="0.01" id="productUnits.{{ $index }}.conversion_factor" wire:model="productUnits.{{ $index }}.conversion_factor" class="mt-1 block w-full h-11 shadow-sm sm:text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200" @if($unit['is_base_unit']) readonly @endif>
                                    @error('productUnits.' . $index . '.conversion_factor') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.selling_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Jual</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" step="1" id="productUnits.{{ $index }}.selling_price" wire:model="productUnits.{{ $index }}.selling_price" class="pl-10 block w-full h-11 shadow-sm sm:text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                                    </div>
                                    @error('productUnits.' . $index . '.selling_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>

                                <div>
                                    <label for="productUnits.{{ $index }}.purchase_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Harga Beli</label>
                                    <div class="mt-1 relative rounded-md shadow-sm">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500 sm:text-sm">Rp</span>
                                        </div>
                                        <input type="number" step="1" id="productUnits.{{ $index }}.purchase_price" wire:model="productUnits.{{ $index }}.purchase_price" class="pl-10 block w-full h-11 shadow-sm sm:text-sm border-gray-300 rounded-lg dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all duration-200">
                                    </div>
                                    @error('productUnits.' . $index . '.purchase_price') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            @if(!$unit['is_base_unit'])
                                <div class="mt-4 text-right">
                                    <button type="button" wire:click="removeUnit({{ $index }})" class="inline-flex items-center px-3 py-1.5 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-red-500 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 dark:bg-red-600 dark:hover:bg-red-700 transition-all duration-200">
                                        Hapus Satuan
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="flex justify-end space-x-2 mt-4">
                        <button type="button" wire:click="addUnit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-emerald-700 bg-gradient-to-r from-emerald-100 to-teal-100 hover:from-emerald-200 hover:to-teal-200 dark:from-emerald-900/40 dark:to-teal-900/40 dark:text-emerald-300 dark:hover:from-emerald-900/60 dark:hover:to-teal-900/60 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-400 transition-all duration-300">
                            <x-heroicon-o-plus class="-ml-1 mr-2 h-5 w-5" />
                            Tambah Satuan
                        </button>
                    </div>
                </div>

                <div>
                    <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori</label>
                    <select id="category_id" wire:model="category_id" class="mt-1 block w-full h-11 pl-3 pr-10 text-base border-2 border-emerald-200 dark:border-emerald-700 bg-emerald-50/30 dark:bg-emerald-900/10 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:bg-white dark:focus:bg-gray-800 sm:text-sm rounded-lg dark:text-gray-200 transition-all duration-200">
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
                <a href="{{ route('products.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0 transition-all duration-200">Batal</a>
                <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2.5 border border-transparent shadow-lg text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-600 hover:from-emerald-600 hover:via-teal-600 hover:to-cyan-700 hover:shadow-2xl hover:shadow-emerald-500/50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-400 transition-all duration-300">
                    Simpan Produk
                </button>
            </div>
        </form>
    </div>
</div>