<?php

namespace Database\Factories;

use App\Models\KategoriMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriMenuFactory extends Factory
{
    protected $model = KategoriMenu::class;

    public function definition(): array
    {
        $categories = ['Makanan', 'Minuman', 'Snack', 'Dessert', 'Paket'];

        return [
            'nama' => fake()->unique()->randomElement($categories),
        ];
    }
}
