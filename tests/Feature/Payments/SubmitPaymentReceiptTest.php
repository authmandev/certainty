<?php

use App\Enums\PaymentStatus;
use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyPayment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    Storage::fake('local');
});

function pdfReceipt(string $name = 'receipt.pdf'): UploadedFile
{
    return UploadedFile::fake()->createWithContent($name, "%PDF-1.4\n1 0 obj << /Type /Catalog >> endobj\ntrailer << /Root 1 0 R >>\n%%EOF\n");
}

test('the study page shows the transfer instructions', function () {
    config(['certainty.bank' => ['name' => 'البنك الأهلي', 'account_name' => 'شركة يقين', 'iban' => 'SA0380000000608010167519']]);
    $payment = StudyPayment::factory()->create();

    $this->actingAs($payment->user)
        ->get(route('studies.show', $payment->study))
        ->assertInertia(fn (Assert $page) => $page
            ->where('bank.iban', 'SA0380000000608010167519')
            ->where('payment.data.reference_number', $payment->reference_number)
            ->where('payment.data.accepts_receipts', true)
            ->where('receiptRules.max_kilobytes', 5120),
        );
});

test('a customer can upload a receipt, which puts the payment under review', function (UploadedFile $file) {
    $payment = StudyPayment::factory()->create();

    $this->actingAs($payment->user)
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => $file])
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('studies.show', $payment->study));

    $receipt = PaymentReceipt::sole();
    expect($payment->fresh()->status)->toBe(PaymentStatus::UnderReview)
        ->and($receipt->status)->toBe(ReceiptStatus::Submitted)
        ->and($receipt->uploaded_by)->toBe($payment->user_id)
        ->and($receipt->file_name)->toBe($file->getClientOriginalName())
        ->and($receipt->file_path)->toStartWith("receipts/{$payment->id}/")
        ->and($receipt->file_path)->not->toContain($file->getClientOriginalName());
    Storage::disk('local')->assertExists($receipt->file_path);
})->with([
    'pdf' => fn () => pdfReceipt(),
    'jpg' => fn () => UploadedFile::fake()->image('إيصال.jpg'),
    'png' => fn () => UploadedFile::fake()->image('receipt.png'),
]);

test('receipts must be a small JPG, PNG or PDF file', function (UploadedFile $file) {
    $payment = StudyPayment::factory()->create();

    $this->actingAs($payment->user)
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => $file])
        ->assertSessionHasErrors('receipt');

    expect(PaymentReceipt::count())->toBe(0)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Pending);
})->with([
    'wrong type' => fn () => UploadedFile::fake()->createWithContent('receipt.txt', 'plain text'),
    // A real upload, so the type is detected from the contents, not the name.
    'disguised as an image' => function () {
        $path = tempnam(sys_get_temp_dir(), 'receipt');
        file_put_contents($path, 'plain text pretending to be an image');

        return new UploadedFile($path, 'receipt.png', 'image/png', null, true);
    },
    'too large' => fn () => UploadedFile::fake()->image('receipt.jpg')->size(6000),
]);

test('a new upload replaces a receipt still under review', function () {
    $previous = PaymentReceipt::factory()->create();
    $payment = $previous->payment;

    $this->actingAs($payment->user)
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => pdfReceipt()])
        ->assertSessionHasNoErrors();

    expect($previous->fresh()->status)->toBe(ReceiptStatus::Replaced)
        ->and($payment->receipts()->where('status', ReceiptStatus::Submitted)->count())->toBe(1);
});

test('a rejected payment can be resubmitted', function () {
    $rejected = PaymentReceipt::factory()->create(['status' => ReceiptStatus::Rejected, 'rejection_reason' => 'المبلغ غير مطابق']);
    $payment = $rejected->payment;
    $payment->forceFill(['status' => PaymentStatus::Rejected])->save();

    $this->actingAs($payment->user)
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => pdfReceipt()])
        ->assertSessionHasNoErrors();

    expect($payment->fresh()->status)->toBe(PaymentStatus::UnderReview)
        ->and($rejected->fresh()->status)->toBe(ReceiptStatus::Rejected);
});

test('an approved payment accepts no more receipts', function () {
    $payment = StudyPayment::factory()->create(['status' => PaymentStatus::Approved]);

    $this->actingAs($payment->user)
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => pdfReceipt()])
        ->assertForbidden();

    expect(PaymentReceipt::count())->toBe(0);
});

test("customers cannot upload a receipt for another customer's study", function () {
    $payment = StudyPayment::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('studies.receipts.store', $payment->study), ['receipt' => pdfReceipt()])
        ->assertNotFound();

    expect(PaymentReceipt::count())->toBe(0);
});

test('guests cannot upload receipts', function () {
    $payment = StudyPayment::factory()->create();

    $this->post(route('studies.receipts.store', $payment->study), ['receipt' => pdfReceipt()])
        ->assertRedirect(route('login'));
});
