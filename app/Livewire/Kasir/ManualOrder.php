<?php

namespace App\Livewire\Kasir;

use App\Enums\MetodeBayar;
use App\Events\OrderPlaced;
use App\Models\Meja;
use App\Models\Menu;
use App\Services\OrderService;
use Livewire\Component;

class ManualOrder extends Component
{
    public $mejaList = [];

    public $menuItems = [];

    public $selectedMeja = '';

    public $orderItems = [];

    public $catatan = '';

    public $totalHarga = 0;

    public function mount()
    {
        $this->mejaList = Meja::where('status', 'Aktif')->get();
        $this->menuItems = Menu::where('status', 'Tersedia')->with('kategori')->get();
        $this->orderItems[] = ['menu_id' => '', 'jumlah' => 1, 'harga' => 0];
        $this->calculateTotal();
    }

    public function addOrderItem()
    {
        $this->orderItems[] = ['menu_id' => '', 'jumlah' => 1, 'harga' => 0];
        $this->calculateTotal();
    }

    public function removeOrderItem($index)
    {
        if (count($this->orderItems) > 1) {
            unset($this->orderItems[$index]);
            $this->orderItems = array_values($this->orderItems);
            $this->calculateTotal();
        }
    }

    public function updated($property, $value)
    {
        if (preg_match('/orderItems\.(\d+)\.menu_id/', $property, $matches)) {
            $index = (int) $matches[1];
            if ($value) { 
                $menu = Menu::find($value);
                if ($menu) {
                    $this->orderItems[$index]['harga'] = $menu->harga;
                } else {
                    $this->orderItems[$index]['harga'] = 0; // Menu tidak ditemukan
                }
            } else {
                $this->orderItems[$index]['harga'] = 0; // Menu tidak dipilih
            }
            $this->calculateTotal();
        }

        // Tangani perubahan pada orderItems.INDEX.jumlah
        if (preg_match('/orderItems\.(\d+)\.jumlah/', $property, $matches)) {
            $index = (int) $matches[1];
            // Pastikan kuantitas minimal 1
            if ($this->orderItems[$index]['jumlah'] < 1) {
                $this->orderItems[$index]['jumlah'] = 1;
            }
            $this->calculateTotal();
        }
        // Perubahan pada selectedMeja akan divalidasi saat createOrder, tidak perlu aksi khusus di sini
    }

    public function calculateTotal()
    {
        $this->totalHarga = 0;
        foreach ($this->orderItems as $item) {
            if (isset($item['menu_id']) && $item['menu_id'] && isset($item['jumlah']) && $item['jumlah'] > 0) {
                $this->totalHarga += $item['harga'] * $item['jumlah'];
            }
        }
    }

    public function createOrder()
    {
        $this->validate([
            'selectedMeja' => 'required|exists:meja,id',
            'orderItems.*.menu_id' => 'required|exists:menu,id',
            'orderItems.*.jumlah' => 'required|integer|min:1',
        ], [
            'selectedMeja.required' => 'Pilih meja terlebih dahulu',
            'orderItems.*.menu_id.required' => 'Pilih menu untuk setiap item',
            'orderItems.*.jumlah.min' => 'Jumlah minimal 1',
        ]);

        $orderDetails = [];
        foreach ($this->orderItems as $item) {
            if ($item['menu_id']) {
                $orderDetails[] = [
                    'menu_id' => $item['menu_id'],
                    'jumlah' => $item['jumlah'],
                    'harga_satuan' => $item['harga'],
                ];
            }
        }

        if (empty($orderDetails)) {
            $this->addError('orderItems', 'Tambahkan minimal satu item pesanan');

            return;
        }

        try {
            $meja = Meja::findOrFail($this->selectedMeja);
            $orderService = app(OrderService::class);
            $order = $orderService->checkout($meja, $orderDetails, MetodeBayar::Tunai, $this->catatan, auth()->id());

            event(new OrderPlaced($order->fresh()));

            $this->dispatch('order-created');
            $this->reset(['selectedMeja', 'orderItems', 'catatan', 'totalHarga']);
            $this->orderItems[] = ['menu_id' => '', 'jumlah' => 1, 'harga' => 0]; // Reset ke 1 item kosong
            $this->calculateTotal(); // Hitung ulang total setelah reset

            $this->dispatch('success', message: 'Pesanan manual berhasil dibuat');
        } catch (\Exception $e) {
            $this->dispatch('error', message: 'Gagal membuat pesanan: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.kasir.manual-order');
    }
}
