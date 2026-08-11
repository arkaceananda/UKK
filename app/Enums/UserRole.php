<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'Admin';
    case Kasir = 'Kasir';

    public static function tryFromValue(?string $value): ?self
    {
        if (! $value) {
            return null;
        }

        return self::tryFrom(ucfirst(strtolower($value))) ?? self::tryFrom($value);
    }
}
