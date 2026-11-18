<?php

namespace App\Enums;

enum StudyStatus: string
{
    case Draft = 'draft';
    case Expired = 'expired';
    case Finalized = 'finalized';

    /**
     * Get the Arabic label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Expired => 'منتهية الصلاحية',
            self::Finalized => 'مكتملة',
        };
    }
}
