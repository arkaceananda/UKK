<?php

namespace Database\Seeders;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\UserRole;
use App\Models\DetailPesanan;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // --- Users ---
        $admin = User::factory()->create([
            'name' => 'Admin Burjo',
            'email' => 'admin@burjo.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Admin,
        ]);

        $kasir = User::factory()->create([
            'name' => 'Kasir Burjo',
            'email' => 'kasir@burjo.test',
            'password' => Hash::make('password'),
            'role' => UserRole::Kasir,
        ]);

        // --- Kategori ---
        $snack = KategoriMenu::factory()->create(['nama' => 'Snack']);
        $nasiGoreng = KategoriMenu::factory()->create(['nama' => 'Nasi Goreng']);
        $nasiKatsu = KategoriMenu::factory()->create(['nama' => 'Nasi Katsu']);
        $chickenRiceBowl = KategoriMenu::factory()->create(['nama' => 'Chicken Rice Bowl']);
        $menuNasi = KategoriMenu::factory()->create(['nama' => 'Menu Nasi']);
        $baseMie = KategoriMenu::factory()->create(['nama' => 'Base Mie']);
        $beefSlice = KategoriMenu::factory()->create(['nama' => 'Beef Slice']);
        $teaAndFreshDrink = KategoriMenu::factory()->create(['nama' => 'Tea & Fresh Drink']);
        $juice = KategoriMenu::factory()->create(['nama' => 'Juice']);
        $coffee = KategoriMenu::factory()->create(['nama' => 'Coffee']);
        $minumanFavorit = KategoriMenu::factory()->create(['nama' => 'Minuman Favorit']);
        $addOn = KategoriMenu::factory()->create(['nama' => 'Add On']);

        // --- Menu: Makanan ---
        $nasiGorengTelur = Menu::factory()->create([
            'kategori_id' => $nasiGoreng->id, 'nama' => 'Nasi Goreng Telur',
            'harga' => 15000, 'stok' => 30, 'deskripsi' => 'Nasi goreng dengan telur',
        ]);
        $nasiGorengAyam = Menu::factory()->create([
            'kategori_id' => $nasiGoreng->id, 'nama' => 'Nasi Goreng Ayam',
            'harga' => 15000, 'stok' => 20, 'deskripsi' => 'Nasi goreng dengan ayam suwir',
        ]);
        $nasiKatsuOriginal = Menu::factory()->create([
            'kategori_id' => $nasiKatsu->id, 'nama' => 'Nasi Katsu Original',
            'harga' => 15000, 'stok' => 15, 'deskripsi' => 'Nasi dengan katsu ayam original',
        ]);
        $nasiKatsuBlackPaper = Menu::factory()->create([
            'kategori_id' => $nasiKatsu->id, 'nama' => 'Nasi Katsu Black Paper',
            'harga' => 17000, 'stok' => 10, 'deskripsi' => 'Nasi dengan katsu ayam black paper',
        ]);
        $chickenRiceBowlMenu = Menu::factory()->create([
            'kategori_id' => $chickenRiceBowl->id, 'nama' => 'Chicken Rice Bowl',
            'harga' => 15000, 'stok' => 25, 'deskripsi' => 'Rice bowl dengan ayam goreng crispy',
        ]);
        $nasiAyamBali = Menu::factory()->create([
            'kategori_id' => $menuNasi->id, 'nama' => 'Nasi Ayam Bali',
            'harga' => 15000, 'stok' => 20, 'deskripsi' => 'Nasi dengan ayam bumbu bali',
        ]);
        $MiDokDok = Menu::factory()->create([
            'kategori_id' => $baseMie->id, 'nama' => 'Mi Dok-Dok',
            'harga' => 12000, 'stok' => 30, 'deskripsi' => 'Mie dengan topping ayam dan sayuran',
        ]);
        $sliceBlackPaper = Menu::factory()->create([
            'kategori_id' => $beefSlice->id, 'nama' => 'Beef Slice Black Paper',
            'harga' => 23000, 'stok' => 15, 'deskripsi' => 'Irisan daging sapi dengan saus black paper',
        ]);
        $esTeh = Menu::factory()->create([
            'kategori_id' => $teaAndFreshDrink->id, 'nama' => 'Es Teh',
            'harga' => 5000, 'stok' => 50, 'deskripsi' => 'Teh manis dingin',
        ]);
        $jusJeruk = Menu::factory()->create([
            'kategori_id' => $juice->id, 'nama' => 'Jus Jeruk',
            'harga' => 10000, 'stok' => 30, 'deskripsi' => 'Jus jeruk segar',
        ]);
        $americano = Menu::factory()->create([
            'kategori_id' => $coffee->id, 'nama' => 'Americano',
            'harga' => 10000, 'stok' => 20, 'deskripsi' => 'Kopi Americano dingin',
        ]);
        $tehTarik = Menu::factory()->create([
            'kategori_id' => $minumanFavorit->id, 'nama' => 'Teh Tarik',
            'harga' => 8000, 'stok' => 25, 'deskripsi' => 'Teh tarik manis',
        ]);
        $mendoan = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Mendoan',
            'harga' => 7000, 'stok' => 30, 'deskripsi' => 'Tempe mendoan goreng',
        ]);
        $nasi = Menu::factory()->create([
            'kategori_id' => $addOn->id, 'nama' => 'Nasi',
            'harga' => 5000, 'stok' => 50, 'deskripsi' => 'Nasi putih',
        ]);

        // --- Meja ---
        $this->call(MejaSeeder::class);

        // --- Sample orders (3 historical) ---
        $meja1 = Meja::firstWhere('nomor', '1');

        $pesanan1 = Pesanan::factory()->selesai()->create([
            'meja_id' => $meja1->id, 'kasir_id' => $kasir->id,
            'total_harga' => 48000, 'created_at' => now()->subDays(3),
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan1->id, 'menu_id' => $nasiGorengTelur->id,
            'jumlah' => 1, 'harga_satuan' => 15000, 'subtotal' => 15000,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan1->id, 'menu_id' => $esTeh->id,
            'jumlah' => 2, 'harga_satuan' => 5000, 'subtotal' => 10000,
        ]);
        Transaksi::factory()->create([
            'pesanan_id' => $pesanan1->id, 'metode_bayar' => MetodeBayar::Tunai,
            'total_bayar' => 48000, 'status_bayar' => StatusBayar::Lunas,
            'created_at' => $pesanan1->created_at,
        ]);
    }
}
