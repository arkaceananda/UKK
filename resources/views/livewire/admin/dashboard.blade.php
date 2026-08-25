<script src="{{ asset('js/admin-charts.js') }}"></script>
<div class="space-y-8">
    {{-- Header Title & Top Actions --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-arang dark:text-paper">Admin Overview</h2>
            <p class="text-sm text-muted-dark dark:text-muted-light">Kelola grafik penjualan, daftar menu, kategori, dan meja burjo di sini.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button wire:click="openCreateKategoriModal" class="px-4 py-2 bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-arang dark:text-kertas hover:bg-kertas dark:hover:bg-arang rounded-lg transition-colors font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Kategori</span>
            </button>
            <button wire:click="openCreateMenuModal" class="px-4 py-2 bg-accent hover:bg-opacity-90 text-white rounded-lg transition-colors font-medium text-sm flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Menu</span>
            </button>
            <button wire:click="openCreateMejaModal" class="px-4 py-2 bg-daun hover:bg-opacity-90 text-white rounded-lg transition-colors font-medium text-sm flex items-center gap-2 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Tambah Meja</span>
            </button>
        </div>
    </div>

    {{-- SECTION 1: CHARTS — Independent Alpine.js components --}}
    <section id="dashboard-charts" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Sales Trend Area Chart --}}
        <div x-data="chartFilter('sales', '7d')" x-init="$nextTick(() => renderSalesChart(filter))" class="bg-paper-card dark:bg-surface p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-sm space-y-4" wire:ignore>
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-lg font-display font-semibold text-arang dark:text-paper">Tren Penjualan</h3>
                    <p class="text-xs text-muted-dark dark:text-muted-light">Total pendapatan transaksi terbayar</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center bg-kertas dark:bg-ink rounded-lg p-1 border border-border-light dark:border-border-dark">
                        <template x-for="opt in options" :key="opt.value">
                            <button @click="setFilter(opt.value)" :class="filter === opt.value ? 'bg-accent text-white shadow-sm' : 'text-muted-dark dark:text-muted-light hover:text-arang dark:hover:text-kertas'" class="px-3 py-1 text-xs font-medium rounded-md transition-colors" x-text="opt.label"></button>
                        </template>
                    </div>
                    <a :href="`{{ route('admin.sales.export', '') }}/${filter}`" class="px-3 py-1.5 bg-gas hover:bg-opacity-90 text-white text-xs font-medium rounded-lg transition-colors flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>CSV</span>
                    </a>
                </div>
            </div>
            <div id="sales-chart" class="w-full min-h-[300px]"></div>
        </div>

        {{-- Top Selling Menu Bar Chart --}}
        <div x-data="chartFilter('top-menu', '7d')" x-init="$nextTick(() => renderTopMenuChart(filter))" class="bg-paper-card dark:bg-surface p-6 rounded-2xl border border-border-light dark:border-border-dark shadow-sm space-y-4" wire:ignore>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-display font-semibold text-arang dark:text-paper">Top 5 Menu Terlaris</h3>
                    <p class="text-xs text-muted-dark dark:text-muted-light">Jumlah porsi terjual per menu</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex items-center bg-kertas dark:bg-ink rounded-lg p-1 border border-border-light dark:border-border-dark">
                        <template x-for="opt in options" :key="opt.value">
                            <button @click="setFilter(opt.value)" :class="filter === opt.value ? 'bg-accent text-white shadow-sm' : 'text-muted-dark dark:text-muted-light hover:text-arang dark:hover:text-kertas'" class="px-2 py-0.5 text-[10px] font-medium rounded-md transition-colors" x-text="opt.label"></button>
                        </template>
                    </div>
                    <a :href="`{{ route('admin.top-menu.export', '') }}/${filter}`" class="px-2.5 py-1 bg-gas hover:bg-opacity-90 text-white text-[10px] font-medium rounded-full transition-colors flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        <span>CSV</span>
                    </a>
                </div>
            </div>
            <div id="top-menu-chart" class="w-full min-h-[300px]"></div>
        </div>
    </section>

    {{-- SECTION 2: MANAGEMENT MENU --}}
    <livewire:admin.menu-manager />

    {{-- SECTION 3: MANAGEMENT MEJA --}}
    <livewire:admin.meja-manager />

    {{-- MODAL DIALOG: FORM KATEGORI --}}
    @if($showKategoriModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-data x-transition.opacity>
            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl max-w-sm w-full p-6 space-y-5 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-3">
                    <h3 class="text-lg font-display font-bold text-arang dark:text-paper">Tambah Kategori Baru</h3>
                    <button wire:click="$set('showKategoriModal', false)" class="text-muted-dark hover:text-ink dark:text-muted-light">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveKategori" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Nama Kategori</label>
                        <input wire:model="namaKategori" type="text" placeholder="Contoh: Special Drinks" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                        @error('namaKategori') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-border-light dark:border-border-dark">
                        <button type="button" wire:click="$set('showKategoriModal', false)" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-accent hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">Simpan Kategori</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

@script
<script>
    window.routes = window.routes || {};
    window.routes.apiChartSales = '{{ route('admin.api.chart.sales') }}';
    window.routes.apiChartTopMenu = '{{ route('admin.api.chart.top-menu') }}'; 
</script>
@endscript

