<?php

namespace Tests\Feature;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assign_stores_meja_in_session_and_redirects(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->get(route('meja.assign', $meja->token));

        $response->assertRedirect(route('customer.menu', ['meja' => $meja->id]));

        $this->assertSame($meja->id, session('assigned_meja_id'));
        $this->assertSame($meja->fresh()->token, session('assigned_meja_token'));
        $this->assertFalse($meja->fresh()->is_occupied);
    }

    public function test_assign_refreshes_session_token_for_occupied_table(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        session(['assigned_meja_id' => $meja->id, 'assigned_meja_token' => 'old-token']);

        $this->get(route('meja.assign', $meja->token));

        $this->assertSame($meja->fresh()->token, session('assigned_meja_token'));
    }

    public function test_assign_rejects_nonaktif_table(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Nonaktif]);

        $this->get(route('meja.assign', $meja->token))
            ->assertRedirect(route('customer.scan-required'))
            ->assertSessionHas('error');
    }

    public function test_assign_rejects_unknown_token(): void
    {
        $this->get(route('meja.assign', 'token-tidak-ada'))
            ->assertRedirect(route('customer.scan-required'))
            ->assertSessionHas('error');
    }
}
