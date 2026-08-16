@extends('layouts.customer')

@section('content')
    <div class="min-h-[80vh] flex flex-col items-center justify-center px-6 text-center">
        <div class="w-16 h-16 rounded-2xl bg-kertas dark:bg-surface border border-border-light dark:border-border-dark flex items-center justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-muted-dark dark:text-muted-light"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
        </div>
        <h2 class="font-display font-semibold text-lg text-arang dark:text-kertas mb-2">Scan QR Meja Dulu</h2>
        <p class="text-sm text-muted-dark dark:text-muted-light mb-6 max-w-xs">Untuk mulai memesan, pelanggan wajib memindai QR code yang terpasang di meja. Buka kamera dan arahkan ke QR meja Anda.</p>
        <a href="{{ url('/') }}" class="px-6 py-3 bg-accent hover:bg-accent-dark text-ink font-semibold text-sm rounded-xl transition-colors">Kembali ke Beranda</a>
    </div>
@endsection
