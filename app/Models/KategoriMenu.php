<?php

namespace App\Models;

use Database\Factories\KategoriMenuFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriMenu extends Model
{
    /** @use HasFactory<KategoriMenuFactory> */
    use HasFactory;

    protected $table = 'kategori_menu';

    protected $fillable = ['nama'];

    public function menu(): HasMany
    {
        return $this->hasMany(Menu::class, 'kategori_id');
    }
}
