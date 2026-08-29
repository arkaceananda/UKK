<?php

namespace App\Contracts;

interface CartContract
{
    public function add(int $menuId, int $qty = 1, ?string $option = null): void;

    public function remove(string $key): void;

    public function update(string $key, int $qty): void;

    public function clear(): void;

    public function getItems(): array;

    public function getCount(): int;

    public function getTotal(): float;

    public function setMejaId(int $mejaId): void;

    public function getMejaId(): int;
}
