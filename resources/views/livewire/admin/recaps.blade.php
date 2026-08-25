<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-2xl md:text-3xl font-display font-bold text-arang dark:text-paper">Recap Penjualan</h2>
            <p class="text-sm text-muted-dark dark:text-muted-light">Rekap dihitung otomatis tiap hari. Satu baris = satu tanggal.</p>
        </div>
        <a href="{{ route('admin.recaps.export') }}" class="self-start sm:self-auto px-4 py-2 bg-gas hover:bg-opacity-90 text-white text-sm font-medium rounded-xl transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            <span>Export PDF</span>
        </a>
    </div>

    {{-- Recap Table (responsive, sticky header + sticky first column) --}}
    <div class="overflow-x-auto max-h-[70vh] rounded-2xl border border-border-light dark:border-border-dark bg-paper-card dark:bg-surface">
        <table class="w-full text-sm border-collapse">
            <thead class="sticky top-0 z-10">
                <tr class="bg-surface dark:bg-arang text-paper dark:text-paper">
                    <th class="sticky left-0 top-0 z-20 bg-surface dark:bg-arang text-left font-semibold px-4 py-3 whitespace-nowrap">Tanggal</th>
                    <th class="text-right font-semibold px-4 py-3 whitespace-nowrap">Pendapatan</th>
                    <th class="text-right font-semibold px-4 py-3 whitespace-nowrap">Pesanan</th>
                    <th class="text-right font-semibold px-4 py-3 whitespace-nowrap">Item</th>
                    <th class="text-left font-semibold px-4 py-3 whitespace-nowrap">Top Menu</th>
                    <th class="text-left font-semibold px-4 py-3 whitespace-nowrap">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recaps as $recap)
                    <tr class="border-t border-border-light dark:border-border-dark hover:bg-kertas/40 dark:hover:bg-arang/40">
                        <td class="sticky left-0 z-10 bg-paper-card dark:bg-surface px-4 py-3 whitespace-nowrap">
                            <div class="font-display font-bold text-arang dark:text-paper">
                                {{ \Carbon\Carbon::parse($recap->period_start)->translatedFormat('d M Y') }}
                            </div>
                            <div class="text-xs text-muted-dark dark:text-muted-light font-mono">
                                {{ \Carbon\Carbon::parse($recap->period_start)->translatedFormat('l') }}
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold text-arang dark:text-kertas whitespace-nowrap">Rp {{ number_format($recap->total_revenue, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-arang dark:text-kertas whitespace-nowrap">{{ number_format($recap->total_orders, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-mono text-arang dark:text-kertas whitespace-nowrap">{{ number_format($recap->total_items_sold, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if(!empty($recap->top_menus))
                                <div class="flex flex-col gap-0.5">
                                    @foreach(array_slice($recap->top_menus, 0, 3) as $menu)
                                        <span class="text-xs text-arang dark:text-kertas">{{ $loop->iteration }}. {{ $menu['nama'] }} ({{ $menu['total'] }})</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-muted-dark dark:text-muted-light">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($recap->is_finalized)
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-daun/20 text-daun border border-daun/30">Final</span>
                            @else
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-cabai/20 text-cabai border border-cabai/30">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center">
                            <p class="text-arang dark:text-paper font-medium">Belum Ada Recap</p>
                            <p class="text-xs text-muted-dark dark:text-muted-light mt-1">Recap harian akan muncul otomatis tiap hari. Jalankan <code class="font-mono">php artisan recaps:generate backfill</code> untuk mengisi data dari transaksi yang sudah ada.</p>
                        </td>
                    </tr>
                @endforelse

                <tr class="border-t-2 border-border-light dark:border-border-dark bg-kertas/60 dark:bg-arang/60 font-bold">
                    <td class="sticky left-0 z-10 bg-kertas dark:bg-arang px-4 py-3 whitespace-nowrap text-arang dark:text-paper">Total</td>
                    <td class="px-4 py-3 text-right font-mono text-arang dark:text-kertas whitespace-nowrap">Rp {{ number_format($totals['total_revenue'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-arang dark:text-kertas whitespace-nowrap">{{ number_format($totals['total_orders'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono text-arang dark:text-kertas whitespace-nowrap">{{ number_format($totals['total_items_sold'], 0, ',', '.') }}</td>
                    <td class="px-4 py-3"></td>
                    <td class="px-4 py-3"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="pt-2">
        {{ $recaps->links() }}
    </div>
</div>
