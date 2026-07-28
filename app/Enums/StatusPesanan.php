<?php

namespace App\Enums;

enum StatusPesanan: string
{
    case Menunggu = 'menunggu';
    case Diterima = 'diterima';
    case Ditolak = 'ditolak';
    case Diproses = 'diproses';
    case Selesai = 'selesai';

    /** Status transitions kasir can make. */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Menunggu => [self::Diterima, self::Ditolak],
            self::Diterima => [self::Diproses],
            self::Diproses => [self::Selesai],
            self::Ditolak, self::Selesai => [],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
