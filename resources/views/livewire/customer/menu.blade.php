@if (! $verified)
    <div class="min-h-[70vh] flex flex-col items-center justify-center px-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-kertas dark:bg-surface border border-border-light dark:border-border-dark flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
        </div>
        <h2 class="font-display font-semibold text-lg text-arang dark:text-kertas mb-2">Silakan Scan Ulang QR Meja</h2>
        <p class="text-sm text-muted-dark dark:text-muted-light mb-6 max-w-xs">Sesi meja ini sudah berakhir. Scan QR code di meja untuk mulai memesan kembali.</p>
        <a href="{{ route('meja.assign', $meja->token) }}" class="px-6 py-3 bg-accent hover:bg-accent-dark text-ink font-semibold text-sm rounded-xl transition-colors">Scan Ulang</a>
    </div>
@else
<div class="pb-32" wire:on.window="refreshStock" wire:poll.15s="refreshStock" x-data="{
    activeCategory: null,
    scrollToActiveCategory() {
        if (!this.activeCategory) {
            return;
        }

        this.$nextTick(() => {
            const container = this.$refs.pillContainer;
            const activeEl = this.$refs['pill-' + this.activeCategory];

            if (container && activeEl) {
                const targetLeft = Math.max(0, activeEl.offsetLeft - 16);
                container.scrollTo({ left: targetLeft, behavior: 'smooth' });
            }
        });
    }
}" x-init="
    activeCategory = {{ $categories->first()->id ?? 'null' }};
    const categories = {{ json_encode($categories->pluck('id')->toArray()) }};
    categories.forEach(function(categoryId) {
        const el = document.getElementById('kategori-' + categoryId);
        if (el) {
            const obs = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        activeCategory = categoryId;
                    }
                });
            }, { rootMargin: '-30% 0px -70% 0px', threshold: 0 });
            obs.observe(el);
        }
    });
" x-effect="scrollToActiveCategory()">

{{-- Theme detection --}}
<script>
    document.addEventListener('livewire:navigated', function() {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        document.documentElement.classList.toggle('dark', prefersDark);
    });
