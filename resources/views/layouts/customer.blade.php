<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BurjoOrder') }} - Menu</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400;500&family=plus-jakarta-sans:400;500;600;700&family=space-grotesk:600;700&display=swap" rel="stylesheet" />

    <script>
        (function() {
            var stored = localStorage.getItem('theme');
            var system = window.matchMedia('(prefers-color-scheme: dark)').matches;
            var theme = stored || (system ? 'dark' : 'light');
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        })();
    </script>

    @vite(['resources/css/customer.css', 'resources/js/customer.js'])
</head>
<body class="font-body text-arang bg-paper dark:text-kertas dark:bg-ink transition-colors duration-200 min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col mx-auto w-full max-w-lg lg:max-w-4xl">
        <header class="sticky top-0 z-40 bg-paper/80 dark:bg-surface/80 backdrop-blur-md border-b border-border-light dark:border-border-dark px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-display text-lg font-bold text-ink dark:text-kertas">BurjoOrder</h1>
                </div>
                <div class="flex items-center gap-2">
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-surface transition-colors" aria-label="Tema">
                            <svg class="theme-icon-dark hidden w-5 h-5 text-amber-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 0 018 0z"/></svg>
                            <svg class="theme-icon-light w-5 h-5 text-slate-700" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2m-7.07-11.07l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2m-13.66 3.66l-1.41 1.41M19.07 6.34l-1.41 1.41"/></svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-paper-card dark:bg-surface rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 z-50">
                            <button @click="setTheme('light'); open = false" class="w-full text-left px-4 py-2 text-sm text-arang dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors">Terang</button>
                            <button @click="setTheme('dark'); open = false" class="w-full text-left px-4 py-2 text-sm text-arang dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors">Gelap</button>
                            <button @click="setTheme('system'); open = false" class="w-full text-left px-4 py-2 text-sm text-arang dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors">Sistem</button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto px-4 py-4 md:px-6 lg:px-8" id="main-content">
            {{ $slot ?? '' }}
        </main>
    </div>

    <script>
        function setTheme(theme) {
            if (theme === 'system') {
                localStorage.removeItem('theme');
            } else {
                localStorage.setItem('theme', theme);
            }
            document.documentElement.classList.toggle('dark', theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches));
            initThemeSystem();
        }

        document.addEventListener('livewire:navigated', function() {
            initLazyImages();
        });

        function initLazyImages() {
            document.querySelectorAll('img[data-src]').forEach(function(img) {
                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                img.src = img.dataset.src;
                                img.onload = function() { img.classList.add('loaded'); };
                                img.onerror = function() {
                                    img.classList.add('error');
                                    img.style.background = '#1E2229';
                                };
                                img.removeAttribute('data-src');
                                observer.unobserve(img);
                            }
                        });
                    }, { rootMargin: '300px', threshold: 0.01 });
                    observer.observe(img);
                } else {
                    img.src = img.dataset.src;
                    img.classList.add('loaded');
                    img.removeAttribute('data-src');
                }
            });
        }

        function initThemeSystem() {
            var isDark = document.documentElement.classList.contains('dark');
            document.querySelectorAll('.theme-icon-dark').forEach(function(el) {
                el.classList.toggle('hidden', !isDark);
            });
            document.querySelectorAll('.theme-icon-light').forEach(function(el) {
                el.classList.toggle('hidden', isDark);
            });
        }

        initLazyImages();
        initThemeSystem();

        window.addEventListener('notify', function(e) {
            var detail = e.detail;
            if (detail && detail.message) {
                var toast = document.createElement('div');
                toast.className = 'fixed top-4 right-4 z-[9999] px-4 py-3 rounded-xl shadow-lg text-sm font-medium ' +
                    (detail.type === 'error' ? 'bg-cabai text-white' : 'bg-daun text-ink');
                toast.textContent = detail.message;
                document.body.appendChild(toast);
                setTimeout(function() { toast.remove(); }, 3000);
            }
        });
    </script>
</body>
</html>