<?php

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Jobs\GenerateStudyReportPdf;
use App\Models\Study;
use App\Models\StudyInput;
use App\Models\StudyPayment;
use App\Models\StudyReport;
use App\Models\User;
use App\Services\Calculation\FeasibilityCalculationService;
use App\Services\Reports\StudyReportPdfGenerator;
use App\Services\Studies\StudyInputSchema;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelPdf\Facades\Pdf;

beforeEach(function () {
    $this->seed(ReferenceOptionSeeder::class);
    Storage::fake('local');
    Pdf::fake();
});

/**
 * A finalized, paid study with a report version built from the complete payload.
 */
function finalizedReport(PaymentStatus $payment = PaymentStatus::Approved): StudyReport
{
    $study = Study::factory()->finalized()->create();
    StudyPayment::factory()->for($study)->create(['user_id' => $study->user_id, 'amount' => 1200, 'currency' => 'SAR', 'status' => $payment]);
    $input = StudyInputSchema::normalize(completeStudyPayload());
    StudyInput::factory()->for($study)->create(['input_data' => $input]);

    return StudyReport::factory()->for($study)->create([
        'input_snapshot' => [...$input, 'title' => $study->title, 'project_name' => $study->project_name, 'labels' => ['sector' => 'الإقامة والطعام', 'project_type' => 'مقهى']],
        'calculation_snapshot' => app(FeasibilityCalculationService::class)->calculate($study->fresh()),
        'finalized_by' => $study->user_id,
    ]);
}

test('the queued job renders the report from its snapshots', function () {
    $report = finalizedReport();

    (new GenerateStudyReportPdf($report))->handle(app(StudyReportPdfGenerator::class));

    $report->refresh();
    expect($report->pdf_path)->toStartWith("reports/{$report->study_id}/")
        ->and($report->generated_at)->not->toBeNull();
    Storage::disk('local')->assertExists($report->pdf_path);
    Pdf::assertViewIs('reports.study');
    Pdf::assertSee([
        $report->study->title,
        'الجدوى الاقتصادية',
        'تكاليف التأسيس والانشاء',
        'رسوم إعداد دراسة الجدوى',
        'بيانات الدخل',
        'بيانات التدفق النقدي',
        'ورقة الموازنة',
        'تحليل SWOT',
        'الإقامة والطعام',
    ]);
});

test('a report is rendered only once', function () {
    $report = StudyReport::factory()->generated()->create();
    $path = $report->pdf_path;

    (new GenerateStudyReportPdf($report))->handle(app(StudyReportPdfGenerator::class));

    expect($report->fresh()->pdf_path)->toBe($path);
});

test('the customer can download the final report, rendered on demand', function () {
    $report = finalizedReport();

    $this->actingAs($report->study->user)
        ->get(route('studies.report', ['study' => $report->study, 'download' => 1]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertDownload("{$report->study->reference}-v1.pdf");

    expect($report->fresh()->pdf_path)->not->toBeNull();
});

test('administrators can open any final report', function () {
    $report = finalizedReport();
    Storage::disk('local')->put($path = 'reports/existing.pdf', '%PDF-1.4');
    $report->forceFill(['pdf_path' => $path, 'generated_at' => now()])->save();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('studies.report', $report->study))
        ->assertOk();
});

test("customers cannot download another customer's report", function () {
    $report = finalizedReport();

    $this->actingAs(User::factory()->create())
        ->get(route('studies.report', $report->study))
        ->assertNotFound();
});

test('the report is unavailable until the study is finalized and paid', function () {
    $report = finalizedReport(PaymentStatus::UnderReview);

    $this->actingAs($report->study->user)
        ->get(route('studies.report', $report->study))
        ->assertForbidden();

    $draft = StudyPayment::factory()->create(['status' => PaymentStatus::Approved])->study;

    $this->actingAs($draft->user)
        ->get(route('studies.report', $draft))
        ->assertForbidden();
});

test('guests cannot download reports', function () {
    $this->get(route('studies.report', finalizedReport()->study))
        ->assertRedirect(route('login'));
});

test('earlier report versions stay available while a reopened study is corrected', function () {
    $report = finalizedReport();
    $report->study->forceFill(['status' => StudyStatus::Draft, 'finalized_at' => null])->save();

    $this->actingAs($report->study->user)
        ->get(route('studies.report', ['study' => $report->study, 'version' => 1]))
        ->assertOk();
});
