<?php

namespace App\Services\Charts;

use App\Contracts\ChartInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class ChartService
{
    private ChartInterface $chart;

    public function __construct(ChartInterface $chart)
    {
        $this->chart = $chart;
    }

    public function getData(int $days): array
    {
        return Cache::remember($this->getCacheKey($days), $this->chart->getCacheTtl($days), function () use ($days) {
            $data = $this->chart->getData($days);

            return $data->map(fn ($item) => (array) $item)->toArray();
        });
    }

    public function getTotals(int $days): array
    {
        $data = $this->getData($days);

        return collect($data)->pluck('total')->map(fn ($v) => is_float($v) ? $v : (int) $v)->values()->toArray();
    }

    public function getLabels(int $days): array
    {
        $data = $this->getData($days);

        return collect($data)->pluck('label')->values()->toArray();
    }

    public function flushCache(): void
    {
        Cache::flush();
    }

    private function getCacheKey(int $days): string
    {
        return "chart_{$this->chart->getTitle()}_{$days}d_".Carbon::now()->format('Y-m-d_H');
    }
}
