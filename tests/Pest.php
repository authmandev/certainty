<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * A study payload in which every required item is filled in. Codes must exist in ReferenceOptionSeeder.
 *
 * @return array<string, mixed>
 */
function completeStudyPayload(): array
{
    return [
        'title' => 'دراسة جدوى مقهى',
        'project_name' => 'مقهى الصباح',
        'settings' => ['currency' => 'SAR', 'decimals' => 2, 'vat_percent' => 15, 'study_years' => 3],
        'project' => [
            'start_date' => '2027-01-01',
            'description' => 'مقهى مختص في حي سكني.',
            'organizational_feasibility' => 'يديره المالك بخبرة خمس سنوات.',
            'sector_code' => 'isic4-i',
            'project_type_code' => 'restaurant-cafe',
            'project_type_other' => null,
        ],
        'owners' => [['key' => 'o1', 'name' => 'سارة', 'share' => 100, 'experience' => 'إدارة مقاهٍ']],
        'market' => [
            'name' => 'سوق المقاهي في الرياض',
            'size' => 50000000,
            'description' => 'سوق نامٍ.',
            'products' => 'قهوة مختصة.',
            'target_customers' => 'الشباب والموظفون.',
            'suppliers' => 'محامص محلية.',
            'marketing_plan' => 'وسائل التواصل الاجتماعي.',
            'competition_analysis' => 'منافسة متوسطة.',
        ],
        'competitors' => [],
        'assets' => [['key' => 'a1', 'category_code' => 'machinery', 'name' => 'آلة قهوة', 'value' => 60000, 'life_span' => 5, 'depreciation_rate' => 20, 'includes_vat' => true]],
        'loans' => [],
        'fixed_costs' => [['key' => 'f1', 'category_code' => 'rent', 'name' => null, 'monthly_amount' => 8000, 'includes_vat' => false]],
        'employees' => [['key' => 'e1', 'job_code' => 'barista', 'title' => null, 'count' => 2, 'monthly_salary' => 5000]],
        'products' => [['key' => 'p1', 'name' => 'قهوة', 'unit_cost' => 6, 'unit_price' => 18, 'monthly_quantity' => 3000, 'monthly_capacity' => 4500, 'annual_growth' => 10]],
        'swot' => ['strengths' => 'جودة.', 'weaknesses' => 'رأس مال محدود.', 'opportunities' => 'نمو الطلب.', 'threats' => 'منافسة.'],
    ];
}
