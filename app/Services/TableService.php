<?php

namespace App\Services;

use App\Enums\StatusMeja;
use App\Enums\StatusPesanan;
use App\Models\Meja;
use App\Models\Pesanan;
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

    public function setOccupied(Meja $meja, bool $occupied): void
    {
        if ($meja->is_occupied !== $occupied) {
            $meja->update(['is_occupied' => $occupied]);
        }
    }

    public function refreshOccupancy(Meja $meja): void
    {
        $hasActive = Pesanan::where('meja_id', $meja->id)
            ->whereIn('status', [
                StatusPesanan::Menunggu,
                StatusPesanan::Diterima,
                StatusPesanan::Diproses,
            ])
            ->exists();

        $this->setOccupied($meja, $hasActive);
    }
}
