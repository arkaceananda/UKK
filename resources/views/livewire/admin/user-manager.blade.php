<div class="space-y-6">
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="font-display font-semibold text-lg text-arang dark:text-kertas">Manajemen User</h2>
            <p class="text-sm text-muted-dark dark:text-muted-light">Kelola akun kasir dan admin</p>
        </div>
        <button wire:click="openCreateUserModal" class="px-4 py-2 bg-accent hover:bg-opacity-90 text-white rounded-lg transition-colors font-medium text-sm flex items-center gap-2 shadow-sm shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Tambah User</span>
        </button>
    </div>

    {{-- SEARCH --}}
    <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-dark dark:text-muted-light" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama atau email..." class="w-full pl-10 pr-4 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent focus:border-accent">
            </div>
            <span class="text-sm text-muted-dark dark:text-muted-light shrink-0 flex items-center">
                {{ $users->total() }} user
            </span>
        </div>
    </div>

    {{-- USER TABLE --}}
    <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark overflow-hidden">
        <table class="w-full">
            <thead class="bg-kertas/50 dark:bg-arang/50 border-b border-border-light dark:border-border-dark">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-arang dark:text-kertas uppercase tracking-wider">Nama</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-arang dark:text-kertas uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-arang dark:text-kertas uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-arang dark:text-kertas uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-light dark:divide-border-dark">
                @forelse($users as $user)
                    <tr class="hover:bg-kertas/50 dark:hover:bg-arang/50 transition-colors" wire:key="user-{{ $user->id }}">
                        <td class="px-4 py-3">
                            <div class="font-medium text-arang dark:text-kertas">{{ $user->name }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="text-sm text-muted-dark dark:text-muted-light font-mono">{{ $user->email }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $user->role === 'Admin' ? 'bg-merak/20 text-merak' : 'bg-gas/20 text-gas' }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <button wire:click="openEditUserModal({{ $user->id }})" class="px-3 py-1.5 bg-kertas dark:bg-ink border border-border-light dark:border-border-dark text-arang dark:text-kertas rounded-lg text-sm font-medium hover:bg-black/5 dark:hover:bg-white/5 transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </button>
                                @if($user->id !== auth()->id())
                                    <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Hapus user ini?" class="px-3 py-1.5 bg-cabai/10 text-cabai border border-cabai/20 rounded-lg text-sm font-medium hover:bg-cabai/20 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-12 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40 text-muted-dark dark:text-muted-light">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                            <p class="text-sm text-muted-dark dark:text-muted-light">Tidak ada user ditemukan</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- PAGINATION --}}
        @if($users->hasPages())
            <div class="px-4 py-3 border-t border-border-light dark:border-border-dark">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- MODAL DIALOG: FORM USER --}}
    @if($showUserModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-data x-transition.opacity>
            <div class="bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark rounded-2xl max-w-md w-full p-6 space-y-5 shadow-2xl relative">
                <div class="flex items-center justify-between border-b border-border-light dark:border-border-dark pb-3">
                    <h3 class="text-lg font-display font-bold text-arang dark:text-paper">{{ $editingUserId ? 'Edit User' : 'Tambah User Baru' }}</h3>
                    <button wire:click="$set('showUserModal', false)" class="text-muted-dark hover:text-ink dark:text-muted-light">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>

                <form wire:submit.prevent="saveUser" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Nama</label>
                        <input wire:model="name" type="text" placeholder="Contoh: Budi Santoso" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                        @error('name') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Email</label>
                        <input wire:model="email" type="email" placeholder="contoh@burjo.com" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                        @error('email') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Role</label>
                        <select wire:model="role" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent">
                            <option value="{{ \App\Enums\UserRole::Admin->value }}">Admin</option>
                            <option value="{{ \App\Enums\UserRole::Kasir->value }}">Kasir</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Password {{ $editingUserId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                        <input wire:model="password" type="password" placeholder="{{ $editingUserId ? 'Biarkan kosong untuk tidak mengubah' : 'Masukkan password' }}" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent" autocomplete="new-password">
                        @error('password') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-arang dark:text-paper uppercase tracking-wider mb-1">Konfirmasi Password</label>
                        <input wire:model="password_confirmation" type="password" placeholder="Ulangi password" class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-xl bg-paper dark:bg-ink text-arang dark:text-kertas text-sm focus:ring-accent" autocomplete="new-password">
                        @error('password_confirmation') <span class="text-cabai text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-border-light dark:border-border-dark">
                        <button type="button" wire:click="$set('showUserModal', false)" class="px-4 py-2 text-sm font-medium text-muted-dark hover:text-ink dark:text-muted-light">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-accent hover:bg-opacity-90 text-white rounded-xl font-medium text-sm transition-colors">{{ $editingUserId ? 'Simpan Perubahan' : 'Tambah User' }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>