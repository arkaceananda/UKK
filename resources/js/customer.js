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
    initLazyImages();
    initThemeSystem();
});

document.addEventListener('livewire:navigated', () => {
    initLazyImages();
});

function initLazyImages() {
    if (! ('IntersectionObserver' in window)) {
        document.querySelectorAll('img[data-src]').forEach((img) => {
            img.src = img.dataset.src;
            img.classList.add('loaded');
            img.removeAttribute('data-src');
        });

        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;

                img.onload = () => {
                    img.classList.add('loaded');
                };

                img.onerror = () => {
                    img.classList.add('error');
                    img.style.background = '#1E2229';
                };

                img.removeAttribute('data-src');
                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '300px',
        threshold: 0.01,
    });

    document.querySelectorAll('img[data-src]').forEach((img) => {
        observer.observe(img);
    });
}

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