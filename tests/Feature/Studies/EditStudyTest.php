<?php

use App\Models\Study;
use App\Models\StudyInput;
use App\Models\StudyPayment;
use App\Models\User;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
});

test('customers can open the editor for their draft', function () {
    $study = Study::factory()->create();

    $this->actingAs($study->user)
        ->get(route('studies.edit', $study))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('studies/Edit')
            ->where('input.settings.currency', 'SAR')
            ->has('progress.sections', 5)
            ->has('options.sectors', 18)
            ->has('options.projectTypes'),
        );
});

test("customers cannot open another customer's editor", function () {
    $this->actingAs(User::factory()->create())
        ->get(route('studies.edit', Study::factory()->create()))
        ->assertNotFound();
});

test('expired and finalized studies send the customer back to the study page', function (string $state) {
    $study = Study::factory()->{$state}()->create();

    $this->actingAs($study->user)
        ->get(route('studies.edit', $study))
        ->assertRedirect(route('studies.show', $study));
})->with(['expired', 'finalized']);

test('the editor shows a live preview of an empty draft with its warnings', function () {
    $study = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR'])->study;

    $this->actingAs($study->user)
        ->get(route('studies.edit', $study))
        ->assertInertia(fn (Assert $page) => $page
            ->where('preview.calculation_version', 'cassandra_legacy_v1')
            ->where('preview.study_years', 2)
            ->has('preview.yearly', 2)
            ->where('preview.setup_costs.0.is_study_fee', true)
            ->where('preview.warnings', fn ($warnings) => collect($warnings)->pluck('code')->contains('study_years_not_set')),
        );
});

test('saving a draft refreshes the live preview', function () {
    $study = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR'])->study;

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), completeStudyPayload())
        ->assertRedirect();

    $this->actingAs($study->user)
        ->get(route('studies.edit', $study))
        ->assertInertia(fn (Assert $page) => $page
            ->reloadOnly(['progress', 'study', 'preview'], fn (Assert $reload) => $reload
                ->missing('options')
                ->where('preview.study_years', 3)
                ->has('preview.yearly', 3)
                ->where('preview.summary.total_capital', 61200),
            ),
        );
});

test('a draft whose figures overflow shows no preview instead of failing', function () {
    $payload = completeStudyPayload();
    $payload['products'][0]['monthly_quantity'] = 1e307;
    $payload['products'][0]['monthly_capacity'] = null;
    $study = Study::factory()->create();
    StudyInput::factory()->for($study)->create(['input_data' => StudyInputSchema::normalize($payload)]);

    $this->actingAs($study->user)
        ->get(route('studies.edit', $study))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('preview', null));
});
