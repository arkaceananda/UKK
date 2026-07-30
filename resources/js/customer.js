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