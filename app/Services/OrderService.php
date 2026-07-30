<?php

namespace App\Services;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Events\OrderPlaced;
use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Checkout: create order + details + transaction, reduce stock, broadcast.
     *
     * @param  array<int, array{menu_id: int, jumlah: int}>  $items
     */
    public function checkout(Meja $meja, array $items, MetodeBayar $metodeBayar, ?string $catatan = null): Pesanan
    {
        return DB::transaction(function () use ($meja, $items, $metodeBayar, $catatan) {
            $pesanan = Pesanan::create([
                'meja_id' => $meja->id,
                'status' => StatusPesanan::Menunggu,
                'catatan' => $catatan,
                'total_harga' => 0,
            ]);

            $totalHarga = 0;

            foreach ($items as $item) {
                $menu = Menu::lockForUpdate()->findOrFail($item['menu_id']);

                if (! $menu->isAvailable() || $menu->stok < $item['jumlah']) {
                    throw new \DomainException("Menu '{$menu->nama}' tidak tersedia atau stok tidak cukup.");
                }

                $subtotal = $menu->harga * $item['jumlah'];

                DetailPesanan::create([
                    'pesanan_id' => $pesanan->id,
                    'menu_id' => $menu->id,
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $menu->harga,
                    'subtotal' => $subtotal,
                ]);

                $menu->reduceStock($item['jumlah']);
                $totalHarga += $subtotal;
            }

            $pesanan->update(['total_harga' => $totalHarga]);

            Transaksi::create([
                'pesanan_id' => $pesanan->id,
                'metode_bayar' => $metodeBayar,
                'total_bayar' => $totalHarga,
                'status_bayar' => StatusBayar::Pending,
            ]);

            $pesanan->load(['meja', 'details.menu', 'transaksi']);

            event(new OrderPlaced($pesanan));

            return $pesanan;
        });
    }
}
