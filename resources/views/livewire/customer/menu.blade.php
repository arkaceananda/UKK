<div>
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
<div class="pb-40" x-data="{
    activeCategory: {{ $categories->first()->id ?? 'null' }},
    categoryIds: {{ json_encode($categories->pluck('id')->toArray()) }},
    init() {
        const handler = () => this.highlightActive();
        window.addEventListener('scroll', handler, { passive: true });
        this.highlightActive();
    },
    highlightActive() {
        for (const id of [...this.categoryIds].reverse()) {
            const el = document.getElementById('kategori-' + id);
            if (el && el.getBoundingClientRect().top <= 96) {
                if (this.activeCategory !== id) {
                    this.activeCategory = id;
                }
                return;
            }
        }
    },
    select(categoryId) {
        this.activeCategory = categoryId;
        this.highlightActive();
    },
    scrollToActiveCategory() {
        if (!this.activeCategory) {
            return;
        }

        this.$nextTick(() => {
            const container = this.$refs.pillContainer;
            const activeEl = this.$refs['pill-' + this.activeCategory];

            if (container && activeEl) {
                container.scrollTo({ left: Math.max(0, activeEl.offsetLeft - 16), behavior: 'smooth' });
            }
        });
    }
}" x-effect="scrollToActiveCategory()">

    {{-- STICKY HEADER + CATEGORY PILLS --}}
    <div class="sticky top-0 z-30 bg-paper dark:bg-ink">
        {{-- HEADER --}}
        <div class="px-4 pt-4 pb-3 border-b border-border-light dark:border-border-dark">
            <div class="flex items-center justify-between gap-3">
                <div class="flex items-center gap-2 min-w-0">
                    <h1 class="font-display font-semibold text-arang dark:text-kertas text-lg truncate">Menu</h1>
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
                            @click="select({{ $category->id }})"
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
                    <div id="kategori-{{ $category->id }}" wire:key="category-{{ $category->id }}" class="scroll-mt-20 mb-8">
                        <h2 class="font-display font-bold text-lg text-arang dark:text-kertas mb-4">{{ $category->nama }}</h2>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-4">
                            @foreach($categoryMenus as $item)
                                <div wire:key="menu-{{ $item['id'] }}" class="flex gap-3 bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-3">

                                    <div class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-xl bg-black/5 dark:bg-surface-alt overflow-hidden flex items-center justify-center">
                                        @if($item['cachedImage'])
                                            <img
                                                src="{{ $item['cachedImage'] }}"
                                                alt="{{ $item['nama'] }}"
                                                loading="lazy"
                                                class="w-full h-full object-cover"
                                                onerror="this.onerror=null;this.style.background='#1E2229';"
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
                                                        wire:click="{{ empty($item['options']) ? 'addToCart('.$item['id'].')' : 'openOptionModal('.$item['id'].')' }}"
                                                        wire:loading.attr="disabled"
                                                        wire:target="{{ empty($item['options']) ? 'addToCart('.$item['id'].')' : 'openOptionModal('.$item['id'].')' }}"
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
            <div class="flex items-center justify-center py-8">
                <button
                    wire:click="loadMoreMenus"
                    wire:loading.attr="disabled"
                    class="px-5 py-2.5 rounded-xl border border-border-light dark:border-border-dark text-sm font-medium text-arang dark:text-kertas bg-paper-card dark:bg-surface hover:bg-black/5 dark:hover:bg-white/5 transition-colors disabled:opacity-50"
                >
                    <span wire:loading.remove>Muat lebih banyak</span>
                    <span wire:loading>Memuat…</span>
                </button>
            </div>
        @else
            <div class="flex items-center justify-center py-8 text-muted-dark dark:text-muted-light">
                <span class="text-xs">Semua menu telah dimuat</span>
            </div>
        @endif
    </div>

    {{-- FLOATING CHECKOUT BAR --}}
    @if(count($cart) > 0)
        <div class="fixed bottom-0 left-0 right-0 z-40 px-4 pb-4 bg-gradient-to-t from-paper to-transparent dark:from-ink pt-4">
            <div class="mx-auto max-w-lg flex items-center gap-2">
                <button
                    wire:click="clearCart"
                    wire:confirm="Kosongkan seluruh keranjang?"
                    class="shrink-0 w-12 h-12 rounded-2xl bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-muted-dark dark:text-muted-light flex items-center justify-center hover:text-cabai transition-colors"
                    aria-label="Kosongkan keranjang"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
                <a
                    href="{{ route('customer.checkout', $meja) }}"
                    class="flex-1 flex items-center justify-between bg-accent hover:bg-accent-dark text-ink rounded-2xl shadow-elevated px-5 py-3.5 transition-all"
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
        </div>
    @endif
    </div>

    @if($optionModalItemId !== null)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center bg-black/50 px-4" wire:key="option-modal" wire:click.self="closeOptionModal">
            <div x-data="{ selected: @js($pendingOption ?? '') }" class="w-full max-w-sm bg-paper-card dark:bg-surface rounded-t-2xl sm:rounded-2xl p-5 mb-0 sm:mb-4">
                <h3 class="font-display font-semibold text-lg text-arang dark:text-kertas mb-1">Pilih Opsi</h3>
                <p class="text-sm text-muted-dark dark:text-muted-light mb-4">{{ $optionModalName }}</p>
                <div class="flex flex-col gap-2 mb-5">
                    @foreach($optionModalOptions as $opt)
                        <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer"
                            :class="selected === '{{ $opt }}' ? 'bg-accent/10 border-accent' : 'border-border-light dark:border-border-dark'">
                            <input type="radio" name="pendingOption" value="{{ $opt }}" x-model="selected" class="w-4 h-4 accent-accent">
                            <span class="text-sm font-medium text-arang dark:text-kertas">{{ ucfirst($opt) }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="flex gap-3">
                    <button type="button" wire:click="closeOptionModal" class="flex-1 py-3 rounded-xl border border-border-light dark:border-border-dark text-arang dark:text-kertas font-medium">Batal</button>
                    <button type="button" @click="$wire.confirmOptionAddWith(selected)" class="flex-1 py-3 rounded-xl bg-accent hover:bg-accent-dark text-ink font-semibold">Tambah</button>
                </div>
            </div>
        </div>
    @endif
@endif
</div>