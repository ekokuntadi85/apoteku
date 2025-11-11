<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div>
        <h1 class="text-3xl font-bold mb-6 dark:text-gray-100">Dashboard Muazara-App</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Jumlah Penjualan Hari Ini -->
            <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
                <div>
                    <p class="text-gray-500 text-sm dark:text-gray-300">Penjualan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">Rp {{ number_format($salesToday, 0) }}</p>
                </div>
                <div class="text-green-500 text-4xl">
                    <x-heroicon-o-currency-dollar class="w-10 h-10" />
                </div>
            </div>

            <!-- Card 2: Jumlah Kunjungan Hari Ini -->
            <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
                <div>
                    <p class="text-gray-500 text-sm dark:text-gray-300">Kunjungan Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $visitsToday }}</p>
                </div>
                <div class="text-blue-500 text-4xl">
                    <x-heroicon-o-users class="w-10 h-10" />
                </div>
            </div>

            <!-- Card 3: Jumlah Obat Mendekati Expire -->
            <div class="bg-white shadow-md rounded-lg p-6 flex items-center justify-between dark:bg-gray-700 dark:shadow-lg">
                <div>
                    <p class="text-gray-500 text-sm dark:text-gray-300">Obat Mendekati Expire (30 Hari)</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $expiringProductsCount }}</p>
                </div>
                <div class="text-yellow-500 text-4xl">
                    <x-heroicon-o-exclamation-triangle class="w-10 h-10" />
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-700 shadow-md rounded-lg p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4 dark:text-gray-100">Tren Penjualan (30 Hari Terakhir)</h2>
            <div id="salesChart"></div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('livewire:load', function () {
                const salesData = @json($salesChartData);

                const options = {
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Penjualan',
                        data: salesData.series
                    }],
                    xaxis: {
                        categories: salesData.labels,
                        type: 'datetime',
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            },
                            formatter: function (value) {
                                return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMMM yyyy'
                        },
                        y: {
                            formatter: function (value) {
                                return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                            }
                        },
                        theme: 'dark'
                    },
                    noData: {
                        text: 'Tidak ada data penjualan untuk ditampilkan.'
                    }
                };

                const chart = new ApexCharts(document.querySelector("#salesChart"), options);
                chart.render();
            });
        </script>
        @endpush
        </div>

        <h2 class="text-2xl font-bold mb-4 mt-10 dark:text-gray-100">10 Pembelian Terbaru</h2>

        <!-- Desktop Table View -->
        <div class="hidden md:block shadow overflow-hidden border-b border-gray-200 sm:rounded-lg dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Nomor Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Total Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($latestPurchases as $purchase)
                    <tr class="dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('purchases.show', $purchase) }}" class="text-blue-600 hover:underline dark:text-blue-400">{{ $purchase->invoice_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline">{{ $purchase->supplier->name }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap currency-cell text-gray-900 dark:text-gray-200">
                            <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline">
                                <span class="currency-symbol">Rp</span>
                                <span class="currency-value">{{ number_format($purchase->total_price, 0) }}</span>
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline">{{ \Carbon\Carbon::parse($purchase->created_at)->format('Y-m-d') }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">
                            <a href="{{ route('purchases.show', $purchase) }}" class="hover:underline capitalize">
                                {{ $purchase->payment_status }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-gray-500 dark:text-gray-400">Tidak ada data pembelian.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View for Latest Purchases -->
        <div class="block md:hidden space-y-4">
            @forelse($latestPurchases as $purchase)
            <a href="{{ route('purchases.show', $purchase) }}" class="block bg-white dark:bg-gray-700 shadow-md rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Invoice: {{ $purchase->invoice_number }}</span>
                    <span class="text-xs text-gray-600 dark:text-gray-300">Tanggal: {{ \Carbon\Carbon::parse($purchase->created_at)->format('Y-m-d') }}</span>
                </div>
                <div class="text-gray-700 dark:text-gray-200 mb-1">
                    <span class="font-medium">Supplier:</span> {{ $purchase->supplier->name }}
                </div>
                <div class="text-gray-700 dark:text-gray-200">
                    <span class="font-medium">Total:</span> Rp {{ number_format($purchase->total_price, 0) }}
                </div>
                <div class="text-gray-700 dark:text-gray-200">
                    <span class="font-medium">Status:</span> <span class="capitalize">{{ $purchase->payment_status }}</span>
                </div>
            </a>
            @empty
            <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada data pembelian.</p>
            @endforelse
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const salesData = @json($salesChartData);

                const options = {
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: {
                            show: false
                        }
                    },
                    series: [{
                        name: 'Penjualan',
                        data: salesData.series
                    }],
                    xaxis: {
                        categories: salesData.labels,
                        type: 'datetime',
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#9CA3AF'
                            },
                            formatter: function (value) {
                                return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth'
                    },
                    tooltip: {
                        x: {
                            format: 'dd MMMM yyyy'
                        },
                        y: {
                            formatter: function (value) {
                                return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                            }
                        },
                        theme: 'dark'
                    },
                    noData: {
                        text: 'Tidak ada data penjualan untuk ditampilkan.'
                    }
                };

                const chart = new ApexCharts(document.querySelector("#salesChart"), options);
                chart.render();
            });
        </script>
    </div>
</div>
