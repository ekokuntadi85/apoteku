<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Pusat Laporan Penjualan</h2>

    {{-- Tab Navigation --}}
    <div class="mb-4 border-b border-gray-200 dark:border-gray-700">
        <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" id="myTab" role="tablist">
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'summary' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" 
                        wire:click.prevent="$set('activeTab', 'summary')" 
                        type="button" role="tab">Ringkasan</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'item' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" 
                        wire:click.prevent="$set('activeTab', 'item')" 
                        type="button" role="tab">Per Item</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'detailed' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" 
                        wire:click.prevent="$set('activeTab', 'detailed')" 
                        type="button" role="tab">Laporan Rinci</button>
            </li>
            <li class="mr-2" role="presentation">
                <button class="inline-block p-4 border-b-2 rounded-t-lg {{ $activeTab === 'top-selling' ? 'border-blue-500 text-blue-600 dark:border-blue-400 dark:text-blue-400' : 'border-transparent hover:text-gray-600 hover:border-gray-300 dark:hover:text-gray-300' }}" 
                        wire:click.prevent="$set('activeTab', 'top-selling')" 
                        type="button" role="tab">Produk Terlaris</button>
            </li>
        </ul>
    </div>

    {{-- Tab Content --}}
    <div>
        @if ($activeTab === 'summary')
            @livewire('summary-report')
        @elseif ($activeTab === 'item')
            @livewire('item-report')
        @elseif ($activeTab === 'detailed')
            @livewire('detailed-log-report')
        @elseif ($activeTab === 'top-selling')
            @livewire('top-selling-products-report')
        @endif
    </div>

</div>
