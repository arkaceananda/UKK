<?php

namespace App\Console\Commands;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Recap;
use App\Models\SesiMeja;
use App\Models\Transaksi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo {--days=30} {--clean}';

    protected $description = 'Generate dummy pesanan/transaksi untuk demo dashboard (default 30 hari ke belakang).';

    private array $catatan = [
        null, null, null, null, null,
        'Tolong tidak pakai cabe',
        'Dibungkus, dibawa pulang',
        'Es batu pisah',
        'Level pedas 3',
        'Extra sambal',
    ];

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));

        $kasir = User::all()->first(fn (User $user) => $user->isKasir());
        $mejaIds = Meja::pluck('id')->all();
        $menus = Menu::select('id', 'harga')->get();

        if (! $kasir || count($mejaIds) === 0 || $menus->isEmpty()) {
            $this->error('Butuh data dasar: user kasir, meja, dan menu. Jalankan migrate:fresh --seed dulu.');

            return self::FAILURE;
        }

        if ($this->option('clean')) {
            $this->cleanUp();
        }

        $targetDates = $this->targetDates($days);
        $eventDays = $targetDates->random(min(3, count($targetDates)))->pluck('ymd')->all();

        $stats = ['pesanan' => 0, 'detail' => 0, 'transaksi' => 0, 'revenue' => 0];
        $bar = $this->getOutput()->createProgressBar(count($targetDates));
        $bar->start();

        foreach ($targetDates as $date) {
            $isWeekend = in_array($date->date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
            $isEvent = in_array($date->date->format('Y-m-d'), $eventDays);

            $base = $isWeekend ? random_int(4, 9) : random_int(6, 16);

            if ($isEvent) {
                $base = (int) round($base * random_int(15, 25) / 10);
            }

            for ($order = 0; $order < max(1, $base); $order++) {
                $payload = $this->makePesananPayload($kasir->id, $mejaIds, $menus, $date);

                $pesanan = new Pesanan([
                    'meja_id' => $payload['meja_id'],
                    'kasir_id' => $kasir->id,
                    'status' => $payload['status'],
                    'catatan' => $payload['catatan'],
                    'total_harga' => $payload['total'],
                ]);
                $pesanan->timestamps = false;
                $pesanan->created_at = $payload['created_at'];
                $pesanan->updated_at = $payload['created_at'];
                $pesanan->save();

                foreach ($payload['items'] as $item) {
                    DetailPesanan::create([
                        'pesanan_id' => $pesanan->id,
                        'menu_id' => $item['menu_id'],
                        'jumlah' => $item['jumlah'],
                        'harga_satuan' => $item['harga_satuan'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                $transaksi = new Transaksi([
                    'pesanan_id' => $pesanan->id,
                    'metode_bayar' => $this->pickMetode(),
                    'total_bayar' => $payload['total'],
                    'status_bayar' => StatusBayar::Lunas,
                ]);
                $transaksi->created_at = $payload['created_at'];
                $transaksi->save();

                $stats['pesanan']++;
                $stats['detail'] += count($payload['items']);
                $stats['transaksi']++;
                $stats['revenue'] += $payload['total'];
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info(
            "Selesai: {$stats['pesanan']} pesanan, {$stats['detail']} detail item, "
            ."{$stats['transaksi']} transaksi, revenue Rp".number_format($stats['revenue'], 0, ',', '.').' '
            ."untuk {$days} hari terakhir."
        );

        return self::SUCCESS;
    }

    private function targetDates(int $days): Collection
    {
        return collect(range(0, $days - 1))
            ->map(fn (int $offset) => Carbon::today()->subDays($offset))
            ->map(fn (Carbon $date) => (object) [
                'date' => $date,
                'ymd' => $date->format('Y-m-d'),
            ]);
    }

    private function makePesananPayload(int $kasirId, array $mejaIds, Collection $menus, object $dateInfo): array
    {
        $cheap = $menus->filter(fn (Menu $menu) => (int) $menu->harga <= 15000);

        $roll = random_int(0, 99);
        $itemCount = $roll < 55 ? 2 : ($roll < 80 ? 3 : 1);

        $items = [];
        $total = 0;

        for ($i = 0; $i < $itemCount; $i++) {
            $menu = random_int(0, 3) === 0 || $cheap->isEmpty()
                ? $menus->random()
                : $cheap->random();

            $qtyRoll = random_int(0, 99);
            $jumlah = $qtyRoll < 70 ? 1 : ($qtyRoll < 90 ? 2 : 3);

            $items[] = [
                'menu_id' => $menu->id,
                'jumlah' => $jumlah,
                'harga_satuan' => (int) $menu->harga,
                'subtotal' => (int) $menu->harga * $jumlah,
            ];
            $total += (int) $menu->harga * $jumlah;
        }

        $createdAt = $dateInfo->date
            ->copy()
            ->startOfDay()
            ->addHours(10)
            ->addMinutes(random_int(0, 719));

        if ($createdAt->isFuture()) {
            $createdAt = now()->subMinutes(random_int(5, 45));
        }

        $status = $createdAt->isToday()
            ? $this->pickTodayStatus()
            : StatusPesanan::Selesai;

        return [
            'meja_id' => $mejaIds[array_rand($mejaIds)],
            'status' => $status,
            'catatan' => $this->catatan[array_rand($this->catatan)],
            'items' => $items,
            'total' => $total,
            'created_at' => $createdAt,
        ];
    }

    private function pickTodayStatus(): StatusPesanan
    {
        $roll = random_int(0, 99);

        if ($roll < 60) {
            return StatusPesanan::Selesai;
        }

        return [StatusPesanan::Menunggu, StatusPesanan::Diproses, StatusPesanan::Diterima][random_int(0, 2)];
    }

    private function pickMetode(): MetodeBayar
    {
        return random_int(0, 99) < 60 ? MetodeBayar::Qris : MetodeBayar::Tunai;
    }

    private function cleanUp(): void
    {
        Transaksi::query()->delete();
        DetailPesanan::query()->delete();
        SesiMeja::query()->delete();
        Pesanan::query()->delete();
        Recap::query()->delete();

        $this->info('Data demo lama dihapus (pesanan, detail, transaksi, sesi meja, recap).');
    }
}
