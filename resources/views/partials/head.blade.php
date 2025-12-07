<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>


@if(config('settings.app_logo_path'))
    {{-- Use app logo as favicon if available --}}
    <link rel="icon" href="{{ asset('storage/' . config('settings.app_logo_path')) }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('storage/' . config('settings.app_logo_path')) }}">
@else
    {{-- Fallback to default favicon --}}
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">
@endif


<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800&display=swap" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
