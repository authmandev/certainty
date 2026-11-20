<?php

namespace App\Models;

use App\Enums\AuditAction;
use Carbon\CarbonImmutable;
use Database\Factories\StudyAuditLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One entry in a study's action history (BRD FR-042). Entries are written
 * once and never updated. A null actor means the system acted, such as a
 * queued report job.
 *
 * @property int $id
 * @property int $study_id
 * @property int|null $actor_id
 * @property AuditAction $action
 * @property array<string, mixed>|null $old_values
 * @property array<string, mixed>|null $new_values
 * @property string|null $reason
 * @property CarbonImmutable|null $created_at
 * @property-read Study $study
 * @property-read User|null $actor
 */
class StudyAuditLog extends Model
{
    /** @use HasFactory<StudyAuditLogFactory> */
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
            'action' => AuditAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    /**
     * Record an action. Call it inside the transaction that makes the change,
     * so the history and the data never disagree.
     *
     * @param  array<string, mixed>|null  $old
     * @param  array<string, mixed>|null  $new
     */
    public static function record(Study|int $study, ?User $actor, AuditAction $action, ?array $old = null, ?array $new = null, ?string $reason = null): self
    {
        $entry = new self;
        $entry->forceFill([
            'study_id' => $study instanceof Study ? $study->id : $study,
            'actor_id' => $actor?->id,
            'action' => $action,
            'old_values' => $old,
            'new_values' => $new,
            'reason' => $reason,
        ])->save();

        return $entry;
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
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
