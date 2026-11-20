<?php

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Jobs\GenerateStudyReportPdf;
use App\Models\Study;
use App\Models\StudyInput;
use App\Models\StudyPayment;
use App\Models\StudyReport;
use App\Models\User;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
    Queue::fake();
});

/**
 * A complete draft whose payment has been approved.
 */
function finalizableStudy(array $input = [], PaymentStatus $payment = PaymentStatus::Approved): Study
{
    $study = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR', 'status' => $payment])->study;
    StudyInput::factory()->for($study)->create(['input_data' => StudyInputSchema::normalize(array_replace_recursive(completeStudyPayload(), $input))]);

    return $study->fresh();
}

test('a complete, paid study can be finalized', function () {
    $study = finalizableStudy();

    $this->actingAs($study->user)
        ->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('studies.show', $study));

    $study->refresh();
    $report = StudyReport::sole();
    expect($study->status)->toBe(StudyStatus::Finalized)
        ->and($study->finalized_at)->not->toBeNull()
        ->and($study->current_version)->toBe(1)
        ->and($report->version)->toBe(1)
        ->and($report->finalized_by)->toBe($study->user_id)
        ->and($report->calculation_version)->toBe('cassandra_legacy_v1')
        ->and($report->input_snapshot['title'])->toBe($study->title)
        ->and($report->input_snapshot['labels']['sector'])->not->toBeNull()
        ->and($report->input_snapshot['products'][0]['name'])->toBe('قهوة')
        ->and($report->calculation_snapshot['summary']['total_capital'])->toEqual(61200)
        ->and($report->pdf_path)->toBeNull();
    Queue::assertPushed(GenerateStudyReportPdf::class, fn (GenerateStudyReportPdf $job) => $job->report->is($report));
});

test('finalizing needs explicit confirmation', function () {
    $study = finalizableStudy();

    $this->actingAs($study->user)
        ->post(route('studies.finalize', $study))
        ->assertSessionHasErrors('confirmed');

    expect($study->fresh()->status)->toBe(StudyStatus::Draft)
        ->and(StudyReport::count())->toBe(0);
});

test('a study that is not ready cannot be finalized', function (Study $study) {
    $this->actingAs($study->user)
        ->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertSessionHasErrors('finalize');

    expect($study->fresh()->status)->toBe(StudyStatus::Draft)
        ->and(StudyReport::count())->toBe(0);
    Queue::assertNothingPushed();
})->with([
    'payment pending' => fn () => finalizableStudy(payment: PaymentStatus::Pending),
    'payment under review' => fn () => finalizableStudy(payment: PaymentStatus::UnderReview),
    'incomplete data' => fn () => finalizableStudy(['swot' => ['threats' => null]]),
    'expired' => function () {
        $study = finalizableStudy();
        $study->forceFill(['expires_at' => now()->subDay()])->save();

        return $study;
    },
]);

test('finalizing twice keeps a single report version', function () {
    $study = finalizableStudy();

    $this->actingAs($study->user)->post(route('studies.finalize', $study), ['confirmed' => true]);
    $this->actingAs($study->user)->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertSessionHasNoErrors();

    expect(StudyReport::count())->toBe(1)
        ->and($study->fresh()->current_version)->toBe(1);
    Queue::assertPushed(GenerateStudyReportPdf::class, 1);
});

test("customers cannot finalize another customer's study", function () {
    $study = finalizableStudy();

    $this->actingAs(User::factory()->create())
        ->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertNotFound();

    expect($study->fresh()->status)->toBe(StudyStatus::Draft);
});

test('a finalized study can no longer be edited', function () {
    $study = finalizableStudy();
    $this->actingAs($study->user)->post(route('studies.finalize', $study), ['confirmed' => true]);

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), completeStudyPayload())
        ->assertForbidden();
});

test('the study page lists what is still needed before finalizing', function () {
    $study = finalizableStudy(payment: PaymentStatus::Pending);

    $this->actingAs($study->user)
        ->get(route('studies.show', $study))
        ->assertInertia(fn (Assert $page) => $page
            ->where('finalization.ready', false)
            ->where('finalization.checks', fn ($checks) => collect($checks)->firstWhere('key', 'payment_approved')['passed'] === false
                && collect($checks)->firstWhere('key', 'complete')['passed'] === true),
        );
});
