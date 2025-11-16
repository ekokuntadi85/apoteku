<section class="w-full dark:bg-gray-800 dark:text-gray-200">
    <form wire:submit.prevent="save">
        <x-settings.layout>
            <x-slot name="heading">
                {{ __('Appearance') }}
            </x-slot>
            <x-slot name="subheading">
                {{ __('Manage your application appearance settings.') }}
            </x-slot>

            <div class="space-y-4">
                <div>
                    <label for="appName" class="block text-sm font-medium text-gray-700 dark:text-gray-200">App Name</label>
                    <input type="text" wire:model.defer="appName" id="appName" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('appName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Address</label>
                    <textarea wire:model.defer="address" id="address" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"></textarea>
                    @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="phoneNumber" class="block text-sm font-medium text-gray-700 dark:text-gray-200">Phone Number</label>
                    <input type="text" wire:model.defer="phoneNumber" id="phoneNumber" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                    @error('phoneNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label for="appLogo" class="block text-sm font-medium text-gray-700 dark:text-gray-200">App Logo</label>
                    <div class="mt-1 flex items-center space-x-4">
                        @if ($existingLogo)
                            <img src="{{ asset('storage/' . $existingLogo) }}" alt="Current Logo" class="h-12 w-12 rounded-full object-cover">
                        @endif
                        <input type="file" wire:model="appLogo" id="appLogo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200">
                    </div>
                    <div wire:loading wire:target="appLogo" class="mt-2 text-sm text-gray-500">Uploading...</div>
                    @error('appLogo') <span class="text-red-500 text-xs mt-2">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end space-x-4">
                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Save
                </button>
                <div wire:loading wire:target="save" class="text-sm text-gray-500">
                    Saving...
                </div>
                <div x-data="{ show: false }" x-init="Livewire.on('saved', () => { show = true; setTimeout(() => show = false, 2000) })" x-show="show" x-transition class="text-sm text-gray-600 dark:text-gray-300">
                    Saved.
                </div>
            </div>
        </x-settings.layout>
    </form>

    <x-settings.layout :heading="__('Theme')" :subheading=" __('Select your preferred color scheme.')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
