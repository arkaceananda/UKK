<section id="menu-manager" class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border-light dark:border-border-dark pb-4">
        <div>
            <div class="flex items-center gap-2">
                <h3 class="text-xl font-display font-bold text-arang dark:text-paper">Manajemen Menu</h3>
                @if($this->habisCount > 0)
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full {{ $this->restockBadgeCount > 0 ? 'bg-cabai text-white animate-pulse' : 'bg-cabai/10 text-cabai border border-cabai/20' }}">
                        {{ $this->habisCount }} Habis
                        @if($this->restockBadgeCount > 0) · {{ $this->restockBadgeCount }} minta restock @endif
                    </span>
                @endif
            </div>
            <p class="text-xs text-muted-dark dark:text-muted-light">Daftar item kuliner Burjo dalam bentuk card interaktif.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if($this->habisCount > 0)
                <button wire:click="toggleRestockFilter" class="px-3 py-2 rounded-xl text-xs font-bold border transition-colors {{ $filterRestockOnly ? 'bg-cabai text-white border-cabai' : 'bg-paper-card dark:bg-surface border-cabai/30 text-cabai hover:bg-cabai/5' }}">
                    {{ $filterRestockOnly ? 'Tampilkan Semua' : 'Hanya Perlu Restock ('.$this->restockBadgeCount.')' }}
                </button>
            @endif
            <input 
                wire:model.live.debounce.300ms="searchMenu" 
                type="text" 
                placeholder="Cari nama menu..." 
                class="px-4 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent w-full sm:w-60"
            >
            <select wire:model.live="filterKategori" class="px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat->id }}">{{ $kat->nama }}</option>
                @endforeach
            </select>
            <button wire:click="openCreateMenuModal" class="px-4 py-2 bg-accent hover:bg-opacity-90 text-white rounded-xl transition-colors font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Menu Baru</span>
            </button>
        </div>
    </div>

    @if($this->restockBadgeCount > 0)
        <div class="border border-cabai/30 bg-cabai/5 dark:bg-cabai/10 rounded-2xl p-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-2 h-2 rounded-full bg-cabai animate-pulse"></span>
                <h4 class="text-sm font-bold text-cabai">Perlu restock — permintaan dari kasir</h4>
                <span class="text-xs text-muted-dark dark:text-muted-light">({{ $this->restockBadgeCount }} menu)</span>
            </div>
            <div class="flex flex-wrap gap-2">
                @foreach($this->pendingRestockMenus as $rm)
                    <button wire:click="openEditMenuModal({{ $rm['id'] }})" class="px-3 py-1.5 rounded-full bg-paper-card dark:bg-surface border border-cabai/20 text-xs font-medium text-arang dark:text-paper hover:bg-cabai hover:text-white transition-colors">
                        {{ $rm['nama'] }} <span class="text-muted-dark dark:text-muted-light font-normal">· {{ $rm['kategori'] ?? '—' }}</span> <span class="ml-1 text-[10px] opacity-70">oleh {{ $rm['by'] }}</span>
                    </button>
                @endforeach
            </div>
            <p class="text-[11px] text-muted-dark dark:text-muted-light mt-2">Klik menu untuk restock (ubah stok/status jadi Tersedia — flag otomatis hilang).</p>
        </div>
    @endif

    {{-- Menu Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($menus as $menu)
            <div wire:key="menu-{{ $menu['id'] }}" class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl p-4 flex flex-col justify-between hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="space-y-3">
                    <div class="w-full h-36 bg-kertas dark:bg-arang rounded-xl overflow-hidden flex items-center justify-center relative">
                        @if($menu['foto'])
                            <img src="{{ app(\App\Services\ImageCacheService::class)->url($menu['foto']) }}" alt="{{ $menu['nama'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="text-center p-4 text-muted-dark dark:text-muted-light">
                                <svg class="w-10 h-10 mx-auto opacity-50 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="text-xs font-mono">No Image</span>
                            </div>
                        @endif
                        <span class="absolute top-2 right-2 px-2.5 py-0.5 text-xs font-semibold rounded-full {{ $menu['status'] === \App\Enums\StatusMenu::Tersedia->value ? 'bg-daun/20 text-daun border border-daun/30' : 'bg-cabai/20 text-cabai border border-cabai/30' }}">
                            {{ $menu['status'] === \App\Enums\StatusMenu::Tersedia->value ? 'Tersedia' : 'Habis' }}
                        </span>
                        @if(!empty($menu['restockRequested']))
                            <span class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-bold rounded-full bg-cabai text-white shadow">Minta restock @if(!empty($menu['restockBy']))· {{ $menu['restockBy'] }}@endif</span>
                        @endif
                    </div>

                    <div>
                        <span class="text-xs font-medium text-accent uppercase tracking-wider">{{ $menu['kategori'] ?? 'Uncategorized' }}</span>
                        <h4 class="text-base font-display font-bold text-arang dark:text-paper line-clamp-1">{{ $menu['nama'] }}</h4>
                        <p class="text-xs text-muted-dark dark:text-muted-light line-clamp-2 mt-1 min-h-[2rem]">{{ $menu['deskripsi'] ?: 'Tidak ada deskripsi.' }}</p>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-border-light dark:border-border-dark flex items-center justify-between">
                    <div>
                        <span class="text-xs text-muted-dark dark:text-muted-light block">Harga</span>
                        <span class="text-sm font-mono font-bold text-arang dark:text-kertas">Rp {{ number_format($menu['harga'], 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-muted-dark dark:text-muted-light block">Stok</span>
                        <span class="text-sm font-mono font-bold {{ $menu['stok'] > 0 ? 'text-daun' : 'text-cabai' }}">{{ $menu['stok'] }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <button wire:click="openEditMenuModal({{ $menu['id'] }})" class="p-2 text-gas hover:bg-gas/10 rounded-lg transition-colors" title="Edit Menu">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button wire:click="deleteMenu({{ $menu['id'] }})" wire:confirm="Apakah Anda yakin ingin menghapus menu '{{ $menu['nama'] }}'?" class="p-2 text-cabai hover:bg-cabai/10 rounded-lg transition-colors" title="Hapus Menu">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl">
                <svg class="w-12 h-12 mx-auto text-muted-dark dark:text-muted-light opacity-40 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                <p class="text-arang dark:text-paper font-medium">Belum Ada Menu</p>
                <p class="text-xs text-muted-dark dark:text-muted-light mt-1">Klik "+ Menu Baru" untuk mulai menambahkan kuliner baru.</p>
            </div>
        @endforelse
    </div>

    @if($hasMoreMenus)
        <div x-data x-intersect.margin.-200px="$wire.loadMoreMenus()" class="flex items-center justify-center py-6 text-muted-dark dark:text-muted-light">
            <svg class="animate-spin w-5 h-5 {{ $loadingMore ? '' : 'opacity-0' }}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
            <span class="ml-2 text-xs">Memuat menu lainnya…</span>
        </div>
    @else
        @if($menus->count() > 0)
            <p class="text-center text-xs text-muted-dark dark:text-muted-light py-2">
                Menampilkan semua {{ $totalMenus }} menu
            </p>
        @endif
    @endif

    {{-- MODAL DIALOG: FORM MENU (Create / Edit) --}}
    @if($showMenuModal)
        @php
            $editMenuFoto = $editingMenuId ? (\App\Models\Menu::find($editingMenuId)?->foto) : null;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-data="menuImageCropper()" x-init="previewUrl = '{{ $editMenuFoto ? app(\App\Services\ImageCacheService::class)->url($editMenuFoto) : '' }}'" x-transition.opacity>
            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-3">
                    <h3 class="text-lg font-display font-bold text-arang dark:text-paper">
                        {{ $editingMenuId ? 'Edit Menu' : 'Tambah Menu Baru' }}
                    </h3>
                    <button wire:click="$set('showMenuModal', false)" class="text-muted-dark hover:text-ink dark:text-muted-light">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveMenu" class="space-y-4" enctype="multipart/form-data">
                    {{-- Photo Upload --}}
                    <div class="flex items-center gap-4">
                        <div class="w-20 h-20 rounded-xl bg-kertas dark:bg-arang border border-border-light dark:border-border-dark overflow-hidden flex items-center justify-center flex-shrink-0">
                            <template x-if="previewUrl">
                                <img :src="previewUrl" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!previewUrl">
                                <svg class="w-8 h-8 text-muted-dark dark:text-muted-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </template>
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Foto Menu</label>
                            <input x-ref="fileInput" type="file" accept="image/*" @change="onFileSelect($event)" class="w-full text-xs text-arang dark:text-kertas file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-accent/10 file:text-accent hover:file:bg-accent/20 cursor-pointer">
                            <p class="text-[10px] text-muted-dark dark:text-muted-light mt-1">Rasio 1:1 otomatis. Format: JPG, PNG, WEBP. Maks 2MB.</p>
                            <template x-if="previewUrl">
                                <div class="flex items-center gap-2 mt-2">
                                    <button type="button" @click="$refs.fileInput.click()" class="px-3 py-1.5 bg-gas hover:bg-opacity-90 text-white text-xs font-medium rounded-lg transition-colors">Ganti</button>
                                    <button type="button" @click="previewUrl = ''; $wire.set('fotoMenuCropped', null); $wire.set('removeFoto', true)" class="px-3 py-1.5 bg-cabai hover:bg-opacity-90 text-white text-xs font-medium rounded-lg transition-colors">Hapus</button>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Opsi Menu (Panas / Dingin) --}}
                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Opsi Menu (Panas / Dingin)</label>
                        @if(!$enableOptions)
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-dashed border-border-light dark:border-border-dark bg-kertas/40 dark:bg-arang/40">
                                <span class="text-xs text-muted-dark dark:text-muted-light">Tidak ada opsi (default untuk semua menu).</span>
                                <button type="button" wire:click="$set('enableOptions', true)" class="shrink-0 px-3 py-1.5 bg-accent hover:bg-opacity-90 text-white text-xs font-medium rounded-lg transition-colors">
                                    + Tambah Opsi
                                </button>
                            </div>
                        @else
                            <div class="flex items-center justify-between gap-3 p-3 rounded-xl border border-border-light dark:border-border-dark bg-daun/10">
                                <div>
                                    <span class="text-sm font-medium text-daun">Opsi Aktif</span>
                                    <p class="text-[10px] text-muted-dark dark:text-muted-light mt-0.5">Customer dapat memilih <b>Panas</b> atau <b>Dingin</b> (satu pilihan).</p>
                                </div>
                                <button type="button" wire:click="$set('enableOptions', false)" class="shrink-0 px-3 py-1.5 bg-cabai hover:bg-opacity-90 text-white text-xs font-medium rounded-lg transition-colors">
                                    Hapus Opsi
                                </button>
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Nama Menu</label>
                        <input wire:model="namaMenu" type="text" placeholder="Contoh: Nasi Goreng Spesial" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                        @error('namaMenu') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Harga (Rp)</label>
                            <input wire:model="hargaMenu" type="number" placeholder="15000" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                            @error('hargaMenu') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Stok Int</label>
                            <input wire:model="stokMenu" type="number" placeholder="50" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                            @error('stokMenu') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Kategori</label>
                            <select wire:model="kategoriMenuId" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                                @foreach($kategoriList as $kategori)
                                    <option value="{{ $kategori->id }}">{{ $kategori->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Status Menu</label>
                            <select wire:model="statusMenu" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                                <option value="Tersedia">Tersedia</option>
                                <option value="Habis">Habis</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Deskripsi Singkat</label>
                        <textarea wire:model="deskripsiMenu" rows="2" placeholder="Catatan bahan/rasa..." class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent"></textarea>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-border-light dark:border-border-dark">
                        <button type="button" wire:click="$set('showMenuModal', false)" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-accent hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">Simpan Menu</button>
                    </div>
                </form>
            </div>

            {{-- CROPPER MODAL --}}
            <div x-show="cropModal" x-transition.opacity class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/80" style="display: none;">
                <div class="bg-paper-card dark:bg-surface rounded-2xl max-w-lg w-full p-4 space-y-4 shadow-2xl">
                    <h3 class="text-lg font-display font-bold text-arang dark:text-paper">Potong Foto Menu</h3>
                    <div class="w-full h-72 bg-black/40 rounded-xl overflow-hidden flex items-center justify-center">
                        <img id="crop-image" :src="imageUrl" class="max-w-full max-h-full block">
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <button type="button" @click="closeCrop()" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light">Batal</button>
                        <button type="button" @click="applyCrop()" class="px-5 py-2 bg-accent hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">Terapkan</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
