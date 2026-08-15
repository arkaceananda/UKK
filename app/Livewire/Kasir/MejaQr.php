<?php

namespace App\Livewire\Kasir;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Livewire\Attributes\On;
use Livewire\Component;

class MejaQr extends Component
{
    public array $mejas = [];

    public ?int $updatedMejaId = null;

    public function mount(): void
    {
        $this->loadMejas();
    }

    public function loadMejas(): void
    {
        $this->mejas = Meja::query()
            ->where('status', StatusMeja::Aktif)
            ->orderBy('nomor')
            ->get()
            ->map(fn (Meja $meja) => [
                'id' => $meja->id,
                'nomor' => $meja->nomor,
                'is_occupied' => (bool) $meja->is_occupied,
            ])
            ->values()
            ->all();
    }

    public function releaseMeja(int $mejaId): void
    {
        $meja = Meja::findOrFail($mejaId);
        $meja->resetSesi();

        $this->dispatch('notify', message: 'Meja #'.$meja->nomor.' berhasil di-release.', type: 'success');
        $this->loadMejas();
    }

    #[On('echo:kasir-channel,TableStatusUpdated')]
    public function onTableUpdated(array $payload): void
    {
        $this->updatedMejaId = (int) ($payload['meja_id'] ?? 0);
        $this->loadMejas();
    }

    public function render()
    {
        return view('livewire.kasir.meja-qr');
    }
}
