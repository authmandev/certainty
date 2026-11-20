<?php

namespace App\Policies;

use App\Models\StudyPayment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudyPaymentPolicy
{
    /**
     * Determine whether the user can submit a bank-transfer receipt.
     *
     * Other customers get a 404 so they cannot learn that the payment exists.
     */
    public function submitReceipt(User $user, StudyPayment $payment): Response
    {
        if ($payment->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return $payment->acceptsReceipts()
            ? Response::allow()
            : Response::deny(__('This payment has already been approved.'));
    }

    /**
     * Determine whether the user can open the payment's receipt files: the customer who owns it, or an administrator.
     */
    public function viewReceipts(User $user, StudyPayment $payment): Response
    {
        return $user->isAdmin() || $payment->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can approve or reject the payment (BRD FR-021).
     */
    public function review(User $user, StudyPayment $payment): bool
    {
        return $user->isAdmin();
    }
}
