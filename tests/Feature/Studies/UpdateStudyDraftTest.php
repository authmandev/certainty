<?php

use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\User;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;

beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
});

test('a partial draft is saved and its progress stored', function () {
    $study = Study::factory()->create();
    $payload = completeStudyPayload();
    $payload['market'] = array_map(fn () => null, $payload['market']);
    $payload['swot'] = array_map(fn () => null, $payload['swot']);

    $this->actingAs($study->user)
        ->from(route('studies.edit', $study))
        ->put(route('studies.update', $study), $payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('studies.edit', $study));

    $study->refresh();
    expect($study->status)->toBe(StudyStatus::Draft)
        ->and($study->completion_percentage)->toBe(60)
        ->and($study->title)->toBe('دراسة جدوى مقهى')
        ->and($study->input->input_data['owners'][0]['name'])->toBe('سارة')
        ->and($study->input->input_data['market']['name'])->toBeNull();
});

test('saving restores everything that was entered', function () {
    $study = Study::factory()->create();

    $this->actingAs($study->user)->put(route('studies.update', $study), completeStudyPayload());

    expect($study->fresh()->input->input_data)->toEqual(StudyInputSchema::normalize(completeStudyPayload()))
        ->and($study->fresh()->completion_percentage)->toBe(100)
        ->and($study->fresh()->status)->toBe(StudyStatus::Draft);
});

test('rows can be removed by saving without them', function () {
    $study = Study::factory()->create();
    $payload = completeStudyPayload();
    $this->actingAs($study->user)->put(route('studies.update', $study), $payload);

    $payload['assets'] = [];
    $this->actingAs($study->user)->put(route('studies.update', $study), $payload);

    expect($study->fresh()->input->input_data['assets'])->toBe([]);
});

test('invalid values are rejected with arabic messages', function (string $path, mixed $value, string $errorKey, string $message) {
    $study = Study::factory()->create();
    $payload = completeStudyPayload();
    data_set($payload, $path, $value);

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), $payload)
        ->assertSessionHasErrors([$errorKey => $message]);

    expect($study->fresh()->input)->toBeNull();
})->with([
    'negative asset value' => ['assets.0.value', -5, 'assets.0.value', 'يجب أن تكون قيمة حقل قيمة الأصل 0 على الأقل.'],
    'loan with no repayment years' => ['loans', [['lender' => 'بنك', 'amount' => 1000, 'interest_rate' => 5, 'years' => 0]], 'loans.0.years', 'يجب أن تكون قيمة حقل سنوات السداد 1 على الأقل.'],
    'share above 100%' => ['competitors', [['name' => 'منافس', 'share' => 120, 'characteristics' => null]], 'competitors.0.share', 'يجب ألا تتجاوز قيمة حقل الحصة السوقية 100.'],
    'vat outside legacy range' => ['settings.vat_percent', 30, 'settings.vat_percent', 'يجب ألا تتجاوز قيمة حقل نسبة ضريبة القيمة المضافة 25.'],
    'unknown sector' => ['project.sector_code', 'made-up', 'project.sector_code', 'قيمة القطاع المختارة غير صالحة.'],
    'unsupported study length' => ['settings.study_years', 4, 'settings.study_years', 'قيمة مدة الدراسة المختارة غير صالحة.'],
]);

test('owner shares cannot exceed 100% in total', function () {
    $study = Study::factory()->create();
    $payload = completeStudyPayload();
    $payload['owners'][] = ['name' => 'خالد', 'share' => 10, 'experience' => null];

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), $payload)
        ->assertSessionHasErrors(['owners' => 'يجب ألا يتجاوز مجموع حصص الملاك 100%.']);
});

test('unexpected keys are not stored', function () {
    $study = Study::factory()->create();
    $payload = completeStudyPayload() + ['status' => 'finalized', 'completion_percentage' => 100, 'user_id' => 999];
    $payload['settings']['calculation_override'] = true;

    $this->actingAs($study->user)->put(route('studies.update', $study), $payload);

    $study->refresh();
    expect($study->status)->toBe(StudyStatus::Draft)
        ->and($study->user_id)->not->toBe(999)
        ->and($study->input->input_data['settings'])->not->toHaveKey('calculation_override');
});

test('expired drafts cannot be saved', function () {
    $study = Study::factory()->expired()->create();

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), completeStudyPayload())
        ->assertForbidden();

    expect($study->fresh()->input)->toBeNull();
});

test("customers cannot save another customer's draft", function () {
    $study = Study::factory()->create();

    $this->actingAs(User::factory()->create())
        ->put(route('studies.update', $study), completeStudyPayload())
        ->assertNotFound();
});
