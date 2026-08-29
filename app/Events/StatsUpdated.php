<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public string $type, // 'sales', 'orders', 'stock'
        public array $data = [],
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('admin.stats');
    }

    public function broadcastAs(): string
    {
        return 'StatsUpdated';
    }
}
