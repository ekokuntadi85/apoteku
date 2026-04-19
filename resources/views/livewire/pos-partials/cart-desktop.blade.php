{{-- ╔══════════════════════════════════════════════════════╗
     ║  CART PANEL — DESKTOP ONLY                         ║
     ║  File: pos-partials/cart-desktop.blade.php         ║
     ╚══════════════════════════════════════════════════╝ --}}
<aside class="hidden md:flex md:flex-col justify-between
              w-80 lg:w-96 shrink-0
              bg-white dark:bg-gray-800
              border-l border-gray-200 dark:border-gray-700
              shadow-xl
              h-full
              overflow-y-auto">

    <div>
        {{-- 1. Header (fixed height) --}}
        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 shrink-0">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 text-sm">Pesanan</h2>
            <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ count($cart_items) }}</span>
        </div>

        {{-- 2. Items List (scrollable, fixed height to max 3 items) --}}
        <div class="h-[270px] overflow-y-auto p-2 scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600">
            @include('livewire.pos-partials.cart-items')
        </div>
    </div>

    {{-- 3. Payment Area (sticky at bottom) --}}
    <div class="shrink-0 bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-3 shadow-inner">
        @include('livewire.pos-partials.cart-payment', ['idPrefix' => 'desktop-'])
    </div>

</aside>
