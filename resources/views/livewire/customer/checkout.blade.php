@if (! $verified)
    <div class="min-h-[70vh] flex flex-col items-center justify-center px-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-kertas dark:bg-surface border border-border-light dark:border-border-dark flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
        </div>
        <h2 class="font-display font-semibold text-lg text-arang dark:text-kertas mb-2">Silakan Scan Ulang QR Meja</h2>
        <p class="text-sm text-muted-dark dark:text-muted-light mb-6 max-w-xs">Sesi meja ini sudah berakhir. Scan QR code di meja untuk mulai memesan kembali.</p>
        <a href="{{ route('meja.assign', $mejaToken) }}" class="px-6 py-3 bg-accent hover:bg-accent-dark text-ink font-semibold text-sm rounded-xl transition-colors">Scan Ulang</a>
    </div>
@else
<div class="pb-32" wire:on.window="refreshStock" wire:poll.15s="refreshStock">

    {{-- HEADER --}}
    <div class="px-4 pt-4 pb-3">
        <div class="flex items-center justify-between">
            <button wire:click="backToMenu" class="flex items-center gap-2 text-arang dark:text-kertas">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                <span class="font-display font-semibold text-base">Pesanan Anda</span>
            </button>
            <span class="shrink-0 px-4 py-1.5 rounded-full bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas font-medium">
                Meja {{ $nomorMeja }}
            </span>
        </div>
    </div>

    {{-- RINGKASAN PESANAN — Card --}}
    <div class="px-4">
        <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="font-display font-semibold text-arang dark:text-kertas">Ringkasan Pesanan</h2>
                <span class="text-accent text-sm font-medium">{{ count($cart) }} Menu Terpilih</span>
            </div>

            @if(count($cart) > 0)
                <div class="space-y-3">
                    @foreach($cart as $key => $item)
                        <div class="relative flex gap-3 bg-paper dark:bg-ink rounded-xl p-3">
                            <button
                                wire:click="removeItem('{{ $key }}')"
                                class="absolute top-2 right-2 text-muted-dark dark:text-muted-light hover:text-cabai transition-colors"
                                aria-label="Hapus {{ $item['nama'] }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </button>

                            <div class="w-12 h-12 shrink-0 rounded-lg bg-black/5 dark:bg-surface-alt overflow-hidden flex items-center justify-center">
                                @if($item['foto'])
                                    <img src="{{ asset('storage/' . $item['foto']) }}" alt="{{ $item['nama'] }}" class="w-full h-full object-cover" />
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="font-display font-semibold text-sm text-arang dark:text-kertas truncate">{{ $item['nama'] }}</p>
                                @if(!empty($item['selected_option']))
                                    <span class="inline-block mt-0.5 px-2 py-0.5 text-[10px] font-semibold rounded-full bg-accent/15 text-accent">{{ ucfirst($item['selected_option']) }}</span>
                                @endif
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="font-mono text-xs text-muted-dark dark:text-muted-light">Qty: {{ $item['jumlah'] }}</span>
                                    <span class="text-muted-dark dark:text-muted-light">×</span>
                                    <span class="font-mono text-xs text-arang dark:text-kertas">Rp {{ number_format($item['harga'], 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <div class="flex items-end">
                                <span class="font-mono text-sm font-semibold text-arang dark:text-kertas">
                                    Rp {{ number_format($item['harga'] * $item['jumlah'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light mb-3">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <p class="text-muted-dark dark:text-muted-light text-sm">Keranjang kosong</p>
                </div>
            @endif
        </div>
    </div>

    {{-- CATATAN UNTUK DAPUR --}}
    <div class="px-4 pt-4">
        <label class="text-sm text-arang dark:text-kertas font-medium mb-1.5 block">Catatan Untuk Dapur</label>
        <textarea
            wire:model="notes"
            rows="2"
            placeholder="cth. Tidak Pedas"
            class="w-full px-4 py-3 rounded-xl bg-paper-card dark:bg-ink border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas placeholder-muted-dark dark:placeholder-muted-light focus:outline-none focus:ring-2 focus:ring-accent resize-none"
        ></textarea>
    </div>

    {{-- METODE PEMBAYARAN --}}
    <div class="px-4 pt-4">
        <label class="text-sm text-arang dark:text-kertas font-medium mb-1.5 block">Metode Pembayaran</label>
        <div class="grid grid-cols-2 gap-3">
            <button
                wire:click="$set('metodeBayar', 'tunai')"
                class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 transition-colors
                    {{ $metodeBayar === 'tunai'
                        ? 'bg-accent/10 border-accent'
                        : 'bg-paper-card dark:bg-surface border-border-light dark:border-border-dark opacity-50' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $metodeBayar === 'tunai' ? 'text-accent' : 'text-muted-dark dark:text-muted-light' }}">
                    <rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="3"/>
                </svg>
                <span class="text-sm font-medium {{ $metodeBayar === 'tunai' ? 'text-accent' : 'text-arang dark:text-kertas' }}">Tunai</span>
            </button>

            <button
                wire:click="$set('metodeBayar', 'qris')"
                class="flex flex-col items-center justify-center gap-2 py-4 rounded-xl border-2 transition-colors
                    {{ $metodeBayar === 'qris'
                        ? 'bg-accent/10 border-accent'
                        : 'bg-paper-card dark:bg-surface border-border-light dark:border-border-dark opacity-50' }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="{{ $metodeBayar === 'qris' ? 'text-accent' : 'text-muted-dark dark:text-muted-light' }}">
                    <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><line x1="14" y1="14" x2="14" y2="21"/><line x1="21" y1="14" x2="21" y2="14.01"/><line x1="14" y1="21" x2="21" y2="21"/>
                </svg>
                <span class="text-sm font-medium {{ $metodeBayar === 'qris' ? 'text-accent' : 'text-arang dark:text-kertas' }}">QRIS</span>
            </button>
        </div>
    </div>

    {{-- SUBTOTAL --}}
    <div class="px-4 pt-4">
        <div class="flex items-center justify-between bg-paper-card dark:bg-surface rounded-xl border border-border-light dark:border-border-dark px-4 py-3">
            <span class="text-sm text-arang dark:text-kertas font-medium">Total</span>
            <span class="font-mono font-bold text-arang dark:text-kertas">Rp {{ number_format((int) $subtotal, 0, ',', '.') }}</span>
        </div>
    </div>

    {{-- FLOATING CHECKOUT BUTTON --}}
    <div class="fixed bottom-0 left-0 right-0 z-50 px-4 pb-4">
        <button
            wire:click="checkout"
            class="mx-auto max-w-lg w-full flex items-center justify-between px-5 py-3.5 rounded-2xl font-semibold text-sm shadow-elevated transition-colors
                {{ $metodeBayar !== ''
                    ? 'bg-accent hover:bg-accent-dark text-ink cursor-pointer'
                    : 'bg-muted-light/30 dark:bg-muted-dark/20 text-muted-light dark:text-muted-dark cursor-not-allowed' }}"
        >
            <span class="flex items-center gap-1">
                Pesan Sekarang
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            </span>
            <span class="font-mono font-bold">Rp {{ number_format((int) $subtotal, 0, ',', '.') }}</span>
        </button>
    </div>
</div>
@endif