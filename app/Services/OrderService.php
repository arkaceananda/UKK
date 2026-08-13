<?php

namespace App\Services;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use App\Enums\StatusPesanan;
use App\Events\StockUpdated;
use App\Models\DetailPesanan;
use App\Models\Meja;
use App\Models\Menu;
use App\Models\Pesanan;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Checkout: create order + details + transaction, reduce stock, broadcast.
     *
     * @param  array<int, array{menu_id: int, jumlah: int, harga_satuan: int}>  $items
     */
    public function checkout(Meja $meja, array $items, MetodeBayar $metodeBayar = MetodeBayar::Tunai, ?string $catatan = null, ?int $kasirId = null): Pesanan
    {
        $pesanan = DB::transaction(function () use ($meja, $items, $metodeBayar, $catatan, $kasirId) {
            $pesanan = Pesanan::create([
                'meja_id' => $meja->id,
                'kasir_id' => $kasirId,
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
                event(new StockUpdated($menu->id, $menu->fresh()->stok, $menu->fresh()->status->value));
                $totalHarga += $subtotal;
            }

            $pesanan->update(['total_harga' => $totalHarga]);

            Transaksi::create([
                'pesanan_id' => $pesanan->id,
                'metode_bayar' => $metodeBayar,
                'total_bayar' => $totalHarga,
                'status_bayar' => StatusBayar::Pending,
            ]);

            $pesanan->load(['meja', 'details', 'transaksi']);

            return $pesanan;
        });

        if ($metodeBayar === MetodeBayar::Qris) {
            try {
                $this->createMidtransCharge($pesanan, $items);
            } catch (\Exception $e) {
                logger()->warning('Midtrans QRIS charge failed: '.$e->getMessage());
            }
        }

        Cache::flush();

        $pesanan->load(['meja', 'details.menu', 'transaksi']);

        return $pesanan;
    }

    protected function createMidtransCharge(Pesanan $pesanan, array $items): void
    {
        $midtransService = app(MidtransService::class);

        $itemDetails = [];
        foreach ($items as $item) {
            $menu = Menu::findOrFail($item['menu_id']);
            $itemDetails[] = [
                'id' => (string) $menu->id,
                'price' => (int) $menu->harga,
                'quantity' => $item['jumlah'],
                'name' => $menu->nama,
            ];
        }

        $midtransOrderId = 'pesanan-'.$pesanan->id;
        $grossAmount = (int) $pesanan->total_harga;

        $response = $midtransService->createQrisCharge(
            $midtransOrderId,
            $grossAmount,
            $itemDetails,
            [
                'first_name' => 'Meja '.$pesanan->meja->nomor,
                'phone' => '',
            ]
        );

        $transaksi = $pesanan->transaksi;
        $transaksi->update([
            'midtrans_transaction_id' => $midtransService->getTransactionId($response),
            'midtrans_order_id' => $midtransOrderId,
            'qr_code_url' => $midtransService->getQrCodeUrl($response),
            'qr_string' => $midtransService->getQrString($response),
        ]);
    }
}
