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

    <script>
        window.TENANT_ID      = '{{ auth()->user()->tenant_id }}';
        window.REVERB_HOST    = '{{ config('reverb.apps.apps.0.options.host', 'localhost') }}';
        window.REVERB_PORT    = {{ config('reverb.apps.apps.0.options.port', 8080) }};
        window.REVERB_APP_KEY = '{{ config('reverb.apps.apps.0.key', '') }}';
    </script>
    @stack('scripts')
</body>
</html>
