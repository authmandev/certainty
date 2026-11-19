<?php

namespace App\Actions\Studies;

use App\Enums\PaymentStatus;
use App\Models\Study;
use App\Models\User;
use App\Services\Studies\StudyInputSchema;
use Illuminate\Support\Facades\DB;

class CreateStudyAction
{
    /**
     * Create a draft study with its pending bank-transfer payment and empty input.
     *
     * @param  array{title: string, project_name: string}  $attributes
     */
    public function handle(User $customer, array $attributes): Study
    {
        return DB::transaction(function () use ($customer, $attributes): Study {
            $study = $customer->studies()->make($attributes);
            $study->expires_at = now()->addDays(config('certainty.draft_duration_days'));
            $study->calculation_version = config('certainty.calculation_version');
            $study->save();

            $study->forceFill(['reference' => $this->formatReference('CS', $study->id)])->save();

            $payment = $study->payment()->make();
            $payment->user_id = $customer->id;
            $payment->amount = (string) config('certainty.study_price');
            $payment->currency = config('certainty.currency');
            $payment->status = PaymentStatus::Pending;
            $payment->save();

            $payment->forceFill(['reference_number' => $this->formatReference('PAY', $payment->id)])->save();

            $study->input()->create([
                'input_data' => StudyInputSchema::defaults(),
                'schema_version' => StudyInputSchema::VERSION,
            ]);

            return $study->setRelation('payment', $payment);
        });
    }

    /**
     * Build a readable, zero-padded reference such as CS-000042.
     */
    private function formatReference(string $prefix, int $id): string
    {
        return $prefix.'-'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }
}
