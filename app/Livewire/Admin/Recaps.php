<?php

namespace App\Livewire\Admin;

use App\Models\Recap;
use Livewire\Component;
use Livewire\WithPagination;

class Recaps extends Component
{
    use WithPagination;

    public string $tab = 'daily';

    public function render()
    {
        $recaps = Recap::where('type', $this->tab)
            ->orderByDesc('period_start')
            ->paginate(10);

        return view('livewire.admin.recaps', [
            'recaps' => $recaps,
        ])->layout('layouts.admin', ['title' => 'Recap']);
    }
}
