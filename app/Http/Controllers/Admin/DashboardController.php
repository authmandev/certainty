<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Enums\StudyStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\AuditLogResource;
use App\Models\Study;
use App\Models\StudyAuditLog;
use App\Models\StudyPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Days ahead that count as "expiring soon".
     */
    private const int EXPIRING_WITHIN_DAYS = 7;

    /**
     * Days back that count as "new" customers.
     */
    private const int NEW_CUSTOMERS_WITHIN_DAYS = 30;

    /**
     * Show the admin panel overview.
     */
    public function __invoke(): Response
    {
        $paymentCounts = StudyPayment::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return Inertia::render('admin/Dashboard', [
            'customers' => [
                'total' => User::query()->where('role', UserRole::Customer)->count(),
                'new' => User::query()->where('role', UserRole::Customer)->where('created_at', '>=', now()->subDays(self::NEW_CUSTOMERS_WITHIN_DAYS))->count(),
            ],
            'studyCounts' => [
                'draft' => Study::query()->where('status', StudyStatus::Draft)->where('expires_at', '>', now())->count(),
                'expiring' => Study::query()->where('status', StudyStatus::Draft)->whereBetween('expires_at', [now(), now()->addDays(self::EXPIRING_WITHIN_DAYS)])->count(),
                'expired' => Study::query()->where(fn (Builder $query) => $query
                    ->where('status', StudyStatus::Expired)
                    ->orWhere(fn (Builder $query) => $query->where('status', StudyStatus::Draft)->where('expires_at', '<=', now())))->count(),
                'finalized' => Study::query()->where('status', StudyStatus::Finalized)->count(),
            ],
            'paymentCounts' => collect(PaymentStatus::cases())->mapWithKeys(fn (PaymentStatus $status): array => [
                $status->value => (int) ($paymentCounts[$status->value] ?? 0),
            ]),
            'approvedRevenue' => [
                'amount' => (float) StudyPayment::query()->where('status', PaymentStatus::Approved)->where('currency', config('certainty.currency'))->sum('amount'),
                'currency' => config('certainty.currency'),
            ],
            'expiringWithinDays' => self::EXPIRING_WITHIN_DAYS,
            'recentActivity' => AuditLogResource::collection(StudyAuditLog::query()->with(['actor', 'study'])->latest('id')->limit(6)->get()),
        ]);
    }
}
