<?php

namespace App\Actions\Payments;

use App\Enums\PaymentStatus;
use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewPaymentAction
{
    /**
     * Approve the receipt under review and the payment with it (BRD FR-021).
     */
    public function approve(StudyPayment $payment, User $admin): StudyPayment
    {
        return $this->review($payment, $admin, ReceiptStatus::Approved, null);
    }

    /**
     * Reject the receipt under review with a reason the customer will see.
     */
    public function reject(StudyPayment $payment, User $admin, string $reason): StudyPayment
    {
        return $this->review($payment, $admin, ReceiptStatus::Rejected, $reason);
    }

    private function review(StudyPayment $payment, User $admin, ReceiptStatus $decision, ?string $reason): StudyPayment
    {
        return DB::transaction(function () use ($payment, $admin, $decision, $reason): StudyPayment {
            $payment = StudyPayment::query()->lockForUpdate()->findOrFail($payment->id);
            $receipt = PaymentReceipt::query()
                ->whereBelongsTo($payment, 'payment')
                ->where('status', ReceiptStatus::Submitted)
                ->first();

            if ($payment->status !== PaymentStatus::UnderReview || $receipt === null) {
                throw ValidationException::withMessages(['payment' => __('Only a payment with a receipt under review can be reviewed.')]);
            }

            $receipt->forceFill([
                'status' => $decision,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $reason,
            ])->save();

            $payment->forceFill($decision === ReceiptStatus::Approved
                ? ['status' => PaymentStatus::Approved, 'approved_by' => $admin->id, 'approved_at' => now()]
                : ['status' => PaymentStatus::Rejected])->save();

            return $payment;
        });
    }
}
