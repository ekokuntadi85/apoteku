<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
        <div class="flex flex-col md:flex-row justify-between md:items-start mb-4">
            <div>
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Pembelian #{{ $purchase->invoice_number }}</h2>
                <p class="text-md text-gray-500 dark:text-gray-400">Dari: {{ $purchase->supplier->name }}</p>
                <p class="text-lg font-semibold text-gray-800 dark:text-gray-100 mt-2">Total: Rp {{ number_format($purchase->total_price, 0) }}</p>
                @php
                    $status = strtolower($purchase->payment_status);
                    $badge = match($status) {
                        'paid','lunas' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 ring-1 ring-emerald-200/50',
                        'unpaid','belum lunas' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 ring-1 ring-rose-200/50',
                        'partial','sebagian' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 ring-1 ring-amber-200/50',
                        default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-zinc-200/50',
                    };
                @endphp
                <span class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold capitalize {{ $badge }}">
                    <span class="w-2 h-2 rounded-full @if(in_array($status,['paid','lunas'])) bg-emerald-500 @elseif(in_array($status,['unpaid','belum lunas'])) bg-rose-500 @elseif(in_array($status,['partial','sebagian'])) bg-amber-500 @else bg-zinc-400 @endif"></span>
                    {{ ucfirst($purchase->payment_status) }}
                </span>
            </div>
            <div class="flex space-x-2 mt-4 md:mt-0">
                @can('delete-purchase')
                <a href="{{ route('purchases.edit', $purchase->id) }}" class="w-full md:w-auto text-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500">Edit</a>
                @can('manage-users')
                <button wire:click="deletePurchase()" wire:confirm="Yakin hapus pembelian ini?" class="w-full md:w-auto text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600">Hapus</button>
                @endcan
                @endcan
                @can('delete-purchase')
                @if ($purchase->payment_status === 'unpaid')
                    <button 
                        x-data="{ 
                            async markPaid() {
                                if (confirm('Tandai lunas?')) {
                                    await $wire.markAsPaid();
                                }
                            }
                        }"
                        @click="markPaid()"
                        class="w-full md:w-auto px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600">
                        Tandai Lunas
                    </button>
                @endif
                @endcan
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-2 border-t border-b py-2 border-gray-200 dark:border-gray-600">
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Tanggal Pembelian</h4>
                <p class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Nomor Invoice</h4>
                <p class="text-gray-900 dark:text-white">{{ $purchase->invoice_number }}</p>
            </div>
            <div>
                <h4 class="font-semibold text-gray-600 dark:text-gray-300">Jatuh Tempo</h4>
                <p class="text-gray-900 dark:text-white">{{ $purchase->due_date ? \Carbon\Carbon::parse($purchase->due_date)->format('d/m/Y') : '-' }}</p>
            </div>
        </div>

        <div class="mt-4">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100">Item Pembelian</h3>
            <div class="space-y-4">
                @php
                    $gradientClasses = [
                        'bg-gradient-to-br from-blue-50/50 to-indigo-50/50 dark:from-blue-900/20 dark:to-indigo-900/20',
                        'bg-gradient-to-br from-green-50/50 to-emerald-50/50 dark:from-green-900/20 dark:to-emerald-900/20',
                        'bg-gradient-to-br from-yellow-50/50 to-amber-50/50 dark:from-yellow-900/20 dark:to-amber-900/20',
                        'bg-gradient-to-br from-pink-50/50 to-rose-50/50 dark:from-pink-900/20 dark:to-rose-900/20',
                        'bg-gradient-to-br from-purple-50/50 to-fuchsia-50/50 dark:from-purple-900/20 dark:to-fuchsia-900/20',
                    ];
                @endphp
                @forelse($purchase->productBatches as $item)
                    <div class="{{ $gradientClasses[$loop->index % count($gradientClasses)] }} p-4 rounded-lg shadow-sm border border-gray-200/60 dark:border-gray-700/60">
                        <div class="flex justify-between items-center mb-2">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                            <span class="text-sm text-gray-600 dark:text-gray-400">( {{ $item->batch_number ?: '-' }} )</span>
                        </div>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Tanggal Expire:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ \Carbon\Carbon::parse($item->expiration_date)->format('d/m/Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Harga Beli:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">Rp {{ number_format($item->display_purchase_price, 0) }} / {{ $item->display_unit_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Kuantitas Beli:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ rtrim(rtrim(number_format($item->original_input_quantity, 2), '0'), '.') }} {{ $item->display_unit_name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Sisa Stok Batch Ini:</span>
                                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ $item->stock }} {{ $item->product->baseUnit->name }}</span>
                            </div>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="font-semibold text-gray-800 dark:text-gray-100">Subtotal:</span>
                            <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($item->display_purchase_price * $item->original_input_quantity, 0) }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada item dalam pembelian ini.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
            <!-- Footer can be used for notes or other info in the future -->
        </div>
    </div>
</div>
