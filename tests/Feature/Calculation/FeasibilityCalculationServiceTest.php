<?php

use App\Models\Study;
use App\Models\StudyInput;
use App\Models\StudyPayment;
use App\Services\Calculation\FeasibilityCalculationService;
use App\Services\Calculation\LegacyFormMapper;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
});

function studyWithInput(array $input, string $feeCurrency = 'SAR'): Study
{
    $payment = StudyPayment::factory()->create(['amount' => 1200, 'currency' => $feeCurrency]);
    StudyInput::factory()->for($payment->study)->create(['input_data' => StudyInputSchema::normalize($input)]);

    return $payment->study->fresh();
}

function calculateStudy(Study $study): array
{
    return app(FeasibilityCalculationService::class)->calculate($study);
}

test('the result follows the documented contract', function () {
    $result = calculateStudy(studyWithInput(completeStudyPayload()));

    expect($result)->toHaveKeys([
        'calculation_version', 'summary', 'capital', 'financing', 'yearly_projections',
        'income_statements', 'cash_flows', 'balance_sheets', 'product_pricing',
        'indicators', 'charts', 'warnings', 'currency', 'decimals',
    ])
        ->and($result['calculation_version'])->toBe('cassandra_legacy_v1')
        ->and($result['yearly_projections'])->toHaveCount(3)
        ->and($result['currency'])->toBe('SAR');
});

test('the study fee is added exactly once as a non-depreciable setup cost', function () {
    $study = studyWithInput(completeStudyPayload());

    $first = calculateStudy($study);
    $second = calculateStudy($study);

    $feeRows = collect($first['capital']['setup_costs'])->where('name', LegacyFormMapper::FEE_NAME);
    expect($feeRows)->toHaveCount(1)
        ->and($feeRows->first())->toMatchArray(['value' => 1200.0, 'life_span' => 0.0, 'depreciation_rate' => 0.0])
        ->and($first['summary']['total_capital'])->toBe(60000.0 + 1200.0)
        ->and($second)->toEqual($first)
        ->and($study->fresh()->input->input_data['assets'])->toHaveCount(1);
});

test('a fee in another currency is not mixed into the figures', function () {
    $payload = completeStudyPayload();
    $payload['settings']['currency'] = 'USD';

    $result = calculateStudy(studyWithInput($payload));

    expect(collect($result['capital']['setup_costs'])->where('name', LegacyFormMapper::FEE_NAME))->toBeEmpty()
        ->and(collect($result['warnings'])->pluck('code'))->toContain('fee_currency_mismatch');
});

test('rows without a description use their category label', function () {
    $result = calculateStudy(studyWithInput(completeStudyPayload()));

    expect(collect($result['fixed_costs'])->pluck('name'))->toContain('إيجار')
        ->and($result['employees'][0]['title'])->toBe('باريستا');
});

test('incomplete or inconsistent input produces warnings instead of errors', function () {
    $payload = completeStudyPayload();
    $payload['settings']['vat_percent'] = null;
    $payload['settings']['study_years'] = null;
    $payload['loans'] = [['lender' => null, 'amount' => 1000, 'interest_rate' => 5, 'years' => 2]];
    $payload['products'][0]['monthly_capacity'] = 100;

    $result = calculateStudy(studyWithInput($payload));

    expect(collect($result['warnings'])->pluck('code')->all())
        ->toContain('vat_not_set', 'study_years_not_set', 'row_ignored', 'capacity_below_quantity')
        ->and($result['yearly_projections'])->toHaveCount(2)
        ->and($result['financing']['loans'])->toBeEmpty();
});

test('loss years are reported as informational warnings', function () {
    $payload = completeStudyPayload();
    $payload['products'][0]['monthly_quantity'] = 0;

    $warnings = collect(calculateStudy(studyWithInput($payload))['warnings']);

    expect($warnings->where('code', 'loss_year'))->toHaveCount(3);
});

test('an empty draft can be calculated', function () {
    $result = calculateStudy(studyWithInput(StudyInputSchema::defaults()));

    expect($result['summary']['total_capital'])->toBe(1200.0)
        ->and($result['yearly_projections'])->toHaveCount(2);
});

test('calculating never writes to the database', function () {
    $study = studyWithInput(completeStudyPayload());
    $writes = 0;
    DB::listen(function ($query) use (&$writes) {
        if (preg_match('/^\s*(insert|update|delete)/i', $query->sql)) {
            $writes++;
        }
    });

    calculateStudy($study);

    expect($writes)->toBe(0);
});
