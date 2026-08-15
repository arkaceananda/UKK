<?php

namespace App\Http\Controllers;

use App\Services\TableService;
use Illuminate\Http\RedirectResponse;

class TableAssignmentController extends Controller
{
    public function assign(string $token): RedirectResponse
    {
        $meja = (new TableService)->findByToken($token);

        if (! $meja) {
            return redirect()->route('customer.scan-required')
                ->with('error', 'QR code tidak valid atau meja sedang nonaktif.');
        }

        session(['assigned_meja_id' => $meja->id, 'assigned_meja_token' => $meja->token]);

        return redirect()->route('customer.menu', ['meja' => $meja->id]);
    }
}
