<?php

namespace App\Actions\Studies;

use App\Enums\ReferenceGroup;
use App\Enums\StudyStatus;
use App\Jobs\GenerateStudyReportPdf;
use App\Models\ReferenceOption;
use App\Models\Study;
use App\Models\StudyReport;
use App\Models\User;
use App\Services\Calculation\FeasibilityCalculationService;
use App\Services\Studies\FinalizationReadiness;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FinalizeStudyAction
{
    public function __construct(
        private FinalizationReadiness $readiness,
        private FeasibilityCalculationService $calculator,
    ) {}

    /**
     * Lock the study and store an immutable snapshot of its input and results
     * as the next report version (BRD FR-016, FR-036). The PDF is generated
     * by a queued job afterwards.
     *
     * Finalizing an already finalized study returns its current report, so a
     * repeated request never creates a second version.
     */
    public function handle(Study $study, User $customer): StudyReport
    {
        return DB::transaction(function () use ($study, $customer): StudyReport {
            $study = Study::query()->lockForUpdate()->findOrFail($study->id);

            if ($study->status === StudyStatus::Finalized) {
                return $study->latestReport()->firstOrFail();
            }

            $checks = $this->readiness->checks($study);

            if (! $this->readiness->passes($checks)) {
                $failed = array_filter($checks, fn (array $check): bool => ! $check['passed']);

                throw ValidationException::withMessages([
                    'finalize' => array_values(array_map(fn (array $check): string => $check['detail'] ?? $check['label'], $failed)),
                ]);
            }

            $input = StudyInputSchema::normalize($study->input->input_data ?? []);
            $version = $study->current_version + 1;

            $report = new StudyReport;
            $report->forceFill([
                'study_id' => $study->id,
                'version' => $version,
                'input_snapshot' => [
                    'title' => $study->title,
                    'project_name' => $study->project_name,
                    'labels' => $this->labels($input),
                    ...$input,
                ],
                'calculation_snapshot' => $this->calculator->calculate($study),
                'calculation_version' => $study->calculation_version,
                'finalized_by' => $customer->id,
                'disk' => (string) config('certainty.reports.disk'),
            ])->save();

            $study->forceFill([
                'status' => StudyStatus::Finalized,
                'finalized_at' => now(),
                'current_version' => $version,
            ])->save();

            GenerateStudyReportPdf::dispatch($report)->afterCommit();

            return $report;
        });
    }

    /**
     * Freeze the dropdown labels the report shows, so later edits to the
     * reference lists never change a finalized report.
     *
     * @param  array<string, mixed>  $input
     * @return array{sector: string|null, project_type: string|null}
     */
    private function labels(array $input): array
    {
        $label = fn (ReferenceGroup $group, mixed $code): ?string => is_string($code)
            ? ReferenceOption::query()->where('group', $group)->where('code', $code)->value('label_ar')
            : null;

        return [
            'sector' => $label(ReferenceGroup::Sector, $input['project']['sector_code'] ?? null),
            'project_type' => ($input['project']['project_type_other'] ?? null) ?: $label(ReferenceGroup::ProjectType, $input['project']['project_type_code'] ?? null),
        ];
    }
}
