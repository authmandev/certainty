<?php

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\User;
use App\Services\Studies\StudyInputSchema;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $this->get(route('studies.create'))->assertRedirect(route('login'));
    $this->post(route('studies.store'))->assertRedirect(route('login'));
});

test('administrators cannot create studies', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post(route('studies.store'), [
            'title' => 'دراسة',
            'project_name' => 'مشروع',
        ])
        ->assertForbidden();

    expect(Study::count())->toBe(0);
});

test('customers see the study price on the create page', function () {
    config(['certainty.study_price' => 1200, 'certainty.currency' => 'SAR']);

    $this->actingAs(User::factory()->create())
        ->get(route('studies.create'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('studies/Create')
            ->where('price', 1200)
            ->where('currency', 'SAR'),
        );
});

test('a study needs a title and a project name', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('studies.store'), [])
        ->assertSessionHasErrors([
            'title' => 'حقل اسم الدراسة مطلوب.',
            'project_name' => 'حقل اسم المشروع مطلوب.',
        ]);

    expect(Study::count())->toBe(0);
});

test('customers can create a draft study with a pending payment', function () {
    $this->freezeSecond();
    config([
        'certainty.study_price' => 1200,
        'certainty.currency' => 'SAR',
        'certainty.draft_duration_days' => 60,
    ]);
    $customer = User::factory()->create();

    $response = $this->actingAs($customer)->post(route('studies.store'), [
        'title' => 'دراسة جدوى مقهى',
        'project_name' => 'مقهى الصباح',
    ]);

    $study = Study::sole();
    $response->assertRedirect(route('studies.edit', $study));

    expect($study)
        ->user_id->toBe($customer->id)
        ->title->toBe('دراسة جدوى مقهى')
        ->project_name->toBe('مقهى الصباح')
        ->status->toBe(StudyStatus::Draft)
        ->reference->toBe('CS-'.str_pad((string) $study->id, 6, '0', STR_PAD_LEFT))
        ->calculation_version->toBe('cassandra_legacy_v1')
        ->and($study->expires_at->equalTo(now()->addDays(60)))->toBeTrue()
        ->and($study->payment)
        ->user_id->toBe($customer->id)
        ->amount->toBe('1200.00')
        ->currency->toBe('SAR')
        ->status->toBe(PaymentStatus::Pending)
        ->reference_number->toBe('PAY-'.str_pad((string) $study->payment->id, 6, '0', STR_PAD_LEFT))
        ->and($study->input->input_data)->toBe(StudyInputSchema::defaults());
});

test('the draft duration and price come from configuration', function () {
    $this->freezeSecond();
    config(['certainty.study_price' => 750, 'certainty.draft_duration_days' => 30]);

    $this->actingAs(User::factory()->create())->post(route('studies.store'), [
        'title' => 'دراسة',
        'project_name' => 'مشروع',
    ]);

    $study = Study::sole();
    expect($study->expires_at->equalTo(now()->addDays(30)))->toBeTrue()
        ->and($study->payment->amount)->toBe('750.00');
});

test('a customer can create several independent studies', function () {
    $customer = User::factory()->create();

    foreach (['الأولى', 'الثانية'] as $title) {
        $this->actingAs($customer)->post(route('studies.store'), [
            'title' => $title,
            'project_name' => 'مشروع',
        ]);
    }

    expect($customer->studies()->count())->toBe(2)
        ->and($customer->studies()->with('payment')->get()->pluck('payment.reference_number')->unique())->toHaveCount(2);
});
