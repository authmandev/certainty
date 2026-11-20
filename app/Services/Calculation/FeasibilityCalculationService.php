<?php

namespace App\Services\Calculation;

use App\Models\ReferenceOption;
use App\Models\Study;
use App\Services\Studies\StudyInputSchema;
use Throwable;

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
     * The slice of the results the study editor shows while the customer types
     * (BRD FR-029 to FR-031). Returns null when the draft cannot be calculated, so a
     * preview problem never blocks editing.
     *
     * @return array<string, mixed>|null
     */
    public function preview(Study $study): ?array
    {
        try {
            $result = $this->calculate($study);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }

        if (! $this->isFinite($result)) {
            return null;
        }

        return [
            'calculation_version' => $result['calculation_version'],
            'currency' => $result['currency'],
            'decimals' => $result['decimals'],
            'study_years' => $result['settings']['study_years'],
            'summary' => $result['summary'],
            'indicators' => $result['indicators'],
            'setup_costs' => array_map(fn (array $cost): array => [
                'name' => $cost['name'],
                'value' => $cost['value'],
                'is_study_fee' => $cost['name'] === LegacyFormMapper::FEE_NAME,
                'is_unused_borrowed_funds' => $cost['is_unused_borrowed_funds'],
            ], $result['capital']['setup_costs']),
            'yearly' => array_map(fn (array $year): array => [
                'year' => $year['year'],
                'revenue' => $year['total_revenue'],
                'expenses' => $year['total_expenses'],
                'profit' => $year['profit'],
                'profit_ratio' => $year['profit_ratio'],
            ], $result['yearly_projections']),
            'warnings' => $result['warnings'],
        ];
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
     * Extreme inputs can overflow to INF or NAN, which cannot be sent as JSON.
     *
     * @param  array<string, mixed>  $result
     */
    private function isFinite(array $result): bool
    {
        $isFinite = true;

        array_walk_recursive($result, function (mixed $value) use (&$isFinite): void {
            if (is_float($value) && ! is_finite($value)) {
                $isFinite = false;
            }
        });

        return $isFinite;
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
