<?php

namespace App\Livewire\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMenu;
use App\Events\OrderPlaced;
use App\Livewire\Concerns\HasCart;
use App\Livewire\Concerns\HasMenuBrowsing;
use App\Livewire\Concerns\VerifiesTable;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Services\OrderService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
class Checkout extends Component
{
    use HasCart;
    use HasMenuBrowsing;
    use VerifiesTable;

    #[Locked]
    public int $mejaId;

    public string $notes = '';

    public string $metodeBayar = '';

    protected $queryString = [
        'selectedCategory' => ['except' => ''],
        'selectedMejaId' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount(Meja $meja): void
    {
        $this->mejaId = $meja->id;
        $this->cartService->setMejaId($meja->id);
        $this->syncCart();
        $this->selectedMejaId = (string) $meja->id;

        $this->initializeTable($meja);

        $firstCategory = KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->first();

        if ($this->selectedCategory === '' && $firstCategory) {
            $this->selectedCategory = (string) $firstCategory->id;
            $this->menuService->setCategory($this->selectedCategory);
        }

        $this->menuService->resetMenus();
    }

    public function backToMenu(): void
    {
        $this->redirect(route('customer.menu', ['meja' => $this->selectedMejaId]));
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

    public function removeItem(int|string $key): void
    {
        $this->cartService->remove((string) $key);
        $this->syncCart();
    }

    public function removeFromCart(int|string $key): void
    {
        $this->removeItem($key);
    }

    public function updateQuantity(int|string $key, int $jumlah): void
    {
        try {
            $this->cartService->update((string) $key, $jumlah);
            $this->syncCart();
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->menus->setCategory($categoryId);
    }

    public function checkout(): void
    {
        $items = $this->cartService->getItems();
        if (empty($items)) {
            return;
        }

        if ($this->metodeBayar === '') {
            $this->dispatch('notify', message: 'Pilih metode pembayaran terlebih dahulu', type: 'error');

            return;
        }

        if ($this->selectedMejaId === '') {
            $this->dispatch('notify', message: 'Pilih meja terlebih dahulu', type: 'error');

            return;
        }

        $orderService = app(OrderService::class);

        try {
            $pesanan = $orderService->checkout(
                Meja::findOrFail($this->selectedMejaId),
                array_map(fn ($item) => [
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'selected_option' => $item['selected_option'] ?? null,
                ], $items),
                MetodeBayar::from($this->metodeBayar),
                $this->notes !== '' ? $this->notes : null,
                null,
                $this->tableToken,
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal membuat pesanan: '.$e->getMessage(), type: 'error');

            return;
        }

        $this->cartService->clear();
        $this->notes = '';
        $this->metodeBayar = '';
        $this->editingQuantityId = null;

        $this->dispatch('notify', message: 'Pesanan berhasil dibuat!', type: 'success');
        $this->dispatch('order-placed', orderId: $pesanan->id);

        try {
            event(new OrderPlaced($pesanan->fresh()));
        } catch (\Throwable $e) {
            report($e);
        }

        if ($pesanan->transaksi?->metode_bayar === MetodeBayar::Qris) {
            $this->redirectRoute('customer.payment-qris', $pesanan->transaksi->id);
        } else {
            $this->redirectRoute('order.status', $pesanan);
        }
    }

    public function clearCart(): void
    {
        $this->cartService->clear();
        $this->editingQuantityId = null;
        $this->dispatch('cart-updated', count: 0, total: 0);
    }

    public function startEditingQuantity(int|string $key): void
    {
        $this->editingQuantityId = $key;
        $items = $this->cartService->getItems();
        $this->editingQuantity = $items[$key]['jumlah'] ?? 1;
    }

    public function confirmQuantity(): void
    {
        if ($this->editingQuantityId === null) {
            return;
        }

        if ($this->editingQuantity <= 0) {
            $this->cart->remove((string) $this->editingQuantityId);
            $this->editingQuantityId = null;
            $this->syncCart();

            return;
        }

        try {
            $this->cartService->update((string) $this->editingQuantityId, $this->editingQuantity);
            $this->editingQuantityId = null;
            $this->syncCart();
        } catch (\RuntimeException $e) {
            $this->dispatch('notify', message: $e->getMessage(), type: 'error');
        }
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

    #[Computed]
    public function nomorMeja(): ?string
    {
        if ($this->selectedMejaId === '') {
            return null;
        }

        $meja = Meja::find($this->selectedMejaId);

        return $meja?->nomor;
    }

    #[Computed]
    public function selectedMeja(): ?Meja
    {
        if ($this->selectedMejaId === '') {
            return null;
        }

        return Meja::find($this->selectedMejaId);
    }

    #[Computed]
    public function subtotal(): float
    {
        return $this->cartService->getTotal();
    }

    #[Computed]
    public function burjoName(): string
    {
        return config('app.name', 'BurjoOrder');
    }

    #[Title('Checkout')]
    public function render()
    {
        return view('livewire.customer.checkout', [
            'categories' => $this->categories,
            'menus' => $this->getMenus(),
            'burjoName' => $this->burjoName,
            'nomorMeja' => $this->nomorMeja,
            'mejaToken' => $this->tableToken,
            'cart' => $this->cartItems,
            'cartCount' => $this->cartCount,
            'cartTotal' => $this->cartTotal,
            'subtotal' => $this->subtotal,
        ]);
    }
}
