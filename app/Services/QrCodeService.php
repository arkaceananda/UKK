<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeService
{
    public function svgForToken(string $token, int $size = 120): string
    {
        $host = Request::getSchemeAndHttpHost() ?: (string) config('app.url');
        $url = $host.route('meja.assign', $token, false);
        $key = 'qr:meja:'.md5($url).":{$size}";

        return Cache::remember($key, 600, fn (): string => (string) QrCode::size($size)->generate($url));
    }

    public function forget(string $token): void
    {
        Cache::forget("qr:meja:{$token}:120");
    }
}
