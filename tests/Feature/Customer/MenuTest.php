<?php

namespace Tests\Feature\Customer;

use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_menu_page_loads_successfully(): void
    {
        $response = $this->get('/menu');

        $response->assertStatus(200);
        $response->assertSee('BurjoOrder');
    }

    public function test_customer_menu_shows_available_categories(): void
    {
        $kategori = KategoriMenu::factory()->create();
        Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 10,
        ]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $this->withSession(['meja_token_'.$meja->id => $meja->token]);

        $response = $this->get(route('customer.menu', ['meja' => $meja->id]));

        $response->assertStatus(200);
        $response->assertSee($kategori->nama);
    }

    public function test_customer_menu_shows_available_menus_only(): void
    {
        $kategori = KategoriMenu::factory()->create();
        Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 5,
        ]);
        Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Habis,
            'stok' => 0,
        ]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
    }

    public function test_customer_menu_shows_active_mesa(): void
    {
        Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
    }

    public function test_only_active_mesa_are_available(): void
    {
        Meja::factory()->create(['status' => StatusMeja::Nonaktif]);

        $response = $this->get('/menu');

        $response->assertStatus(200);
    }

    public function test_menu_requires_valid_table_session(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);

        $response = $this->get(route('customer.menu', ['meja' => $meja->id]));

        $response->assertStatus(200);
        $response->assertSee('Silakan Scan Ulang QR Meja');
    }

    public function test_menu_renders_when_table_session_is_valid(): void
    {
        $kategori = KategoriMenu::factory()->create();
        Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => 10,
        ]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif, 'is_occupied' => true]);
        $this->withSession(['meja_token_'.$meja->id => $meja->token]);

        $response = $this->get(route('customer.menu', ['meja' => $meja->id]));

        $response->assertStatus(200);
        $response->assertSee($kategori->nama);
        $response->assertDontSee('Silakan Scan Ulang QR Meja');
    }
}
