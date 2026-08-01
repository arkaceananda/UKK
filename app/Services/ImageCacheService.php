<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

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

        $optimizedPath = 'optimized/'.md5($imagePath.'-400-75').'.webp';
        if (Storage::disk('public')->exists($optimizedPath)) {
            Storage::disk('public')->delete($optimizedPath);
        }
    }

    public function invalidateAllImageCache(): void
    {
        Cache::flush();

        $optimizedDir = 'optimized';
        if (Storage::disk('public')->exists($optimizedDir)) {
            Storage::disk('public')->deleteDirectory($optimizedDir);
        }
    }

    private function generateOptimizedUrl(string $imagePath, int $width, int $quality): string
    {
        if (! Storage::disk('public')->exists($imagePath)) {
            return '';
        }

        $originalPath = Storage::disk('public')->path($imagePath);
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        if ($extension === 'webp') {
            return asset('storage/'.$imagePath);
        }

        $optimizedPath = 'optimized/'.md5($imagePath.'-'.$width.'-'.$quality).'.webp';
        $optimizedFullPath = Storage::disk('public')->path($optimizedPath);

        if (Storage::disk('public')->exists($optimizedPath)) {
            return asset('storage/'.$optimizedPath);
        }

        try {
            Storage::disk('public')->makeDirectory('optimized');

            $manager = new ImageManager([
                'driver' => 'gd',
            ]);
            $image = $manager->read($originalPath);

            if ($image->width() > $width) {
                $image->scale(width: $width);
            }

            $image->toWebp($quality);

            $image->save($optimizedFullPath);

            return asset('storage/'.$optimizedPath);
        } catch (\Exception $e) {
            Log::warning('Gagal mengoptimasi gambar: '.$imagePath, ['error' => $e->getMessage()]);

            return asset('storage/'.$imagePath);
        }
    }

    public function warmCache(array $imagePaths, int $width = 400, int $quality = 75): void
    {
        foreach ($imagePaths as $path) {
            $this->getCachedUrl($path, $width, $quality);
        }
    }
}
