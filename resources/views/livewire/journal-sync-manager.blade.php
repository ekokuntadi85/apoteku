<div class="container mx-auto p-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Sinkronisasi Jurnal</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Kelola sinkronisasi data transaksi ke journal entries</p>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('message'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
    
    @if (session()->has('error'))
        <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
            {{ session('error') }}
        </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <!-- Data Source -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Data Sumber</h3>
            <div class="space-y-1">
                <div class="flex justify-between">
                    <span class="text-xs">Penjualan:</span>
                    <span class="text-sm font-bold">{{ number_format($stats['transactions']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Pembelian:</span>
                    <span class="text-sm font-bold">{{ number_format($stats['purchases']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Pengeluaran:</span>
                    <span class="text-sm font-bold">{{ number_format($stats['expenses']) }}</span>
                </div>
            </div>
        </div>

        <!-- Journal Entries -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Journal Entries</h3>
            <div class="space-y-1">
                <div class="flex justify-between">
                    <span class="text-xs">Sales:</span>
                    <span class="text-sm font-bold text-blue-600">{{ number_format($stats['sales_journals']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">COGS:</span>
                    <span class="text-sm font-bold text-purple-600">{{ number_format($stats['cogs_journals']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Purchase:</span>
                    <span class="text-sm font-bold text-green-600">{{ number_format($stats['purchase_journals']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs">Expense:</span>
                    <span class="text-sm font-bold text-red-600">{{ number_format($stats['expense_journals']) }}</span>
                </div>
            </div>
        </div>

        <!-- Total -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg shadow p-6 text-white">
            <h3 class="text-sm font-medium mb-2 opacity-90">Total Journals</h3>
            <div class="text-3xl font-bold">{{ number_format($stats['journal_entries']) }}</div>
            <div class="text-xs mt-2 opacity-75">dari ~{{ number_format($stats['expected_min'] * 2) }} expected</div>
        </div>

        <!-- Coverage -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Coverage</h3>
            <div class="text-3xl font-bold {{ $stats['coverage_percent'] >= 90 ? 'text-green-600' : 'text-yellow-600' }}">
                {{ $stats['coverage_percent'] }}%
            </div>
            <div class="text-xs text-gray-500 mt-2">
                @if($stats['coverage_percent'] >= 90)
                    ✅ Sangat Baik
                @elseif($stats['coverage_percent'] >= 70)
                    ⚠️ Perlu Sync
                @else
                    ❌ Perlu Sync Penuh
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-bold mb-4">Aksi Sinkronisasi</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Sync All -->
            <button 
                wire:click="syncAll" 
                wire:loading.attr="disabled"
                class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                <div wire:loading.remove wire:target="syncAll">
                    🔄 Sync Semua
                </div>
                <div wire:loading wire:target="syncAll">
                    ⏳ Syncing...
                </div>
            </button>

            <!-- Fix Missing COGS -->
            <button 
                wire:click="fixMissingCOGS" 
                wire:loading.attr="disabled"
                class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
                <div wire:loading.remove wire:target="fixMissingCOGS">
                    🔧 Fix Missing COGS
                </div>
                <div wire:loading wire:target="fixMissingCOGS">
                    ⏳ Fixing...
                </div>
            </button>

            <!-- Clear All -->
            <button 
                wire:click="clearAllJournals" 
                wire:confirm="Apakah Anda yakin ingin menghapus SEMUA journal entries? Ini tidak bisa di-undo!"
                class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-lg shadow-md transition"
            >
                🗑️ Hapus Semua Journals
            </button>
        </div>

        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            <p><strong>Sync Semua:</strong> Sinkronisasi semua transaksi, pembelian, dan pengeluaran ke journal entries</p>
            <p><strong>Fix Missing COGS:</strong> Perbaiki COGS yang hilang dengan fallback ke latest batch</p>
            <p><strong>Hapus Semua:</strong> Hapus semua journal entries (gunakan sebelum re-sync penuh)</p>
        </div>
    </div>

    <!-- Sync Log -->
    <div class="bg-gray-900 text-green-400 rounded-lg shadow p-6 font-mono text-sm">
        <h2 class="text-xl font-bold mb-4 text-white">Log Sinkronisasi</h2>
        
        @if($syncLog)
            <pre class="whitespace-pre-wrap">{{ $syncLog }}</pre>
        @else
            <p class="text-gray-500">Belum ada log. Klik tombol di atas untuk memulai sinkronisasi.</p>
        @endif
        
        <div class="mt-4">
            <button 
                wire:click="refreshStats" 
                class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded"
            >
                🔄 Refresh Stats
            </button>
        </div>
    </div>
</div>
