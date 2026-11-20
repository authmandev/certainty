<?php

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('the command creates a verified administrator', function () {
    $this->artisan('certainty:create-admin', ['email' => 'ops@example.com', '--name' => 'فريق العمليات'])
        ->expectsQuestion('Password', 'a-strong-password-123')
        ->assertSuccessful();

    $user = User::query()->where('email', 'ops@example.com')->sole();
    expect($user->role)->toBe(UserRole::Admin)
        ->and($user->email_verified_at)->not->toBeNull()
        ->and(Hash::check('a-strong-password-123', $user->password))->toBeTrue();
});

test('the command refuses an email that is already registered', function () {
    $existing = User::factory()->create(['email' => 'taken@example.com']);

    $this->artisan('certainty:create-admin', ['email' => 'taken@example.com', '--name' => 'مكرر'])
        ->expectsQuestion('Password', 'a-strong-password-123')
        ->assertFailed();

    expect($existing->fresh()->role)->toBe(UserRole::Customer);
});

test('the command can promote an existing user', function () {
    $user = User::factory()->create();

    $this->artisan('certainty:create-admin', ['email' => $user->email, '--promote' => true])
        ->assertSuccessful();

    expect($user->fresh()->role)->toBe(UserRole::Admin);
});
