<?php

namespace Tests\Feature\Kasir;

use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Livewire\Kasir\ManualOrder;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManualOrderPageRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_manual_order_meja_list_is_populated(): void
    {
        $kasir = User::factory()->kasir()->create();
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'nomor' => '1']);

        Livewire::actingAs($kasir)
            ->test(ManualOrder::class)
            ->assertSet('mejaList', function ($list) use ($meja) {
                return $list->count() === 1 && $list->first()->id === $meja->id;
            });
    }

    public function test_manual_order_menu_list_is_populated(): void
    {
        $kasir = User::factory()->kasir()->create();
        $kategori = KategoriMenu::factory()->create();
        $menu = Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 5,
        ]);

        Livewire::actingAs($kasir)
            ->test(ManualOrder::class)
            ->assertSet('menuItems', function ($list) use ($menu) {
                return $list->count() === 1 && $list->first()->id === $menu->id;
            });
    }
}
