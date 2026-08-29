<?php

namespace App\Services;

use App\Contracts\TableVerificationContract;
use App\Models\Meja;
use Illuminate\Support\Facades\Session;

class TableVerificationService implements TableVerificationContract
{
    private ?string $tableToken = null;

    private ?int $assignedMejaId = null;

    private bool $verified = false;

    public function verifyTable(Meja $meja): bool
    {
        $this->tableToken = Session::get('assigned_meja_token');
        $this->assignedMejaId = Session::get('assigned_meja_id');

        $this->verified = $this->assignedMejaId === $meja->id
            && $this->tableToken === $meja->token;

        return $this->verified;
    }

    public function getTableToken(): ?string
    {
        return $this->tableToken;
    }

    public function getAssignedMejaId(): ?int
    {
        return $this->assignedMejaId;
    }

    public function initializeTableVerification(Meja $meja): void
    {
        $this->verifyTable($meja);
    }

    public function isVerified(): bool
    {
        return $this->verified;
    }
}