</script>

    {{-- STICKY HEADER + CATEGORY PILLS --}}
    <div class="sticky top-0 z-30 bg-paper dark:bg-ink">
        {{-- HEADER --}}
        <div class="px-4 pt-4 pb-3 border-b border-border-light dark:border-border-dark">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-11 h-11 rounded-full bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark shrink-0"></div>
                    <h1 class="font-display font-semibold text-arang dark:text-kertas text-lg truncate">BurjoOrder</h1>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @if(count($cart) > 0)
                        <span class="px-2 py-1 rounded-full bg-accent text-ink text-xs font-bold font-mono">{{ $cartCount }}</span>
                    @endif
                    <span class="shrink-0 px-4 py-2 rounded-full bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas font-medium">
                        Meja {{ $meja->nomor }}
                    </span>
                </div>
            </div>
        </div>

        {{-- CATEGORY PILLS --}}
        @if($categories->isNotEmpty())
            <div class="bg-paper/95 dark:bg-ink/95 backdrop-blur-md border-b border-border-light/50 dark:border-border-dark/50 px-4 py-3">
                <div x-ref="pillContainer" class="flex gap-2 overflow-x-auto scrollbar-hide">
                    @foreach($categories as $category)
                        <a
                            href="#kategori-{{ $category->id }}"
                            x-ref="pill-{{ $category->id }}"
                            @click="activeCategory = {{ $category->id }}"
                            :class="activeCategory === {{ $category->id }}
                                ? 'bg-accent text-ink font-semibold border-accent shadow-sm'
                                : 'bg-paper-card text-arang border-border-light dark:bg-surface dark:text-kertas dark:border-border-dark'"
                            class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap border"
                        >
                            {{ $category->nama }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- MENU LIST --}}
    <div class="px-4 pt-4">
        @php
            $menusByCategory = collect($menus)->groupBy('kategori');
        @endphp

        @if($menus->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light mb-3">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <p class="text-muted-dark dark:text-muted-light text-sm">Tidak ada menu ditemukan</p>
            </div>
        @else
            @foreach($categories as $category)
                @php
                    $categoryMenus = collect($menus)->where('kategori', $category->nama);
                @endphp

                @if($categoryMenus->isNotEmpty())
                    <div id="kategori-{{ $category->id }}" class="scroll-mt-20 mb-8">
                        <h2 class="font-display font-bold text-lg text-arang dark:text-kertas mb-4">{{ $category->nama }}</h2>

                        <div class="space-y-3">
                            @foreach($categoryMenus as $item)
                                <div class="flex gap-3 bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-3">

                                    <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-xl bg-black/5 dark:bg-surface-alt overflow-hidden flex items-center justify-center">
                                        @if($item['cachedImage'])
                                            <img
                                                data-src="{{ $item['cachedImage'] }}"
                                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Crect fill='%231E2229' width='400' height='400'/%3E%3C/svg%3E"
                                                alt="{{ $item['nama'] }}"
                                                class="w-full h-full object-cover lazy-image"
                                            />
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light">
                                                <rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/>
                                            </svg>
                                        @endif
                                    </div>

                                    <div class="flex-1 min-w-0 flex flex-col justify-between">
                                        <div>
                                            <h3 class="font-display font-semibold text-sm sm:text-base line-clamp-1 {{ ($item['is_available'] ?? false) ? 'text-arang dark:text-kertas' : 'text-muted-dark dark:text-muted-light' }}">
                                                {{ $item['nama'] }}
                                            </h3>
                                            <p class="text-xs line-clamp-2 mt-0.5 {{ ($item['is_available'] ?? false) ? 'text-muted-dark dark:text-muted-light' : 'text-muted-dark/70 dark:text-muted-light/70' }}">
                                                {{ $item['deskripsi'] }}
                                            </p>
                                        </div>

                                        <div class="flex items-end justify-between mt-2">
                                            <div>
                                                <span class="font-mono font-bold text-sm {{ ($item['is_available'] ?? false) ? 'text-accent' : 'text-accent/50' }}">
                                                    Rp {{ number_format($item['harga'], 0, ',', '.') }}
                                                </span>
                                                @unless($item['is_available'] ?? false)
                                                    <p class="text-xs text-muted-dark dark:text-muted-light leading-tight">Habis</p>
                                                @endunless
                                            </div>

                                             @if($item['is_available'] ?? false)
                                                 @if(!empty($item['options']))
                                                     <div class="mt-2 flex flex-wrap gap-2">
                                                         @foreach($item['options'] as $opt)
                                                             <label class="inline-flex items-center gap-1.5 text-xs font-medium text-arang dark:text-kertas cursor-pointer">
                                                                 <input
                                                                     type="radio"
                                                                     wire:model="selectedOptions.{{ $item['id'] }}"
                                                                     value="{{ $opt }}"
                                                                     {{ ($selectedOptions[$item['id']] ?? $item['options'][0]) === $opt ? 'checked' : '' }}
                                                                     class="w-3.5 h-3.5 accent-accent"
                                                                 >
                                                                 {{ ucfirst($opt) }}
                                                             </label>
                                                         @endforeach
                                                     </div>
                                                 @endif

                                                 @php
                                                     $cartLine = null;
                                                     $cartKey = null;
                                                     foreach ($cart as $k => $c) {
                                                         if (($c['menu_id'] ?? null) == $item['id']) {
                                                             $cartLine = $c;
                                                             $cartKey = $k;
                                                             break;
                                                         }
                                                     }
                                                 @endphp
                                                 @if($cartLine)
                                                     <div class="flex items-center gap-2">
                                                         <button
                                                             wire:click="decrementQuantity('{{ $cartKey }}')"
                                                             wire:loading.attr="disabled"
                                                             class="w-9 h-9 rounded-xl bg-paper dark:bg-ink border border-border-light dark:border-border-dark text-arang dark:text-kertas flex items-center justify-center hover:bg-black/5 dark:hover:bg-white/5 transition-colors"
                                                             aria-label="Kurangi {{ $item['nama'] }}"
                                                         >
                                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                         </button>
                                                         <span class="w-6 text-center font-mono font-bold text-sm text-arang dark:text-kertas">
                                                             {{ $cartLine['jumlah'] }}
                                                         </span>
                                                         <button
                                                             wire:click="addToCart({{ $item['id'] }})"
                                                             wire:loading.attr="disabled"
                                                             class="w-9 h-9 rounded-xl bg-accent hover:bg-accent-dark text-ink flex items-center justify-center transition-colors"
                                                             aria-label="Tambah {{ $item['nama'] }}"
                                                         >
                                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                         </button>
                                                     </div>
                                                 @else
                                                     <button
                                                         wire:click="addToCart({{ $item['id'] }})"
                                                         wire:loading.attr="disabled"
                                                         wire:target="addToCart({{ $item['id'] }})"
                                                         class="shrink-0 w-9 h-9 rounded-xl bg-accent hover:bg-accent-dark text-ink flex items-center justify-center transition-colors"
                                                         aria-label="Tambah {{ $item['nama'] }}"
                                                     >
                                                         <span wire:loading.remove wire:target="addToCart({{ $item['id'] }})">
                                                             <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                                         </span>
                                                         <span wire:loading wire:target="addToCart({{ $item['id'] }})">
                                                             <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                                         </span>
                                                     </button>
                                                 @endif
                                             @else
                                                 <div class="shrink-0 w-9 h-9 rounded-xl bg-accent/60 text-ink flex items-center justify-center cursor-not-allowed" aria-label="{{ $item['nama'] }} habis">
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.9" y1="4.9" x2="19.1" y2="19.1"/></svg>
                                                 </div>
                                             @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endforeach
        @endif

        @if($hasMoreMenus)
            <div wire:intersect="loadMoreMenus" class="flex items-center justify-center py-8 text-muted-dark dark:text-muted-light">
                <svg class="animate-spin w-5 h-5 {{ $loadingMore ? '' : 'opacity-0' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span class="ml-2 text-xs">Memuat menu lainnya…</span>
            </div>
        @endif
    </div>

    {{-- FLOATING CHECKOUT BUTTON --}}
    @if(count($cart) > 0)
        <div class="fixed bottom-0 left-0 right-0 z-40 px-4 pb-4 bg-gradient-to-t from-paper via-paper to-transparent dark:from-ink dark:via-ink pt-6">
            <a
                href="{{ route('customer.checkout', $meja) }}"
                wire:navigate
                class="mx-auto max-w-lg w-full flex items-center justify-between bg-accent hover:bg-accent-dark text-ink rounded-2xl shadow-elevated px-5 py-3.5 transition-all"
            >
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-ink/10 flex items-center justify-center">
                        <span class="font-mono font-bold text-sm">{{ $cartCount }}</span>
                    </div>
                    <span class="font-semibold text-sm">Lihat Pesanan</span>
                </div>
                <span class="font-mono font-bold text-sm">Rp {{ number_format((int) $cartTotal, 0, ',', '.') }}</span>
            </a>
        </div>
    @endif
    </div>
@endif