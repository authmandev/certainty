<?php

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\Study;
use App\Models\StudyPayment;
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

test('administrators see the platform summary', function () {
    User::factory()->create(['created_at' => now()->subDays(60)]);
    StudyPayment::factory()->create(['status' => PaymentStatus::UnderReview]);
    StudyPayment::factory()->create(['status' => PaymentStatus::Approved, 'amount' => 1200, 'currency' => 'SAR']);
    Study::factory()->create(['expires_at' => now()->addDays(3)]);
    Study::factory()->expired()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/Dashboard')
            ->where('customers.total', 5)
            ->where('customers.new', 4)
            ->where('paymentCounts.under_review', 1)
            ->where('paymentCounts.pending', 0)
            ->where('approvedRevenue.amount', 1200)
            ->where('studyCounts.draft', 3)
            ->where('studyCounts.expiring', 1)
            ->where('studyCounts.expired', 1),
        );
});
