<?php

use App\Enums\UserRole;
use App\Http\Controllers\Admin\ChartDataController;
use App\Http\Controllers\Admin\RecapExportController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Http\Controllers\Kasir\DashboardController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\TableAssignmentController;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Recaps;
use App\Livewire\Customer\Checkout;
use App\Livewire\Customer\QrisPayment;
use App\Livewire\OrderStatus;
use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('/dashboard', function () {
    if (auth()->check()) {
        if (auth()->user()->role === UserRole::Admin) {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->role === UserRole::Kasir) {
            return redirect()->route('kasir.dashboard');
        }
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/menu', function () {
    $mejaId = session('assigned_meja_id');
    $meja = $mejaId ? Meja::find($mejaId) : null;

    if ($meja && session('assigned_meja_token') === $meja->token) {
        return view('customer.menu', compact('meja'));
    }

    return redirect()->route('customer.scan-required');
});

Route::get('/menu/{meja}', function (Meja $meja) {
    return view('customer.menu', compact('meja'));
})->name('customer.menu');

Route::get('/meja/{token}', [TableAssignmentController::class, 'assign'])
    ->name('meja.assign');

Route::view('/scan-required', 'customer.scan-required')->name('customer.scan-required');

Route::get('/menu/{meja}/checkout', Checkout::class)->name('customer.checkout');

Route::get('/order/{pesanan}/status', OrderStatus::class)
    ->name('order.status');

Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle'])
    ->name('midtrans.webhook');

Route::get('/payment/qris/{transaksi}', QrisPayment::class)
    ->name('customer.payment-qris');

Route::view('/payment/success', 'customer.payment-success')
    ->name('customer.payment-success');

Route::middleware(['auth', 'kasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::view('/history', 'kasir.history')->name('history');
    Route::view('/manual-order', 'kasir.manual-order')->name('manual-order');
    Route::get('/meja-qr', fn () => view('kasir.meja-qr'))->name('meja-qr');
    Route::get('/order/{pesanan}/ticket', fn (Pesanan $pesanan) => view('kitchen.ticket', [
        'pesanan' => $pesanan->load('details.menu', 'meja'),
    ]))->name('ticket');
});

require __DIR__.'/auth.php';

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', Dashboard::class)->name('dashboard');
    Route::get('/recaps', Recaps::class)->name('recaps');
    Route::get('/recaps/export/{recap}', [RecapExportController::class, 'exportCsv'])->name('recaps.export');
    Route::get('/api/chart/sales', [ChartDataController::class, 'sales'])->name('api.chart.sales');
    Route::get('/api/chart/top-menu', [ChartDataController::class, 'topMenu'])->name('api.chart.top-menu');
    Route::get('/sales/export/{filter?}', [SalesReportController::class, 'exportSales'])->name('sales.export');
    Route::get('/top-menu/export/{filter?}', [SalesReportController::class, 'exportTopMenu'])->name('top-menu.export');
});
