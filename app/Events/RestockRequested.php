<?php

namespace App\Events;

use App\Models\Menu;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RestockRequested implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Menu $menu,
        public readonly User $kasir,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('admin-channel'),
            new Channel('kasir-channel'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'RestockRequested';
    }

    public function broadcastWith(): array
    {
        return [
            'menu_id' => $this->menu->id,
            'menu_nama' => $this->menu->nama,
            'kasir_id' => $this->kasir->id,
            'kasir_nama' => $this->kasir->name,
            'requested_at' => now()->toISOString(),
        ];
    }
}
