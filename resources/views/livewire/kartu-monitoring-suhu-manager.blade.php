<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200" x-data="{}" @open-new-tab.window="window.open($event.detail.url, '_blank')">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex flex-col-reverse md:flex-row md:justify-between md:items-center mb-4 space-y-4 md:space-y-0">
        <div class="relative w-full md:w-1/3">
            <input type="month" wire:model.live="selectedMonth" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>
        <button type="button" wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-blue-600 dark:hover:bg-blue-700 md:ml-4 mb-4">Tambah Data</button>
        <button type="button" wire:click="printCard()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full md:w-auto dark:bg-green-600 dark:hover:bg-green-700 md:ml-4 mb-4">Cetak Kartu</button>
    </div>

    <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 mb-4 border border-gray-200 dark:border-gray-600">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="text-center">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Rata-rata Suhu Ruangan</h4>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($averageSuhuRuangan, 2) }} &deg;C</p>
            </div>
            <div class="text-center">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white">Rata-rata Suhu Pendingin</h4>
                <p class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ number_format($averageSuhuPendingin, 2) }} &deg;C</p>
            </div>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Suhu Ruangan</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Suhu Pendingin</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Waktu Pengukuran</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($kartuMonitoringSuhus as $kartu)
                    <tr class="dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($kartu->waktu_pengukuran)->format('d') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $kartu->suhu_ruangan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $kartu->suhu_pendingin }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($kartu->waktu_pengukuran)->format('H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ $kartu->user?->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button wire:click="edit({{ $kartu->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full mr-2 dark:bg-green-600 dark:hover:bg-green-700">Edit</button>
                            <button wire:click="delete({{ $kartu->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus data ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($kartuMonitoringSuhus as $kartu)
        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Tanggal : {{ \Carbon\Carbon::parse($kartu->waktu_pengukuran)->format('d') }}</h3>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Waktu : {{ \Carbon\Carbon::parse($kartu->waktu_pengukuran)->format('H:i') }}</p>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Suhu Ruangan : {{ $kartu->suhu_ruangan }}</p>
                    <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Suhu Pendingin : {{ $kartu->suhu_pendingin }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">User : {{ $kartu->user?->name }}</p>
                </div>
                <div class="flex space-x-2">
                    <button wire:click="edit({{ $kartu->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-green-600 dark:hover:bg-green-700">Edit</button>
                    <button wire:click="delete({{ $kartu->id }})" onclick="confirm('Apakah Anda yakin ingin menghapus data ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-3 rounded-full text-xs dark:bg-red-600 dark:hover:bg-red-700">Hapus</button>
                </div>
            </div>
        </div>
        @empty
        <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada data ditemukan.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $kartuMonitoringSuhus->links() }}
    </div>

    @if($showModal)
        @include('livewire.kartu-monitoring-suhu-modal')
    @endif
</div>
