<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ImageCacheService
{
    public function getCachedUrl(string $imagePath, int $width = 400, int $quality = 75): string
    {
        $cacheKey = 'image:'.md5($imagePath.'-'.$width.'-'.$quality);

        $cached = Cache::get($cacheKey);

        if ($cached) {
            return $cached;
        }

        $url = $this->generateOptimizedUrl($imagePath, $width, $quality);

        Cache::put($cacheKey, $url, 86400);

        return $url;
    }

    public function getLazyImageData(string $imagePath, int $width = 400): array
    {
        $src = $this->getCachedUrl($imagePath, $width);
        $placeholder = 'data:image/svg+xml,'.urlencode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 400"><rect fill="#1E2229" width="400" height="400"/></svg>');

        return [
            'src' => $src,
            'placeholder' => $placeholder,
        ];
    }

    public function invalidateImageCache(string $imagePath): void
    {
        Cache::forget('image:'.md5($imagePath.'-400-75'));
    }

    public function invalidateAllImageCache(): void
    {
        Cache::flush();
    }

    private function generateOptimizedUrl(string $imagePath, int $width, int $quality): string
    {
        if (! Storage::disk('public')->exists($imagePath)) {
            return '';
        }

        return asset('storage/'.$imagePath);
    }

    public function warmCache(array $imagePaths, int $width = 400, int $quality = 75): void
    {
        foreach ($imagePaths as $path) {
            $this->getCachedUrl($path, $width, $quality);
        }
    }
}
