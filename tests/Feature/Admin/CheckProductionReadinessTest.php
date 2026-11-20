<?php

use App\Models\User;
use Database\Seeders\ReferenceOptionSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

test('a server without reference lists or an administrator is not ready', function () {
    $this->artisan('certainty:check-production')
        ->expectsOutputToContain('FAIL')
        ->assertFailed();
});

test('a configured server is ready', function () {
    $this->seed(ReferenceOptionSeeder::class);
    User::factory()->admin()->create();
    config([
        'app.debug' => false,
        'laravel-pdf.driver' => 'gotenberg',
        'laravel-pdf.gotenberg.url' => 'http://gotenberg:3000',
    ]);

    $this->artisan('certainty:check-production')
        ->doesntExpectOutputToContain('FAIL')
        ->assertSuccessful();
});

test('a public disk for receipts fails the check', function () {
    $this->seed(ReferenceOptionSeeder::class);
    User::factory()->admin()->create();
    config(['certainty.receipts.disk' => 'public', 'laravel-pdf.driver' => 'gotenberg']);

    $this->artisan('certainty:check-production')->assertFailed();
});
