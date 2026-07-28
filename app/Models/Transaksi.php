<?php

namespace App\Models;

use App\Enums\MetodeBayar;
use App\Enums\StatusBayar;
use Database\Factories\TransaksiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaksi extends Model
{
    /** @use HasFactory<TransaksiFactory> */
    use HasFactory;

    protected $table = 'transaksi';

    public $timestamps = false;

    protected $fillable = ['pesanan_id', 'metode_bayar', 'total_bayar', 'status_bayar'];

    protected function casts(): array
    {
        return [
            'metode_bayar' => MetodeBayar::class,
            'total_bayar' => 'decimal:2',
            'status_bayar' => StatusBayar::class,
            'created_at' => 'datetime',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }
}
