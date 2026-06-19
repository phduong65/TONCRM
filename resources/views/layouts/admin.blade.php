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
        window.TENANT_ID = '{{ auth()->user()->tenant_id }}';
    </script>

    @if(config('services.pusher_beams.instance_id'))
    <script src="https://js.pusher.com/beams/1.0/push-notifications-cdn.js"></script>
    <script>
    (function () {
        if (typeof PusherPushNotifications === 'undefined') return;

        const beamsClient = new PusherPushNotifications.Client({
            instanceId: '{{ config('services.pusher_beams.instance_id') }}',
        });

        beamsClient.start()
            .then(() => beamsClient.addDeviceInterest('tenant-' + window.TENANT_ID))
            .catch(function (err) {
                // Silently ignore — Beams requires HTTPS + granted permission
                // Will work on production (HTTPS). No action needed on HTTP local dev.
            });
    })();
    </script>
    @endif

    @stack('scripts')
</body>
</html>
