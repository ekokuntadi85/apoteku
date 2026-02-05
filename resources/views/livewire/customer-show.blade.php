<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex items-center gap-3 mb-4">
        <h2 class="text-2xl font-bold dark:text-gray-100">Detail Customer: {{ $customer->name }}</h2>
        @if($customer->is_member)
            <span class="bg-gradient-to-r from-amber-400 to-amber-500 text-white text-sm font-bold px-3 py-1 rounded-full shadow-sm">
                ⭐ MEMBER
            </span>
        @endif
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4 dark:bg-gray-700 dark:shadow-lg">
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Nama Customer:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $customer->name }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Telepon:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $customer->phone ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Alamat:</p>
            <p class="text-gray-900 dark:text-gray-200">{{ $customer->address ?? '-' }}</p>
        </div>
        <div class="mb-4">
            <p class="text-gray-700 text-sm font-bold dark:text-gray-300">Status Keanggotaan:</p>
            <div class="mt-1">
                @if($customer->is_member)
                    <span class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-400 to-amber-500 text-white text-sm font-bold px-4 py-2 rounded-lg shadow-md">
                        ⭐ Member Apotek
                        <span class="text-xs bg-white/20 px-2 py-0.5 rounded">Mendapat Harga Khusus</span>
                    </span>
                @else
                    <span class="inline-flex items-center bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-semibold px-4 py-2 rounded-lg">
                        Customer Umum
                    </span>
                @endif
            </div>
        </div>

        <div class="flex justify-end mt-4">
            <a href="{{ route('customers.edit', $customer->id) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-full mr-2 dark:bg-green-600 dark:hover:bg-green-700">Edit Customer</a>
            <button wire:click="deleteCustomer()" onclick="confirm('Apakah Anda yakin ingin menghapus customer ini?') || event.stopImmediatePropagation()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-full dark:bg-red-600 dark:hover:bg-red-700">Hapus Customer</button>
        </div>
    </div>
</div>