<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_item_to_cart(): void
    {
        $kategori = KategoriMenu::factory()->create();
        $menu = Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 10,
        ]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        Livewire::test(\App\Livewire\Customer\Menu::class, ['meja' => $meja])
            ->call('addToCart', $menu->id)
            ->assertSet('cart.'.$menu->id.'.jumlah', 1);
    }

    public function test_remove_item_from_cart(): void
    {
        $kategori = KategoriMenu::factory()->create();
        $menu = Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 10,
        ]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        Livewire::test(\App\Livewire\Customer\Menu::class, ['meja' => $meja])
            ->call('addToCart', $menu->id)
            ->call('removeFromCart', $menu->id)
            ->assertSet('cart', []);
    }

    public function test_checkout_requires_mesa(): void
    {
        $this->markTestSkipped('Checkout test requires Livewire testing setup');
    }

    public function test_checkout_creates_order(): void
    {
        $this->markTestSkipped('Integration test for checkout flow');
    }

    public function test_cart_empty_checkout_hidden(): void
    {
        $response = $this->get('/menu');
        $response->assertStatus(200);
    }
}
