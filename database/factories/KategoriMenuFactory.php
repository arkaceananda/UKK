<?php

namespace Database\Factories;

use App\Models\KategoriMenu;
use Illuminate\Database\Eloquent\Factories\Factory;

class KategoriMenuFactory extends Factory
{
    protected $model = KategoriMenu::class;

    public function definition(): array
    {
        $categories = [
            'Nasi Goreng',
            'Nasi Katsu',
            'Chicken Rice Bowl',
            'Menu Nasi',
            'Base Mie',
            'Beef Slice',
            'Tea & Fresh Drink',
            'Juice',
            'Coffee',
            'Minuman Favorit',
            'Snack',
            'Add On',
        ];

        return [
            'nama' => fake()->randomElement($categories),
        ];
    }
}
