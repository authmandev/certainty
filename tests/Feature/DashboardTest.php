<?php

use App\Models\Study;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('customers see the customer dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->component('Dashboard'));
});

test('administrators are redirected to the admin dashboard', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get(route('dashboard'))
        ->assertRedirect(route('admin.dashboard'));
});

test('customers only see their own studies on the dashboard', function () {
    $this->freezeTime();
    $customer = User::factory()->create();
    $ownStudy = Study::factory()->for($customer)->create(['expires_at' => now()->addDays(10)]);
    $expiredStudy = Study::factory()->for($customer)->expired()->create();
    Study::factory()->create();

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('Dashboard')
            ->has('studies.data', 2)
            ->where('studies.data.1.id', $ownStudy->id)
            ->where('studies.data.1.status.value', 'draft')
            ->where('studies.data.1.days_remaining', 10)
            ->where('studies.data.0.status.value', 'expired')
            ->where('studies.data.0.days_remaining', null),
        );
});
