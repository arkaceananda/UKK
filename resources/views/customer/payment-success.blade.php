@extends('layouts.customer')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12">
    <div class="max-w-md w-full text-center">
        <div class="w-16 h-16 bg-daun rounded-full flex items-center justify-center mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
            </svg>
        </div>

        <h1 class="text-2xl font-display font-bold text-ink mb-2">Pembayaran Berhasil!</h1>

        <p class="text-muted-dark mb-6">Terima kasih, pembayaran Anda telah dikonfirmasi.</p>

        <a href="/menu" class="inline-block px-6 py-3 bg-accent text-white rounded-lg font-medium hover:bg-daun transition-colors">
            Pesan Lagi
        </a>
    </div>
</div>
@endsection
