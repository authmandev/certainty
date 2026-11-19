<?php

namespace App\Services\Studies;

/**
 * The documented shape of a study's editable input (BRD §13.1 study_inputs, §25.3).
 *
 * Every stored payload is normalized against these defaults so the editor,
 * the progress rules and the calculation engine always see the same keys.
 * Numeric option sets mirror the legacy Cassandra form.
 */
class StudyInputSchema
{
    public const int VERSION = 1;

    /** @var list<int> */
    public const array STUDY_YEARS = [1, 2, 3, 5, 10, 15];

    /** @var list<int> */
    public const array DECIMAL_PLACES = [0, 1, 2, 3];

    public const int MAX_VAT_PERCENT = 25;

    /**
     * Row templates for each repeatable group.
     *
     * @var array<string, array<string, mixed>>
     */
    public const array ROWS = [
        'owners' => ['key' => null, 'name' => null, 'share' => null, 'experience' => null],
        'competitors' => ['key' => null, 'name' => null, 'share' => null, 'characteristics' => null],
        'assets' => ['key' => null, 'category_code' => null, 'name' => null, 'value' => null, 'life_span' => null, 'depreciation_rate' => null, 'includes_vat' => false],
        'loans' => ['key' => null, 'lender' => null, 'amount' => null, 'interest_rate' => null, 'years' => null],
        'fixed_costs' => ['key' => null, 'category_code' => null, 'name' => null, 'monthly_amount' => null, 'includes_vat' => false],
        'employees' => ['key' => null, 'job_code' => null, 'title' => null, 'count' => null, 'monthly_salary' => null],
        'products' => ['key' => null, 'name' => null, 'unit_cost' => null, 'unit_price' => null, 'monthly_quantity' => null, 'monthly_capacity' => null, 'annual_growth' => null],
    ];

    /**
     * The default input for a new study.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'settings' => [
                'currency' => array_key_first(config('certainty.study_currencies')),
                'decimals' => 2,
                'vat_percent' => null,
                'study_years' => null,
            ],
            'project' => [
                'start_date' => null,
                'description' => null,
                'organizational_feasibility' => null,
                'sector_code' => null,
                'project_type_code' => null,
                'project_type_other' => null,
            ],
            'owners' => [],
            'market' => [
                'name' => null,
                'size' => null,
                'description' => null,
                'products' => null,
                'target_customers' => null,
                'suppliers' => null,
                'marketing_plan' => null,
                'competition_analysis' => null,
            ],
            'competitors' => [],
            'assets' => [],
            'loans' => [],
            'fixed_costs' => [],
            'employees' => [],
            'products' => [],
            'swot' => [
                'strengths' => null,
                'weaknesses' => null,
                'opportunities' => null,
                'threats' => null,
            ],
        ];
    }

    /**
     * Keep only known keys, fill in missing ones and reindex repeatable rows.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function normalize(array $input): array
    {
        $normalized = [];

        foreach (self::defaults() as $section => $default) {
            $value = $input[$section] ?? $default;

            if (array_key_exists($section, self::ROWS)) {
                $normalized[$section] = array_values(array_map(
                    fn (mixed $row): array => array_replace(
                        self::ROWS[$section],
                        array_intersect_key(is_array($row) ? $row : [], self::ROWS[$section]),
                    ),
                    is_array($value) ? $value : [],
                ));

                continue;
            }

            $normalized[$section] = array_replace(
                $default,
                array_intersect_key(is_array($value) ? $value : [], $default),
            );
        }

        return $normalized;
    }
}
