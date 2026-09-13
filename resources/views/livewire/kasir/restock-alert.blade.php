<div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl p-5">
    <div class="flex items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-cabai/10 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-cabai" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-display font-bold text-arang dark:text-paper">Permintaan Restock ke Owner</h3>
                <p class="text-xs text-muted-dark dark:text-muted-light">Menu <span class="font-semibold text-cabai">Habis</span> — kasir bisa ingatkan owner tanpa ubah stok.</p>
            </div>
        </div>
        @if($pendingCount > 0)
            <span class="px-2.5 py-1 text-xs font-bold rounded-full bg-cabai text-white">{{ $pendingCount }} menunggu</span>
        @endif
    </div>

    @if($habisMenus->isEmpty())
        <div class="py-6 text-center border border-dashed border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink">
            <p class="text-sm text-arang dark:text-paper font-medium">Semua menu tersedia</p>
            <p class="text-xs text-muted-dark dark:text-muted-light mt-1">Tidak ada menu habis. Tidak perlu minta restock.</p>
        </div>
    @else
        <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
            @foreach($habisMenus as $menu)
                <div wire:key="restock-{{ $menu['id'] }}" class="flex items-center justify-between gap-3 px-3 py-2.5 rounded-xl border {{ $menu['requested'] ? 'border-cabai/30 bg-cabai/5' : 'border-border-light dark:border-border-dark bg-paper dark:bg-ink' }}">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-arang dark:text-paper truncate">{{ $menu['nama'] }}</p>
                        <p class="text-xs text-muted-dark dark:text-muted-light">{{ $menu['kategori'] ?? 'Tanpa kategori' }} · Stok: <span class="font-mono">{{ $menu['stok'] }}</span></p>
                        @if($menu['requested'])
                            <p class="text-[11px] text-cabai mt-0.5">Diminta oleh {{ $menu['requested_by'] }} · menunggu owner</p>
                        @endif
                    </div>
                    @if($menu['requested'])
                        <span class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-cabai/10 text-cabai border border-cabai/20">Terkirim</span>
                    @else
                        <button wire:click="requestRestock({{ $menu['id'] }})" class="shrink-0 px-3 py-1.5 text-xs font-semibold rounded-lg bg-accent hover:bg-accent/90 text-white transition-colors">
                            Minta Restock
                        </button>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="text-[11px] text-muted-dark dark:text-muted-light mt-3">Reuse <code class="font-mono bg-kertas dark:bg-arang px-1 py-0.5 rounded">menu.status = Habis</code> — tidak ada migrasi baru. Owner melihat badge yang sama di Admin.</p>
    @endif
</div>
