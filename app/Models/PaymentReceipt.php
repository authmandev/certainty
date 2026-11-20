<?php

namespace App\Models;

use App\Enums\ReceiptStatus;
use Carbon\CarbonImmutable;
use Database\Factories\PaymentReceiptFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A bank-transfer receipt a customer submitted for a payment. Files live on a
 * private disk and are only served through PaymentReceiptController.
 *
 * @property int $id
 * @property int $study_payment_id
 * @property int $uploaded_by
 * @property string $disk
 * @property string $file_path
 * @property string $file_name
 * @property string $mime_type
 * @property int $file_size
 * @property ReceiptStatus $status
 * @property CarbonImmutable $submitted_at
 * @property int|null $reviewed_by
 * @property CarbonImmutable|null $reviewed_at
 * @property string|null $rejection_reason
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read StudyPayment $payment
 * @property-read User $uploader
 * @property-read User|null $reviewer
 */
class PaymentReceipt extends Model
{
    /** @use HasFactory<PaymentReceiptFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => ReceiptStatus::class,
            'file_size' => 'integer',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<StudyPayment, $this>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(StudyPayment::class, 'study_payment_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
