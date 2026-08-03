<div class="max-w-4xl mx-auto">
    <div class="bg-paper-card dark:bg-surface rounded-lg border border-border-light dark:border-border-dark p-6">
        <h2 class="text-2xl font-display font-semibold text-arang dark:text-paper mb-6">Buat Pesanan Manual</h2>

        <form wire:submit.prevent="createOrder" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Pilih Meja</label>
                <select 
                    wire:model.live="selectedMeja"
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                    required
                >
                    <option value="">-- Pilih Meja --</option>
                    @foreach($mejaList as $meja)
                        <option value="{{ $meja->id }}">Meja {{ $meja->nomor }}</option>
                    @endforeach
                </select>
                @error('selectedMeja')
                    <p class="mt-1 text-sm text-cabai">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-arang dark:text-paper">Item Pesanan</label>
                    <button 
                        type="button"
                        wire:click="addOrderItem"
                        class="px-3 py-1 bg-accent text-white rounded-lg text-sm font-medium hover:bg-accent/90 transition-colors"
                    >
                        + Tambah Item
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach($orderItems as $index => $item)
                        <div wire:key="item-{{ $index }}" class="flex gap-3 items-start bg-kertas dark:bg-arang p-4 rounded-lg border border-border-light dark:border-border-dark">
                            <div class="flex-1">
                                <label class="block text-xs text-muted-dark dark:text-muted-light mb-1">Menu</label>
                                <select 
                                    wire:model.live="orderItems.{{ $index }}.menu_id"
                                    class="w-full px-3 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent"
                                    required
                                >
                                    <option value="">-- Pilih Menu --</option>
                                    @foreach($menuItems->groupBy('kategori.nama') as $kategoriNama => $menus)
                                        <optgroup label="{{ $kategoriNama }}">
                                            @foreach($menus as $menu)
                                                <option value="{{ $menu->id }}">{{ $menu->nama }} - Rp {{ number_format($menu->harga, 0, ',', '.') }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error("orderItems.{$index}.menu_id")
                                    <p class="mt-1 text-xs text-cabai">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="w-24">
                                <label class="block text-xs text-muted-dark dark:text-muted-light mb-1">Jumlah</label>
                                <input 
                                    type="number" 
                                    wire:model.live="orderItems.{{ $index }}.quantity"
                                    min="1"
                                    class="w-full px-3 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg text-sm focus:ring-2 focus:ring-accent focus:border-transparent"
                                    required
                                >
                                @error("orderItems.{$index}.quantity")
                                    <p class="mt-1 text-xs text-cabai">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="w-32">
                                <label class="block text-xs text-muted-dark dark:text-muted-light mb-1">Subtotal</label>
                                <p class="px-3 py-2 text-sm font-semibold text-arang dark:text-kertas">
                                    Rp {{ number_format(($item['harga'] ?? 0) * ($item['quantity'] ?? 0), 0, ',', '.') }}
                                </p>
                            </div>

                            @if(count($orderItems) > 1)
                                <button 
                                    type="button"
                                    wire:click="removeOrderItem({{ $index }})"
                                    class="mt-6 p-2 text-cabai hover:bg-cabai/10 rounded-lg transition-colors"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('orderItems')
                    <p class="mt-2 text-sm text-cabai">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Catatan (Opsional)</label>
                <textarea 
                    wire:model="catatan"
                    rows="3"
                    placeholder="Tambahkan catatan untuk pesanan ini..."
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent resize-none"
                ></textarea>
            </div>

            <div class="border-t border-border-light dark:border-border-dark pt-6">
                <div class="flex items-center justify-between mb-6">
                    <span class="text-lg font-medium text-arang dark:text-paper">Total Harga</span>
                    <span class="text-2xl font-bold text-arang dark:text-paper">Rp {{ number_format($totalHarga, 0, ',', '.') }}</span>
                </div>

                <div class="flex gap-4">
                    <button 
                        type="button"
                        wire:click="$refresh"
                        class="px-6 py-3 border border-border-light dark:border-border-dark text-arang dark:text-kertas rounded-lg font-medium hover:bg-kertas dark:hover:bg-arang transition-colors"
                    >
                        Reset
                    </button>
                    <button 
                        type="submit"
                        class="flex-1 px-6 py-3 bg-accent text-white rounded-lg font-medium hover:bg-accent/90 transition-colors"
                    >
                        Buat Pesanan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
