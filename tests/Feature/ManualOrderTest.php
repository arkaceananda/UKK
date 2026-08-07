<?php

namespace Tests\Feature;

use App\Enums\MetodeBayar;
use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Livewire\Kasir\ManualOrder;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_kasir_can_create_manual_order()
    {
        $kasir = User::factory()->kasir()->create();
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif->value]);
        $menu = Menu::factory()->create(['stok' => 10, 'status' => StatusMenu::Tersedia->value]);

        Livewire::actingAs($kasir)
            ->test(ManualOrder::class)
            ->set('selectedMeja', $meja->id)
            ->set('orderItems.0.menu_id', $menu->id)
            ->set('orderItems.0.jumlah', 2)
            ->call('createOrder')
            ->assertDispatched('order-created')
            ->assertDispatched('success');

        $this->assertDatabaseHas('pesanan', [
            'meja_id' => $meja->id,
            'kasir_id' => $kasir->id,
            'total_harga' => $menu->harga * 2,
        ]);

        $this->assertDatabaseHas('detail_pesanan', [
            'menu_id' => $menu->id,
            'jumlah' => 2,
            'harga_satuan' => $menu->harga,
        ]);

        $this->assertDatabaseHas('transaksi', [
            'total_bayar' => $menu->harga * 2,
            'metode_bayar' => MetodeBayar::Tunai->value,
        ]);

        $this->assertEquals(8, $menu->fresh()->stok);
    }
}
