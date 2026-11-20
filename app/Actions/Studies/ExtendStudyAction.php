<?php

namespace App\Actions\Studies;

use App\Enums\AuditAction;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\StudyExtension;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExtendStudyAction
{
    /**
     * Give a draft more time (BRD FR-039). An expired draft gets the days
     * counted from now; a draft that has not expired gets them added to its
     * current deadline. Either way it becomes an editable draft again.
     */
    public function handle(Study $study, User $admin, int $days, string $reason): StudyExtension
    {
        return DB::transaction(function () use ($study, $admin, $days, $reason): StudyExtension {
            $study = Study::query()->lockForUpdate()->findOrFail($study->id);

            if ($study->status === StudyStatus::Finalized) {
                throw ValidationException::withMessages(['days' => __('A finalized study cannot be extended. Reopen it instead.')]);
            }

            $previousExpiresAt = $study->expires_at;
            $previousStatus = $study->currentStatus();
            $base = $previousExpiresAt->isFuture() ? $previousExpiresAt : now()->toImmutable();
            $newExpiresAt = $base->addDays($days);

            $study->forceFill(['status' => StudyStatus::Draft, 'expires_at' => $newExpiresAt])->save();

            $extension = new StudyExtension;
            $extension->forceFill([
                'study_id' => $study->id,
                'previous_expires_at' => $previousExpiresAt,
                'extension_days' => $days,
                'new_expires_at' => $newExpiresAt,
                'reason' => $reason,
                'approved_by' => $admin->id,
            ])->save();

            StudyAuditLog::record(
                $study,
                $admin,
                AuditAction::DraftExtended,
                ['status' => $previousStatus->value, 'expires_at' => $previousExpiresAt->toIso8601String()],
                ['status' => StudyStatus::Draft->value, 'expires_at' => $newExpiresAt->toIso8601String(), 'extension_days' => $days],
                $reason,
            );

            return $extension;
        });
    }
}
