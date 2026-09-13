<?php

namespace App\Services;

use App\Enums\StatusMenu;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;

class RestockService
{
    public const CACHE_PREFIX = 'restock:menu:';

    public const TTL_SECONDS = 86400;

    public static function key(int $menuId): string
    {
        return self::CACHE_PREFIX.$menuId;
    }

    public static function isRequested(int $menuId): bool
    {
        return Cache::has(self::key($menuId));
    }

    public static function get(int $menuId): ?array
    {
        return Cache::get(self::key($menuId));
    }

    public static function request(int $menuId, int $kasirId, string $kasirName): void
    {
        Cache::put(self::key($menuId), [
            'kasir_id' => $kasirId,
            'kasir_name' => $kasirName,
            'requested_at' => now()->toISOString(),
        ], self::TTL_SECONDS);
    }

    public static function clear(int $menuId): void
    {
        Cache::forget(self::key($menuId));
    }

    public static function countPending(): int
    {
        return Menu::where('status', StatusMenu::Habis->value)
            ->get()
            ->filter(fn (Menu $m) => self::isRequested($m->id))
            ->count();
    }

    public static function habisCount(): int
    {
        return Menu::where('status', StatusMenu::Habis->value)->count();
    }
}
