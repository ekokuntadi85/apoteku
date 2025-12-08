
<div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg overflow-hidden mt-8">
    <div class="px-4 sm:px-6 py-5 border-b border-gray-200 dark:border-gray-700">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Restore Database</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pilih file backup SQL (.sql) untuk me-restore database. Semua data yang ada saat ini akan ditimpa.</p>
    </div>

    <div class="p-4 sm:p-6">
        @if (session()->has('message'))
            <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 mb-6" role="alert">
                <p class="font-bold">Berhasil</p>
                <p>{{ session('message') }}</p>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 text-red-700 dark:text-red-200 p-4 mb-6" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        {{-- Info box about automatic migration --}}
        <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 dark:border-blue-400 text-blue-700 dark:text-blue-200 p-4 mb-6" role="alert">
            <p class="font-bold flex items-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                Informasi Penting
            </p>
            <p class="mt-2 text-sm">
                Setelah restore berhasil, sistem akan <strong>otomatis menjalankan migration</strong> untuk memastikan struktur database up-to-date. 
                Ini akan menambahkan kolom atau tabel baru yang mungkin belum ada di backup lama.
            </p>
            <p class="mt-2 text-sm">
                ⚠️ <strong>Peringatan:</strong> Semua data yang ada saat ini akan ditimpa oleh data dari file backup.
            </p>
        </div>

        <form wire:submit.prevent="restoreDatabase">
            <div class="mb-4">
                <label for="sqlFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300">File Backup SQL</label>
                <input type="file" id="sqlFile" wire:model="sqlFile" class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none">
                @error('sqlFile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150" wire:loading.attr="disabled" wire:target="restoreDatabase">
                    <span wire:loading.remove wire:target="restoreDatabase">Restore Database</span>
                    <span wire:loading wire:target="restoreDatabase">Me-restore...</span>
                </button>
            </div>
        </form>

        @if($isRestoring || !empty($restoreLog))
            <div class="mt-6 p-4 bg-gray-900 text-white font-mono text-sm rounded-lg overflow-x-auto">
                <h4 class="font-bold mb-2">Log Proses Restore:</h4>
                <pre class="whitespace-pre-wrap">{{ $restoreLog }}</pre>
            </div>
        @endif
    </div>
</div>
