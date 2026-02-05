<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-2xl mx-auto bg-white dark:bg-gray-700 shadow-lg rounded-lg p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Detail Transaksi</h1>
                <p class="text-gray-500 dark:text-gray-400">#{{ $transaction->invoice_number }}</p>
            </div>
            <div class="text-right">
                <p class="text-gray-500 dark:text-gray-400">{{ $transaction->created_at->format('d/m/Y, H:i') }}</p>
                <span class="mt-1 px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                    {{ $transaction->payment_status == 'paid' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-100' }}">
                    {{ ucfirst($transaction->payment_status) }}
                </span>
            </div>
        </div>

        <!-- Customer & Cashier Info -->
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300">PELANGGAN</h2>
                <p class="text-gray-900 dark:text-white">{{ $transaction->customer->name ?? 'Walk-in Customer' }}</p>
                @if($transaction->customer)
                <p class="text-gray-500 dark:text-gray-400 text-sm">{{ $transaction->customer->phone ?? '' }}</p>
                @endif
            </div>
            <div class="text-right">
                <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-300">KASIR</h2>
                <p class="text-gray-900 dark:text-white">{{ $transaction->user->name }}</p>
            </div>
        </div>

        <!-- Items -->
        <div class="border-t border-b border-gray-200 dark:border-gray-600 py-4 mb-8">
            <div class="hidden md:grid md:grid-cols-4 gap-4 font-semibold text-gray-600 dark:text-gray-300 mb-2">
                <div class="col-span-2">Item</div>
                <div class="text-right">Harga</div>
                <div class="text-right">Subtotal</div>
            </div>
            <div class="space-y-4">
                @foreach($transaction->transactionDetails as $item)
                <div class="grid grid-cols-3 md:grid-cols-4 gap-4 items-center">
                    <div class="col-span-2">
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $item->quantity }} x Rp {{ number_format($item->price, 0) }}</p>
                    </div>
                    <div class="hidden md:block text-right text-gray-700 dark:text-gray-200">Rp {{ number_format($item->price, 0) }}</div>
                    <div class="text-right font-semibold text-gray-900 dark:text-white">Rp {{ number_format($item->quantity * $item->price, 0) }}</div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Totals -->
        <div class="space-y-2 mb-8">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-300">Subtotal</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($transaction->total_price, 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-300">Pajak</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp 0</span>
            </div>
            <div class="flex justify-between text-xl font-bold pt-2 border-t border-gray-200 dark:border-gray-600">
                <span class="text-gray-900 dark:text-white">Total</span>
                <span class="text-gray-900 dark:text-white">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-300">Bayar</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($transaction->amount_paid, 0) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-300">Kembali</span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($transaction->change, 0) }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col space-y-3 mt-8 pt-6 border-t border-gray-200 dark:border-gray-600 md:flex-row md:justify-end md:space-x-3 md:space-y-0">
            {{-- Print Buttons --}}
            @can('access-sales')
            <a href="{{ route('transactions.print-receipt', $transaction->id) }}" target="_blank" class="w-full text-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 md:w-auto">Cetak Struk</a>
            <a href="{{ route('transactions.print-invoice', $transaction->id) }}" target="_blank" class="w-full text-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 md:w-auto">Cetak Invoice</a>
            @endcan

            {{-- Separator --}}
            <hr class="border-gray-300 dark:border-gray-600 my-2 w-full md:w-px md:h-auto md:my-0 md:mx-3">

            {{-- Edit/Delete Buttons --}}
            @can('delete-purchase')
            <a href="{{ route('transactions.edit', $transaction->id) }}" class="w-full text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 md:w-auto">Edit</a>
                @can('manage-users')
                <button wire:click="deleteTransaction" wire:confirm="Yakin ingin menghapus transaksi ini?" class="w-full text-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 dark:bg-red-500 dark:hover:bg-red-600 md:w-auto">Hapus</button>
                @endcan
            @endcan
        </div>
    </div>
</div>
