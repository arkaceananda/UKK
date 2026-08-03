<?php

namespace App\Livewire\Kasir;

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
        $this->orderItems[] = ['menu_id' => '', 'quantity' => 1, 'harga' => 0];
    }

    public function addOrderItem()
    {
        $this->orderItems[] = ['menu_id' => '', 'quantity' => 1, 'harga' => 0];
    }

    public function removeOrderItem($index)
    {
        if (count($this->orderItems) > 1) {
            unset($this->orderItems[$index]);
            $this->orderItems = array_values($this->orderItems);
            $this->calculateTotal();
        }
    }

    public function updatedOrderItems($value, $key)
    {
        if (str_contains($key, '.menu_id') && $value) {
            $index = explode('.', $key)[0];
            $menu = Menu::find($value);
            if ($menu) {
                $this->orderItems[$index]['harga'] = $menu->harga;
                $this->calculateTotal();
            }
        }

        if (str_contains($key, '.quantity')) {
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->totalHarga = 0;
        foreach ($this->orderItems as $item) {
            if ($item['menu_id'] && $item['quantity'] > 0) {
                $this->totalHarga += $item['harga'] * $item['quantity'];
            }
        }
    }

    public function createOrder()
    {
        $this->validate([
            'selectedMeja' => 'required|exists:meja,id',
            'orderItems.*.menu_id' => 'required|exists:menu,id',
            'orderItems.*.quantity' => 'required|integer|min:1',
        ], [
            'selectedMeja.required' => 'Pilih meja terlebih dahulu',
            'orderItems.*.menu_id.required' => 'Pilih menu untuk setiap item',
            'orderItems.*.quantity.min' => 'Jumlah minimal 1',
        ]);

        $orderDetails = [];
        foreach ($this->orderItems as $item) {
            if ($item['menu_id']) {
                $orderDetails[] = [
                    'menu_id' => $item['menu_id'],
                    'quantity' => $item['quantity'],
                    'harga' => $item['harga'],
                ];
            }
        }

        if (empty($orderDetails)) {
            $this->addError('orderItems', 'Tambahkan minimal satu item pesanan');

            return;
        }

        try {
            $orderService = app(OrderService::class);
            $order = $orderService->checkout($this->selectedMeja, $orderDetails, $this->catatan, auth()->id());

            event(new OrderPlaced($order->fresh()));

            $this->dispatch('order-created');
            $this->reset(['selectedMeja', 'orderItems', 'catatan', 'totalHarga']);
            $this->orderItems[] = ['menu_id' => '', 'quantity' => 1, 'harga' => 0];

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
