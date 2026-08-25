<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ChartInterface
{
    public function getTitle(): string;

    public function getLabel(): string;

    public function getData(int $days): Collection;

    public function getTotals(int $days): array;

    public function getLabels(int $days): array;

    public function getExportRows(): array;

    public function getCacheTtl(int $days): int;
}
