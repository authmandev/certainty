<?php

use App\Enums\AuditAction;
use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\User;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\LaravelPdf\Facades\Pdf;

/**
 * The V1 acceptance scenario (BRD §22) end to end through the HTTP layer:
 * register, create and fill a study, pay by bank transfer, get approved,
 * finalize, download the report, then have it reopened and re-finalized.
 */
beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
    Storage::fake('local');
    Pdf::fake();
    config(['queue.default' => 'sync']);
});

test('a customer completes a paid study from registration to its final report', function () {
    // Register and land on the customer dashboard.
    $this->post(route('register.store'), [
        'name' => 'هند الحربي',
        'email' => 'hind@example.com',
        'password' => 'a-strong-password-123',
        'password_confirmation' => 'a-strong-password-123',
    ])->assertRedirect(route('dashboard', absolute: false));

    $customer = User::query()->where('email', 'hind@example.com')->sole();
    $this->actingAs($customer);

    // Create a study; its pending payment carries the configured price.
    $this->post(route('studies.store'), ['title' => 'دراسة جدوى مقهى', 'project_name' => 'مقهى الصباح'])
        ->assertSessionHasNoErrors();
    $study = Study::query()->whereBelongsTo($customer)->sole();
    expect($study->payment->status)->toBe(PaymentStatus::Pending)
        ->and((float) $study->payment->amount)->toBe((float) config('certainty.study_price'));

    // Save a partial draft, then the complete study; the live preview follows.
    $partial = completeStudyPayload();
    $partial['products'] = [];
    $this->put(route('studies.update', $study), $partial)->assertSessionHasNoErrors();
    expect($study->fresh()->completion_percentage)->toBeLessThan(100);

    $this->put(route('studies.update', $study), completeStudyPayload())->assertSessionHasNoErrors();
    $this->get(route('studies.edit', $study))
        ->assertInertia(fn (Assert $page) => $page
            ->where('progress.percentage', 100)
            ->where('preview.summary.total_capital', 61200));

    // Not paid yet: finalizing and the report are blocked.
    $this->post(route('studies.finalize', $study), ['confirmed' => true])->assertSessionHasErrors('finalize');
    $this->get(route('studies.report', $study))->assertForbidden();

    // Upload the bank-transfer receipt.
    $this->post(route('studies.receipts.store', $study), ['receipt' => UploadedFile::fake()->image('receipt.jpg')])
        ->assertSessionHasNoErrors();
    expect($study->payment->fresh()->status)->toBe(PaymentStatus::UnderReview);

    // An administrator approves it.
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin)->post(route('admin.payments.approve', $study->payment))->assertSessionHasNoErrors();
    expect($study->payment->fresh()->status)->toBe(PaymentStatus::Approved);

    // The customer finalizes with explicit confirmation; the PDF job runs.
    $this->actingAs($customer)->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertSessionHasNoErrors();
    $study->refresh();
    expect($study->status)->toBe(StudyStatus::Finalized)
        ->and($study->latestReport->pdf_path)->not->toBeNull();
    Pdf::assertSee(['دراسة جدوى مقهى', 'رسوم إعداد دراسة الجدوى', 'ورقة الموازنة']);

    // The finalized study is read-only, and the report downloads.
    $this->put(route('studies.update', $study), completeStudyPayload())->assertForbidden();
    $this->get(route('studies.report', ['study' => $study, 'download' => 1]))
        ->assertOk()
        ->assertDownload("{$study->reference}-v1.pdf");

    // Another customer can see none of it.
    $stranger = User::factory()->create();
    $this->actingAs($stranger)->get(route('studies.show', $study))->assertNotFound();
    $this->actingAs($stranger)->get(route('studies.report', $study))->assertNotFound();

    // An administrator reopens it; the customer corrects and re-finalizes.
    $this->actingAs($admin)->post(route('admin.studies.reopen', $study), ['days' => 7, 'reason' => 'تصحيح قيمة القرض'])
        ->assertSessionHasNoErrors();

    $corrected = completeStudyPayload();
    $corrected['assets'][0]['value'] = 70000;
    $this->actingAs($customer)->put(route('studies.update', $study), $corrected)->assertSessionHasNoErrors();
    $this->post(route('studies.finalize', $study), ['confirmed' => true])->assertSessionHasNoErrors();

    $study->refresh();
    expect($study->reports()->pluck('version')->all())->toBe([2, 1])
        ->and($study->latestReport->calculation_snapshot['summary']['total_capital'])->toEqual(71200)
        ->and($study->reports()->where('version', 1)->sole()->calculation_snapshot['summary']['total_capital'])->toEqual(61200);
    $this->get(route('studies.report', ['study' => $study, 'version' => 1]))->assertOk();

    // Every privileged step is in the action history.
    expect(StudyAuditLog::query()->whereBelongsTo($study)->oldest('id')->pluck('action')->all())->toBe([
        AuditAction::ReceiptSubmitted,
        AuditAction::PaymentApproved,
        AuditAction::StudyFinalized,
        AuditAction::ReportGenerated,
        AuditAction::StudyReopened,
        AuditAction::StudyFinalized,
        AuditAction::ReportGenerated,
    ]);
});
