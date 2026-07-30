<?php

namespace App\Livewire\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMeja;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu as MenuModel;
use App\Services\ImageCacheService;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class Menu extends Component
{
    public array $cart = [];

    public string $selectedCategory = '';

    public string $selectedMejaId = '';

    public string $searchQuery = '';

    public string $notes = '';

    public string $metodeBayar = 'tunai';

    public ?int $editingQuantityId = null;

    public int $editingQuantity = 1;

    protected $queryString = [
        'selectedCategory' => ['except' => ''],
        'selectedMejaId' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount(): void
    {
        $firstCategory = KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->first();

        if ($this->selectedCategory === '' && $firstCategory) {
            $this->selectedCategory = (string) $firstCategory->id;
        }

        if ($this->selectedMejaId === '' && request()->has('meja')) {
            $this->selectedMejaId = request('meja');
        }
    }

    public function addToCart(int $menuId): void
    {
        $menu = MenuModel::findOrFail($menuId);

        if (! $menu->isAvailable()) {
            $this->dispatch('notify', message: 'Menu tidak tersedia', type: 'error');

            return;
        }

        if (isset($this->cart[$menuId])) {
            if ($this->cart[$menuId]['jumlah'] >= $menu->stok) {
                $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

                return;
            }

            $this->cart[$menuId]['jumlah']++;
        } else {
            $this->cart[$menuId] = [
                'menu_id' => $menuId,
                'nama' => $menu->nama,
                'harga' => (float) $menu->harga,
                'jumlah' => 1,
                'foto' => $menu->foto,
            ];
        }

        $this->dispatch('cart-updated', count: $this->cartCount, total: $this->cartTotal);
    }

    public function removeFromCart(int $menuId): void
    {
        unset($this->cart[$menuId]);

        $this->dispatch('cart-updated', count: $this->cartCount, total: $this->cartTotal);
    }

    public function startEditingQuantity(int $menuId): void
    {
        $this->editingQuantityId = $menuId;
        $this->editingQuantity = $this->cart[$menuId]['jumlah'] ?? 1;
    }

    public function confirmQuantity(): void
    {
        if ($this->editingQuantityId === null) {
            return;
        }

        $menuId = $this->editingQuantityId;

        if ($this->editingQuantity <= 0) {
            $this->removeFromCart($menuId);
            $this->editingQuantityId = null;

            return;
        }

        $menu = MenuModel::findOrFail($menuId);

        if ($this->editingQuantity > $menu->stok) {
            $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

            return;
        }

        $this->cart[$menuId]['jumlah'] = $this->editingQuantity;
        $this->editingQuantityId = null;

        $this->dispatch('cart-updated', count: $this->cartCount, total: $this->cartTotal);
    }

    public function cancelEditingQuantity(): void
    {
        $this->editingQuantityId = null;
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            return;
        }

        if ($this->selectedMejaId === '') {
            $this->dispatch('notify', message: 'Pilih meja terlebih dahulu', type: 'error');

            return;
        }

        $orderService = app(OrderService::class);

        $pesanan = $orderService->checkout(
            Meja::findOrFail($this->selectedMejaId),
            array_map(fn ($item) => [
                'menu_id' => $item['menu_id'],
                'jumlah' => $item['jumlah'],
            ], $this->cart),
            MetodeBayar::from($this->metodeBayar),
            $this->notes !== '' ? $this->notes : null,
        );

        $this->cart = [];
        $this->notes = '';
        $this->editingQuantityId = null;

        $this->dispatch('notify', message: 'Pesanan berhasil dibuat!', type: 'success');
        $this->dispatch('order-placed', orderId: $pesanan->id);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->editingQuantityId = null;

        $this->dispatch('cart-updated', count: 0, total: 0);
    }

    public function getCartTotalProperty(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
    }

    public function getCartCountProperty(): int
    {
        return collect($this->cart)->sum(fn ($item) => $item['jumlah']);
    }

    public function getCategoriesProperty(): \Illuminate\Database\Eloquent\Collection
    {
        return KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->withCount(['menu' => fn ($q) => $q->where('status', StatusMenu::Tersedia)])
            ->orderBy('nama')
            ->get();
    }

    public function getMenusProperty(): Collection
    {
        $query = MenuModel::query()
            ->where('status', StatusMenu::Tersedia)
            ->where('stok', '>', 0);

        if ($this->selectedCategory !== '') {
            $query->where('kategori_id', $this->selectedCategory);
        }

        if ($this->searchQuery !== '') {
            $query->where('nama', 'ilike', '%'.$this->searchQuery.'%');
        }

        $menus = $query->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama')
            ->get();

        $imageService = app(ImageCacheService::class);

        return $menus->map(function ($menu) use ($imageService) {
            return [
                'id' => $menu->id,
                'nama' => $menu->nama,
                'deskripsi' => $menu->deskripsi,
                'harga' => (float) $menu->harga,
                'foto' => $menu->foto,
                'kategori' => $menu->kategori?->nama,
                'cachedImage' => $menu->foto ? $imageService->getCachedUrl($menu->foto) : '',
            ];
        });
    }

    #[Layout('layouts.customer')]
    #[Title('Menu')]
    public function render()
    {
        return view('livewire.customer.menu', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'meja' => Meja::where('status', StatusMeja::Aktif)->get(),
            'cartCount' => $this->cartCount,
            'cartTotal' => $this->cartTotal,
        ]);
    }
}
