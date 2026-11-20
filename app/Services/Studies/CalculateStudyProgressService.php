<?php

namespace App\Services\Studies;

use App\Models\ReferenceOption;
use App\Models\Study;

/**
 * Computes study completion from the saved input, never from visited pages (BRD FR-011).
 *
 * Rules: each of the five sections has a list of required items, and a
 * section's score is the share of its items that are satisfied. The overall
 * percentage is the rounded average of the five section scores, so every
 * section weighs 20%. Owners, assets and products need at least one row.
 * For every repeatable group, rows the customer added must be complete;
 * an empty optional group (loans, fixed costs, employees, competitors)
 * adds no item.
 *
 * @phpstan-import-type SectionProgress from StudyProgress
 */
class CalculateStudyProgressService
{
    /**
     * @param  array<string, mixed>  $input  normalized study input
     */
    public function calculate(Study $study, array $input): StudyProgress
    {
        $sections = [
            $this->section('basic', 'معلومات أساسية', $this->basicItems($study, $input)),
            $this->section('market', 'دراسة السوق', $this->marketItems($input)),
            $this->section('costs', 'التكاليف', $this->costItems($input)),
            $this->section('sales', 'المبيعات', $this->salesItems($input)),
            $this->section('swot', 'تحليل SWOT', $this->swotItems($input)),
        ];

        $average = array_sum(array_map(
            fn (array $section): float => $section['completed'] / $section['total'],
            $sections,
        )) / count($sections);

        return new StudyProgress($sections, (int) round($average * 100));
    }

    /**
     * @param  array<string, bool|null>  $items  label => satisfied, or null when not applicable
     * @return SectionProgress
     */
    private function section(string $key, string $title, array $items): array
    {
        $items = array_filter($items, fn (?bool $satisfied): bool => $satisfied !== null);
        $missing = array_keys(array_filter($items, fn (bool $satisfied): bool => ! $satisfied));
        $completed = count($items) - count($missing);

        return [
            'key' => $key,
            'title' => $title,
            'completed' => $completed,
            'total' => count($items),
            'is_complete' => $missing === [],
            'missing' => $missing,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool|null>
     */
    private function basicItems(Study $study, array $input): array
    {
        $settings = $input['settings'];
        $project = $input['project'];
        $owners = $input['owners'];

        return [
            'اسم الدراسة' => $this->filled($study->title),
            'اسم المشروع' => $this->filled($study->project_name),
            'تاريخ بدء المشروع' => $this->filled($project['start_date']),
            'نبذة عن المشروع' => $this->filled($project['description']),
            'الجدوى التنظيمية' => $this->filled($project['organizational_feasibility']),
            'القطاع' => $this->filled($project['sector_code']),
            'نوع المشروع' => $this->filled($project['project_type_code'])
                && ($project['project_type_code'] !== ReferenceOption::OTHER || $this->filled($project['project_type_other'])),
            'العملة' => $this->filled($settings['currency']),
            'خانات الأعشار' => $settings['decimals'] !== null,
            'نسبة ضريبة القيمة المضافة' => $settings['vat_percent'] !== null,
            'مدة الدراسة' => $this->filled($settings['study_years']),
            'مالك واحد على الأقل' => $owners !== [],
            'بيانات الملاك مكتملة' => $this->allRows($owners, fn (array $owner): bool => $this->filled($owner['name']) && $this->positive($owner['share'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool|null>
     */
    private function marketItems(array $input): array
    {
        $market = $input['market'];

        return [
            'اسم السوق المستهدف' => $this->filled($market['name']),
            'حجم السوق' => $market['size'] !== null,
            'نبذة عن السوق' => $this->filled($market['description']),
            'المنتجات والخدمات' => $this->filled($market['products']),
            'الفئة المستهدفة' => $this->filled($market['target_customers']),
            'الموردون' => $this->filled($market['suppliers']),
            'خطة التسويق' => $this->filled($market['marketing_plan']),
            'تحليل المنافسة' => $this->filled($market['competition_analysis']),
            'بيانات المنافسين مكتملة' => $this->allRows($input['competitors'], fn (array $competitor): bool => $this->filled($competitor['name']) && $this->positive($competitor['share'])),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool|null>
     */
    private function costItems(array $input): array
    {
        return [
            'أصل تأسيسي واحد على الأقل' => $input['assets'] !== [],
            'بيانات تكاليف التأسيس مكتملة' => $this->allRows($input['assets'], fn (array $asset): bool => $this->labelled($asset['category_code'], $asset['name'])
                && $asset['value'] !== null && $asset['life_span'] !== null && $asset['depreciation_rate'] !== null),
            'بيانات القروض مكتملة' => $this->allRows($input['loans'], fn (array $loan): bool => $this->filled($loan['lender'])
                && $this->positive($loan['amount']) && $loan['interest_rate'] !== null && $this->positive($loan['years'])),
            'بيانات التكاليف الثابتة مكتملة' => $this->allRows($input['fixed_costs'], fn (array $cost): bool => $this->labelled($cost['category_code'], $cost['name'])
                && $cost['monthly_amount'] !== null),
            'بيانات الموظفين مكتملة' => $this->allRows($input['employees'], fn (array $employee): bool => $this->labelled($employee['job_code'], $employee['title'])
                && $this->positive($employee['count']) && $employee['monthly_salary'] !== null),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool|null>
     */
    private function salesItems(array $input): array
    {
        return [
            'منتج واحد على الأقل' => $input['products'] !== [],
            'بيانات المنتجات مكتملة' => $this->allRows($input['products'], fn (array $product): bool => $this->filled($product['name'])
                && $product['unit_cost'] !== null && $product['unit_price'] !== null && $product['monthly_quantity'] !== null),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array<string, bool|null>
     */
    private function swotItems(array $input): array
    {
        $swot = $input['swot'];

        return [
            'نقاط القوة' => $this->filled($swot['strengths']),
            'نقاط الضعف' => $this->filled($swot['weaknesses']),
            'الفرص' => $this->filled($swot['opportunities']),
            'المخاطر' => $this->filled($swot['threats']),
        ];
    }

    private function filled(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function positive(mixed $value): bool
    {
        return is_numeric($value) && $value > 0;
    }

    /**
     * A row needs a category, plus a description when the category is "other".
     */
    private function labelled(mixed $code, mixed $name): bool
    {
        return $this->filled($code) && ($code !== ReferenceOption::OTHER || $this->filled($name));
    }

    /**
     * Rows only count once the customer adds some; an empty optional group is
     * not an item at all (null), so it neither helps nor hurts the score.
     *
     * @param  list<array<string, mixed>>  $rows
     * @param  callable(array<string, mixed>): bool  $isComplete
     */
    private function allRows(array $rows, callable $isComplete): ?bool
    {
        if ($rows === []) {
            return null;
        }

        foreach ($rows as $row) {
            if (! $isComplete($row)) {
                return false;
            }
        }

        return true;
    }
}
