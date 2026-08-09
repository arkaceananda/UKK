<div class="space-y-5 pb-24">
    {{-- HEADER --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('customer.menu', ['meja' => $pesanan->meja->id]) }}" wire:navigate class="text-arang dark:text-kertas">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </a>
        <span class="shrink-0 px-4 py-1.5 rounded-full bg-paper-card dark:bg-surface border border-border-light dark:border-border-dark text-sm text-arang dark:text-kertas font-medium">
            Meja {{ $pesanan->meja->nomor }}
        </span>
    </div>

    {{-- STEP TRACKER --}}
    @php
        $labels = ['Menunggu', 'Diterima', 'Diproses', 'Selesai'];
        $current = $this->currentStepIndex;
        $isSelesai = $this->isSelesai;
    @endphp
    <div class="flex items-center justify-between w-full">
        @foreach($labels as $i => $label)
            @php
                $isDone = $i < $current || ($i === $current && $isSelesai);
                $isActive = $i === $current && ! $isSelesai;
            @endphp

            <div class="flex flex-col items-center z-10">
                <div class="flex items-center gap-2">
                    @if($i > 0)
                        <div class="w-10 h-0.5 {{ $i <= $current ? 'bg-daun' : 'bg-border-light dark:bg-border-dark' }}"></div>
                    @endif

                    <div class="w-7 h-7 shrink-0 rounded-full flex items-center justify-center
                        {{ $isDone ? 'bg-daun' : ($isActive ? 'bg-daun/80 ring-4 ring-daun/20' : 'bg-border-light dark:bg-border-dark') }}">
                        @if($isDone)
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @endif
                    </div>

                    @if($i < count($labels) - 1)
                        <div class="w-10 h-0.5 {{ $i < $current || $isSelesai ? 'bg-daun' : 'bg-border-light dark:bg-border-dark' }}"></div>
                    @endif
                </div>
                <span class="text-[10px] text-center leading-tight mt-1 {{ $isDone || $isActive ? 'text-daun font-medium' : 'text-muted-dark dark:text-muted-light' }}">
                    {{ $label }}
                </span>
            </div>
        @endforeach
    </div>

    {{-- STATUS UTAMA --}}
    <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-6 flex flex-col items-center text-center">
        <div class="w-16 h-16 rounded-full flex items-center justify-center mb-4 {{ $isSelesai ? 'bg-daun/15' : 'bg-accent/15' }}">
            @if($isSelesai)
                <div class="w-10 h-10 rounded-full bg-daun flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            @else
                <div class="relative w-8 h-8">
                    <div class="absolute inset-0 rounded-full bg-gas animate-ping opacity-40"></div>
                    <div class="relative w-8 h-8 rounded-full bg-gas"></div>
                </div>
            @endif
        </div>

        <h2 class="font-display font-semibold text-arang dark:text-kertas">{{ $this->statusMeta['title'] }}</h2>
        <p class="text-sm text-muted-dark dark:text-muted-light mt-1">{{ $this->statusMeta['subtitle'] }}</p>

        <p class="text-xs text-muted-dark dark:text-muted-light mt-4">
            @if($isSelesai)
                Kamu sudah bisa meninggalkan layar ini dengan kembali ke menu utama
            @else
                Mohon jangan matikan layarmu dan terus perhatikan proses pesananmu :)
            @endif
        </p>
    </div>

    {{-- RINGKASAN PESANAN --}}
    <div x-data="{ open: true }" class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark overflow-hidden">
        <button @click="open = !open" class="w-full flex items-center justify-between px-4 py-3.5">
            <span class="flex items-center gap-2 font-display font-semibold text-sm text-arang dark:text-kertas">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 2H8a2 2 0 0 0-2 2v16l3-2 3 2 3-2 3 2V4a2 2 0 0 0-2-2Z"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="9" y1="12" x2="15" y2="12"/></svg>
                Ringkasan Pesanan
            </span>
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light transition-transform" :class="open ? 'rotate-180' : ''"><polyline points="18 15 12 9 6 15"/></svg>
        </button>

        <div x-show="open" x-collapse class="px-4 pb-4 space-y-3">
            @foreach($pesanan->details as $detail)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 shrink-0 rounded-lg bg-black/5 dark:bg-surface"></div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs text-arang dark:text-kertas truncate">{{ $detail->menu->nama }} <span class="text-muted-dark dark:text-muted-light">x{{ $detail->jumlah }}</span></p>
                        <p class="text-xs font-mono text-muted-dark dark:text-muted-light">Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</p>
                    </div>
                </div>
            @endforeach

            <div class="pt-2 border-t border-border-light dark:border-border-dark space-y-1">
                <div class="flex justify-between text-xs">
                    <span class="text-muted-dark dark:text-muted-light">Subtotal</span>
                    <span class="font-mono text-arang dark:text-kertas">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-sm font-semibold">
                    <span class="text-arang dark:text-kertas">Total</span>
                    <span class="font-mono text-accent">Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    @unless($isSelesai)
        <p class="flex items-center justify-center gap-1.5 text-xs text-muted-dark dark:text-muted-light">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><polyline points="21 3 21 9 15 9"/></svg>
            Halaman ini akan otomatis diperbarui
        </p>
    @else
        <a
            href="{{ route('customer.menu', ['meja' => $pesanan->meja->id]) }}"
            wire:navigate
            class="block text-center w-full py-3 rounded-xl bg-accent hover:bg-accent-dark text-ink font-semibold text-sm"
        >
            🍽️ Pesan Lagi
        </a>
    @endunless

    <div wire:poll.5s="refreshStatus" class="hidden">
        <span class="text-xs text-muted-dark">Polling active</span>
    </div>
</div>
