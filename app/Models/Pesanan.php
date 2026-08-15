<?php

namespace App\Models;

use App\Enums\StatusPesanan;
use App\Jobs\RegenerateTableToken;
use Database\Factories\PesananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pesanan extends Model
{
    /** @use HasFactory<PesananFactory> */
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = ['meja_id', 'kasir_id', 'status', 'catatan', 'total_harga'];

    protected function casts(): array
    {
        return [
            'status' => StatusPesanan::class,
            'total_harga' => 'decimal:2',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class);
    }

    public function kasir(): BelongsTo
    {
        return $this->belongsTo(User::class, 'kasir_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailPesanan::class, 'pesanan_id')->with(['menu']);
    }

    public function transaksi(): HasOne
    {
        return $this->hasOne(Transaksi::class);
    }

    public function transitionTo(StatusPesanan $nextStatus): void
    {
        if (! $this->status->canTransitionTo($nextStatus)) {
            throw new \DomainException(
                "Transisi dari '{$this->status->value}' ke '{$nextStatus->value}' tidak valid."
            );
        }

        $this->update(['status' => $nextStatus]);

        if ($nextStatus === StatusPesanan::Selesai && $this->meja) {
            RegenerateTableToken::dispatch($this->meja)
                ->delay(now()->addMinutes(5));
        }
    }
}
