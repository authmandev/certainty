<?php

use App\Models\Study;
use App\Models\User;
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
