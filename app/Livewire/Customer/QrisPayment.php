<?php

namespace App\Livewire\Customer;

use App\Enums\StatusBayar;
use App\Models\Transaksi;
use App\Services\MidtransService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('Pembayaran QRIS')]
class QrisPayment extends Component
{
    public Transaksi $transaksi;

    public bool $paid = false;

    public function mount(Transaksi $transaksi): void
    {
        $this->transaksi = $transaksi;
    }

    public function checkPaymentStatus(MidtransService $midtransService): void
    {
        if ($this->transaksi->status_bayar === StatusBayar::Lunas) {
            $this->redirectRoute('order.status', $this->transaksi->pesanan);

            return;
        }

        if (! $this->transaksi->midtrans_transaction_id) {
            return;
        }

        try {
            $status = $midtransService->getTransactionStatus($this->transaksi->midtrans_transaction_id);

            if (in_array($status['transaction_status'] ?? '', ['capture', 'settlement'])) {
                $this->transaksi->update(['status_bayar' => StatusBayar::Lunas]);
                $this->redirectRoute('order.status', $this->transaksi->pesanan);
            }
        } catch (\Exception $e) {
        }
    }

    public function simulatePayment(): void
    {
        $this->transaksi->update(['status_bayar' => StatusBayar::Lunas]);
        $this->redirectRoute('order.status', $this->transaksi->pesanan);
    }

    public function render()
    {
        return view('livewire.customer.qris-payment', [
            'isSandbox' => ! (bool) config('services.midtrans.is_production'),
        ]);
    }
}
