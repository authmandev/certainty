<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Get the Arabic label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'بانتظار التحويل',
            self::UnderReview => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
        };
    }
}
