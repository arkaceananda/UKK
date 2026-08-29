import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
});

window.Echo.channel('stock-updates')
    .listen('StockUpdated', (e) => {
        window.dispatchEvent(new CustomEvent('refreshStock', { detail: e }));
    });

document.addEventListener('livewire:initialized', () => {
    initThemeSystem();
});

document.addEventListener('livewire:navigated', () => {
    initThemeSystem();
});

function initThemeSystem() {
    const theme = localStorage.getItem('theme') || 'system';

    function applyTheme(t) {
        if (t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }

    applyTheme(theme);

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (theme === 'system') {
            applyTheme('system');
        }
    });
}