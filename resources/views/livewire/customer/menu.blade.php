<div x-data="{
    cartCount: {{ $cartCount }},
    cartTotal: {{ $cartTotal }},
    init() {
        Livewire.on('cart-updated', (data) => {
            this.cartCount = data.count;
            this.cartTotal = data.total;
        });
    }
}" class="space-y-4 pb-24">

    <div class="flex items-center gap-3">
        <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-dark dark:text-muted-light"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <input
                type="text"
                wire:model.debounce.300ms="searchQuery"
                placeholder="Cari menu..."
                class="w-full pl-10 pr-4 py-3 rounded-xl bg-surface dark:bg-ink border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent transition-all"
            />
        </div>
        @if(count($cart) > 0)
            <a href="#cart" class="shrink-0 relative p-3 rounded-xl bg-surface dark:bg-ink border border-border-light dark:border-border-light hover:border-accent transition-colors" title="Keranjang">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                <span class="absolute -top-1.5 -right-1.5 bg-cabai text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
            </a>
        @endif
    </div>

    @if($categories->isNotEmpty())
        <div class="flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
            @foreach($categories as $category)
                <button
                    wire:click="selectedCategory = '{{ $category->id }}'"
                    class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap"
                    :class="selectedCategory === '{{ $category->id }}'
                        ? 'bg-accent text-ink font-semibold shadow-sm'
                        : 'bg-surface dark:bg-ink text-arang dark:text-kertas border border-border-light dark:border-border-dark hover:border-accent'"
                >
                    {{ $category->nama }}
                    <span class="ml-1 text-xs opacity-70">{{ $category->menu_count }}</span>
                </button>
            @endforeach
        </div>
    @endif

    @if($menus->isEmpty())
        <div class="flex flex-col items-center justify-center py-16 text-center">
            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light mb-3"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            <p class="text-muted-dark dark:text-muted-light text-sm">Tidak ada menu ditemukan</p>
        </div>
    @else
        <div class="grid grid-cols-2 gap-3">
            @foreach($menus as $item)
                <div class="bg-surface dark:bg-ink rounded-2xl border border-border-light dark:border-border-dark overflow-hidden hover:shadow-lg hover:shadow-black/5 dark:hover:shadow-black/20 transition-all duration-200 group">
                    <div class="relative aspect-square bg-ink/5 dark:bg-surface/50 flex items-center justify-center overflow-hidden">
                        @if($item['cachedImage'])
                            <img
                                data-src="{{ $item['cachedImage'] }}"
                                src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 400 400'%3E%3Crect fill='%231E2229' width='400' height='400'/%3E%3C/svg%3E"
                                alt="{{ $item['nama'] }}"
                                class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105 lazy-image"
                            />
                        @else
                            <div class="w-full h-full bg-ink/10 dark:bg-surface/30 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            </div>
                        @endif
                        @if(! \App\Models\Menu::find($item['id'])?->isAvailable())
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <span class="bg-cabai text-white text-xs font-semibold px-3 py-1 rounded-full">Habis</span>
                            </div>
                        @endif
                    </div>
                    <div class="p-3 space-y-1">
                        <h3 class="font-semibold text-sm text-arang dark:text-kertas line-clamp-1 leading-snug">{{ $item['nama'] }}</h3>
                        <p class="text-xs text-muted-dark dark:text-muted-light line-clamp-2 leading-relaxed">{{ $item['deskripsi'] }}</p>
                        <div class="flex items-center justify-between pt-1">
                            <span class="font-mono font-bold text-accent text-sm">Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                            <button
                                wire:click="addToCart({{ $item['id'] }})"
                                wire:loading.attr="disabled"
                                class="shrink-0 w-8 h-8 rounded-full bg-accent hover:bg-accent-dark text-ink flex items-center justify-center transition-colors shadow-sm"
                                aria-label="Tambah {{ $item['nama'] }}"
                            >
                                <template wire:loading>
                                    <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                </template>
                                <template wire:loading.remove>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                </template>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(count($cart) > 0)
        <div class="fixed bottom-0 left-0 right-0 z-50 px-4 pb-4 safe-area-inset-bottom">
            <div class="max-w-lg mx-auto bg-surface dark:bg-ink rounded-2xl border border-border-light dark:border-border-dark shadow-2xl overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 border-b border-border-light dark:border-border-dark">
                    <div class="flex items-center gap-2">
                        <div class="relative">
                            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-accent"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                            <span class="absolute -top-1.5 -right-1.5 bg-cabai text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                        </div>
                        <span class="font-semibold text-sm text-arang dark:text-kertas">Keranjang</span>
                        <span class="font-mono text-sm font-bold text-accent">Rp {{ number_format((int) $cartTotal, 0, ',', '.') }}</span>
                    </div>
                    <button wire:click="clearCart" class="p-2 rounded-lg hover:bg-ink/10 dark:hover:bg-ink/30 transition-colors" title="Kosongkan">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><polyline points="3 6 5 6 21 6"/></svg>
                    </button>
                </div>

                <div class="max-h-40 overflow-y-auto px-4 py-2 space-y-2">
                    @foreach($cart as $item)
                    <div class="flex items-center gap-3 py-1">
                        <div class="w-10 h-10 rounded-lg bg-ink/5 dark:bg-ink/30 flex items-center justify-center shrink-0">
                            @if($item['foto'])
                                <img src="{{ asset('storage/') }}/{{ $item['foto'] }}" alt="" class="w-full h-full object-cover rounded-lg" />
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-medium text-arang dark:text-kertas truncate">{{ $item['nama'] }}</p>
                            <p class="text-xs font-mono text-accent">Rp {{ number_format($item['harga'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="updateQuantity({{ $item['menu_id'] }}, {{ $item['jumlah'] - 1 }})" class="w-7 h-7 rounded-full bg-ink/5 dark:bg-ink/30 flex items-center justify-center hover:bg-ink/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                            <span class="text-xs font-mono font-medium text-arang dark:text-kertas w-5 text-center">{{ $item['jumlah'] }}</span>
                            <button wire:click="updateQuantity({{ $item['menu_id'] }}, {{ $item['jumlah'] + 1 }})" class="w-7 h-7 rounded-full bg-ink/5 dark:bg-ink/30 flex items-center justify-center hover:bg-ink/10 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="px-4 py-3 border-t border-border-light dark:border-border-dark space-y-2">
                    <select wire:model="metodeBayar" class="w-full px-3 py-2 rounded-lg bg-ink/5 dark:bg-ink/30 border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas focus:outline-none focus:ring-2 focus:ring-accent">
                        <option value="tunai">Tunai</option>
                        <option value="qris">QRIS</option>
                    </select>
                    <textarea
                        wire:model="notes"
                        placeholder="Catatan pesanan (opsional)"
                        rows="2"
                        class="w-full px-3 py-2 rounded-lg bg-ink/5 dark:bg-ink/30 border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent resize-none"
                    ></textarea>
                    <button
                        wire:click="checkout"
                        class="w-full py-3 rounded-xl bg-accent hover:bg-accent-dark text-ink font-semibold text-sm transition-colors shadow-sm flex items-center justify-center gap-2"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        Checkout — Rp {{ number_format((int) $cartTotal, 0, ',', '.') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>