<?php

namespace App\Livewire\Kasir;

use App\Enums\StatusMenu;
use App\Events\RestockRequested;
use App\Models\Menu;
use App\Services\RestockService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RestockAlert extends Component
{
    public function requestRestock(int $menuId): void
    {
        $menu = Menu::findOrFail($menuId);

        if ($menu->status !== StatusMenu::Habis) {
            $this->dispatch('notify', message: 'Menu masih tersedia, tidak perlu restock.', type: 'info');

            return;
        }

        if (RestockService::isRequested($menuId)) {
            $this->dispatch('notify', message: 'Permintaan restock untuk "'.$menu->nama.'" sudah dikirim ke owner.', type: 'info');

            return;
        }

        $kasir = Auth::user();
        RestockService::request($menuId, $kasir->id, $kasir->name);

        try {
            event(new RestockRequested($menu->fresh(), $kasir));
        } catch (\Throwable $e) {
            report($e);
        }

        $this->dispatch('notify', message: 'Permintaan restock "'.$menu->nama.'" terkirim ke owner.', type: 'success');
    }

    public function render()
    {
        $habisMenus = Menu::where('status', StatusMenu::Habis->value)
            ->with('kategori')
            ->orderBy('nama')
            ->get()
            ->map(fn (Menu $m) => [
                'id' => $m->id,
                'nama' => $m->nama,
                'kategori' => $m->kategori?->nama,
                'stok' => $m->stok,
                'requested' => RestockService::isRequested($m->id),
                'requested_by' => RestockService::get($m->id)['kasir_name'] ?? null,
            ]);

        return view('livewire.kasir.restock-alert', [
            'habisMenus' => $habisMenus,
            'pendingCount' => $habisMenus->where('requested', true)->count(),
        ]);
    }
}
