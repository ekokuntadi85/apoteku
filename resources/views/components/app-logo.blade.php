@if(config('settings.app_logo_path'))
    <img src="{{ asset('storage/' . config('settings.app_logo_path')) }}" alt="{{ config('settings.app_name', 'App Logo') }}" class="h-8 w-auto">
@else
    <div class="flex aspect-square size-8 items-center justify-center rounded-md bg-accent-content text-accent-foreground">
        <x-app-logo-icon class="size-5 fill-current text-white dark:text-black" />
    </div>
@endif
<div class="ms-1 grid flex-1 text-start text-sm">
    <span class="mb-0.5 truncate leading-tight font-semibold">{{ config('settings.app_name', 'Muazara-App') }}</span>
</div>
