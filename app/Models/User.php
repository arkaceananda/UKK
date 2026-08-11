<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected function role(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value instanceof UserRole ? $value : (UserRole::tryFromValue($value) ?? UserRole::Kasir),
            set: fn ($value) => $value instanceof UserRole ? $value->value : ucfirst(strtolower((string) $value)),
        );
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isKasir(): bool
    {
        return $this->role === UserRole::Kasir;
    }

    public function pesananDikonfirmasi(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'kasir_id');
    }
}
