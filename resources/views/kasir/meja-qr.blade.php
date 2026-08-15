<x-layouts.kasir>
    <x-slot name="pageTitle">QR Code Meja</x-slot>

    <div class="mb-4">
        <p class="text-sm text-muted-dark">Scan QR code di bawah ini untuk masuk ke nomor meja. QR bersifat statis — token meja diperbarui otomatis secara real-time saat status meja berubah. Meja yang sudah di-scan akan otomatis terblokir.</p>
    </div>

    <livewire:kasir.meja-qr />
</x-layouts.kasir>
