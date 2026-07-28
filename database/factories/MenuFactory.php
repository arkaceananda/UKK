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
            'Nasi Goreng', 'Mie Ayam', 'Soto Ayam', 'Gado-gado', 'Rendang',
            'Es Teh Manis', 'Es Jeruk', 'Kopi Hitam', 'Cappuccino', 'Matcha Latte',
            'Keripik Singkong', 'Pisang Goreng', 'Tahu Crispy', 'Tempe Mendoan',
            'Es Krim Vanilla', 'Puding Coklat', 'Brownies', 'Cheesecake',
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
