<?php

namespace App\Models;

use Database\Factories\DetailPesananFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPesanan extends Model
{
    /** @use HasFactory<DetailPesananFactory> */
    use HasFactory;

    protected $table = 'detail_pesanan';

    public $timestamps = false;

    protected $fillable = ['pesanan_id', 'menu_id', 'jumlah', 'harga_satuan', 'subtotal'];

    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'harga_satuan' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}
