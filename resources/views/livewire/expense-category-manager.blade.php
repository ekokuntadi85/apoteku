<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Kategori Pengeluaran</h1>

    @if($errors->has('delete'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-800 dark:border-red-700 dark:text-red-200" role="alert">
            <span class="block sm:inline">{{ $errors->first('delete') }}</span>
        </div>
    @endif

    <div class="flex flex-col-reverse md:flex-row md:justify-between md:items-center mb-6 space-y-4 md:space-y-0">
        <div class="relative w-full md:w-1/3">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" wire:model.live.debounce="search" placeholder="Cari kategori..." class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>
        <button wire:click="create" class="inline-flex items-center justify-center bg-gradient-to-r from-indigo-500 to-fuchsia-500 hover:from-indigo-600 hover:to-fuchsia-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md border-b-4 border-indigo-700 hover:border-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 w-full md:w-auto md:ml-4 transition-all transform active:scale-95 active:border-b-0 active:mt-1">
            Tambah Kategori
        </button>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border border-gray-200 sm:rounded-xl dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 backdrop-blur">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-indigo-50 to-fuchsia-50 dark:from-zinc-800 dark:to-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300 w-32">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($categories as $category)
                    <tr class="hover:bg-indigo-50/60 dark:hover:bg-zinc-800/70 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200 font-medium">{{ $category->name }}</td>
                        <td class="px-6 py-4 text-gray-500 dark:text-gray-400">{{ $category->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <button wire:click="edit({{ $category->id }})" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-1.5 px-3 rounded-full text-xs dark:bg-emerald-600 dark:hover:bg-emerald-700">Edit</button>
                                <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus?" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold py-1.5 px-3 rounded-full text-xs dark:bg-rose-600 dark:hover:bg-rose-700">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @foreach($categories as $category)
        <div class="bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-4 border border-gray-200/70 dark:border-gray-600/60 backdrop-blur">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $category->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $category->description }}</p>
                </div>
                <div class="flex flex-col space-y-2">
                    <button wire:click="edit({{ $category->id }})" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-1 px-3 rounded-full text-xs dark:bg-emerald-600 dark:hover:bg-emerald-700">Edit</button>
                    <button wire:click="delete({{ $category->id }})" wire:confirm="Yakin ingin menghapus?" class="bg-rose-500 hover:bg-rose-600 text-white font-semibold py-1 px-3 rounded-full text-xs dark:bg-rose-600 dark:hover:bg-rose-700">Hapus</button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 transition-opacity" x-data x-transition>
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4 border-b pb-2">{{ $editMode ? 'Edit Kategori' : 'Tambah Kategori' }}</h3>
            <form wire:submit.prevent="save">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Nama Kategori</label>
                    <input type="text" wire:model="name" placeholder="Contoh: Operasional, Gaji" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                    @error('name') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>
                
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Deskripsi</label>
                    <textarea wire:model="description" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 h-24" placeholder="Keterangan tambahan..."></textarea>
                    @error('description') <span class="text-red-500 text-xs italic">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center justify-end space-x-2">
                    <button type="button" wire:click="$set('showModal', false)" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700">Batal</button>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
