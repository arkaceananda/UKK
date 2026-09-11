<?php

namespace App\Livewire\Admin;

use App\Enums\StatusMenu;
use App\Models\KategoriMenu;
use App\Models\Menu;
use App\Services\ImageCacheService;
use App\Services\RestockService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

class MenuManager extends Component
{
    use WithPagination;

    public string $searchMenu = '';

    public string $filterKategori = '';

    public bool $filterRestockOnly = false;

    public bool $showMenuModal = false;

    public ?int $editingMenuId = null;

    #[Validate('required|string|min:2|max:255')]
    public string $namaMenu = '';

    #[Validate('required|numeric|min:0')]
    public string $hargaMenu = '';

    #[Validate('required|exists:kategori_menu,id')]
    public ?int $kategoriMenuId = null;

    public string $deskripsiMenu = '';

    public int $stokMenu = 50;

    public string $statusMenu = 'Tersedia';

    public bool $enableOptions = false;

    public ?string $fotoMenuCropped = null;

    public bool $removeFoto = false;

    public array $menusAll = [];

    public int $menuPage = 1;

    public bool $hasMoreMenus = true;

    public bool $loadingMore = false;

    public int $menuPerPage = 12;

    public int $totalMenus = 0;

    protected $listeners = ['menuCreated' => 'resetMenus', 'menuDeleted' => 'resetMenus'];

    #[Computed]
    public function restockBadgeCount(): int
    {
        return RestockService::countPending();
    }

    #[Computed]
    public function habisCount(): int
    {
        return RestockService::habisCount();
    }

    #[On('echo:admin-channel,RestockRequested')]
    public function onRestockRequested(): void
    {
        $this->resetMenus();
        $this->dispatch('notify', message: 'Kasir minta restock menu habis!', type: 'info');
    }

    public function clearRestockFlag(int $menuId): void
    {
        RestockService::clear($menuId);
        $this->dispatch('notify', message: 'Flag restock dibersihkan.', type: 'success');
    }

    public function openCreateMenuModal(): void
    {
        $this->resetValidation();
        $this->editingMenuId = null;
        $this->namaMenu = '';
        $this->hargaMenu = '';
        $this->kategoriMenuId = KategoriMenu::first()?->id;
        $this->deskripsiMenu = '';
        $this->stokMenu = 50;
        $this->statusMenu = StatusMenu::Tersedia->value;
        $this->enableOptions = false;
        $this->fotoMenuCropped = null;
        $this->removeFoto = false;
        $this->showMenuModal = true;
    }

    public function openEditMenuModal(int $menuId): void
    {
        $this->resetValidation();
        $menu = Menu::findOrFail($menuId);
        $this->editingMenuId = $menu->id;
        $this->namaMenu = $menu->nama;
        $this->hargaMenu = (string) $menu->harga;
        $this->kategoriMenuId = $menu->kategori_id;
        $this->deskripsiMenu = $menu->deskripsi ?? '';
        $this->stokMenu = $menu->stok;
        $this->statusMenu = $menu->status->value;
        $this->enableOptions = ! empty($menu->options);
        $this->fotoMenuCropped = null;
        $this->removeFoto = false;
        $this->showMenuModal = true;
    }

    public function saveMenu(): void
    {
        $this->validate([
            'namaMenu' => 'required|string|min:2|max:255',
            'hargaMenu' => 'required|numeric|min:0',
            'kategoriMenuId' => 'required|exists:kategori_menu,id',
            'stokMenu' => 'required|integer|min:0',
        ]);

        $status = StatusMenu::tryFrom($this->statusMenu) ?? StatusMenu::Tersedia;

        $data = [
            'nama' => $this->namaMenu,
            'harga' => (float) $this->hargaMenu,
            'kategori_id' => $this->kategoriMenuId,
            'deskripsi' => $this->deskripsiMenu,
            'stok' => $this->stokMenu,
            'status' => $status,
            'options' => $this->enableOptions ? ['panas', 'dingin'] : null,
        ];

        $oldFoto = $this->editingMenuId ? (Menu::find($this->editingMenuId)?->foto) : null;

        if ($this->fotoMenuCropped && str_starts_with($this->fotoMenuCropped, 'data:image')) {
            $imageData = base64_decode(substr($this->fotoMenuCropped, strpos($this->fotoMenuCropped, ',') + 1), true);

            if ($imageData !== false) {
                $filename = 'menu-'.time().'-'.uniqid().'.webp';
                $path = 'menu-photos/'.$filename;
                Storage::disk('public')->put($path, $imageData);
                $data['foto'] = $path;

                if ($oldFoto && $oldFoto !== $path) {
                    Storage::disk('public')->delete($oldFoto);
                    app(ImageCacheService::class)->invalidateImageCache($oldFoto);
                }
            }
        } elseif ($this->removeFoto) {
            if ($oldFoto) {
                Storage::disk('public')->delete($oldFoto);
                app(ImageCacheService::class)->invalidateImageCache($oldFoto);
            }
            $data['foto'] = null;
        }

        if ($this->editingMenuId) {
            $wasRequested = RestockService::isRequested($this->editingMenuId);
            $menu = Menu::findOrFail($this->editingMenuId);
            // auto-fix: jika stok diisi >0, paksa status jadi Tersedia
            if ($data['stok'] > 0 && $status === StatusMenu::Habis) {
                $status = StatusMenu::Tersedia;
                $data['status'] = $status;
            }
            $menu->update($data);
            $fresh = $menu->fresh();
            if ($wasRequested || $fresh->status === StatusMenu::Tersedia || $fresh->stok > 0) {
                RestockService::clear($fresh->id);
            }
            if ($wasRequested) {
                $this->dispatch('notify', message: 'Restock "'.$fresh->nama.'" selesai — flag kasir dibersihkan.', type: 'success');
            } else {
                $this->dispatch('notify', message: 'Menu berhasil diperbarui!', type: 'success');
            }
        } else {
            Menu::create($data);
            $this->dispatch('notify', message: 'Menu baru berhasil ditambahkan!', type: 'success');
        }

        $this->reset(['namaMenu', 'hargaMenu', 'kategoriMenuId', 'deskripsiMenu', 'stokMenu', 'fotoMenuCropped', 'removeFoto']);
        $this->showMenuModal = false;
        $this->resetMenus();
        $this->dispatch('menuUpdated');
    }

