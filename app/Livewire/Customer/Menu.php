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
use App\Models\Menu as MenuModel;
use App\Services\OrderService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Menu')]
class Menu extends Component
{
    use HasCart;
    use HasMenuBrowsing;
    use VerifiesTable;

    #[Locked]
    public int $mejaId;

    public string $notes = '';

    public string $metodeBayar = 'tunai';

    public array $selectedOptions = [];

    public ?int $optionModalItemId = null;

    public array $optionModalOptions = [];

    public string $optionModalName = '';

    public ?string $pendingOption = null;

    public array $cart = [];

    protected $queryString = [
        'selectedCategory' => ['except' => ''],
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

    public function addToCart(int|string $key): void
    {
        [$menuId, $selectedOption] = $this->parseCartKey($key);

        if (! empty($selectedOption)) {
            $this->addToCartWithOption($menuId, $selectedOption);

            return;
        }

        if (! empty($this->optionModalOptions) && $this->optionModalItemId === (int) $menuId) {
            $selectedOption = $this->pendingOption;
            $this->addToCartWithOption($menuId, $selectedOption);
            $this->closeOptionModal();

            return;
        }

        $this->cartService->add($menuId);
        $this->syncCart();
        $this->dispatch('notify', message: 'Ditambahkan ke keranjang', type: 'success');
    }

    public function openOptionModal(int $menuId): void
    {
        $menu = MenuModel::find($menuId);

        if (! $menu || empty($menu->options)) {
            $this->cartService->add($menuId);
            $this->syncCart();
            $this->dispatch('notify', message: 'Ditambahkan ke keranjang', type: 'success');

            return;
        }

        $this->optionModalItemId = $menuId;
        $this->optionModalOptions = $menu->options;
        $this->optionModalName = $menu->nama;
        $this->pendingOption = $menu->options[0];
    }

    public function confirmOptionAdd(): void
    {
        if ($this->optionModalItemId === null) {
            return;
        }

        $menuId = $this->optionModalItemId;

        if ($this->pendingOption !== null) {
            $this->selectedOptions[$menuId] = $this->pendingOption;
        }

        $this->addToCartWithOption($menuId, $this->pendingOption);
        $this->closeOptionModal();
    }

    public function confirmOptionAddWith(?string $option): void
    {
        if ($this->optionModalItemId === null) {
            return;
        }

        $menuId = $this->optionModalItemId;
        $option = ($option === '' || $option === null) ? null : $option;

        if ($option !== null) {
            $this->selectedOptions[$menuId] = $option;
        }

        $this->addToCartWithOption($menuId, $option);
        $this->closeOptionModal();
    }

    public function closeOptionModal(): void
    {
        $this->optionModalItemId = null;
        $this->optionModalOptions = [];
        $this->optionModalName = '';
        $this->pendingOption = null;
    }

    public function decrementQuantity(int|string $key): void
    {
        $items = $this->cartService->getItems();
        if (! isset($items[$key])) {
            return;
        }

        if ($items[$key]['jumlah'] > 1) {
            $this->cartService->update((string) $key, $items[$key]['jumlah'] - 1);
        } else {
            $this->cartService->remove((string) $key);
        }
        $this->syncCart();
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
            $this->cartService->remove((string) $this->editingQuantityId);
            $this->editingQuantityId = null;
            $this->syncCart();

            return;
        }

        $this->cartService->update((string) $this->editingQuantityId, $this->editingQuantity);
        $this->editingQuantityId = null;
        $this->syncCart();
    }

    public function cancelEditingQuantity(): void
    {
        $this->editingQuantityId = null;
    }

    public function checkout(): void
    {
        $items = $this->cartService->getItems();
        if (empty($items)) {
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

    protected function parseCartKey(int|string $key): array
    {
        if (is_string($key) && str_contains($key, '__')) {
            [$menuId, $option] = explode('__', $key, 2);

            return [(int) $menuId, $option];
        }

        return [(int) $key, null];
    }

    #[Computed]
    public function meja(): Meja
    {
        return Meja::find($this->mejaId) ?? new Meja;
    }

    public function render()
    {
        return view('livewire.customer.menu', [
            'categories' => $this->categories,
            'menus' => $this->getMenus(),
            'cartCount' => $this->cartCount,
            'cartTotal' => $this->cartTotal,
            'meja' => $this->meja,
        ]);
    }
}
