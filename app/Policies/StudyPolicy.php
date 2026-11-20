<?php

namespace App\Policies;

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Models\Study;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class StudyPolicy
{
    /**
     * Determine whether the user can create studies. Only customers buy studies (BRD §2.2).
     */
    public function create(User $user): bool
    {
        return ! $user->isAdmin();
    }

    /**
     * Determine whether the user can view the study from the customer area.
     *
     * Other customers get a 404 so they cannot learn that the study exists.
     */
    public function view(User $user, Study $study): Response
    {
        return $study->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the customer can edit the study's draft input.
     */
    public function update(User $user, Study $study): Response
    {
        if ($study->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return $study->isEditable()
            ? Response::allow()
            : Response::deny(__('This study can no longer be edited.'));
    }

    /**
     * Determine whether the customer can finalize the study. Whether it is
     * ready is checked by FinalizeStudyAction (BRD FR-016).
     */
    public function finalize(User $user, Study $study): Response
    {
        return $study->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }

    /**
     * Determine whether the user can download a study's final report: the
     * owning customer or an administrator, once the study is finalized and
     * its payment approved (BRD FR-035).
     */
    public function downloadReport(User $user, Study $study): Response
    {
        if (! $user->isAdmin() && $study->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return $study->status === StudyStatus::Finalized && $study->payment?->status === PaymentStatus::Approved
            ? Response::allow()
            : Response::deny(__('The final report is not available for this study.'));
    }
}