    public function deleteMenu(int $menuId): void
    {
        $menu = Menu::findOrFail($menuId);

        if ($menu->foto) {
            app(ImageCacheService::class)->invalidateImageCache($menu->foto);
        }

        $menu->delete();
        $this->dispatch('notify', message: 'Menu berhasil dihapus.', type: 'info');
        $this->dispatch('menuUpdated');
        $this->resetMenus();
    }

    public function mount(): void
    {
        $this->resetMenus();
    }

    public function updatedSearchMenu(): void
    {
        $this->resetMenus();
    }

    public function updatedFilterKategori(): void
    {
        $this->resetMenus();
    }

    public function toggleRestockFilter(): void
    {
        $this->filterRestockOnly = ! $this->filterRestockOnly;
        $this->resetMenus();
    }

    #[Computed]
    public function pendingRestockMenus(): Collection
    {
        return Menu::where('status', StatusMenu::Habis->value)
            ->with('kategori')
            ->get()
            ->filter(fn (Menu $m) => RestockService::isRequested($m->id))
            ->map(fn (Menu $m) => [
                'id' => $m->id,
                'nama' => $m->nama,
                'kategori' => $m->kategori?->nama,
                'by' => RestockService::get($m->id)['kasir_name'] ?? 'Kasir',
            ])
            ->values();
    }

    protected function loadMenuPage(): void
    {
        $query = Menu::with('kategori');

        if ($this->searchMenu !== '') {
            $query->where('nama', 'ilike', '%'.$this->searchMenu.'%');
        }

        if ($this->filterKategori !== '') {
            $query->where('kategori_id', $this->filterKategori);
        }

        if ($this->filterRestockOnly) {
            $ids = Menu::where('status', StatusMenu::Habis->value)
                ->get()
                ->filter(fn (Menu $m) => RestockService::isRequested($m->id))
                ->pluck('id')->all();
            $query->whereIn('id', $ids ?: [-1]);
        }

        $this->totalMenus = (clone $query)->count();

        $pageItems = $query->orderBy('nama')
            ->forPage($this->menuPage, $this->menuPerPage)
            ->get()
            ->sortByDesc(fn (Menu $m) => RestockService::isRequested($m->id) ? 1 : 0)
            ->values();

        $mapped = $pageItems->map(fn (Menu $menu) => [
            'id' => $menu->id,
            'nama' => $menu->nama,
            'deskripsi' => $menu->deskripsi,
            'harga' => (float) $menu->harga,
            'foto' => $menu->foto,
            'stok' => $menu->stok,
            'status' => $menu->status->value,
            'kategori' => $menu->kategori?->nama,
            'restockRequested' => RestockService::isRequested($menu->id),
            'restockBy' => RestockService::get($menu->id)['kasir_name'] ?? null,
        ])->all();

        if ($this->menuPage === 1) {
            $this->menusAll = $mapped;
        } else {
            $this->menusAll = array_merge($this->menusAll, $mapped);
        }

        $this->hasMoreMenus = $pageItems->count() >= $this->menuPerPage;
    }

    public function resetMenus(): void
    {
        $this->menuPage = 1;
        $this->hasMoreMenus = true;
        $this->loadingMore = false;
        $this->loadMenuPage();
    }

    public function loadMoreMenus(): void
    {
        if ($this->loadingMore || ! $this->hasMoreMenus) {
            return;
        }

        $this->loadingMore = true;
        $this->menuPage++;
        $this->loadMenuPage();
        $this->loadingMore = false;
    }

    public function render()
    {
        return view('livewire.admin.menu-manager', [
            'menus' => collect($this->menusAll),
            'kategoriList' => KategoriMenu::orderBy('nama')->get(),
        ]);
    }
}
