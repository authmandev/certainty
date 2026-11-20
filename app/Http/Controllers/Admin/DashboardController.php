<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\StudyPayment;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the admin panel overview.
     */
    public function __invoke(): Response
    {
        return Inertia::render('admin/Dashboard', [
            'customersCount' => User::query()->where('role', UserRole::Customer)->count(),
            'paymentsUnderReviewCount' => StudyPayment::query()->where('status', PaymentStatus::UnderReview)->count(),
        ]);
    }
}
