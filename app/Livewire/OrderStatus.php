<?php

namespace App\Livewire;

use App\Enums\StatusPesanan;
use App\Models\Pesanan;
use App\Services\TableService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

    #[Computed]
    public function isOwnOrder(): bool
    {
        $mejaId = session('assigned_meja_id');
        $token = session('assigned_meja_token');

        return $mejaId === $this->pesanan->meja_id
            && $token !== null
            && $token === $this->pesanan->meja->token;
    }

    #[Computed]
    public function canCancel(): bool
    {
        return $this->pesanan->status === StatusPesanan::Menunggu && $this->isOwnOrder;
    }

    public function cancelOrder(): void
    {
        if (! $this->canCancel) {
            $this->dispatch('notify', message: 'Pesanan tidak dapat dibatalkan.', type: 'error');

            return;
        }

        $pesanan = $this->pesanan;

        DB::transaction(function () use ($pesanan) {
            foreach ($pesanan->details as $detail) {
                $menu = $detail->menu;

                if ($menu) {
                    $menu->increaseStock($detail->jumlah);
                }

                $detail->delete();
            }

            $pesanan->transaksi()?->delete();
            $pesanan->delete();
        });

        app(TableService::class)->refreshOccupancy($pesanan->meja);

        Cache::flush();

        $this->dispatch('notify', message: 'Pesanan berhasil dibatalkan.', type: 'success');
        $this->redirect(route('customer.menu', ['meja' => $pesanan->meja_id]));
    }

    public function render()
    {
        return view('livewire.order-status');
    }
}
