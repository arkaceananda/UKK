<?php

namespace Tests\Feature\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Enums\StatusPesanan;
use App\Livewire\Customer\Checkout;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\SesiMeja;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerFlowTest extends TestCase
{
    use RefreshDatabase;

    private function createAvailableMenu(int $stok = 10, float $harga = 15000): Menu
    {
        $kategori = KategoriMenu::factory()->create();

        return Menu::factory()->create([
            'kategori_id' => $kategori->id,
            'status' => StatusMenu::Tersedia,
            'stok' => $stok,
            'harga' => $harga,
        ]);
    }

    private function createAktifMeja(): Meja
    {
        return Meja::factory()->create(['status' => StatusMeja::Aktif]);
    }

    private function scanQr(Meja $meja): TestResponse
    {
        return $this->get(route('meja.assign', $meja->token));
    }

    public function test_full_http_journey_scan_menu_checkout_page(): void
    {
        $meja = $this->createAktifMeja();
        $this->createAvailableMenu();

        // 1. Scan QR -> assigns table in session and redirects to menu
        $this->scanQr($meja)->assertRedirect(route('customer.menu', ['meja' => $meja->id]));
        $this->assertSame($meja->id, session('assigned_meja_id'));

        // 2. Full menu HTML loads for the assigned table
        $this->get(route('customer.menu', ['meja' => $meja->id]))->assertOk();

        // 3. Checkout page loads without crashing (session carries the assigned table)
        $this->get(route('customer.checkout', ['meja' => $meja->id]))->assertOk();
    }

    public function test_without_scan_menu_redirects_to_scan_required(): void
    {
        $response = $this->get('/menu');

        $response->assertRedirect(route('customer.scan-required'));
    }

    public function test_scan_to_order_creates_pesanan_and_reduces_stock(): void
    {
        $meja = $this->createAktifMeja();
        $menu = $this->createAvailableMenu(stok: 5, harga: 20000);

        $this->scanQr($meja)->assertRedirect();

        // Build the cart for this table in the session, then run checkout via Livewire
        Livewire::test(Checkout::class, ['meja' => $meja])
            ->set('metodeBayar', MetodeBayar::Tunai->value)
            ->call('addToCart', $menu->id)
            ->call('addToCart', $menu->id)
            ->call('checkout');

        $this->assertDatabaseHas('pesanan', [
            'meja_id' => $meja->id,
            'status' => StatusPesanan::Menunggu,
        ]);

        $pesanan = Pesanan::where('meja_id', $meja->id)->first();
        $this->assertNotNull($pesanan);
        $this->assertSame(3, $menu->fresh()->stok, 'Stock should be reduced by the ordered quantity');
        $this->assertDatabaseHas('detail_pesanan', [
            'menu_id' => $menu->id,
            'jumlah' => 2,
        ]);
    }

    public function test_scan_twice_creates_only_one_active_session(): void
    {
        $meja = $this->createAktifMeja();

        $this->scanQr($meja)->assertRedirect();
        $this->scanQr($meja)->assertRedirect();

        $activeCount = SesiMeja::where('meja_id', $meja->id)
            ->where('status', 'aktif')
            ->count();

        $this->assertLessThanOrEqual(1, $activeCount, 'Scanning twice should not create duplicate active sessions');
    }

    public function test_menu_page_hydrates_existing_cart_and_shows_decrement_controls(): void
    {
        $meja = $this->createAktifMeja();
        $menu = $this->createAvailableMenu(stok: 5, harga: 20000);

        // Pre-populate the cart in the session (as if items were added earlier),
        // then mount the menu page fresh. The menu must reflect the existing cart.
        session([
            'assigned_meja_id' => $meja->id,
            'assigned_meja_token' => $meja->token,
        ]);
        session(['burjo_cart_'.$meja->id => [
            (string) $menu->id => [
                'menu_id' => $menu->id,
                'nama' => $menu->nama,
                'harga' => (float) $menu->harga,
                'jumlah' => 2,
                'foto' => $menu->foto,
                'selected_option' => null,
            ],
        ]]);

        $html = $this->get(route('customer.menu', ['meja' => $meja->id]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(
            "wire:click=\"decrementQuantity('{$menu->id}')\"",
            $html,
            'Menu page must render decrement controls for items already in the cart on mount.'
        );
    }
}
