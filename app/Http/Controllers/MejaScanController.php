<?php

namespace App\Http\Controllers;

use App\Enums\StatusMeja;
use App\Models\Meja;
use Illuminate\Http\RedirectResponse;

class MejaScanController extends Controller
{
    public function show(Meja $meja): RedirectResponse
    {
        if ($meja->status !== StatusMeja::Aktif) {
            return redirect('/menu')->with('error', 'Meja tidak tersedia.');
        }

        $meja->update(['is_occupied' => true]);
        session(['meja_token_'.$meja->id => $meja->fresh()->token]);

        return redirect()->route('customer.menu', ['meja' => $meja->id]);
    }
}
