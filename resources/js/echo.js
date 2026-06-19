import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const pusherKey     = import.meta.env.VITE_PUSHER_APP_KEY     || '';
const pusherCluster = import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1';

if (pusherKey) {
    window.Echo = new Echo({
        broadcaster:  'pusher',
        key:          pusherKey,
        cluster:      pusherCluster,
        forceTLS:     true,
        authEndpoint: '/broadcasting/auth',
        auth: {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
        },
    });

    document.dispatchEvent(new CustomEvent('echo:ready'));
}
