<?php

namespace App\Console\Commands;

use App\Enums\StatusPesanan;
use App\Enums\StatusSesiMeja;
use App\Models\SesiMeja;
use App\Services\TableService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('sesi:cleanup {--hours=2 : Hours after which active sessions are considered abandoned}')]
#[Description('Clean up abandoned SesiMeja (older than specified hours with no active orders)')]
class SesiMejaCleanup extends Command
{
    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $threshold = now()->subHours($hours);

        $abandonedSessions = SesiMeja::where('status', StatusSesiMeja::Aktif)
            ->where('started_at', '<', $threshold)
            ->whereDoesntHave('pesanan', function ($query) {
                $query->whereIn('status', [
                    StatusPesanan::Menunggu,
                    StatusPesanan::Diterima,
                    StatusPesanan::Diproses,
                ]);
            })
            ->get();

        $count = 0;
        foreach ($abandonedSessions as $session) {
            $session->update([
                'status' => StatusSesiMeja::Selesai,
                'ended_at' => now(),
            ]);

            (new TableService)->refreshOccupancy($session->meja);
            $count++;

            Log::info('SesiMeja cleaned up', [
                'sesi_meja_id' => $session->id,
                'meja_id' => $session->meja_id,
            ]);
        }

        $this->info("Cleaned up {$count} abandoned sesi_meja (older than {$hours} hours)");

        return Command::SUCCESS;
    }
}
