<?php

namespace App\Livewire\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMenu;
use App\Events\OrderPlaced;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu as MenuModel;
use App\Services\ImageCacheService;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
#[Title('Menu')]
class Menu extends Component
{
    #[Locked]
    public int $mejaId;

    public ?string $tableToken = null;

    public bool $verified = false;

    public array $cart = [];

    public string $selectedCategory = '';

    public string $searchQuery = '';

    public string $selectedMejaId = '';

    public string $notes = '';

    public string $metodeBayar = 'tunai';

    public array $selectedOptions = [];

    public int|string|null $editingQuantityId = null;

    public int $editingQuantity = 1;

    public array $menusAll = [];

    public int $menuPage = 1;

    public bool $hasMoreMenus = true;

    public bool $loadingMore = false;

    public int $menuPerPage = 18;

    protected $queryString = [
        'selectedCategory' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount(Meja $meja): void
    {
        $this->mejaId = $meja->id;
        $this->cart = session('burjo_cart_'.$this->mejaId, []);
        $this->selectedMejaId = (string) $this->mejaId;

        $this->tableToken = session('assigned_meja_token');
        $this->verified = session('assigned_meja_id') === $meja->id
            && $this->tableToken === $meja->token;

        $firstCategory = KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->first();

        if ($this->selectedCategory === '' && $firstCategory) {
            $this->selectedCategory = (string) $firstCategory->id;
        }

        $this->resetMenus();
    }

    public function updatedSearchQuery(): void
    {
        $this->resetMenus();
    }

    protected function loadMenuPage(): void
    {
        $query = MenuModel::query()
            ->where('status', StatusMenu::Tersedia)
            ->where('stok', '>', 0);

        if ($this->searchQuery !== '') {
            $query->where('nama', 'ilike', '%'.$this->searchQuery.'%');
        }

        $pageItems = $query->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama')
            ->forPage($this->menuPage, $this->menuPerPage)
            ->get();

        $imageService = app(ImageCacheService::class);

        $mapped = $pageItems->map(function ($menu) use ($imageService) {
            return [
                'id' => $menu->id,
                'nama' => $menu->nama,
                'deskripsi' => $menu->deskripsi,
                'harga' => (float) $menu->harga,
                'foto' => $menu->foto,
                'kategori' => $menu->kategori?->nama,
                'cachedImage' => $menu->foto ? $imageService->getCachedUrl($menu->foto) : '',
                'is_available' => $menu->isAvailable(),
                'options' => $menu->options ?? [],
            ];
        })->all();

        if ($this->menuPage === 1) {
            $this->menusAll = $mapped;
        } else {
            $this->menusAll = array_merge($this->menusAll, $mapped);
        }

        $this->hasMoreMenus = $pageItems->count() >= $this->menuPerPage;
    }

    public function resetMenus(): void
    {
        $this->menuPage = 1;
        $this->hasMoreMenus = true;
        $this->loadingMore = false;
        $this->loadMenuPage();
    }

    public function loadMoreMenus(): void
    {
        if ($this->loadingMore || ! $this->hasMoreMenus) {
            return;
        }

        $this->loadingMore = true;
        $this->menuPage++;
        $this->loadMenuPage();
        $this->loadingMore = false;

        $this->dispatch('refresh-images');
    }

    public function addToCart(int|string $key): void
    {
        [$menuId, $selectedOption] = $this->parseCartKey($key);

        $lock = Cache::lock('menu-stock:'.$menuId, 3);

        if (! $lock->get()) {
            $this->dispatch('notify', message: 'Menu sedang dibooking, coba lagi', type: 'error');

            return;
        }

        try {
            $menu = MenuModel::findOrFail($menuId);

            if (! $menu->isAvailable()) {
                $this->dispatch('notify', message: 'Menu tidak tersedia', type: 'error');

                return;
            }

            if (! empty($menu->options)) {
                $selectedOption = $this->selectedOptions[$menuId] ?? $menu->options[0] ?? null;
            }

            $cartKey = $selectedOption !== null ? $menuId.'__'.$selectedOption : $menuId;

            if (isset($this->cart[$cartKey])) {
                if ($this->cart[$cartKey]['jumlah'] >= $menu->stok) {
                    $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

                    return;
                }

                $this->cart[$cartKey]['jumlah']++;
            } else {
                $this->cart[$cartKey] = [
                    'menu_id' => $menuId,
                    'nama' => $menu->nama,
                    'harga' => (float) $menu->harga,
                    'jumlah' => 1,
                    'foto' => $menu->foto,
                    'is_available' => $menu->isAvailable(),
                    'selected_option' => $selectedOption,
                ];
            }
        } finally {
            $lock->release();
        }

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    public function decrementQuantity(int|string $key): void
    {
        if (! isset($this->cart[$key])) {
            return;
        }

        if ($this->cart[$key]['jumlah'] > 1) {
            $this->cart[$key]['jumlah']--;
        } else {
            unset($this->cart[$key]);
        }

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    public function removeFromCart(int|string $key): void
    {
        unset($this->cart[$key]);

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    protected function parseCartKey(int|string $key): array
    {
        if (is_string($key) && str_contains($key, '__')) {
            [$menuId, $option] = explode('__', $key, 2);

            return [(int) $menuId, $option];
        }

        return [(int) $key, null];
    }

    public function startEditingQuantity(int|string $key): void
    {
        $this->editingQuantityId = $key;
        $this->editingQuantity = $this->cart[$key]['jumlah'] ?? 1;
    }

    public function confirmQuantity(): void
    {
        if ($this->editingQuantityId === null) {
            return;
        }

        $key = $this->editingQuantityId;

        if ($this->editingQuantity <= 0) {
            $this->removeFromCart($key);
            $this->editingQuantityId = null;

            return;
        }

        [$menuId] = $this->parseCartKey($key);

        $lock = Cache::lock('menu-stock:'.$menuId, 3);

        if (! $lock->get()) {
            $this->dispatch('notify', message: 'Menu sedang dibooking, coba lagi', type: 'error');

            return;
        }

        try {
            $menu = MenuModel::findOrFail($menuId);

            if ($this->editingQuantity > $menu->stok) {
                $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

                return;
            }

            if (isset($this->cart[$key])) {
                $this->cart[$key]['jumlah'] = $this->editingQuantity;
            }

            $this->editingQuantityId = null;
        } finally {
            $lock->release();
        }

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
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

        try {
            $pesanan = $orderService->checkout(
                Meja::findOrFail($this->selectedMejaId),
                array_map(fn ($item) => [
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'selected_option' => $item['selected_option'] ?? null,
                ], $this->cart),
                MetodeBayar::from($this->metodeBayar),
                $this->notes !== '' ? $this->notes : null,
                null,
                $this->tableToken,
            );
        } catch (\Exception $e) {
            $this->dispatch('notify', message: 'Gagal membuat pesanan: '.$e->getMessage(), type: 'error');

            return;
        }

        $this->cart = [];
        $this->notes = '';
        $this->editingQuantityId = null;
        session(['burjo_cart_'.$this->mejaId => $this->cart]);

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
        $this->cart = [];
        $this->editingQuantityId = null;
        session(['burjo_cart_'.$this->mejaId => $this->cart]);

        $this->dispatch('cart-updated', count: 0, total: 0);
    }

    #[On('refreshStock')]
    public function refreshStock(): void
    {
        $this->resetMenus();
        unset($this->cartCount);
        unset($this->cartTotal);
    }

    #[Computed]
    public function categories(): Collection
    {
        $categories = KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->withCount(['menu' => fn ($q) => $q->where('status', StatusMenu::Tersedia)])
            ->get();

        $order = [
            'Snack',
            'Nasi Goreng',
            'Nasi Katsu',
            'Chicken Rice Bowl',
            'Menu Nasi',
            'Base Mie',
            'Beef Slice',
            'Tea & Fresh Drink',
            'Juice',
            'Coffee',
            'Minuman Favorit',
            'Add On',
        ];

        return $categories->sortBy(function ($category) use ($order) {
            $index = array_search($category->nama, $order, true);

            return $index === false ? PHP_INT_MAX : $index;
        })->values();
    }

    #[Computed]
    public function menus(): Collection
    {
        return collect($this->menusAll);
    }

    #[Computed]
    public function cartTotal(): float
    {
        return collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
    }

    #[Computed]
    public function cartCount(): int
    {
        return collect($this->cart)->sum(fn ($item) => $item['jumlah']);
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
            'menus' => $this->menus,
            'cartCount' => $this->cartCount,
            'cartTotal' => $this->cartTotal,
            'meja' => $this->meja,
        ]);
    }
}
