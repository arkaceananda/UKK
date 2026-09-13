<div class="space-y-6">
    <p class="text-sm text-muted-dark dark:text-muted-light">Scan QR code di bawah untuk masuk ke nomor meja. Token diperbarui realtime saat status berubah.</p>

    <x-qr-grid :mejas="$mejas" :updatedMejaId="$updatedMejaId" :showActions="true" />
</div>