<?php

namespace App\Enums;

/**
 * The review outcome of one receipt submission. A payment keeps every
 * submission, so the list doubles as its review history (BRD FR-021).
 */
enum ReceiptStatus: string
{
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Replaced = 'replaced';

    /**
     * Get the Arabic label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'قيد المراجعة',
            self::Approved => 'مقبول',
            self::Rejected => 'مرفوض',
            self::Replaced => 'استُبدل بإيصال أحدث',
        };
    }
}
