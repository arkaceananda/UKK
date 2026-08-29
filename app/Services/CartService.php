<?php

namespace App\Services;

use App\Contracts\CartContract;
use App\Models\Menu;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CartService implements CartContract
{
    private int $mejaId = 0;

    private string $sessionKey = '';

    public function setMejaId(int $mejaId): void
    {
        $this->mejaId = $mejaId;
        $this->sessionKey = 'burjo_cart_'.$mejaId;
    }

    public function getMejaId(): int
    {
        return $this->mejaId;
    }

    private function getCart(): array
    {
        return Session::get($this->sessionKey, []);
    }

    private function saveCart(array $cart): void
    {
        Session::put($this->sessionKey, $cart);
    }

    public function add(int $menuId, int $qty = 1, ?string $option = null): void
    {
        $lock = Cache::lock('menu-stock:'.$menuId, 3);

        if (! $lock->get()) {
            throw new \RuntimeException('Menu sedang dibooking, coba lagi');
        }

        try {
            $menu = Menu::findOrFail($menuId);

            if (! $menu->isAvailable()) {
                throw new \RuntimeException('Menu tidak tersedia');
            }

            $cart = $this->getCart();
            $cartKey = $option !== null ? $menuId.'__'.$option : (string) $menuId;

            if (isset($cart[$cartKey])) {
                if ($cart[$cartKey]['jumlah'] + $qty > $menu->stok) {
                    throw new \RuntimeException('Stok tidak cukup');
                }
                $cart[$cartKey]['jumlah'] += $qty;
            } else {
                if ($qty > $menu->stok) {
                    throw new \RuntimeException('Stok tidak cukup');
                }
                $cart[$cartKey] = [
                    'menu_id' => $menuId,
                    'nama' => $menu->nama,
                    'harga' => (float) $menu->harga,
                    'jumlah' => $qty,
                    'foto' => $menu->foto,
                    'is_available' => $menu->isAvailable(),
                    'selected_option' => $option,
                ];
            }

            $this->saveCart($cart);
        } finally {
            $lock->release();
        }
    }

    public function remove(string $key): void
    {
        $cart = $this->getCart();
        unset($cart[$key]);
        $this->saveCart($cart);
    }

    public function update(string $key, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($key);

            return;
        }

        $cart = $this->getCart();

        if (! isset($cart[$key])) {
            return;
        }

        [$menuId] = $this->parseCartKey($key);

        $lock = Cache::lock('menu-stock:'.$menuId, 3);

        if (! $lock->get()) {
            throw new \RuntimeException('Menu sedang dibooking, coba lagi');
        }

        try {
            $menu = Menu::findOrFail($menuId);

            if ($qty > $menu->stok) {
                throw new \RuntimeException('Stok tidak cukup');
            }

            $cart[$key]['jumlah'] = $qty;
            $this->saveCart($cart);
        } finally {
            $lock->release();
        }
    }

    public function clear(): void
    {
        Session::forget($this->sessionKey);
    }

    public function getItems(): array
    {
        return $this->getCart();
    }

    public function getCount(): int
    {
        return collect($this->getCart())->sum(fn ($item) => $item['jumlah']);
    }

    public function getTotal(): float
    {
        return collect($this->getCart())->sum(fn ($item) => $item['harga'] * $item['jumlah']);
    }

    private function parseCartKey(string $key): array
    {
        if (str_contains($key, '__')) {
            [$menuId, $option] = explode('__', $key, 2);

            return [(int) $menuId, $option];
        }

        return [(int) $key, null];
    }
}
