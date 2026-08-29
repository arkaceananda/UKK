<?php

namespace App\Services;

use App\Contracts\MenuBrowsingContract;
use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MenuService implements MenuBrowsingContract
{
    private int $menuPage = 1;

    private int $menuPerPage = 500;

    private array $menusAll = [];

    private bool $hasMoreMenus = true;

    private bool $loadingMore = false;

    private string $selectedCategory = '';

    private string $searchQuery = '';

    public function getCategories(): Collection
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

    public function getMenus(?string $categoryId = null, ?string $search = null): Collection
    {
        $query = Menu::query()
            ->where('status', StatusMenu::Tersedia)
            ->where('stok', '>', 0);

        if ($categoryId !== null && $categoryId !== '') {
            $query->where('kategori_id', $categoryId);
        }

        if ($search !== null && $search !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where('nama', $operator, '%'.$search.'%');
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
                'is_available' => $menu->isAvailable(),
                'options' => $menu->options ?? [],
            ];
        });
    }

    public function loadMore(): void
    {
        if ($this->loadingMore || ! $this->hasMoreMenus) {
            return;
        }

        $this->loadingMore = true;
        $this->menuPage++;

        $pageItems = $this->queryMenuPage($this->menuPage);
        $mapped = $this->mapMenuItems($pageItems);

        $this->menusAll = array_merge($this->menusAll, $mapped);
        $this->hasMoreMenus = $pageItems->count() >= $this->menuPerPage;
        $this->loadingMore = false;
    }

    public function resetMenus(): void
    {
        $this->menuPage = 1;
        $this->hasMoreMenus = true;
        $this->loadingMore = false;
        $this->menusAll = [];
        $this->loadMenuPage();
    }

    public function hasMore(): bool
    {
        return $this->hasMoreMenus;
    }

    public function isLoading(): bool
    {
        return $this->loadingMore;
    }

    public function setCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->resetMenus();
    }

    public function getSelectedCategory(): string
    {
        return $this->selectedCategory;
    }

    public function setSearch(string $search): void
    {
        $this->searchQuery = $search;
        $this->resetMenus();
    }

    public function getSearch(): string
    {
        return $this->searchQuery;
    }

    public function getAllMenus(): array
    {
        return $this->menusAll;
    }

    public function getMenuPage(): int
    {
        return $this->menuPage;
    }

    private function loadMenuPage(): void
    {
        $pageItems = $this->queryMenuPage($this->menuPage);
        $mapped = $this->mapMenuItems($pageItems);

        $this->menusAll = $mapped;
        $this->hasMoreMenus = $pageItems->count() >= $this->menuPerPage;
    }

    private function queryMenuPage(int $page): Collection
    {
        $query = Menu::query()
            ->where('status', StatusMenu::Tersedia)
            ->where('stok', '>', 0);

        if ($this->searchQuery !== '') {
            $operator = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
            $query->where('nama', $operator, '%'.$this->searchQuery.'%');
        }

        if ($this->selectedCategory !== '') {
            $query->where('kategori_id', $this->selectedCategory);
        }

        return $query->with('kategori')
            ->orderBy('kategori_id')
            ->orderBy('nama')
            ->forPage($page, $this->menuPerPage)
            ->get();
    }

    private function mapMenuItems(Collection $pageItems): array
    {
        $imageService = app(ImageCacheService::class);

        return $pageItems->map(function ($menu) use ($imageService) {
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
    }
}
