<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        
    </head>
    <body class="min-h-screen bg-gradient-to-br from-emerald-50 via-teal-50 to-cyan-50 dark:from-gray-950 dark:via-emerald-950 dark:to-teal-950 antialiased relative overflow-hidden">
        
        <!-- Enhanced animated background shapes with multiple colors -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-40 -right-40 w-[800px] h-[800px] bg-gradient-to-br from-emerald-400/30 to-teal-400/30 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute -bottom-40 -left-40 w-[800px] h-[800px] bg-gradient-to-br from-teal-400/30 to-cyan-400/30 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-br from-cyan-300/20 to-emerald-300/20 rounded-full blur-3xl"></div>
        </div>

        <div class="flex min-h-svh flex-col items-center justify-center gap-6 p-6 md:p-10 relative z-10">
            <div class="flex w-full max-w-sm flex-col gap-2">
                @if(!request()->routeIs('login'))
                <a href="{{ route('dashboard') }}" class="flex flex-col items-center gap-2 font-medium" wire:navigate>
                    <span class="flex h-9 w-9 mb-1 items-center justify-center rounded-md">
                        <x-app-logo-icon class="size-9 fill-current text-black dark:text-white" />
                    </span>
                    <span class="sr-only">{{ config('app.name', 'Laravel') }}</span>
                </a>
                @endif
                <div class="flex flex-col gap-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
        @fluxScripts
    </body>
</html>
