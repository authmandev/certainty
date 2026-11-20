<?php

use App\Services\Calculation\CassandraLegacyEngine;
use App\Services\Calculation\LegacyForm;

/**
 * Compares the PHP engine with the original Cassandra JavaScript on every
 * fixture produced by tests/Legacy/harness.mjs (BRD FR-028). Differences up
 * to one part in a billion are floating-point noise between JS and PHP.
 */
function legacyFixtures(): array
{
    $fixtures = [];

    foreach (glob(dirname(__DIR__, 2).'/Fixtures/legacy/*.json') as $path) {
        $fixture = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
        $fixtures[$fixture['name']] = [$fixture];
    }

    return $fixtures;
}

function legacyForm(array $form): LegacyForm
{
    return new LegacyForm(
        vat: $form['vat'],
        studyYears: $form['study_years'],
        marketValue: $form['market_value'],
        loans: $form['loans'],
        assets: $form['assets'],
        employees: $form['employees'],
        fixedCosts: $form['fixed_costs'],
        products: $form['products'],
    );
}

function expectClose(mixed $actual, mixed $expected, string $label): void
{
    $tolerance = 1e-9 * max(1, abs((float) $expected));

    expect(abs((float) $actual - (float) $expected))
        ->toBeLessThanOrEqual($tolerance, "{$label}: expected {$expected}, got {$actual}");
}

