<?php

namespace App\Livewire\Concerns;

use App\Contracts\CartContract;
use Livewire\Attributes\On;

trait HasCart
{
    protected CartContract $cartService;

    public array $cartItems = [];

    public int|string|null $editingQuantityId = null;

    public int $editingQuantity = 1;

    public function bootHasCart(): void
    {
        $this->cartService = app(CartContract::class);

        if (! empty($this->mejaId)) {
            $this->cartService->setMejaId($this->mejaId);
        }

        $this->cartItems = $this->cartService->getItems();
        $this->cart = $this->cartItems;
    }

    public function addToCart(int $menuId): void
    {
        try {
            $this->cartService->add($menuId);
            $this->syncCart();
            $this->dispatch('notify', message: 'Ditambahkan ke keranjang', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function addToCartWithOption(int $menuId, ?string $option): void
    {
        try {
            $this->cartService->add($menuId, 1, $option);
            $this->syncCart();
            $this->dispatch('notify', message: 'Ditambahkan ke keranjang', type: 'success');
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function removeFromCart(int|string $key): void
    {
        $this->cartService->remove((string) $key);
        $this->syncCart();
    }

    public function updateQuantity(int|string $key, int $qty): void
    {
        try {
            $this->cartService->update((string) $key, $qty);
            $this->syncCart();
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function clearCart(): void
    {
        $this->cartService->clear();
        $this->syncCart();
        $this->dispatch('cart-updated', count: 0, total: 0);
    }

    public function startEditingQuantity(int|string $key): void
    {
        $this->editingQuantityId = $key;
        $this->editingQuantity = $this->cartItems[$key]['jumlah'] ?? 1;
    }

    public function confirmQuantity(): void
    {
        if ($this->editingQuantityId === null) {
            return;
        }

        $this->updateQuantity($this->editingQuantityId, $this->editingQuantity);
        $this->editingQuantityId = null;
    }

    public function cancelEditingQuantity(): void
    {
        $this->editingQuantityId = null;
    }

    #[On('refreshStock')]
    public function refreshStock(): void
    {
        $this->syncCart();
    }

    protected function syncCart(): void
    {
        $this->cartItems = $this->cartService->getItems();
        $this->cart = $this->cartItems;
        $count = $this->cartService->getCount();
        $total = $this->cartService->getTotal();

        $this->dispatch('cart-updated', count: $count, total: $total);
    }

    public function getCartProperty(): array
    {
        return $this->cartService->getItems();
    }

    public function getCartItemsProperty(): array
    {
        return $this->cartService->getItems();
    }

    public function getCartCountProperty(): int
    {
        return $this->cartService->getCount();
    }

    public function getCartTotalProperty(): float
    {
        return $this->cartService->getTotal();
    }
}
