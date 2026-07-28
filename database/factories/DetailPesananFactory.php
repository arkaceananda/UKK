<?php

namespace Database\Factories;

use App\Models\DetailPesanan;
use App\Models\Menu;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;

class DetailPesananFactory extends Factory
{
    protected $model = DetailPesanan::class;

    public function definition(): array
    {
        $harga = fake()->randomFloat(2, 8000, 55000);
        $jumlah = fake()->numberBetween(1, 5);

        return [
            'pesanan_id' => Pesanan::factory(),
            'menu_id' => Menu::factory(),
            'jumlah' => $jumlah,
            'harga_satuan' => $harga,
            'subtotal' => $harga * $jumlah,
        ];
    }
}
