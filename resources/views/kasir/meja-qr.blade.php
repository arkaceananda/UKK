<x-layouts.kasir>
    <x-slot name="pageTitle">QR Code Meja</x-slot>

    <div class="mb-4">
        <p class="text-sm text-muted-dark">Scan QR code di bawah ini untuk masuk ke nomor meja. Meja yang sudah di-scan akan otomatis terblokir.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($mejas as $meja)
            <div class="border border-border-light rounded-lg p-4 text-center bg-paper-card">
                <div class="mb-2">
                    <span class="text-lg font-bold text-ink">Meja {{ $meja->nomor }}</span>
                    @if($meja->is_occupied)
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
                    {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(120)->generate(url('/scan/' . $meja->token)) !!}
                </div>

                <div class="text-xs text-muted-dark break-all mb-2">
                    {{ url('/scan/' . $meja->token) }}
                </div>

                @if($meja->is_occupied)
                    <form method="POST" action="{{ route('kasir.meja-qr') }}">
                        @csrf
                        <input type="hidden" name="release_meja" value="1">
                        <input type="hidden" name="meja_id" value="{{ $meja->id }}">
                        <button type="submit"
                            class="w-full px-3 py-1 text-xs text-cabai bg-paper hover:bg-kertas border border-cabai rounded transition-colors">
                            Release Meja
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
</x-layouts.kasir>
