<?php

namespace App\Events;

use App\Models\Pesanan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Pesanan $pesanan,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('meja.'.$this->pesanan->meja->nomor);
    }

    public function broadcastAs(): string
    {
        return 'order.status.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->pesanan->id,
            'meja_nomor' => $this->pesanan->meja->nomor,
            'status' => $this->pesanan->status->value,
            'updated_at' => $this->pesanan->updated_at->toISOString(),
        ];
    }
}
