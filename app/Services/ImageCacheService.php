<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;

class ImageCacheService
{
    protected string $cachePath = 'optimized';

    public function optimizeAndCache(string $originalPath): string
    {
        $fileName = pathinfo($originalPath, PATHINFO_FILENAME);
        $optimizedPath = $this->cachePath.'/'.$fileName.'.webp';
        $fullOptimizedPath = Storage::disk('public')->path($optimizedPath);
        $fullOriginalPath = Storage::disk('public')->path($originalPath);

        if (! Storage::disk('public')->exists($optimizedPath)) {
            try {
                Storage::disk('public')->makeDirectory($this->cachePath);
                (new ImageManager(new Driver))
                    ->decodePath($fullOriginalPath)
                    ->encodeUsingFormat(Format::WEBP, 80)
                    ->save($fullOptimizedPath);
            } catch (\Throwable $e) {
                // Log the error if optimization fails, return original path
                return $originalPath;
            }
        }

        return $optimizedPath;
    }

    public function getCachedUrl(string $path): string
    {
        $optimizedPath = $this->optimizeAndCache($path);

        return Storage::url($optimizedPath);
    }

    public function url(string $path): string
    {
        return $this->getCachedUrl($path);
    }

    public function invalidateImageCache(string $path)
    {
        $fileName = pathinfo($path, PATHINFO_FILENAME);
        $optimizedPath = $this->cachePath.'/'.$fileName.'.webp';

        // Hapus file yang dioptimasi dan juga file aslinya jika masih ada
        Storage::disk('public')->delete([$optimizedPath, $path]);
    }
}
