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
<body class="font-body text-arang bg-paper dark:text-kertas dark:bg-ink transition-colors duration-200 min-h-screen">
    {{ $slot ?? '' }}
    @yield('content')

    <script>
        function applyTheme() {
            var prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', prefersDark);
        }

        document.addEventListener('livewire:navigated', function() {
            applyTheme();
        });

        applyTheme();

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