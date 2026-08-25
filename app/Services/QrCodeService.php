<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function svgForToken(string $token, int $size = 120): string
    {
        $key = "qr:meja:{$token}:{$size}";

        return Cache::rememberForever($key, fn (): string => (string) QrCode::size($size)->generate(route('meja.assign', $token)));
    }

    public function forget(string $token): void
    {
        Cache::forget("qr:meja:{$token}:120");
    }
}
