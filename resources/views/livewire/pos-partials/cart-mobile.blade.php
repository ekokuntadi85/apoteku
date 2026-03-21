{{-- ╔══════════════════════════════════════════════════════╗
     ║  CART PANEL — MOBILE ONLY (Tab Pesanan)            ║
     ║  File: pos-partials/cart-mobile.blade.php          ║
     ║  Visible: x-show="mobileTab === 'cart'" + md:hidden ║
     ║                                                    ║
     ║  Layout: flex-col, h-full                         ║
     ║  - Header  (shrink-0)                             ║
     ║  - Scroller Container (flex-1 overflow-y-auto)     ║
     ║    - Items List   (shrink-0)                       ║
     ║    - Payment Area (shrink-0)                       ║
     ╚══════════════════════════════════════════════════╝ --}}
<div class="flex flex-col h-full md:hidden bg-white dark:bg-gray-800 overflow-hidden">

    {{-- 1. Header (Tetap di atas) --}}
    <div class="p-3 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center border-b border-gray-200 dark:border-gray-700 shrink-0">
        <h2 class="font-bold text-gray-700 dark:text-gray-200 text-sm">Pesanan</h2>
        <span class="text-xs font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">{{ count($cart_items) }}</span>
    </div>

    {{-- 2. Scroller Container (Satu area scroll untuk Items + Payment) 
         Ini memastikan Tombol BAYAR bisa di-scroll ke atas jika tertutup keyboard atau layar sempit --}}
    <div class="flex-1 overflow-y-auto scrollbar-none">
        
        {{-- List Item --}}
        <div class="p-2">
            @include('livewire.pos-partials.cart-items')
        </div>

        {{-- Payment Section (Menempel di bawah list item) --}}
        <div class="bg-gray-50 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-4 pb-24 shadow-inner">
            @include('livewire.pos-partials.cart-payment')
        </div>

    </div>

</div>
