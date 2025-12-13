<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="mb-6">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white">Detail Surat Pesanan: {{ $purchaseOrder->po_number }}</h2>
            
            <div class="flex flex-col sm:flex-row gap-2">
                <!-- Navigation -->
                <a href="{{ route('purchase-orders.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-gray-600 dark:hover:bg-gray-700 text-center">
                    Kembali
                </a>
                
                <!-- Primary Actions (Status-based) -->
                @if($purchaseOrder->status === 'draft')
                    <a href="{{ route('purchase-orders.edit', $purchaseOrder->id) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-yellow-600 dark:hover:bg-yellow-700 text-center">
                        Edit
                    </a>
                    <button wire:click="markAsSent" wire:confirm="Apakah Anda yakin ingin menandai SP ini sebagai Terkirim? Setelah ini SP tidak dapat diedit." class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700">
                        Kirim ke PBF
                    </button>
                @endif
                
                @if($purchaseOrder->status === 'sent')
                    <a href="{{ route('purchases.create', ['po_id' => $purchaseOrder->id]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-green-600 dark:hover:bg-green-700 text-center">
                        Proses ke Pembelian
                    </a>
                @endif

                @if($purchaseOrder->status === 'completed' && $purchaseOrder->purchase)
                    <a href="{{ route('purchases.show', $purchaseOrder->purchase->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-blue-600 dark:hover:bg-blue-700 text-center">
                        Lihat Pembelian
                    </a>
                @endif
                
                <!-- Output Actions -->
                <a href="{{ route('purchase-orders.print', $purchaseOrder->id) }}" target="_blank" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-indigo-600 dark:hover:bg-indigo-700 text-center">
                    Cetak PDF
                </a>
                
                <!-- Destructive Actions (Grouped in dropdown) -->
                <div x-data="{ open: false }" class="relative">
                    @if($purchaseOrder->status !== 'completed' || in_array($purchaseOrder->status, ['draft', 'cancelled']))
                        <button @click="open = !open" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline dark:bg-red-600 dark:hover:bg-red-700 flex items-center justify-center w-full sm:w-auto">
                            <span>Aksi Lainnya</span>
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 dark:bg-gray-700 ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                @if($purchaseOrder->status !== 'completed' && $purchaseOrder->status !== 'cancelled')
                                    <button wire:click="cancelOrder" wire:confirm="Apakah Anda yakin ingin membatalkan Surat Pesanan ini?" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600">
                                        Batalkan Pesanan
                                    </button>
                                @endif
                                @if(in_array($purchaseOrder->status, ['draft', 'cancelled']))
                                    <button wire:click="deleteOrder" wire:confirm="Apakah Anda yakin ingin menghapus Surat Pesanan ini? Tindakan ini tidak dapat dibatalkan." class="block w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900">
                                        Hapus Pesanan
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg mb-6">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Informasi Pesanan</h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-600">
            <dl>
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Supplier</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 sm:mt-0 sm:col-span-2">{{ $purchaseOrder->supplier->name }}</dd>
                </div>
                <div class="bg-white dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Pesanan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 sm:mt-0 sm:col-span-2">{{ \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d F Y') }}</dd>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 sm:mt-0 sm:col-span-2">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $purchaseOrder->status == 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-100' : 
                               ($purchaseOrder->status == 'sent' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-100' : 
                               ($purchaseOrder->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-100' : 
                               'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')) }}">
                            {{ ucfirst($purchaseOrder->status) }}
                        </span>
                    </dd>
                </div>
                <div class="bg-white dark:bg-gray-700 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Catatan</dt>
                    <dd class="mt-1 text-sm text-gray-900 dark:text-gray-200 sm:mt-0 sm:col-span-2">{{ $purchaseOrder->notes ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-700 shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Item Pesanan</h3>
        </div>
        <div class="border-t border-gray-200 dark:border-gray-600">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-600">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Produk</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Satuan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jml Pesan</th>
                        @if($purchaseOrder->status === 'completed' && $purchaseOrder->purchase)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jml Diterima</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-700 dark:divide-gray-600">
                    @foreach($purchaseOrder->details as $detail)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-200">{{ $detail->product->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $detail->productUnit->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $detail->quantity }}</td>
                        @if($purchaseOrder->status === 'completed' && $purchaseOrder->purchase)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                @if(isset($actualQuantities[$detail->id]))
                                    @php 
                                        $qty = $actualQuantities[$detail->id];
                                        // Display decimals only if necessary
                                        echo fmod($qty, 1) !== 0.00 ? number_format($qty, 2, ',', '.') : number_format($qty, 0, ',', '.');
                                    @endphp
                                @else
                                    -
                                @endif
                            </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
