<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} - Kasir</title>

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
    </head>
    <body class="font-body antialiased bg-paper dark:bg-ink text-arang dark:text-kertas transition-colors duration-200">
        <div class="flex h-screen overflow-hidden" x-data="{ confirmLogout: false }">
            <aside class="w-64 bg-paper-card dark:bg-surface border-r border-border-light dark:border-border-dark flex flex-col">
                <div class="p-6 border-b border-border-light dark:border-border-dark">
                    <h1 class="text-xl font-display font-bold text-arang dark:text-paper">BurjoOrder</h1>
                    <p class="text-sm text-muted-dark dark:text-muted-light">Kasir Dashboard</p>
                </div>

                <nav class="flex-1 p-4 space-y-2">
                    <a href="{{ route('kasir.dashboard') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('kasir.dashboard') ? 'bg-accent text-white' : 'text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang' }} transition-colors">
                        Dashboard
                    </a>
                    <a href="{{ route('kasir.history') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('kasir.history') ? 'bg-accent text-white' : 'text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang' }} transition-colors">
                        Order History
                    </a>
                    <a href="{{ route('kasir.manual-order') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('kasir.manual-order') ? 'bg-accent text-white' : 'text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang' }} transition-colors">
                        Manual Order
                    </a>
                    <a href="{{ route('kasir.meja-qr') }}" class="block px-4 py-3 rounded-lg {{ request()->routeIs('kasir.meja-qr') ? 'bg-accent text-white' : 'text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang' }} transition-colors">
                        QR Code Meja
                    </a>
                </nav>

                <div class="p-4 border-t border-border-light dark:border-border-dark">
                    <button
                        type="button"
                        @click="confirmLogout = true"
                        class="w-full px-4 py-3 text-left rounded-lg text-cabai hover:bg-kertas dark:hover:bg-arang transition-colors"
                    >
                        Logout
                    </button>
                </div>
            </aside>

            <div class="flex-1 flex flex-col overflow-hidden">
                <header class="bg-paper-card dark:bg-surface border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-display font-semibold text-arang dark:text-paper">{{ $pageTitle ?? 'Dashboard' }}</h2>
                    </div>

                    <div class="flex items-center gap-4">
                        <button 
                            id="sound-toggle-btn"
                            type="button"
                            @click="toggleSound()"
                            :class="localStorage.getItem('kasir_sound_enabled') !== 'false' 
                                ? 'bg-accent text-white' 
                                : 'bg-paper-card dark:bg-surface text-arang dark:text-kertas'
                            "
                            class="p-2 rounded-lg hover:bg-kertas dark:hover:bg-arang transition-colors flex items-center justify-center"
                            title="Toggle sound notifications"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1a1 1 0 011 1v3.586a1 1 0 01-.293.707l5.586 5.587a1 1 0 001.414 0l5.586-5.587a1 1 0 001.414-1.414l-5.586-5.587a1 1 0 00-1.414 0H10a1 1 0 01-1-1V4a1 1 0 011-1h1a1 1 0 011 1v3.586l5.586 5.586a1 1 0 001.414 0l5.586-5.586a1 1 0 001.414-1.414l-5.586-5.586a1 1 0 00-1.414 0z"></path>
                            </svg>
                        </button>
                        <button 
                            id="theme-toggle"
                            type="button"
                            class="p-2 rounded-lg hover:bg-kertas dark:hover:bg-arang transition-colors"
                        >
                            <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path>
                            </svg>
                            <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path>
                            </svg>
                        </button>

                        <div 
                            id="realtime-clock"
                            class="font-mono text-sm text-arang dark:text-kertas"
                        >
                            00:00:00
                        </div>

                        <div class="text-sm">
                            <span class="font-medium text-arang dark:text-paper">{{ auth()->user()->name }}</span>
                            <span class="block text-xs text-muted-dark dark:text-muted-light">Kasir</span>
                        </div>
                    </div>
                </header>

                <main class="flex-1 overflow-y-auto bg-paper dark:bg-ink p-6">
                    {{ $slot }}
                </main>
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
                document.getElementById('realtime-clock').textContent = `${hours}:${minutes}:${seconds}`;
            }

            updateClock();
            setInterval(updateClock, 1000);

            // Toast Notification System
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

                // Animate entrance
                requestAnimationFrame(() => {
                    toast.classList.remove('translate-y-2', 'opacity-0');
                });

                // Dismiss after 3s
                setTimeout(() => {
                    toast.classList.add('translate-y-[-8px]', 'opacity-0');
                    setTimeout(() => toast.remove(), 300);
                }, 3000);
            }

            // Sound Notification System
            let audioCtx = null;
            let soundEnabled = localStorage.getItem('kasir_sound_enabled') !== 'false';
            
            function initAudio() {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }
                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }
            
            function playNotificationSound(type) {
                if (!soundEnabled) return;
                initAudio();
                const ctx = audioCtx;
                const oscillator = ctx.createOscillator();
                const gainNode = ctx.createGain();
                oscillator.connect(gainNode);
                gainNode.connect(ctx.destination);
                
                switch(type) {
                    case 'new_order':
                        // Double beep for new order
                        oscillator.frequency.setValueAtTime(800, ctx.currentTime);
                        oscillator.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.2);
                        gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                        oscillator.start(ctx.currentTime);
                        oscillator.stop(ctx.currentTime + 0.3);
                        
                        // Second beep
                        setTimeout(() => {
                            const osc2 = ctx.createOscillator();
                            const gain2 = ctx.createGain();
                            osc2.connect(gain2);
                            gain2.connect(ctx.destination);
                            osc2.frequency.setValueAtTime(800, ctx.currentTime);
                            osc2.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.2);
                            gain2.gain.setValueAtTime(0.3, ctx.currentTime);
                            gain2.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                            osc2.start(ctx.currentTime);
                            osc2.stop(ctx.currentTime + 0.3);
                        }, 150);
                        break;
                        
                    case 'order_ready':
                        // Ascending tone for order ready
                        oscillator.frequency.setValueAtTime(400, ctx.currentTime);
                        oscillator.frequency.setValueAtTime(600, ctx.currentTime + 0.1);
                        oscillator.frequency.setValueAtTime(800, ctx.currentTime + 0.2);
                        gainNode.gain.setValueAtTime(0.25, ctx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                        oscillator.start(ctx.currentTime);
                        oscillator.stop(ctx.currentTime + 0.5);
                        break;
                        
                    case 'payment':
                        // Pleasant chime for payment
                        oscillator.frequency.setValueAtTime(600, ctx.currentTime);
                        oscillator.frequency.setValueAtTime(800, ctx.currentTime + 0.1);
                        oscillator.frequency.setValueAtTime(1000, ctx.currentTime + 0.2);
                        gainNode.gain.setValueAtTime(0.25, ctx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.4);
                        oscillator.start(ctx.currentTime);
                        oscillator.stop(ctx.currentTime + 0.4);
                        break;
                        
                    default:
                        // Simple beep
                        oscillator.frequency.setValueAtTime(600, ctx.currentTime);
                        gainNode.gain.setValueAtTime(0.2, ctx.currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                        oscillator.start(ctx.currentTime);
                        oscillator.stop(ctx.currentTime + 0.3);
                }
            }

            // Initialize audio on first user interaction
            document.addEventListener('click', initAudio, { once: true });
            document.addEventListener('keydown', initAudio, { once: true });

            window.addEventListener('success', e => {
                showToast(e.detail.message || e.detail, 'success');
                playNotificationSound(e.detail.type || 'default');
            });
            window.addEventListener('error', e => {
                showToast(e.detail.message || e.detail, 'error');
                playNotificationSound('default');
            });
            window.addEventListener('notify', e => {
                showToast(e.detail.message || e.detail, e.detail.type || 'info');
                playNotificationSound(e.detail.type === 'success' ? 'order_ready' : 'default');
            });
            
            // Listen for Livewire events
            document.addEventListener('livewire:load', () => {
                Livewire.on('order-placed', (data) => {
                    playNotificationSound('new_order');
                });
                Livewire.on('order-status-updated', (data) => {
                    if (data?.status === 'Selesai') {
                        playNotificationSound('order_ready');
                    }
                });
            });
            
            // Toggle sound
            window.toggleSound = function() {
                soundEnabled = !soundEnabled;
                localStorage.setItem('kasir_sound_enabled', soundEnabled);
                const btn = document.getElementById('sound-toggle-btn');
                if (btn) {
                    btn.innerHTML = soundEnabled 
                        ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1a1 1 0 011 1v3.586a1 1 0 01-.293.707l5.586 5.587a1 1 0 001.414 0l5.586-5.587a1 1 0 001.414-1.414l-5.586-5.587a1 1 0 00-1.414 0H10a1 1 0 01-1-1V4a1 1 0 011-1h1a1 1 0 011 1v3.586l5.586 5.586a1 1 0 001.414 0z"></path></svg>'
                        : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1a1 1 0 011 1v3.586a1 1 0 01-.293.707l5.586 5.587a1 1 0 001.414 0l5.586-5.587a1 1 0 001.414-1.414l-5.586-5.587a1 1 0 00-1.414 0H10a1 1 0 01-1-1V4a1 1 0 011-1h1a1 1 0 011 1v3.586l5.586 5.586a1 1 0 001.414 0l5.586-5.586a1 1 0 001.414-1.414l-5.586-5.587a1 1 0 00-1.414 0H10a1 1 0 01-1-1V4a1 1 0 011-1h1a1 1 0 011 1v3.586a1 1 0 00-.293.707l-5.586 5.586a1 1 0 000 1.414z"></path></svg>';
                    btn.classList.toggle('bg-accent', soundEnabled);
                    btn.classList.toggle('text-white', soundEnabled);
                    btn.classList.toggle('bg-paper-card', !soundEnabled);
                    btn.classList.toggle('dark:bg-surface', !soundEnabled);
                    btn.classList.toggle('text-arang', !soundEnabled);
                    btn.classList.toggle('dark:text-kertas', !soundEnabled);
                }
            };
        </script>

        {{-- MODAL: KONFIRMASI LOGOUT --}}
        <div
            x-show="confirmLogout"
            x-transition.opacity
            @keydown.escape.window="confirmLogout = false"
            class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            style="display: none;"
        >
            <div @click.outside="confirmLogout = false" class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl max-w-sm w-full p-6 space-y-5 shadow-2xl">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-full bg-cabai/10 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6 text-cabai" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a2 2 0 012-2h4a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-display font-bold text-arang dark:text-paper">Keluar dari Akun?</h3>
                        <p class="text-xs text-muted-dark dark:text-muted-light mt-1">Sesi kamu akan diakhiri dan kamu harus login kembali untuk mengakses dashboard.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2 border-t border-border-light dark:border-border-dark">
                    <button type="button" @click="confirmLogout = false" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light rounded-lg transition-colors">Batal</button>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-5 py-2 bg-cabai hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">Ya, Keluar</button>
                    </form>
                </div>
            </div>
        </div>
        </div>
    </body>
</html>
