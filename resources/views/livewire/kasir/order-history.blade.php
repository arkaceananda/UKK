<div class="space-y-6">
    <div class="bg-paper-card dark:bg-surface rounded-lg border border-border-light dark:border-border-dark p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Cari</label>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nomor meja atau nama kasir..."
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Dari Tanggal</label>
                <input 
                    type="date" 
                    wire:model.live="dateFrom"
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Sampai Tanggal</label>
                <input 
                    type="date" 
                    wire:model.live="dateTo"
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-arang dark:text-paper mb-2">Status</label>
                <select 
                    wire:model.live="statusFilter"
                    class="w-full px-4 py-2 bg-paper dark:bg-ink border border-border-light dark:border-border-dark rounded-lg focus:ring-2 focus:ring-accent focus:border-transparent"
                >
                    <option value="">Semua Status</option>
                    <option value="menunggu">Menunggu</option>
                    <option value="diterima">Diterima</option>
                    <option value="ditolak">Ditolak</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="border-b border-border-light dark:border-border-dark">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Meja</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Kasir</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Waktu</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Items</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Total</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-muted-dark dark:text-muted-light uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($orders as $order)
                        <tr wire:key="history-{{ $order->id }}" class="hover:bg-kertas dark:hover:bg-arang transition-colors">
                            <td class="px-4 py-4 text-sm font-mono text-arang dark:text-kertas">#{{ $order->id }}</td>
                            <td class="px-4 py-4 text-sm text-arang dark:text-kertas">Meja {{ $order->meja->nomor }}</td>
                            <td class="px-4 py-4 text-sm text-arang dark:text-kertas">{{ $order->kasir?->name ?? '-' }}</td>
                            <td class="px-4 py-4 text-sm font-mono text-arang dark:text-kertas">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-4 text-sm text-arang dark:text-kertas">
                                <details class="cursor-pointer">
                                    <summary class="text-accent hover:underline">{{ $order->details->count() }} items</summary>
                                    <ul class="mt-2 space-y-1 text-xs">
                                        @foreach($order->details as $detail)
                                            <li class="text-muted-dark dark:text-muted-light">
                                                {{ $detail->quantity }}x {{ $detail->menu->nama }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            </td>
                            <td class="px-4 py-4 text-sm font-semibold text-arang dark:text-kertas">Rp {{ number_format($order->total_harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-4">
                                @php
                                    $statusColors = [
                                        'menunggu' => 'bg-gas/20 text-gas',
                                        'diterima' => 'bg-merak/20 text-merak',
                                        'ditolak' => 'bg-cabai/20 text-cabai',
                                        'diproses' => 'bg-merak/20 text-merak',
                                        'selesai' => 'bg-daun/20 text-daun',
                                    ];
                                @endphp
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-medium {{ $statusColors[$order->status->value] ?? 'bg-muted-light dark:bg-muted-dark text-arang dark:text-kertas' }}">
                                    {{ ucfirst($order->status->value) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-muted-dark dark:text-muted-light">
                                Tidak ada data pesanan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $orders->links() }}
        </div>
    </div>
</div>
