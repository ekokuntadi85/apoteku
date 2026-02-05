<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 10mm 15mm; /* Reduced margins */
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
            
            /* Nuclear reset for ALL containers */
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
                padding: 20px !important; /* Only internal padding for content */
            }
            
            .bg-gradient-to-r {
                background: none !important;
                color: black !important;
            }
            
            .print-text-base {
                font-size: 0.95rem !important;
            }
        }
    </style>

    <div class="flex justify-between items-center mb-6 no-print">
        <h1 class="text-3xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Laporan Laba Rugi</h1>
        
        <div class="flex gap-2 items-center">
             <select wire:model.live="period" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                <option value="this_month">Bulan Ini</option>
                <option value="last_month">Bulan Lalu</option>
                <option value="this_year">Tahun Ini</option>
                <option value="custom">Custom</option>
            </select>
            
            @if($period == 'custom')
                <div class="flex items-center gap-2">
                    <input type="date" wire:model.live="startDate" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" />
                    <span class="text-gray-500">-</span>
                    <input type="date" wire:model.live="endDate" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" />
                </div>
            @endif
            
            <button onclick="window.print()" class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak
            </button>
        </div>
    </div>

    <!-- Report Loading Indicator -->
    <div wire:loading class="w-full text-center py-4 no-print">
        <span class="loading loading-spinner loading-lg text-indigo-500"></span>
        <p class="text-sm text-gray-500 mt-2">Menghitung data keuangan...</p>
    </div>

    <!-- Report Content (Paper Style) -->
    <div wire:loading.remove class="max-w-4xl mx-auto bg-white text-gray-900 p-12 shadow-lg border rounded-lg print-container print:max-w-none print:w-full print:border-none print:shadow-none print:p-0">
        
        <!-- Report Header -->
        <div class="text-center mb-8 pb-4 border-b-2 border-double border-gray-300">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-gray-800">Apoteku</h1>
            <h2 class="text-xl font-semibold text-gray-700 mt-1">Laporan Laba Rugi</h2>
            <p class="text-sm text-gray-500 mt-1">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</p>
        </div>

        <div class="font-mono text-sm md:text-base print-text-base space-y-6">
            
            <!-- Revenue Section -->
            <section>
                <div class="flex justify-between items-center font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1">
                    <span>PENDAPATAN USAHA (REVENUE)</span>
                </div>
                <div class="flex justify-between items-center py-1 hover:bg-gray-50 text-gray-700 transition">
                    <span class="pl-4">Penjualan Bersih</span>
                    <span class="font-medium text-right whitespace-nowrap">Rp {{ number_format($revenue, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-end mt-2">
                    <div class="flex justify-between items-center py-1 font-bold text-gray-900 border-t border-gray-400 w-full md:w-1/2 print:w-1/2">
                        <span>Total Pendapatan</span>
                        <span class="text-right whitespace-nowrap">Rp {{ number_format($revenue, 2, ',', '.') }}</span>
                    </div>
                </div>
            </section>

            <!-- COGS Section -->
            <section>
                <div class="flex justify-between items-center font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1">
                    <span>BEBAN POKOK PENJUALAN (COGS)</span>
                </div>
                <div class="flex justify-between items-center py-1 hover:bg-gray-50 text-gray-700 transition">
                    <span class="pl-4">Harga Pokok Penjualan (HPP)</span>
                    <span class="text-red-600 text-right whitespace-nowrap">(Rp {{ number_format($cogs, 2, ',', '.') }})</span>
                </div>
                 <div class="flex justify-end mt-2">
                    <div class="flex justify-between items-center py-1 font-bold text-gray-900 border-t border-gray-400 w-full md:w-1/2 print:w-1/2">
                        <span>Total HPP</span>
                        <span class="text-red-600 text-right whitespace-nowrap">(Rp {{ number_format($cogs, 2, ',', '.') }})</span>
                    </div>
                </div>
            </section>

            <!-- Gross Profit -->
            <div class="flex justify-between items-center py-2 bg-gray-100 px-4 rounded border border-gray-200 font-bold text-lg print:bg-gray-100 print:text-black">
                <span>LABA KOTOR (GROSS PROFIT)</span>
                <span class="{{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-700' }} text-right whitespace-nowrap">
                    Rp {{ number_format($grossProfit, 2, ',', '.') }}
                </span>
            </div>

            <!-- Expenses Section -->
             <section>
                <div class="flex justify-between items-center font-bold text-gray-800 mb-2 border-b border-gray-200 pb-1">
                    <span>BEBAN OPERASIONAL (EXPENSES)</span>
                </div>
                
                <div class="space-y-1">
                @if(count($expensesByCategory) > 0)
                    @foreach($expensesByCategory as $category => $amount)
                    <div class="flex justify-between items-center py-1 hover:bg-gray-50 text-gray-700 transition">
                        <span class="pl-4">{{ $category }}</span>
                        <span class="text-red-600 text-right whitespace-nowrap">(Rp {{ number_format($amount, 2, ',', '.') }})</span>
                    </div>
                    @endforeach
                @else
                    <div class="flex justify-between items-center py-1 hover:bg-gray-50 italic text-gray-500">
                        <span class="pl-4">Tidak ada pengeluaran</span>
                        <span>-</span>
                    </div>
                @endif
                </div>

                <div class="flex justify-end mt-2">
                    <div class="flex justify-between items-center py-1 font-bold text-gray-900 border-t border-gray-400 w-full md:w-1/2 print:w-1/2">
                        <span>Total Beban Operasional</span>
                        <span class="text-red-600 text-right whitespace-nowrap">(Rp {{ number_format($totalExpenses, 2, ',', '.') }})</span>
                    </div>
                </div>
            </section>

            <!-- Net Profit -->
             <div class="flex justify-between items-center py-4 border-t-4 border-double border-gray-800 font-bold text-xl mt-8 px-4 rounded-lg {{ $netProfit >= 0 ? 'bg-green-50' : 'bg-red-50' }} print:bg-transparent print:border-black">
                <span>LABA BERSIH (NET PROFIT)</span>
                <span class="{{ $netProfit >= 0 ? 'text-green-800' : 'text-red-800' }} text-right whitespace-nowrap">
                    Rp {{ number_format($netProfit, 2, ',', '.') }}
                </span>
            </div>

        </div>

         <div class="text-center mt-12 text-xs text-gray-400">
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Apoteku Finance System</p>
        </div>

    </div>
</div>