test('the engine reproduces the legacy Cassandra results', function (array $fixture) {
    $result = (new CassandraLegacyEngine)->calculate(legacyForm($fixture['form']));
    $legacy = $fixture['expected'];
    $od = $legacy['OD'];

    $totals = [
        'TotalBorrowed' => $result['summary']['total_borrowed'],
        'TotalLiabilities' => $result['summary']['total_liabilities_payable'],
        'TotalAssets' => $result['summary']['total_capital'],
        'UnassignedLiabilities' => $result['summary']['unused_borrowed_funds'],
        'Self_Financing' => $result['summary']['self_financing'],
        'TotalSalaries' => $result['summary']['monthly_salaries'],
        'TotalFC' => $result['summary']['monthly_fixed_costs'],
        'TotalFCPaidVAT' => $result['summary']['monthly_fixed_costs_vat'],
        'TotalRevenue_wTax' => $result['summary']['monthly_revenue_with_vat'],
        'TotalRevenue_woTax' => $result['summary']['monthly_revenue_without_vat'],
        'TotalVC_wTax' => $result['summary']['monthly_variable_costs_with_vat'],
        'TotalVC_woTax' => $result['summary']['monthly_variable_costs_without_vat'],
        'TotalUnitsSold' => $result['summary']['monthly_units_sold'],
        'TotalSales' => $result['indicators']['total_sales'],
        'AverageSales' => $result['indicators']['average_sales'],
        'AverageProfit' => $result['indicators']['average_profit'],
        'MarketShare' => $result['indicators']['market_share'] ?? 0,
    ];

    foreach ($totals as $key => $actual) {
        expectClose($actual, $od[$key], "OD.{$key}");
    }

    expect($result['indicators']['capital_recovery']['label'])->toBe($od['CapitalRecovery'])
        ->and($result['settings']['study_years'])->toBe((int) $legacy['sy'])
        ->and($result['yearly_projections'])->toHaveCount(count($legacy['years']));

    foreach ($legacy['years'] as $index => $year) {
        $actual = [
            'income' => $result['income_statements'][$index],
            'cash_flow' => $result['cash_flows'][$index],
            'balance_sheet' => $result['balance_sheets'][$index],
            'profitability' => $result['yearly_projections'][$index],
            'closing_retained_earnings' => $result['yearly_projections'][$index]['closing_retained_earnings'],
            'units_sold' => $result['yearly_projections'][$index]['units_sold'],
        ];
        $map = [
            'TotalRevenue_woTax' => $actual['income']['operating_revenue'],
            'TotalVC_woTax' => -$actual['income']['raw_materials_cost'],
            'ValueOfAssetsSold' => $actual['income']['assets_sold'],
            'VATin' => $actual['income']['vat_collected'],
            'TotalRevenueBeforeTax' => $actual['income']['total_revenue'],
            'ValueOfAssetsBought' => $actual['income']['assets_bought'],
            'AccDepr' => $actual['income']['depreciation'],
            'VATout' => $actual['income']['vat_paid'],
            'TotalExpensesBeforeTax' => $actual['income']['total_expenses'],
            'ProfitBeforeTax' => $actual['income']['profit_before_vat'],
            'PendingVAT' => $actual['income']['vat_due'],
            'ProfitAfterTax' => $actual['income']['net_profit'],
            'TotalFC_woPayments' => -$actual['cash_flow']['operating_costs'],
            'CashFlowFromOperating' => $actual['cash_flow']['operating_total'],
            'CashFlowFromInvesting' => $actual['cash_flow']['investing_total'],
            'CashFlowFromFinancing' => $actual['cash_flow']['financing_total'],
            'NetCashFlow' => $actual['cash_flow']['net_cash_flow'],
            'CashFlowBeginning' => $actual['cash_flow']['opening_cash'],
            'CashFlowEnding' => $actual['cash_flow']['closing_cash'],
            'TotalLiabilities' => $actual['balance_sheet']['total_liabilities'],
            'TotalLiabsToEquity' => $actual['balance_sheet']['liabilities_to_equity'],
            'TotalAssets' => $actual['balance_sheet']['total_assets'],
            'TotalOwnersEquity' => $actual['balance_sheet']['total_equity'],
            'RetainedEarnings' => $actual['closing_retained_earnings'],
            'FinalTotalRevenue' => $actual['profitability']['total_revenue'],
            'FinalTotalExpenses' => $actual['profitability']['total_expenses'],
            'FinalTotalProfit' => $actual['profitability']['profit'],
            'TotalUnitsSold' => $actual['units_sold'],
        ];

        foreach ($map as $key => $value) {
            expectClose($value, $year[$key], 'year '.($index + 1)." {$key}");
        }

        expectClose(
            $actual['balance_sheet']['long_term_assets'],
            $year['LongTermAssets'] - $year['CashLongTermAssets'],
            'year '.($index + 1).' long-term assets',
        );
    }

    expect($result['fixed_costs'])->toHaveCount(count($legacy['fixedCosts']));

    foreach ($legacy['fixedCosts'] as $index => $cost) {
        expect($result['fixed_costs'][$index]['name'])->toBe($cost['Name']);
        expectClose($result['fixed_costs'][$index]['monthly_amount'], $cost['Amount'], "fixed cost {$cost['Name']}");
    }

    $pricing = collect($result['product_pricing'])->flatMap(fn (array $year) => $year['products'])->values();
    expect($pricing)->toHaveCount(count($legacy['pricing']));

    foreach ($legacy['pricing'] as $index => $price) {
        expectClose($pricing[$index]['break_even_price'], $price['breakEven'], "break-even {$price['name']}");

        if ($price['margin'] === 'na') {
            expect($pricing[$index]['profit_margin'])->toBeNull();
        } else {
            expectClose($pricing[$index]['profit_margin'], $price['margin'], "margin {$price['name']}");
        }
    }

    $legacyCharts = collect($legacy['charts'])->keyBy('div');
    expect(array_map(fn (array $slice) => [$slice['label'], $slice['value']], $result['charts']['setup_costs']))
        ->toEqual($legacyCharts->get('chartSetupCosts')['data'] ?? []);
    expect(array_map(fn (array $row) => [(string) $row['year'], $row['revenue'], $row['expenses'], $row['profit']], $result['charts']['profitability']))
        ->toEqual($legacy['chartProfitibility']);
})->with(legacyFixtures());
