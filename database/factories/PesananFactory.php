<?php

namespace Database\Factories;

use App\Enums\StatusPesanan;
use App\Models\Meja;
use App\Models\Pesanan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PesananFactory extends Factory
{
    protected $model = Pesanan::class;

    public function definition(): array
    {
        return [
            'meja_id' => Meja::factory(),
            'kasir_id' => null,
            'status' => StatusPesanan::Menunggu,
            'catatan' => fake()->optional(0.3)->sentence(4),
            'total_harga' => fake()->randomFloat(2, 15000, 150000),
        ];
    }

    public function diterima(): static
    {
        return $this->state(fn () => ['status' => StatusPesanan::Diterima]);
    }

    public function diproses(): static
    {
        return $this->state(fn () => ['status' => StatusPesanan::Diproses]);
    }

    public function selesai(): static
    {
        return $this->state(fn () => ['status' => StatusPesanan::Selesai]);
    }

    public function ditolak(): static
    {
        return $this->state(fn () => ['status' => StatusPesanan::Ditolak]);
    }
}
