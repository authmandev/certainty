<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Payments\ReviewPaymentAction;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Resources\StudyPaymentResource;
use App\Models\StudyPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * List payments, receipts waiting for review first.
     */
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::enum(PaymentStatus::class)],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $search = $filters['search'] ?? null;

        $payments = StudyPayment::query()
            ->with(['user', 'study', 'latestReceipt'])
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($search, fn (Builder $query, string $search) => $query->where(fn (Builder $query) => $query
                ->where('reference_number', 'like', "%{$search}%")
                ->orWhereHas('study', fn (Builder $query) => $query->where('reference', 'like', "%{$search}%")->orWhere('title', 'like', "%{$search}%"))
                ->orWhereHas('user', fn (Builder $query) => $query->where('email', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"))))
            ->orderByRaw('case when status = ? then 0 else 1 end', [PaymentStatus::UnderReview->value])
            ->latest('updated_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('admin/payments/Index', [
            'payments' => StudyPaymentResource::collection($payments),
            'filters' => [
                'status' => $filters['status'] ?? null,
                'search' => $search,
            ],
            'statusCounts' => StudyPayment::query()
                ->toBase()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'statuses' => collect(PaymentStatus::cases())->map(fn (PaymentStatus $status): array => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    /**
     * Show a payment with its receipts and review history.
     */
    public function show(StudyPayment $payment): Response
    {
        return Inertia::render('admin/payments/Show', [
            'payment' => new StudyPaymentResource($payment->load(['user', 'study', 'receipts.reviewer', 'approver'])),
            'approvedBy' => $payment->approver?->name,
        ]);
    }

    /**
     * Approve the receipt under review.
     */
    public function approve(StudyPayment $payment, ReviewPaymentAction $review, Request $request): RedirectResponse
    {
        Gate::authorize('review', $payment);

        $review->approve($payment, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment approved.')]);

        return to_route('admin.payments.show', $payment);
    }

    /**
     * Reject the receipt under review with a reason.
     */
    public function reject(RejectPaymentRequest $request, StudyPayment $payment, ReviewPaymentAction $review): RedirectResponse
    {
        $review->reject($payment, $request->user(), $request->string('reason')->trim()->toString());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Payment rejected.')]);

        return to_route('admin.payments.show', $payment);
    }
}
