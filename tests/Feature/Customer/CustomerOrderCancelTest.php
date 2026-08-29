<?php

namespace Tests\Feature\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMenu;
use App\Enums\StatusPesanan;
use App\Livewire\OrderStatus;
use App\Models\DetailPesanan;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerOrderCancelTest extends TestCase
{
    use RefreshDatabase;

    private function createAktifMeja(): Meja
    {
        return Meja::factory()->create();
    }

    private function createMenu(int $stok = 10): Menu
    {
        return Menu::factory()->create([
            'kategori_id' => KategoriMenu::factory()->create()->id,
            'stok' => $stok,
            'status' => StatusMenu::Tersedia,
        ]);
    }

    private function assignTable(Meja $meja): void
    {
        session([
            'assigned_meja_id' => $meja->id,
            'assigned_meja_token' => $meja->token,
        ]);
    }

    private function buildPesanan(Meja $meja, Menu $menu, int $jumlah = 2, StatusPesanan $status = StatusPesanan::Menunggu): Pesanan
    {
        $menu->reduceStock($jumlah);

        $pesanan = Pesanan::create([
            'meja_id' => $meja->id,
            'kasir_id' => null,
            'status' => $status,
            'total_harga' => $menu->harga * $jumlah,
        ]);

        DetailPesanan::create([
            'pesanan_id' => $pesanan->id,
            'menu_id' => $menu->id,
            'jumlah' => $jumlah,
            'harga_satuan' => $menu->harga,
            'subtotal' => $menu->harga * $jumlah,
        ]);

        Transaksi::create([
            'pesanan_id' => $pesanan->id,
            'metode_bayar' => MetodeBayar::Tunai->value,
            'total_bayar' => $menu->harga * $jumlah,
            'status_bayar' => 'pending',
        ]);

        return $pesanan->load('details', 'transaksi');
    }

    public function test_customer_can_cancel_own_menunggu_order_and_restore_stock(): void
    {
        $meja = $this->createAktifMeja();
        $menu = $this->createMenu(stok: 10);
        $this->assignTable($meja);

        $pesanan = $this->buildPesanan($meja, $menu, jumlah: 3);
        $this->assertSame(7, $menu->fresh()->stok, 'Stock reduced after order');

        Livewire::test(OrderStatus::class, ['pesanan' => $pesanan])
            ->call('cancelOrder')
            ->assertRedirect(route('customer.menu', ['meja' => $meja->id]));

        $this->assertDatabaseMissing('pesanan', ['id' => $pesanan->id]);
        $this->assertDatabaseMissing('detail_pesanan', ['pesanan_id' => $pesanan->id]);
        $this->assertDatabaseMissing('transaksi', ['pesanan_id' => $pesanan->id]);
        $this->assertSame(10, $menu->fresh()->stok, 'Stock restored after cancellation');
    }

    public function test_customer_cannot_cancel_another_tables_order(): void
    {
        $myMeja = $this->createAktifMeja();
        $otherMeja = $this->createAktifMeja();
        $menu = $this->createMenu(stok: 10);

        $this->assignTable($myMeja);
        $otherPesanan = $this->buildPesanan($otherMeja, $menu, jumlah: 2);

        Livewire::test(OrderStatus::class, ['pesanan' => $otherPesanan])
            ->call('cancelOrder');

        $this->assertDatabaseHas('pesanan', ['id' => $otherPesanan->id]);
        $this->assertSame(8, $menu->fresh()->stok, 'Stock not restored for unauthorized cancellation');
    }

    public function test_customer_cannot_cancel_order_not_in_menunggu(): void
    {
        $meja = $this->createAktifMeja();
        $menu = $this->createMenu(stok: 10);
        $this->assignTable($meja);

        $pesanan = $this->buildPesanan($meja, $menu, jumlah: 2, status: StatusPesanan::Diterima);

        Livewire::test(OrderStatus::class, ['pesanan' => $pesanan])
            ->call('cancelOrder');

        $this->assertDatabaseHas('pesanan', ['id' => $pesanan->id]);
        $this->assertSame(8, $menu->fresh()->stok, 'Stock not restored for non-Menunggu order');
    }
}
