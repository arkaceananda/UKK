<?php

namespace App\Http\Controllers;

use App\Enums\StatusSesiMeja;
use App\Models\SesiMeja;
use App\Services\QrCodeService;
use App\Services\TableService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class TableAssignmentController extends Controller
{
    public function qr(string $token): Response
    {
        $svg = app(QrCodeService::class)->svgForToken($token);

        return response($svg)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=31536000, immutable');
    }

    public function assign(string $token): RedirectResponse
    {
        $meja = (new TableService)->findByToken($token);

        if (! $meja) {
            return redirect()->route('customer.scan-required')
                ->with('error', 'QR code tidak valid atau meja sedang nonaktif.');
        }

        session(['assigned_meja_id' => $meja->id, 'assigned_meja_token' => $meja->token]);

        $active = SesiMeja::where('meja_id', $meja->id)
            ->where('status', StatusSesiMeja::Aktif)
            ->latest('id')
            ->first();

        if (! $active) {
            SesiMeja::create([
                'meja_id' => $meja->id,
                'token' => $meja->token,
                'status' => StatusSesiMeja::Aktif,
                'started_at' => now(),
            ]);
        }

        return redirect()->route('customer.menu', ['meja' => $meja->id]);
    }
}
