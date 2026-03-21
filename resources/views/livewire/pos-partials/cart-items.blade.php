{{-- Partial: cart-items.blade.php
     Dipakai oleh:  pos-partials/cart-desktop.blade.php
                    pos-partials/cart-mobile.blade.php
     Variabel dari Livewire component: $cart_items
--}}
<div class="space-y-2">
    @forelse($cart_items as $index => $item)
        <div class="bg-white dark:bg-gray-700 rounded border border-gray-200 dark:border-gray-600 p-2 text-sm hover:border-emerald-400">
            <div class="flex justify-between items-start mb-1">
                <span class="font-semibold text-gray-800 dark:text-gray-200 line-clamp-1 w-4/5">{{ $item['product_name'] }}</span>
                <button wire:click="removeItem({{ $index }})" class="text-gray-400 hover:text-red-500 shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="flex items-center justify-between mt-1">
                <div class="flex items-center space-x-1">
                    <input type="number"
                           value="{{ $item['original_quantity_input'] }}"
                           wire:blur="updateItemQuantity({{ $index }}, $event.target.value)"
                           wire:keydown.enter="$event.target.blur()"
                           class="w-12 px-1 py-1 text-center font-bold border rounded bg-gray-50 dark:bg-gray-600 dark:text-white border-gray-300 dark:border-gray-500 focus:ring-1 focus:ring-emerald-500 text-sm">
                    @if(isset($item['available_units']) && count($item['available_units']) > 1)
                        <select wire:change="updateItemUnit({{ $index }}, $event.target.value)"
                                class="w-20 text-xs py-1 pl-1 pr-4 border-none bg-transparent focus:ring-0 cursor-pointer">
                            @foreach($item['available_units'] as $unit)
                                <option value="{{ $unit['id'] }}" @selected($unit['id'] == $item['product_unit_id'])>{{ $unit['name'] }}</option>
                            @endforeach
                        </select>
                    @else
                        <span class="text-xs text-gray-500 px-1">{{ $item['unit_name'] }}</span>
                    @endif
                </div>
                <div class="text-right">
                    @if(isset($item['is_member_price']) && $item['is_member_price'])
                        <div class="text-[10px] text-gray-400 line-through">
                            {{ number_format($item['regular_price'] * $item['original_quantity_input'], 0, ',', '.') }}
                        </div>
                        <div class="font-bold text-emerald-600 dark:text-emerald-400 flex items-center justify-end gap-1">
                            <span class="text-[10px] bg-amber-100 text-amber-700 px-1 rounded">MEMBER</span>
                            <span>{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        </div>
                    @else
                        <div class="font-bold text-gray-900 dark:text-white">
                            {{ number_format($item['subtotal'], 0, ',', '.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="flex flex-col items-center justify-center py-8 text-gray-400 text-xs">
            <p>Keranjang Kosong</p>
        </div>
    @endforelse
</div>
