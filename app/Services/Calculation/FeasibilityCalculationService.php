<?php

namespace App\Services\Calculation;

use App\Models\ReferenceOption;
use App\Models\Study;
use App\Services\Studies\StudyInputSchema;

/**
 * The authoritative source of a study's financial results (BRD FR-025, §14.4).
 * Pure with respect to the database: it reads the study and never writes.
 */
class FeasibilityCalculationService
{
    public function __construct(
        private CassandraLegacyEngine $engine,
        private LegacyFormMapper $mapper,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function calculate(Study $study): array
    {
        $study->loadMissing(['input', 'payment']);
        $input = StudyInputSchema::normalize($study->input->input_data ?? []);

        return $this->calculateInput($input, $study->payment ? [
            'amount' => $study->payment->amount,
            'currency' => $study->payment->currency,
        ] : null);
    }

    /**
     * @param  array<string, mixed>  $input  normalized study input
     * @param  array{amount: string|float, currency: string}|null  $fee
     * @return array<string, mixed>
     */
    public function calculateInput(array $input, ?array $fee): array
    {
        ['form' => $form, 'warnings' => $warnings] = $this->mapper->map($input, $this->labels(), $fee);
        $result = $this->engine->calculate($form);

        foreach ($result['yearly_projections'] as $year) {
            if ($year['profit'] < 0) {
                $warnings[] = [
                    'code' => 'loss_year',
                    'section' => 'results',
                    'message' => "تُظهر السنة {$year['year']} خسارة متوقعة وفق البيانات المدخلة.",
                ];
            }
        }

        return [
            ...$result,
            'currency' => $input['settings']['currency'],
            'decimals' => $input['settings']['decimals'] ?? 2,
            'warnings' => $warnings,
        ];
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function labels(): array
    {
        $labels = [];

        foreach (ReferenceOption::query()->get(['group', 'code', 'label_ar']) as $option) {
            $labels[$option->group->value][$option->code] = $option->label_ar;
        }

        return $labels;
    }
}
