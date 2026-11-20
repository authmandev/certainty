<?php

namespace App\Actions\Payments;

use App\Enums\AuditAction;
use App\Enums\PaymentStatus;
use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyAuditLog;
use App\Models\StudyPayment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubmitPaymentReceiptAction
{
    /**
     * Store a receipt privately and put the payment under review. A receipt
     * still waiting for review is replaced; earlier decisions stay in the
     * history (BRD FR-019, FR-020).
     */
    public function handle(StudyPayment $payment, User $customer, UploadedFile $file): PaymentReceipt
    {
        $disk = (string) config('certainty.receipts.disk');
        $extension = $file->guessExtension() ?? $file->extension();
        $path = $file->storeAs("receipts/{$payment->id}", Str::uuid7().'.'.$extension, ['disk' => $disk]);

        if ($path === false) {
            throw ValidationException::withMessages(['receipt' => __('The receipt could not be uploaded. Please try again.')]);
        }

        try {
            return DB::transaction(function () use ($payment, $customer, $file, $disk, $path): PaymentReceipt {
                $payment = StudyPayment::query()->lockForUpdate()->findOrFail($payment->id);

                if (! $payment->acceptsReceipts()) {
                    throw ValidationException::withMessages(['receipt' => __('This payment has already been approved.')]);
                }

                PaymentReceipt::query()
                    ->whereBelongsTo($payment, 'payment')
                    ->where('status', ReceiptStatus::Submitted)
                    ->update(['status' => ReceiptStatus::Replaced]);

                $receipt = new PaymentReceipt;
                $receipt->forceFill([
                    'study_payment_id' => $payment->id,
                    'uploaded_by' => $customer->id,
                    'disk' => $disk,
                    'file_path' => $path,
                    'file_name' => Str::limit(basename($file->getClientOriginalName()), 200, ''),
                    'mime_type' => (string) $file->getMimeType(),
                    'file_size' => (int) $file->getSize(),
                    'status' => ReceiptStatus::Submitted,
                    'submitted_at' => now(),
                ])->save();

                $previousStatus = $payment->status;
                $payment->forceFill(['status' => PaymentStatus::UnderReview])->save();

                StudyAuditLog::record(
                    $payment->study_id,
                    $customer,
                    AuditAction::ReceiptSubmitted,
                    ['payment_status' => $previousStatus->value],
                    ['payment_status' => PaymentStatus::UnderReview->value, 'receipt_id' => $receipt->id],
                );

                return $receipt;
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }
    }
}
