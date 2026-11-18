<?php

use App\Models\Study;
use App\Models\StudyPayment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('customers can view their own study', function () {
    $payment = StudyPayment::factory()->create();

    $this->actingAs($payment->user)
        ->get(route('studies.show', $payment->study))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('studies/Show')
            ->where('study.data.id', $payment->study->id)
            ->where('study.data.payment.reference_number', $payment->reference_number),
        );
});

test("customers cannot see another customer's study", function () {
    $study = Study::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('studies.show', $study))
        ->assertNotFound();
});
