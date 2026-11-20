<?php

use App\Enums\AuditAction;
use App\Enums\PaymentStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyAuditLog;
use App\Models\StudyInput;
use App\Models\StudyPayment;
use App\Models\StudyReport;
use App\Models\User;
use App\Services\Reports\StudyReportPdfGenerator;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\LaravelPdf\Facades\Pdf;

test('submitting a receipt and reviewing it are recorded', function () {
    Storage::fake('local');
    $payment = StudyPayment::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($payment->user)->post(route('studies.receipts.store', $payment->study), [
        'receipt' => UploadedFile::fake()->image('receipt.png'),
    ]);
    $this->actingAs($admin)->post(route('admin.payments.reject', $payment), ['reason' => 'المبلغ غير مطابق']);

    $entries = StudyAuditLog::query()->oldest('id')->get();
    expect($entries->pluck('action')->all())->toBe([AuditAction::ReceiptSubmitted, AuditAction::PaymentRejected])
        ->and($entries[0]->actor_id)->toBe($payment->user_id)
        ->and($entries[0]->new_values['payment_status'])->toBe('under_review')
        ->and($entries[1]->actor_id)->toBe($admin->id)
        ->and($entries[1]->reason)->toBe('المبلغ غير مطابق')
        ->and($entries[1]->new_values['payment_status'])->toBe('rejected');
});

test('approving a payment is recorded', function () {
    $receipt = PaymentReceipt::factory()->create();

    $this->actingAs(User::factory()->admin()->create())->post(route('admin.payments.approve', $receipt->payment));

    expect(StudyAuditLog::sole())
        ->action->toBe(AuditAction::PaymentApproved)
        ->study_id->toBe($receipt->payment->study_id);
});

test('finalizing a study and generating its report are recorded', function () {
    $this->seed(ReferenceOptionSeeder::class);
    Queue::fake();
    Storage::fake('local');
    Pdf::fake();
    $payment = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR', 'status' => PaymentStatus::Approved]);
    StudyInput::factory()->for($payment->study)->create(['input_data' => StudyInputSchema::normalize(completeStudyPayload())]);

    $this->actingAs($payment->user)->post(route('studies.finalize', $payment->study), ['confirmed' => true]);
    app(StudyReportPdfGenerator::class)->ensureGenerated(StudyReport::sole());

    $entries = StudyAuditLog::query()->oldest('id')->get();
    expect($entries->pluck('action')->all())->toBe([AuditAction::StudyFinalized, AuditAction::ReportGenerated])
        ->and($entries[0]->new_values['report_version'])->toBe(1)
        ->and($entries[1]->actor_id)->toBeNull();
});

test('administrators can browse and filter the action history', function () {
    $admin = User::factory()->admin()->create();
    $extended = StudyAuditLog::factory()->create(['action' => AuditAction::DraftExtended]);
    StudyAuditLog::factory()->create(['action' => AuditAction::PaymentApproved]);

    $this->actingAs($admin)
        ->get(route('admin.audit-log'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/audit/Index')->has('logs.data', 2));

    $this->actingAs($admin)
        ->get(route('admin.audit-log', ['action' => 'draft_extended']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('logs.data', 1)
            ->where('logs.data.0.id', $extended->id)
            ->where('logs.data.0.study.reference', $extended->study->reference));
});

test('customers cannot see the action history', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.audit-log'))
        ->assertForbidden();
});
