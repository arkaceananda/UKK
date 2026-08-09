<div class="max-w-2xl mx-auto py-12 px-4">
    @if($paid)
        <div class="text-center">
            <div class="w-16 h-16 bg-daun rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <h1 class="text-2xl font-display font-bold text-ink mb-2">Pembayaran Berhasil!</h1>
            <p class="text-muted-dark mb-6">Terima kasih, pembayaran Anda telah dikonfirmasi.</p>
            <a href="/menu/{{ $transaksi->pesanan->meja_id ?? '' }}" class="px-6 py-3 bg-accent text-white rounded-lg font-medium hover:bg-daun transition-colors">
                Pesan Lagi
            </a>
        </div>
    @else
        <div class="text-center">
            <h1 class="text-2xl font-display font-bold text-ink mb-6">Pembayaran QRIS</h1>

            <div class="mb-4">
                <span class="text-sm text-muted-dark">Meja {{ $transaksi->pesanan->meja->nomor ?? '-' }}</span>
            </div>

            <div class="mb-6">
                <span class="text-lg font-bold text-ink">Rp {{ number_format($transaksi->total_bayar) }}</span>
            </div>

            @if($transaksi->qr_code_url)
                <div class="mb-6">
                    <div class="inline-block border border-border-light rounded-lg bg-white p-4 mb-4">
                        <img src="{{ $transaksi->qr_code_url }}" alt="QRIS Code" class="w-48 h-48 object-contain" />
                    </div>

                    <a href="{{ $transaksi->qr_code_url }}" download="qris-{{ $transaksi->id }}.png"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gas bg-paper-card border border-border-light rounded-lg hover:bg-kertas transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 15V3m0 0l-4 4m4-4l4 4" />
                        </svg>
                        Unduh QR Code
                    </a>
                </div>
            @elseif($transaksi->qr_string)
                <div class="mb-6">
                    <div class="inline-block border border-border-light rounded-lg bg-white p-4 mb-4">
                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(200)->generate($transaksi->qr_string) !!}
                    </div>

                    <a href="data:image/svg+xml;base64,{{ base64_encode(\SimpleSoftwareIO\QrCode\Facades\QrCode::size(300)->generate($transaksi->qr_string)) }}" download="qris-{{ $transaksi->id }}.svg"
                       class="inline-flex items-center gap-2 px-4 py-2 text-sm text-gas bg-paper-card border border-border-light rounded-lg hover:bg-kertas transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1M12 15V3m0 0l-4 4m4-4l4 4" />
                        </svg>
                        Unduh QR Code
                    </a>
                </div>
            @else
                <div class="mb-6 text-center text-muted-dark">
                    QR code belum tersedia. Silakan hubungi kasir.
                </div>
            @endif

            <div class="text-xs text-muted-dark mb-4">
                Scan QR code di atas dengan aplikasi kode QR favorit Anda
            </div>

            <div class="bg-paper-card border border-border-light rounded-lg p-4 mb-6">
                <div class="font-mono text-xs text-center break-all">
                    {{ $transaksi->midtrans_order_id ?? 'N/A' }}
                </div>
            </div>

            <div class="text-sm text-muted-dark">
                Menunggu pembayaran...
                <span class="font-medium">Jangan tutup halaman ini.</span>
            </div>

            <div class="mt-4">
                <span class="text-xs text-muted-dark">Status: </span>
                <span class="text-xs font-medium {{ $transaksi->status_bayar === \App\Enums\StatusBayar::Lunas ? 'text-daun' : 'text-cabai' }}">
                    {{ $transaksi->status_bayar === \App\Enums\StatusBayar::Lunas ? 'LUNAS' : 'PENDING' }}
                </span>
            </div>

            @if($isSandbox)
                <button wire:click="simulatePayment"
                        class="mt-6 px-4 py-2 bg-cabai text-white text-sm font-medium rounded-lg hover:bg-daun transition-colors">
                    Simulate Payment Success (Sandbox)
                </button>
            @endif
        </div>
    @endif

    <div class="text-center mt-6" wire:poll.5s="checkPaymentStatus">
        <span class="text-xs text-muted-dark">Auto-refresh: 5s</span>
    </div>
</div>
