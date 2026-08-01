<?php

use App\Models\Meja;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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

require __DIR__.'/auth.php';
