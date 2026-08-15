<?php

namespace Tests\Feature;

use App\Events\TableStatusUpdated;
use App\Models\Meja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class TableStatusUpdatedTest extends TestCase
{
    use RefreshDatabase;

    public function test_occupancy_change_dispatches_event(): void
    {
        Event::fake([TableStatusUpdated::class]);

        $meja = Meja::factory()->create();
        $meja->update(['is_occupied' => true]);

        Event::assertDispatched(TableStatusUpdated::class, fn (TableStatusUpdated $event) => $event->meja->is($meja));
    }

    public function test_token_regeneration_dispatches_event(): void
    {
        Event::fake([TableStatusUpdated::class]);

        $meja = Meja::factory()->create();
        $meja->resetSesi();

        Event::assertDispatched(TableStatusUpdated::class, fn (TableStatusUpdated $event) => $event->meja->is($meja));
    }

    public function test_unrelated_update_does_not_dispatch_event(): void
    {
        Event::fake([TableStatusUpdated::class]);

        $meja = Meja::factory()->create();
        $meja->update(['nomor' => '99']);

        Event::assertNotDispatched(TableStatusUpdated::class);
    }
}
