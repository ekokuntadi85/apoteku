<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
        @fluxAppearance
    </head>
	<body class="min-h-screen bg-white dark:bg-zinc-800">
		<flux:sidebar sticky stashable class="relative border-e border-zinc-200 bg-gradient-to-b from-indigo-50 via-fuchsia-50 to-rose-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />
            
            
			<a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse py-3 px-2 rounded-lg hover:bg-white/60 dark:hover:bg-zinc-800/60 transition-colors" wire:navigate>
                <x-app-logo />
            </a>

			<flux:navlist variant="outline" class="px-2">
				@can('access-dashboard')
				<flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('dashboard') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-indigo-400' : '' }}">
					<span class="inline-flex items-center gap-2">
						{{ __('Dashboard') }}
						<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Home</span>
					</span>
				</flux:navlist.item>
                @endcan
                
				<flux:navlist.group :heading="__('Master Data')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="layout-grid" :href="route('products.index')" :current="request()->routeIs('products.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('products.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-emerald-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Daftar Produk
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Data</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document-list" :href="route('categories.index')" :current="request()->routeIs('categories.index')" class="rounded-lg hover:bg-sky-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('categories.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-sky-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Kategori
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">Data</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document" :href="route('units.index')" :current="request()->routeIs('units.index')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('units.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-amber-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Satuan
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Data</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="building-office-2" :href="route('suppliers.index')" :current="request()->routeIs('suppliers.*')" class="rounded-lg hover:bg-violet-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('suppliers.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-violet-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Supplier
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">Relasi</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="users" :href="route('customers.index')" :current="request()->routeIs('customers.*')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('customers.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-rose-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Customer
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Relasi</span>
						</span>
					</flux:navlist.item>
                </flux:navlist.group>

				<flux:navlist.group :heading="__('Transaksi')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="credit-card" :href="route('purchases.index')" :current="request()->routeIs('purchases.*')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('purchases.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-indigo-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Daftar Pembelian
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Transaksi</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="currency-dollar" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" class="rounded-lg hover:bg-fuchsia-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('transactions.*') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-fuchsia-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Daftar Penjualan
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300">Transaksi</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="computer-desktop" :href="route('pos.index')" :current="request()->routeIs('pos.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('pos.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-emerald-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							POS
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Kasir</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="banknotes" :href="route('accounts-receivable.index')" :current="request()->routeIs('accounts-receivable.index')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('accounts-receivable.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-rose-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Daftar Invoice Kredit
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Piutang</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="adjustments-horizontal" :href="route('stock-opname.index')" :current="request()->routeIs('stock-opname.index')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('stock-opname.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-amber-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Stok Opname
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Stok</span>
						</span>
					</flux:navlist.item>
                </flux:navlist.group>

				<flux:navlist.group :heading="__('Laporan')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="chart-bar" :href="route('reports.sales')" :current="request()->routeIs('reports.sales')" class="rounded-lg hover:bg-sky-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('reports.sales') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-sky-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Laporan Penjualan
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300">Report</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="calendar-days" :href="route('reports.expiring-stock')" :current="request()->routeIs('reports.expiring-stock')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('reports.expiring-stock') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-indigo-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Laporan Stok Kedaluwarsa
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Report</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="arrow-down-circle" :href="route('reports.low-stock')" :current="request()->routeIs('reports.low-stock')" class="rounded-lg hover:bg-amber-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('reports.low-stock') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-amber-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Laporan Stok Menipis
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">Report</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="document-text" :href="route('stock-card.index')" :current="request()->routeIs('stock-card.index')" class="rounded-lg hover:bg-fuchsia-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('stock-card.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-fuchsia-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Kartu Stok
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300">Report</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="clipboard-document-list" :href="route('kartu-monitoring-suhu')" :current="request()->routeIs('kartu-monitoring-suhu')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('kartu-monitoring-suhu') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-rose-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Kartu Monitoring Suhu
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Report</span>
						</span>
					</flux:navlist.item>
                </flux:navlist.group>

                @can('manage-users')
				<flux:navlist.group :heading="__('Pengaturan Sistem')" expandable :expanded="false" class="mt-2">
					<flux:navlist.item icon="users" :href="route('users.index')" :current="request()->routeIs('users.index')" class="rounded-lg hover:bg-violet-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('users.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-violet-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Manajemen Pengguna
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300">Admin</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="server" :href="route('database.backup')" :current="request()->routeIs('database.backup')" class="rounded-lg hover:bg-indigo-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('database.backup') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-indigo-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Manajemen Backup
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">Admin</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="server" :href="route('database.restore')" :current="request()->routeIs('database.restore')" class="rounded-lg hover:bg-rose-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('database.restore') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-rose-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Restore Database
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300">Admin</span>
						</span>
					</flux:navlist.item>
					<flux:navlist.item icon="shield-check" :href="route('stock-consistency.index')" :current="request()->routeIs('stock-consistency.index')" class="rounded-lg hover:bg-emerald-50/70 dark:hover:bg-zinc-800/70 transition-colors {{ request()->routeIs('stock-consistency.index') ? 'bg-white/70 dark:bg-zinc-800/60 border-l-4 border-emerald-400' : '' }}" wire:navigate>
						<span class="inline-flex items-center gap-2">
							Integritas Stok
							<span class="hidden md:inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">Admin</span>
						</span>
					</flux:navlist.item>
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