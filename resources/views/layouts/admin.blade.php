<!DOCTYPE html>
<html lang="vi" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TonCRM') — TonCRM</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full flex overflow-hidden bg-zinc-50">

    {{-- Left Navigation Rail --}}
    @include('components.sidebar')

    {{-- Main Content Area --}}
    <main class="@yield('main-class', 'flex-1 overflow-y-auto') min-w-0 relative">
        @include('components.flash-messages')
        @yield('content')
    </main>

    @stack('modals')

    @php
        // Derive client-side WebSocket vars from APP_URL so Railway (HTTPS) works automatically.
        // Server-side Reverb config (REVERB_HOST/PORT) stays as-is for internal broadcasting.
        $appUrl      = config('app.url', 'http://localhost:8000');
        $isHttps     = str_starts_with($appUrl, 'https://');
        $wsHost      = parse_url($appUrl, PHP_URL_HOST) ?? 'localhost';
        $wsPort      = $isHttps ? 443 : (int) config('reverb.apps.apps.0.options.port', 8080);
        $wsScheme    = $isHttps ? 'https' : 'http';
    @endphp
    <script>
        window.TENANT_ID      = '{{ auth()->user()->tenant_id }}';
        window.REVERB_HOST    = '{{ $wsHost }}';
        window.REVERB_PORT    = {{ $wsPort }};
        window.REVERB_SCHEME  = '{{ $wsScheme }}';
        window.REVERB_APP_KEY = '{{ config('reverb.apps.apps.0.key', '') }}';
    </script>
    @stack('scripts')
</body>
</html>
