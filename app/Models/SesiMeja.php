<?php

namespace App\Models;

use App\Enums\StatusSesiMeja;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SesiMeja extends Model
{
    protected $table = 'sesi_meja';

    protected $fillable = ['meja_id', 'user_id', 'token', 'status', 'started_at', 'ended_at'];

    protected function casts(): array
    {
        return [
            'status' => StatusSesiMeja::class,
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'meja_id' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function meja(): BelongsTo
    {
        return $this->belongsTo(Meja::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', StatusSesiMeja::Aktif);
    }
}
