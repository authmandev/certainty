<?php

namespace App\Http\Controllers;

use App\Actions\Payments\SubmitPaymentReceiptAction;
use App\Http\Requests\StorePaymentReceiptRequest;
use App\Models\PaymentReceipt;
use App\Models\Study;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentReceiptController extends Controller
{
    /**
     * Upload a bank-transfer receipt for the study's payment.
     */
    public function store(StorePaymentReceiptRequest $request, Study $study, SubmitPaymentReceiptAction $submitReceipt): RedirectResponse
    {
        /** @var UploadedFile $file */
        $file = $request->file('receipt');

        $submitReceipt->handle($study->payment()->firstOrFail(), $request->user(), $file);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Receipt uploaded. We will review it shortly.')]);

        return to_route('studies.show', $study);
    }

    /**
     * Stream a receipt file to its owner or an administrator. Files are never
     * publicly reachable.
     */
    public function show(PaymentReceipt $receipt): StreamedResponse
    {
        Gate::authorize('viewReceipts', $receipt->payment);

        $disk = Storage::disk($receipt->disk);
        abort_unless($disk->exists($receipt->file_path), 404);

        return $disk->response($receipt->file_path, $receipt->file_name, [
            'Content-Type' => $receipt->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ], 'inline');
    }
}
