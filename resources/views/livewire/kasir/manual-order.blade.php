<div class="max-w-3xl mx-auto">
    <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark shadow-elevated p-6 sm:p-8">

        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-accent/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-accent" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-display font-semibold text-arang dark:text-kertas">Buat Pesanan Manual</h2>
                <p class="text-xs text-muted-dark dark:text-muted-light">Untuk pesanan yang dicatat langsung oleh kasir</p>
            </div>
        </div>

        <form wire:submit.prevent="createOrder" class="space-y-6">

            {{-- PILIH MEJA --}}
            <div>
                <label class="block text-sm font-medium text-arang dark:text-kertas mb-2">Pilih Meja</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2.5">
                    @forelse($mejaList as $meja)
                        <button
                            type="button"
                            wire:key="meja-{{ $meja->id }}"
                            wire:click="$set('selectedMeja', {{ $meja->id }})"
                            class="flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-medium transition-all
                                   {{ $selectedMeja == $meja->id
                                        ? 'border-accent bg-accent text-ink shadow-sm'
                                        : 'border-border-light dark:border-border-dark bg-paper dark:bg-ink text-arang dark:text-kertas hover:border-accent/60' }}"
                        >
                            Meja {{ $meja->nomor }}
                        </button>
                    @empty
                        <p class="col-span-full text-center py-6 text-sm text-muted-dark dark:text-muted-light">Belum ada meja aktif</p>
                    @endforelse
                </div>
                @error('selectedMeja')
                    <p class="mt-2 text-xs text-cabai">{{ $message }}</p>
                @enderror
            </div>

            <div class="border-t border-border-light dark:border-border-dark"></div>

            {{-- ITEM PESANAN --}}
            <div>
                <div class="flex items-center justify-between gap-3 mb-2">
                    <label class="block text-sm font-medium text-arang dark:text-kertas">Item Pesanan</label>
                    <button
                        type="button"
                        wire:click="addOrderItem"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-dashed border-accent text-accent text-xs font-medium hover:bg-accent/10 transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Tambah Item
                    </button>
                </div>
                <div class="space-y-3">
                    @php
                        $menuGroupsJson = $menuItems
                            ->groupBy(fn ($menu) => $menu->kategori->nama ?? 'Lainnya')
                            ->map(fn ($menus, $kategoriNama) => [
                                'nama' => $kategoriNama,
                                'items' => $menus->map(fn ($menu) => [
                                    'id' => $menu->id,
                                    'nama' => $menu->nama,
                                    'harga' => number_format($menu->harga, 0, ',', '.'),
                                ])->values(),
                            ])->values();
                    @endphp
                    @foreach($orderItems as $index => $item)
                        <div wire:key="item-{{ $index }}" class="relative bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-xl p-4">
                            <div class="flex items-end gap-3">
                                <div class="flex-1 min-w-0">
                                    <label class="block text-xs text-muted-dark dark:text-muted-light mb-1.5">Menu</label>
                                    <div
                                        x-data="{
                                            open: false,
                                            search: '',
                                            selectedId: @js($item['menu_id'] ?? ''),
                                            groups: @js($menuGroupsJson),
                                            get filteredGroups() {
                                                const s = this.search.toLowerCase().trim();
                                                if (!s) return this.groups;
                                                return this.groups
                                                    .map(g => ({ ...g, items: g.items.filter(i => i.nama.toLowerCase().includes(s)) }))
                                                    .filter(g => g.items.length);
                                            },
                                            get selectedLabel() {
                                                for (const g of this.groups) {
                                                    const found = g.items.find(i => String(i.id) === String(this.selectedId));
                                                    if (found) return found.nama;
                                                }
                                                return '';
                                            },
                                            select(item) {
                                                this.selectedId = item.id;
                                                this.open = false;
                                                this.search = '';
                                                $wire.set('orderItems.{{ $index }}.menu_id', item.id);
                                            }
                                        }"
                                        @click.outside="open = false"
                                        class="relative"
                                    >
                                        <button
                                            type="button"
                                            @click="open = !open"
                                            class="flex w-full items-center justify-between gap-2 rounded-xl border border-border-light dark:border-border-dark bg-paper dark:bg-ink pl-2 pr-3.5 py-2.5 text-sm transition-colors focus:outline-none focus:ring-2 focus:ring-accent"
                                        >
                                            <span class="flex-1 min-w-0 truncate text-left" :class="selectedId ? 'text-arang dark:text-kertas' : 'text-muted-dark dark:text-muted-light'" x-text="selectedLabel || 'Pilih Menu'"></span>
                                            <svg class="h-4 w-4 shrink-0 text-muted-dark dark:text-muted-light transition-transform duration-200" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                                        </button>

                                        <div
                                            x-show="open"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 -translate-y-1"
                                            x-transition:enter-end="opacity-100 translate-y-0"
                                            x-cloak
                                            class="absolute z-20 mt-1.5 w-full rounded-xl border border-border-light dark:border-border-dark bg-paper-card dark:bg-surface shadow-elevated overflow-hidden"
                                        >
                                            <div class="p-2">
                                                <input
                                                    type="text"
                                                    x-model="search"
                                                    placeholder="Cari menu..."
                                                    class="w-full rounded-lg border border-border-light dark:border-border-dark bg-paper dark:bg-ink py-2 px-3 text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent"
                                                >
                                            </div>

                                            <div class="max-h-64 overflow-y-auto p-1.5">
                                                <template x-for="group in filteredGroups" :key="group.nama">
                                                    <div class="mb-1">
                                                        <div class="px-2 py-1.5 text-[11px] font-semibold uppercase tracking-wider text-muted-dark dark:text-muted-light" x-text="group.nama"></div>
                                                        <template x-for="item in group.items" :key="item.id">
                                                            <button
                                                                type="button"
                                                                @click="select(item)"
                                                                class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 text-sm text-left transition-colors"
                                                                :class="String(selectedId) === String(item.id) ? 'bg-accent/10 text-accent' : 'text-arang dark:text-kertas hover:bg-paper dark:hover:bg-ink'"
                                                            >
                                                                <span class="truncate" x-text="item.nama"></span>
                                                                <span class="shrink-0 font-mono text-xs text-muted-dark dark:text-muted-light" x-text="'Rp ' + item.harga"></span>
                                                            </button>
                                                        </template>
                                                    </div>
                                                </template>
                                                <p x-show="!filteredGroups.length" class="px-3 py-4 text-center text-sm text-muted-dark dark:text-muted-light">Tidak ada menu ditemukan</p>
                                            </div>
                                        </div>
                                    </div>
                                    @error("orderItems.{$index}.menu_id")
                                        <p class="mt-1 text-xs text-cabai">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="shrink-0">
                                    <label class="block text-xs text-muted-dark dark:text-muted-light mb-1.5">Jumlah</label>
                                    <div class="flex items-center gap-1.5">
                                        <button
                                            type="button"
                                            @click="$wire.set('orderItems.{{ $index }}.jumlah', Math.max(1, {{ $item['jumlah'] ?? 1 }} - 1))"
                                            class="w-9 h-9 rounded-lg border border-border-light dark:border-border-dark flex items-center justify-center text-arang dark:text-kertas hover:bg-paper-card dark:hover:bg-surface transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                        <input
                                            type="number"
                                            wire:model.live="orderItems.{{ $index }}.jumlah"
                                            min="1"
                                            class="w-14 text-center px-2 py-2 bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-lg text-sm font-mono text-arang dark:text-kertas focus:outline-none focus:ring-2 focus:ring-accent"
                                            required
                                        >
                                        <button
                                            type="button"
                                            @click="$wire.set('orderItems.{{ $index }}.jumlah', {{ $item['jumlah'] ?? 1 }} + 1)"
                                            class="w-9 h-9 rounded-lg border border-border-light dark:border-border-dark flex items-center justify-center text-arang dark:text-kertas hover:bg-paper-card dark:hover:bg-surface transition-colors"
                                        >
                                            <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        </button>
                                    </div>
                                    @error("orderItems.{$index}.jumlah")
                                        <p class="mt-1 text-xs text-cabai">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between border-t border-border-light dark:border-border-dark pt-3">
                                <span class="text-xs text-muted-dark dark:text-muted-light">Subtotal</span>
                                <span class="font-mono font-semibold text-sm text-arang dark:text-kertas">
                                    Rp {{ number_format(($item['harga'] ?? 0) * ($item['jumlah'] ?? 0), 0, ',', '.') }}
                                </span>
                            </div>

                            @if(count($orderItems) > 1)
                                <button
                                    type="button"
                                    wire:click="removeOrderItem({{ $index }})"
                                    class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center rounded-lg text-muted-dark dark:text-muted-light hover:text-cabai hover:bg-cabai/10 transition-colors"
                                    aria-label="Hapus item"
                                >
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('orderItems')
                    <p class="mt-2 text-sm text-cabai">{{ $message }}</p>
                @enderror
            </div>

            {{-- CATATAN --}}
            <div>
                <label for="catatan" class="block text-sm font-medium text-arang dark:text-kertas mb-2">Catatan (Opsional)</label>
                <textarea
                    wire:model="catatan"
                    id="catatan"
                    rows="2"
                    placeholder="Tambahkan catatan untuk pesanan ini..."
                    class="w-full px-4 py-2.5 rounded-xl bg-paper dark:bg-ink border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent focus:border-transparent resize-none transition-colors"
                ></textarea>
            </div>

            {{-- TOTAL --}}
            <div class="bg-accent/10 rounded-xl px-5 py-4 flex items-center justify-between">
                <span class="text-sm font-medium text-arang dark:text-kertas">Total Harga</span>
                <span class="text-2xl font-display font-bold font-mono text-accent">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
            </div>

            {{-- AKSI --}}
            <div class="flex gap-3">
                <button
                    type="button"
                    wire:click="$refresh"
                    class="px-6 py-3 rounded-xl border border-border-light dark:border-border-dark text-arang dark:text-kertas text-sm font-medium hover:bg-paper dark:hover:bg-ink transition-colors"
                >
                    Reset
                </button>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="createOrder"
                    class="flex-1 px-6 py-3 rounded-xl bg-accent hover:bg-accent-dark text-ink text-sm font-semibold transition-colors disabled:opacity-60 flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="createOrder">Buat Pesanan</span>
                    <span wire:loading wire:target="createOrder" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                        Memproses...
                    </span>
                </button>
            </div>
        </form>
    </div>
</div>
