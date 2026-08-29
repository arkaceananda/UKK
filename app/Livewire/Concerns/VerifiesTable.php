<?php

namespace App\Livewire\Concerns;

use App\Contracts\TableVerificationContract;
use App\Models\Meja;

trait VerifiesTable
{
    protected TableVerificationContract $tableVerification;

    public ?string $tableToken = null;

    public bool $verified = false;

    public string $selectedMejaId = '';

    public function bootVerifiesTable(): void
    {
        $this->tableVerification = app(TableVerificationContract::class);

        if (! empty($this->mejaId)) {
            $meja = Meja::find($this->mejaId);
            if ($meja) {
                $this->tableVerification->initializeTableVerification($meja);
                $this->verified = $this->tableVerification->isVerified();
                $this->tableToken = $this->tableVerification->getTableToken();
            }
        }
    }

    public function initializeTable(Meja $meja): void
    {
        $this->selectedMejaId = (string) $meja->id;
        $this->tableVerification->initializeTableVerification($meja);

        $this->tableToken = $this->tableVerification->getTableToken();
        $this->verified = $this->tableVerification->isVerified();
    }

    public function isTableVerified(): bool
    {
        return $this->verified;
    }

    public function getTableTokenProperty(): ?string
    {
        return $this->tableToken;
    }
}
