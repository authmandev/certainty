<?php

use App\Services\Calculation\CassandraLegacyEngine;
use App\Services\Calculation\LegacyForm;

/**
 * Each legacy quirk from BRD §25.7 is kept on purpose; these tests pin it.
 */
function quirkForm(array $overrides = []): LegacyForm
{
    return new LegacyForm(...array_merge([
        'vat' => 15,
        'studyYears' => 3,
        'marketValue' => null,
        'loans' => [],
        'assets' => [['name' => 'معدات', 'value' => 100000, 'life_span' => 5, 'depreciation' => 20, 'includes_vat' => false]],
        'employees' => [],
        'fixedCosts' => [['name' => 'إيجار', 'amount' => 5000, 'includes_vat' => true]],
        'products' => [['name' => 'منتج', 'vc' => 10, 'quantity' => 1000, 'price' => 40, 'capacity' => 1500, 'yearly_increase' => 10]],
    ], $overrides));
}

function calculateQuirk(array $overrides = []): array
{
    return (new CassandraLegacyEngine)->calculate(quirkForm($overrides));
}

test('Q-01: the loan instalment booked as a fixed cost is rounded to a whole number', function () {
    $result = calculateQuirk(['loans' => [['bank' => 'بنك', 'amount' => 100000, 'interest' => 6, 'years' => 4]]]);
    $loan = $result['financing']['loans'][0];
    $instalment = collect($result['fixed_costs'])->firstWhere('is_loan_instalment', true);

    expect($loan['monthly_instalment'])->toEqualWithDelta(2348.50, 0.01)
        ->and($instalment['monthly_amount'])->toBe(2349.0)
        ->and($loan['total_payable'])->toEqualWithDelta($loan['monthly_instalment'] * 48, 1e-6);
});

test('Q-02: VAT is taken from VAT-inclusive costs as amount × rate', function () {
    $rent = calculateQuirk()['fixed_costs'][0];

    expect($rent['monthly_vat'])->toEqualWithDelta(5000 * 0.15, 1e-9)
        ->and($rent['monthly_vat'])->not->toEqualWithDelta(5000 * 0.15 / 1.15, 1e-6);
});

test('Q-03: loan instalments never carry VAT', function () {
    $result = calculateQuirk(['loans' => [['bank' => 'بنك', 'amount' => 50000, 'interest' => 0, 'years' => 2]]]);

    expect(collect($result['fixed_costs'])->firstWhere('is_loan_instalment', true)['monthly_vat'])->toBe(0.0);
});

test('Q-05: capital recovered within the first year is reported as not available', function () {
    $result = calculateQuirk([
        'assets' => [['name' => 'معدات', 'value' => 1000, 'life_span' => 5, 'depreciation' => 10, 'includes_vat' => false]],
        'fixedCosts' => [],
    ]);

    expect($result['indicators']['capital_recovery'])->toBe(['status' => 'not_available', 'years' => null, 'label' => 'غير متوفر']);
});

test('Q-06: the VAT flag on a setup cost does not change any figure', function () {
    $withoutVat = calculateQuirk();
    $withVat = calculateQuirk(['assets' => [['name' => 'معدات', 'value' => 100000, 'life_span' => 5, 'depreciation' => 20, 'includes_vat' => true]]]);

    expect($withVat)->toEqual($withoutVat);
});

test('Q-07: balance-sheet depreciation is the current year only, on a declining balance', function () {
    $sheets = calculateQuirk()['balance_sheets'];

    expect($sheets[0]['depreciation'])->toEqualWithDelta(-20000, 1e-6)
        ->and($sheets[1]['depreciation'])->toEqualWithDelta(-16000, 1e-6);
});

test('Q-08: market share uses VAT-inclusive sales', function () {
    $result = calculateQuirk(['marketValue' => 10000000]);

    expect($result['indicators']['market_share'])
        ->toEqualWithDelta($result['indicators']['average_sales'] / 10000000 * 100, 1e-9)
        ->and($result['indicators']['average_sales'])->toEqualWithDelta($result['indicators']['total_sales'] / 3, 1e-6)
        ->and($result['yearly_projections'][0]['sales_with_vat'])->toEqualWithDelta(1000 * 40 * 1.15 * 12, 1e-6);
});
