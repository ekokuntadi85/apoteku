<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 10mm 15mm;
            }
            
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                background: white !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            div, section, main, article {
                margin-left: 0 !important;
                margin-right: 0 !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
            }
            
            .container, .mx-auto {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            .print-container {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                margin: 0 !important;
                padding: 20px !important;
            }
            
            .bg-gradient-to-r {
                background: none !important;
                color: black !important;
            }
            
            .print-table {
                display: table !important;
                width: 100% !important;
            }
            
            .print-hidden {
                display: none !important;
            }
            
            .print-text-sm {
                font-size: 0.875rem !important;
            }
            
            .overflow-x-auto {
                overflow: visible !important;
            }
        }
    </style>

    <div class="flex flex-col md:flex-row justify-between items-center mb-6 no-print gap-4">
        <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Buku Besar (General Ledger)</h1>
        
        <a href="{{ route('reports.finance.general-ledger.print', ['accountId' => $accountId, 'startDate' => $startDate, 'endDate' => $endDate]) }}" target="_blank" class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 w-full md:w-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Cetak
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 shadow-md rounded-xl p-4 mb-8 border border-gray-200 dark:border-gray-700 no-print">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pilih Akun</label>
                <select wire:model.live="accountId" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    @foreach($accounts as $account)
                        <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dari Tanggal</label>
                <input type="date" wire:model.live="startDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
        </div>
        
        <!-- View Mode Toggle -->
        <div class="mt-4 flex items-center">
            <label class="flex items-center cursor-pointer">
                <input type="checkbox" wire:model.live="showDetail" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                <span class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    Tampilkan Detail Transaksi (Default: Ringkasan Harian)
                </span>
            </label>
        </div>
    </div>

    <div wire:loading class="w-full text-center py-4 no-print">
        <span class="loading loading-spinner loading-lg text-indigo-500"></span>
        <p class="text-sm text-gray-500 mt-2">Memuat data...</p>
    </div>

    <!-- Report Body -->
    <div wire:loading.remove class="bg-white dark:bg-gray-800 shadow-xl rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden print-container print:overflow-visible print:border-none print:shadow-none">
        
        <!-- Header Print -->
        <div class="p-8 pb-4 border-b-2 border-double border-gray-300 dark:border-gray-600">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-gray-800 text-center">Apoteku</h1>
            <h2 class="text-xl font-semibold text-gray-700 mt-1 text-center">Buku Besar (General Ledger)</h2>
            <p class="text-sm text-gray-500 mt-1 text-center">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
            
            @if($selectedAccount)
            <div class="mt-4 text-center">
                <div class="inline-block bg-indigo-50 dark:bg-indigo-900/30 px-6 py-2 rounded-full border border-indigo-100 dark:border-indigo-800">
                    <span class="font-bold text-lg text-indigo-900 dark:text-indigo-200">{{ $selectedAccount->code }} - {{ $selectedAccount->name }}</span>
                    <span class="text-xs ml-2 text-indigo-600 dark:text-indigo-300">({{ ucfirst($selectedAccount->type) }})</span>
                </div>
            </div>
            @endif
        </div>

        <!-- Desktop Table View (For Print) -->
        <div class="overflow-x-auto print-table">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 print-table text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ref</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider w-1/3">Keterangan</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Debit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Kredit</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider bg-gray-100 dark:bg-gray-600">Saldo</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    <!-- Opening Balance Row -->
                    <tr class="bg-yellow-50 dark:bg-yellow-900/10 font-medium">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 italic" colspan="2">Saldo Awal</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-500 dark:text-gray-400 italic"></td>
                         <td class="px-6 py-4 whitespace-nowrap text-right text-gray-400">-</td>
                         <td class="px-6 py-4 whitespace-nowrap text-right text-gray-400">-</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-gray-900 dark:text-white bg-yellow-100/50 dark:bg-gray-600/50">
                            Rp {{ number_format($openingBalance, 2, ',', '.') }}
                        </td>
                    </tr>

                    @forelse($ledgerData as $row)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            {{ \Carbon\Carbon::parse($row['date'])->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-indigo-600 dark:text-indigo-400 text-xs">
                            {{ $row['reference'] ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                            <div class="font-medium">{{ $row['description'] }}</div>
                            @if(isset($row['memo']) && $row['memo'])
                                <div class="text-xs text-gray-500 dark:text-gray-400 italic">{{ $row['memo'] }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 dark:text-gray-200">
                            @if($row['debit'] > 0)
                                Rp {{ number_format($row['debit'], 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-gray-900 dark:text-gray-200">
                             @if($row['credit'] > 0)
                                Rp {{ number_format($row['credit'], 2, ',', '.') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-semibold text-gray-900 dark:text-white bg-gray-50 dark:bg-gray-700">
                            Rp {{ number_format($row['balance'], 2, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400 italic">
                            Tidak ada transaksi untuk periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-700 font-bold border-t-2 border-gray-300 dark:border-gray-500">
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-right text-gray-800 dark:text-gray-200 uppercase">Total Pergerakan</td>
                        <td class="px-6 py-4 text-right text-indigo-700 dark:text-indigo-300">Rp {{ number_format($totalDebit, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right text-rose-700 dark:text-rose-300">Rp {{ number_format($totalCredit, 2, ',', '.') }}</td>
                        <td class="px-6 py-4 text-right bg-gray-200 dark:bg-gray-600 text-gray-900 dark:text-white">
                             @if(!empty($ledgerData))
                                Rp {{ number_format(end($ledgerData)['balance'], 2, ',', '.') }}
                             @else
                                Rp {{ number_format($openingBalance, 2, ',', '.') }}
                             @endif
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="text-center py-6 text-xs text-gray-400 print:block hidden">
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Apoteku Finance System</p>
        </div>
    </div>
</div>
