<section id="table-manager" class="space-y-6 pt-6 border-t border-border-light dark:border-border-dark" wire:poll.5s="refreshMeja">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-xl font-display font-bold text-arang dark:text-paper">Manajemen Meja & QR Code</h3>
            <p class="text-xs text-muted-dark dark:text-muted-light">Kelola nomor meja, status aktif, dan QR Token untuk pelanggan scan meja di Burjo.</p>
        </div>
        <button wire:click="openCreateMejaModal" class="px-4 py-2 bg-daun hover:bg-opacity-90 text-white rounded-xl transition-colors font-medium text-sm flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah Meja Baru</span>
        </button>
    </div>

    {{-- Meja Cards Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
        @forelse($mejasList as $meja)
            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl p-5 space-y-4 hover:shadow-md transition-shadow relative">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-10 h-10 rounded-xl bg-accent/10 text-accent font-display font-bold text-lg flex items-center justify-center">
                            {{ $meja->nomor }}
                        </span>
                        <div>
                            <h4 class="font-display font-bold text-arang dark:text-paper">Meja {{ $meja->nomor }}</h4>
                            <span class="text-xs text-muted-dark dark:text-muted-light block">
                                {{ $meja->is_occupied ? '🔴 Terisi' : '🟢 Kosong' }}
                            </span>
                        </div>
                    </div>

                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $meja->status === \App\Enums\StatusMeja::Aktif ? 'bg-daun/20 text-daun border border-daun/30' : 'bg-cabai/20 text-cabai border border-cabai/30' }}">
                        {{ $meja->status === \App\Enums\StatusMeja::Aktif ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                {{-- QR Code Card Preview --}}
                <div class="p-3 bg-white dark:bg-ink rounded-xl border border-border-light dark:border-border-dark text-center space-y-2">
                    <div class="w-32 h-32 mx-auto bg-white p-2 rounded-lg border border-gray-200 flex items-center justify-center">
                        <img src="{{ route('meja.qr', $meja->token) }}" alt="QR Meja {{ $meja->nomor }}" class="w-full h-full object-contain" loading="lazy">
                    </div>
                    <div class="text-[10px] font-mono text-muted-dark dark:text-muted-light truncate px-2" title="{{ route('meja.assign', $meja->token) }}">
                        Token: {{ Str::limit($meja->token, 16) }}
                    </div>
                </div>

                {{-- Card Actions --}}
                <div class="flex items-center justify-between pt-2 border-t border-border-light dark:border-border-dark text-xs">
                    <button wire:click="regenerateMejaToken({{ $meja->id }})" wire:confirm="Regenerate QR token meja #{{ $meja->nomor }}?" class="text-gas hover:underline font-medium flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Regen QR</span>
                    </button>
                    <div class="flex items-center gap-2">
                        <button wire:click="toggleOccupied({{ $meja->id }})" class="p-1.5 {{ $meja->is_occupied ? 'text-daun hover:bg-daun/10' : 'text-cabai hover:bg-cabai/10' }} rounded-lg transition-colors" title="{{ $meja->is_occupied ? 'Bebaskan Meja' : 'Tandai Terisi' }}">
                            @if($meja->is_occupied)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            @endif
                        </button>
                        <button wire:click="openEditMejaModal({{ $meja->id }})" class="p-1.5 text-gas hover:bg-gas/10 rounded-lg transition-colors" title="Edit Meja">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </button>
                        <button wire:click="deleteMeja({{ $meja->id }})" wire:confirm="Yakin ingin menghapus Meja #{{ $meja->nomor }}?" class="p-1.5 text-cabai hover:bg-cabai/10 rounded-lg transition-colors" title="Hapus Meja">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full py-12 text-center bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl">
                <p class="text-arang dark:text-paper font-medium">Belum Ada Meja Terdaftar</p>
                <p class="text-xs text-muted-dark dark:text-muted-light mt-1">Klik "+ Tambah Meja Baru" untuk menambahkan unit meja.</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL DIALOG: FORM MEJA (Create / Edit) --}}
    @if($showMejaModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-data x-transition.opacity>
            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl max-w-sm w-full p-6 space-y-5 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-3">
                    <h3 class="text-lg font-display font-bold text-arang dark:text-paper">
                        {{ $editingMejaId ? 'Edit Data Meja' : 'Tambah Meja Baru' }}
                    </h3>
                    <button wire:click="$set('showMejaModal', false)" class="text-muted-dark hover:text-ink dark:text-muted-light">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveMeja" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Nomor Meja</label>
                        <input wire:model="nomorMeja" type="text" placeholder="Contoh: 12" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                        @error('nomorMeja') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Status Meja</label>
                        <select wire:model="statusMeja" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-border-light dark:border-border-dark">
                        <button type="button" wire:click="$set('showMejaModal', false)" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-daun hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">Simpan Meja</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</section>
