<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BurjoOrder') }} - Menu</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=jetbrains-mono:400;500&family=plus-jakarta-sans:400;500;600;700&family=space-grotesk:600;700&display=swap" rel="stylesheet" />

    {{-- Critical inline styles to prevent FOUC while CSS loads --}}
    <style>
        html {
            color-scheme: light dark;
        }
        html.dark {
            background-color: #14171B;
            color: #EDE7DA;
        }
        html:not(.dark) {
            background-color: #F6F1E7;
            color: #14171B;
        }
        body {
            background-color: inherit;
            color: inherit;
        }
    </style>

    @vite(['resources/css/customer.css', 'resources/js/customer.js'])

    <script>
        (function() {
            // Clear any old theme preference from localStorage
            localStorage.removeItem('theme');
            
            // Apply theme based on system preference only
            function applyTheme() {
                var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (prefersDark) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            // Apply immediately before rendering to prevent flash
            applyTheme();

            // Listen for system preference changes
            var mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', applyTheme);
            } else if (mediaQuery.addListener) {
                mediaQuery.addListener(applyTheme);
            }
        })();
    </script>
</head>
<body class="font-body text-arang bg-paper dark:text-kertas dark:bg-ink transition-colors duration-200 min-h-screen flex flex-col">
    <div class="flex-1 flex flex-col mx-auto w-full max-w-lg lg:max-w-4xl">
        <header class="bg-paper dark:bg-ink border-b border-border-light dark:border-border-dark px-4 py-3">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-display text-lg font-bold text-arang dark:text-kertas">BurjoOrder</h1>
                </div>
                <div class="flex items-center gap-2"></div>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto px-4 py-4 md:px-6 lg:px-8" id="main-content">
            {{ $slot ?? '' }}
        </main>
    </div>

    <script>
        function applyTheme() {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', prefersDark);
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

        initLazyImages();
        applyTheme();

        window.addEventListener('refresh-images', initLazyImages);

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