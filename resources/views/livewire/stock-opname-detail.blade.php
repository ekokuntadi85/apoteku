<div class="container mx-auto px-4 py-6">
    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Stock Opname Detail #{{ $opname->id }}</h1>
            <p class="text-gray-600 mt-1">{{ $opname->opname_date->format('d F Y') }}</p>
        </div>
        <div>
            @if($opname->isDraft())
                <span class="px-4 py-2 bg-yellow-100 text-yellow-800 rounded-full font-semibold">
                    DRAFT
                </span>
            @else
                <span class="px-4 py-2 bg-green-100 text-green-800 rounded-full font-semibold flex items-center">
                    <svg class="w-5 h-5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                    FINALIZED
                </span>
            @endif
        </div>
    </div>

    {{-- Notes --}}
    @if($opname->notes)
    <div class="mb-6 p-4 bg-blue-50 rounded-lg">
        <p class="text-gray-700"><strong>Catatan:</strong> {{ $opname->notes }}</p>
    </div>
    @endif

    {{-- Warning for items without price --}}
    @if($hasWarnings && count($itemsWithoutPrice) > 0)
    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">⚠️ PERINGATAN: Item Tanpa Harga Beli!</h3>
                <div class="mt-2 text-sm text-red-700">
                    <p class="font-semibold mb-2">Item berikut tidak memiliki harga beli (Rp 0):</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($itemsWithoutPrice as $item)
                        <li><strong>{{ $item['name'] }}</strong> - Batch: {{ $item['batch'] }} (Selisih: {{ $item['difference'] > 0 ? '+' : '' }}{{ $item['difference'] }} unit)</li>
                        @endforeach
                    </ul>
                    <p class="mt-3 font-semibold bg-red-100 p-2 rounded">
                        🚫 Tidak dapat difinalisasi sampai harga beli diupdate!
                    </p>
                    <p class="mt-2 text-xs">
                        Silakan update harga beli di master Product Batch, lalu refresh halaman ini.
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm mb-2">Total Items</div>
            <div class="text-3xl font-bold text-gray-800">{{ $opname->details->count() }}</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm mb-2">System Stock</div>
            <div class="text-3xl font-bold text-gray-800">{{ number_format($opname->details->sum('system_stock')) }} unit</div>
        </div>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-gray-500 text-sm mb-2">Physical Stock</div>
            <div class="text-3xl font-bold {{ $opname->details->sum('physical_stock') < $opname->details->sum('system_stock') ? 'text-red-600' : ($opname->details->sum('physical_stock') > $opname->details->sum('system_stock') ? 'text-green-600' : 'text-gray-800') }}">
                {{ number_format($opname->details->sum('physical_stock')) }} unit
            </div>
        </div>
    </div>

    {{-- Adjustment Summary --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg shadow-lg p-6 mb-6 border-l-4 {{ $totalAdjustmentValue < 0 ? 'border-red-500' : ($totalAdjustmentValue > 0 ? 'border-green-500' : 'border-gray-500') }}">
        <h2 class="text-xl font-bold mb-3 text-gray-800">Ringkasan Penyesuaian</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600 mb-1">Selisih Nilai</p>
                <p class="text-3xl font-bold {{ $totalAdjustmentValue < 0 ? 'text-red-600' : ($totalAdjustmentValue > 0 ? 'text-green-600' : 'text-gray-700') }}">
                    Rp {{ number_format(abs($totalAdjustmentValue), 0, ',', '.') }}
                </p>
            </div>
            
            <div>
                <p class="text-sm text-gray-600 mb-1">Type</p>
                <p class="text-xl font-semibold {{ $totalAdjustmentValue < 0 ? 'text-red-600' : ($totalAdjustmentValue > 0 ? 'text-green-600' : 'text-gray-700') }}">
                    {{ $adjustmentType }}
                    @if($adjustmentType == 'LOSS')
                        <span class="text-sm font-normal">(Kehilangan)</span>
                    @elseif($adjustmentType == 'GAIN')
                        <span class="text-sm font-normal">(Kelebihan)</span>
                    @endif
                </p>
                
                @if($totalAdjustmentValue != 0)
                    <p class="text-sm text-gray-600 mt-2">
                        @if($totalAdjustmentValue < 0)
                            💸 Akan dicatat sebagai <strong>Biaya Selisih Stock</strong>
                        @else
                            💰 Akan dicatat sebagai <strong>Pendapatan Lain-lain</strong>
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Journal Info (if finalized) --}}
    @if($opname->isFinalized() && $opname->journal_entry_id)
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div>
                    <h3 class="font-bold text-gray-800">Jurnal Keuangan</h3>
                    <p class="text-sm text-gray-600">Reference: OP-{{ $opname->id }}</p>
                    <p class="text-sm text-gray-600">Journal Entry ID: #{{ $opname->journal_entry_id }}</p>
                </div>
            </div>
            <a href="{{ route('finance.journal-sync') }}?search=OP-{{ $opname->id }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Lihat Jurnal →
            </a>
        </div>
    </div>
    @endif

    {{-- Data Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock System</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Stock Fisik</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Selisih</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 {{ $opname->isFinalized() ? 'opacity-75' : '' }}">
                    @foreach($opname->details as $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $detail->productBatch->product->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $detail->productBatch->batch_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                            {{ number_format($detail->system_stock) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-700">
                            {{ number_format($detail->physical_stock) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $detail->difference < 0 ? 'text-red-600' : ($detail->difference > 0 ? 'text-green-600' : 'text-gray-700') }}">
                            {{ $detail->difference > 0 ? '+' : '' }}{{ number_format($detail->difference) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $detail->difference * $detail->productBatch->purchase_price < 0 ? 'text-red-600' : ($detail->difference * $detail->productBatch->purchase_price > 0 ? 'text-green-600' : 'text-gray-700') }}">
                            Rp {{ number_format(abs($detail->difference * $detail->productBatch->purchase_price), 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="flex justify-between items-center">
        <a href="{{ route('stock-opname.index') }}" class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
            ← Kembali
        </a>
        
        @if($opname->isDraft())
            <div>
                <button 
                    wire:click="openFinalizeModal" 
                    @if($hasWarnings) disabled @endif
                    class="px-6 py-3 {{ $hasWarnings ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700' }} text-white rounded-lg transition font-semibold shadow-lg">
                    @if($hasWarnings)
                        🚫 Tidak Dapat Difinalisasi
                    @else
                        Finalize Stock Opname
                    @endif
                </button>
                @if($hasWarnings)
                    <p class="text-sm text-red-600 mt-2 font-semibold">
                        ⚠️ Ada item tanpa harga beli! Scroll ke atas untuk melihat detail.
                    </p>
                @else
                    <p class="text-sm text-yellow-600 mt-2">
                        ⚠️ Setelah difinalisasi, jurnal keuangan akan otomatis dibuat dan data tidak dapat diubah lagi
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- Finalize Confirmation Modal --}}
    @if($showFinalizeModal)
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" wire:click="$set('showFinalizeModal', false)">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-lg bg-white" wire:click.stop>
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-2xl font-bold text-gray-900">Finalisasi Stock Opname?</h3>
                    <button wire:click="$set('showFinalizeModal', false)" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700 font-semibold">Perhatian!</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 mb-6">
                    <p class="text-gray-700">Setelah difinalisasi:</p>
                    <ul class="list-none space-y-2 ml-4">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">Jurnal keuangan akan otomatis dibuat</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-gray-700">Data tidak dapat diubah lagi</span>
                        </li>
                    </ul>

                    <div class="bg-gray-50 p-4 rounded mt-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600">Selisih Nilai:</p>
                                <p class="text-2xl font-bold {{ $totalAdjustmentValue < 0 ? 'text-red-600' : ($totalAdjustmentValue > 0 ? 'text-green-600' : 'text-gray-700') }}">
                                    {{ $totalAdjustmentValue < 0 ? '-' : '+' }}Rp {{ number_format(abs($totalAdjustmentValue), 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600">Type:</p>
                                <p class="text-xl font-semibold {{ $totalAdjustmentValue < 0 ? 'text-red-600' : ($totalAdjustmentValue > 0 ? 'text-green-600' : 'text-gray-700') }}">
                                    {{ $adjustmentType }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    @if($totalAdjustmentValue < 0)
                                        (Biaya expense)
                                    @elseif($totalAdjustmentValue > 0)
                                        (Pendapatan lain-lain)
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button wire:click="$set('showFinalizeModal', false)" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button wire:click="confirmFinalize" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold">
                        Ya, Finalize
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
