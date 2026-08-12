<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Auto-detect tema sistem, taruh SEBELUM @vite supaya nggak ada
         "flash" warna salah sesaat sebelum CSS/JS selesai load --}}
    <script>
        if (
            localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        }
    </script>
    {{-- Lottie Player CDN for login page animation --}}
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
{{--
    TIDAK ADA lagi wrapper "min-h-screen flex justify-center items-center"
    + card "max-w-md" di sini seperti bawaan Breeze. Slot ({{ $slot }})
    sekarang mengontrol PENUH layout halamannya sendiri — itu kenapa
    login-split.blade.php bisa full-width split-screen tanpa ke-squeeze.
--}}
<body class="h-full font-body antialiased bg-paper dark:bg-ink">
    {{ $slot }}
</body>
</html>