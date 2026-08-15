<?php

namespace Tests\Feature;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MejaScanTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_marks_table_occupied_and_stores_token_in_session(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->get(route('meja.scan', $meja->id));

        $response->assertRedirect(route('customer.menu', ['meja' => $meja->id]));

        $this->assertTrue($meja->fresh()->is_occupied);
        $this->assertSame($meja->fresh()->token, session('meja_token_'.$meja->id));
    }

    public function test_scan_refreshes_session_token_for_occupied_table(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        session(['meja_token_'.$meja->id => 'old-token']);

        $this->get(route('meja.scan', $meja->id));

        $this->assertSame($meja->fresh()->token, session('meja_token_'.$meja->id));
    }

    public function test_scan_rejects_nonaktif_table(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Nonaktif]);

        $this->get(route('meja.scan', $meja->id))
            ->assertRedirect('/menu')
            ->assertSessionHas('error');
    }
}
