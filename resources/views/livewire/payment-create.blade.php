<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="max-w-3xl mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Catat Pembayaran</h2>

        <!-- Invoice Info -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Informasi Invoice</h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">No. Invoice</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $transaction->invoice_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Pelanggan</p>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $transaction->customer->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Tagihan</p>
                    <p class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($transaction->grand_total, 0) }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sudah Dibayar</p>
                    <p class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format($transaction->amount_paid, 0) }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Sisa Tagihan</p>
                    <p class="font-bold text-2xl text-red-600 dark:text-red-400">Rp {{ number_format($remaining_amount, 0) }}</p>
                </div>
            </div>
        </div>

        <!-- Payment History -->
        @if($transaction->payments->count() > 0)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Riwayat Pembayaran</h3>
            <div class="space-y-2">
                @foreach($transaction->payments as $payment)
                <div class="flex justify-between items-center p-3 bg-gray-50 dark:bg-gray-800 rounded">
                    <div>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d/m/Y') }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ ucfirst($payment->payment_method) }}</p>
                    </div>
                    <p class="font-bold text-green-600 dark:text-green-400">Rp {{ number_format($payment->amount, 0) }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Payment Form -->
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6">
            <h3 class="text-xl font-semibold mb-4 text-gray-800 dark:text-gray-100 border-b pb-2">Input Pembayaran Baru</h3>
            <div class="space-y-4">
                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Pembayaran</label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" id="amount" wire:model.live="amount" step="0.01" min="0.01" max="{{ $remaining_amount }}"
                               class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    </div>
                    @error('amount') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Metode Pembayaran</label>
                    <select id="payment_method" wire:model="payment_method" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                        <option value="cash">Tunai</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="giro">Giro</option>
                        <option value="other">Lainnya</option>
                    </select>
                    @error('payment_method') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="payment_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal Pembayaran</label>
                    <input type="date" id="payment_date" wire:model="payment_date" max="{{ now()->format('Y-m-d') }}"
                           class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600">
                    @error('payment_date') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Catatan (Opsional)</label>
                    <textarea id="notes" wire:model="notes" rows="3" maxlength="500"
                              class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600"
                              placeholder="Catatan tambahan..."></textarea>
                    @error('notes') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2">
            <a href="{{ route('accounts-receivable.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500 dark:hover:bg-gray-500 mt-4 sm:mt-0">Batal</a>
            <button type="button" wire:click="savePayment()" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600">
                Simpan Pembayaran
            </button>
        </div>
    </div>
</div>
