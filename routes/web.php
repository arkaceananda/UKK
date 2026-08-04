<?php

use App\Enums\StatusMeja;
use App\Enums\UserRole;
use App\Http\Controllers\Kasir\DashboardController;
use App\Livewire\Customer\Checkout;
use App\Models\Meja;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->role === UserRole::Kasir) {
        return redirect()->route('kasir.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/menu', function () {
    $meja = Meja::where('status', StatusMeja::Aktif)->first();
    if (! $meja) {
        $meja = Meja::factory()->create(['nomor' => '5', 'status' => StatusMeja::Aktif]);
    }

    return view('customer.menu', compact('meja'));
});

Route::get('/menu/{meja}', function (Meja $meja) {
    return view('customer.menu', compact('meja'));
})->name('customer.menu');

Route::get('/menu/{meja}/checkout', Checkout::class)->name('customer.checkout');

Route::middleware(['auth', 'kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/history', 'kasir.history')->name('history');
    Route::view('/manual-order', 'kasir.manual-order')->name('manual-order');
});

require __DIR__.'/auth.php';
