import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Build-time vars (local dev via Vite) take priority; fall back to runtime
// window.REVERB_* vars set by the PHP layout for production (Railway etc.)
const reverbKey    = import.meta.env.VITE_REVERB_APP_KEY || window.REVERB_APP_KEY || '';
const reverbHost   = import.meta.env.VITE_REVERB_HOST    || window.REVERB_HOST    || 'localhost';
const reverbPort   = Number(import.meta.env.VITE_REVERB_PORT   || window.REVERB_PORT   || 8080);
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME  || window.REVERB_SCHEME  || 'http';

if (reverbKey) {
    window.Echo = new Echo({
        broadcaster:       'reverb',
        key:               reverbKey,
        wsHost:            reverbHost,
        wsPort:            reverbPort,
        wssPort:           reverbPort,
        forceTLS:          reverbScheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint:      '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        },
    });

    document.dispatchEvent(new CustomEvent('echo:ready'));
}
