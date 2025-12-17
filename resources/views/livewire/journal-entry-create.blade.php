<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Jurnal Umum & Saldo Awal</h2>
    </div>

    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4 dark:bg-green-800 dark:border-green-700 dark:text-green-200" role="alert">
            <strong class="font-bold">Sukses!</strong>
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 dark:bg-red-800 dark:border-red-700 dark:text-red-200" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-xl rounded-xl p-8 border border-gray-200 dark:border-gray-700">
        <div class="mb-6">
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 dark:bg-blue-900/30 dark:border-blue-500">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200">
                            Gunakan halaman ini untuk mencatat <strong>Modal Awal</strong> (Kas/Bank) atau penyesuaian akuntansi manual lainnya.
                            Total Debit harus sama dengan Total Kredit.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Transaction Date -->
                <div>
                    <label for="transaction_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tanggal Transaksi</label>
                    <input type="date" id="transaction_date" wire:model="transaction_date" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('transaction_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <!-- Reference Number -->
                <div>
                    <label for="reference_number" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nomor Referensi (Opsional)</label>
                    <input type="text" id="reference_number" wire:model="reference_number" placeholder="Contoh: REF-001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @error('reference_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Description -->
            <div class="mb-8">
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi / Keterangan</label>
                <textarea id="description" wire:model="description" rows="2" placeholder="Contoh: Setoran Modal Awal Pemilik" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <!-- Journal Details Table -->
            <div class="mb-6 overflow-hidden border border-gray-200 dark:border-gray-700 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">Akun</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">Memo (Opsional)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Debit</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-32">Kredit</th>
                            <th class="px-4 py-3 text-center w-10"></th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($rows as $index => $row)
                        <tr>
                            <td class="px-4 py-2">
                                <select wire:model="rows.{{ $index }}.account_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                    <option value="">Pilih Akun</option>
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error("rows.{$index}.account_id") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </td>
                            <td class="px-4 py-2">
                                <input type="text" wire:model="rows.{{ $index }}.memo" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="rows.{{ $index }}.debit" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0">
                            </td>
                            <td class="px-4 py-2">
                                <input type="number" step="0.01" wire:model.live.debounce.500ms="rows.{{ $index }}.credit" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm text-right dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="0">
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if(count($rows) > 2)
                                    <button type="button" wire:click="removeRow({{ $index }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700 font-bold">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">Total:</td>
                            <td class="px-4 py-3 text-right {{ $this->totalDebit == $this->totalCredit ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($this->totalDebit, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right {{ $this->totalDebit == $this->totalCredit ? 'text-green-600' : 'text-red-600' }}">
                                Rp {{ number_format($this->totalCredit, 2, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-between items-center">
                <button type="button" wire:click="addRow" class="inline-flex items-center px-4 py-2 border border-blue-300 text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-900/40 dark:text-blue-200 dark:border-blue-700 dark:hover:bg-blue-900/60 transition">
                    + Tambah Baris
                </button>

                <div class="flex space-x-3">
                     <span class="inline-flex items-center text-sm mr-2 {{ $this->totalDebit == $this->totalCredit && $this->totalDebit > 0 ? 'text-green-600 font-bold' : 'text-red-500 italic' }}">
                        @if($this->totalDebit != $this->totalCredit)
                            Selisih: Rp {{ number_format(abs($this->totalDebit - $this->totalCredit), 2, ',', '.') }} (Tidak Seimbang)
                        @elseif($this->totalDebit == 0)
                            Total tidak boleh 0
                        @else
                            Seimbang (Balance)
                        @endif
                    </span>
                
                    <button type="submit" 
                        @if($this->totalDebit != $this->totalCredit || $this->totalDebit == 0) disabled @endif
                        class="inline-flex items-center justify-center px-8 py-3 border-b-4 border-indigo-800 shadow-lg text-sm font-bold rounded-lg text-white bg-gradient-to-r from-indigo-600 to-fuchsia-600 hover:from-indigo-700 hover:to-fuchsia-700 hover:border-indigo-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all transform active:scale-95 active:border-b-0 active:translate-y-1">
                        Simpan Jurnal
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="mt-8 bg-white dark:bg-gray-800 shadow-lg rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-4">Contoh Penginputan Modal Awal</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-bold mb-2">Kasir (Uang Tunai di Tangan)</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Akun Debit: <span class="font-mono bg-gray-200 dark:bg-gray-600 px-1 rounded">1101 - Kas</span> (Sebesar jumlah uang)</li>
                    <li>Akun Kredit: <span class="font-mono bg-gray-200 dark:bg-gray-600 px-1 rounded">3101 - Modal Awal</span> (Sebesar jumlah uang)</li>
                </ul>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
                <h4 class="font-bold mb-2">Setoran Bank</h4>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Akun Debit: <span class="font-mono bg-gray-200 dark:bg-gray-600 px-1 rounded">1102 - Bank</span> (Sebesar saldo bank)</li>
                    <li>Akun Kredit: <span class="font-mono bg-gray-200 dark:bg-gray-600 px-1 rounded">3101 - Modal Awal</span> (Sebesar saldo bank)</li>
                </ul>
            </div>
        </div>
    </div>
</div>
