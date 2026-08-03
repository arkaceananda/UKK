<?php

namespace App\Livewire\Kasir;

use App\Models\Pesanan;
use Livewire\Component;
use Livewire\WithPagination;

class OrderHistory extends Component
{
    use WithPagination;

    public $search = '';

    public $dateFrom = '';

    public $dateTo = '';

    public $statusFilter = '';

    public function mount()
    {
        $this->dateFrom = now()->subDays(7)->format('Y-m-d');
        $this->dateTo = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Pesanan::with(['meja', 'kasir', 'details.menu'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('meja', function ($mejaQuery) {
                    $mejaQuery->where('nomor', 'like', '%'.$this->search.'%');
                })->orWhereHas('kasir', function ($kasirQuery) {
                    $kasirQuery->where('name', 'like', '%'.$this->search.'%');
                });
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $orders = $query->paginate(15);

        return view('livewire.kasir.order-history', [
            'orders' => $orders,
        ]);
    }
}
