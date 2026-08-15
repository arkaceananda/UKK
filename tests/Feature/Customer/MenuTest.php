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

    private function validSession(Meja $meja): array
    {
        return ['assigned_meja_id' => $meja->id, 'assigned_meja_token' => $meja->token];
    }

    public function test_customer_menu_page_loads_successfully(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))->get('/menu');

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

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))
            ->get(route('customer.menu', ['meja' => $meja->id]));

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

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))->get('/menu');

        $response->assertStatus(200);
    }

    public function test_customer_menu_shows_active_mesa(): void
    {
        Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))->get('/menu');

        $response->assertStatus(200);
    }

    public function test_only_active_mesa_are_available(): void
    {
        Meja::factory()->create(['status' => StatusMeja::Nonaktif]);

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))->get('/menu');

        $response->assertStatus(200);
    }

    public function test_menu_requires_valid_table_session(): void
    {
        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

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

        $meja = Meja::factory()->create(['status' => StatusMeja::Aktif]);

        $response = $this->withSession($this->validSession($meja))
            ->get(route('customer.menu', ['meja' => $meja->id]));

        $response->assertStatus(200);
        $response->assertSee($kategori->nama);
        $response->assertDontSee('Silakan Scan Ulang QR Meja');
    }
}
