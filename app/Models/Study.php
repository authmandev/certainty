<?php

namespace App\Models;

use App\Enums\StudyStatus;
use Carbon\CarbonImmutable;
use Database\Factories\StudyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $reference
 * @property string $title
 * @property string $project_name
 * @property StudyStatus $status
 * @property int $completion_percentage
 * @property CarbonImmutable $expires_at
 * @property CarbonImmutable|null $finalized_at
 * @property int $current_version
 * @property string $calculation_version
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read User $user
 * @property-read StudyPayment|null $payment
 * @property-read StudyInput|null $input
 */
#[Fillable(['title', 'project_name'])]
class Study extends Model
{
    /** @use HasFactory<StudyFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => StudyStatus::class,
            'expires_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasOne<StudyPayment, $this>
     */
    public function payment(): HasOne
    {
        return $this->hasOne(StudyPayment::class);
    }

    /**
     * @return HasOne<StudyInput, $this>
     */
    public function input(): HasOne
    {
        return $this->hasOne(StudyInput::class);
    }

    /**
     * Determine whether the customer may still edit the study: it is a draft and its deadline has not passed.
     */
    public function isEditable(): bool
    {
        return $this->currentStatus() === StudyStatus::Draft;
    }

    /**
     * Get the lifecycle status, treating a draft past its deadline as expired.
     */
    public function currentStatus(): StudyStatus
    {
        if ($this->status === StudyStatus::Draft && $this->expires_at->isPast()) {
            return StudyStatus::Expired;
        }

        return $this->status;
    }

    /**
     * Get the whole days left before the draft expires, or null when the study is no longer a draft.
     */
    public function daysRemaining(): ?int
    {
        if ($this->currentStatus() !== StudyStatus::Draft) {
            return null;
        }

        return (int) ceil(now()->diffInDays($this->expires_at));
    }
}
