<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'Admin Dashboard' }} - BurjoOrder</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700|jetbrains-mono:400,500,600|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    @livewireStyles
</head>
<body class="font-body antialiased bg-paper dark:bg-ink text-arang dark:text-kertas transition-colors duration-200">
    <div class="flex h-screen overflow-hidden" x-data="{ sidebarOpen: false }">
        {{-- Mobile Overlay --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="fixed inset-y-0 left-0 w-full bg-black/50 z-40 md:hidden"></div>

        {{-- Sidebar --}}
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="w-64 bg-paper-card dark:bg-surface border-r border-border-light dark:border-border-dark flex flex-col fixed inset-y-0 left-0 z-50 transition-transform duration-300 md:static md:z-auto"
        >
            <div class="p-6 border-b border-border-light dark:border-border-dark flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-display font-bold text-arang dark:text-paper">BurjoOrder</h1>
                    <p class="text-xs text-muted-dark dark:text-muted-light font-medium">Admin Control Panel</p>
                </div>
                <button @click="sidebarOpen = false" class="md:hidden text-muted-dark hover:text-ink dark:text-muted-light">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path></svg>
                    <span>Dashboard</span>
                </a>
                <a href="{{ Route::has('admin.recaps') ? route('admin.recaps') : '#' }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Recap</span>
                </a>
                <a href="#menu-manager" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span>Manajemen Menu</span>
                </a>
                <a href="#table-manager" class="flex items-center space-x-3 px-4 py-3 rounded-lg text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                    <span>Manajemen Meja</span>
                </a>
            </nav>

            <div class="p-4 border-t border-border-light dark:border-border-dark">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-cabai hover:bg-kertas dark:hover:bg-arang transition-colors text-left font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main Container --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            {{-- Topbar (Right-aligned items like Kasir layout) --}}
            <header class="bg-paper-card dark:bg-surface border-b border-border-light dark:border-border-dark px-4 md:px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden p-2 rounded-lg hover:bg-kertas dark:hover:bg-arang text-arang dark:text-kertas">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <h2 class="text-xl md:text-2xl font-display font-semibold text-arang dark:text-paper">{{ $title ?? 'Admin Dashboard' }}</h2>
                </div>

                <div class="flex items-center gap-4">
                    {{-- Theme Toggle Button --}}
                    <button 
                        id="theme-toggle"
                        type="button"
                        class="p-2 rounded-lg hover:bg-kertas dark:hover:bg-arang transition-colors text-arang dark:text-kertas"
                        title="Toggle Dark / Light Mode"
                    >
                        <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                        </svg>
                        <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                        </svg>
                    </button>

                    {{-- Realtime Clock --}}
                    <div 
                        id="realtime-clock"
                        class="font-mono text-sm hidden sm:block text-arang dark:text-kertas"
                    >
                        00:00:00
                    </div>

                    {{-- User Profile Info --}}
                    <div class="text-sm border-l border-border-light dark:border-border-dark pl-4">
                        <span class="font-medium text-arang dark:text-paper block leading-tight">{{ auth()->user()?->name ?? 'Admin' }}</span>
                        <span class="text-xs text-muted-dark dark:text-muted-light">Administrator</span>
                    </div>
                </div>
            </header>

            {{-- Main Scrollable Content --}}
            <main class="flex-1 overflow-y-auto bg-paper dark:bg-ink p-4 md:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script>
        const themeToggleBtn = document.getElementById('theme-toggle');
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            themeToggleLightIcon.classList.remove('hidden');
        } else {
            themeToggleDarkIcon.classList.remove('hidden');
        }

        themeToggleBtn.addEventListener('click', function() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');

            if (localStorage.getItem('theme')) {
                if (localStorage.getItem('theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                }
            } else {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }
        });

        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            const clockEl = document.getElementById('realtime-clock');
            if (clockEl) {
                clockEl.textContent = `${hours}:${minutes}:${seconds}`;
            }
        }

        updateClock();
        setInterval(updateClock, 1000);

        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container') || (() => {
                const c = document.createElement('div');
                c.id = 'toast-container';
                c.className = 'fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none';
                document.body.appendChild(c);
                return c;
            })();

            const toast = document.createElement('div');
            toast.className = 'px-4 py-3 rounded-xl shadow-lg text-sm font-semibold flex items-center gap-3 transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto max-w-sm';
            
            let bgClass = 'bg-paper-card dark:bg-surface text-arang dark:text-kertas border border-border-light dark:border-border-dark';
            let icon = '';

            if (type === 'success') {
                bgClass = 'bg-daun text-white';
                icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            } else if (type === 'error') {
                bgClass = 'bg-cabai text-white';
                icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            } else {
                bgClass = 'bg-gas text-white';
                icon = '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>';
            }

            toast.className += ' ' + bgClass;
            toast.innerHTML = `${icon}<span>${message}</span>`;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            setTimeout(() => {
                toast.classList.add('translate-y-[-8px]', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        window.addEventListener('success', e => showToast(e.detail.message || e.detail, 'success'));
        window.addEventListener('error', e => showToast(e.detail.message || e.detail, 'error'));
        window.addEventListener('notify', e => showToast(e.detail.message || e.detail, e.detail.type || 'info'));
    </script>
    @livewireScripts
</body>
</html>