<?php

namespace Database\Seeders;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class SampleTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $menus = Menu::all();
        $meja = Meja::first();

        if ($menus->count() < 3 || ! $meja) {
            $this->command->warn('Need at least 3 menus and 1 table.');

            return;
        }

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);

            for ($j = 0; $j < rand(2, 5); $j++) {
                $pesanan = Pesanan::create([
                    'meja_id' => $meja->id,
                    'status' => StatusPesanan::Selesai,
                    'total_harga' => rand(3, 8) * 25000,
                    'created_at' => $date->copy()->setTime(rand(8, 20), rand(0, 59)),
                ]);

                $menuItems = $menus->random(rand(1, 3));
                foreach ($menuItems as $menu) {
                    DetailPesanan::create([
                        'pesanan_id' => $pesanan->id,
                        'menu_id' => $menu->id,
                        'jumlah' => rand(1, 3),
                        'harga_satuan' => $menu->harga,
                        'subtotal' => $menu->harga * rand(1, 3),
                    ]);
                }

                Transaksi::create([
                    'pesanan_id' => $pesanan->id,
                    'metode_bayar' => fake()->randomElement(MetodeBayar::cases()),
                    'total_bayar' => $pesanan->total_harga,
                    'status_bayar' => StatusBayar::Lunas,
                    'created_at' => $pesanan->created_at,
                ]);
            }
        }

        $this->command->info('Sample transactions created: '.Transaksi::count());
    }
}
