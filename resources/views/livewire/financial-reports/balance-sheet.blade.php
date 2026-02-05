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
            
            .print-grid {
                display: grid !important;
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 2rem !important;
            }
            
            .print-text-base {
                font-size: 0.95rem !important;
            }
        }
    </style>

    <div class="flex justify-between items-center mb-6 no-print">
        <h1 class="text-3xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Laporan Neraca (Balance Sheet)</h1>
        
        <div class="flex gap-2 items-center">
            <input type="date" wire:model.live="endDate" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600" />
            
            <button onclick="window.print()" class="inline-flex items-center justify-center bg-gray-800 hover:bg-gray-900 text-white font-semibold py-2 px-4 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-700 dark:bg-gray-600 dark:hover:bg-gray-500 ml-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Cetak
            </button>
        </div>
    </div>

    <div wire:loading class="w-full text-center py-4 no-print">
        <span class="loading loading-spinner loading-lg text-indigo-500"></span>
        <p class="text-sm text-gray-500 mt-2">Menghitung neraca...</p>
    </div>

    <!-- Report Body -->
    <div wire:loading.remove class="max-w-4xl mx-auto bg-white text-gray-900 p-8 shadow-lg border rounded-lg print-container print:max-w-none print:w-full print:border-none print:shadow-none print:p-0">
        
        <!-- Header -->
        <div class="text-center mb-8 pb-4 border-b-2 border-double border-gray-300">
            <h1 class="text-2xl font-bold uppercase tracking-wider text-gray-800">Apoteku</h1>
            <h2 class="text-xl font-semibold text-gray-700 mt-1">Laporan Neraca (Balance Sheet)</h2>
            <p class="text-sm text-gray-500 mt-1">Per Tanggal: {{ \Carbon\Carbon::parse($endDate)->format('d F Y') }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 font-mono text-sm md:text-base print-grid print-text-base">
            
            <!-- Left Column: Assets -->
            <div>
                <div class="flex justify-between items-center bg-gray-100 p-2 font-bold text-gray-800 mb-4 rounded border-l-4 border-indigo-500 print:bg-gray-100 print:text-black">
                    <span>ASET (AKTIVA)</span>
                </div>

                <div class="space-y-1 mb-6">
                    @foreach($assets as $account)
                    <div class="flex justify-between items-center py-1 border-b border-gray-100 hover:bg-gray-50">
                        <div class="flex flex-col">
                            <span class="font-semibold text-gray-700">{{ $account['code'] }} - {{ $account['name'] }}</span>
                        </div>
                        <span class="text-gray-900 whitespace-nowrap">Rp {{ number_format($account['balance'], 2, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-between items-center py-3 border-t-2 border-gray-800 font-bold text-lg mt-auto">
                    <span>TOTAL ASET</span>
                    <span class="text-indigo-700 whitespace-nowrap">Rp {{ number_format($totalAssets, 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- Right Column: Liabilities & Equity -->
            <div class="flex flex-col">
                
                <!-- Liabilities -->
                <div class="mb-8">
                    <div class="flex justify-between items-center bg-gray-100 p-2 font-bold text-gray-800 mb-4 rounded border-l-4 border-rose-500 print:bg-gray-100 print:text-black">
                        <span>KEWAJIBAN (LIABILITAS)</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($liabilities as $account)
                        <div class="flex justify-between items-center py-1 border-b border-gray-100 hover:bg-gray-50">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700">{{ $account['code'] }} - {{ $account['name'] }}</span>
                            </div>
                            <span class="text-gray-900 whitespace-nowrap">Rp {{ number_format($account['balance'], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between items-center py-2 mt-2 font-bold text-gray-700 bg-gray-50 px-2 rounded">
                        <span>Total Kewajiban</span>
                        <span class="whitespace-nowrap">Rp {{ number_format($totalLiabilities, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Equity -->
                <div>
                     <div class="flex justify-between items-center bg-gray-100 p-2 font-bold text-gray-800 mb-4 rounded border-l-4 border-fuchsia-500 print:bg-gray-100 print:text-black">
                        <span>EKUITAS (MODAL)</span>
                    </div>
                    <div class="space-y-1">
                        @foreach($equity as $account)
                        <div class="flex justify-between items-center py-1 border-b border-gray-100 hover:bg-gray-50">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700">{{ $account['code'] }} - {{ $account['name'] }}</span>
                            </div>
                            <span class="text-gray-900 whitespace-nowrap">Rp {{ number_format($account['balance'], 2, ',', '.') }}</span>
                        </div>
                        @endforeach
                        
                        <!-- Calculated Current Earnings -->
                        <div class="flex justify-between items-center py-1 border-b border-gray-100 hover:bg-gray-50 bg-indigo-50/30">
                            <div class="flex flex-col">
                                <span class="font-semibold text-gray-700">Laba Periode Berjalan</span>
                            </div>
                            <span class="text-gray-900 whitespace-nowrap">Rp {{ number_format($currentEarnings, 2, ',', '.') }}</span>
                        </div>
                    </div>
                     <div class="flex justify-between items-center py-2 mt-2 font-bold text-gray-700 bg-gray-50 px-2 rounded">
                        <span>Total Ekuitas</span>
                        <span class="whitespace-nowrap">Rp {{ number_format($totalEquity + $currentEarnings, 2, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Grand Total Right -->
                <div class="flex justify-between items-center py-3 border-t-2 border-gray-800 font-bold text-lg mt-auto">
                    <span>TOTAL KEWAJIBAN & EKUITAS</span>
                    <span class="text-indigo-700 whitespace-nowrap">Rp {{ number_format($totalLiabilities + $totalEquity + $currentEarnings, 2, ',', '.') }}</span>
                </div>
            </div>

        </div>
        
        <div class="text-center mt-12 text-xs text-gray-400">
            <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Apoteku Finance System</p>
        </div>
    </div>
</div>
