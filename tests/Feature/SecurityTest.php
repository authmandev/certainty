<?php

use App\Models\PaymentReceipt;
use Illuminate\Support\Facades\Storage;

test('web responses carry baseline security headers', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeaderMissing('Strict-Transport-Security');
});

test('HTTPS responses ask browsers to stay on HTTPS', function () {
    $this->get('https://localhost/')
        ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
});

test('private files have no public URL', function () {
    Storage::fake('local');
    $receipt = PaymentReceipt::factory()->create();
    Storage::disk('local')->put($receipt->file_path, '%PDF-1.4');

    $this->get('/storage/'.$receipt->file_path)->assertNotFound();
    $this->get('/storage/app/private/'.$receipt->file_path)->assertNotFound();
});
