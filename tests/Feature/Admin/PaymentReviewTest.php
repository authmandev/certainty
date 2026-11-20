<?php

use App\Enums\PaymentStatus;
use App\Enums\ReceiptStatus;
use App\Models\PaymentReceipt;
use App\Models\StudyPayment;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('administrators see payments under review first', function () {
    StudyPayment::factory()->create(['status' => PaymentStatus::Approved]);
    $underReview = PaymentReceipt::factory()->create()->payment;
    StudyPayment::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/Index')
            ->has('payments.data', 3)
            ->where('payments.data.0.id', $underReview->id)
            ->where('statusCounts.under_review', 1),
        );
});

test('payments can be filtered by status and searched', function () {
    $approved = StudyPayment::factory()->create(['status' => PaymentStatus::Approved]);
    StudyPayment::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.payments.index', ['status' => 'approved']))
        ->assertInertia(fn (Assert $page) => $page
            ->has('payments.data', 1)
            ->where('payments.data.0.id', $approved->id),
        );

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.payments.index', ['search' => $approved->user->email]))
        ->assertInertia(fn (Assert $page) => $page
            ->has('payments.data', 1)
            ->where('payments.data.0.id', $approved->id),
        );
});

test('administrators can open a payment with its review history', function () {
    $receipt = PaymentReceipt::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.payments.show', $receipt->payment))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('admin/payments/Show')
            ->where('payment.data.customer.email', $receipt->uploader->email)
            ->where('payment.data.study.reference', $receipt->payment->study->reference)
            ->has('payment.data.receipts', 1),
        );
});

test('approving a receipt approves the payment', function () {
    $receipt = PaymentReceipt::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.payments.approve', $receipt->payment))
        ->assertRedirect(route('admin.payments.show', $receipt->payment));

    $payment = $receipt->payment->fresh();
    expect($payment->status)->toBe(PaymentStatus::Approved)
        ->and($payment->approved_by)->toBe($admin->id)
        ->and($payment->approved_at)->not->toBeNull()
        ->and($receipt->fresh()->status)->toBe(ReceiptStatus::Approved)
        ->and($receipt->fresh()->reviewed_by)->toBe($admin->id);
});

test('rejecting a receipt records the reason', function () {
    $receipt = PaymentReceipt::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.payments.reject', $receipt->payment), ['reason' => 'المبلغ المحوّل أقل من المطلوب'])
        ->assertRedirect(route('admin.payments.show', $receipt->payment));

    expect($receipt->payment->fresh()->status)->toBe(PaymentStatus::Rejected)
        ->and($receipt->fresh())
        ->status->toBe(ReceiptStatus::Rejected)
        ->rejection_reason->toBe('المبلغ المحوّل أقل من المطلوب')
        ->reviewed_by->toBe($admin->id);
});

test('a rejection needs a reason', function () {
    $receipt = PaymentReceipt::factory()->create();

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.payments.reject', $receipt->payment), ['reason' => ''])
        ->assertSessionHasErrors('reason');

    expect($receipt->payment->fresh()->status)->toBe(PaymentStatus::UnderReview);
});

test('only a payment with a receipt under review can be reviewed', function (PaymentStatus $status, string $action) {
    $payment = StudyPayment::factory()->create(['status' => $status]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route("admin.payments.{$action}", $payment), ['reason' => 'سبب الرفض المكتوب'])
        ->assertSessionHasErrors('payment');

    expect($payment->fresh()->status)->toBe($status);
})->with([
    'pending, approve' => [PaymentStatus::Pending, 'approve'],
    'approved, reject' => [PaymentStatus::Approved, 'reject'],
    'approved, approve' => [PaymentStatus::Approved, 'approve'],
    'rejected, approve' => [PaymentStatus::Rejected, 'approve'],
]);

test('customers cannot review payments', function (string $method, string $route) {
    $receipt = PaymentReceipt::factory()->create();

    $this->actingAs($receipt->uploader)
        ->{$method}(route($route, $receipt->payment), ['reason' => 'سبب الرفض المكتوب'])
        ->assertForbidden();

    expect($receipt->payment->fresh()->status)->toBe(PaymentStatus::UnderReview);
})->with([
    ['get', 'admin.payments.index'],
    ['get', 'admin.payments.show'],
    ['post', 'admin.payments.approve'],
    ['post', 'admin.payments.reject'],
]);
