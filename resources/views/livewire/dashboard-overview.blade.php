<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <div>
        <h1 class="text-3xl font-bold mb-6 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Dashboard Muazara-App</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card 1: Jumlah Penjualan Hari Ini -->
            <div class="relative overflow-hidden rounded-xl p-6 flex items-center justify-between shadow-md bg-gradient-to-br from-emerald-400/10 via-emerald-500/10 to-emerald-600/10 border border-emerald-500/20 dark:bg-emerald-500/10">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-emerald-400/20 blur-2xl"></div>
                <div>
                    <p class="text-emerald-600 text-sm dark:text-emerald-300">Penjualan Hari Ini</p>
                    <p class="text-3xl font-extrabold text-emerald-700 dark:text-emerald-300">Rp {{ number_format($salesToday, 0) }}</p>
                </div>
                <div class="text-emerald-500 text-4xl">
                    <x-heroicon-o-currency-dollar class="w-12 h-12" />
                </div>
            </div>

            <!-- Card 2: Jumlah Kunjungan Hari Ini -->
            <div class="relative overflow-hidden rounded-xl p-6 flex items-center justify-between shadow-md bg-gradient-to-br from-sky-400/10 via-sky-500/10 to-sky-600/10 border border-sky-500/20 dark:bg-sky-500/10">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-sky-400/20 blur-2xl"></div>
                <div>
                    <p class="text-sky-600 text-sm dark:text-sky-300">Kunjungan Hari Ini</p>
                    <p class="text-3xl font-extrabold text-sky-700 dark:text-sky-300">{{ $visitsToday }}</p>
                </div>
                <div class="text-sky-500 text-4xl">
                    <x-heroicon-o-users class="w-12 h-12" />
                </div>
            </div>

            <!-- Card 3: Jumlah Obat Mendekati Expire -->
            <div class="relative overflow-hidden rounded-xl p-6 flex items-center justify-between shadow-md bg-gradient-to-br from-amber-400/10 via-amber-500/10 to-amber-600/10 border border-amber-500/20 dark:bg-amber-500/10">
                <div class="absolute -right-8 -top-8 h-32 w-32 rounded-full bg-amber-400/20 blur-2xl"></div>
                <div>
                    <p class="text-amber-600 text-sm dark:text-amber-300">Obat Mendekati Expire (30 Hari)</p>
                    <p class="text-3xl font-extrabold text-amber-700 dark:text-amber-300">{{ $expiringProductsCount }}</p>
                </div>
                <div class="text-amber-500 text-4xl">
                    <x-heroicon-o-exclamation-triangle class="w-12 h-12" />
                </div>
            </div>
        </div>

        <div class="bg-white/80 backdrop-blur dark:bg-gray-700/70 shadow-md rounded-xl p-6 mb-8 border border-zinc-200/60 dark:border-zinc-700/60">
            <h2 class="text-2xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-fuchsia-500 to-purple-600">Tren Penjualan (30 Hari Terakhir)</h2>
            <div id="salesChart" wire:ignore></div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            (function () {
                const initialSalesData = @json($salesChartData);

                function buildOptions(salesData) {
                    return {
                        chart: { type: 'area', height: 350, toolbar: { show: false }, foreColor: '#9CA3AF' },
                        colors: ['#6366f1'],
                        series: [{ name: 'Penjualan', data: salesData.series }],
                        xaxis: {
                            categories: salesData.labels,
                            type: 'datetime',
                            labels: { style: { colors: '#9CA3AF' } },
                            axisBorder: { color: '#E5E7EB' },
                            axisTicks: { color: '#E5E7EB' }
                        },
                        yaxis: {
                            labels: {
                                style: { colors: '#9CA3AF' },
                                formatter: function (value) {
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            axisBorder: { color: '#E5E7EB' },
                            axisTicks: { color: '#E5E7EB' }
                        },
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.4,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        dataLabels: { enabled: false },
                        stroke: { curve: 'smooth', width: 3 },
                        tooltip: {
                            x: { format: 'dd MMMM yyyy' },
                            y: { formatter: (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v) },
                            theme: 'dark'
                        },
                        noData: { text: 'Tidak ada data penjualan untuk ditampilkan.' }
                    };
                }

                function renderWhenReady(salesData, attempt = 0) {
                    const el = document.querySelector('#salesChart');
                    const ready = !!(el && window.ApexCharts);
                    if (!ready) {
                        if (attempt < 20) { // retry up to ~2s
                            return setTimeout(() => renderWhenReady(salesData, attempt + 1), 100);
                        }
                        return;
                    }
                    // Clear existing content to avoid duplicate charts on re-render
                    el.innerHTML = '';
                    const chart = new ApexCharts(el, buildOptions(salesData));
                    chart.render();
                }

                // Attach Livewire listener even if framework already initialized
                (function attachLivewireListener(attempt = 0) {
                    if (window.Livewire && typeof Livewire.on === 'function') {
                        Livewire.on('render-sales-chart', (event) => {
                            renderWhenReady(event.data);
                        });
                    } else if (attempt < 50) {
                        setTimeout(() => attachLivewireListener(attempt + 1), 100);
                    }
                })();

                // Render immediately with initial data when this view is loaded (covers SPA navigate)
                renderWhenReady(initialSalesData);

                // Also render on Livewire SPA navigations to this view
                document.addEventListener('livewire:navigated', () => {
                    renderWhenReady(initialSalesData);
                });
            })();
        </script>
        @endpush
        </div>

        <h2 class="text-2xl font-bold mb-4 mt-10 bg-clip-text text-transparent bg-gradient-to-r from-rose-500 to-orange-500">10 Pembelian Terbaru</h2>

        <!-- Desktop Table View -->
        <div class="hidden md:block shadow overflow-hidden border border-gray-200 sm:rounded-xl dark:border-gray-700">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 bg-white/60 dark:bg-gray-800/50 backdrop-blur">
                <thead class="bg-gradient-to-r from-indigo-50 to-fuchsia-50 dark:from-zinc-800 dark:to-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Nomor Invoice</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Supplier</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Total Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider dark:text-gray-300">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @forelse($latestPurchases as $purchase)
                    @php
                        $status = strtolower($purchase->payment_status);
                        $badge = match($status) {
                            'paid','lunas' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300 ring-1 ring-emerald-200/50',
                            'unpaid','belum lunas' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300 ring-1 ring-rose-200/50',
                            'partial','sebagian' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 ring-1 ring-amber-200/50',
                            default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-zinc-200/50',
                        };
                    @endphp
                    <tr class="hover:bg-indigo-50/60 dark:hover:bg-zinc-800/70 transition-colors">
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
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('purchases.show', $purchase) }}" class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold capitalize {{ $badge }}">
                                <span class="w-2 h-2 rounded-full @if(in_array($status,['paid','lunas'])) bg-emerald-500 @elseif(in_array($status,['unpaid','belum lunas'])) bg-rose-500 @elseif(in_array($status,['partial','sebagian'])) bg-amber-500 @else bg-zinc-400 @endif"></span>
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
            @php
                $status = strtolower($purchase->payment_status);
                $badge = match($status) {
                    'paid','lunas' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
                    'unpaid','belum lunas' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300',
                    'partial','sebagian' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                    default => 'bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-300',
                };
            @endphp
            <a href="{{ route('purchases.show', $purchase) }}" class="block bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-4 border border-gray-200/70 dark:border-gray-600/60 backdrop-blur">
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
                <div class="mt-2">
                    <span class="text-xs font-medium">Status:</span>
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold capitalize {{ $badge }}">
                        {{ $purchase->payment_status }}
                    </span>
                </div>
            </a>
            @empty
            <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada data pembelian.</p>
            @endforelse
        </div>

        
    </div>
</div>
