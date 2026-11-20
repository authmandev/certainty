<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\StudyExtensionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An administrator's extension of a draft's deadline (BRD FR-039).
 *
 * @property int $id
 * @property int $study_id
 * @property CarbonImmutable $previous_expires_at
 * @property int $extension_days
 * @property CarbonImmutable $new_expires_at
 * @property string $reason
 * @property int $approved_by
 * @property CarbonImmutable|null $created_at
 * @property-read Study $study
 * @property-read User $approver
 */
class StudyExtension extends Model
{
    /** @use HasFactory<StudyExtensionFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'previous_expires_at' => 'datetime',
            'new_expires_at' => 'datetime',
            'extension_days' => 'integer',
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
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
