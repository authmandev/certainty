<?php

use App\Enums\AuditAction;
use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\StudyExtension;
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
    $this->admin = User::factory()->admin()->create();
});

test('administrators can list and filter studies', function () {
    $draft = Study::factory()->create(['title' => 'دراسة مخبز']);
    $expired = Study::factory()->expired()->create();
    $finalized = Study::factory()->finalized()->create();
    StudyPayment::factory()->for($finalized)->create(['user_id' => $finalized->user_id, 'status' => PaymentStatus::Approved]);

    $this->actingAs($this->admin)
        ->get(route('admin.studies.index'))
        ->assertInertia(fn (Assert $page) => $page->component('admin/studies/Index')->has('studies.data', 3));

    $this->actingAs($this->admin)
        ->get(route('admin.studies.index', ['status' => 'expired']))
        ->assertInertia(fn (Assert $page) => $page->has('studies.data', 1)->where('studies.data.0.id', $expired->id));

    $this->actingAs($this->admin)
        ->get(route('admin.studies.index', ['payment' => 'approved']))
        ->assertInertia(fn (Assert $page) => $page->has('studies.data', 1)->where('studies.data.0.id', $finalized->id));

    $this->actingAs($this->admin)
        ->get(route('admin.studies.index', ['search' => 'مخبز']))
        ->assertInertia(fn (Assert $page) => $page->has('studies.data', 1)->where('studies.data.0.id', $draft->id));

    $this->actingAs($this->admin)
        ->get(route('admin.studies.index', ['search' => $expired->user->email]))
        ->assertInertia(fn (Assert $page) => $page->has('studies.data', 1)->where('studies.data.0.id', $expired->id));
});

test('administrators can inspect a study read-only', function () {
    $payment = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR']);
    StudyInput::factory()->for($payment->study)->create(['input_data' => StudyInputSchema::normalize(completeStudyPayload())]);
    StudyAuditLog::record($payment->study, $this->admin, AuditAction::DraftExtended, reason: 'اختبار');

    $this->actingAs($this->admin)
        ->get(route('admin.studies.show', $payment->study))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/studies/Show')
            ->where('customer.email', $payment->user->email)
            ->where('input.products.0.name', 'قهوة')
            ->where('progress.percentage', 100)
            ->where('preview.summary.total_capital', 61200)
            ->has('auditLogs.data', 1)
            ->where('labels.sector.isic4-i', fn ($label) => is_string($label))
            ->where('canExtend', true)
            ->where('canReopen', false),
        );
});

test('extending an expired draft counts the days from now', function () {
    $this->freezeSecond();
    $study = Study::factory()->expired()->create();
    $study->forceFill(['status' => StudyStatus::Expired])->save();
    $previous = $study->expires_at;

    $this->actingAs($this->admin)
        ->post(route('admin.studies.extend', $study), ['days' => 10, 'reason' => 'طلب العميل وقتًا إضافيًا'])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('admin.studies.show', $study));

    $study->refresh();
    $extension = StudyExtension::sole();
    expect($study->status)->toBe(StudyStatus::Draft)
        ->and($study->isEditable())->toBeTrue()
        ->and($study->expires_at->equalTo(now()->addDays(10)))->toBeTrue()
        ->and($extension->previous_expires_at->equalTo($previous))->toBeTrue()
        ->and($extension->extension_days)->toBe(10)
        ->and($extension->approved_by)->toBe($this->admin->id);

    $log = StudyAuditLog::sole();
    expect($log->action)->toBe(AuditAction::DraftExtended)
        ->and($log->actor_id)->toBe($this->admin->id)
        ->and($log->reason)->toBe('طلب العميل وقتًا إضافيًا')
        ->and($log->old_values['status'])->toBe('expired')
        ->and($log->new_values['extension_days'])->toBe(10);
});

