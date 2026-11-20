<?php

use App\Models\Study;
use App\Models\StudyPayment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('administrators can list and search users', function () {
    $admin = User::factory()->admin()->create();
    $customer = User::factory()->create(['name' => 'منى العتيبي', 'email' => 'mona@example.com']);
    Study::factory()->for($customer)->count(2)->create();
    User::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/Index')
            ->has('users.data', 3)
            ->has('users.meta.last_page')
            ->has('users.links'));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['search' => 'mona@']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('users.data', 1)
            ->where('users.data.0.id', $customer->id)
            ->where('users.data.0.studies_count', 2));

    $this->actingAs($admin)
        ->get(route('admin.users.index', ['role' => 'admin']))
        ->assertInertia(fn (Assert $page) => $page->has('users.data', 1)->where('users.data.0.id', $admin->id));
});

test("administrators can view a user's studies and payment statuses", function () {
    $payment = StudyPayment::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.users.show', $payment->user))
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/users/Show')
            ->where('user.data.email', $payment->user->email)
            ->has('studies.data', 1)
            ->where('studies.data.0.payment.status.value', 'pending'));
});

test('customers cannot view the user list', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.users.index'))
        ->assertForbidden();
});
