<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500">Manajemen Backup Database</h1>

    {{-- Success Message --}}
    @if($successMessage)
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => { show = false; @this.set('successMessage', ''); }, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 mb-6 rounded-lg shadow-md" 
             role="alert">
            <p class="font-bold">✅ Berhasil</p>
            <p>{{ $successMessage }}</p>
        </div>
    @endif

    {{-- Error Message --}}
    @if($errorMessage)
        <div x-data="{ show: true }" 
             x-show="show" 
             x-init="setTimeout(() => { show = false; @this.set('errorMessage', ''); }, 3000)"
             x-transition:leave="transition ease-in duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 text-red-700 dark:text-red-200 p-4 mb-6 rounded-lg shadow-md" 
             role="alert">
            <p class="font-bold">❌ Terjadi Kesalahan</p>
            <p>{{ $errorMessage }}</p>
        </div>
    @endif

    {{-- Progress Indicator --}}
    @if($isBackingUp && $uploadProgress)
        <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 p-4 mb-6 rounded-lg shadow-md">
            <div class="flex items-center">
                <svg class="animate-spin h-5 w-5 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-blue-700 dark:text-blue-300 font-medium">{{ $uploadProgress }}</span>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- Dropbox Settings --}}
        <div class="md:col-span-2 bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100 flex items-center">
                <svg class="w-6 h-6 mr-2 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M6 2L0 6l6 4 6-4-6-4zm12 0l-6 4 6 4 6-4-6-4zM0 14l6 4 6-4-6-4-6 4zm12 0l6 4 6-4-6-4-6 4zM6 18l6 4 6-4-6-4-6 4z"/>
                </svg>
                Pengaturan Dropbox
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">Auto-upload ke Dropbox</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Backup otomatis diupload ke Dropbox</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="dropboxEnabled" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                    <div>
                        <label class="font-semibold text-gray-700 dark:text-gray-300">Kompresi GZIP</label>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Kompres file backup (hemat 70-90%)</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model.live="compressionEnabled" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                    </label>
                </div>

                @if($dropboxEnabled)
                    <div class="space-y-3 pt-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Dropbox Access Token
                            </label>
                            <input type="password" wire:model="dropboxAccessToken" 
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:text-gray-200"
                                   placeholder="Masukkan Dropbox Access Token">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Dapatkan token di <a href="https://www.dropbox.com/developers/apps" target="_blank" class="text-blue-500 hover:underline">Dropbox Developer Console</a>
                            </p>
                        </div>
                        
                        <div class="flex gap-2">
                            <button wire:click="testDropboxConnection" 
                                    class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm font-medium transition">
                                Test Koneksi
                            </button>
                            <button wire:click="saveDropboxSettings" 
                                    class="flex-1 px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-md text-sm font-medium transition">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Actions --}}
        <div class="relative md:col-span-1 bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
            <h2 class="text-xl font-bold mb-4 text-gray-800 dark:text-gray-100">Aksi</h2>
            <button wire:click="performBackup" wire:loading.attr="disabled" class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:from-blue-600 hover:to-indigo-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition ease-in-out duration-150">
                <span wire:loading.remove wire:target="performBackup">
                    <x-heroicon-o-plus-circle class="w-5 h-5 mr-2"/>
                    Buat Backup Baru
                </span>
                <span wire:loading wire:target="performBackup" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Membuat Backup...
                </span>
            </button>
            @if(count($selectedBackups) > 0)
                <button wire:click="deleteSelectedBackups" wire:confirm="Anda yakin ingin menghapus backup yang dipilih?" class="w-full mt-4 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-500 to-rose-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:from-red-600 hover:to-rose-700 active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 disabled:opacity-50 transition ease-in-out duration-150">
                    <x-heroicon-o-trash class="w-5 h-5 mr-2"/>
                    Hapus {{ count($selectedBackups) }} item
                </button>
            @endif
        </div>
    </div>
    
    <div class="bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-6 border border-zinc-200/60 dark:border-zinc-700/60 backdrop-blur">
        <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Daftar Backup</h2>
        <div class="overflow-x-auto">
            <!-- Desktop Table View -->
            <div class="hidden md:block">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-left">
                                <input type="checkbox" wire:model.live="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900">
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Nama File</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Ukuran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($backups as $backup)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/50 transition-colors">
                                <td class="px-4 py-3">
                                    <input type="checkbox" wire:model.live="selectedBackups" value="{{ $backup['name'] }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900">
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">
                                    {{ $backup['name'] }}
                                    @if(str_ends_with($backup['name'], '.gz'))
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                            GZIP
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('d M Y H:i:s') }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button wire:click="downloadBackup('{{ $backup['name'] }}')" class="inline-flex items-center p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300 transition">
                                        <x-heroicon-o-arrow-down-tray class="w-5 h-5"/>
                                    </button>
                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')" wire:confirm="Anda yakin ingin menghapus backup ini?" class="inline-flex items-center p-2 rounded-full text-gray-500 hover:text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition">
                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Tidak ada backup yang ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Mobile Card View -->
            <div class="md:hidden space-y-4">
                @php
                    $gradientClasses = [
                        'bg-gradient-to-br from-blue-50/50 to-indigo-50/50 dark:from-blue-900/20 dark:to-indigo-900/20',
                        'bg-gradient-to-br from-green-50/50 to-emerald-50/50 dark:from-green-900/20 dark:to-emerald-900/20',
                        'bg-gradient-to-br from-yellow-50/50 to-amber-50/50 dark:from-yellow-900/20 dark:to-amber-900/20',
                        'bg-gradient-to-br from-pink-50/50 to-rose-50/50 dark:from-pink-900/20 dark:to-rose-900/20',
                        'bg-gradient-to-br from-purple-50/50 to-fuchsia-50/50 dark:from-purple-900/20 dark:to-fuchsia-900/20',
                    ];
                @endphp
                @forelse ($backups as $backup)
                    <div class="{{ $gradientClasses[$loop->index % count($gradientClasses)] }} p-4 rounded-lg shadow-sm border border-gray-200/60 dark:border-gray-700/60">
                        <div class="flex items-start">
                            <input type="checkbox" wire:model.live="selectedBackups" value="{{ $backup['name'] }}" class="mt-1 rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-900">
                            <div class="ml-4 flex-grow">
                                <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ $backup['name'] }}</h3>
                                <div class="mt-2 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                    <p><strong class="font-semibold">Ukuran:</strong> {{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</p>
                                    <p><strong class="font-semibold">Tanggal:</strong> {{ \Carbon\Carbon::createFromTimestamp($backup['last_modified'])->format('d M Y H:i:s') }}</p>
                                </div>
                                <div class="mt-4 flex justify-end space-x-2">
                                    <button wire:click="downloadBackup('{{ $backup['name'] }}')" class="inline-flex items-center p-2 rounded-full text-gray-500 hover:text-indigo-600 hover:bg-indigo-100 dark:hover:bg-indigo-900/20 dark:hover:text-indigo-300 transition">
                                        <x-heroicon-o-arrow-down-tray class="w-5 h-5"/>
                                    </button>
                                    <button wire:click="deleteBackup('{{ $backup['name'] }}')" wire:confirm="Anda yakin ingin menghapus backup ini?" class="inline-flex items-center p-2 rounded-full text-gray-500 hover:text-red-600 hover:bg-red-100 dark:hover:bg-red-900/20 dark:hover:text-red-400 transition">
                                        <x-heroicon-o-trash class="w-5 h-5"/>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <p>Tidak ada backup yang ditemukan.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
