<div class="min-h-screen flex items-center justify-center bg-white dark:bg-gray-900 p-4 relative">
    
    <!-- Enhanced animated background shapes with multiple colors - Behind everything -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
        <div class="absolute -top-40 -right-40 w-[800px] h-[800px] bg-gradient-to-br from-emerald-400/30 to-teal-400/30 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-40 -left-40 w-[800px] h-[800px] bg-gradient-to-br from-teal-400/30 to-cyan-400/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-cyan-300/20 to-emerald-300/20 rounded-full blur-3xl"></div>
    </div>

    <!-- Full Screen Card - Layer above blobs -->
    <div class="w-full h-full absolute inset-0 z-10">
        <div class="h-full flex flex-col bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm overflow-y-auto">
            
            <!-- Header Section with Vibrant Gradient - Responsive with smaller sizes on mobile -->
            <div class="bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-600 px-4 py-4 md:px-8 md:py-16 text-center relative overflow-hidden">
                <!-- Decorative pattern overlay -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white rounded-full -translate-y-16 translate-x-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white rounded-full translate-y-12 -translate-x-12"></div>
                </div>
                
                @if(config('settings.app_logo_path'))
                    <div class="flex justify-center mb-2 md:mb-6 relative z-10">
                        <div class="bg-white rounded-xl md:rounded-2xl p-2 md:p-5 shadow-2xl ring-2 md:ring-4 ring-white/30">
                            <img src="{{ asset('storage/' . config('settings.app_logo_path')) }}" 
                                 alt="{{ config('settings.app_name', 'App Logo') }}" 
                                 class="w-12 h-12 sm:w-16 sm:h-16 md:w-24 md:h-24 object-contain">
                        </div>
                    </div>
                @endif
                
                <!-- Text - Smaller on mobile, larger on desktop -->
                <h1 class="text-xl sm:text-2xl md:text-4xl font-bold text-white mb-1 md:mb-3 relative z-10 drop-shadow-lg">
                    {{ config('settings.app_name', 'Muazara') }}
                </h1>
                <p class="text-xs sm:text-sm md:text-base text-emerald-50 relative z-10">
                    {{ __('Professional Pharmacy Management System') }}
                </p>
            </div>

            <!-- Form Section - Centered in Remaining Space with adjusted padding -->
            <div class="flex-1 flex items-center justify-center p-4 sm:p-6 md:p-8 relative z-50">
                <div class="w-full max-w-md space-y-4 sm:space-y-6">
                    
                    <!-- Welcome Message with Gradient Text -->
                    <div class="text-center">
                        <h2 class="text-2xl font-semibold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
                            {{ __('Welcome Back') }}
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ __('Please sign in to continue') }}
                        </p>
                    </div>

                    <!-- Session Status -->
                    <x-auth-session-status :status="session('status')" />

                    <!-- Login Form -->
                    <form wire:submit="login" class="space-y-5">
                        
                        <!-- Account Selection -->
                        <div class="space-y-2">
                            <flux:select
                                wire:model="email"
                                :label="__('Account')"
                                required
                                autofocus
                            >
                                <option value="" disabled>{{ __('Select your account') }}</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->email }}">{{ $user->name }}</option>
                                @endforeach
                            </flux:select>
                        </div>

                        <!-- Password -->
                        <div class="space-y-2">
                            <flux:input
                                wire:model="password"
                                :label="__('Password')"
                                type="password"
                                required
                                autocomplete="current-password"
                                :placeholder="__('Enter your password')"
                                viewable
                            />
                        </div>

                        <!-- Submit Button with Multi-Color Gradient -->
                        <div class="pt-3">
                            <flux:button 
                                variant="primary" 
                                type="submit" 
                                class="w-full h-12 text-base font-semibold bg-gradient-to-r from-emerald-500 via-teal-500 to-cyan-600 hover:from-emerald-600 hover:via-teal-600 hover:to-cyan-700 transition-all duration-300 shadow-lg hover:shadow-2xl hover:shadow-emerald-500/50"
                            >
                                <span class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __("Sign In") }}
                                </span>
                            </flux:button>
                        </div>
                    </form>

                    <!-- Footer Info with Colorful Accents -->
                    <div class="text-center space-y-3 pt-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ __('Secure login protected by encryption') }}
                        </p>
                        <div class="flex items-center justify-center gap-1 text-xs">
                            <div class="flex items-center gap-1 px-2 py-1 rounded-full bg-gradient-to-r from-emerald-100 to-teal-100 dark:from-emerald-900/30 dark:to-teal-900/30">
                                <svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                                </svg>
                                <span class="text-emerald-700 dark:text-emerald-300 font-medium">SSL Secured Connection</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Footer - Full Width -->
            <div class="bg-gradient-to-r from-emerald-50 via-teal-50 to-cyan-50 dark:from-gray-900/50 dark:via-emerald-900/20 dark:to-teal-900/20 px-8 py-4 border-t-2 border-emerald-200 dark:border-emerald-800">
                <div class="max-w-md mx-auto">
                    <p class="text-center text-xs text-gray-600 dark:text-gray-400">
                        &copy; {{ date('Y') }} {{ config('settings.app_name', 'Muazara') }}. All rights reserved.
                    </p>
                    <p class="text-center text-xs bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent font-medium mt-2">
                        {{ __('Need help? Contact your system administrator') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
