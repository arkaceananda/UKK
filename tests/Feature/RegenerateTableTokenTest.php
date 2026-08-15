<?php

namespace Tests\Feature;

use App\Enums\StatusPesanan;
use App\Jobs\RegenerateTableToken;
use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RegenerateTableTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_completing_order_schedules_token_regeneration(): void
    {
        Queue::fake();

        $meja = Meja::factory()->create();
        $pesanan = Pesanan::factory()->create([
            'meja_id' => $meja->id,
            'status' => StatusPesanan::Diproses,
        ]);

        $pesanan->transitionTo(StatusPesanan::Selesai);

        Queue::assertPushed(RegenerateTableToken::class, fn (RegenerateTableToken $job) => $job->meja->is($meja));
    }

    public function test_non_completed_transition_does_not_schedule_job(): void
    {
        Queue::fake();

        $pesanan = Pesanan::factory()->create(['status' => StatusPesanan::Menunggu]);

        $pesanan->transitionTo(StatusPesanan::Diterima);

        Queue::assertNotPushed(RegenerateTableToken::class);
    }

    public function test_job_regenerates_token_and_releases_table(): void
    {
        $meja = Meja::factory()->create(['is_occupied' => true]);
        $oldToken = $meja->token;

        (new RegenerateTableToken($meja))->handle();

        $meja->refresh();

        $this->assertNotSame($oldToken, $meja->token);
        $this->assertFalse($meja->is_occupied);
    }
}
