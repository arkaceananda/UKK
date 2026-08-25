<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Admin\MenuManager;
use App\Models\KategoriMenu;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->afterApplicationCreated(function () {
            Route::getRoutes()->refreshNameLookups();
            $files = glob(storage_path('framework/views/*'));
            foreach ($files as $file) {
                @unlink($file);
            }
        });
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);
        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
    }

    public function test_kasir_cannot_access_admin_dashboard(): void
    {
        $kasir = User::factory()->create(['role' => UserRole::Kasir]);
        $this->actingAs($kasir);
        $response = $this->get(route('admin.dashboard'));
        $response->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_dashboard_displays_correct_filter_data(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        Transaksi::factory()->create(['total_bayar' => 100000, 'created_at' => now()->subDays(5)->startOfDay()]);
        Transaksi::factory()->create(['total_bayar' => 150000, 'created_at' => now()->subDays(6)->startOfDay()]);
        $response = $this->get(route('admin.dashboard').'?filter=7d');
        $response->assertOk();
    }

    public function test_can_create_new_menu_with_livewire(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $kategori = KategoriMenu::factory()->create();

        $response = Livewire::test(MenuManager::class)
            ->set('namaMenu', 'Nasi Goreng Spesial')
            ->set('hargaMenu', 25000)
            ->set('kategoriMenuId', $kategori->id)
            ->call('saveMenu');

        $this->assertDatabaseHas('menu', ['nama' => 'Nasi Goreng Spesial', 'harga' => 25000]);
    }

    public function test_validation_fails_for_invalid_menu_data(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $this->actingAs($admin);

        $response = Livewire::test(MenuManager::class)
            ->set('namaMenu', '')
            ->set('hargaMenu', 'invalid')
            ->call('saveMenu');

        $response->assertHasErrors(['namaMenu', 'hargaMenu']);
    }
}
