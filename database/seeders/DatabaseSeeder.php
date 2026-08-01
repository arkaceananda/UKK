<?php

namespace Database\Seeders;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
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
        $makanan = KategoriMenu::factory()->create(['nama' => 'Makanan']);
        $minuman = KategoriMenu::factory()->create(['nama' => 'Minuman']);
        $snack = KategoriMenu::factory()->create(['nama' => 'Snack']);

        // --- Menu: Makanan ---
        $nasiGoreng = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Nasi Goreng Spesial',
            'harga' => 22000, 'stok' => 30, 'deskripsi' => 'Nasi goreng dengan telur dan ayam',
        ]);
        $mieAyam = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Mie Ayam Jamur',
            'harga' => 18000, 'stok' => 25, 'deskripsi' => 'Mie ayam dengan topping jamur',
        ]);
        $soto = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Soto Ayam Lamongan',
            'harga' => 20000, 'stok' => 20, 'deskripsi' => 'Soto ayam khas Lamongan',
        ]);
        $ayamGoreng = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Ayam Goreng Kremes',
            'harga' => 28000, 'stok' => 25, 'deskripsi' => 'Ayam goreng dengan kremes renyah',
        ]);
        $rendang = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Rendang Sapi',
            'harga' => 35000, 'stok' => 15, 'deskripsi' => 'Rendang daging sapi khas Padang',
        ]);
        $gadoGado = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Gado-Gado',
            'harga' => 17000, 'stok' => 20, 'deskripsi' => 'Salad sayur dengan bumbu kacang',
        ]);
        $pecelLele = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Pecel Lele',
            'harga' => 25000, 'stok' => 18, 'deskripsi' => 'Lele goreng dengan sambal',
        ]);
        $nasgorSeafood = Menu::factory()->create([
            'kategori_id' => $makanan->id, 'nama' => 'Nasi Goreng Seafood',
            'harga' => 30000, 'stok' => 12, 'deskripsi' => 'Nasi goreng dengan cumi, udang, kepiting',
        ]);

        // --- Menu: Minuman ---
        $esTeh = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Es Teh Manis',
            'harga' => 8000, 'stok' => 50, 'deskripsi' => 'Teh manis dingin',
        ]);
        $kopi = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Kopi Hitam Tubruk',
            'harga' => 12000, 'stok' => 40, 'deskripsi' => 'Kopi hitam tradisional',
        ]);
        $matcha = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Matcha Latte',
            'harga' => 25000, 'stok' => 15, 'deskripsi' => 'Matcha latte premium',
        ]);
        $esJeruk = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Es Jeruk Segar',
            'harga' => 10000, 'stok' => 40, 'deskripsi' => 'Jeruk peras dengan es',
        ]);
        $cappuccino = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Cappuccino',
            'harga' => 20000, 'stok' => 25, 'deskripsi' => 'Kopi cappuccino dengan busa susu',
        ]);
        $smoothieBerry = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Smoothie Berry',
            'harga' => 28000, 'stok' => 20, 'deskripsi' => 'Smoothie campuran stroberi, blueberry, raspberry',
        ]);
        $esCendol = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Es Cendol',
            'harga' => 15000, 'stok' => 30, 'deskripsi' => 'Es cendol tradisional dengan santan',
        ]);
        $lemonTea = Menu::factory()->create([
            'kategori_id' => $minuman->id, 'nama' => 'Lemon Tea',
            'harga' => 12000, 'stok' => 35, 'deskripsi' => 'Teh lemon segar',
        ]);

        // --- Menu: Snack ---
        $keripik = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Keripik Singkong',
            'harga' => 10000, 'stok' => 35, 'deskripsi' => 'Keripik singkong renyah',
        ]);
        $pisangGoreng = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Pisang Goreng',
            'harga' => 12000, 'stok' => 20, 'deskripsi' => 'Pisang goreng crispy',
        ]);
        $bakwan = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Bakwan Jagung',
            'harga' => 8000, 'stok' => 40, 'deskripsi' => 'Bakwan jagung goreng',
        ]);
        $tahuIsi = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Tahu Isi',
            'harga' => 10000, 'stok' => 30, 'deskripsi' => 'Tahu goreng isi sayuran',
        ]);
        $martabakMini = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Martabak Mini',
            'harga' => 15000, 'stok' => 25, 'deskripsi' => 'Martabak mini manis',
        ]);
        $risoles = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Risoles Mayo',
            'harga' => 12000, 'stok' => 28, 'deskripsi' => 'Risoles dengan isian mayo dan ayam',
        ]);
        $lumpia = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Lumpia Semarang',
            'harga' => 18000, 'stok' => 22, 'deskripsi' => 'Lumpia khas Semarang',
        ]);
        $sosisBakar = Menu::factory()->create([
            'kategori_id' => $snack->id, 'nama' => 'Sosis Bakar',
            'harga' => 15000, 'stok' => 30, 'deskripsi' => 'Sosis bakar dengan saus',
        ]);

        // --- Meja ---
        for ($i = 1; $i <= 8; $i++) {
            Meja::factory()->create(['nomor' => (string) $i]);
        }

        // --- Sample orders (3 historical) ---
        $meja1 = Meja::firstWhere('nomor', '1');

        $pesanan1 = Pesanan::factory()->selesai()->create([
            'meja_id' => $meja1->id, 'kasir_id' => $kasir->id,
            'total_harga' => 48000, 'created_at' => now()->subDays(3),
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan1->id, 'menu_id' => $nasiGoreng->id,
            'jumlah' => 1, 'harga_satuan' => 22000, 'subtotal' => 22000,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan1->id, 'menu_id' => $esTeh->id,
            'jumlah' => 2, 'harga_satuan' => 8000, 'subtotal' => 16000,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan1->id, 'menu_id' => $keripik->id,
            'jumlah' => 1, 'harga_satuan' => 10000, 'subtotal' => 10000,
        ]);
        Transaksi::factory()->create([
            'pesanan_id' => $pesanan1->id, 'metode_bayar' => MetodeBayar::Tunai,
            'total_bayar' => 48000, 'status_bayar' => StatusBayar::Lunas,
            'created_at' => $pesanan1->created_at,
        ]);

        $pesanan2 = Pesanan::factory()->selesai()->create([
            'meja_id' => $meja1->id, 'kasir_id' => $kasir->id,
            'total_harga' => 30000, 'created_at' => now()->subDays(1),
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan2->id, 'menu_id' => $mieAyam->id,
            'jumlah' => 1, 'harga_satuan' => 18000, 'subtotal' => 18000,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan2->id, 'menu_id' => $kopi->id,
            'jumlah' => 1, 'harga_satuan' => 12000, 'subtotal' => 12000,
        ]);
        Transaksi::factory()->create([
            'pesanan_id' => $pesanan2->id, 'metode_bayar' => MetodeBayar::Qris,
            'total_bayar' => 30000, 'status_bayar' => StatusBayar::Lunas,
            'created_at' => $pesanan2->created_at,
        ]);

        $meja3 = Meja::firstWhere('nomor', '3');
        $pesanan3 = Pesanan::factory()->create([
            'meja_id' => $meja3->id, 'total_harga' => 0,
            'status' => StatusPesanan::Menunggu,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan3->id, 'menu_id' => $matcha->id,
            'jumlah' => 2, 'harga_satuan' => 25000, 'subtotal' => 50000,
        ]);
        DetailPesanan::factory()->create([
            'pesanan_id' => $pesanan3->id, 'menu_id' => $pisangGoreng->id,
            'jumlah' => 1, 'harga_satuan' => 12000, 'subtotal' => 12000,
        ]);
        $pesanan3->update(['total_harga' => 62000]);
        Transaksi::factory()->pending()->create([
            'pesanan_id' => $pesanan3->id, 'total_bayar' => 62000,
            'created_at' => $pesanan3->created_at,
        ]);
    }
}
