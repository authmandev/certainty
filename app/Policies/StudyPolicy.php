<?php

namespace App\Policies;

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
}
