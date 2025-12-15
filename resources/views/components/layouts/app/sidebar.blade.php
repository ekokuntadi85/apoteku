<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @fluxAppearance
        <style>
            @media print {
                /* Hide ALL navigation elements */
                aside, nav, header, 
                .flux-sidebar, .flux-header,
                [data-flux-sidebar], [data-flux-header] {
                    display: none !important;
                }
                
                /* Ultra-aggressive body reset */
                body, html {
                    width: 100vw !important;
                    max-width: 100vw !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    overflow-x: hidden !important;
                }
                
                /* Reset main content area */
                main, 
                .flux-main, 
                [data-flux-main],
                [data-flux-container] {
                    margin: 0 !important;
                    padding: 0 !important;
                    width: 100vw !important;
                    max-width: 100vw !important;
                    margin-left: 0 !important;
                    padding-left: 0 !important;
                }
                
                /* Force all containers to full width */
                .container,
                .mx-auto,
                div[class*="container"] {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin-left: 0 !important;
                    margin-right: 0 !important;
                    padding-left: 0 !important;
                    padding-right: 0 !important;
                }
                
                /* Force background colors to print */
                * {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
            }
        </style>
    </head>
	<body class="min-h-screen bg-white dark:bg-zinc-800">
		<flux:sidebar sticky stashable class="relative border-e border-zinc-200 bg-gradient-to-b from-sky-100 via-indigo-100 to-purple-200 dark:border-zinc-700 dark:bg-gradient-to-b dark:from-slate-900 dark:via-slate-800 dark:to-slate-700">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            
            
			<a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse py-3 px-2 rounded-lg hover:bg-white/60 dark:hover:bg-zinc-800/60 transition-colors" wire:navigate>
                <x-app-logo />
            </a>

			<flux:navlist variant="outline" class="px-2">
                @can('access-dashboard')
				<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors">
					{{ __('Dashboard') }}
				</flux:navlist.item>
                @endcan
                
				<flux:navlist.group :heading="__('Master Data')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="layout-grid" :href="route('products.index')" :current="request()->routeIs('products.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Daftar Produk</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document-list" :href="route('categories.index')" :current="request()->routeIs('categories.index')" class="rounded-lg hover:bg-sky-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Kategori</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document" :href="route('units.index')" :current="request()->routeIs('units.index')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Satuan</flux:navlist.item>
					<flux:navlist.item icon="building-office-2" :href="route('suppliers.index')" :current="request()->routeIs('suppliers.*')" class="rounded-lg hover:bg-violet-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Supplier</flux:navlist.item>
					<flux:navlist.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.*')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Customer</flux:navlist.item>
                </flux:navlist.group>

				<flux:navlist.group :heading="__('Transaksi')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="document-text" :href="route('purchase-orders.index')" :current="request()->routeIs('purchase-orders.*')" class="rounded-lg hover:bg-blue-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Surat Pesanan</flux:navlist.item>
					<flux:navlist.item icon="credit-card" :href="route('purchases.index')" :current="request()->routeIs('purchases.*')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Daftar Pembelian</flux:navlist.item>
					<flux:navlist.item icon="currency-dollar" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" class="rounded-lg hover:bg-fuchsia-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Daftar Penjualan</flux:navlist.item>
					<flux:navlist.item icon="computer-desktop" :href="route('pos.index')" :current="request()->routeIs('pos.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>POS</flux:navlist.item>
					<flux:navlist.item icon="banknotes" :href="route('accounts-receivable.index')" :current="request()->routeIs('accounts-receivable.index')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Daftar Invoice Kredit</flux:navlist.item>
					<flux:navlist.item icon="adjustments-horizontal" :href="route('stock-opname.index')" :current="request()->routeIs('stock-opname.index')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Stok Opname</flux:navlist.item>
                </flux:navlist.group>

				<flux:navlist.group :heading="__('Keuangan')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="banknotes" :href="route('expenses.index')" :current="request()->routeIs('expenses.*')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Pengeluaran Operasional</flux:navlist.item>
					<flux:navlist.item icon="tag" :href="route('expense-categories.index')" :current="request()->routeIs('expense-categories.index')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Kategori Pengeluaran</flux:navlist.item>
                    <flux:navlist.item icon="book-open" :href="route('journal-entries.create')" :current="request()->routeIs('journal-entries.create')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Input Jurnal Umum</flux:navlist.item>
					<flux:navlist.item icon="credit-card" :href="route('reports.finance.accounts-payable')" :current="request()->routeIs('reports.finance.accounts-payable')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Hutang Usaha</flux:navlist.item>
                    <flux:navlist.item icon="presentation-chart-line" :href="route('reports.finance.income-statement')" :current="request()->routeIs('reports.finance.income-statement')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Laporan Laba Rugi</flux:navlist.item>
                    <flux:navlist.item icon="scale" :href="route('reports.finance.balance-sheet')" :current="request()->routeIs('reports.finance.balance-sheet')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Laporan Neraca</flux:navlist.item>
                    <flux:navlist.item icon="book-open" :href="route('reports.finance.general-ledger')" :current="request()->routeIs('reports.finance.general-ledger')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Buku Besar</flux:navlist.item>
                </flux:navlist.group>

				<flux:navlist.group :heading="__('Laporan')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" class="rounded-lg hover:bg-sky-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Laporan Penjualan</flux:navlist.item>
					<flux:navlist.item icon="calendar-days" :href="route('reports.expiring-stock')" :current="request()->routeIs('reports.expiring-stock')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Laporan Stok Kedaluwarsa</flux:navlist.item>
					<flux:navlist.item icon="arrow-down-circle" :href="route('reports.low-stock')" :current="request()->routeIs('reports.low-stock')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Laporan Stok Menipis</flux:navlist.item>
					<flux:navlist.item icon="document-text" :href="route('stock-card.index')" :current="request()->routeIs('stock-card.index')" class="rounded-lg hover:bg-fuchsia-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Kartu Stok</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document-list" :href="route('kartu-monitoring-suhu')" :current="request()->routeIs('kartu-monitoring-suhu')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Kartu Monitoring Suhu</flux:navlist.item>
                </flux:navlist.group>

                @can('manage-users')
				<flux:navlist.group :heading="__('Pengaturan Sistem')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.index')" class="rounded-lg hover:bg-violet-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Manajemen Pengguna</flux:navlist.item>
					<flux:navlist.item icon="server" :href="route('database.backup')" :current="request()->routeIs('database.backup')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Manajemen Backup</flux:navlist.item>
					<flux:navlist.item icon="server" :href="route('database.restore')" :current="request()->routeIs('database.restore')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Restore Database</flux:navlist.item>
					<flux:navlist.item icon="shield-check" :href="route('stock-consistency.index')" :current="request()->routeIs('stock-consistency.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Integritas Stok</flux:navlist.item>
					<flux:navlist.item icon="command-line" :href="route('artisan.commands')" :current="request()->routeIs('artisan.commands')" class="rounded-lg hover:bg-purple-50/70 dark:hover:bg-zinc-800/70 transition-colors" wire:navigate>Artisan Commands</flux:navlist.item>
                </flux:navlist.group>
                @endcan
            </flux:navlist>

            <flux:spacer />

			<flux:navlist variant="outline">
                {{-- <flux:navlist.item icon="folder-git-2" href="https://github.com/laravel/livewire-starter-kit" target="_blank">
                {{ __('Repository') }}
                </flux:navlist.item>

                <flux:navlist.item icon="book-open-text" href="https://laravel.com/docs/starter-kits#livewire" target="_blank">
                {{ __('Documentation') }}
                </flux:navlist.item> --}}
            </flux:navlist>

            <!-- Desktop User Menu -->
            @auth
                <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />

                    <flux:menu class="w-[220px]">
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden sticky top-0 z-10 bg-white dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            @auth
                <flux:dropdown position="top" align="end">
                    <flux:profile
                        :initials="auth()->user()->initials()"
                        icon-trailing="chevron-down"
                    />

                    <flux:menu>
                        <flux:menu.radio.group>
                            <div class="p-0 text-sm font-normal">
                                <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    </span>

                                    <div class="grid flex-1 text-start text-sm leading-tight">
                                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                    </div>
                                </div>
                            </div>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <flux:menu.radio.group>
                            <flux:menu.item :href="route('settings.profile')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                        </flux:menu.radio.group>

                        <flux:menu.separator />

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                                {{ __('Log Out') }}
                            </flux:menu.item>
                        </form>
                    </flux:menu>
                </flux:dropdown>
            @endauth
        </flux:header>


        {{ $slot }}

        

        @vite('resources/js/app.js')
        @stack('scripts')
        @fluxScripts
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-in-new-tab', (event) => {
                    window.open(event.url, '_blank');
                });
            });
        </script>
    </body>
</html>