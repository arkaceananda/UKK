<?php

namespace Database\Seeders;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Database\Seeder;

class MejaSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            Meja::updateOrCreate(
                ['nomor' => (string) $i],
                ['status' => StatusMeja::Aktif],
            );
        }
    }
}
