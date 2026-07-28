<?php

namespace Database\Factories;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransaksiFactory extends Factory
{
    protected $model = Transaksi::class;

    public function definition(): array
    {
        return [
            'pesanan_id' => Pesanan::factory(),
            'metode_bayar' => fake()->randomElement(MetodeBayar::cases()),
            'total_bayar' => fake()->randomFloat(2, 15000, 150000),
            'status_bayar' => StatusBayar::Lunas,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status_bayar' => StatusBayar::Pending]);
    }
}
