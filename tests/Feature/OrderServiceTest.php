<?php

namespace Tests\Feature;

use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function menu(): Menu
    {
        $kategori = KategoriMenu::factory()->create();

        return Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 10,
        ]);
    }

    public function test_checkout_rejects_stale_table_token(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $menu = $this->menu();

        $this->expectException(\DomainException::class);

        app(OrderService::class)->checkout(
            $meja,
            [['menu_id' => $menu->id, 'jumlah' => 1]],
            tableToken: 'stale-token',
        );
    }

    public function test_checkout_rejects_unoccupied_table(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => false]);
        $menu = $this->menu();

        $this->expectException(\DomainException::class);

        app(OrderService::class)->checkout(
            $meja,
            [['menu_id' => $menu->id, 'jumlah' => 1]],
            tableToken: $meja->token,
        );
    }

    public function test_checkout_with_valid_token_creates_order(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $menu = $this->menu();

        $pesanan = app(OrderService::class)->checkout(
            $meja,
            [['menu_id' => $menu->id, 'jumlah' => 2]],
            tableToken: $meja->token,
        );

        $this->assertDatabaseHas('pesanan', ['id' => $pesanan->id, 'meja_id' => $meja->id]);
        $this->assertSame(2 * $menu->harga, (float) $pesanan->total_harga);
        $this->assertSame(8, $menu->fresh()->stok);
    }
}
