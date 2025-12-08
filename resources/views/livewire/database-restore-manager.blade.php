
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
                <label for="sqlFile" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">File Backup SQL</label>
                
                {{-- File Input with Custom Styling --}}
                <div class="relative">
                    <input 
                        type="file" 
                        id="sqlFile" 
                        wire:model="sqlFile" 
                        accept=".sql,.txt,.bin"
                        class="mt-1 block w-full text-sm text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-700 focus:outline-none file:mr-4 file:py-2 file:px-4 file:rounded-l-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 dark:hover:file:bg-indigo-900/50 transition-all duration-200"
                    >
                    
                    {{-- Upload Progress Indicator --}}
                    <div wire:loading wire:target="sqlFile" class="absolute inset-0 bg-white/90 dark:bg-gray-800/90 rounded-lg flex items-center justify-center backdrop-blur-sm">
                        <div class="flex items-center space-x-3">
                            <svg class="animate-spin h-5 w-5 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">Mengupload file...</span>
                        </div>
                    </div>
                </div>
                
                @error('sqlFile') 
                    <span class="text-red-500 text-sm mt-1 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </span>
                @enderror
                
                {{-- File Info Display (shown after upload complete) --}}
                @if($sqlFile && !$errors->has('sqlFile'))
                    <div wire:loading.remove wire:target="sqlFile" class="mt-3 p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg animate-fade-in">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-green-800 dark:text-green-200">File berhasil dipilih</p>
                                <div class="mt-1 text-sm text-green-700 dark:text-green-300">
                                    <p class="font-semibold truncate">📄 {{ $sqlFile->getClientOriginalName() }}</p>
                                    <p class="text-xs mt-1">
                                        Ukuran: <span class="font-medium">{{ number_format($sqlFile->getSize() / 1024 / 1024, 2) }} MB</span>
                                    </p>
                                </div>
                            </div>
                            <button 
                                type="button" 
                                wire:click="$set('sqlFile', null)" 
                                class="flex-shrink-0 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors"
                                title="Hapus file"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex items-center justify-end space-x-3">
                {{-- Cancel/Clear Button --}}
                @if($sqlFile)
                    <button 
                        type="button" 
                        wire:click="$set('sqlFile', null)"
                        class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 transition ease-in-out duration-150"
                    >
                        Batal
                    </button>
                @endif
                
                {{-- Restore Button - Disabled during upload or restore --}}
                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-50 disabled:cursor-not-allowed transition ease-in-out duration-150" 
                    wire:loading.attr="disabled" 
                    wire:target="sqlFile,restoreDatabase"
                    @disabled(!$sqlFile)
                >
                    <span wire:loading.remove wire:target="restoreDatabase">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Restore Database
                    </span>
                    <span wire:loading wire:target="restoreDatabase" class="flex items-center">
                        <svg class="animate-spin h-4 w-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Me-restore...
                    </span>
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
