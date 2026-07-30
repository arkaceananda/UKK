@props([
    'variant' => 'button',
])

<div x-data="{
    theme: localStorage.getItem('theme') || 'system',
    isDark: false,
    init() {
        this.updateTheme();
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
            if (this.theme === 'system') {
                this.updateTheme();
            }
        });
    },
    updateTheme() {
        if (this.theme === 'dark' || (this.theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
            this.isDark = true;
        } else {
            document.documentElement.classList.remove('dark');
            this.isDark = false;
        }
    },
    setTheme(val) {
        this.theme = val;
        if (val === 'system') {
            localStorage.removeItem('theme');
        } else {
            localStorage.setItem('theme', val);
        }
        this.updateTheme();
    },
    toggle() {
        if (this.isDark) {
            this.setTheme('light');
        } else {
            this.setTheme('dark');
        }
    }
}" class="relative inline-flex items-center">
    @if ($variant === 'dropdown')
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button type="button" 
                    title="Ubah Tema"
                    class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-surface focus:outline-none focus:ring-2 focus:ring-accent transition-colors duration-150 flex items-center gap-2">
                    <template x-if="isDark">
                        <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </template>
                    <template x-if="!isDark">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </template>
                </button>
            </x-slot>

            <x-slot name="content">
                <button type="button" @click="setTheme('light')" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors" :class="{ 'font-semibold text-accent': theme === 'light' }">
                    <svg class="w-4 h-4 mr-2 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span>Terang (Light)</span>
                </button>
                <button type="button" @click="setTheme('dark')" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors" :class="{ 'font-semibold text-accent': theme === 'dark' }">
                    <svg class="w-4 h-4 mr-2 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <span>Gelap (Dark)</span>
                </button>
                <button type="button" @click="setTheme('system')" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 dark:text-kertas hover:bg-gray-100 dark:hover:bg-surface transition-colors" :class="{ 'font-semibold text-accent': theme === 'system' }">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    <span>Sistem</span>
                </button>
            </x-slot>
        </x-dropdown>
    @else
        <button type="button" 
            @click="toggle()" 
            title="Ganti Tema (Terang / Gelap)"
            aria-label="Ganti Tema (Terang / Gelap)"
            class="relative p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-surface border border-transparent hover:border-gray-200 dark:hover:border-border-dark focus:outline-none focus:ring-2 focus:ring-accent transition-all duration-200">
            <span class="sr-only">Toggle theme</span>
            
            <template x-if="isDark">
                <svg class="w-5 h-5 text-amber-400 transition-transform duration-300 transform rotate-0 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </template>

            <template x-if="!isDark">
                <svg class="w-5 h-5 text-slate-700 transition-transform duration-300 transform rotate-0 scale-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                </svg>
            </template>
        </button>
    @endif
</div>
