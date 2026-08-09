<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly int $menuId,
        public readonly int $stok,
        public readonly string $status,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('stock-updates'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'StockUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menuId,
            'stok' => $this->stok,
            'status' => $this->status,
        ];
    }
}
