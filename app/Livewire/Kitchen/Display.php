<?php

namespace App\Livewire\Kitchen;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.guest')]
class Display extends Component
{
    #[On('echo:kitchen,OrderPlaced')]
    #[On('echo:kitchen,OrderStatusUpdated')]
    public function refreshOrders(): void
    {
        $this->reset();
    }

    #[Computed]
    public function baruOrders()
    {
        return Pesanan::with('details.menu', 'meja')
            ->where('status', StatusPesanan::Menunggu)
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function diprosesOrders()
    {
        return Pesanan::with('details.menu', 'meja')
            ->whereIn('status', [StatusPesanan::Diterima, StatusPesanan::Diproses])
            ->orderBy('created_at')
            ->get();
    }

    #[Computed]
    public function siapOrders()
    {
        return Pesanan::with('details.menu', 'meja')
            ->where('status', StatusPesanan::Selesai)
            ->orderBy('updated_at')
            ->get();
    }

    public function acceptOrder(int $pesananId): void
    {
        $pesanan = Pesanan::findOrFail($pesananId);
        $pesanan->transitionTo(StatusPesanan::Diterima);
        $this->dispatch('notify', message: 'Pesanan diterima', type: 'success');
    }

    public function startProcessing(int $pesananId): void
    {
        $pesanan = Pesanan::findOrFail($pesananId);
        $pesanan->transitionTo(StatusPesanan::Diproses);
        $this->dispatch('notify', message: 'Pesanan diproses', type: 'success');
    }

    public function completeOrder(int $pesananId): void
    {
        $pesanan = Pesanan::findOrFail($pesananId);
        $pesanan->transitionTo(StatusPesanan::Selesai);
        $this->dispatch('notify', message: 'Pesanan siap disajikan', type: 'success');
    }

    public function render()
    {
        return view('livewire.kitchen.display');
    }
}
