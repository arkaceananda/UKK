<?php

namespace App\Jobs;

use App\Models\Meja;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RegenerateTableToken implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Meja $meja,
    ) {}

    public function handle(): void
    {
        $this->meja->resetSesi();
    }
}
