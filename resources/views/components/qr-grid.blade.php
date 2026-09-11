{{-- QR Grid Component - Shared between Kasir and Admin --}}
{{-- Usage: <x-qr-grid :mejas="$mejas" :showActions="true" :layout="grid" /> --}}

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($mejas as $meja)
        <div wire:key="qr-{{ $meja['id'] }}" class="border border-border-light dark:border-border-dark rounded-xl p-4 text-center bg-paper-card dark:bg-surface transition-all duration-300 hover:shadow-md {{ $updatedMejaId === $meja['id'] ? 'ring-2 ring-accent shadow-lg' : '' }}">
            <div class="mb-2">
                <span class="text-lg font-bold text-arang dark:text-paper">Meja {{ $meja['nomor'] }}</span>
                @if($meja['is_occupied'])
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-cabai text-white">
                        Terpakai
                    </span>
                @else
                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-daun text-white">
                        Tersedia
                    </span>
                @endif
            </div>

            @if($meja['is_occupied'] && $meja['sesi_started_at'])
                <div class="text-[11px] text-muted-dark dark:text-muted-light mb-2">Sesi aktif sejak {{ $meja['sesi_started_at'] }}</div>
            @endif

            <div class="flex justify-center mb-3">
                <img src="{{ route('meja.qr', $meja['token']) }}" alt="QR Meja {{ $meja['nomor'] }}" width="120" height="120" loading="lazy" class="rounded-lg bg-white dark:bg-surface p-2">
            </div>

            <div class="text-xs text-muted-dark dark:text-muted-light break-all mb-2 font-mono">
                {{ route('meja.assign', $meja['token']) }}
            </div>

            @if($showActions ?? true)
                @if($meja['is_occupied'])
                    <button
                        wire:click="releaseMeja({{ $meja['id'] }})"
                        wire:loading.attr="disabled"
                        class="w-full px-3 py-1.5 text-xs text-cabai bg-paper dark:bg-ink hover:bg-kertas dark:hover:bg-arang border border-cabai rounded transition-colors"
                    >
                        Bebaskan Meja
                    </button>
                @else
                    <button
                        wire:click="occupyMeja({{ $meja['id'] }})"
                        wire:loading.attr="disabled"
                        class="w-full px-3 py-1.5 text-xs text-daun bg-paper dark:bg-ink hover:bg-kertas dark:hover:bg-arang border border-daun rounded transition-colors"
                    >
                        Tandai Terisi
                    </button>
                @endif
            @endif
        </div>
    @empty
        <p class="col-span-full text-center text-muted-dark dark:text-muted-light py-8">Tidak ada meja aktif.</p>
    @endforelse
</div>