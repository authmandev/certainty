<?php

namespace App\Services\Studies;

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Services\Calculation\FeasibilityCalculationService;

/**
 * The conditions a study must meet before it can be finalized (BRD FR-016).
 * Ownership is checked by StudyPolicy, and finalizing twice is handled by
 * FinalizeStudyAction, so neither appears here.
 *
 * @phpstan-type Check array{key: string, label: string, passed: bool, detail: string|null}
 */
class FinalizationReadiness
{
    public function __construct(
        private CalculateStudyProgressService $progress,
        private FeasibilityCalculationService $calculator,
    ) {}

    /**
     * @return list<Check>
     */
    public function checks(Study $study): array
    {
        $study->loadMissing(['input', 'payment']);
        $input = StudyInputSchema::normalize($study->input->input_data ?? []);
        $progress = $this->progress->calculate($study, $input);
        $incomplete = array_values(array_filter($progress->sections, fn (array $section): bool => ! $section['is_complete']));
        $isComplete = $incomplete === [];

        return [
            [
                'key' => 'not_expired',
                'label' => 'الدراسة ضمن مدة الإكمال',
                'passed' => $study->currentStatus() !== StudyStatus::Expired,
                'detail' => $study->currentStatus() === StudyStatus::Expired ? 'انتهت مدة إكمال الدراسة. تواصل معنا لطلب تمديدها.' : null,
            ],
            [
                'key' => 'complete',
                'label' => 'اكتمال جميع بيانات الدراسة',
                'passed' => $isComplete,
                'detail' => $isComplete ? null : 'أكمل: '.implode('، ', array_column($incomplete, 'title')).'.',
            ],
            [
                'key' => 'calculable',
                'label' => 'إمكانية حساب النتائج المالية',
                'passed' => $calculable = $isComplete && $this->calculator->preview($study) !== null,
                'detail' => $isComplete && ! $calculable ? 'تعذّر حساب النتائج بالقيم الحالية. راجع القيم الكبيرة جدًا أو غير المنطقية.' : null,
            ],
            [
                'key' => 'payment_approved',
                'label' => 'اعتماد دفع رسوم الدراسة',
                'passed' => $study->payment?->status === PaymentStatus::Approved,
                'detail' => $study->payment?->status === PaymentStatus::Approved ? null : 'يُعتمد الدفع بعد مراجعة إيصال التحويل البنكي.',
            ],
        ];
    }

    /**
     * Determine whether every check passes.
     *
     * @param  list<Check>  $checks
     */
    public function passes(array $checks): bool
    {
        return array_all($checks, fn (array $check): bool => $check['passed']);
    }
}
