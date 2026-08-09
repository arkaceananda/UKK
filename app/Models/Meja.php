<?php

namespace App\Models;

use App\Enums\StatusMeja;
use Database\Factories\MejaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Meja extends Model
{
    /** @use HasFactory<MejaFactory> */
    use HasFactory;

    protected $table = 'meja';

    protected $fillable = ['nomor', 'token', 'status', 'is_occupied'];

    protected function casts(): array
    {
        return [
            'status' => StatusMeja::class,
            'is_occupied' => 'boolean',
        ];
    }

    public static function booted(): void
    {
        static::creating(function (Meja $meja) {
            if (empty($meja->token)) {
                $meja->token = Str::random(64);
            }
        });
    }

    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class);
    }

    public function generateNewToken(): string
    {
        $this->token = Str::random(64);
        $this->save();

        return $this->token;
    }
}
