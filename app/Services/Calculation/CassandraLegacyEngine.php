<?php

namespace App\Services\Calculation;

/**
 * Port of the legacy Cassandra calculation, `fieldsUpdated()` in
 * tests/Legacy/cassandra-legacy.js (BRD FR-023, FR-027, §25.4).
 *
 * The order of every operation follows the legacy code so floating-point
 * results match it. Known legacy quirks are kept on purpose (BRD §25.7) and
 * are marked "Q-xx" where they occur. The engine is pure: it reads a
 * LegacyForm and returns arrays, with no database access or formatting.
 */
final class CassandraLegacyEngine
{
    public const string VERSION = 'cassandra_legacy_v1';

    private const string NOT_AVAILABLE = 'غير متوفر';

    /**
     * @return array<string, mixed>
     */
    public function calculate(LegacyForm $form): array
    {
        $vat = $this->vatRate($form->vat);
        $studyYears = $this->studyYears($form->studyYears);
        $marketValue = JsMath::number($form->marketValue);

        // Liabilities (25.4 items 1-2).
        $totalBorrowed = 0.0;
        $totalLiabilities = 0.0;
        $loans = [];
        $instalmentRows = [];

        foreach ($form->loans as $loan) {
            $bank = JsMath::text($loan['bank']);
            $amount = JsMath::number($loan['amount']);
            $interest = JsMath::number($loan['interest']);
            $period = JsMath::number($loan['years']);

            if (! ($bank !== '' && ! is_nan($amount) && ! is_nan($interest) && ! is_nan($period) && $period != 0)) {
                continue;
            }

            $totalBorrowed += $amount;
            $payment = $interest > 0
                ? (($interest / 100 / 12) * $amount) / (1 - (1 + ($interest / 100 / 12)) ** (-1 * ($period * 12)))
                : $amount / $period / 12;

            if ($payment > 0) {
                // Q-01: the instalment booked as a fixed cost is rounded to a whole number.
                // The legacy helper prepends each instalment row, so later loans come first.
                array_unshift($instalmentRows, [
                    'name' => 'قسط مديونية: '.$bank,
                    'amount' => JsMath::toFixedZero($payment),
                    'period' => $period,
                    'includes_vat' => false, // Q-03
                    'is_debt_payment' => true,
                ]);
            }

            $payable = $payment * 12 * $period;
            $totalLiabilities += $payable;

            $loans[] = [
                'lender' => $bank,
                'amount' => $amount,
                'interest_rate' => $interest,
                'years' => $period,
                'monthly_instalment' => $payment,
                'total_payable' => $payable,
                'monthly_principal' => $amount / 12 / $period,
                'remaining' => $period,
            ];
        }

        // Setup costs and capital (25.4 items 3-4).
        $totalAssets = 0.0;
        $assets = [];
        $setupCostChart = [];

        foreach ($form->assets as $asset) {
            $name = JsMath::text($asset['name']);
            $value = JsMath::number($asset['value']);

            if (! ($name !== '' && ! is_nan($value))) {
                continue;
            }

            $lifeSpan = JsMath::number($asset['life_span']);
            $totalAssets += $value;
            $setupCostChart[] = ['label' => $name, 'value' => $value];
            $assets[] = [
                'name' => $name,
                'value' => $value,
                'accumulated_value' => $value,
                'is_cash' => false,
                'life_span' => $lifeSpan,
                'life_to_go' => $lifeSpan,
                'depreciation_ratio' => JsMath::number($asset['depreciation']),
            ];
        }

        $unassignedLiabilities = 0.0;

        if ($totalBorrowed > $totalAssets) {
            $unassignedLiabilities = $totalBorrowed - $totalAssets;
            $assets[] = [
                'name' => 'مبالغ غير مستغلة',
                'value' => $unassignedLiabilities,
                'accumulated_value' => $unassignedLiabilities,
                'is_cash' => true,
                'life_span' => 0.0,
                'life_to_go' => 0.0,
                'depreciation_ratio' => 0.0,
            ];
            $totalAssets += $unassignedLiabilities;
        }

        $selfFinancing = ($totalAssets - $totalBorrowed) > 0 ? ($totalAssets - $totalBorrowed) : 0.0;

        // Employees.
        $totalSalaries = 0.0;
        $employees = [];

        foreach ($form->employees as $employee) {
            $title = JsMath::text($employee['title']);
            $total = JsMath::number($employee['total']);
            $salary = JsMath::number($employee['salary']);

            if (! ($title !== '' && ! is_nan($salary) && ! is_nan($total))) {
                continue;
            }

            $totalSalaries += $salary * $total;
            $employees[] = ['title' => $title, 'count' => $total, 'monthly_salary' => $salary];
        }

        // Fixed costs: loan instalments first, then the customer's rows (25.4 item 9).
        $userFixedCosts = array_map(fn (array $cost): array => [
            'name' => JsMath::text($cost['name']),
            'amount' => JsMath::number($cost['amount']),
            'period' => 0.0,
            'includes_vat' => $cost['includes_vat'],
            'is_debt_payment' => false,
        ], $form->fixedCosts);

        $totalFixedCosts = 0.0;
        $totalFixedCostsVat = 0.0;
        $fixedCosts = [];

        foreach ([...$instalmentRows, ...$userFixedCosts] as $cost) {
            // The legacy check also rejects a NaN period, which cannot occur: customer rows use 0 and instalments a validated loan period.
            if (! ($cost['name'] !== '' && ! is_nan($cost['amount']))) {
                continue;
            }

            $totalFixedCosts += $cost['amount'];
            $cost['vat_amount'] = $cost['includes_vat'] ? ($cost['amount'] * $vat) : 0.0;
            $cost['remaining'] = $cost['period'];
            $totalFixedCostsVat += $cost['vat_amount'];
            $fixedCosts[] = $cost;
        }

        // Products: first-month figures (25.4 items 7-9).
        $monthly = ['revenue_with_vat' => 0.0, 'revenue_without_vat' => 0.0, 'units_sold' => 0.0, 'variable_costs_with_vat' => 0.0, 'variable_costs_without_vat' => 0.0];
        $products = [];

        foreach ($form->products as $product) {
            $name = JsMath::text($product['name']);
            $variableCost = JsMath::number($product['vc']);
            $quantity = JsMath::number($product['quantity']);
            $price = JsMath::number($product['price']);

            if (! ($name !== '' && ! is_nan($variableCost) && ! is_nan($quantity) && ! is_nan($price))) {
                continue;
            }

            $capacity = JsMath::number($product['capacity']);
            $capacity = (! is_nan($capacity) && $capacity > $quantity) ? JsMath::round($capacity) : JsMath::round($quantity);

            $revenueWithoutVat = $price * $quantity;
            $revenueWithVat = $revenueWithoutVat + ($revenueWithoutVat * $vat);
            $monthly['revenue_without_vat'] += $revenueWithoutVat;
            $monthly['revenue_with_vat'] += $revenueWithVat;
            $variableCostsWithVat = $variableCost * $quantity;
            $variableCostsWithoutVat = $variableCostsWithVat - ($variableCostsWithVat * $vat);
            $monthly['variable_costs_with_vat'] += $variableCostsWithVat;
            $monthly['variable_costs_without_vat'] += $variableCostsWithoutVat;
            $monthly['units_sold'] += $quantity;

            $products[] = [
                'name' => $name,
                'variable_cost' => $variableCost,
                'quantity' => $quantity,
                'capacity' => $capacity,
                'yearly_increase' => JsMath::number($product['yearly_increase']),
                'price' => $price,
                'revenue_without_vat' => $revenueWithoutVat,
                'variable_costs_without_vat' => $variableCostsWithoutVat,
                'variable_cost_without_vat' => 0.0,
            ];
        }

        // Yearly statements.
        $years = [];
        $productPricing = [];
        $profitabilityChart = [];
        $totalSales = 0.0;
        $capitalRecoveryYears = 0;
        $previous = null;

        for ($i = 0; $i < $studyYears; $i++) {
            $year = [
                'payment_without_interest' => 0.0,
                'total_liabilities' => $previous === null ? $totalBorrowed : $previous['total_liabilities'],
                'liabilities_to_equity' => $previous === null ? 0.0 : $previous['liabilities_to_equity'],
                'retained_earnings' => $previous === null ? 0.0 : $previous['retained_earnings'],
                'long_term_assets' => 0.0,
                'cash_long_term_assets' => 0.0,
                'depreciation' => 0.0,
                'assets_bought' => 0.0,
                'assets_sold' => 0.0,
                'fixed_costs' => 0.0,
                'fixed_costs_without_payments' => 0.0,
                'fixed_costs_vat' => 0.0,
                'revenue_with_vat' => 0.0,
                'revenue_without_vat' => 0.0,
                'units_sold' => 0.0,
                'variable_costs_with_vat' => 0.0,
                'variable_costs_without_vat' => 0.0,
                'fixed_cost_lines' => [],
            ];

            foreach ($products as $t => $product) {
                if ($i !== 0) {
                    $grown = $product['quantity'] + ($product['quantity'] * $product['yearly_increase'] / 100);
                    $product['quantity'] = $grown > $product['capacity'] ? JsMath::round($product['capacity']) : JsMath::round($grown);
                }

                $product['revenue_without_vat'] = $product['price'] * $product['quantity'];
                $revenueWithVat = $product['revenue_without_vat'] + ($product['revenue_without_vat'] * $vat);
                $year['revenue_without_vat'] += ($product['revenue_without_vat'] * 12);
                $year['revenue_with_vat'] += ($revenueWithVat * 12);
                $variableCostsWithVat = $product['variable_cost'] * $product['quantity'];
                $product['variable_costs_without_vat'] = $variableCostsWithVat - ($variableCostsWithVat * $vat);
                $year['variable_costs_with_vat'] += ($variableCostsWithVat * 12);
                $year['variable_costs_without_vat'] += ($product['variable_costs_without_vat'] * 12);
                $year['units_sold'] += ($product['quantity'] * 12);
                $product['variable_cost_without_vat'] = $product['variable_cost'] - ($product['variable_cost'] * $vat);
                $products[$t] = $product;
            }

            $totalSales += $year['revenue_with_vat'];
            $variableCostsVat = $year['variable_costs_with_vat'] - $year['variable_costs_without_vat'];
            $cashBeginning = $previous === null ? $unassignedLiabilities : $previous['cash_ending'];

            // Depreciation and replacement (25.4 items 5-6).
            foreach ($assets as $t => $asset) {
                if ($asset['life_to_go'] > 0 || $asset['life_span'] == 0) {
                    $asset['life_to_go'] = ($asset['life_to_go'] - 1) < 0 ? 0.0 : ($asset['life_to_go'] - 1);
                } else {
                    $asset['life_to_go'] = $asset['life_span'];
                    $year['assets_sold'] += $asset['accumulated_value'];
                    $asset['accumulated_value'] = $asset['value'];
                    $year['assets_bought'] += $asset['value'];
                    $asset['life_to_go']--;
                }

                if ($asset['is_cash']) {
                    $year['cash_long_term_assets'] += $asset['accumulated_value'];
                }

                $year['long_term_assets'] += $asset['accumulated_value'];
                $depreciation = $asset['accumulated_value'] * $asset['depreciation_ratio'] / 100;
                $year['depreciation'] += $depreciation;
                $asset['accumulated_value'] = $asset['accumulated_value'] - $depreciation;
                $assets[$t] = $asset;
            }

            // Income statement (25.4 item 10).
            $totalRevenue = $year['assets_sold'] + $year['revenue_with_vat'] - $year['variable_costs_without_vat'];
            $vatIn = $year['revenue_with_vat'] - $year['revenue_without_vat'];

            foreach ($fixedCosts as $t => $cost) {
                if ($cost['remaining'] > 0 || $cost['period'] == 0) {
                    $cost['remaining'] = ($cost['remaining'] - 1) < 0 ? 0.0 : ($cost['remaining'] - 1);
                    $year['fixed_costs_vat'] += ($cost['vat_amount'] * 12);
                    $year['fixed_costs'] += (($cost['amount'] - $cost['vat_amount']) * 12);
                    $year['fixed_costs_without_payments'] += $cost['is_debt_payment'] ? 0 : (($cost['amount'] - $cost['vat_amount']) * 12);
                    $year['fixed_cost_lines'][] = [
                        'name' => $cost['name'],
                        'amount' => (($cost['amount'] - $cost['vat_amount']) * 12),
                        'is_loan_instalment' => $cost['is_debt_payment'],
                    ];
                    $fixedCosts[$t] = $cost;
                }
            }

            $vatOut = $year['fixed_costs_vat'] + $variableCostsVat;
            $totalExpenses = $year['assets_bought'] + $year['depreciation'] + $year['fixed_costs'] + $vatOut + ($totalSalaries * 12);
            $pendingVat = ($vatIn - $vatOut) > 0 ? ($vatIn - $vatOut) : 0.0;
            $profitBeforeVat = $totalRevenue - $totalExpenses;
            $netProfit = $profitBeforeVat - $pendingVat;

            // Cash flow (25.4 item 11).
            $operating = $year['revenue_without_vat'] + $vatIn - $year['fixed_costs_without_payments'] - ($totalSalaries * 12) - $year['variable_costs_without_vat'] - $vatOut - $pendingVat;
            $investing = $year['assets_sold'] - $year['assets_bought'];
            $financing = ($year['fixed_costs'] - $year['fixed_costs_without_payments']) * -1;
            $netCashFlow = $operating + $investing + $financing;
            $cashEnding = $cashBeginning + $netCashFlow;

            // Balance sheet (25.4 item 12; Q-04: it may not balance, Q-07: depreciation is this year's only).
            foreach ($loans as $t => $loan) {
                // The legacy `|| Period == 0` branch is unreachable: zero-year loans are dropped above.
                if ($loan['remaining'] > 0) {
                    $loan['remaining'] = ($loan['remaining'] - 1) < 0 ? 0.0 : ($loan['remaining'] - 1);
                    $year['payment_without_interest'] += ($loan['monthly_principal'] * 12);
                    $loans[$t] = $loan;
                }
            }

            $year['liabilities_to_equity'] += $year['payment_without_interest'];
            $year['total_liabilities'] = ($year['total_liabilities'] - $year['payment_without_interest']) < 0 ? 0.0 : ($year['total_liabilities'] - $year['payment_without_interest']);
            $balanceAssets = $cashEnding + $year['long_term_assets'] - $year['depreciation'] - $year['cash_long_term_assets'];
            $openingRetainedEarnings = $year['retained_earnings'];
            $ownersEquity = $openingRetainedEarnings + $selfFinancing + $netProfit + ($investing * -1) + $year['liabilities_to_equity'];

            // Profitability (25.4 item 17).
            $finalExpenses = $year['variable_costs_without_vat'] + $totalExpenses + $pendingVat;
            $finalRevenue = $totalRevenue + $year['variable_costs_without_vat'];
            $finalProfit = $finalRevenue - $finalExpenses;

            if ($finalRevenue > 0) {
                $profitabilityChart[] = ['year' => $i + 1, 'revenue' => $finalRevenue, 'expenses' => $finalExpenses, 'profit' => $finalProfit];
            }

            // Cost-based pricing (25.4 item 18).
            if ($products !== []) {
                $productPricing[] = [
                    'year' => $i + 1,
                    'products' => array_map(fn (array $product): array => $this->pricing($product, $finalProfit), $products),
                ];
            }

            $year['retained_earnings'] += $netProfit + ($investing * -1);
            $capitalRecoveryYears = ($year['retained_earnings'] < $totalAssets) ? $capitalRecoveryYears + 1 : $capitalRecoveryYears;

            $year['cash_ending'] = $cashEnding;
            $previous = $year;

            $years[] = [
                'year' => $i + 1,
                'units_sold' => $year['units_sold'],
                'revenue_with_vat' => $year['revenue_with_vat'],
                'income' => [
                    'operating_revenue' => $year['revenue_without_vat'],
                    'raw_materials_cost' => $year['variable_costs_without_vat'] * -1,
                    'assets_sold' => $year['assets_sold'],
                    'vat_collected' => $vatIn,
                    'total_revenue' => $totalRevenue,
                    'assets_bought' => $year['assets_bought'],
                    'depreciation' => $year['depreciation'],
                    'salaries' => $totalSalaries * 12,
                    'fixed_costs' => $year['fixed_cost_lines'],
                    'vat_paid' => $vatOut,
                    'total_expenses' => $totalExpenses,
                    'profit_before_vat' => $profitBeforeVat,
                    'vat_due' => $pendingVat,
                    'net_profit' => $netProfit,
                ],
                'cash_flow' => [
                    'operating_revenue' => $year['revenue_without_vat'],
                    'raw_materials_cost' => $year['variable_costs_without_vat'] * -1,
                    'operating_costs' => $year['fixed_costs_without_payments'] * -1,
                    'salaries' => $totalSalaries * -12,
                    'vat_collected' => $vatIn,
                    'vat_paid' => $vatOut * -1,
                    'vat_due' => $pendingVat * -1,
                    'operating_total' => $operating,
                    'assets_bought' => $year['assets_bought'] * -1,
                    'assets_sold' => $year['assets_sold'],
                    'investing_total' => $investing,
                    'loan_instalments' => $financing,
                    'financing_total' => $financing,
                    'net_cash_flow' => $netCashFlow,
                    'opening_cash' => $cashBeginning,
                    'closing_cash' => $cashEnding,
                ],
                'balance_sheet' => [
                    'short_term_assets' => $cashEnding,
                    'long_term_assets' => $year['long_term_assets'] - $year['cash_long_term_assets'],
                    'depreciation' => $year['depreciation'] * -1,
                    'total_assets' => $balanceAssets,
                    'long_term_liabilities' => $year['total_liabilities'],
                    'total_liabilities' => $year['total_liabilities'],
                    'profit' => $netProfit,
                    'retained_earnings' => $openingRetainedEarnings,
                    'liabilities_to_equity' => $year['liabilities_to_equity'],
                    'net_investment_in_assets' => $investing,
                    'personal_capital' => $selfFinancing,
                    'total_equity' => $ownersEquity,
                    'total_liabilities_and_equity' => $ownersEquity + $year['total_liabilities'],
                ],
                'profitability' => [
                    'total_revenue' => $finalRevenue,
                    'total_expenses' => $finalExpenses,
                    'profit' => $finalProfit,
                    'monthly_expenses' => $finalExpenses / 12,
                    'monthly_profit' => $finalProfit / 12,
                    'expense_ratio' => $finalRevenue != 0 ? ($finalExpenses / $finalRevenue * 100) : null,
                    'profit_ratio' => $finalRevenue != 0 ? ($finalProfit / $finalRevenue * 100) : null,
                ],
                'closing_retained_earnings' => $year['retained_earnings'],
            ];
        }

        $finalRetainedEarnings = $previous === null ? 0.0 : $previous['retained_earnings'];
        $averageSales = $totalSales / $studyYears;

        return [
            'calculation_version' => self::VERSION,
            'settings' => [
                'study_years' => $studyYears,
                'vat_rate' => $vat,
            ],
            'summary' => [
                'total_capital' => $totalAssets,
                'total_borrowed' => $totalBorrowed,
                'total_liabilities_payable' => $totalLiabilities,
                'self_financing' => $selfFinancing,
                'unused_borrowed_funds' => $unassignedLiabilities,
                'monthly_salaries' => $totalSalaries,
                'monthly_fixed_costs' => $totalFixedCosts,
                'monthly_fixed_costs_vat' => $totalFixedCostsVat,
                'monthly_revenue_with_vat' => $monthly['revenue_with_vat'],
                'monthly_revenue_without_vat' => $monthly['revenue_without_vat'],
                'monthly_variable_costs_with_vat' => $monthly['variable_costs_with_vat'],
                'monthly_variable_costs_without_vat' => $monthly['variable_costs_without_vat'],
                'monthly_units_sold' => $monthly['units_sold'],
            ],
            'capital' => [
                'setup_costs' => array_map(fn (array $asset): array => [
                    'name' => $asset['name'],
                    'value' => $asset['value'],
                    'life_span' => $asset['life_span'],
                    'depreciation_rate' => $asset['depreciation_ratio'],
                    'is_unused_borrowed_funds' => $asset['is_cash'],
                ], $assets),
                'total' => $totalAssets,
                'borrowed' => $totalBorrowed,
                'self_financing' => $selfFinancing,
            ],
            'financing' => [
                'loans' => array_map(fn (array $loan): array => array_diff_key($loan, ['remaining' => true]), $loans),
                'total_borrowed' => $totalBorrowed,
                'total_payable' => $totalLiabilities,
            ],
            'employees' => $employees,
            'fixed_costs' => array_map(fn (array $cost): array => [
                'name' => $cost['name'],
                'monthly_amount' => $cost['amount'],
                'monthly_vat' => $cost['vat_amount'],
                'years' => $cost['period'] == 0 ? null : $cost['period'],
                'is_loan_instalment' => $cost['is_debt_payment'],
            ], $fixedCosts),
            'products' => array_map(fn (array $product): array => [
                'name' => $product['name'],
                'unit_cost' => $product['variable_cost'],
                'unit_price' => $product['price'],
                'monthly_capacity' => $product['capacity'],
                'annual_growth' => $product['yearly_increase'],
            ], $products),
            'yearly_projections' => array_map(fn (array $year): array => [
                'year' => $year['year'],
                'units_sold' => $year['units_sold'],
                'sales_with_vat' => $year['revenue_with_vat'],
                ...$year['profitability'],
                'closing_retained_earnings' => $year['closing_retained_earnings'],
            ], $years),
            'income_statements' => array_map(fn (array $year): array => ['year' => $year['year'], ...$year['income']], $years),
            'cash_flows' => array_map(fn (array $year): array => ['year' => $year['year'], ...$year['cash_flow']], $years),
            'balance_sheets' => array_map(fn (array $year): array => ['year' => $year['year'], ...$year['balance_sheet']], $years),
            'product_pricing' => $productPricing,
            'indicators' => [
                'total_sales' => $totalSales,
                'average_sales' => $averageSales,
                'average_profit' => $finalRetainedEarnings / $studyYears,
                'capital_recovery' => $this->capitalRecovery($capitalRecoveryYears, $finalRetainedEarnings, $totalAssets),
                'market_share' => $marketValue > 0 ? ($averageSales / $marketValue * 100) : null,
            ],
            'charts' => [
                'setup_costs' => $setupCostChart,
                'capital_distribution' => ($totalBorrowed > 0 || $totalAssets > 0)
                    ? [['label' => 'مديونية', 'value' => $totalBorrowed], ['label' => 'تمويل خاص', 'value' => $selfFinancing]]
                    : [],
                'profitability' => $profitabilityChart,
            ],
        ];
    }

