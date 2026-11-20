<?php

namespace App\Services\Reports;

use App\Models\StudyReport;
use Illuminate\Support\Facades\Config;

/**
 * Prepares a report version's snapshots for the PDF view. Labels follow the
 * legacy Cassandra report (BRD §25, FR-032); every figure comes from the
 * stored calculation snapshot, never from a fresh calculation.
 *
 * @phpstan-type StatementRow array{label: string, values: list<float|int>, strong: bool}
 * @phpstan-type Statement array{title: string, years: list<int>, rows: list<StatementRow>}
 */
class StudyReportPresenter
{
    /**
     * Years per statement table, so fifteen-year studies still fit an A4 page.
     */
    private const int YEARS_PER_TABLE = 5;

    private const string COLOR_REVENUE = '#0f6f7f';

    private const string COLOR_EXPENSES = '#94a3b8';

    private const string COLOR_PROFIT = '#d4a017';

    /**
     * @return array<string, mixed>
     */
    public function present(StudyReport $report): array
    {
        $input = $report->input_snapshot;
        $result = $report->calculation_snapshot;
        $currency = (string) ($result['currency'] ?? $input['settings']['currency'] ?? '');
        $years = array_column($result['yearly_projections'], 'year');

        return [
            'report' => $report,
            'study' => $report->study,
            'customerName' => $report->study->user->name,
            'company' => (string) config('certainty.reports.company_name'),
            'input' => $input,
            'result' => $result,
            'format' => new ReportFormatter((int) ($result['decimals'] ?? 2)),
            'currency' => [
                'code' => $currency,
                'name' => Config::array('certainty.study_currencies')[$currency] ?? $currency,
            ],
            'fonts' => $this->fonts(),
            'profitabilityChart' => ReportCharts::lines(
                array_map(fn (int $year): string => (string) $year, $years),
                [
                    ['label' => 'الإيرادات', 'color' => self::COLOR_REVENUE, 'values' => array_column($result['yearly_projections'], 'total_revenue')],
                    ['label' => 'التكاليف', 'color' => self::COLOR_EXPENSES, 'values' => array_column($result['yearly_projections'], 'total_expenses'), 'dashed' => true],
                    ['label' => 'الأرباح', 'color' => self::COLOR_PROFIT, 'values' => array_column($result['yearly_projections'], 'profit')],
                ],
            ),
            'chartLegend' => [
                ['label' => 'الإيرادات السنوية', 'color' => self::COLOR_REVENUE],
                ['label' => 'التكاليف السنوية', 'color' => self::COLOR_EXPENSES],
                ['label' => 'الأرباح السنوية', 'color' => self::COLOR_PROFIT],
            ],
            'financingChart' => ReportCharts::donut([
                ['label' => 'مديونية', 'color' => self::COLOR_PROFIT, 'value' => max(0, $result['capital']['borrowed'])],
                ['label' => 'تمويل خاص', 'color' => self::COLOR_REVENUE, 'value' => max(0, $result['capital']['self_financing'])],
            ]),
            'financingLegend' => [
                ['label' => 'مديونية لتمويل المشروع', 'color' => self::COLOR_PROFIT, 'value' => $result['capital']['borrowed']],
                ['label' => 'تمويل خاص للمشروع', 'color' => self::COLOR_REVENUE, 'value' => $result['capital']['self_financing']],
            ],
            'pricing' => $result['product_pricing'][0]['products'] ?? [],
            'statements' => [
                ...$this->chunk('بيانات الدخل', $this->incomeRows($result['income_statements']), $years),
                ...$this->chunk('بيانات التدفق النقدي', $this->cashFlowRows($result['cash_flows']), $years),
                ...$this->chunk('ورقة الموازنة', $this->balanceSheetRows($result['balance_sheets']), $years),
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $statements
     * @return list<StatementRow>
     */
    private function incomeRows(array $statements): array
    {
        $fixedCostNames = [];

        foreach ($statements as $statement) {
            foreach ($statement['fixed_costs'] as $cost) {
                $fixedCostNames[$cost['name']] = true;
            }
        }

        $fixedCostRows = [];

        foreach (array_keys($fixedCostNames) as $name) {
            $fixedCostRows[] = $this->row((string) $name, array_map(
                fn (array $statement): float => (float) array_sum(array_column(array_filter($statement['fixed_costs'], fn (array $cost): bool => $cost['name'] === $name), 'amount')),
                $statements,
            ));
        }

        return [
            $this->row('إيرادات تشغيلية', array_column($statements, 'operating_revenue')),
            $this->row('تكلفة مواد أولية', array_column($statements, 'raw_materials_cost')),
            $this->row('بيع أصول مستهلكة', array_column($statements, 'assets_sold')),
            $this->row('ضريبة محصلة', array_column($statements, 'vat_collected')),
            $this->row('اجمالي الإيرادات', array_column($statements, 'total_revenue'), true),
            $this->row('شراء أصول جديدة', array_column($statements, 'assets_bought')),
            $this->row('تكاليف استهلاك أصول', array_column($statements, 'depreciation')),
            $this->row('رواتب موظفين', array_column($statements, 'salaries')),
            ...$fixedCostRows,
            $this->row('ضريبة مدفوعة', array_column($statements, 'vat_paid')),
            $this->row('اجمالي المصروفات', array_column($statements, 'total_expenses'), true),
            $this->row('أرباح قبل الضريبة', array_column($statements, 'profit_before_vat')),
            $this->row('ضريبة القيمة المضافة', array_column($statements, 'vat_due')),
            $this->row('اجمالي الأرباح', array_column($statements, 'net_profit'), true),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $statements
     * @return list<StatementRow>
     */
    private function cashFlowRows(array $statements): array
    {
        return [
            $this->row('إيرادات تشغيلية', array_column($statements, 'operating_revenue')),
            $this->row('تكلفة مواد أولية', array_column($statements, 'raw_materials_cost')),
            $this->row('تكاليف تشغيلية', array_column($statements, 'operating_costs')),
            $this->row('رواتب موظفين', array_column($statements, 'salaries')),
            $this->row('ضريبة محصلة', array_column($statements, 'vat_collected')),
            $this->row('ضريبة مدفوعة', array_column($statements, 'vat_paid')),
            $this->row('ضريبة القيمة المضافة', array_column($statements, 'vat_due')),
            $this->row('اجمالي التدفق التشغيلي', array_column($statements, 'operating_total'), true),
            $this->row('شراء أصول جديدة', array_column($statements, 'assets_bought')),
            $this->row('بيع أصول مستهلكة', array_column($statements, 'assets_sold')),
            $this->row('اجمالي التدفق الاستثماري', array_column($statements, 'investing_total'), true),
            $this->row('أقساط مطلوبات', array_column($statements, 'loan_instalments')),
            $this->row('اجمالي التدفق التمويلي', array_column($statements, 'financing_total'), true),
            $this->row('السيولة هذه السنة', array_column($statements, 'net_cash_flow')),
            $this->row('السيولة في بداية السنة', array_column($statements, 'opening_cash')),
            $this->row('السيولة في نهاية السنة', array_column($statements, 'closing_cash'), true),
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $statements
     * @return list<StatementRow>
     */
    private function balanceSheetRows(array $statements): array
    {
        return [
            $this->row('اصول المدى القصير', array_column($statements, 'short_term_assets')),
            $this->row('أصول طويلة المدى', array_column($statements, 'long_term_assets')),
            $this->row('استهلاك أصول طويلة المدى', array_column($statements, 'depreciation')),
            $this->row('اجمالي الاصول', array_column($statements, 'total_assets'), true),
            $this->row('مطلوبات طويلة المدى', array_column($statements, 'long_term_liabilities')),
            $this->row('اجمالي المطلوبات', array_column($statements, 'total_liabilities'), true),
            $this->row('أرباح', array_column($statements, 'profit')),
            $this->row('أرباح متراكمة', array_column($statements, 'retained_earnings')),
            $this->row('أصول مطلوبة إلى مملوكة', array_column($statements, 'liabilities_to_equity')),
            $this->row('صافي الاستثمار في الأصول', array_column($statements, 'net_investment_in_assets')),
            $this->row('رأس المال الشخصي', array_column($statements, 'personal_capital')),
            $this->row('اجمالي حقوق الملاك', array_column($statements, 'total_equity'), true),
            $this->row('اجمالي المطلوبات و حقوق الملاك', array_column($statements, 'total_liabilities_and_equity'), true),
        ];
    }

    /**
     * @param  array<int, float|int>  $values
     * @return StatementRow
     */
    private function row(string $label, array $values, bool $strong = false): array
    {
        return ['label' => $label, 'values' => array_values($values), 'strong' => $strong];
    }

    /**
     * Split a statement into tables of at most five years each.
     *
     * @param  list<StatementRow>  $rows
     * @param  list<int>  $years
     * @return list<Statement>
     */
    private function chunk(string $title, array $rows, array $years): array
    {
        $tables = [];

        foreach (array_chunk(array_keys($years), self::YEARS_PER_TABLE) as $indexes) {
            $tables[] = [
                'title' => $title,
                'years' => array_map(fn (int $index): int => $years[$index], $indexes),
                'rows' => array_map(fn (array $row): array => [
                    ...$row,
                    'values' => array_map(fn (int $index): float|int => $row['values'][$index], $indexes),
                ], $rows),
            ];
        }

        return $tables;
    }

    /**
     * The report font, embedded so rendering never depends on the server's
     * installed fonts or network access.
     *
     * @return list<array{weight: int, data: string}>
     */
    private function fonts(): array
    {
        $files = [400 => 'Regular', 600 => 'SemiBold', 700 => 'Bold'];
        $fonts = [];

        foreach ($files as $weight => $name) {
            $path = resource_path("fonts/ibm-plex-sans-arabic/IBMPlexSansArabic-{$name}.ttf");
            $fonts[] = ['weight' => $weight, 'data' => base64_encode((string) file_get_contents($path))];
        }

        return $fonts;
    }
}
