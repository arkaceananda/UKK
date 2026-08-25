<?php

namespace App\Services\Charts;

use App\Contracts\ChartInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TopMenuChart implements ChartInterface
{
    public function getTitle(): string
    {
        return 'Top 5 Menu Terlaris';
    }

    public function getLabel(): string
    {
        return 'Menu';
    }

    public function getData(int $days): Collection
    {
        $startDate = now()->subDays($days - 1)->startOfDay();

        return DB::table('detail_pesanan')
            ->join('menu', 'detail_pesanan.menu_id', '=', 'menu.id')
            ->join('pesanan', 'detail_pesanan.pesanan_id', '=', 'pesanan.id')
            ->where('pesanan.created_at', '>=', $startDate)
            ->select('menu.nama as label', DB::raw('SUM(detail_pesanan.jumlah) as total'))
            ->groupBy('menu.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($item) => (object) ['label' => $item->label, 'total' => (int) $item->total]);
    }

    public function getTotals(int $days): array
    {
        return $this->getData($days)->pluck('total')->map(fn ($v) => (int) $v)->values()->toArray();
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
            ->map(fn ($item) => ['Menu' => $item->label, 'Total Porsi' => $item->total])
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
}
