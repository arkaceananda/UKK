<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($mejas as $meja)
        <div wire:key="meja-{{ $meja['id'] }}" class="border border-border-light rounded-lg p-4 text-center bg-paper-card transition-all duration-300 {{ $updatedMejaId === $meja['id'] ? 'ring-2 ring-accent shadow-lg' : '' }}">
            <div class="mb-2">
                <span class="text-lg font-bold text-ink">Meja {{ $meja['nomor'] }}</span>
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

            <div class="flex justify-center mb-3">
                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate(route('meja.assign', $meja['token'])) !!}
            </div>

            <div class="text-xs text-muted-dark break-all mb-2">
                {{ route('meja.assign', $meja['token']) }}
            </div>

            @if($meja['is_occupied'])
                <button
                    wire:click="releaseMeja({{ $meja['id'] }})"
                    wire:loading.attr="disabled"
                    class="w-full px-3 py-1 text-xs text-cabai bg-paper hover:bg-kertas border border-cabai rounded transition-colors"
                >
                    Bebaskan Meja
                </button>
            @else
                <button
                    wire:click="occupyMeja({{ $meja['id'] }})"
                    wire:loading.attr="disabled"
                    class="w-full px-3 py-1 text-xs text-daun bg-paper hover:bg-kertas border border-daun rounded transition-colors"
                >
                    Tandai Terisi
                </button>
            @endif
        </div>
    @empty
        <p class="col-span-full text-center text-muted-dark">Tidak ada meja aktif.</p>
    @endforelse
</div>
