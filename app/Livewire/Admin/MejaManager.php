<?php

namespace App\Livewire\Admin;

use App\Enums\StatusMeja;
use App\Models\Meja;
use App\Services\QrCodeService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class MejaManager extends Component
{
    public bool $showMejaModal = false;

    public ?int $editingMejaId = null;

    #[Validate('required|string|max:50')]
    public string $nomorMeja = '';

    public string $statusMeja = 'Aktif';

    protected $listeners = [
        'openCreateMejaModal' => 'openCreateMejaModal',
        'echo:kasir-channel,TableStatusUpdated' => 'refreshMeja',
    ];

    public function openCreateMejaModal(): void
    {
        $this->resetValidation();
        $this->editingMejaId = null;
        $this->nomorMeja = '';
        $this->statusMeja = StatusMeja::Aktif->value;
        $this->showMejaModal = true;
    }

    public function openEditMejaModal(int $mejaId): void
    {
        $this->resetValidation();
        $meja = Meja::findOrFail($mejaId);
        $this->editingMejaId = $meja->id;
        $this->nomorMeja = $meja->nomor;
        $this->statusMeja = $meja->status->value;
        $this->showMejaModal = true;
    }

    public function saveMeja(): void
    {
        $rules = ['nomorMeja' => 'required|string|max:50|unique:meja,nomor'];
        if ($this->editingMejaId) {
            $rules['nomorMeja'] = 'required|string|max:50|unique:meja,nomor,'.$this->editingMejaId;
        }
        $this->validate($rules);

        $status = StatusMeja::tryFrom($this->statusMeja) ?? StatusMeja::Aktif;

        if ($this->editingMejaId) {
            $meja = Meja::findOrFail($this->editingMejaId);
            $meja->update([
                'nomor' => $this->nomorMeja,
                'status' => $status,
            ]);
            $this->dispatch('notify', message: 'Data meja berhasil diperbarui!', type: 'success');
        } else {
            Meja::create([
                'nomor' => $this->nomorMeja,
                'status' => $status,
            ]);
            $this->dispatch('notify', message: 'Meja baru berhasil dibuat!', type: 'success');
        }

        $this->showMejaModal = false;
    }

    public function deleteMeja(int $mejaId): void
    {
        $meja = Meja::findOrFail($mejaId);
        $meja->delete();
        $this->dispatch('notify', message: 'Meja berhasil dihapus.', type: 'info');
    }

    public function regenerateMejaToken(int $mejaId): void
    {
        $meja = Meja::findOrFail($mejaId);
        app(QrCodeService::class)->forget($meja->token);
        $meja->generateNewToken();
        $this->dispatch('notify', message: 'QR Code / Token meja #'.$meja->nomor.' berhasil diperbarui!', type: 'success');
    }

    public function toggleOccupied(int $mejaId): void
    {
        $meja = Meja::findOrFail($mejaId);
        $meja->update(['is_occupied' => ! $meja->is_occupied]);
        $this->dispatch('notify', message: 'Status meja #'.$meja->nomor.' diubah.', type: 'success');
    }

    public function refreshMeja(): void
    {
        //
    }

    public function render()
    {
        return view('livewire.admin.meja-manager', [
            'mejasList' => Meja::orderByRaw('CAST(nomor AS INTEGER)')->get(),
        ]);
    }
}
