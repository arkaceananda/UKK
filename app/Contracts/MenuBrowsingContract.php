<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface MenuBrowsingContract
{
    public function getCategories(): Collection;

    public function getMenus(?string $categoryId = null, ?string $search = null): Collection;

    public function loadMore(): void;

    public function resetMenus(): void;

    public function hasMore(): bool;

    public function isLoading(): bool;

    public function setCategory(string $categoryId): void;

    public function getSelectedCategory(): string;

    public function setSearch(string $search): void;

    public function getSearch(): string;
}
