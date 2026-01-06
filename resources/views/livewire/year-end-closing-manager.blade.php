<div class="min-h-screen bg-gray-50 dark:bg-gray-900 p-6">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">🔒 Tutup Tahun Buku</h1>
            <p class="text-gray-600 dark:text-gray-400">Proses penutupan buku tahunan dan reset data transaksi</p>
        </div>

        {{-- Warning Alert --}}
        <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-600 p-6 mb-6 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-semibold text-red-800 dark:text-red-200">⚠️ PERINGATAN PENTING</h3>
                    <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                        <p class="font-semibold">Proses ini akan MENGHAPUS PERMANEN:</p>
                        <ul class="list-disc list-inside ml-2 mt-2 space-y-1">
                            <li>Semua transaksi penjualan</li>
                            <li>Semua pembelian</li>
                            <li>Semua jurnal akuntansi</li>
                            <li>Semua biaya operasional</li>
                        </ul>
                        <p class="mt-3 font-semibold text-green-700 dark:text-green-300">✅ Database akan di-backup otomatis</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Success Message --}}
        @if($successMessage)
            <div x-data="{ show: true }" 
                 x-show="show" 
                 x-init="setTimeout(() => show = false, 10000)"
                 class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 p-4 mb-6 rounded-lg">
                <p class="font-bold text-green-800 dark:text-green-200">{{ $successMessage }}</p>
            </div>
        @endif

        {{-- Error Message --}}
        @if($errorMessage)
            <div x-data="{ show: true }" 
                 x-show="show" 
                 class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 p-4 mb-6 rounded-lg">
                <p class="font-bold text-red-800 dark:text-red-200">{{ $errorMessage }}</p>
            </div>
        @endif

        {{-- Processing Indicator --}}
        @if($processing)
            <div class="bg-blue-50 dark:bg-blue-900/30 border-l-4 border-blue-500 p-6 mb-6 rounded-lg">
                <div class="flex items-center">
                    <svg class="animate-spin h-8 w-8 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <div>
                        <p class="text-lg font-bold text-blue-800 dark:text-blue-200">Proses Tutup Tahun Sedang Berjalan...</p>
                        <p class="text-sm text-blue-600 dark:text-blue-300 mt-1">Mohon tunggu, proses ini membutuhkan waktu 1-3 menit</p>
                        <div class="mt-3 space-y-1 text-sm">
                            <p>⏳ Backup database...</p>
                            <p>⏳ Membuat saldo awal...</p>
                            <p>⏳ Menghapus data lama...</p>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-blue-200 dark:bg-blue-800 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Proses Tutup Tahun</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Pilih Tahun yang Akan Ditutup
                    </label>
                    <select wire:model="closingYear" 
                            class="w-full md:w-64 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-gray-200 text-lg font-semibold">
                        @foreach($this->yearOptions as $year => $label)
                            <option value="{{ $year }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        ℹ️ Hanya tahun lalu yang dapat ditutup
                    </p>
                </div>

                <div class="pt-4">
                    <button 
                        wire:click="showConfirmation"
                        type="button"
                        class="inline-flex items-center px-8 py-4 bg-red-600 hover:bg-red-700 border border-transparent rounded-lg font-bold text-white text-lg uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-all duration-150 shadow-lg hover:shadow-xl transform hover:scale-105 active:scale-95">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Proses Tutup Tahun
                    </button>
                </div>
            </div>
        </div>

        {{-- History --}}
        @if(count($this->closingHistory) > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">📜 Riwayat Tutup Tahun</h2>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Tahun</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-bold text-gray-700 dark:text-gray-300 uppercase">Data Dihapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($this->closingHistory as $history)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                        {{ $history->closing_year }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ $history->closed_at ? $history->closed_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                        {{ optional($history->user)->name ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($history->status === 'completed')
                                            <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full bg-green-100 text-green-800">Selesai</span>
                                        @elseif($history->status === 'processing')
                                            <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full bg-yellow-100 text-yellow-800">Proses</span>
                                        @else
                                            <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full bg-red-100 text-red-800">Gagal</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                        <div class="space-y-1">
                                            <div>TRX: {{ $history->total_transactions_deleted ?? 0 }}</div>
                                            <div>PUR: {{ $history->total_purchases_deleted ?? 0 }}</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" style="z-index: 9999;">
            <div class="flex items-center justify-center min-h-screen px-4">
                {{-- Backdrop --}}
                <div class="fixed inset-0 bg-gray-900 bg-opacity-75" wire:click="closeModal" style="z-index: 9998;"></div>
                
                {{-- Modal Content --}}
                <div class="relative bg-white dark:bg-gray-800 rounded-lg max-w-lg w-full p-6 shadow-2xl" style="z-index: 9999;">
                    <div class="flex items-start mb-4">
                        <div class="flex-shrink-0 h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                            <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        
                        <div class="ml-4 flex-1">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                ⚠️ Konfirmasi Tutup Tahun
                            </h3>
                            
                            <div class="mt-4 space-y-4">
                                <div class="bg-red-50 dark:bg-red-950 border-2 border-red-200 dark:border-red-800 rounded-lg p-4">
                                    <p class="text-sm font-bold text-red-800 dark:text-red-200 mb-2">
                                        Anda akan menutup tahun {{ $closingYear }}
                                    </p>
                                    <p class="text-xs text-red-700 dark:text-red-300">
                                        Proses ini TIDAK DAPAT DIBATALKAN setelah selesai
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">
                                        Ketik <span class="text-red-600">TUTUP TAHUN {{ $closingYear }}</span> untuk konfirmasi:
                                    </label>
                                    <input type="text" 
                                           wire:model.live="confirmationText" 
                                           placeholder="TUTUP TAHUN {{ $closingYear }}"
                                           autocomplete="off"
                                           class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 dark:bg-gray-700 dark:text-gray-200 font-mono text-lg">
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                        Case-insensitive (huruf besar/kecil bebas)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-6">
                        <button 
                            wire:click="executeClosing"
                            type="button"
                            class="relative flex-1 px-6 py-3 bg-red-600 text-white font-bold rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 uppercase transition-colors cursor-pointer">
                            🔒 Ya, Tutup Tahun
                        </button>
                        <button 
                            wire:click="closeModal"
                            type="button"
                            class="relative flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 uppercase transition-colors cursor-pointer">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
