<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use Carbon\CarbonImmutable;
use Database\Factories\StudyPaymentFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $study_id
 * @property int $user_id
 * @property string|null $reference_number
 * @property string $amount
 * @property string $currency
 * @property PaymentStatus $status
 * @property int|null $approved_by
 * @property CarbonImmutable|null $approved_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Study $study
 * @property-read User $user
 * @property-read User|null $approver
 * @property-read Collection<int, PaymentReceipt> $receipts
 * @property-read PaymentReceipt|null $latestReceipt
 */
class StudyPayment extends Model
{
    /** @use HasFactory<StudyPaymentFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'status' => PaymentStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Study, $this>
     */
    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Every receipt submission, newest first.
     *
     * @return HasMany<PaymentReceipt, $this>
     */
    public function receipts(): HasMany
    {
        return $this->hasMany(PaymentReceipt::class)->latest('submitted_at')->latest('id');
    }

    /**
     * @return HasOne<PaymentReceipt, $this>
     */
    public function latestReceipt(): HasOne
    {
        return $this->hasOne(PaymentReceipt::class)->latestOfMany();
    }

    /**
     * Determine whether a customer may still submit a receipt.
     */
    public function acceptsReceipts(): bool
    {
        return $this->status !== PaymentStatus::Approved;
    }
}
