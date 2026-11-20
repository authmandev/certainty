<?php

use App\Models\Study;
use App\Services\Studies\CalculateStudyProgressService;
use App\Services\Studies\StudyInputSchema;

function progressFor(Study $study, array $input): array
{
    return app(CalculateStudyProgressService::class)
        ->calculate($study, StudyInputSchema::normalize($input))
        ->toArray();
}

test('a new study only counts its names and default settings', function () {
    $study = Study::factory()->make();

    $progress = progressFor($study, StudyInputSchema::defaults());

    // Basic: title, project name, currency and decimals = 4 of 12; other sections empty.
    expect($progress['percentage'])->toBe((int) round((4 / 12) / 5 * 100))
        ->and(collect($progress['sections'])->every(fn (array $section) => ! $section['is_complete']))->toBeTrue();
});

test('a fully filled study is 100% complete', function () {
    $study = Study::factory()->make();

    $progress = progressFor($study, completeStudyPayload());

    expect($progress['percentage'])->toBe(100)
        ->and(collect($progress['sections'])->every(fn (array $section) => $section['is_complete']))->toBeTrue();
});

test('an incomplete optional row lowers its section and is listed as missing', function () {
    $study = Study::factory()->make();
    $payload = completeStudyPayload();
    $payload['loans'] = [['lender' => 'بنك', 'amount' => 100000, 'interest_rate' => null, 'years' => 5]];

    $costs = collect(progressFor($study, $payload)['sections'])->firstWhere('key', 'costs');

    expect($costs)
        ->is_complete->toBeFalse()
        ->missing->toBe(['بيانات القروض مكتملة']);
});

test('an "other" category needs a description', function () {
    $study = Study::factory()->make();
    $payload = completeStudyPayload();
    $payload['project']['project_type_code'] = 'other';

    $basic = collect(progressFor($study, $payload)['sections'])->firstWhere('key', 'basic');

    expect($basic['missing'])->toBe(['نوع المشروع']);
});
