<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Models\Meja;
use App\Models\Pesanan;
use App\Services\TableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableOccupancyTest extends TestCase
{
    use RefreshDatabase;

    private function service(): TableService
    {
        return app(TableService::class);
    }

    public function test_table_becomes_occupied_when_active_order_exists(): void
    {
        $meja = Meja::factory()->create(['is_occupied' => false]);
        Pesanan::factory()->create(['meja_id' => $meja->id, 'status' => StatusPesanan::Diproses]);

        $this->service()->refreshOccupancy($meja->fresh());

        $this->assertTrue($meja->fresh()->is_occupied);
    }

    public function test_table_freed_when_last_order_done(): void
    {
        $meja = Meja::factory()->create(['is_occupied' => true]);
        $pesanan = Pesanan::factory()->create(['meja_id' => $meja->id, 'status' => StatusPesanan::Diproses]);

        $this->service()->refreshOccupancy($meja->fresh());
        $this->assertTrue($meja->fresh()->is_occupied);

        $pesanan->transitionTo(StatusPesanan::Selesai);
        $this->service()->refreshOccupancy($meja->fresh());

        $this->assertFalse($meja->fresh()->is_occupied);
    }

    public function test_table_stays_occupied_with_another_active_order(): void
    {
        $meja = Meja::factory()->create(['is_occupied' => true]);
        $first = Pesanan::factory()->create(['meja_id' => $meja->id, 'status' => StatusPesanan::Diproses]);
        Pesanan::factory()->create(['meja_id' => $meja->id, 'status' => StatusPesanan::Menunggu]);

        $first->transitionTo(StatusPesanan::Selesai);
        $this->service()->refreshOccupancy($meja->fresh());

        $this->assertTrue($meja->fresh()->is_occupied);
    }
}