    /**
     * `_cvat`: a VAT percentage between 0 and 99 as a fraction, otherwise 0.
     */
    private function vatRate(int|float|string|null $value): float
    {
        $number = JsMath::number($value);

        return ($number > -1 && $number < 100) ? ($number / 100) : 0.0;
    }

    /**
     * `_sy`: 1 to 15 years, falling back to 3.
     */
    private function studyYears(int|float|string|null $value): int
    {
        $number = JsMath::number($value);

        return ($number > 0 && $number < 16 && JsMath::text($value) !== '') ? (int) ceil($number) : 3;
    }

    /**
     * @param  array<string, mixed>  $product
     * @return array<string, mixed>
     */
    private function pricing(array $product, float $yearProfit): array
    {
        $gap = ($yearProfit / 12) - ($product['revenue_without_vat'] - $product['variable_costs_without_vat']);
        $gap = $product['quantity'] > 0 ? ($gap / $product['quantity']) : 0;
        $breakEven = ($product['variable_cost_without_vat'] + ($gap * -1)) > $product['variable_cost_without_vat']
            ? ($product['variable_cost_without_vat'] + ($gap * -1))
            : $product['variable_cost_without_vat'];
        $margin = $breakEven > 0 ? (($product['price'] - $breakEven) / $breakEven * 100) : null;

        return [
            'name' => $product['name'],
            'break_even_price' => $breakEven,
            'profit_margin' => $margin,
            'margin_display' => $margin !== null ? null : (($product['price'] > 0 && $product['quantity'] > 0) ? 'infinite' : 'not_available'),
            'monthly_quantity' => $product['quantity'],
        ];
    }

    /**
     * Q-05: a count of 0 (recovered in year 1) is reported as not available.
     *
     * @return array{status: 'within'|'more_than'|'not_available', years: int|null, label: string}
     */
    private function capitalRecovery(int $years, float $finalRetainedEarnings, float $totalAssets): array
    {
        if ($years === 0) {
            return ['status' => 'not_available', 'years' => null, 'label' => self::NOT_AVAILABLE];
        }

        return $finalRetainedEarnings < $totalAssets
            ? ['status' => 'more_than', 'years' => $years, 'label' => "أكثر من {$years} سنوات"]
            : ['status' => 'within', 'years' => $years, 'label' => "خلال {$years} سنوات"];
    }
}
