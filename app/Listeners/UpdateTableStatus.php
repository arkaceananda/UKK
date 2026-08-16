<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Services\TableService;

class UpdateTableStatus
{
    public function __construct(
        protected TableService $tableService,
    ) {}

    public function handle(OrderStatusUpdated $event): void
    {
        $meja = $event->pesanan->meja;

        if ($meja) {
            $this->tableService->refreshOccupancy($meja);
        }
    }
}
