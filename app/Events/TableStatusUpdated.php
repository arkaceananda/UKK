<?php

namespace App\Events;

use App\Models\Meja;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TableStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Meja $meja,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('kasir-channel');
    }

    public function broadcastAs(): string
    {
        return 'TableStatusUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'meja_id' => $this->meja->id,
            'nomor' => $this->meja->nomor,
            'is_occupied' => (bool) $this->meja->is_occupied,
            'status' => $this->meja->status->value,
        ];
    }
}
