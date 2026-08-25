<?php

namespace Tests\Feature;

use App\Enums\MetodeBayar;
use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Enums\StatusSesiMeja;
use App\Enums\UserRole;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\SesiMeja;
use App\Models\User;
use App\Services\OrderService;
use App\Services\TableService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SesiMejaTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_creates_active_session(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);
        $meja->generateNewToken();

        $this->get(route('meja.assign', $meja->token))
            ->assertRedirect(route('customer.menu', ['meja' => $meja->id]));

        $this->assertDatabaseHas('sesi_meja', [
            'meja_id' => $meja->id,
            'status' => StatusSesiMeja::Aktif->value,
        ]);
        $this->assertEquals(1, SesiMeja::where('meja_id', $meja->id)
            ->where('status', StatusSesiMeja::Aktif)->count());
    }

    public function test_duplicate_scan_reuses_active_session(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);
        $meja->generateNewToken();

        $this->get(route('meja.assign', $meja->token));
        $this->get(route('meja.assign', $meja->token));

        $this->assertEquals(1, SesiMeja::where('meja_id', $meja->id)
            ->where('status', StatusSesiMeja::Aktif)->count());
    }

    public function test_checkout_stamps_sesi_and_kasir(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $meja->generateNewToken();
        $kasir = User::factory()->create(['role' => UserRole::Kasir]);
        $menu = Menu::factory()->create(['harga' => 10000, 'stok' => 10, 'status' => StatusMenu::Tersedia]);

        $this->get(route('meja.assign', $meja->token));

        $order = (new OrderService)->checkout($meja, [
            ['menu_id' => $menu->id, 'jumlah' => 2, 'harga_satuan' => 10000],
        ], MetodeBayar::Tunai, null, $kasir->id, $meja->token);

        $this->assertNotNull($order->sesi_meja_id);
        $sesi = SesiMeja::find($order->sesi_meja_id);
        $this->assertEquals($kasir->id, $sesi->user_id);
    }

    public function test_session_closed_when_table_freed(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $meja->generateNewToken();
        $sesi = SesiMeja::create([
            'meja_id' => $meja->id,
            'token' => $meja->token,
            'status' => StatusSesiMeja::Aktif,
            'started_at' => now(),
        ]);

        (new TableService)->refreshOccupancy($meja);

        $sesi->refresh();
        $this->assertEquals(StatusSesiMeja::Selesai, $sesi->status);
        $this->assertNotNull($sesi->ended_at);
    }
}
