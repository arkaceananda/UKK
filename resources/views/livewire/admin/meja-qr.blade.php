<div class="space-y-6">
    <div class="mb-4">
        <h2 class="text-xl font-display font-bold text-arang dark:text-paper">QR Code Meja</h2>
        <p class="text-sm text-muted-dark dark:text-muted-light">Scan QR code di bawah ini untuk masuk ke nomor meja. QR bersifat statis — token meja diperbarui otomatis secara real-time saat status meja berubah. Meja yang sudah di-scan akan otomatis terblokir.</p>
    </div>

    <x-qr-grid :mejas="$mejas" :updatedMejaId="$updatedMejaId" :showActions="true" />
</div>