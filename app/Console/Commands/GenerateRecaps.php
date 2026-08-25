<?php

namespace App\Console\Commands;

use App\Models\DetailPesanan;
use App\Models\Pesanan;
use App\Models\Recap;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class GenerateRecaps extends Command
{
    protected $signature = 'recaps:generate {type=daily} {--date=}';

    protected $description = 'Generate daily recaps (berjalan otomatis tiap hari via scheduler)';

    public function handle(): int
    {
        $type = $this->argument('type');

        if ($type === 'backfill') {
            $this->generateBackfill();

            return self::SUCCESS;
        }

        if (in_array($type, ['all', 'daily'])) {
            $this->generateDaily();
        }

        return self::SUCCESS;
    }

    private function generateDaily(): void
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        if (Recap::where('type', 'daily')->where('period_start', $start->format('Y-m-d'))->exists()) {
            $this->info("Daily recap untuk {$start->format('Y-m-d')} sudah ada. Lewati.");

            return;
        }

        $data = $this->getRecapData($start, $end);

        Recap::create([
            'type' => 'daily',
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
            ...$data,
            'is_finalized' => true,
            'finalized_at' => now(),
        ]);

        $this->info("Daily recap untuk {$start->format('Y-m-d')} berhasil dibuat.");
    }

    private function generateBackfill(): void
    {
        $earliest = Transaksi::min('created_at');

        if (! $earliest) {
            $this->info('Tidak ada transaksi untuk di-backfill.');

            return;
        }

        $start = Carbon::parse($earliest)->startOfDay();
        $end = Carbon::yesterday()->endOfDay();

        $created = 0;
        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $dayStart = $day->copy()->startOfDay();
            $dayEnd = $day->copy()->endOfDay();

            if (Recap::where('type', 'daily')->where('period_start', $dayStart->format('Y-m-d'))->exists()) {
                continue;
            }

            $data = $this->getRecapData($dayStart, $dayEnd);

            Recap::create([
                'type' => 'daily',
                'period_start' => $dayStart->format('Y-m-d'),
                'period_end' => $dayEnd->format('Y-m-d'),
                ...$data,
                'is_finalized' => true,
                'finalized_at' => now(),
            ]);

            $created++;
        }

        $this->info("Backfill selesai. {$created} daily recap dibuat ({$start->format('Y-m-d')} – {$end->format('Y-m-d')}).");
    }

    private function getRecapData(Carbon $start, Carbon $end): array
    {
        $transactions = Transaksi::whereBetween('created_at', [$start, $end])->get();

        $totalRevenue = $transactions->sum('total_bayar');
        $totalOrders = Pesanan::whereIn('id', $transactions->pluck('pesanan_id'))->count();

        $pesananIds = $transactions->pluck('pesanan_id');
        $totalItemsSold = DetailPesanan::whereIn('pesanan_id', $pesananIds)->sum('jumlah');

        $topMenus = DetailPesanan::whereIn('pesanan_id', $pesananIds)
            ->join('menu', 'detail_pesanan.menu_id', '=', 'menu.id')
            ->select('menu.nama', DB::raw('SUM(detail_pesanan.jumlah) as total'))
            ->groupBy('menu.nama')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();

        $dailyBreakdown = [[
            'date' => $start->format('Y-m-d'),
            'revenue' => $totalRevenue,
            'orders' => $totalOrders,
        ]];

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'total_items_sold' => $totalItemsSold,
            'top_menus' => $topMenus,
            'daily_breakdown' => $dailyBreakdown,
        ];
    }
}
