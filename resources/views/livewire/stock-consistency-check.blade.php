<div class="container mx-auto p-4 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <h1 class="text-2xl font-bold mb-4">Pemeriksaan Integritas Stok</h1>

    <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-700">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Kontrol Stok</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
            Gunakan tombol di bawah ini untuk membandingkan stok yang tercatat di batch produk dengan riwayat pergerakan stok (kartu stok).
        </p>
        <button wire:click="checkStockConsistency" wire:loading.attr="disabled" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700">
            <span wire:loading.remove wire:target="checkStockConsistency">
                Periksa Kesalahan Stok
            </span>
            <span wire:loading wire:target="checkStockConsistency">
                Memeriksa...
            </span>
        </button>
    </div>

    <div wire:loading.class.delay="opacity-50" wire:target="checkStockConsistency, fixStockInconsistencies">
        @if ($checkPerformed)
            @if (!empty($inconsistentProducts))
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700 mb-4">
                     <div class="p-4 bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 text-red-700 dark:text-red-200 dark:bg-red-800 dark:border-red-600 dark:text-red-200 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold">Ditemukan {{ count($inconsistentProducts) }} Ketidaksesuaian</h3>
                            <p>Stok di batch produk tidak cocok dengan catatan di kartu stok.</p>
                        </div>
                        <button wire:click="fixStockInconsistencies" wire:loading.attr="disabled" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            <span wire:loading.remove wire:target="fixStockInconsistencies">
                                Perbaiki Semua
                            </span>
                            <span wire:loading wire:target="fixStockInconsistencies">
                                Memperbaiki...
                            </span>
                        </button>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok (Batch)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok (Kartu Stok)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Selisih</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach ($inconsistentProducts as $product)
                                    @php
                                        $product = (object) $product;
                                        $batchStock = $product->product_table_stock ?? 0;
                                        $cardStock = $product->calculated_stock ?? 0;
                                        $difference = $cardStock - $batchStock;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-red-500 dark:text-red-400 font-semibold">{{ number_format($batchStock, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-green-500 dark:text-green-400 font-semibold">{{ number_format($cardStock, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-bold {{ $difference > 0 ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                                            {{ $difference > 0 ? '+' : '' }}{{ number_format($difference, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 mb-4 dark:bg-green-800 dark:border-green-600 dark:text-green-200" role="alert">
                    <p class="font-bold">Pemeriksaan Selesai</p>
                    <p>Tidak ditemukan adanya ketidaksesuaian stok. Semua data sudah konsisten.</p>
                </div>
            @endif

            {{-- Negative Stock Products Section --}}
            @if (!empty($negativeStockProducts))
                <div class="shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700 mb-4">
                    <div class="p-4 bg-orange-100 border-l-4 border-orange-500 text-orange-700 dark:bg-orange-800 dark:border-orange-600 dark:text-orange-200">
                        <h3 class="font-bold">Ditemukan {{ count($negativeStockProducts) }} Produk dengan Stok Negatif!</h3>
                        <p>Produk-produk berikut memiliki stok di bawah nol.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok (Batch)</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Stok (Kartu Stok)</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                                @foreach ($negativeStockProducts as $product)
                                    @php
                                        $product = (object) $product;
                                        $batchStock = $product->product_table_stock ?? 0;
                                        $cardStock = $product->calculated_stock ?? 0;
                                    @endphp
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-semibold {{ $batchStock < 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-900 dark:text-gray-200' }}">{{ number_format($batchStock, 0, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center font-semibold {{ $cardStock < 0 ? 'text-red-500 dark:text-red-400' : 'text-gray-900 dark:text-gray-200' }}">{{ number_format($cardStock, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center text-gray-500 dark:text-gray-400 p-10 bg-white dark:bg-gray-800 rounded-lg shadow-md border border-gray-200 dark:border-gray-700">
                <p>Klik tombol "Periksa Kesalahan Stok" untuk memulai analisis.</p>
            </div>
        @endif
    </div>
</div>