<?php

namespace App\Services\Calculation;

/**
 * Maps a normalized study input (StudyInputSchema) to the values the legacy
 * form fields would hold (BRD §25.3), adds the study fee (A-05 Option A) and
 * collects input warnings (FR-031).
 *
 * @phpstan-type Warning array{code: string, section: string, message: string}
 */
final class LegacyFormMapper
{
    public const string FEE_NAME = 'رسوم إعداد دراسة الجدوى';

    /**
     * The study length the legacy form selects by default.
     */
    private const int DEFAULT_STUDY_YEARS = 2;

    /** @var list<Warning> */
    private array $warnings = [];

    /**
     * @param  array<string, mixed>  $input  normalized study input
     * @param  array<string, array<string, string>>  $labels  category labels keyed by group then code
     * @param  array{amount: string|float, currency: string}|null  $fee  the study's payment
     * @return array{form: LegacyForm, warnings: list<Warning>}
     */
    public function map(array $input, array $labels, ?array $fee): array
    {
        $this->warnings = [];
        $settings = $input['settings'];

        if ($settings['vat_percent'] === null) {
            $this->warn('vat_not_set', 'basic', 'لم تُحدَّد نسبة ضريبة القيمة المضافة، وتُحسب الأرقام دون ضريبة.');
        }

        $studyYears = $settings['study_years'];

        if ($studyYears === null) {
            $studyYears = self::DEFAULT_STUDY_YEARS;
            $this->warn('study_years_not_set', 'basic', 'لم تُحدَّد مدة الدراسة، وتُعرض الأرقام لسنتين مؤقتًا.');
        }

        $assets = $this->assets($input['assets'], $labels['asset_category'] ?? []);

        if ($fee !== null) {
            if ($fee['currency'] === $settings['currency']) {
                // Option A: a one-time setup cost that is never depreciated or replaced.
                $assets[] = ['name' => self::FEE_NAME, 'value' => (float) $fee['amount'], 'life_span' => 0, 'depreciation' => 0, 'includes_vat' => false];
            } else {
                $this->warn('fee_currency_mismatch', 'costs', 'رسوم الدراسة بعملة مختلفة عن عملة الدراسة، ولم تُضف إلى تكاليف التأسيس.');
            }
        }

        $form = new LegacyForm(
            vat: $settings['vat_percent'],
            studyYears: $studyYears,
            marketValue: $input['market']['size'],
            loans: $this->loans($input['loans']),
            assets: $assets,
            employees: $this->employees($input['employees'], $labels['job_category'] ?? []),
            fixedCosts: $this->fixedCosts($input['fixed_costs'], $labels['fixed_cost_category'] ?? []),
            products: $this->products($input['products']),
        );

        return ['form' => $form, 'warnings' => $this->warnings];
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{bank: string|null, amount: int|float|string|null, interest: int|float|string|null, years: int|float|string|null}>
     */
    private function loans(array $rows): array
    {
        $loans = [];

        foreach ($rows as $index => $row) {
            if (! $this->hasText($row['lender']) || ! ($row['years'] > 0)) {
                $this->warn('row_ignored', 'costs', 'القرض '.($index + 1).' لا يدخل في الحسابات لأنه بلا جهة مقرضة أو سنوات سداد.');
            }

            $loans[] = ['bank' => $row['lender'], 'amount' => $row['amount'], 'interest' => $row['interest_rate'], 'years' => $row['years']];
        }

        return $loans;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, string>  $labels
     * @return list<array{name: string|null, value: int|float|string|null, life_span: int|float|string|null, depreciation: int|float|string|null, includes_vat: bool}>
     */
    private function assets(array $rows, array $labels): array
    {
        $assets = [];

        foreach ($rows as $index => $row) {
            $name = $this->label($row['name'], $row['category_code'], $labels);

            if ($name === null) {
                $this->warn('row_ignored', 'costs', 'الأصل '.($index + 1).' لا يدخل في الحسابات لأنه بلا فئة أو وصف.');
            }

            $assets[] = ['name' => $name, 'value' => $row['value'], 'life_span' => $row['life_span'], 'depreciation' => $row['depreciation_rate'], 'includes_vat' => (bool) $row['includes_vat']];
        }

        return $assets;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, string>  $labels
     * @return list<array{title: string|null, total: int|float|string|null, salary: int|float|string|null}>
     */
    private function employees(array $rows, array $labels): array
    {
        $employees = [];

        foreach ($rows as $index => $row) {
            $title = $this->label($row['title'], $row['job_code'], $labels);

            if ($title === null) {
                $this->warn('row_ignored', 'costs', 'الوظيفة '.($index + 1).' لا تدخل في الحسابات لأنها بلا مسمى.');
            }

            $employees[] = ['title' => $title, 'total' => $row['count'], 'salary' => $row['monthly_salary']];
        }

        return $employees;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @param  array<string, string>  $labels
     * @return list<array{name: string|null, amount: int|float|string|null, includes_vat: bool}>
     */
    private function fixedCosts(array $rows, array $labels): array
    {
        $costs = [];

        foreach ($rows as $index => $row) {
            $name = $this->label($row['name'], $row['category_code'], $labels);

            if ($name === null) {
                $this->warn('row_ignored', 'costs', 'التكلفة الثابتة '.($index + 1).' لا تدخل في الحسابات لأنها بلا فئة أو وصف.');
            }

            $costs[] = ['name' => $name, 'amount' => $row['monthly_amount'], 'includes_vat' => (bool) $row['includes_vat']];
        }

        return $costs;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{name: string|null, vc: int|float|string|null, quantity: int|float|string|null, price: int|float|string|null, capacity: int|float|string|null, yearly_increase: int|float|string|null}>
     */
    private function products(array $rows): array
    {
        $products = [];

        foreach ($rows as $index => $row) {
            $number = $index + 1;

            if (! $this->hasText($row['name'])) {
                $this->warn('row_ignored', 'sales', "المنتج {$number} لا يدخل في الحسابات لأنه بلا اسم.");
            }

            if ($row['monthly_capacity'] !== null && $row['monthly_quantity'] !== null && $row['monthly_capacity'] < $row['monthly_quantity']) {
                $this->warn('capacity_below_quantity', 'sales', "الطاقة الإنتاجية للمنتج {$number} أقل من الكمية المتوقعة، وتُعتبر مساوية لها.");
            }

            if ($row['unit_price'] !== null && $row['unit_cost'] !== null && $row['unit_price'] < $row['unit_cost']) {
                $this->warn('price_below_cost', 'sales', "سعر بيع المنتج {$number} أقل من تكلفة مواده.");
            }

            $products[] = [
                'name' => $row['name'],
                'vc' => $row['unit_cost'],
                'quantity' => $row['monthly_quantity'],
                'price' => $row['unit_price'],
                'capacity' => $row['monthly_capacity'],
                'yearly_increase' => $row['annual_growth'],
            ];
        }

        return $products;
    }

    /**
     * The legacy engine needs a row name; fall back to the category label, and drop rows with neither.
     *
     * @param  array<string, string>  $labels
     */
    private function label(mixed $name, mixed $code, array $labels): ?string
    {
        if ($this->hasText($name)) {
            return trim((string) $name);
        }

        return is_string($code) ? ($labels[$code] ?? null) : null;
    }

    private function hasText(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function warn(string $code, string $section, string $message): void
    {
        $this->warnings[] = ['code' => $code, 'section' => $section, 'message' => $message];
    }
}
