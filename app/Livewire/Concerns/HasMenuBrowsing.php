<?php

namespace App\Livewire\Concerns;

use App\Contracts\MenuBrowsingContract;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

trait HasMenuBrowsing
{
    protected MenuBrowsingContract $menuService;

    public string $selectedCategory = '';

    public string $searchQuery = '';

    public bool $hasMoreMenus = true;

    public bool $loadingMore = false;

    public function bootHasMenuBrowsing(): void
    {
        $this->menuService = app(MenuBrowsingContract::class);
    }

    public function updatedSearchQuery(): void
    {
        $this->menuService->setSearch($this->searchQuery);
        $this->hasMoreMenus = $this->menuService->hasMore();
        $this->loadingMore = $this->menuService->isLoading();
    }

    public function selectCategory(string $categoryId): void
    {
        $this->selectedCategory = $categoryId;
        $this->menuService->setCategory($categoryId);
        $this->hasMoreMenus = $this->menuService->hasMore();
        $this->loadingMore = $this->menuService->isLoading();
    }

    public function loadMoreMenus(): void
    {
        if ($this->loadingMore) {
            return;
        }

        $this->loadingMore = true;
        $this->menuService->loadMore();
        $this->hasMoreMenus = $this->menuService->hasMore();
        $this->loadingMore = $this->menuService->isLoading();
    }

    #[Computed]
    public function categories(): Collection
    {
        return $this->menuService->getCategories();
    }

    public function getMenus(): Collection
    {
        $menus = $this->menuService->getMenus();

        return $menus ? collect($menus) : collect();
    }
}
