<?php

namespace App\Models;

use App\Enums\StatusMenu;
use Database\Factories\MenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    /** @use HasFactory<MenuFactory> */
    use HasFactory;

    protected $table = 'menu';

    protected $fillable = ['kategori_id', 'nama', 'deskripsi', 'harga', 'foto', 'stok', 'status'];

    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'stok' => 'integer',
            'status' => StatusMenu::class,
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriMenu::class, 'kategori_id');
    }

    public function detailPesanan(): HasMany
    {
        return $this->hasMany(DetailPesanan::class);
    }

    public function isAvailable(): bool
    {
        return $this->status === StatusMenu::Tersedia && $this->stok > 0;
    }

    public function reduceStock(int $quantity): void
    {
        if ($this->stok < $quantity) {
            throw new \DomainException("Stok '{$this->nama}' tidak mencukupi.");
        }

        $this->decrement('stok', $quantity);

        if ($this->stok === 0) {
            $this->update(['status' => StatusMenu::Habis]);
        }
    }
}
