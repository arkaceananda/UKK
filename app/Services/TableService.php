<?php

namespace App\Services;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Support\Str;

class TableService
{
    public function createTable(string $nomor): Meja
    {
        return Meja::create([
            'nomor' => $nomor,
            'token' => Str::random(64),
            'status' => StatusMeja::Aktif,
        ]);
    }

    public function regenerateToken(Meja $meja): string
    {
        return $meja->generateNewToken();
    }

    public function findByToken(string $token): ?Meja
    {
        return Meja::where('token', $token)
            ->where('status', StatusMeja::Aktif)
            ->first();
    }
}
