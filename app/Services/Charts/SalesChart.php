<?php

namespace App\Services\Charts;

use App\Contracts\ChartInterface;
use App\Models\Transaksi;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesChart implements ChartInterface
{
    public function getTitle(): string
    {
        return 'Tren Penjualan';
    }

    public function getLabel(): string
    {
        return 'Waktu';
    }

    public function getData(int $days): Collection
    {
        return $days === 1 ? $this->getHourly() : $this->getDaily($days);
    }

    public function getTotals(int $days): array
    {
        return $this->getData($days)->pluck('total')->map(fn ($v) => (float) $v)->values()->toArray();
    }

    public function getLabels(int $days): array
    {
        return $this->getData($days)->pluck('label')->values()->toArray();
    }

    public function getExportRows(): array
    {
        $filter = request()->query('filter', '7d');
        $days = $filter === '1d' ? 1 : ($filter === '30d' ? 30 : 7);

        return $this->getData($days)
            ->map(fn ($item) => ['Waktu' => $item->label, 'Total (Rp)' => number_format($item->total, 0, ',', '.')])
            ->toArray();
    }

    public function getCacheTtl(int $days): int
    {
        return match ($days) {
            1 => 300,    // 5 minutes
            7 => 900,    // 15 minutes
            30 => 1800,  // 30 minutes
            default => 900,
        };
    }

    private function getHourly(): Collection
    {
        $data = Transaksi::query()
            ->select(DB::raw('EXTRACT(HOUR FROM created_at) as hour'), DB::raw('SUM(total_bayar) as total'))
            ->whereBetween('created_at', [now()->subDay()->startOfDay(), now()])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->mapWithKeys(fn ($item) => [(int) $item->hour => (float) $item->total])
            ->toArray();

        $result = [];
        for ($hour = 0; $hour < 24; $hour += 2) {
            $result[] = (object) ['label' => str_pad($hour, 2, '0', STR_PAD_LEFT).':00', 'total' => $data[$hour] ?? 0];
        }

        return collect($result);
    }

    private function getDaily(int $days): Collection
    {
        $raw = Transaksi::query()
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_bayar) as total'))
            ->whereBetween('created_at', [now()->subDays($days - 1)->startOfDay(), now()])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->date => (float) $item->total])
            ->toArray();

        $result = [];
        for ($i = 0; $i < $days; $i++) {
            $date = now()->subDays($days - 1 - $i);
            $key = $date->format('Y-m-d');
            $label = $days === 7 ? $date->translatedFormat('D') : $date->format('d/m/y');
            $result[] = (object) ['label' => $label, 'total' => $raw[$key] ?? 0];
        }

        return collect($result);
    }
}
