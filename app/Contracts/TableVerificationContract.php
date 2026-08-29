<?php

namespace App\Contracts;

use App\Models\Meja;

interface TableVerificationContract
{
    public function verifyTable(Meja $meja): bool;

    public function getTableToken(): ?string;

    public function getAssignedMejaId(): ?int;

    public function initializeTableVerification(Meja $meja): void;
}
