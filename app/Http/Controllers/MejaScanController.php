<?php

namespace App\Http\Controllers;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MejaScanController extends Controller
{
    public function show(string $token, Request $request): RedirectResponse
    {
        $meja = Meja::where('token', $token)->first();

        if (! $meja) {
            return redirect('/menu')->with('error', 'QR code tidak valid.');
        }

        if ($meja->status !== StatusMeja::Aktif) {
            return redirect('/menu')->with('error', 'Meja tidak tersedia.');
        }

        if ($meja->is_occupied) {
            return redirect()->route('customer.menu', ['meja' => $meja->id]);
        }

        $meja->update(['is_occupied' => true]);

        return redirect()->route('customer.menu', ['meja' => $meja->id]);
    }
}
