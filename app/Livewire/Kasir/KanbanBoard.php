<?php

namespace App\Livewire\Kasir;

use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Events\OrderStatusUpdated;
use App\Events\StockUpdated;
use App\Models\Pesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class KanbanBoard extends Component
{
    public $menungguOrders;

    public $diterimaOrders;

    public $diprosesOrders;

    public $selesaiOrders;

    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders()
    {
        $this->menungguOrders = Pesanan::with(['meja', 'details.menu'])
            ->where('status', StatusPesanan::Menunggu)
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $this->diterimaOrders = Pesanan::with(['meja', 'details.menu'])
            ->where('status', StatusPesanan::Diterima)
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $this->diprosesOrders = Pesanan::with(['meja', 'details.menu'])
            ->where('status', StatusPesanan::Diproses)
            ->orderBy('created_at')
            ->limit(20)
            ->get();

        $this->selesaiOrders = Pesanan::with(['meja', 'details.menu'])
            ->where('status', StatusPesanan::Selesai)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function acceptOrder($orderId)
    {
        $order = Pesanan::findOrFail($orderId);
        $order->kasir_id = (int) Auth::id();
        $order->save();

        try {
            $order->transitionTo(StatusPesanan::Diterima);
            event(new OrderStatusUpdated($order->fresh()));
            $this->loadOrders();
        } catch (\DomainException $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function rejectOrder($orderId)
    {
        $order = Pesanan::with(['details.menu', 'transaksi', 'meja'])->findOrFail($orderId);
        $order->kasir_id = (int) Auth::id();
        $order->save();

        try {
            DB::transaction(function () use ($order) {
                $order->transitionTo(StatusPesanan::Ditolak);

                foreach ($order->details as $detail) {
                    $menu = $detail->menu->fresh();
                    $menu->increment('stok', $detail->jumlah);
                    event(new StockUpdated($menu->id, $menu->fresh()->stok, $menu->fresh()->status->value));
                }

                if ($order->transaksi) {
                    $order->transaksi->update(['status_bayar' => StatusBayar::Pending]);
                }

                $order->meja->update(['is_occupied' => false]);
            });

            event(new OrderStatusUpdated($order->fresh()));
            $this->loadOrders();
        } catch (\DomainException $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function startProcessing($orderId)
    {
        $order = Pesanan::findOrFail($orderId);

        try {
            $order->transitionTo(StatusPesanan::Diproses);
            event(new OrderStatusUpdated($order->fresh()));
            $this->loadOrders();
        } catch (\DomainException $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    public function completeOrder($orderId)
    {
        $order = Pesanan::findOrFail($orderId);

        try {
            DB::transaction(function () use ($order) {
                $order->transitionTo(StatusPesanan::Selesai);
                $order->meja->update(['is_occupied' => false]);
            });
            event(new OrderStatusUpdated($order->fresh()));
            $this->loadOrders();
        } catch (\DomainException $e) {
            $this->dispatch('error', message: $e->getMessage());
        }
    }

    #[On('echo:kasir-channel,OrderPlaced')]
    public function onNewOrder()
    {
        $this->loadOrders();
        gc_collect_cycles();
    }

    public function render()
    {
        return view('livewire.kasir.kanban-board');
    }
}
