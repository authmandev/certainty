<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;

test('only administrators may access the admin panel', function (UserRole $role, bool $allowed) {
    $user = User::factory()->create(['role' => $role]);

    expect(Gate::forUser($user)->allows('access-admin-panel'))->toBe($allowed);
})->with([
    'customer' => [UserRole::Customer, false],
    'admin' => [UserRole::Admin, true],
]);

test('guests are redirected to the login page', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

test('customers cannot visit the admin dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('admin.dashboard'))
        ->assertForbidden();
});

test('administrators can visit the admin dashboard', function () {
    User::factory()->count(2)->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->where('customersCount', 2),
        );
});
