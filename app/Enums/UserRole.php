<?php

namespace App\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Admin = 'admin';

    /**
     * Get the Arabic label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Customer => 'عميل',
            self::Admin => 'مدير',
        };
    }
}