test('extending a draft that has not expired adds the days to its deadline', function () {
    $study = Study::factory()->create(['expires_at' => now()->addDays(5)]);
    $deadline = $study->expires_at;

    $this->actingAs($this->admin)
        ->post(route('admin.studies.extend', $study), ['days' => 7, 'reason' => 'تمديد مسبق'])
        ->assertSessionHasNoErrors();

    expect($study->fresh()->expires_at->equalTo($deadline->addDays(7)))->toBeTrue();
});

test('an extension needs a valid number of days and a reason', function (array $data, string $field) {
    $study = Study::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.studies.extend', $study), $data)
        ->assertSessionHasErrors($field);

    expect(StudyExtension::count())->toBe(0);
})->with([
    'no reason' => [['days' => 7, 'reason' => ''], 'reason'],
    'zero days' => [['days' => 0, 'reason' => 'سبب كافٍ'], 'days'],
    'too many days' => [['days' => 400, 'reason' => 'سبب كافٍ'], 'days'],
]);

test('a finalized study cannot be extended', function () {
    $study = Study::factory()->finalized()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.studies.extend', $study), ['days' => 7, 'reason' => 'سبب كافٍ'])
        ->assertSessionHasErrors('days');

    expect($study->fresh()->status)->toBe(StudyStatus::Finalized);
});

test('reopening a finalized study keeps its reports and lets the customer finalize a new version', function () {
    $payment = StudyPayment::factory()->create(['amount' => 1200, 'currency' => 'SAR', 'status' => PaymentStatus::Approved]);
    $study = $payment->study;
    StudyInput::factory()->for($study)->create(['input_data' => StudyInputSchema::normalize(completeStudyPayload())]);
    $this->actingAs($study->user)->post(route('studies.finalize', $study), ['confirmed' => true]);

    $this->actingAs($this->admin)
        ->post(route('admin.studies.reopen', $study), ['days' => 14, 'reason' => 'خطأ في بيانات القرض'])
        ->assertSessionHasNoErrors();

    $study->refresh();
    expect($study->status)->toBe(StudyStatus::Draft)
        ->and($study->finalized_at)->toBeNull()
        ->and($study->isEditable())->toBeTrue()
        ->and($study->current_version)->toBe(1)
        ->and(StudyReport::count())->toBe(1);

    $log = StudyAuditLog::query()->where('action', AuditAction::StudyReopened)->sole();
    expect($log->old_values['report_version'])->toBe(1)
        ->and($log->reason)->toBe('خطأ في بيانات القرض');

    $this->actingAs($study->user)
        ->put(route('studies.update', $study), completeStudyPayload())
        ->assertSessionHasNoErrors();

    $this->actingAs($study->user)->post(route('studies.finalize', $study), ['confirmed' => true])
        ->assertSessionHasNoErrors();

    expect(StudyReport::query()->orderBy('version')->pluck('version')->all())->toBe([1, 2])
        ->and($study->fresh()->current_version)->toBe(2);
});

test('only a finalized study can be reopened, and only with a reason', function () {
    $draft = Study::factory()->create();
    $finalized = Study::factory()->finalized()->create();

    $this->actingAs($this->admin)
        ->post(route('admin.studies.reopen', $draft), ['days' => 14, 'reason' => 'سبب كافٍ'])
        ->assertSessionHasErrors('reason');

    $this->actingAs($this->admin)
        ->post(route('admin.studies.reopen', $finalized), ['days' => 14, 'reason' => ''])
        ->assertSessionHasErrors('reason');

    expect($finalized->fresh()->status)->toBe(StudyStatus::Finalized)
        ->and(StudyAuditLog::count())->toBe(0);
});

test('customers cannot manage studies', function (string $method, string $route) {
    $study = Study::factory()->finalized()->create();

    $this->actingAs($study->user)
        ->{$method}(route($route, $study), ['days' => 7, 'reason' => 'سبب كافٍ'])
        ->assertForbidden();

    expect($study->fresh()->status)->toBe(StudyStatus::Finalized);
})->with([
    ['get', 'admin.studies.show'],
    ['post', 'admin.studies.extend'],
    ['post', 'admin.studies.reopen'],
]);
