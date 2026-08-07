<?php

namespace App\Livewire\Customer;

use App\Enums\MetodeBayar;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Meja;
use App\Models\Menu as MenuModel;
use App\Services\OrderService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.customer')]
class Checkout extends Component
{
    #[Locked]
    public int $mejaId;

    public array $cart = [];

    public string $selectedCategory = '';

    public string $selectedMejaId = '';

    public string $searchQuery = '';

    public string $notes = '';

    public string $metodeBayar = '';

    public ?int $editingQuantityId = null;

    public int $editingQuantity = 1;

    protected $queryString = [
        'selectedCategory' => ['except' => ''],
        'selectedMejaId' => ['except' => ''],
        'searchQuery' => ['except' => ''],
    ];

    public function mount(Meja $meja): void
    {
        $this->mejaId = $meja->id;
        $this->selectedMejaId = (string) $meja->id;
        $this->cart = session('burjo_cart_'.$this->mejaId, []);

        $firstCategory = KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->first();

        if ($this->selectedCategory === '' && $firstCategory) {
            $this->selectedCategory = (string) $firstCategory->id;
        }
    }

    public function backToMenu(): void
    {
        $this->redirect(route('customer.menu', ['meja' => $this->selectedMejaId]));
    }

    public function addToCart(int $menuId): void
    {
        $menu = MenuModel::findOrFail($menuId);

        if (! $menu->isAvailable()) {
            $this->dispatch('notify', message: 'Menu tidak tersedia', type: 'error');

            return;
        }

        $cartKey = null;
        foreach ($this->cart as $key => $item) {
            if (isset($item['menu_id']) && $item['menu_id'] == $menuId) {
                $cartKey = $key;
                break;
            }
        }

        if ($cartKey !== null) {
            if ($this->cart[$cartKey]['jumlah'] >= $menu->stok) {
                $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

                return;
            }

            $this->cart[$cartKey]['jumlah']++;
        } else {
            $this->cart[$menuId] = [
                'menu_id' => $menuId,
                'nama' => $menu->nama,
                'harga' => (float) $menu->harga,
                'jumlah' => 1,
                'foto' => $menu->foto,
                'is_available' => $menu->isAvailable(),
            ];
        }

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    public function removeItem(int $menuId): void
    {
        $cartKey = null;
        foreach ($this->cart as $key => $item) {
            if (isset($item['menu_id']) && $item['menu_id'] == $menuId) {
                $cartKey = $key;
                break;
            }
        }

        if ($cartKey === null) {
            return;
        }

        unset($this->cart[$cartKey]);

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    public function removeFromCart(int $menuId): void
    {
        $this->removeItem($menuId);
    }

    public function updateQuantity(int $menuId, int $jumlah): void
    {
        $cartKey = null;
        foreach ($this->cart as $key => $item) {
            if (isset($item['menu_id']) && $item['menu_id'] == $menuId) {
                $cartKey = $key;
                break;
            }
        }

        if ($cartKey === null) {
            return;
        }

        if ($jumlah <= 0) {
            $this->removeItem($menuId);

            return;
        }

        $menu = MenuModel::findOrFail($menuId);

        if ($jumlah > $menu->stok) {
            $this->dispatch('notify', message: 'Stok tidak cukup', type: 'error');

            return;
        }

        $this->cart[$cartKey]['jumlah'] = $jumlah;

        $count = collect($this->cart)->sum(fn ($item) => $item['jumlah']);
        $total = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $this->dispatch('cart-updated', count: $count, total: $total);
        session(['burjo_cart_'.$this->mejaId => $this->cart]);
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
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
        $this->metodeBayar = '';
        $this->editingQuantityId = null;
        session(['burjo_cart_'.$this->mejaId => $this->cart]);

        $this->dispatch('notify', message: 'Pesanan berhasil dibuat!', type: 'success');
        $this->dispatch('order-placed', orderId: $pesanan->id);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->editingQuantityId = null;
        session(['burjo_cart_'.$this->mejaId => $this->cart]);

        $this->dispatch('cart-updated', count: 0, total: 0);
    }

    public function getBurjoNameProperty(): string
    {
        return config('app.name', 'BurjoOrder');
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
        return collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
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
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return KategoriMenu::whereHas('menu', fn ($q) => $q->where('status', StatusMenu::Tersedia))
            ->withCount(['menu' => fn ($q) => $q->where('status', StatusMenu::Tersedia)])
            ->orderBy('nama')
            ->get();
    }

    #[Computed]
    public function menus(): Collection
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

        return $menus->map(function ($menu) {
            return [
                'id' => $menu->id,
                'nama' => $menu->nama,
                'deskripsi' => $menu->deskripsi,
                'harga' => (float) $menu->harga,
                'foto' => $menu->foto,
                'kategori' => $menu->kategori?->nama,
            ];
        });
    }

    #[Title('Checkout')]
    public function render()
    {
        $subtotal = collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']);
        $cartCount = collect($this->cart)->sum(fn ($item) => $item['jumlah']);

        return view('livewire.customer.checkout', [
            'categories' => $this->categories,
            'menus' => $this->menus,
            'burjoName' => $this->burjoName,
            'nomorMeja' => $this->nomorMeja,
            'cartCount' => $cartCount,
            'cartTotal' => collect($this->cart)->sum(fn ($item) => $item['harga'] * $item['jumlah']),
            'subtotal' => $subtotal,
        ]);
    }
}
