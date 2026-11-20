<?php

namespace App\Actions\Studies;

use App\Enums\AuditAction;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReopenStudyAction
{
    /**
     * Return a finalized study to draft so the customer can correct it (BRD
     * FR-040). Earlier report versions are kept; finalizing again creates the
     * next version. The payment stays approved (A-06).
     */
    public function handle(Study $study, User $admin, int $days, string $reason): Study
    {
        return DB::transaction(function () use ($study, $admin, $days, $reason): Study {
            $study = Study::query()->lockForUpdate()->findOrFail($study->id);

            if ($study->status !== StudyStatus::Finalized) {
                throw ValidationException::withMessages(['reason' => __('Only a finalized study can be reopened.')]);
            }

            $previous = [
                'status' => $study->status->value,
                'report_version' => $study->current_version,
                'finalized_at' => $study->finalized_at?->toIso8601String(),
                'expires_at' => $study->expires_at->toIso8601String(),
            ];

            $study->forceFill([
                'status' => StudyStatus::Draft,
                'finalized_at' => null,
                'expires_at' => now()->addDays($days),
            ])->save();

            StudyAuditLog::record(
                $study,
                $admin,
                AuditAction::StudyReopened,
                $previous,
                ['status' => StudyStatus::Draft->value, 'expires_at' => $study->expires_at->toIso8601String(), 'editing_days' => $days],
                $reason,
            );

            return $study;
        });
    }
}
