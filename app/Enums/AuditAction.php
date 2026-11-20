<?php

namespace App\Enums;

/**
 * Critical actions recorded in the study audit history (BRD FR-042).
 */
enum AuditAction: string
{
    case ReceiptSubmitted = 'receipt_submitted';
    case PaymentApproved = 'payment_approved';
    case PaymentRejected = 'payment_rejected';
    case DraftExtended = 'draft_extended';
    case StudyFinalized = 'study_finalized';
    case StudyReopened = 'study_reopened';
    case ReportGenerated = 'report_generated';

    /**
     * Get the Arabic label shown in the interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::ReceiptSubmitted => 'رفع إيصال تحويل',
            self::PaymentApproved => 'قبول الدفع',
            self::PaymentRejected => 'رفض الدفع',
            self::DraftExtended => 'تمديد مدة المسودة',
            self::StudyFinalized => 'إنهاء الدراسة',
            self::StudyReopened => 'إعادة فتح الدراسة',
            self::ReportGenerated => 'إصدار ملف التقرير',
        };
    }
}
