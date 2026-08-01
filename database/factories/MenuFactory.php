<?php

namespace Database\Factories;

use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        $names = [
            'Nasi Goreng Spesial', 'Nasi Goreng Seafood', 'Nasi Goreng Ayam', 'Nasi Goreng Kampung',
            'Mie Ayam Jamur', 'Mie Ayam Pangsit', 'Mie Ayam Bakso', 'Mie Goreng Jawa',
            'Soto Ayam Lamongan', 'Soto Betawi', 'Soto Padang', 'Soto Banjar',
            'Ayam Goreng Kremes', 'Ayam Bakar Madu', 'Ayam Penyet Sambal', 'Ayam Kalasan',
            'Rendang Sapi', 'Gulai Ayam', 'Gulai Kambing', 'Opor Ayam',
            'Gado-Gado', 'Pecel Lele', 'Ikan Bakar', 'Udang Goreng Tepung',
            'Es Teh Manis', 'Es Jeruk Segar', 'Es Cendol', 'Es Dawet',
            'Kopi Hitam Tubruk', 'Kopi Susu', 'Cappuccino', 'Latte',
            'Matcha Latte', 'Taro Latte', 'Smoothie Berry', 'Smoothie Mango',
            'Keripik Singkong', 'Keripik Kentang', 'Keripik Pisang', 'Keripik Tempe',
            'Pisang Goreng', 'Bakwan Jagung', 'Tahu Isi', 'Tahu Crispy',
            'Martabak Mini', 'Risoles Mayo', 'Lumpia Semarang', 'Sosis Bakar',
            'Tempe Mendoan', 'Sate Ayam', 'Siomay', 'Batagor',
            'Brownies', 'Cheesecake', 'Puding Coklat', 'Es Krim Vanilla',
        ];

        return [
            'kategori_id' => KategoriMenu::factory(),
            'nama' => fake()->unique()->randomElement($names),
            'deskripsi' => fake()->sentence(6),
            'harga' => fake()->randomFloat(2, 8000, 55000),
            'stok' => fake()->numberBetween(5, 50),
            'status' => StatusMenu::Tersedia,
        ];
    }

    public function habis(): static
    {
        return $this->state(fn () => [
            'stok' => 0,
            'status' => StatusMenu::Habis,
        ]);
    }
}
