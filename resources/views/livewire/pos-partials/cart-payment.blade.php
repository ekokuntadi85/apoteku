{{-- Partials: cart-payment.blade.php
     Area Pembayaran: Total, Smart Cash, Uang Pas, Input, Kembali, Cetak Struk, BAYAR
--}}
<div class="mb-4">
    <div class="flex justify-between items-center mb-1">
        <span class="text-gray-500 text-xs font-semibold uppercase tracking-wider">Total Pesanan</span>
        <span class="text-xs text-gray-400 font-mono">{{ count($cart_items) }} Items</span>
    </div>
    <div class="p-4 bg-emerald-50 dark:bg-emerald-900/20 rounded-xl border border-emerald-100 dark:border-emerald-800/50 shadow-sm transition-all text-center">
        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 leading-none">
            Rp {{ number_format($total_price, 0, ',', '.') }}
        </span>
    </div>
</div>

<div class="space-y-3">
    {{-- Smart Cash Options --}}
    @if(count($this->smart_cash_options) > 0)
    <div class="grid grid-cols-4 gap-1.5 px-0.5">
        @foreach($this->smart_cash_options as $option)
            <button
                wire:click="$set('amount_paid', {{ $option }})"
                class="bg-white dark:bg-gray-800 border-2 border-emerald-100 dark:border-emerald-800 text-emerald-700 dark:text-emerald-400 py-2 rounded-lg text-xs font-black shadow-sm hover:border-emerald-500 hover:bg-emerald-50 transition-all duration-200 active:scale-95"
            >
                {{ number_format($option/1000, 0) }}k
            </button>
        @endforeach
    </div>
    @endif

    {{-- Uang Pas & Reset --}}
    <div class="grid grid-cols-2 gap-2">
        <button wire:click="$set('amount_paid', {{ $total_price }})"
                class="bg-blue-600 dark:bg-blue-700 text-white border-none py-2.5 rounded-xl text-xs font-bold shadow-md hover:bg-blue-500 transition-all active:scale-95 uppercase tracking-wide">
            Uang Pas
        </button>
        <button wire:click="$set('amount_paid', '')"
                class="bg-white dark:bg-gray-800 text-gray-400 border border-gray-200 dark:border-gray-700 py-2.5 rounded-xl text-xs font-bold shadow-sm hover:text-red-500 hover:border-red-500 transition-all active:scale-95">
            Reset
        </button>
    </div>

    {{-- Input Jumlah Bayar --}}
    <div class="relative group">
        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
            <span class="text-emerald-500 font-black text-sm">Rp</span>
        </div>
        <input type="number"
               id="{{ $idPrefix ?? '' }}amount-paid-input"
               wire:model.live="amount_paid"
               wire:keydown.enter="checkout"
               placeholder="Jumlah Bayar"
               class="w-full pl-10 pr-4 py-3 text-xl font-black text-right rounded-2xl border-2 border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:white focus:border-emerald-500 focus:ring-0 transition-all shadow-inner @error('amount_paid') border-red-400 @enderror">
    </div>
    @error('amount_paid') <span class="text-[10px] text-red-500 font-bold text-right block pr-2 mt-1">{{ $message }}</span> @enderror

    {{-- Kembalian --}}
    @if($amount_paid >= $total_price && $total_price > 0)
        <div class="flex justify-between items-center bg-gray-900 dark:bg-black p-4 rounded-2xl shadow-xl transition-all animate-in fade-in slide-in-from-bottom-2 duration-300">
            <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kembali</span>
            <span class="text-2xl font-black text-amber-400">{{ number_format($change, 0, ',', '.') }}</span>
        </div>
    @endif

    {{-- Cetak Struk Checkbox --}}
    <div class="flex items-center justify-center py-1">
        <label class="group flex items-center space-x-3 cursor-pointer select-none">
            <div class="relative">
                <input type="checkbox" wire:model="print_receipt" class="peer sr-only">
                <div class="w-10 h-5 bg-gray-200 dark:bg-gray-700 rounded-full transition-all peer-checked:bg-emerald-500"></div>
                <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5 shadow-sm"></div>
            </div>
            <span class="text-xs font-bold text-gray-500 dark:text-gray-400 group-hover:text-emerald-500 transition-colors uppercase tracking-tight">Cetak Struk Otomatis</span>
        </label>
    </div>

    {{-- Tombol BAYAR --}}
    <button wire:click="checkout"
            wire:loading.attr="disabled"
            :disabled="{{ count($cart_items) === 0 ? 'true' : 'false' }}"
            class="w-full bg-emerald-500 dark:bg-emerald-600 hover:bg-emerald-400 dark:hover:bg-emerald-500 text-white font-black py-4 rounded-2xl shadow-lg hover:shadow-emerald-500/30 transition-all duration-300 active:scale-[0.98] flex justify-center items-center relative gap-3 text-lg tracking-widest uppercase disabled:opacity-30 disabled:grayscale">
        <span wire:loading.remove>
            <div class="flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                BAYAR
            </div>
        </span>
        <span wire:loading wire:target="checkout" class="text-base flex items-center gap-3">
             <svg class="animate-spin h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
             MEMPROSES...
        </span>
    </button>
</div>
