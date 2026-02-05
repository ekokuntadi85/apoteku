<div class="container mx-auto p-4 dark:bg-gray-800 dark:text-gray-200">
    <h1 class="text-3xl font-bold mb-4 bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-fuchsia-500 to-rose-500">Pengeluaran Operasional</h1>
    
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 space-y-4 md:space-y-0 gap-4">
        <!-- Search -->
        <div class="relative w-full md:w-1/4">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <input type="text" wire:model.live.debounce="search" placeholder="Cari deskripsi..." class="shadow appearance-none border rounded py-2 pl-10 pr-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline w-full dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
        </div>

        <!-- Filters -->
         <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
            <input type="date" wire:model.live="startDate" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            <span class="text-gray-500 self-center hidden md:block">-</span>
            <input type="date" wire:model.live="endDate" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
            
            <select wire:model.live="categoryId" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600">
                <option value="all">Semua Kategori</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
         </div>

        <!-- Add Button -->
        <a href="{{ route('expenses.create') }}" class="inline-flex items-center justify-center bg-gradient-to-r from-indigo-500 to-fuchsia-500 hover:from-indigo-600 hover:to-fuchsia-600 text-white font-semibold py-2 px-6 rounded-lg shadow-md border-b-4 border-indigo-700 hover:border-indigo-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-400 w-full md:w-auto md:ml-4 transition-all transform active:scale-95 active:border-b-0 active:mt-1" wire:navigate>
            Tambah Pengeluaran
        </a>
    </div>

    <!-- Desktop Table -->
    <div class="hidden md:block shadow overflow-hidden border border-gray-200 sm:rounded-xl dark:border-gray-700 bg-white/70 dark:bg-gray-800/60 backdrop-blur">
         <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gradient-to-r from-indigo-50 to-fuchsia-50 dark:from-zinc-800 dark:to-zinc-800">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Oleh</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider dark:text-gray-300">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                    @foreach($expenses as $expense)
                    <tr class="hover:bg-indigo-50/60 dark:hover:bg-zinc-800/70 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-gray-200">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-gray-900 dark:text-gray-200">{{ $expense->description }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300 border border-gray-500">{{ $expense->category->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold text-rose-600 dark:text-rose-400">
                            Rp {{ number_format($expense->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $expense->user->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('expenses.edit', $expense) }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-1.5 px-3 rounded-full dark:bg-emerald-600 dark:hover:bg-emerald-700" wire:navigate>Edit</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
         </div>
    </div>
    
    <!-- Mobile Card View -->
    <div class="block md:hidden space-y-4">
        @forelse($expenses as $expense)
        <div class="bg-white/80 dark:bg-gray-700/70 shadow-md rounded-xl p-4 border border-gray-200/70 dark:border-gray-600/60 backdrop-blur">
             <div class="flex justify-between items-start mb-2">
                <div>
                    <h3 class="text-sm text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d M Y') }}</h3>
                    <p class="font-bold text-gray-900 dark:text-white">{{ $expense->description }}</p>
                </div>
                <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">{{ $expense->category->name }}</span>
            </div>
            <div class="flex justify-between items-center mt-2">
                 <span class="font-mono font-bold text-rose-600 dark:text-rose-400">Rp {{ number_format($expense->amount, 0, ',', '.') }}</span>
                 <a href="{{ route('expenses.edit', $expense) }}" class="bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-1 px-3 rounded-full text-xs dark:bg-emerald-600 dark:hover:bg-emerald-700" wire:navigate>Edit</a>
            </div>
             <div class="text-xs text-gray-400 mt-2">Oleh: {{ $expense->user->name }}</div>
        </div>
        @empty
             <p class="text-gray-600 dark:text-gray-400 text-center">Tidak ada pengeluaran ditemukan.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>
