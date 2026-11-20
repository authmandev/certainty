<?php

use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

function storedReceipt(): PaymentReceipt
{
    $receipt = PaymentReceipt::factory()->create();
    Storage::disk('local')->put($receipt->file_path, '%PDF-1.4 test');

    return $receipt;
}

test('the customer who uploaded a receipt can open it', function () {
    $receipt = storedReceipt();

    $this->actingAs($receipt->uploader)
        ->get(route('receipts.show', $receipt))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('X-Content-Type-Options', 'nosniff');
});

test('administrators can open any receipt', function () {
    $receipt = storedReceipt();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('receipts.show', $receipt))
        ->assertOk();
});

test("customers cannot open another customer's receipt", function () {
    $receipt = storedReceipt();

    $this->actingAs(User::factory()->create())
        ->get(route('receipts.show', $receipt))
        ->assertNotFound();
});

test('guests cannot open receipts', function () {
    $this->get(route('receipts.show', storedReceipt()))
        ->assertRedirect(route('login'));
});
