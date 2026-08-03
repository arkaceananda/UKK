<?php

use App\Http\Controllers\Kasir\DashboardController;
use App\Models\Meja;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    if (auth()->check() && auth()->user()->role === \App\Enums\UserRole::Kasir) {
        return redirect()->route('kasir.dashboard');
    }
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('/menu', 'customer.menu')
    ->name('customer.menu');

Route::get('/menu/{meja}/checkout', function (Meja $meja) {
    return view('customer.checkout', ['meja' => $meja]);
})->name('checkout');

Route::view('/checkout', 'customer.checkout')
    ->name('customer.checkout');

Route::middleware(['auth', 'kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/history', 'kasir.history')->name('history');
    Route::view('/manual-order', 'kasir.manual-order')->name('manual-order');
});

require __DIR__.'/auth.php';
