<?php

namespace Database\Factories;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MejaFactory extends Factory
{
    protected $model = Meja::class;

    public function definition(): array
    {
        return [
            'nomor' => fake()->unique()->numberBetween(1, 30),
            'token' => Str::random(64),
            'status' => StatusMeja::Aktif,
        ];
    }

    public function nonaktif(): static
    {
        return $this->state(fn () => ['status' => StatusMeja::Nonaktif]);
    }
}
