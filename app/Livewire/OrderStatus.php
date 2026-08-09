<?php

namespace App\Livewire;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.customer')]
class OrderStatus extends Component
{
    public Pesanan $pesanan;

    public int $pesananId;

    public function mount(Pesanan $pesanan): void
    {
        $this->pesanan = $pesanan->load('details.menu', 'meja');
        $this->pesananId = $pesanan->id;
    }

    #[On('echo:order.{pesananId},OrderStatusUpdated')]
    public function refreshStatus(): void
    {
        $this->pesanan->refresh();
    }

    #[Computed]
    public function currentStepIndex(): int
    {
        return match ($this->pesanan->status) {
            StatusPesanan::Menunggu => 0,
            StatusPesanan::Diterima => 1,
            StatusPesanan::Diproses => 2,
            StatusPesanan::Selesai => 3,
            default => 0,
        };
    }

    #[Computed]
    public function isSelesai(): bool
    {
        return $this->pesanan->status === StatusPesanan::Selesai;
    }

    #[Computed]
    public function statusMeta(): array
    {
        return match ($this->pesanan->status) {
            StatusPesanan::Menunggu => [
                'title' => 'Pesanan kamu sudah masuk ke kasir',
                'subtitle' => 'Menunggu admin mengkonfirmasi pesananmu...',
            ],
            StatusPesanan::Diterima => [
                'title' => 'Pesanan kamu sedang diproses admin!',
                'subtitle' => 'Pihak kasir sedang meneruskan pesanan kamu ke dapur! harap tunggu, ya!',
            ],
            StatusPesanan::Diproses => [
                'title' => 'Pesanan kamu sedang disiapkan',
                'subtitle' => 'Chef sedang mengolah bahan-bahan untuk santapanmu!',
            ],
            StatusPesanan::Selesai => [
                'title' => 'Pesanan kamu sudah selesai!',
                'subtitle' => 'Nomor mejamu akan dipanggil oleh kasir! segera datangi kasir setelah itu',
            ],
            default => [
                'title' => 'Memuat status pesanan...',
                'subtitle' => '',
            ],
        };
    }

    public function render()
    {
        return view('livewire.order-status');
    }
}
