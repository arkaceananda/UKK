<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Pesanan $pesanan,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('kasir-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'OrderPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->pesanan->id,
            'meja_nomor' => $this->pesanan->meja->nomor,
            'total_harga' => $this->pesanan->total_harga,
            'status' => $this->pesanan->status->value,
            'items' => $this->pesanan->details->map(fn ($d) => [
                'nama' => $d->menu->nama,
                'jumlah' => $d->jumlah,
                'harga' => $d->harga,
            ]),
            'created_at' => $this->pesanan->created_at->toISOString(),
        ];
    }
}
