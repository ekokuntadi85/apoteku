<div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-show="$wire.showModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800" @click.away="$wire.closeModal()">
        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white mb-4">{{ $isUpdateMode ? 'Edit Data' : 'Tambah Data Baru' }}</h3>
        <form wire:submit.prevent="store">
            <input type="hidden" wire:model="kartuMonitoringSuhuId">
            <div class="mb-4">
                <label for="suhu_ruangan" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Suhu Ruangan:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="suhu_ruangan" placeholder="Masukkan Suhu Ruangan" wire:model="suhu_ruangan">
                @error('suhu_ruangan') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="suhu_pendingin" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Suhu Pendingin:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="suhu_pendingin" placeholder="Masukkan Suhu Pendingin" wire:model="suhu_pendingin">
                @error('suhu_pendingin') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="mb-6">
                <label for="waktu_pengukuran" class="block text-gray-700 text-sm font-bold mb-2 dark:text-gray-300">Waktu Pengukuran:</label>
                <input type="datetime-local" class="form-control shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" id="waktu_pengukuran" wire:model="waktu_pengukuran">
                @error('waktu_pengukuran') <span class="text-red-500 text-xs italic">{{ $message }}</span>@enderror
            </div>
            <div class="flex items-center justify-end space-x-2">
                <button type="button" wire:click="closeModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700">Batal</button>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">{{ $isUpdateMode ? 'Update' : 'Simpan' }}</button>
            </div>
        </form>
    </div>
</div>