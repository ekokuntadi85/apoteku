<div class="flex flex-col items-center justify-center min-h-screen p-4">
    <div class="w-full sm:max-w-sm p-8 space-y-6 bg-white dark:bg-gray-800 rounded-lg shadow-2xl border border-gray-200 dark:border-gray-700">
        @if(config('settings.app_logo_path'))
            <div class="flex justify-center mb-4">
                <img src="{{ asset('storage/' . config('settings.app_logo_path')) }}" alt="{{ config('settings.app_name', 'App Logo') }}" class="w-25 h-25 object-contain">
            </div>
        @endif
        <x-auth-header :title="__('Login ' . config('settings.app_name', 'Muazara-App'))" :description="__('Pilih Nama dan masukkan passwordmu')" />

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form wire:submit="login" class="flex flex-col gap-6">
            <!-- Email Address -->
            <flux:select
                wire:model="email"
                :label="__('Pilih Akunmu!')"
                required
                autofocus
            >
                <option value="" disabled>{{ __('Select a user') }}</option>
                @foreach ($users as $user)
                    <option value="{{ $user->email }}">{{ $user->name }}</option>
                @endforeach
            </flux:select>

            <!-- Password -->
            <div class="relative">
                <flux:input
                    wire:model="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                
            </div>

            <!-- Remember Me -->
            {{-- <flux:checkbox wire:model="remember" :label="__('Remember me')" /> --}}

            <div class="flex items-center justify-end">
                <flux:button variant="primary" color="blue" type="submit" class="w-full">{{ __("Log in") }}</flux:button>
            </div>
        </form>

        
    </div>
</div>
