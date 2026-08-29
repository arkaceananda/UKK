<div x-data="{
    soundEnabled: true,
    playSound(type) {
        if (!this.soundEnabled) return;
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = ctx.createOscillator();
        const gainNode = ctx.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(ctx.destination);
        
        switch(type) {
            case 'new':
                oscillator.frequency.setValueAtTime(800, ctx.currentTime);
                oscillator.frequency.exponentialRampToValueAtTime(400, ctx.currentTime + 0.3);
                gainNode.gain.setValueAtTime(0.3, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.3);
                break;
            case 'ready':
                oscillator.frequency.setValueAtTime(500, ctx.currentTime);
                oscillator.frequency.setValueAtTime(800, ctx.currentTime + 0.1);
                oscillator.frequency.setValueAtTime(1000, ctx.currentTime + 0.2);
                gainNode.gain.setValueAtTime(0.2, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.5);
                oscillator.start(ctx.currentTime);
                oscillator.stop(ctx.currentTime + 0.5);
                break;
        }
    }
}">

<div class="min-h-screen bg-ink p-4 sm:p-6">
    {{-- HEADER --}}
    <header class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-display font-bold text-kertas">Dapur Burjo</h1>
            <p class="text-gas/80">Antrian Pesanan Real-time</p>
        </div>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-kertas/80 cursor-pointer">
                <input type="checkbox" x-model="soundEnabled" class="w-4 h-4 accent-accent rounded">
                <span class="text-sm">Suara Notifikasi</span>
            </label>
            <div class="w-10 h-10 rounded-xl bg-accent/20 flex items-center justify-center">
                <span class="text-accent font-mono text-xs animate-pulse">LIVE</span>
            </div>
        </div>
    </header>

    {{-- SECTION 1: BARU (Menunggu) --}}
    <section class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-display font-bold text-cabai flex items-center gap-2">
                <svg class="w-6 h-6 animate-pulse" fill="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle></svg>
                Baru ({{ $baruOrders->count() }})
            </h2>
        </div>
        @if($baruOrders->isEmpty())
            <div class="bg-surface/50 rounded-2xl p-8 text-center border border-border-dark/50">
                <svg class="w-16 h-16 mx-auto text-muted-light/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-muted-light">Tidak ada pesanan baru</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($baruOrders as $order)
                    <div wire:key="kitchen-baru-{{ $order->id }}" class="bg-surface border-2 border-cabai/50 rounded-2xl p-4 relative overflow-hidden">
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-0.5 text-xs font-bold bg-cabai text-kertas rounded-full animate-pulse">BARU</span>
                        </div>
                        <div class="mb-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-display font-bold text-kertas">Meja {{ $order->meja->nomor }}</span>
                                <span class="text-sm font-mono text-gas">{{ $order->created_at->format('H:i:s') }}</span>
                            </div>
                        </div>
                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm bg-ink/50 rounded-xl p-3">
                                    <span class="text-gas">{{ $detail->jumlah }}x {{ $detail->menu->nama }}@if($detail->selected_option) ({{ ucfirst($detail->selected_option) }})@endif</span>
                                </div>
                            @endforeach
                        </div>
                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-gas/10 rounded-lg border border-gas/30">
                                <p class="text-xs text-gas italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif
                        <button wire:click="acceptOrder({{ $order->id }})" class="w-full py-3 bg-cabai hover:bg-cabai/90 text-kertas font-bold rounded-xl transition-colors text-lg">
                            Terima Pesanan
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- SECTION 2: DIPROSES (Diterima + Diproses) --}}
    <section class="mb-6">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-display font-bold text-merak flex items-center gap-2">
                <svg class="w-6 h-6 animate-spin" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
                Diproses ({{ $diprosesOrders->count() }})
            </h2>
        </div>
        @if($diprosesOrders->isEmpty())
            <div class="bg-surface/50 rounded-2xl p-8 text-center border border-border-dark/50">
                <svg class="w-16 h-16 mx-auto text-muted-light/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-muted-light">Tidak ada pesanan diproses</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($diprosesOrders as $order)
                    <div wire:key="kitchen-diproses-{{ $order->id }}" class="bg-surface border-2 border-merak/50 rounded-2xl p-4 relative overflow-hidden">
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-0.5 text-xs font-bold {{ $order->status === \App\Enums\StatusPesanan::Diterima ? 'bg-gas' : 'bg-merak' }} text-kertas rounded-full flex items-center gap-1">
                                @if($order->status === \App\Enums\StatusPesanan::Diterima)
                                    <span class="w-1.5 h-1.5 rounded-full bg-kertas animate-pulse"></span>
                                @endif
                                {{ $order->status === \App\Enums\StatusPesanan::Diterima ? 'DITERIMA' : 'DIPROSES' }}
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-display font-bold text-kertas">Meja {{ $order->meja->nomor }}</span>
                                <span class="text-sm font-mono text-gas">{{ $order->created_at->format('H:i:s') }}</span>
                            </div>
                        </div>
                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm bg-ink/50 rounded-xl p-3">
                                    <span class="text-gas">{{ $detail->jumlah }}x {{ $detail->menu->nama }}@if($detail->selected_option) ({{ ucfirst($detail->selected_option) }})@endif</span>
                                </div>
                            @endforeach
                        </div>
                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-gas/10 rounded-lg border border-gas/30">
                                <p class="text-xs text-gas italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif
                        @if($order->status === \App\Enums\StatusPesanan::Diterima)
                            <button wire:click="startProcessing({{ $order->id }})" class="w-full py-3 bg-gas hover:bg-gas/90 text-kertas font-bold rounded-xl transition-colors text-lg">
                                Mulai Proses
                            </button>
                        @else
                            <button wire:click="completeOrder({{ $order->id }})" class="w-full py-3 bg-daun hover:bg-daun/90 text-kertas font-bold rounded-xl transition-colors text-lg">
                                Selesai (Siap Sajikan)
                            </button>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- SECTION 3: SIAP DIAMBIL (Selesai) - auto remove after 5 min --}}
    <section>
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-xl font-display font-bold text-daun flex items-center gap-2">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg>
                Siap Diambil ({{ $siapOrders->count() }})
            </h2>
        </div>
        @if($siapOrders->isEmpty())
            <div class="bg-surface/50 rounded-2xl p-8 text-center border border-border-dark/50">
                <svg class="w-16 h-16 mx-auto text-muted-light/30 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <p class="text-muted-light">Tidak ada pesanan siap</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($siapOrders as $order)
                    <div wire:key="kitchen-siap-{{ $order->id }}" class="bg-surface border-2 border-daun/50 rounded-2xl p-4 relative overflow-hidden opacity-80">
                        <div class="absolute top-2 right-2">
                            <span class="px-2 py-0.5 text-xs font-bold bg-daun text-kertas rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"></path></svg>
                                SIAP
                            </span>
                        </div>
                        <div class="mb-3">
                            <div class="flex items-center justify-between">
                                <span class="text-2xl font-display font-bold text-kertas">Meja {{ $order->meja->nomor }}</span>
                                <span class="text-sm font-mono text-gas">{{ $order->updated_at->format('H:i:s') }}</span>
                            </div>
                        </div>
                        <div class="space-y-2 mb-4 max-h-48 overflow-y-auto">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm bg-ink/50 rounded-xl p-3">
                                    <span class="text-gas">{{ $detail->jumlah }}x {{ $detail->menu->nama }}@if($detail->selected_option) ({{ ucfirst($detail->selected_option) }})@endif</span>
                                </div>
                            @endforeach
                        </div>
                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-gas/10 rounded-lg border border-gas/30">
                                <p class="text-xs text-gas italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif
                        <div class="text-center text-xs text-daun">
                            Akan hilang otomatis dalam 5 menit
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script>
    // Listen for Livewire events to play sounds
    document.addEventListener('livewire:load', () => {
        Livewire.on('order-placed', () => {
            if (window.Alpine) {
                // Alpine component will handle sound via x-data
            }
        });
    });
</script>
@endpush