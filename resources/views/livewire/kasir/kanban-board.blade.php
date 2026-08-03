<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-semibold text-base text-arang dark:text-paper">Menunggu</h3>
                <span class="shrink-0 px-3 py-1 rounded-full bg-gas/20 text-gas text-xs font-medium">{{ count($menungguOrders) }}</span>
            </div>

            <div class="space-y-3 max-h-[calc(100vh-16rem)] overflow-y-auto">
                @forelse($menungguOrders as $order)
                    <div wire:key="menunggu-{{ $order->id }}" class="bg-paper dark:bg-ink p-4 rounded-xl border border-border-light dark:border-border-dark">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-semibold text-arang dark:text-paper">Meja {{ $order->meja->nomor }}</p>
                                <p class="text-xs text-muted-dark dark:text-muted-light font-mono">{{ $order->created_at->format('H:i:s') }}</p>
                            </div>
                            <span class="text-base font-bold text-accent">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="space-y-1.5 mb-3">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-dark dark:text-muted-light">{{ $detail->quantity }}x {{ $detail->menu->nama }}</span>
                                    <span class="text-arang dark:text-kertas font-mono text-xs">Rp {{ number_format($detail->harga_satuan * $detail->quantity, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-kertas dark:bg-arang rounded-lg">
                                <p class="text-xs text-muted-dark dark:text-muted-light italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif

                        <div class="flex gap-2">
                            <button 
                                wire:click="acceptOrder({{ $order->id }})"
                                class="flex-1 px-3 py-2.5 bg-daun text-white rounded-xl text-sm font-semibold hover:bg-daun/90 transition-colors"
                            >
                                Terima
                            </button>
                            <button 
                                wire:click="rejectOrder({{ $order->id }})"
                                class="flex-1 px-3 py-2.5 bg-cabai text-white rounded-xl text-sm font-semibold hover:bg-cabai/90 transition-colors"
                            >
                                Tolak
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-muted-dark dark:text-muted-light">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        <p class="text-sm">Tidak ada pesanan menunggu</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-semibold text-base text-arang dark:text-paper">Diproses</h3>
                <span class="shrink-0 px-3 py-1 rounded-full bg-merak/20 text-merak text-xs font-medium">{{ count($diterimaOrders) + count($diprosesOrders) }}</span>
            </div>

            <div class="space-y-3 max-h-[calc(100vh-16rem)] overflow-y-auto">
                @forelse($diterimaOrders as $order)
                    <div wire:key="diterima-{{ $order->id }}" class="bg-paper dark:bg-ink p-4 rounded-xl border border-border-light dark:border-border-dark">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-semibold text-arang dark:text-paper">Meja {{ $order->meja->nomor }}</p>
                                <p class="text-xs text-muted-dark dark:text-muted-light font-mono">{{ $order->created_at->format('H:i:s') }}</p>
                            </div>
                            <span class="text-base font-bold text-accent">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="space-y-1.5 mb-3">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-dark dark:text-muted-light">{{ $detail->quantity }}x {{ $detail->menu->nama }}</span>
                                    <span class="text-arang dark:text-kertas font-mono text-xs">Rp {{ number_format($detail->harga_satuan * $detail->quantity, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-kertas dark:bg-arang rounded-lg">
                                <p class="text-xs text-muted-dark dark:text-muted-light italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif

                        <button 
                            wire:click="startProcessing({{ $order->id }})"
                            class="w-full px-3 py-2.5 bg-merak text-white rounded-xl text-sm font-semibold hover:bg-merak/90 transition-colors"
                        >
                            Mulai Proses
                        </button>
                    </div>
                @empty
                @endforelse

                @forelse($diprosesOrders as $order)
                    <div wire:key="diproses-{{ $order->id }}" class="bg-paper dark:bg-ink p-4 rounded-xl border border-border-light dark:border-border-dark relative overflow-hidden">
                        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-merak to-gas animate-pulse"></div>
                        
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <p class="font-semibold text-arang dark:text-paper">Meja {{ $order->meja->nomor }}</p>
                                <p class="text-xs text-muted-dark dark:text-muted-light font-mono">{{ $order->created_at->format('H:i:s') }}</p>
                                <span class="inline-flex items-center gap-1 mt-1.5 px-2 py-0.5 bg-merak/20 text-merak text-xs rounded-full font-medium">
                                    <span class="w-1.5 h-1.5 rounded-full bg-merak animate-pulse"></span>
                                    Sedang Diproses
                                </span>
                            </div>
                            <span class="text-base font-bold text-accent">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="space-y-1.5 mb-3">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-dark dark:text-muted-light">{{ $detail->quantity }}x {{ $detail->menu->nama }}</span>
                                    <span class="text-arang dark:text-kertas font-mono text-xs">Rp {{ number_format($detail->harga_satuan * $detail->quantity, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>

                        @if($order->catatan)
                            <div class="mb-3 p-2 bg-kertas dark:bg-arang rounded-lg">
                                <p class="text-xs text-muted-dark dark:text-muted-light italic">Catatan: {{ $order->catatan }}</p>
                            </div>
                        @endif

                        <button 
                            wire:click="completeOrder({{ $order->id }})"
                            class="w-full px-3 py-2.5 bg-daun text-white rounded-xl text-sm font-semibold hover:bg-daun/90 transition-colors"
                        >
                            Selesai
                        </button>
                    </div>
                @empty
                    @if(count($diterimaOrders) === 0)
                        <div class="text-center py-12 text-muted-dark dark:text-muted-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40"><circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/></svg>
                            <p class="text-sm">Tidak ada pesanan diproses</p>
                        </div>
                    @endif
                @endforelse
            </div>
        </div>

        <div class="bg-paper-card dark:bg-surface rounded-2xl border border-border-light dark:border-border-dark p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-display font-semibold text-base text-arang dark:text-paper">Selesai</h3>
                <span class="shrink-0 px-3 py-1 rounded-full bg-daun/20 text-daun text-xs font-medium">{{ count($selesaiOrders) }}</span>
            </div>

            <div class="space-y-3 max-h-[calc(100vh-16rem)] overflow-y-auto">
                @forelse($selesaiOrders as $order)
                    <div wire:key="selesai-{{ $order->id }}" class="bg-paper dark:bg-ink p-4 rounded-xl border border-border-light dark:border-border-dark opacity-60">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-2">
                                <div class="w-5 h-5 shrink-0 rounded-full bg-daun flex items-center justify-center mt-0.5">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-arang dark:text-paper">Meja {{ $order->meja->nomor }}</p>
                                    <p class="text-xs text-muted-dark dark:text-muted-light font-mono">{{ $order->created_at->format('H:i:s') }}</p>
                                </div>
                            </div>
                            <span class="text-base font-bold text-arang dark:text-paper">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</span>
                        </div>

                        <div class="space-y-1.5">
                            @foreach($order->details as $detail)
                                <div class="flex justify-between text-sm">
                                    <span class="text-muted-dark dark:text-muted-light">{{ $detail->quantity }}x {{ $detail->menu->nama }}</span>
                                    <span class="text-arang dark:text-kertas font-mono text-xs">Rp {{ number_format($detail->harga_satuan * $detail->quantity, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-muted-dark dark:text-muted-light">
                        <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="mx-auto mb-3 opacity-40"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                        <p class="text-sm">Belum ada pesanan selesai</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
