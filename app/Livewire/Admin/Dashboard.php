<?php

namespace App\Livewire\Admin;

use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Dashboard extends Component
{
    // Kategori Modal State
    public bool $showKategoriModal = false;

    #[Validate('required|string|min:2|max:100|unique:kategori_menu,nama')]
    public string $namaKategori = '';

    public function openCreateKategoriModal(): void
    {
        $this->resetValidation();
        $this->namaKategori = '';
        $this->showKategoriModal = true;
    }

    public function openCreateMenuModal(): void
    {
        $this->dispatch('openCreateMenuModal')->to(MenuManager::class);
    }

    public function openCreateMejaModal(): void
    {
        $this->dispatch('openCreateMejaModal')->to(MejaManager::class);
    }

    public function saveKategori(): void
    {
        $this->validate([
            'namaKategori' => 'required|string|min:2|max:100|unique:kategori_menu,nama',
        ]);

        KategoriMenu::create([
            'nama' => $this->namaKategori,
        ]);

        $this->showKategoriModal = false;
        $this->namaKategori = '';
        $this->dispatch('notify', message: 'Kategori baru berhasil dibuat!', type: 'success');
        $this->dispatch('menuCreated')->to(MenuManager::class);
    }

    public function getLowStockMenusProperty()
    {
        $threshold = config('app.low_stock_threshold', 5);

        return Menu::where('stok', '>', 0)
            ->where('stok', '<=', $threshold)
            ->where('status', StatusMenu::Tersedia)
            ->orderBy('stok')
            ->get(['id', 'nama', 'stok']);
    }

    public function render()
    {
        return view('livewire.admin.dashboard', [
            'lowStockMenus' => $this->lowStockMenus,
        ])->layout('layouts.admin', ['title' => 'Admin Dashboard']);
    }
}
