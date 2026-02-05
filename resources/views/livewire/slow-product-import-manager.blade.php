<div class="max-w-4xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h1 class="text-2xl font-bold text-gray-800">Impor Produk (Antrian)</h1>
            <p class="mt-1 text-sm text-gray-600">Unggah file Excel untuk mengimpor produk secara massal. Proses akan berjalan di latar belakang untuk menangani data dalam jumlah besar dengan andal.</p>
        </div>

        <div class="p-6">
            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 mb-6" role="alert">
                    <p class="font-bold">Berhasil</p>
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="bg-red-100 dark:bg-red-900 border-l-4 border-red-500 dark:border-red-400 text-red-700 dark:text-red-200 p-4 mb-6" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <form wire:submit.prevent="import" class="space-y-6">
                <div>
                    <label for="file-upload" class="block text-sm font-medium text-gray-700">File Excel (.xlsx, .xls)</label>
                    <div class="mt-2 flex items-center justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600">
                                <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                    <span>Unggah sebuah file</span>
                                    <input id="file-upload" wire:model="file" name="file-upload" type="file" class="sr-only">
                                </label>
                                <p class="pl-1">atau seret dan lepas</p>
                            </div>
                            <p class="text-xs text-gray-500">XLSX, XLS hingga 10MB</p>
                        </div>
                    </div>
                    @error('file') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                    @if ($file)
                        <div class="mt-4 text-sm text-gray-600">
                            File terpilih: {{ $file->getClientOriginalName() }}
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-end">
                    <div wire:loading wire:target="file" class="text-sm text-gray-500 mr-4">
                        <span>Mengunggah file...</span>
                    </div>
                    <button type="submit" wire:loading.attr="disabled" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150 opacity-100">
                        <span wire:loading.remove wire:target="file, import">Jadwalkan Impor</span>
                        <span wire:loading wire:target="file">Mengunggah...</span>
                        <span wire:loading wire:target="import">Menjadwalkan...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>