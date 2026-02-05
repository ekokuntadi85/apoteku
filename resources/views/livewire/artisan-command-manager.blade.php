<div class="container mx-auto p-6 dark:bg-gray-900 min-h-screen" x-data="{ 
    showConfirmModal: false, 
    pendingCommand: null, 
    pendingCommandName: null,
    confirmCommand(command, name, isDangerous) {
        if (isDangerous) {
            this.pendingCommand = command;
            this.pendingCommandName = name;
            this.showConfirmModal = true;
        } else {
            $wire.runCommand(command, name);
        }
    },
    executeCommand() {
        if (this.pendingCommand) {
            $wire.runCommand(this.pendingCommand, this.pendingCommandName);
            this.showConfirmModal = false;
            this.pendingCommand = null;
            this.pendingCommandName = null;
        }
    },
    cancelCommand() {
        this.showConfirmModal = false;
        this.pendingCommand = null;
        this.pendingCommandName = null;
    }
}">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 mb-2">
            Artisan Command Manager
        </h1>
        <p class="text-gray-600 dark:text-gray-400">Execute Laravel Artisan commands with a single click</p>
    </div>

    <!-- Alert Messages -->
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-transition
             x-init="setTimeout(() => show = false, 3000)"
             class="mb-6 bg-green-100 dark:bg-green-900 border-l-4 border-green-500 dark:border-green-400 text-green-700 dark:text-green-200 p-4 rounded dark:bg-green-900/30 dark:text-green-300"
             role="alert">
            <p class="font-bold">Success!</p>
            <p>{{ session('message') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column: Commands -->
        <div class="lg:col-span-2 space-y-6">
            @foreach($commands as $category => $categoryCommands)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">{{ $category }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($categoryCommands as $cmd)
                                <div class="relative group">
                                    <button 
                                        @click="confirmCommand('{{ $cmd['command'] }}', '{{ $cmd['name'] }}', {{ isset($cmd['dangerous']) && $cmd['dangerous'] ? 'true' : 'false' }})"
                                        wire:loading.attr="disabled"
                                        class="w-full text-left p-4 rounded-lg border-2 transition-all duration-200
                                               {{ isset($cmd['dangerous']) && $cmd['dangerous'] 
                                                   ? 'border-red-300 hover:border-red-500 hover:bg-red-50 dark:border-red-700 dark:hover:bg-red-900/20' 
                                                   : 'border-gray-200 hover:border-indigo-500 hover:bg-indigo-50 dark:border-gray-700 dark:hover:bg-indigo-900/20' }}
                                               disabled:opacity-50 disabled:cursor-not-allowed">
                                        <div class="flex items-start space-x-3">
                                            <span class="text-2xl">{{ $cmd['icon'] }}</span>
                                            <div class="flex-1">
                                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                                    {{ $cmd['name'] }}
                                                </h3>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    {{ $cmd['description'] }}
                                                </p>
                                                <code class="text-xs text-gray-400 dark:text-gray-500 mt-2 block">
                                                    php artisan {{ $cmd['command'] }}
                                                </code>
                                            </div>
                                            @if(isset($cmd['dangerous']) && $cmd['dangerous'])
                                                <span class="text-xs bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 px-2 py-1 rounded">
                                                    Caution
                                                </span>
                                            @endif
                                        </div>
                                    </button>
                                    
                                    <!-- Loading Indicator -->
                                    <div wire:loading wire:target="runCommand('{{ $cmd['command'] }}', '{{ $cmd['name'] }}')"
                                         class="absolute inset-0 bg-white/80 dark:bg-gray-800/80 rounded-lg flex items-center justify-center">
                                        <div class="flex items-center space-x-2">
                                            <svg class="animate-spin h-5 w-5 text-indigo-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-600 dark:text-gray-300">Running...</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Right Column: Output & History -->
        <div class="space-y-6">
            <!-- Command Output -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden sticky top-6">
                <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Command Output</h2>
                    @if($commandOutput)
                        <button wire:click="clearOutput" 
                                class="text-white hover:text-gray-200 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
                <div class="p-6">
                    @if($lastCommand)
                        <div class="mb-4 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Last Command:</p>
                            <p class="font-semibold text-gray-900 dark:text-white">{{ $lastCommand }}</p>
                        </div>
                    @endif

                    @if($commandOutput)
                        <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                            <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ $commandOutput }}</pre>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p>No command executed yet</p>
                            <p class="text-sm mt-2">Click a command to see its output</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Command History -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-purple-500 to-pink-600 px-6 py-4 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Command History</h2>
                    @if(count($commandHistory) > 0)
                        <button wire:click="clearHistory" 
                                class="text-white hover:text-gray-200 transition-colors text-sm">
                            Clear All
                        </button>
                    @endif
                </div>
                <div class="p-6">
                    @if(count($commandHistory) > 0)
                        <div class="space-y-3">
                            @foreach($commandHistory as $history)
                                <div class="flex items-start space-x-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                                    <span class="text-lg">
                                        @if($history['status'] === 'success')
                                            ✅
                                        @else
                                            ❌
                                        @endif
                                    </span>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-900 dark:text-white truncate">
                                            {{ $history['name'] }}
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $history['timestamp'] }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm">No commands executed yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Warning Footer -->
    <div class="mt-8 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                    <strong>Warning:</strong> Some commands can affect your application's functionality. Commands marked with "Caution" should be used carefully, especially in production environments.
                </p>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div x-show="showConfirmModal" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <!-- Background overlay -->
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-80 transition-opacity" 
             @click="cancelCommand()"></div>

        <!-- Modal panel -->
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <!-- Warning Icon -->
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                        
                        <!-- Content -->
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left flex-1">
                            <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">
                                Confirm Dangerous Operation
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    You are about to execute:
                                </p>
                                <p class="mt-2 text-base font-semibold text-gray-900 dark:text-white" x-text="pendingCommandName"></p>
                                <p class="mt-3 text-sm text-red-600 dark:text-red-400">
                                    ⚠️ This is a potentially dangerous operation that may affect your application's data or functionality. This action cannot be undone.
                                </p>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                    Are you sure you want to proceed?
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="bg-gray-50 dark:bg-gray-700/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-3">
                    <button type="button" 
                            @click="executeCommand()"
                            class="inline-flex w-full justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 dark:focus:ring-offset-gray-800 sm:w-auto transition-colors">
                        Yes, Execute Command
                    </button>
                    <button type="button" 
                            @click="cancelCommand()"
                            class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-600 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-500 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 sm:mt-0 sm:w-auto transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
